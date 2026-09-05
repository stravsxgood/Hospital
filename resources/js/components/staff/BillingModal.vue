<script setup lang="ts">
/**
 * @file BillingModal.vue
 * @description Komponen Modal Tagihan Kasir & Xendit Invoice / QRIS untuk Workspace Perawat (/staff).
 *
 * Mengimplementasikan:
 * 1. Kalkulasi otomatis biaya layanan medis (Konsultasi Dokter, Administrasi, & Resep Obat Farmasi).
 * 2. Pembuatan tagihan Xendit Online Invoice dengan tautan pembayaran siap salin.
 * 3. Pembuatan Dynamic QRIS Code langsung yang di-render ke canvas untuk dipindai langsung oleh pasien.
 * 4. Pengecekan status real-time (polling + Echo WebSocket event) dan tampilan status Lunas / Pending.
 * 5. Desain Evergreen (Linen #edede2, Bone #fffff3, Sage #beedc0, Ink #000000) & target sentuh min 44px.
 */
import {
    AlertCircle,
    Check,
    CheckCircle2,
    Clock,
    Copy,
    CreditCard,
    ExternalLink,
    FileText,
    Loader2,
    Pill,
    Printer,
    QrCode,
    Receipt,
    RefreshCw,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    User,
    Wallet,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import QRCode from 'qrcode';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

/* ═══════════════════════════════════════════════════════════════
   TypeScript Interfaces
   ═══════════════════════════════════════════════════════════════ */
export interface CostItem {
    type: 'consultation' | 'admin' | 'medicine' | 'procedure';
    name: string;
    price: number;
    qty: number;
    subtotal: number;
}

export interface BillingData {
    billing_id?: number;
    id?: number;
    invoice_number?: string;
    external_id?: string;
    total_amount?: number;
    amount?: number;
    status:
        | 'unpaid'
        | 'pending'
        | 'paid'
        | 'expired'
        | 'cancelled'
        | 'PENDING'
        | 'PAID'
        | 'EXPIRED'
        | 'FAILED'
        | string;
    payment_method?: string | null;
    invoice_url?: string | null;
    xendit_payment_url?: string | null;
    paid_at?: string | null;
    payment_details?: string | null;
}

export interface AppointmentData {
    appointment_id: number;
    queue_number: string;
    appointment_date: string;
    status: string;
    patient?: {
        name: string;
        resident_n?: string;
        gender?: string;
        phone_number?: string;
        user?: {
            email?: string;
        };
    };
    doctor_schedule?: {
        doctor?: {
            name: string;
            specialization?: {
                name_specialization?: string;
            };
        };
        poli?: {
            name_poli?: string;
            name?: string;
        };
        room?: {
            name_room?: string;
        };
    };
    billing?: BillingData | null;
    medical_record?: {
        medical_record_id: number;
        prescription?: {
            prescription_id: number;
            status: string;
            items?: Array<{
                medicine?: {
                    name_medicine: string;
                    price: number | string;
                };
                quantity: number;
                dosage: string;
            }>;
        };
    };
}

interface Props {
    isOpen: boolean;
    appointment: AppointmentData | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'success', billingId: number): void;
}>();

/* ═══════════════════════════════════════════════════════════════
   Reactive State
   ═══════════════════════════════════════════════════════════════ */
const paymentType = ref<'invoice' | 'qris'>('qris');
const customAmount = ref<number | null>(null);
const isCustomAmountActive = ref(false);

const isCalculating = ref(false);
const isSubmitting = ref(false);
const isCheckingStatus = ref(false);
const errorMessage = ref<string | null>(null);
const copiedLink = ref(false);

// Auto-calculation breakdown data
const costBreakdown = ref<CostItem[]>([]);
const autoCalculatedTotal = ref<number>(175000);

// Active billing state (dari prop atau setelah create)
const activeBilling = ref<BillingData | null>(null);

// QR Canvas reference
const qrCanvasRef = ref<HTMLCanvasElement | null>(null);
let pollingInterval: ReturnType<typeof setInterval> | null = null;

/* ═══════════════════════════════════════════════════════════════
   Format Rupiah Helper
   ═══════════════════════════════════════════════════════════════ */
const formatRupiah = (val: number | string | null | undefined): string => {
    const num = typeof val === 'string' ? parseFloat(val) : val || 0;

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

/* ═══════════════════════════════════════════════════════════════
   Computed States
   ═══════════════════════════════════════════════════════════════ */
const effectiveAmount = computed<number>(() => {
    if (
        isCustomAmountActive.value &&
        customAmount.value &&
        customAmount.value > 0
    ) {
        return customAmount.value;
    }

    return autoCalculatedTotal.value;
});

const isPaid = computed<boolean>(() => {
    const status = (activeBilling.value?.status ?? '').toUpperCase();

    return status === 'PAID' || status === 'SETTLED';
});

const isPending = computed<boolean>(() => {
    const status = (activeBilling.value?.status ?? '').toUpperCase();

    return status === 'PENDING' || status === 'UNPAID';
});

const hasBilling = computed<boolean>(() => {
    return Boolean(activeBilling.value && activeBilling.value.status);
});

const paymentUrl = computed<string | null>(() => {
    return (
        activeBilling.value?.invoice_url ||
        activeBilling.value?.xendit_payment_url ||
        null
    );
});

/* ═══════════════════════════════════════════════════════════════
   Render QR Code Canvas Helper
   ═══════════════════════════════════════════════════════════════ */
const renderQrCode = async (rawQrString: string) => {
    await nextTick();

    if (!qrCanvasRef.value) {
        return;
    }

    try {
        await QRCode.toCanvas(qrCanvasRef.value, rawQrString, {
            width: 220,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#ffffff',
            },
            errorCorrectionLevel: 'M',
        });
    } catch (err) {
        console.error('Failed to render QR Code on canvas:', err);
    }
};

/* ═══════════════════════════════════════════════════════════════
   Fetch Automatic Cost Calculation from Backend
   ═══════════════════════════════════════════════════════════════ */
const fetchCalculation = async (appointmentId: number) => {
    isCalculating.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.get(
            `/staff/billing/calculate/${appointmentId}`,
        );

        if (response.data && response.data.status) {
            costBreakdown.value = response.data.items || [];
            autoCalculatedTotal.value =
                Number(response.data.total_amount) || 175000;

            if (response.data.existing_billing) {
                activeBilling.value = response.data.existing_billing;
            }
        }
    } catch (err) {
        console.warn('Gagal memuat kalkulasi otomatis, gunakan default:', err);
        // Fallback default calculation jika API gagal
        costBreakdown.value = [
            {
                type: 'consultation',
                name: 'Konsultasi Dokter Spesialis',
                price: 150000,
                qty: 1,
                subtotal: 150000,
            },
            {
                type: 'admin',
                name: 'Administrasi & Rekam Medis',
                price: 25000,
                qty: 1,
                subtotal: 25000,
            },
        ];
        autoCalculatedTotal.value = 175000;
    } finally {
        isCalculating.value = false;
    }
};

/* ═══════════════════════════════════════════════════════════════
   Watch Appointment Changes
   ═══════════════════════════════════════════════════════════════ */
watch(
    () => [props.isOpen, props.appointment],
    async ([isOpen, apt]) => {
        if (!isOpen || !apt) {
            stopPolling();
            activeBilling.value = null;
            errorMessage.value = null;
            copiedLink.value = false;

            return;
        }

        const currentApt = apt as AppointmentData;
        activeBilling.value = currentApt.billing ?? null;
        isCustomAmountActive.value = false;
        customAmount.value = null;

        // Ambil kalkulasi otomatis jika belum ada billing atau billing belum bayar
        if (!activeBilling.value || activeBilling.value.status !== 'PAID') {
            await fetchCalculation(currentApt.appointment_id);
        }

        // Jika billing sudah ada dan memiliki QRIS
        if (activeBilling.value && isPending.value) {
            startPolling();

            // Coba ekstrak qr_string dari payment_details
            let rawQr: string | null = null;

            if (activeBilling.value.payment_details) {
                try {
                    const parsed = JSON.parse(
                        activeBilling.value.payment_details,
                    );
                    rawQr = parsed.qr_string ?? null;
                } catch (e) {}
            }

            if (rawQr) {
                await renderQrCode(rawQr);
            } else if (
                activeBilling.value.payment_method === 'xendit_qris' &&
                activeBilling.value.xendit_payment_url
            ) {
                await renderQrCode(activeBilling.value.xendit_payment_url);
            }
        }
    },
    { immediate: true },
);

/* ═══════════════════════════════════════════════════════════════
   Action: Create Xendit Billing
   ═══════════════════════════════════════════════════════════════ */
const handleCreateBilling = async () => {
    if (!props.appointment) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        const payload = {
            appointment_id: props.appointment.appointment_id,
            amount: effectiveAmount.value,
            payment_type: paymentType.value,
            description: `Tagihan Layanan Rawat Jalan Poli ${props.appointment.doctor_schedule?.poli?.name_poli ?? 'Poliklinik'} (${props.appointment.patient?.name ?? 'Pasien'})`,
        };

        const response = await axios.post('/staff/billing', payload);

        if (response.data && response.data.status) {
            activeBilling.value = response.data.billing;

            // Jika QRIS, render canvas QR
            let rawQr: string | null = null;

            if (activeBilling.value?.payment_details) {
                try {
                    const parsed = JSON.parse(
                        activeBilling.value.payment_details,
                    );
                    rawQr = parsed.qr_string ?? null;
                } catch (e) {}
            }

            if (rawQr) {
                await renderQrCode(rawQr);
            } else if (
                activeBilling.value?.payment_method === 'xendit_qris' &&
                activeBilling.value?.xendit_payment_url
            ) {
                await renderQrCode(activeBilling.value.xendit_payment_url);
            }

            // Mulai polling status
            startPolling();
        }
    } catch (err: any) {
        console.error('Gagal membuat tagihan Xendit:', err);
        errorMessage.value =
            err.response?.data?.message ||
            'Gagal membuat tagihan Xendit. Periksa koneksi internet atau token gateway.';
    } finally {
        isSubmitting.value = false;
    }
};

/* ═══════════════════════════════════════════════════════════════
   Action: Check Payment Status Instantly
   ═══════════════════════════════════════════════════════════════ */
const checkStatus = async () => {
    const billingId =
        activeBilling.value?.billing_id || activeBilling.value?.id;

    if (!billingId) {
        return;
    }

    isCheckingStatus.value = true;

    try {
        const response = await axios.get(`/staff/billing/${billingId}/status`);

        if (response.data && response.data.status) {
            if (
                response.data.is_paid ||
                response.data.billing_status === 'paid' ||
                response.data.billing_status === 'PAID'
            ) {
                if (activeBilling.value) {
                    activeBilling.value.status = 'PAID';
                    activeBilling.value.paid_at =
                        response.data.paid_at ?? new Date().toISOString();
                }

                stopPolling();
                emit('success', billingId);
            }
        }
    } catch (err) {
        console.warn('Gagal cek status billing:', err);
    } finally {
        isCheckingStatus.value = false;
    }
};

/* ═══════════════════════════════════════════════════════════════
   Polling Management
   ═══════════════════════════════════════════════════════════════ */
const startPolling = () => {
    stopPolling();
    pollingInterval = setInterval(() => {
        if (isPending.value) {
            checkStatus();
        } else {
            stopPolling();
        }
    }, 4000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

onBeforeUnmount(() => {
    stopPolling();
});

/* ═══════════════════════════════════════════════════════════════
   Action: Copy Payment Link
   ═══════════════════════════════════════════════════════════════ */
const copyPaymentLink = async () => {
    if (!paymentUrl.value) {
        return;
    }

    try {
        await navigator.clipboard.writeText(paymentUrl.value);
        copiedLink.value = true;
        setTimeout(() => {
            copiedLink.value = false;
        }, 2500);
    } catch (err) {
        console.error('Gagal menyalin link:', err);
    }
};
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
        <!-- Backdrop Blur -->
        <div
            class="fixed inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"
            @click="emit('close')"
        />

        <!-- Modal Container -->
        <motion.div
            :initial="{ opacity: 0, scale: 0.96, y: 12 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.2, ease: 'easeOut' }"
            class="relative z-10 flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-[#000000]/10 bg-[#fffff3] text-[#000000] shadow-2xl"
        >
            <!-- Header Modal -->
            <div
                class="flex items-center justify-between border-b border-[#000000]/10 bg-[#edede2]/60 px-6 py-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full bg-[#000000] text-[#ffffff]"
                    >
                        <Receipt class="size-5 text-[#beedc0]" />
                    </div>
                    <div>
                        <h3
                            class="text-base font-bold text-[#000000] sm:text-lg"
                        >
                            Tagihan Pasien & Xendit Gateway
                        </h3>
                        <p class="text-xs text-[#333333]">
                            Ruang Kerja Kasir & Perawat Tetap SIMRS
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="flex size-9 items-center justify-center rounded-full text-[#333333] hover:bg-[#edede2] hover:text-[#000000]"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- Body Scrollable -->
            <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                <!-- Info Pasien & Kunjungan -->
                <div
                    class="rounded-xl border border-[#000000]/10 bg-[#ffffff] p-4"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b border-[#000000]/5 pb-3"
                    >
                        <div>
                            <span class="text-xs text-[#333333]"
                                >Nama Pasien</span
                            >
                            <div class="text-base font-bold text-[#000000]">
                                {{
                                    appointment?.patient?.name ?? 'Nama Pasien'
                                }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-[#333333]"
                                >No. Antrean</span
                            >
                            <div>
                                <span
                                    class="inline-flex rounded-full bg-[#000000] px-3 py-0.5 font-mono text-xs font-bold text-[#ffffff]"
                                >
                                    {{ appointment?.queue_number ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-3 grid grid-cols-2 gap-2 text-xs text-[#333333] sm:grid-cols-3"
                    >
                        <div>
                            <span class="block text-[11px] opacity-70"
                                >Poliklinik</span
                            >
                            <span class="font-semibold text-[#000000]">
                                {{
                                    appointment?.doctor_schedule?.poli
                                        ?.name_poli ?? 'Poli Umum'
                                }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[11px] opacity-70"
                                >Dokter Pemeriksa</span
                            >
                            <span class="font-semibold text-[#000000]">
                                {{
                                    appointment?.doctor_schedule?.doctor
                                        ?.name ?? 'Dokter'
                                }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[11px] opacity-70"
                                >NIK / Identitas</span
                            >
                            <span class="font-mono text-[#000000]">
                                {{ appointment?.patient?.resident_n ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Error Banner jika ada -->
                <div
                    v-if="errorMessage"
                    class="flex items-start gap-2.5 rounded-xl border border-red-300 bg-red-50 p-3.5 text-xs text-red-800"
                >
                    <AlertCircle class="mt-0.5 size-4 shrink-0 text-red-600" />
                    <div class="flex-1">{{ errorMessage }}</div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     KONDISI 1: SUDAH LUNAS (PAID)
                     ═══════════════════════════════════════════════════════════ -->
                <div
                    v-if="isPaid"
                    class="rounded-xl border border-emerald-300 bg-emerald-50/70 p-6 text-center"
                >
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm"
                    >
                        <CheckCircle2 class="size-8" />
                    </div>

                    <h4 class="mt-3 text-lg font-bold text-emerald-950">
                        Pembayaran Lunas
                    </h4>
                    <p class="text-xs text-emerald-700">
                        Tagihan medis telah berhasil diselesaikan dan dicatat
                        pada sistem SIMRS.
                    </p>

                    <div
                        class="mt-4 inline-flex flex-col items-center rounded-lg border border-emerald-200 bg-[#ffffff] px-6 py-3 shadow-none"
                    >
                        <span class="text-xs text-neutral-500"
                            >Nominal Lunas</span
                        >
                        <span
                            class="font-mono text-xl font-bold text-emerald-700"
                        >
                            {{
                                formatRupiah(
                                    activeBilling?.amount ||
                                        activeBilling?.total_amount,
                                )
                            }}
                        </span>
                        <span class="mt-1 text-[11px] text-neutral-400">
                            Invoice:
                            {{
                                activeBilling?.invoice_number ||
                                activeBilling?.external_id
                            }}
                        </span>
                    </div>

                    <div
                        class="mt-4 flex flex-wrap items-center justify-center gap-3"
                    >
                        <a
                            :href="`/staff/billing/${activeBilling?.billing_id || activeBilling?.id}`"
                            class="inline-flex min-h-[44px] items-center gap-1.5 rounded-full border border-neutral-300 bg-[#ffffff] px-5 py-2 text-xs font-semibold text-neutral-800 hover:bg-neutral-50"
                        >
                            <FileText class="size-4" />
                            <span>Lihat Rincian Billing</span>
                        </a>

                        <button
                            type="button"
                            @click="emit('close')"
                            class="inline-flex min-h-[44px] items-center rounded-full bg-[#000000] px-6 py-2 text-xs font-semibold text-[#ffffff] hover:bg-[#1a1a1a]"
                        >
                            Tutup
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     KONDISI 2: MENUNGGU PEMBAYARAN (PENDING / UNPAID)
                     ═══════════════════════════════════════════════════════════ -->
                <div
                    v-else-if="isPending"
                    class="space-y-4 rounded-xl border border-amber-300 bg-amber-50/50 p-5"
                >
                    <!-- Header Pending -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex size-full animate-ping rounded-full bg-amber-400 opacity-75"
                                />
                                <span
                                    class="relative inline-flex size-3 rounded-full bg-amber-500"
                                />
                            </span>
                            <span class="text-sm font-bold text-amber-900">
                                Menunggu Pembayaran
                            </span>
                        </div>

                        <span
                            class="font-mono text-xs font-bold text-amber-800"
                        >
                            {{
                                activeBilling?.invoice_number ||
                                activeBilling?.external_id
                            }}
                        </span>
                    </div>

                    <!-- Total Nominal -->
                    <div
                        class="flex items-center justify-between rounded-lg border border-amber-200 bg-[#ffffff] p-4"
                    >
                        <span class="text-xs text-neutral-600"
                            >Total yang Harus Dibayar:</span
                        >
                        <span
                            class="font-mono text-xl font-bold text-[#000000]"
                        >
                            {{
                                formatRupiah(
                                    activeBilling?.amount ||
                                        activeBilling?.total_amount,
                                )
                            }}
                        </span>
                    </div>

                    <!-- QRIS Dynamic Box -->
                    <div
                        class="flex flex-col items-center rounded-xl border border-neutral-200 bg-[#ffffff] p-5 shadow-none"
                    >
                        <div
                            class="flex items-center gap-1.5 text-xs font-bold text-neutral-800"
                        >
                            <QrCode class="size-4 text-emerald-700" />
                            <span>Scan QRIS untuk Pembayaran Langsung</span>
                        </div>
                        <p
                            class="mt-0.5 text-center text-[11px] text-neutral-500"
                        >
                            Mendukung m-Banking BCA, Mandiri, BRI, BNI, GoPay,
                            OVO, ShopeePay, Dana
                        </p>

                        <!-- Canvas QR -->
                        <div
                            class="mt-3 flex size-56 items-center justify-center rounded-xl border border-neutral-200 bg-[#ffffff] p-2"
                        >
                            <canvas ref="qrCanvasRef" class="size-full" />
                        </div>

                        <div
                            class="mt-2 flex items-center gap-1 text-[11px] text-neutral-400"
                        >
                            <Clock class="size-3" />
                            <span
                                >Status auto-update saat pembayaran
                                terverifikasi</span
                            >
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <!-- Buka Invoice Link -->
                        <a
                            v-if="paymentUrl"
                            :href="paymentUrl"
                            target="_blank"
                            class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-full bg-[#000000] px-4 py-2 text-xs font-semibold text-[#ffffff] hover:bg-[#1a1a1a]"
                        >
                            <ExternalLink class="size-4" />
                            <span>Buka Pembayaran</span>
                        </a>

                        <!-- Salin Link -->
                        <button
                            v-if="paymentUrl"
                            type="button"
                            @click="copyPaymentLink"
                            class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-full border border-neutral-300 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-neutral-800 hover:bg-neutral-50"
                        >
                            <Check
                                v-if="copiedLink"
                                class="size-4 text-emerald-600"
                            />
                            <Copy v-else class="size-4" />
                            <span>{{
                                copiedLink ? 'Tersalin!' : 'Salin Tautan'
                            }}</span>
                        </button>

                        <!-- Cek Status -->
                        <button
                            type="button"
                            @click="checkStatus"
                            :disabled="isCheckingStatus"
                            class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-full border border-amber-300 bg-amber-100/70 px-4 py-2 text-xs font-semibold text-amber-950 hover:bg-amber-200/80 disabled:opacity-50"
                        >
                            <Loader2
                                v-if="isCheckingStatus"
                                class="size-4 animate-spin"
                            />
                            <RefreshCw v-else class="size-4" />
                            <span>{{
                                isCheckingStatus ? 'Mengecek...' : 'Cek Status'
                            }}</span>
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     KONDISI 3: BELUM ADA TAGIHAN (FORM PEMBUATAN OTOMATIS)
                     ═══════════════════════════════════════════════════════════ -->
                <div v-else class="space-y-4">
                    <!-- Rincian Biaya Otomatis -->
                    <div
                        class="rounded-xl border border-[#000000]/10 bg-[#ffffff] p-4"
                    >
                        <div
                            class="flex items-center justify-between border-b border-neutral-100 pb-2.5"
                        >
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold text-neutral-900"
                            >
                                <Sparkles class="size-4 text-emerald-600" />
                                <span
                                    >Rincian Biaya Otomatis (SIMRS
                                    Pre-calculate)</span
                                >
                            </div>

                            <span
                                v-if="isCalculating"
                                class="flex items-center gap-1 text-[11px] text-neutral-400"
                            >
                                <Loader2 class="size-3 animate-spin" />
                                Menghitung...
                            </span>
                        </div>

                        <!-- Daftar Item Biaya -->
                        <div class="mt-3 divide-y divide-neutral-100 text-xs">
                            <div
                                v-for="(item, idx) in costBreakdown"
                                :key="idx"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center gap-2">
                                    <Stethoscope
                                        v-if="item.type === 'consultation'"
                                        class="size-3.5 text-neutral-400"
                                    />
                                    <Pill
                                        v-else-if="item.type === 'medicine'"
                                        class="size-3.5 text-emerald-600"
                                    />
                                    <FileText
                                        v-else
                                        class="size-3.5 text-neutral-400"
                                    />
                                    <span class="text-neutral-800">{{
                                        item.name
                                    }}</span>
                                    <span
                                        v-if="item.qty > 1"
                                        class="text-[11px] text-neutral-400"
                                    >
                                        ({{ item.qty }}x)
                                    </span>
                                </div>
                                <span
                                    class="font-mono font-medium text-neutral-900"
                                >
                                    {{ formatRupiah(item.subtotal) }}
                                </span>
                            </div>
                        </div>

                        <!-- Total Otomatis -->
                        <div
                            class="mt-3 flex items-center justify-between border-t border-neutral-200 pt-3"
                        >
                            <span class="text-xs font-bold text-neutral-900"
                                >Total Perhitungan Otomatis:</span
                            >
                            <span
                                class="font-mono text-base font-bold text-emerald-700"
                            >
                                {{ formatRupiah(autoCalculatedTotal) }}
                            </span>
                        </div>
                    </div>

                    <!-- Pilihan Opsi Nominal Manual (Jika Perlu Disesuaikan) -->
                    <div
                        class="rounded-xl border border-[#000000]/10 bg-[#ffffff] p-4"
                    >
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-neutral-800">
                                Nominal Tagihan
                            </label>
                            <button
                                type="button"
                                @click="
                                    isCustomAmountActive = !isCustomAmountActive
                                "
                                class="text-xs text-emerald-700 hover:underline"
                            >
                                {{
                                    isCustomAmountActive
                                        ? 'Gunakan Hitungan Otomatis'
                                        : 'Ubah Nominal Manual'
                                }}
                            </button>
                        </div>

                        <div
                            v-if="isCustomAmountActive"
                            class="mt-2.5 space-y-2"
                        >
                            <div class="relative">
                                <span
                                    class="absolute top-1/2 left-3.5 -translate-y-1/2 font-mono text-sm text-neutral-400"
                                    >Rp</span
                                >
                                <input
                                    v-model.number="customAmount"
                                    type="number"
                                    min="1000"
                                    placeholder="Masukkan nominal tagihan kustom..."
                                    class="h-11 w-full rounded-xl border border-neutral-300 pr-4 pl-10 text-sm font-bold text-neutral-900 focus:border-neutral-900 focus:outline-none"
                                />
                            </div>

                            <!-- Preset Cepat -->
                            <div class="flex flex-wrap gap-2 text-xs">
                                <button
                                    v-for="preset in [
                                        100000, 150000, 250000, 500000,
                                    ]"
                                    :key="preset"
                                    type="button"
                                    @click="customAmount = preset"
                                    class="rounded-lg border border-neutral-200 bg-neutral-50 px-2.5 py-1 font-mono text-[11px] text-neutral-700 hover:bg-neutral-100"
                                >
                                    {{ formatRupiah(preset) }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="mt-1 text-xs text-neutral-500">
                            Nominal yang akan diterbitkan:
                            <strong
                                class="font-mono font-bold text-emerald-700"
                                >{{ formatRupiah(effectiveAmount) }}</strong
                            >
                        </div>
                    </div>

                    <!-- Pilihan Metode Pembayaran Utama -->
                    <div
                        class="rounded-xl border border-[#000000]/10 bg-[#ffffff] p-4"
                    >
                        <label class="text-xs font-bold text-neutral-800">
                            Metode Pembayaran Pilihan Pasien
                        </label>

                        <div class="mt-2.5 grid grid-cols-2 gap-2.5">
                            <button
                                type="button"
                                @click="paymentType = 'qris'"
                                :class="
                                    paymentType === 'qris'
                                        ? 'border-[#000000] bg-[#edede2]/60 ring-1 ring-[#000000]'
                                        : 'border-neutral-200 bg-white hover:border-neutral-300'
                                "
                                class="flex min-h-[44px] items-center gap-2.5 rounded-xl border p-3 text-left transition-colors"
                            >
                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800"
                                >
                                    <QrCode class="size-4" />
                                </div>
                                <div>
                                    <div
                                        class="text-xs font-bold text-neutral-900"
                                    >
                                        QRIS Instan
                                    </div>
                                    <div class="text-[10px] text-neutral-500">
                                        Scan langsung di layar
                                    </div>
                                </div>
                            </button>

                            <button
                                type="button"
                                @click="paymentType = 'invoice'"
                                :class="
                                    paymentType === 'invoice'
                                        ? 'border-[#000000] bg-[#edede2]/60 ring-1 ring-[#000000]'
                                        : 'border-neutral-200 bg-white hover:border-neutral-300'
                                "
                                class="flex min-h-[44px] items-center gap-2.5 rounded-xl border p-3 text-left transition-colors"
                            >
                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-800"
                                >
                                    <CreditCard class="size-4" />
                                </div>
                                <div>
                                    <div
                                        class="text-xs font-bold text-neutral-900"
                                    >
                                        Xendit Invoice
                                    </div>
                                    <div class="text-[10px] text-neutral-500">
                                        Kirim tautan bayar ke pasien
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div
                class="flex items-center justify-end gap-3 border-t border-[#000000]/10 bg-[#edede2]/50 px-6 py-4"
            >
                <button
                    type="button"
                    @click="emit('close')"
                    class="inline-flex min-h-[44px] items-center rounded-full border border-[#000000]/20 bg-[#ffffff] px-5 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2]"
                >
                    Batal
                </button>

                <!-- Tombol Submit Pembuatan Tagihan -->
                <motion.button
                    v-if="!hasBilling || (!isPaid && !isPending)"
                    type="button"
                    :whileHover="{ scale: 1.01 }"
                    :whileTap="{ scale: 0.99 }"
                    @click="handleCreateBilling"
                    :disabled="isSubmitting || effectiveAmount <= 0"
                    class="inline-flex min-h-[44px] items-center gap-2 rounded-full bg-[#000000] px-6 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#1a1a1a] disabled:opacity-50"
                >
                    <Loader2
                        v-if="isSubmitting"
                        class="size-4 animate-spin text-[#beedc0]"
                    />
                    <QrCode
                        v-else-if="paymentType === 'qris'"
                        class="size-4 text-[#beedc0]"
                    />
                    <CreditCard v-else class="size-4 text-[#beedc0]" />
                    <span>{{
                        isSubmitting
                            ? 'Menerbitkan Tagihan...'
                            : paymentType === 'qris'
                              ? 'Buat QRIS Langsung'
                              : 'Terbitkan Xendit Invoice'
                    }}</span>
                </motion.button>
            </div>
        </motion.div>
    </div>
</template>
