<script setup lang="ts">
/**
 * Billing/Index.vue — Panel Kasir & Manajemen Tagihan SIMRS
 * 
 * Khusus Staf / Perawat Tetap (Pekerja).
 * Menyediakan pemantauan invoice, pembuatan billing otomatis dari antrean dokter,
 * filter status pembayaran, dan aksi pembayaran tunai / QRIS / EDC via PaymentModal.
 */
import { Head, Link, router } from '@inertiajs/vue3'
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Download,
    Eye,
    FileText,
    Filter,
    Loader2,
    PlusCircle,
    Receipt,
    RefreshCw,
    Search,
    ShieldCheck,
    TrendingUp,
    UserCheck,
    Wallet,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, ref, watch } from 'vue'
import PaymentModal from '@/components/PaymentModal.vue'
import ShiftManagementModal from '@/components/ShiftManagementModal.vue'
import ThermalReceiptModal from '@/components/ThermalReceiptModal.vue'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import type { Billing } from '@/types/hospital'

interface Props {
    billings: {
        data: Billing[]
        current_page: number
        last_page: number
        total: number
        links: { url: string | null; label: string; active: boolean }[]
    }
    stats: {
        total_invoices: number
        unpaid_count: number
        pending_count: number
        paid_count: number
        total_revenue: number
        today_revenue: number
    }
    unbilledConsultations: any[]
    filters: {
        search?: string
        status?: string
        date?: string
    }
}

const props = defineProps<Props>()

// Reactive filter state
const searchQuery = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedDate = ref(props.filters.date || '')
const isProcessing = ref(false)

// Quick Pay Modal State
const selectedBillingForPay = ref<Billing | null>(null)
const isPayModalOpen = ref(false)

// Cashier Shift Modal State
const isShiftModalOpen = ref(false)

// Thermal Receipt Modal State
const selectedBillingForThermal = ref<any | null>(null)
const isThermalModalOpen = ref(false)

const openThermalModal = (bill: any) => {
    selectedBillingForThermal.value = bill
    isThermalModalOpen.value = true
}

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

// Search debounce
let timeout: ReturnType<typeof setTimeout> | null = null
const handleFilterChange = () => {
    if (timeout) {
clearTimeout(timeout)
}

    timeout = setTimeout(() => {
        router.get(
            '/staff/billing',
            {
                search: searchQuery.value || undefined,
                status: selectedStatus.value || undefined,
                date: selectedDate.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        )
    }, 300)
}

watch([searchQuery, selectedStatus, selectedDate], handleFilterChange)

// Reset filters
const resetFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = ''
    selectedDate.value = ''
    router.get('/staff/billing', {}, { preserveScroll: true })
}

// Create Billing Confirmation Modal State
const isCreateBillingModalOpen = ref(false)
const selectedUnbilledForBilling = ref<any | null>(null)

const openCreateBillingModal = (unbilled: any) => {
    selectedUnbilledForBilling.value = unbilled
    isCreateBillingModalOpen.value = true
}

const closeCreateBillingModal = () => {
    isCreateBillingModalOpen.value = false
    selectedUnbilledForBilling.value = null
}

const confirmCreateBilling = () => {
    if (!selectedUnbilledForBilling.value) {
return
}

    isProcessing.value = true
    const reservationId = selectedUnbilledForBilling.value.appointment_id ?? selectedUnbilledForBilling.value.reservation_id
    router.post(
        `/staff/billing/create-from-reservation/${reservationId}`,
        {},
        {
            onFinish: () => {
                isProcessing.value = false
                closeCreateBillingModal()
            },
        }
    )
}

// Open Quick Pay Modal
const openPayModal = (billing: Billing) => {
    selectedBillingForPay.value = billing
    isPayModalOpen.value = true
}

const handlePaymentSuccess = () => {
    router.reload()
}
</script>

<template>
    <AppSidebarLayout>
        <Head title="Kasir & Manajemen Tagihan - SIMRS" />

        <div class="min-h-screen bg-[#edede2] px-4 py-6 font-['Rubik'] text-[#000000] md:px-8">
            <!-- ═══════════════════════════════════════════════════════════════
                 Header & RBAC Badge
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000] md:text-3xl">
                            Kasir & Manajemen Tagihan
                        </h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3 py-1 text-xs font-semibold text-[#065f46]">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Akses Kasir Tetap
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-[#333333]">
                        Otorisasi pelunasan tagihan rawat jalan, integrasi QRIS Dinamis & Gateway Xendit, EDC, serta cetak kuitansi resmi.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-full border border-[#000000] bg-[#000000] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#222222] cursor-pointer"
                        @click="isShiftModalOpen = true"
                    >
                        <Wallet class="h-4 w-4 text-[#beedc0]" />
                        Sesi Shift Kasir
                    </button>

                    <button
                        type="button"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-full border border-[#333333]/20 bg-[#fffff3] px-4 py-2 text-sm font-medium text-[#000000] shadow-sm transition hover:bg-[#edede2]"
                        @click="resetFilters"
                    >
                        <RefreshCw class="h-4 w-4" />
                        Segarkan Data
                    </button>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 KPI Cards (Financial & Billing Metrics)
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <!-- Total Invoices -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.05 }"
                    class="flex flex-col justify-between rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-[#666666]">Total Invoice</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#edede2] text-[#000000]">
                            <FileText class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-[#000000]">{{ stats.total_invoices }}</div>
                </motion.div>

                <!-- Unpaid Invoices -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.1 }"
                    class="flex flex-col justify-between rounded-2xl border border-rose-200 bg-rose-50/70 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-rose-800">Belum Bayar</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                            <AlertCircle class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-rose-900">{{ stats.unpaid_count }}</div>
                </motion.div>

                <!-- Pending Xendit -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.15 }"
                    class="flex flex-col justify-between rounded-2xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-amber-800">Menunggu Bayar</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <Clock class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-amber-900">{{ stats.pending_count }}</div>
                </motion.div>

                <!-- Paid Invoices -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.2 }"
                    class="flex flex-col justify-between rounded-2xl border border-[#beedc0] bg-[#beedc0]/30 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-[#065f46]">Lunas</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#beedc0] text-[#065f46]">
                            <CheckCircle2 class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-[#065f46]">{{ stats.paid_count }}</div>
                </motion.div>

                <!-- Today's Revenue -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.25 }"
                    class="flex flex-col justify-between rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-[#666666]">Kas Hari Ini</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#edede2] text-[#000000]">
                            <Wallet class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-lg font-bold text-[#000000]">{{ formatRupiah(stats.today_revenue) }}</div>
                </motion.div>

                <!-- Total Revenue -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3 }"
                    class="flex flex-col justify-between rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-[#666666]">Total Penerimaan</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#edede2] text-[#000000]">
                            <TrendingUp class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-3 text-lg font-bold text-[#000000]">{{ formatRupiah(stats.total_revenue) }}</div>
                </motion.div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Unbilled Completed Consultations (Quick Create Action)
                 ═══════════════════════════════════════════════════════════════ -->
            <div v-if="unbilledConsultations.length > 0" class="mb-8 rounded-2xl border border-amber-300 bg-amber-50/60 p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-amber-900">
                        <AlertCircle class="h-5 w-5 text-amber-600" />
                        <h3 class="text-base font-semibold">Pasien Selesai Konsultasi (Siap Dibuatkan Billing)</h3>
                    </div>
                    <span class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                        {{ unbilledConsultations.length }} Menunggu
                    </span>
                </div>
                <p class="mb-4 text-xs text-amber-800">
                    Pasien berikut telah diperiksa oleh dokter dan memiliki rekam medis / resep obat yang siap dikonversi ke rincian tagihan kasir.
                </p>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in unbilledConsultations"
                        :key="item.appointment_id"
                        class="flex items-center justify-between rounded-xl border border-amber-200 bg-white p-3.5 shadow-sm"
                    >
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[#000000]">{{ item.patient?.name }}</span>
                                <span class="rounded bg-neutral-100 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-600">
                                    {{ item.queue_number }}
                                </span>
                            </div>
                            <p class="text-xs text-neutral-600">
                                {{ item.doctor_schedule?.poli?.name_poli ?? 'Poliklinik' }} · {{ item.doctor_schedule?.doctor?.name }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="isProcessing"
                            class="inline-flex min-h-[38px] items-center gap-1.5 rounded-lg bg-[#000000] px-3.5 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#333333] disabled:opacity-50"
                            @click="openCreateBillingModal(item)"
                        >
                            <PlusCircle class="h-3.5 w-3.5 text-[#beedc0]" />
                            Buat Tagihan
                        </button>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Filter & Search Bar
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nomor invoice, nama pasien, atau NIK..."
                        class="min-h-[44px] w-full rounded-xl border border-neutral-300 bg-[#edede2]/40 pl-10 pr-4 text-sm text-[#000000] placeholder-neutral-500 focus:border-[#000000] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#000000]"
                    />
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        :class="selectedStatus === '' ? 'bg-[#000000] text-white' : 'bg-[#edede2] text-[#000000] hover:bg-[#e2e2d5]'"
                        class="min-h-[38px] rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        @click="selectedStatus = ''"
                    >
                        Semua Status
                    </button>
                    <button
                        type="button"
                        :class="selectedStatus === 'unpaid' ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800 hover:bg-rose-200'"
                        class="min-h-[38px] rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        @click="selectedStatus = 'unpaid'"
                    >
                        Belum Lunas
                    </button>
                    <button
                        type="button"
                        :class="selectedStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800 hover:bg-amber-200'"
                        class="min-h-[38px] rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        @click="selectedStatus = 'pending'"
                    >
                        Menunggu Bayar
                    </button>
                    <button
                        type="button"
                        :class="selectedStatus === 'paid' ? 'bg-[#065f46] text-white' : 'bg-[#beedc0]/50 text-[#065f46] hover:bg-[#beedc0]'"
                        class="min-h-[38px] rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        @click="selectedStatus = 'paid'"
                    >
                        Lunas
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Invoices Data Table
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="overflow-hidden rounded-2xl border border-[#333333]/15 bg-[#fffff3] shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-[#333333]/15 bg-[#edede2]/60 text-xs uppercase tracking-wider text-[#666666]">
                            <tr>
                                <th class="px-5 py-3.5">Invoice & Waktu</th>
                                <th class="px-5 py-3.5">Pasien & Rekam Medis</th>
                                <th class="px-5 py-3.5">Poliklinik / Dokter</th>
                                <th class="px-5 py-3.5 text-right">Total Tagihan</th>
                                <th class="px-5 py-3.5">Metode</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5">Kasir</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#333333]/10">
                            <tr
                                v-for="bill in billings.data"
                                :key="bill.billing_id"
                                class="transition-colors hover:bg-[#edede2]/30"
                            >
                                <!-- Invoice Number -->
                                <td class="px-5 py-4">
                                    <div class="font-bold text-[#000000]">{{ bill.invoice_number }}</div>
                                    <div class="text-xs text-neutral-500">
                                        {{ new Date(bill.created_at || '').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </div>
                                </td>

                                <!-- Patient Info -->
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-[#000000]">{{ bill.patient?.name ?? '-' }}</div>
                                    <div class="text-xs text-neutral-500">NIK: {{ bill.patient?.resident_n ?? '-' }}</div>
                                </td>

                                <!-- Poli & Doctor -->
                                <td class="px-5 py-4">
                                    <div class="font-medium text-[#333333]">
                                        {{ bill.reservation?.doctor_schedule?.poli?.name_poli ?? 'Poliklinik' }}
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        {{ bill.reservation?.doctor_schedule?.doctor?.name ?? '-' }}
                                    </div>
                                </td>

                                <!-- Total Amount -->
                                <td class="px-5 py-4 text-right font-bold text-[#000000]">
                                    {{ formatRupiah(bill.total_amount) }}
                                </td>

                                <!-- Payment Method -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700">
                                        <CreditCard v-if="bill.payment_method?.includes('xendit') || bill.payment_method?.includes('edc')" class="h-3 w-3" />
                                        <DollarSign v-else class="h-3 w-3" />
                                        {{ bill.payment_method ? bill.payment_method.toUpperCase().replace('_', ' ') : '-' }}
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4 text-center">
                                    <span
                                        v-if="bill.status === 'paid'"
                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800"
                                    >
                                        <CheckCircle2 class="h-3 w-3" />
                                        Lunas
                                    </span>
                                    <span
                                        v-else-if="bill.status === 'pending'"
                                        class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800"
                                    >
                                        <Clock class="h-3 w-3" />
                                        Menunggu Bayar
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full border border-rose-300 bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800"
                                    >
                                        <AlertCircle class="h-3 w-3" />
                                        Belum Lunas
                                    </span>
                                </td>

                                <!-- Cashier Name -->
                                <td class="px-5 py-4 text-xs text-neutral-600">
                                    {{ bill.processed_by_nurse?.name ?? '-' }}
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Bayar Kasir Modal Trigger (jika belum lunas) -->
                                        <button
                                            v-if="bill.status !== 'paid'"
                                            type="button"
                                            class="inline-flex min-h-[36px] items-center gap-1 rounded-lg bg-[#000000] px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-[#333333]"
                                            @click="openPayModal(bill)"
                                        >
                                            <Wallet class="h-3.5 w-3.5 text-[#beedc0]" />
                                            Bayar Kasir
                                        </button>

                                        <!-- Cetak Kuitansi PDF (jika lunas) -->
                                        <a
                                            v-if="bill.status === 'paid'"
                                            :href="`/staff/billing/${bill.billing_id}/print-receipt?stream=1`"
                                            target="_blank"
                                            class="inline-flex min-h-[36px] items-center gap-1 rounded-lg border border-[#beedc0] bg-[#beedc0]/30 px-3 py-1 text-xs font-semibold text-[#065f46] transition hover:bg-[#beedc0]"
                                        >
                                            <Download class="h-3.5 w-3.5" />
                                            PDF
                                        </a>

                                        <!-- Cetak Struk Thermal POS ESC/POS (jika lunas) -->
                                        <button
                                            v-if="bill.status === 'paid'"
                                            type="button"
                                            @click="openThermalModal(bill)"
                                            class="inline-flex min-h-[36px] items-center gap-1 rounded-lg border border-[#333333]/20 bg-[#fffff3] px-3 py-1 text-xs font-semibold text-[#000000] transition hover:bg-[#edede2] cursor-pointer"
                                            title="Cetak Struk Kertas Thermal 58mm/80mm"
                                        >
                                            <Receipt class="h-3.5 w-3.5 text-[#000000]" />
                                            Thermal
                                        </button>

                                        <!-- Detail Link -->
                                        <Link
                                            :href="`/staff/billing/${bill.billing_id}`"
                                            class="inline-flex min-h-[36px] items-center justify-center rounded-lg border border-neutral-300 bg-white p-2 text-neutral-700 transition hover:bg-neutral-100"
                                            title="Lihat Rincian Tagihan"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="billings.data.length === 0">
                                <td colspan="8" class="px-5 py-12 text-center text-neutral-500">
                                    <Receipt class="mx-auto mb-2 h-10 w-10 text-neutral-400" />
                                    <p class="font-medium">Tidak ada data tagihan yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="billings.last_page > 1" class="flex items-center justify-between border-t border-[#333333]/15 px-5 py-4">
                    <div class="text-xs text-neutral-500">
                        Menampilkan halaman <strong>{{ billings.current_page }}</strong> dari <strong>{{ billings.last_page }}</strong> (Total {{ billings.total }} tagihan)
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in billings.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-[#000000] text-white font-bold' : 'bg-white text-neutral-700 hover:bg-neutral-100',
                                !link.url ? 'opacity-40 cursor-not-allowed' : '',
                            ]"
                            class="min-h-[36px] min-w-[36px] rounded-lg border border-neutral-300 px-3 py-1.5 text-xs transition"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modern POS Payment Modal Component
             ═══════════════════════════════════════════════════════════════ -->
        <PaymentModal
            :billing="selectedBillingForPay"
            :is-open="isPayModalOpen"
            @close="isPayModalOpen = false"
            @success="handlePaymentSuccess"
        />

        <!-- Cashier Shift Management & Cash Reconciliation Modal -->
        <ShiftManagementModal
            v-model:open="isShiftModalOpen"
            @shift-changed="resetFilters"
        />

        <!-- Thermal Receipt POS ESC/POS Modal (58mm/80mm) -->
        <ThermalReceiptModal
            v-model:open="isThermalModalOpen"
            :billing="selectedBillingForThermal"
        />

        <!-- ═══════════════════════════════════════════════════════════════
             Custom Confirmation Modal: Terbitkan Tagihan Pasien
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="isCreateBillingModalOpen && selectedUnbilledForBilling"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/50 p-4 backdrop-blur-sm"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :exit="{ opacity: 0, scale: 0.95 }"
                :transition="{ duration: 0.2 }"
                class="w-full max-w-md rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-xl space-y-4 font-['Rubik'] text-[#000000]"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-[#333333]/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-full bg-[#beedc0] text-[#065f46]">
                            <Receipt class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-['ivypresto-headline'] text-base font-bold text-[#000000]">
                                Konfirmasi Pembuatan Tagihan
                            </h3>
                            <p class="text-xs text-[#333333]/70">
                                Sistem Point-of-Sale (POS) Kasir
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeCreateBillingModal"
                        class="rounded-full p-1.5 text-[#333333]/60 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- Patient & Consultation Card -->
                <div class="rounded-[10px] bg-[#edede2]/60 p-4 text-xs space-y-2.5 text-[#333333]">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-[#333333]/70">Nama Pasien</span>
                            <div class="font-bold text-sm text-[#000000]">
                                {{ selectedUnbilledForBilling.patient?.name ?? 'Pasien Rawat Jalan' }}
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-[#000000] px-2.5 py-0.5 font-mono text-[11px] font-bold text-[#ffffff]">
                            {{ selectedUnbilledForBilling.queue_number }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-t border-[#333333]/10 pt-2 text-[11px]">
                        <div>
                            <span class="text-[#333333]/70">Poliklinik:</span>
                            <div class="font-semibold text-[#000000]">
                                {{ selectedUnbilledForBilling.doctor_schedule?.poli?.name_poli ?? 'Poli Rawat Jalan' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-[#333333]/70">Dokter Pemeriksa:</span>
                            <div class="font-semibold text-[#000000]">
                                {{ selectedUnbilledForBilling.doctor_schedule?.doctor?.name ?? 'Dokter Spesialis' }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[8px] border border-[#beedc0] bg-[#beedc0]/30 p-2.5 text-[11px] text-[#065f46] space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <CheckCircle2 class="size-3.5" />
                            <span>Kalkulasi Otomatis SIMRS</span>
                        </div>
                        <p class="text-[10px] leading-relaxed text-[#065f46]/90">
                            Tagihan kasir baru akan diterbitkan dengan rincian biaya konsultasi dokter serta biaya obat farmasi terkait.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="closeCreateBillingModal"
                        :disabled="isProcessing"
                        class="min-h-[44px] rounded-[40.5px] border border-[#333333]/20 bg-transparent px-5 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] disabled:opacity-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmCreateBilling"
                        :disabled="isProcessing"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#333333] disabled:opacity-50"
                    >
                        <Loader2 v-if="isProcessing" class="size-4 animate-spin text-[#beedc0]" />
                        <Receipt v-else class="size-4 text-[#beedc0]" />
                        <span>{{ isProcessing ? 'Menerbitkan...' : 'Terbitkan Tagihan' }}</span>
                    </button>
                </div>
            </motion.div>
        </div>
    </AppSidebarLayout>
</template>
