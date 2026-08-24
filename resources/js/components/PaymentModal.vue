<script setup lang="ts">
/**
 * PaymentModal.vue — Point-of-Sale (POS) & Payment Gateway Kasir SIMRS
 *
 * Mendukung 4 metode pembayaran:
 * 1. QRIS Dinamis Instan (Xendit Dynamic QR API + Real-time Background Polling)
 * 2. Tunai / Cash (Fast tender buttons & kalkulator kembalian otomatis)
 * 3. Mesin EDC / Kartu Debit & Kredit (Input bank & approval code)
 * 4. Transfer / Virtual Account (Xendit VA Link)
 */
import {
    AlertCircle,
    Building2,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Download,
    ExternalLink,
    Loader2,
    Printer,
    QrCode,
    RefreshCw,
    ShieldCheck,
    Smartphone,
    Wallet,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import QRCode from 'qrcode';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { Billing } from '@/types/hospital';

interface Props {
    billing: Billing | null;
    isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'success', billingId: number): void;
}>();

// Active Tab ('qris' | 'cash' | 'edc' | 'va')
const selectedMethod = ref<'qris' | 'cash' | 'edc' | 'va'>('qris');

// Loading & UI States
const isGeneratingQris = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);
const isPaidSuccess = ref(false);
const paidReceiptUrl = ref<string | null>(null);

// QRIS State
const qrString = ref<string | null>(null);
const qrCanvasRef = ref<HTMLCanvasElement | null>(null);
const isPolling = ref(false);
let pollingTimer: ReturnType<typeof setInterval> | null = null;

// Cash State
const cashReceived = ref<number>(0);

// EDC State
const edcForm = ref({
    card_type: 'Debit',
    bank_name: 'BCA',
    approval_code: '',
    card_last_four: '',
});

// VA / Online Link State
const xenditInvoiceUrl = ref<string | null>(null);

// Format Rupiah Helper
const formatRupiah = (val: number | string | null | undefined): string => {
    const num = typeof val === 'string' ? parseFloat(val) : val || 0;

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

// Total Tagihan
const totalBill = computed(() => {
    if (!props.billing) {
        return 0;
    }

    return Number(props.billing.total_amount);
});

// Cash Change (Kembalian)
const cashChange = computed(() => {
    return Math.max(0, cashReceived.value - totalBill.value);
});

// Reset States when modal opens
watch(
    () => props.isOpen,
    (open) => {
        if (open && props.billing) {
            errorMessage.value = null;
            isPaidSuccess.value = props.billing.status === 'paid';
            paidReceiptUrl.value = `/staff/billing/${props.billing.billing_id}/print-receipt?stream=1`;
            cashReceived.value = totalBill.value;
            qrString.value = null;
            xenditInvoiceUrl.value = props.billing.xendit_payment_url || null;
            edcForm.value = {
                card_type: 'Debit',
                bank_name: 'BCA',
                approval_code: '',
                card_last_four: '',
            };

            // Jika billing sudah berstatus pending dengan QRIS sebelumnya
            if (
                props.billing.payment_method === 'xendit_qris' &&
                props.billing.xendit_payment_url
            ) {
                qrString.value = props.billing.xendit_payment_url;
                renderQrCode(props.billing.xendit_payment_url);
                startPolling();
            }

            // Hubungkan ke Laravel Echo Private Channel untuk konfirmasi pembayaran instan sub-detik
            try {
                import('@/echo').then(({ echo }) => {
                    if (props.billing) {
                        echo.private(`billing.${props.billing.billing_id}`)
                            .listen('PaymentSettledEvent', (event: any) => {
                                stopPolling();
                                isPaidSuccess.value = true;
                                emit('success', event.billing_id);
                            })
                            .listen('.PaymentSettledEvent', (event: any) => {
                                stopPolling();
                                isPaidSuccess.value = true;
                                emit('success', event.billing_id);
                            });
                    }
                });
            } catch (e) {
                console.warn('Echo billing channel error:', e);
            }
        } else {
            stopPolling();

            if (props.billing) {
                try {
                    import('@/echo').then(({ echo }) => {
                        echo.leave(`billing.${props.billing?.billing_id}`);
                    });
                } catch (e) {}
            }
        }
    },
);

// Render QR Code onto Canvas
const renderQrCode = async (rawQr: string) => {
    await nextTick();

    if (!qrCanvasRef.value) {
        return;
    }

    try {
        await QRCode.toCanvas(qrCanvasRef.value, rawQr, {
            width: 250,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#ffffff',
            },
            errorCorrectionLevel: 'M',
        });
    } catch (err) {
        console.error('Failed to render QR canvas', err);
    }
};

// Generate Dynamic QRIS from Backend
const handleGenerateQris = async () => {
    if (!props.billing) {
        return;
    }

    isGeneratingQris.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(
            `/staff/billing/${props.billing.billing_id}/pay-qris`,
        );

        if (response.data.status && response.data.qr_string) {
            qrString.value = response.data.qr_string;
            await renderQrCode(response.data.qr_string);
            startPolling();
        } else {
            errorMessage.value = response.data.message || 'Gagal membuat QRIS.';
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message ||
            'Terjadi kesalahan saat memproses QRIS.';
    } finally {
        isGeneratingQris.value = false;
    }
};

// Background Polling Fallback (every 3s)
const startPolling = () => {
    stopPolling();

    if (!props.billing) {
        return;
    }

    isPolling.value = true;
    pollingTimer = setInterval(async () => {
        if (!props.billing) {
            return;
        }

        try {
            const res = await axios.get(
                `/staff/billing/${props.billing.billing_id}/status`,
            );

            if (res.data && res.data.is_paid) {
                stopPolling();
                isPaidSuccess.value = true;
                emit('success', props.billing.billing_id);
            }
        } catch (e) {
            console.warn('Status poll warning', e);
        }
    }, 3000);
};

const stopPolling = () => {
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }

    isPolling.value = false;
};

// Process Cash Payment
const handleCashSubmit = async () => {
    if (!props.billing) {
        return;
    }

    if (cashReceived.value < totalBill.value) {
        errorMessage.value = 'Uang yang diterima kurang dari total tagihan.';

        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(
            `/staff/billing/${props.billing.billing_id}/pay-cash`,
            {
                cash_received: cashReceived.value,
            },
        );

        if (response.data.status) {
            isPaidSuccess.value = true;
            emit('success', props.billing.billing_id);
        } else {
            errorMessage.value =
                response.data.message || 'Gagal memproses pembayaran tunai.';
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal memproses pembayaran tunai.';
    } finally {
        isSubmitting.value = false;
    }
};

// Process EDC Card Payment
const handleEdcSubmit = async () => {
    if (!props.billing) {
        return;
    }

    if (!edcForm.value.approval_code.trim()) {
        errorMessage.value =
            'Nomor Approval Code / Trace No. Mesin EDC wajib diisi.';

        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(
            `/staff/billing/${props.billing.billing_id}/pay-edc`,
            edcForm.value,
        );

        if (response.data.status) {
            isPaidSuccess.value = true;
            emit('success', props.billing.billing_id);
        } else {
            errorMessage.value =
                response.data.message || 'Gagal memproses transaksi EDC.';
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal memproses transaksi EDC.';
    } finally {
        isSubmitting.value = false;
    }
};

// Process Xendit VA Invoice Link
const handleGenerateXenditInvoice = async () => {
    if (!props.billing) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(
            `/staff/billing/${props.billing.billing_id}/pay-xendit`,
        );

        if (response.data.status && response.data.xendit_payment_url) {
            xenditInvoiceUrl.value = response.data.xendit_payment_url;
            startPolling();
        } else {
            errorMessage.value =
                response.data.message || 'Gagal membuat tagihan VA Xendit.';
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal membuat tagihan VA Xendit.';
    } finally {
        isSubmitting.value = false;
    }
};

// Clean up on component unmount
onBeforeUnmount(() => {
    stopPolling();
});
</script>

<template>
    <div
        v-if="isOpen && billing"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/65 p-4 backdrop-blur-sm"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.96, y: 10 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.22, ease: 'easeOut' }"
            class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-[#333333]/20 bg-[#fffff3] font-['Rubik'] text-[#000000] shadow-2xl"
        >
            <!-- ═══════════════════════════════════════════════════════════════
                 Modal Header
                 ═══════════════════════════════════════════════════════════════ -->
            <div
                class="flex items-center justify-between border-b border-[#333333]/15 bg-[#edede2]/70 px-6 py-4"
            >
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#000000] text-white"
                    >
                        <Wallet class="h-4 w-4 text-[#beedc0]" />
                    </div>
                    <div>
                        <h2
                            class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                        >
                            Kasir & Pembayaran SIMRS
                        </h2>
                        <p class="text-xs text-neutral-600">
                            Invoice:
                            <strong class="text-neutral-900">{{
                                billing.invoice_number
                            }}</strong>
                            · {{ billing.patient?.name }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-full p-2 text-neutral-500 transition hover:bg-neutral-200 hover:text-black"
                    @click="emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="mx-6 mt-4 flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 p-3 text-xs text-rose-800"
            >
                <AlertCircle class="h-4 w-4 shrink-0 text-rose-600" />
                <span>{{ errorMessage }}</span>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 SUCCESS STATE (Jika Pembayaran Lunas)
                 ═══════════════════════════════════════════════════════════════ -->
            <div v-if="isPaidSuccess" class="p-8 text-center">
                <motion.div
                    :initial="{ scale: 0.8, opacity: 0 }"
                    :animate="{ scale: 1, opacity: 1 }"
                    class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-[#beedc0] text-[#065f46] shadow-inner"
                >
                    <CheckCircle2 class="h-10 w-10" />
                </motion.div>

                <h3
                    class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000]"
                >
                    Pembayaran Berhasil & Lunas!
                </h3>
                <p class="mt-1 text-xs text-neutral-600">
                    Transaksi telah diverifikasi ke dalam sistem SIMRS & antrean
                    pasien otomatis diperbarui.
                </p>

                <div
                    class="mx-auto mt-6 max-w-md space-y-2 rounded-2xl border border-[#333333]/15 bg-[#edede2]/40 p-4 text-left text-xs"
                >
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Nomor Invoice:</span>
                        <span class="font-bold text-neutral-900">{{
                            billing.invoice_number
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Nama Pasien:</span>
                        <span class="font-semibold text-neutral-900">{{
                            billing.patient?.name
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t border-neutral-300 pt-2 text-sm font-bold"
                    >
                        <span>Total Pelunasan:</span>
                        <span class="text-[#065f46]">{{
                            formatRupiah(totalBill)
                        }}</span>
                    </div>
                </div>

                <div
                    class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                >
                    <a
                        :href="
                            paidReceiptUrl ||
                            `/staff/billing/${billing.billing_id}/print-receipt?stream=1`
                        "
                        target="_blank"
                        class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-6 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#333333] sm:w-auto"
                    >
                        <Printer class="h-4 w-4 text-[#beedc0]" />
                        Cetak Kuitansi Resmi (PDF)
                    </a>
                    <button
                        type="button"
                        class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl border border-neutral-300 bg-white px-5 py-2.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100 sm:w-auto"
                        @click="emit('close')"
                    >
                        Tutup Panel Kasir
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 PAYMENT FORM (Jika Belum Lunas)
                 ═══════════════════════════════════════════════════════════════ -->
            <div v-else class="max-h-[80vh] overflow-y-auto p-6">
                <!-- Total Amount Banner -->
                <div
                    class="mb-5 flex items-center justify-between rounded-2xl border border-[#beedc0] bg-[#beedc0]/25 p-4"
                >
                    <div>
                        <span
                            class="text-xs font-semibold tracking-wider text-[#065f46] uppercase"
                            >Total Tagihan Rawat Jalan</span
                        >
                        <div
                            class="font-['ivypresto-headline'] text-2xl font-black text-[#000000] sm:text-3xl"
                        >
                            {{ formatRupiah(totalBill) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span
                            class="rounded-full border border-[#beedc0] bg-white px-3 py-1 text-[11px] font-bold text-[#065f46]"
                        >
                            Status: {{ billing.status.toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     1. Payment Method Selector Cards
                     ═══════════════════════════════════════════════════════════ -->
                <div class="mb-5">
                    <label
                        class="mb-2 block text-xs font-bold tracking-wider text-neutral-500 uppercase"
                    >
                        Pilih Jalur & Metode Pembayaran
                    </label>

                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                        <!-- Card 1: QRIS -->
                        <button
                            type="button"
                            :class="
                                selectedMethod === 'qris'
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-md'
                                    : 'border-neutral-300 bg-white text-neutral-800 hover:border-neutral-400 hover:bg-neutral-50'
                            "
                            class="flex min-h-[72px] flex-col justify-between rounded-xl border p-3 text-left transition"
                            @click="selectedMethod = 'qris'"
                        >
                            <div class="flex items-center justify-between">
                                <QrCode
                                    :class="
                                        selectedMethod === 'qris'
                                            ? 'text-[#beedc0]'
                                            : 'text-neutral-700'
                                    "
                                    class="h-5 w-5"
                                />
                                <span
                                    :class="
                                        selectedMethod === 'qris'
                                            ? 'bg-[#beedc0] text-[#000000]'
                                            : 'bg-emerald-100 text-emerald-800'
                                    "
                                    class="rounded px-1.5 py-0.5 text-[9px] font-bold uppercase"
                                >
                                    Instant
                                </span>
                            </div>
                            <div>
                                <div class="text-xs font-bold">
                                    QRIS Dinamis
                                </div>
                                <div
                                    :class="
                                        selectedMethod === 'qris'
                                            ? 'text-neutral-300'
                                            : 'text-neutral-500'
                                    "
                                    class="text-[10px]"
                                >
                                    Semua M-Banking/E-Wallet
                                </div>
                            </div>
                        </button>

                        <!-- Card 2: Tunai (Cash) -->
                        <button
                            type="button"
                            :class="
                                selectedMethod === 'cash'
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-md'
                                    : 'border-neutral-300 bg-white text-neutral-800 hover:border-neutral-400 hover:bg-neutral-50'
                            "
                            class="flex min-h-[72px] flex-col justify-between rounded-xl border p-3 text-left transition"
                            @click="selectedMethod = 'cash'"
                        >
                            <div class="flex items-center justify-between">
                                <DollarSign
                                    :class="
                                        selectedMethod === 'cash'
                                            ? 'text-[#beedc0]'
                                            : 'text-neutral-700'
                                    "
                                    class="h-5 w-5"
                                />
                                <span
                                    :class="
                                        selectedMethod === 'cash'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-neutral-100 text-neutral-700'
                                    "
                                    class="rounded px-1.5 py-0.5 text-[9px] font-semibold"
                                >
                                    Fisik
                                </span>
                            </div>
                            <div>
                                <div class="text-xs font-bold">
                                    Tunai (Cash)
                                </div>
                                <div
                                    :class="
                                        selectedMethod === 'cash'
                                            ? 'text-neutral-300'
                                            : 'text-neutral-500'
                                    "
                                    class="text-[10px]"
                                >
                                    Hitung Kembalian
                                </div>
                            </div>
                        </button>

                        <!-- Card 3: Mesin EDC -->
                        <button
                            type="button"
                            :class="
                                selectedMethod === 'edc'
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-md'
                                    : 'border-neutral-300 bg-white text-neutral-800 hover:border-neutral-400 hover:bg-neutral-50'
                            "
                            class="flex min-h-[72px] flex-col justify-between rounded-xl border p-3 text-left transition"
                            @click="selectedMethod = 'edc'"
                        >
                            <div class="flex items-center justify-between">
                                <CreditCard
                                    :class="
                                        selectedMethod === 'edc'
                                            ? 'text-[#beedc0]'
                                            : 'text-neutral-700'
                                    "
                                    class="h-5 w-5"
                                />
                                <span
                                    :class="
                                        selectedMethod === 'edc'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-neutral-100 text-neutral-700'
                                    "
                                    class="rounded px-1.5 py-0.5 text-[9px] font-semibold"
                                >
                                    Gesek/Dip
                                </span>
                            </div>
                            <div>
                                <div class="text-xs font-bold">Mesin EDC</div>
                                <div
                                    :class="
                                        selectedMethod === 'edc'
                                            ? 'text-neutral-300'
                                            : 'text-neutral-500'
                                    "
                                    class="text-[10px]"
                                >
                                    Kartu Debit / Kredit
                                </div>
                            </div>
                        </button>

                        <!-- Card 4: Virtual Account Xendit -->
                        <button
                            type="button"
                            :class="
                                selectedMethod === 'va'
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-md'
                                    : 'border-neutral-300 bg-white text-neutral-800 hover:border-neutral-400 hover:bg-neutral-50'
                            "
                            class="flex min-h-[72px] flex-col justify-between rounded-xl border p-3 text-left transition"
                            @click="selectedMethod = 'va'"
                        >
                            <div class="flex items-center justify-between">
                                <Building2
                                    :class="
                                        selectedMethod === 'va'
                                            ? 'text-[#beedc0]'
                                            : 'text-neutral-700'
                                    "
                                    class="h-5 w-5"
                                />
                                <span
                                    :class="
                                        selectedMethod === 'va'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-blue-100 text-blue-800'
                                    "
                                    class="rounded px-1.5 py-0.5 text-[9px] font-semibold"
                                >
                                    VA Bank
                                </span>
                            </div>
                            <div>
                                <div class="text-xs font-bold">
                                    Virtual Account
                                </div>
                                <div
                                    :class="
                                        selectedMethod === 'va'
                                            ? 'text-neutral-300'
                                            : 'text-neutral-500'
                                    "
                                    class="text-[10px]"
                                >
                                    BCA, BNI, Mandiri, BRI
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     TAB 1: QRIS Dinamis Instan
                     ═══════════════════════════════════════════════════════════ -->
                <div v-if="selectedMethod === 'qris'" class="space-y-4">
                    <!-- Jika QR Belum Digenerate -->
                    <div
                        v-if="!qrString"
                        class="rounded-2xl border border-neutral-300 bg-white p-6 text-center shadow-xs"
                    >
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#beedc0]/50 text-[#065f46]"
                        >
                            <QrCode class="h-6 w-6" />
                        </div>
                        <h4 class="text-sm font-bold text-[#000000]">
                            Tampilkan Barcode QRIS Dinamis
                        </h4>
                        <p
                            class="mx-auto mt-1 max-w-sm text-xs text-neutral-600"
                        >
                            Sistem akan men-generate QRIS standar nasional
                            secara otomatis sesuai total tagihan rawat jalan
                            pasien.
                        </p>

                        <div
                            class="mt-4 flex flex-wrap items-center justify-center gap-1.5 text-[10px] text-neutral-500"
                        >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >BCA Mobile</span
                            >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >Livin Mandiri</span
                            >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >GoPay</span
                            >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >OVO</span
                            >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >ShopeePay</span
                            >
                            <span
                                class="rounded bg-neutral-100 px-2 py-0.5 font-semibold"
                                >DANA</span
                            >
                        </div>

                        <button
                            type="button"
                            :disabled="isGeneratingQris"
                            class="mt-5 inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-[#000000] px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-50"
                            @click="handleGenerateQris"
                        >
                            <Loader2
                                v-if="isGeneratingQris"
                                class="h-4 w-4 animate-spin"
                            />
                            <QrCode v-else class="h-4 w-4 text-[#beedc0]" />
                            Generate Barcode QRIS Sekarang
                        </button>
                    </div>

                    <!-- Jika QR Sudah Digenerate & Siap Dipindai -->
                    <div
                        v-else
                        class="rounded-2xl border border-[#333333]/20 bg-white p-5 shadow-sm"
                    >
                        <!-- QRIS Header Motif -->
                        <div
                            class="mb-3 flex items-center justify-between border-b border-neutral-200 pb-2.5"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-sm font-black tracking-wider text-rose-700"
                                    >QRIS</span
                                >
                                <span
                                    class="text-[10px] font-semibold text-neutral-400"
                                    >|</span
                                >
                                <span
                                    class="text-[10px] font-bold text-neutral-700"
                                    >GPN INDONESIA</span
                                >
                            </div>
                            <span
                                class="flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700"
                            >
                                <span
                                    class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                                ></span>
                                Real-time Scanner
                            </span>
                        </div>

                        <!-- QR Canvas Area -->
                        <div
                            class="flex flex-col items-center justify-center p-2"
                        >
                            <div
                                class="rounded-2xl border border-neutral-200 bg-white p-3 shadow-inner"
                            >
                                <canvas
                                    ref="qrCanvasRef"
                                    class="mx-auto block"
                                ></canvas>
                            </div>

                            <div class="mt-3 text-center">
                                <div
                                    class="font-['ivypresto-headline'] text-2xl font-black text-[#000000]"
                                >
                                    {{ formatRupiah(totalBill) }}
                                </div>
                                <p
                                    class="text-[11px] font-medium text-neutral-500"
                                >
                                    Minta pasien membuka aplikasi m-banking /
                                    e-wallet dan memindai barcode di atas
                                </p>
                            </div>
                        </div>

                        <!-- Live Polling Status Indicator -->
                        <div
                            class="mt-4 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-2.5 text-xs text-amber-900"
                        >
                            <div class="flex items-center gap-2">
                                <Loader2
                                    class="h-4 w-4 animate-spin text-amber-700"
                                />
                                <span
                                    >Menunggu pembayaran pasien dari
                                    server...</span
                                >
                            </div>
                            <span class="font-mono text-[11px] text-amber-800"
                                >Auto-check: 2.5s</span
                            >
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     TAB 2: Tunai (Cash)
                     ═══════════════════════════════════════════════════════════ -->
                <div v-else-if="selectedMethod === 'cash'" class="space-y-4">
                    <div
                        class="rounded-2xl border border-neutral-300 bg-white p-5"
                    >
                        <label
                            class="block text-xs font-semibold text-neutral-700"
                            >Nominal Uang Diterima dari Pasien (Rp)</label
                        >
                        <input
                            v-model.number="cashReceived"
                            type="number"
                            step="1000"
                            class="mt-1.5 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-4 text-lg font-bold text-[#000000] focus:border-black focus:ring-1 focus:ring-black focus:outline-none"
                        />

                        <!-- Fast Tender Shortcuts -->
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <button
                                type="button"
                                class="rounded-lg border border-neutral-300 bg-[#edede2] px-3 py-1.5 text-xs font-semibold hover:bg-neutral-200"
                                @click="cashReceived = totalBill"
                            >
                                Uang Pas
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-neutral-300 bg-[#edede2] px-3 py-1.5 text-xs font-semibold hover:bg-neutral-200"
                                @click="
                                    cashReceived =
                                        Math.ceil(totalBill / 50000) * 50000
                                "
                            >
                                Pecahan 50rb
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-neutral-300 bg-[#edede2] px-3 py-1.5 text-xs font-semibold hover:bg-neutral-200"
                                @click="
                                    cashReceived =
                                        Math.ceil(totalBill / 100000) * 100000
                                "
                            >
                                Pecahan 100rb
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-neutral-300 bg-[#edede2] px-3 py-1.5 text-xs font-semibold hover:bg-neutral-200"
                                @click="cashReceived = 500000"
                            >
                                Rp 500.000
                            </button>
                        </div>

                        <!-- Change Calculator Box -->
                        <div
                            class="mt-4 space-y-1 rounded-xl border border-neutral-200 bg-[#edede2]/30 p-3.5 text-xs"
                        >
                            <div class="flex justify-between text-neutral-600">
                                <span>Total Tagihan:</span>
                                <span class="font-bold text-neutral-900">{{
                                    formatRupiah(totalBill)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-neutral-600">
                                <span>Uang Diterima:</span>
                                <span class="font-bold text-neutral-900">{{
                                    formatRupiah(cashReceived)
                                }}</span>
                            </div>
                            <div
                                class="mt-2 flex justify-between border-t border-neutral-300 pt-2 text-sm font-bold"
                            >
                                <span
                                    :class="
                                        cashReceived >= totalBill
                                            ? 'text-emerald-700'
                                            : 'text-rose-700'
                                    "
                                >
                                    {{
                                        cashReceived >= totalBill
                                            ? 'Kembalian Pasien:'
                                            : 'Uang Masih Kurang:'
                                    }}
                                </span>
                                <span
                                    :class="
                                        cashReceived >= totalBill
                                            ? 'text-emerald-700'
                                            : 'text-rose-700'
                                    "
                                >
                                    {{
                                        formatRupiah(
                                            Math.abs(cashReceived - totalBill),
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            :disabled="cashReceived < totalBill || isSubmitting"
                            class="mt-5 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-40"
                            @click="handleCashSubmit"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <CheckCircle2
                                v-else
                                class="h-4 w-4 text-[#beedc0]"
                            />
                            Konfirmasi Pembayaran Tunai & Selesai
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     TAB 3: Mesin EDC (Kartu Debit / Kredit)
                     ═══════════════════════════════════════════════════════════ -->
                <div v-else-if="selectedMethod === 'edc'" class="space-y-4">
                    <div
                        class="space-y-3.5 rounded-2xl border border-neutral-300 bg-white p-5"
                    >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Tipe Kartu</label
                                >
                                <select
                                    v-model="edcForm.card_type"
                                    class="mt-1 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-3 text-xs font-semibold text-neutral-900 focus:border-black focus:outline-none"
                                >
                                    <option value="Debit">Kartu Debit</option>
                                    <option value="Kredit">Kartu Kredit</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Bank Penerbit EDC</label
                                >
                                <select
                                    v-model="edcForm.bank_name"
                                    class="mt-1 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-3 text-xs font-semibold text-neutral-900 focus:border-black focus:outline-none"
                                >
                                    <option value="BCA">BCA</option>
                                    <option value="MANDIRI">
                                        Bank Mandiri
                                    </option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="CIMB">CIMB Niaga</option>
                                    <option value="PERMATA">
                                        Bank Permata
                                    </option>
                                    <option value="OTHER">Bank Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-neutral-700"
                            >
                                Nomor Approval Code / Reff Trace EDC
                                <span class="text-rose-600">*</span>
                            </label>
                            <input
                                v-model="edcForm.approval_code"
                                type="text"
                                placeholder="Contoh: APPV-938201 / TRACE-8821"
                                class="mt-1 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-4 text-xs font-semibold text-neutral-900 uppercase focus:border-black focus:outline-none"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-neutral-700"
                                >4 Digit Terakhir Kartu (Opsional)</label
                            >
                            <input
                                v-model="edcForm.card_last_four"
                                type="text"
                                maxlength="4"
                                placeholder="Contoh: 8821"
                                class="mt-1 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-4 text-xs font-semibold text-neutral-900 focus:border-black focus:outline-none"
                            />
                        </div>

                        <button
                            type="button"
                            :disabled="
                                !edcForm.approval_code.trim() || isSubmitting
                            "
                            class="mt-4 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-40"
                            @click="handleEdcSubmit"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <CreditCard v-else class="h-4 w-4 text-[#beedc0]" />
                            Verifikasi Transaksi Gesek EDC
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     TAB 4: Virtual Account (Xendit VA)
                     ═══════════════════════════════════════════════════════════ -->
                <div v-else-if="selectedMethod === 'va'" class="space-y-4">
                    <div
                        class="rounded-2xl border border-neutral-300 bg-white p-5 text-center"
                    >
                        <div
                            class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700"
                        >
                            <Building2 class="h-6 w-6" />
                        </div>
                        <h4 class="text-sm font-bold text-[#000000]">
                            Virtual Account Bank & Checkout Online
                        </h4>
                        <p
                            class="mx-auto mt-1 max-w-sm text-xs text-neutral-600"
                        >
                            Buat tagihan online multi-bank (BCA, Mandiri, BNI,
                            BRI, Permata) yang dapat dibayar melalui
                            ATM/Internet Banking.
                        </p>

                        <div
                            v-if="xenditInvoiceUrl"
                            class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-4"
                        >
                            <div
                                class="flex items-center justify-center gap-2 text-sm font-bold text-emerald-900"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 text-emerald-600"
                                />
                                Tautan Checkout Aktif
                            </div>
                            <div class="mt-3">
                                <a
                                    :href="xenditInvoiceUrl"
                                    target="_blank"
                                    class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800"
                                >
                                    <ExternalLink class="h-4 w-4" />
                                    Buka Halaman Pembayaran Xendit
                                </a>
                            </div>
                        </div>

                        <button
                            v-else
                            type="button"
                            :disabled="isSubmitting"
                            class="mt-4 inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-[#000000] px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-50"
                            @click="handleGenerateXenditInvoice"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <Building2 v-else class="h-4 w-4 text-[#beedc0]" />
                            Buat Tagihan Virtual Account
                        </button>
                    </div>
                </div>
            </div>
        </motion.div>
    </div>
</template>
