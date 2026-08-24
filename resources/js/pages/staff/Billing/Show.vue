<script setup lang="ts">
/**
 * Billing/Show.vue — Rincian Tagihan & Lembar Pelunasan Kasir Modern POS
 * 
 * Khusus Staf / Perawat Tetap (Pekerja).
 * Menampilkan rincian item tagihan (konsultasi, obat, tindakan),
 * panel Point-of-Sale kasir interaktif:
 * - QRIS Dinamis Instan (dengan real-time status polling & auto-success)
 * - Tunai (Cash) dengan kalkulator kembalian
 * - Mesin EDC (Kartu Debit / Kredit)
 * - Virtual Account Xendit
 */
import { Head, Link, router } from '@inertiajs/vue3'
import {
    AlertCircle,
    ArrowLeft,
    Building2,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Download,
    ExternalLink,
    FileText,
    Loader2,
    Printer,
    QrCode,
    Receipt,
    RefreshCw,
    ShieldCheck,
    Smartphone,
    User,
    Wallet,
} from '@lucide/vue'
import axios from 'axios'
import { motion } from 'motion-v'
import QRCode from 'qrcode'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import type { Billing } from '@/types/hospital'

interface Props {
    billing: Billing
}

const props = defineProps<Props>()

// Format Rupiah Helper
const formatRupiah = (val: number | string | null | undefined): string => {
    const num = typeof val === 'string' ? parseFloat(val) : (val || 0)
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num)
}

// Active Tab ('qris' | 'cash' | 'edc' | 'va')
const selectedMethod = ref<'qris' | 'cash' | 'edc' | 'va'>('qris')

// Loading & UI States
const isGeneratingQris = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)
const isPaidSuccess = ref(props.billing.status === 'paid')

// QRIS State
const qrString = ref<string | null>(props.billing.payment_method === 'xendit_qris' ? (props.billing.xendit_payment_url ?? null) : null)
const qrCanvasRef = ref<HTMLCanvasElement | null>(null)
const isPolling = ref(false)
let pollingTimer: ReturnType<typeof setInterval> | null = null

// Cash State
const cashReceived = ref<number>(Number(props.billing.total_amount))

// EDC State
const edcForm = ref({
    card_type: 'Debit',
    bank_name: 'BCA',
    approval_code: '',
    card_last_four: '',
})

// VA / Online Link State
const xenditInvoiceUrl = ref<string | null>(props.billing.xendit_payment_url || null)

// Total Tagihan
const totalBill = computed(() => Number(props.billing.total_amount))

// Kembalian
const cashChange = computed(() => Math.max(0, cashReceived.value - totalBill.value))

// Render QR Code onto Canvas
const renderQrCode = async (rawQr: string) => {
    await nextTick()
    if (!qrCanvasRef.value) return

    try {
        await QRCode.toCanvas(qrCanvasRef.value, rawQr, {
            width: 250,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#ffffff',
            },
            errorCorrectionLevel: 'M',
        })
    } catch (err) {
        console.error('Failed to render QR canvas', err)
    }
}

// Generate Dynamic QRIS from Backend
const handleGenerateQris = async () => {
    isGeneratingQris.value = true
    errorMessage.value = null

    try {
        const response = await axios.post(`/staff/billing/${props.billing.billing_id}/pay-qris`)
        if (response.data.status && response.data.qr_string) {
            qrString.value = response.data.qr_string
            await renderQrCode(response.data.qr_string)
            startPolling()
        } else {
            errorMessage.value = response.data.message || 'Gagal membuat QRIS.'
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Terjadi kesalahan saat memproses QRIS.'
    } finally {
        isGeneratingQris.value = false
    }
}

// Background Polling Function (every 2.5s)
const startPolling = () => {
    stopPolling()
    if (props.billing.status === 'paid') return

    isPolling.value = true
    pollingTimer = setInterval(async () => {
        try {
            const res = await axios.get(`/staff/billing/${props.billing.billing_id}/status`)
            if (res.data && res.data.is_paid) {
                stopPolling()
                isPaidSuccess.value = true
                router.reload()
            }
        } catch (e) {
            console.warn('Status poll warning', e)
        }
    }, 2500)
}

const stopPolling = () => {
    if (pollingTimer) {
        clearInterval(pollingTimer)
        pollingTimer = null
    }
    isPolling.value = false
}

// Process Cash Payment
const handleCashSubmit = async () => {
    if (cashReceived.value < totalBill.value) {
        errorMessage.value = 'Uang yang diterima kurang dari total tagihan.'
        return
    }

    isSubmitting.value = true
    errorMessage.value = null

    try {
        const response = await axios.post(`/staff/billing/${props.billing.billing_id}/pay-cash`, {
            cash_received: cashReceived.value,
        })

        if (response.data.status) {
            isPaidSuccess.value = true
            router.reload()
        } else {
            errorMessage.value = response.data.message || 'Gagal memproses pembayaran tunai.'
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Gagal memproses pembayaran tunai.'
    } finally {
        isSubmitting.value = false
    }
}

// Process EDC Card Payment
const handleEdcSubmit = async () => {
    if (!edcForm.value.approval_code.trim()) {
        errorMessage.value = 'Nomor Approval Code / Trace No. Mesin EDC wajib diisi.'
        return
    }

    isSubmitting.value = true
    errorMessage.value = null

    try {
        const response = await axios.post(`/staff/billing/${props.billing.billing_id}/pay-edc`, edcForm.value)

        if (response.data.status) {
            isPaidSuccess.value = true
            router.reload()
        } else {
            errorMessage.value = response.data.message || 'Gagal memproses transaksi EDC.'
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Gagal memproses transaksi EDC.'
    } finally {
        isSubmitting.value = false
    }
}

// Process Xendit VA Invoice Link
const handleGenerateXenditInvoice = async () => {
    isSubmitting.value = true
    errorMessage.value = null

    try {
        const response = await axios.post(`/staff/billing/${props.billing.billing_id}/pay-xendit`)
        if (response.data.status && response.data.xendit_payment_url) {
            xenditInvoiceUrl.value = response.data.xendit_payment_url
            startPolling()
        } else {
            errorMessage.value = response.data.message || 'Gagal membuat tagihan VA Xendit.'
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Gagal membuat tagihan VA Xendit.'
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    if (props.billing.status === 'pending' && props.billing.payment_method === 'xendit_qris' && qrString.value) {
        renderQrCode(qrString.value)
        startPolling()
    }
})

onBeforeUnmount(() => {
    stopPolling()
})
</script>

<template>
    <AppSidebarLayout>
        <Head :title="`Tagihan #${billing.invoice_number} - SIMRS`" />

        <div class="min-h-screen bg-[#edede2] px-4 py-6 font-['Rubik'] text-[#000000] md:px-8">
            <!-- ═══════════════════════════════════════════════════════════════
                 Back Navigation & Header
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div class="flex items-center gap-3">
                    <Link
                        href="/staff/billing"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#333333]/20 bg-[#fffff3] p-2 text-[#000000] shadow-sm transition hover:bg-[#edede2]"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000] md:text-3xl">
                                Tagihan #{{ billing.invoice_number }}
                            </h1>
                            <span
                                v-if="billing.status === 'paid'"
                                class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-3 py-0.5 text-xs font-bold text-emerald-800"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5" />
                                Lunas
                            </span>
                            <span
                                v-else-if="billing.status === 'pending'"
                                class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-3 py-0.5 text-xs font-bold text-amber-800"
                            >
                                <Clock class="h-3.5 w-3.5" />
                                Menunggu Pembayaran
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 rounded-full border border-rose-300 bg-rose-100 px-3 py-0.5 text-xs font-bold text-rose-800"
                            >
                                <AlertCircle class="h-3.5 w-3.5" />
                                Belum Bayar
                            </span>
                        </div>
                        <p class="text-xs text-neutral-600">
                            Dibuat: {{ new Date(billing.created_at || '').toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }} WIB
                        </p>
                    </div>
                </div>

                <!-- PDF Action (if paid) -->
                <div v-if="billing.status === 'paid'" class="flex items-center gap-3">
                    <a
                        :href="`/staff/billing/${billing.billing_id}/print-receipt?stream=1`"
                        target="_blank"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-[#065f46] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#044e3a]"
                    >
                        <Printer class="h-4 w-4 text-[#beedc0]" />
                        Cetak Kuitansi Resmi (PDF)
                    </a>
                </div>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="mb-6 flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800">
                <AlertCircle class="h-5 w-5 shrink-0 text-rose-600" />
                <span>{{ errorMessage }}</span>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- ═══════════════════════════════════════════════════════════
                     Left Col: Itemized Invoice Breakdown
                     ═══════════════════════════════════════════════════════════ -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Patient & Consultation Card -->
                    <div class="rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-5 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-neutral-500">
                            Informasi Pasien & Kunjungan Rawat Jalan
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-1">
                                <span class="text-xs text-neutral-500">Nama Pasien:</span>
                                <div class="font-bold text-[#000000]">{{ billing.patient?.name }}</div>
                                <div class="text-xs text-neutral-600">NIK: {{ billing.patient?.resident_n }}</div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs text-neutral-500">Nomor Telepon:</span>
                                <div class="font-medium text-[#000000]">{{ billing.patient?.number_phone || '-' }}</div>
                                <div class="text-xs text-neutral-600">{{ billing.patient?.gender }} · {{ billing.patient?.address || 'Jakarta' }}</div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs text-neutral-500">Poliklinik Tujuan:</span>
                                <div class="font-semibold text-[#000000]">
                                    {{ billing.reservation?.doctor_schedule?.poli?.name_poli ?? 'Poliklinik' }}
                                </div>
                                <div class="text-xs text-neutral-600">
                                    Ruang: {{ billing.reservation?.doctor_schedule?.room?.name_room ?? '-' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs text-neutral-500">Dokter Spesialis:</span>
                                <div class="font-semibold text-[#000000]">
                                    {{ billing.reservation?.doctor_schedule?.doctor?.name ?? '-' }}
                                </div>
                                <div class="text-xs text-neutral-600">
                                    {{ billing.reservation?.doctor_schedule?.doctor?.specialization?.name_specialization ?? 'Umum' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Itemized Breakdown Table -->
                    <div class="overflow-hidden rounded-2xl border border-[#333333]/15 bg-[#fffff3] shadow-sm">
                        <div class="border-b border-[#333333]/15 bg-[#edede2]/60 px-5 py-3.5 font-bold text-[#000000]">
                            Rincian Item Tagihan Pelayanan Medis
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase tracking-wider text-neutral-600">
                                    <tr>
                                        <th class="px-5 py-3 text-center">No</th>
                                        <th class="px-5 py-3">Uraian / Layanan Medis</th>
                                        <th class="px-5 py-3 text-center">Qty</th>
                                        <th class="px-5 py-3 text-right">Tarif Satuan</th>
                                        <th class="px-5 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200">
                                    <tr v-for="(item, idx) in billing.items" :key="item.billing_item_id">
                                        <td class="px-5 py-3.5 text-center text-xs text-neutral-500">{{ idx + 1 }}</td>
                                        <td class="px-5 py-3.5">
                                            <div class="font-medium text-[#000000]">{{ item.item_name }}</div>
                                            <span class="text-[11px] font-semibold text-neutral-500 uppercase">
                                                {{ item.item_type.replace('_', ' ') }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-semibold text-neutral-700">{{ item.quantity }}</td>
                                        <td class="px-5 py-3.5 text-right font-medium text-neutral-700">
                                            {{ formatRupiah(item.unit_price) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-bold text-[#000000]">
                                            {{ formatRupiah(item.subtotal) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="border-t-2 border-neutral-300 bg-[#edede2]/30">
                                    <tr>
                                        <td colspan="4" class="px-5 py-4 text-right font-bold text-base text-[#000000]">
                                            TOTAL TAGIHAN:
                                        </td>
                                        <td class="px-5 py-4 text-right font-['ivypresto-headline'] text-xl font-extrabold text-[#000000]">
                                            {{ formatRupiah(billing.total_amount) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     Right Col: Modern POS Payment Panel
                     ═══════════════════════════════════════════════════════════ -->
                <div class="space-y-6">
                    <!-- Status Card if Paid -->
                    <div v-if="billing.status === 'paid' || isPaidSuccess" class="rounded-2xl border border-emerald-300 bg-emerald-50/80 p-6 text-center shadow-sm">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <CheckCircle2 class="h-8 w-8" />
                        </div>
                        <h3 class="text-lg font-bold text-emerald-900">Tagihan Sudah Lunas</h3>
                        <p class="mt-1 text-xs text-emerald-700">
                            Pelunasan telah berhasil diverifikasi oleh Kasir.
                        </p>

                        <div class="mt-4 rounded-xl border border-emerald-200 bg-white p-3.5 text-left text-xs space-y-1.5 text-neutral-700">
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Metode Bayar:</span>
                                <span class="font-bold uppercase">{{ billing.payment_method?.replace('_', ' ') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Waktu Pelunasan:</span>
                                <span class="font-semibold">{{ billing.paid_at ? new Date(billing.paid_at).toLocaleString('id-ID') : 'Baru saja' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Kasir / Petugas:</span>
                                <span class="font-semibold">{{ billing.processed_by_nurse?.name ?? 'Staf Kasir' }}</span>
                            </div>
                        </div>

                        <div class="mt-5">
                            <a
                                :href="`/staff/billing/${billing.billing_id}/print-receipt?stream=1`"
                                target="_blank"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-emerald-900"
                            >
                                <Printer class="h-4 w-4 text-[#beedc0]" />
                                Cetak Kuitansi Pembayaran
                            </a>
                        </div>
                    </div>

                    <!-- Modern POS Execution Box if Unpaid / Pending -->
                    <div v-else class="rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <div class="flex items-center gap-2">
                                <Wallet class="h-5 w-5 text-[#065f46]" />
                                <h3 class="font-bold text-[#000000] text-base">Point of Sale (POS) Kasir</h3>
                            </div>
                            <span class="rounded-full bg-[#beedc0]/50 px-2 py-0.5 text-[10px] font-bold text-[#065f46]">
                                Multi-Channel
                            </span>
                        </div>

                        <!-- Method Selector Tabs -->
                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <button
                                type="button"
                                :class="selectedMethod === 'qris' ? 'border-black bg-black text-white' : 'border-neutral-200 bg-white text-neutral-800 hover:bg-neutral-50'"
                                class="flex flex-col items-center justify-center gap-1 rounded-xl border p-2 text-center transition"
                                @click="selectedMethod = 'qris'"
                            >
                                <QrCode :class="selectedMethod === 'qris' ? 'text-[#beedc0]' : 'text-neutral-700'" class="h-4 w-4" />
                                <span class="text-[11px] font-bold">QRIS</span>
                            </button>

                            <button
                                type="button"
                                :class="selectedMethod === 'cash' ? 'border-black bg-black text-white' : 'border-neutral-200 bg-white text-neutral-800 hover:bg-neutral-50'"
                                class="flex flex-col items-center justify-center gap-1 rounded-xl border p-2 text-center transition"
                                @click="selectedMethod = 'cash'"
                            >
                                <DollarSign :class="selectedMethod === 'cash' ? 'text-[#beedc0]' : 'text-neutral-700'" class="h-4 w-4" />
                                <span class="text-[11px] font-bold">Tunai</span>
                            </button>

                            <button
                                type="button"
                                :class="selectedMethod === 'edc' ? 'border-black bg-black text-white' : 'border-neutral-200 bg-white text-neutral-800 hover:bg-neutral-50'"
                                class="flex flex-col items-center justify-center gap-1 rounded-xl border p-2 text-center transition"
                                @click="selectedMethod = 'edc'"
                            >
                                <CreditCard :class="selectedMethod === 'edc' ? 'text-[#beedc0]' : 'text-neutral-700'" class="h-4 w-4" />
                                <span class="text-[11px] font-bold">EDC</span>
                            </button>

                            <button
                                type="button"
                                :class="selectedMethod === 'va' ? 'border-black bg-black text-white' : 'border-neutral-200 bg-white text-neutral-800 hover:bg-neutral-50'"
                                class="flex flex-col items-center justify-center gap-1 rounded-xl border p-2 text-center transition"
                                @click="selectedMethod = 'va'"
                            >
                                <Building2 :class="selectedMethod === 'va' ? 'text-[#beedc0]' : 'text-neutral-700'" class="h-4 w-4" />
                                <span class="text-[11px] font-bold">VA Bank</span>
                            </button>
                        </div>

                        <!-- 1. QRIS VIEW -->
                        <div v-if="selectedMethod === 'qris'" class="mt-4 space-y-3">
                            <div v-if="!qrString" class="rounded-xl border border-neutral-200 bg-white p-4 text-center">
                                <p class="text-xs text-neutral-600">
                                    Tampilkan Barcode QRIS Dinamis langsung di layar untuk dipindai oleh pasien:
                                </p>
                                <button
                                    type="button"
                                    :disabled="isGeneratingQris"
                                    class="mt-3 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-50"
                                    @click="handleGenerateQris"
                                >
                                    <Loader2 v-if="isGeneratingQris" class="h-4 w-4 animate-spin" />
                                    <QrCode v-else class="h-4 w-4 text-[#beedc0]" />
                                    Generate QRIS Dinamis
                                </button>
                            </div>

                            <div v-else class="rounded-xl border border-neutral-200 bg-white p-3 text-center">
                                <div class="mb-2 flex items-center justify-between border-b border-neutral-200 pb-1.5 text-[11px]">
                                    <span class="font-bold text-rose-700">QRIS GPN</span>
                                    <span class="flex items-center gap-1 font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Live Polling (2.5s)
                                    </span>
                                </div>

                                <div class="flex justify-center p-1">
                                    <canvas ref="qrCanvasRef" class="mx-auto block"></canvas>
                                </div>

                                <div class="mt-2 flex items-center justify-center gap-1.5 rounded-lg bg-amber-50 p-2 text-[11px] text-amber-800">
                                    <Loader2 class="h-3.5 w-3.5 animate-spin" />
                                    <span>Menunggu pembayaran pasien via m-banking...</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. CASH VIEW -->
                        <div v-else-if="selectedMethod === 'cash'" class="mt-4 space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-neutral-700">Nominal Diterima (Rp)</label>
                                <input
                                    v-model.number="cashReceived"
                                    type="number"
                                    step="1000"
                                    class="mt-1 min-h-[44px] w-full rounded-xl border border-neutral-300 bg-white px-3 text-sm font-bold text-neutral-900 focus:border-black focus:outline-none"
                                />
                            </div>

                            <div class="flex flex-wrap gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg border border-neutral-200 bg-[#edede2] px-2 py-1 text-[11px] font-semibold hover:bg-neutral-200"
                                    @click="cashReceived = totalBill"
                                >
                                    Uang Pas
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-neutral-200 bg-[#edede2] px-2 py-1 text-[11px] font-semibold hover:bg-neutral-200"
                                    @click="cashReceived = Math.ceil(totalBill / 50000) * 50000"
                                >
                                    50rb
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg border border-neutral-200 bg-[#edede2] px-2 py-1 text-[11px] font-semibold hover:bg-neutral-200"
                                    @click="cashReceived = Math.ceil(totalBill / 100000) * 100000"
                                >
                                    100rb
                                </button>
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-white p-3 space-y-1 text-xs">
                                <div class="flex justify-between text-neutral-600">
                                    <span>Total:</span>
                                    <span class="font-bold">{{ formatRupiah(totalBill) }}</span>
                                </div>
                                <div class="flex justify-between text-neutral-600">
                                    <span>Diterima:</span>
                                    <span class="font-bold">{{ formatRupiah(cashReceived) }}</span>
                                </div>
                                <div class="border-t border-neutral-200 pt-1.5 flex justify-between font-bold text-xs">
                                    <span :class="cashReceived >= totalBill ? 'text-emerald-700' : 'text-rose-700'">
                                        {{ cashReceived >= totalBill ? 'Kembalian:' : 'Kurang:' }}
                                    </span>
                                    <span :class="cashReceived >= totalBill ? 'text-emerald-700' : 'text-rose-700'">
                                        {{ formatRupiah(Math.abs(cashReceived - totalBill)) }}
                                    </span>
                                </div>
                            </div>

                            <button
                                type="button"
                                :disabled="cashReceived < totalBill || isSubmitting"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2.5 text-xs font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-40"
                                @click="handleCashSubmit"
                            >
                                <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                                <CheckCircle2 v-else class="h-4 w-4 text-[#beedc0]" />
                                Pelunasan Tunai
                            </button>
                        </div>

                        <!-- 3. EDC VIEW -->
                        <div v-else-if="selectedMethod === 'edc'" class="mt-4 space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-neutral-700">Tipe</label>
                                    <select
                                        v-model="edcForm.card_type"
                                        class="mt-1 min-h-[40px] w-full rounded-lg border border-neutral-300 bg-white px-2 text-xs font-semibold"
                                    >
                                        <option value="Debit">Debit</option>
                                        <option value="Kredit">Kredit</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-neutral-700">Bank</label>
                                    <select
                                        v-model="edcForm.bank_name"
                                        class="mt-1 min-h-[40px] w-full rounded-lg border border-neutral-300 bg-white px-2 text-xs font-semibold"
                                    >
                                        <option value="BCA">BCA</option>
                                        <option value="MANDIRI">Mandiri</option>
                                        <option value="BNI">BNI</option>
                                        <option value="BRI">BRI</option>
                                        <option value="OTHER">Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-neutral-700">Approval / Trace No.</label>
                                <input
                                    v-model="edcForm.approval_code"
                                    type="text"
                                    placeholder="Contoh: APPV-938201"
                                    class="mt-1 min-h-[40px] w-full rounded-lg border border-neutral-300 bg-white px-3 text-xs uppercase"
                                />
                            </div>

                            <button
                                type="button"
                                :disabled="!edcForm.approval_code.trim() || isSubmitting"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-40"
                                @click="handleEdcSubmit"
                            >
                                <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                                <CreditCard v-else class="h-4 w-4 text-[#beedc0]" />
                                Verifikasi Gesek EDC
                            </button>
                        </div>

                        <!-- 4. VA VIEW -->
                        <div v-else-if="selectedMethod === 'va'" class="mt-4 space-y-3">
                            <div v-if="xenditInvoiceUrl" class="rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-center">
                                <span class="font-bold text-xs text-emerald-900">Tautan VA Aktif</span>
                                <div class="mt-2">
                                    <a
                                        :href="xenditInvoiceUrl"
                                        target="_blank"
                                        class="inline-flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white"
                                    >
                                        <ExternalLink class="h-3.5 w-3.5" />
                                        Buka Checkout VA
                                    </a>
                                </div>
                            </div>
                            <div v-else>
                                <button
                                    type="button"
                                    :disabled="isSubmitting"
                                    class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-[#333333] disabled:opacity-50"
                                    @click="handleGenerateXenditInvoice"
                                >
                                    <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                                    <Building2 v-else class="h-4 w-4 text-[#beedc0]" />
                                    Generate Tagihan VA Bank
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
