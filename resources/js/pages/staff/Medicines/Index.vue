<script setup lang="ts">
/**
 * @file Index.vue (staff/Medicines/Index.vue)
 * @description Modul Manajemen Master Data & Inventori Obat Farmasi Rumah Sakit.
 * 
 * Khusus Staf / Perawat Tetap (Pekerja):
 *  - Pencarian, filter sediaan, dan filter status stok (Habis, Menipis, Tersedia).
 *  - Modal Tambah & Edit Master Obat.
 *  - Modal Penyesuaian / Restok Cepat Inventori dengan kalkulasi live.
 *  - Metrik finansial total nilai inventori farmasi.
 *  - Mengikuti DESIGN.md (Evergreen Theme) & GEMINI.md.
 */
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import {
    AlertCircle,
    AlertTriangle,
    ArrowDownRight,
    ArrowLeft,
    ArrowUpRight,
    CheckCircle2,
    Edit3,
    Filter,
    Layers,
    Loader2,
    Package,
    PackageCheck,
    PackageMinus,
    PackagePlus,
    Pill,
    Plus,
    RefreshCw,
    Search,
    ShieldAlert,
    Trash2,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, ref, watch } from 'vue'

interface Medicine {
    medicine_id: number
    code_medicine: string
    name_medicine: string
    type: string
    stock: number
    unit: string
    price: string | number
    created_at?: string
    updated_at?: string
}

interface Stats {
    total_items: number
    out_of_stock_count: number
    low_stock_count: number
    available_count: number
    total_stock_units: number
    total_inventory_value: number
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: PaginationLink[]
}

interface Props {
    medicines: PaginatedData<Medicine>
    stats: Stats
    availableTypes: string[]
    filters: {
        search?: string
        type?: string
        stock_status?: string
    }
}

const props = defineProps<Props>()

// ── Search & Filter State ──
const searchQuery = ref(props.filters.search || '')
const selectedType = ref(props.filters.type || '')
const selectedStockStatus = ref(props.filters.stock_status || 'all')

// Format Rupiah
const formatRupiah = (val: number | string | null | undefined): string => {
    const num = typeof val === 'string' ? parseFloat(val) : (val || 0)
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num)
}

// Debounced Search/Filter
let searchTimeout: ReturnType<typeof setTimeout> | null = null
const applyFilters = () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(
            '/staff/medicines',
            {
                search: searchQuery.value || undefined,
                type: selectedType.value || undefined,
                stock_status: selectedStockStatus.value !== 'all' ? selectedStockStatus.value : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        )
    }, 300)
}

watch([searchQuery, selectedType, selectedStockStatus], () => {
    applyFilters()
})

const resetFilters = () => {
    searchQuery.value = ''
    selectedType.value = ''
    selectedStockStatus.value = 'all'
}

// ── Modal Create / Edit Medicine ──
const isFormModalOpen = ref(false)
const isEditing = ref(false)
const selectedMedicine = ref<Medicine | null>(null)

const medicineForm = useForm({
    code_medicine: '',
    name_medicine: '',
    type: 'Tablet',
    stock: 100,
    unit: 'Strip',
    price: 10000,
})

const openCreateModal = () => {
    isEditing.value = false
    selectedMedicine.value = null
    medicineForm.reset()
    medicineForm.clearErrors()
    isFormModalOpen.value = true
}

const openEditModal = (med: Medicine) => {
    isEditing.value = true
    selectedMedicine.value = med
    medicineForm.clearErrors()
    medicineForm.code_medicine = med.code_medicine
    medicineForm.name_medicine = med.name_medicine
    medicineForm.type = med.type
    medicineForm.stock = med.stock
    medicineForm.unit = med.unit
    medicineForm.price = Number(med.price)
    isFormModalOpen.value = true
}

const closeFormModal = () => {
    isFormModalOpen.value = false
    medicineForm.reset()
    medicineForm.clearErrors()
}

const submitMedicineForm = () => {
    if (isEditing.value && selectedMedicine.value) {
        medicineForm.put(`/staff/medicines/${selectedMedicine.value.medicine_id}`, {
            onSuccess: () => closeFormModal(),
        })
    } else {
        medicineForm.post('/staff/medicines', {
            onSuccess: () => closeFormModal(),
        })
    }
}

// ── Modal Penyesuaian Stok (Stock Adjustment) ──
const isStockModalOpen = ref(false)
const adjustingMedicine = ref<Medicine | null>(null)

const stockForm = useForm({
    type: 'add' as 'add' | 'subtract' | 'set',
    amount: 10,
    notes: '',
})

const openStockModal = (med: Medicine) => {
    adjustingMedicine.value = med
    stockForm.reset()
    stockForm.clearErrors()
    stockForm.type = 'add'
    stockForm.amount = 10
    stockForm.notes = ''
    isStockModalOpen.value = true
}

const closeStockModal = () => {
    isStockModalOpen.value = false
    adjustingMedicine.value = null
    stockForm.reset()
}

const previewResultStock = computed(() => {
    if (!adjustingMedicine.value) return 0
    const curr = Number(adjustingMedicine.value.stock) || 0
    const amt = Number(stockForm.amount) || 0

    if (stockForm.type === 'add') return curr + amt
    if (stockForm.type === 'subtract') return Math.max(0, curr - amt)
    if (stockForm.type === 'set') return Math.max(0, amt)
    return curr
})

const submitStockAdjustment = () => {
    if (!adjustingMedicine.value) return

    stockForm.post(`/staff/medicines/${adjustingMedicine.value.medicine_id}/adjust-stock`, {
        onSuccess: () => closeStockModal(),
    })
}

// ── Custom Delete Confirmation Modal ──
const isDeleteModalOpen = ref(false)
const deletingMedicine = ref<Medicine | null>(null)
const isDeleting = ref(false)

const openDeleteModal = (med: Medicine) => {
    deletingMedicine.value = med
    isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false
    deletingMedicine.value = null
}

const confirmDeleteMedicine = () => {
    if (!deletingMedicine.value) return
    isDeleting.value = true
    router.delete(`/staff/medicines/${deletingMedicine.value.medicine_id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false
            closeDeleteModal()
        },
    })
}
</script>

<template>
    <Head title="Inventori Obat Farmasi - Hospital Population" />

    <div class="min-h-screen bg-[#edede2] px-4 py-6 font-['Rubik'] text-[#000000] sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            <!-- ═══════════════════════════════════════════════════════════════
                 Header Seksi & Tombol Aksi Utama
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut' }"
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#333333]/70">
                        <Link href="/staff" class="hover:text-[#000000] hover:underline">
                            Dashboard Staf
                        </Link>
                        <span>/</span>
                        <span class="text-[#065f46]">Inventori Farmasi</span>
                    </div>
                    <h1 class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl">
                        Katalog & Inventori Obat Farmasi
                    </h1>
                    <p class="text-xs text-[#333333]/80 sm:text-sm">
                        Monitoring stok fisik real-time, pengadaan restok obat, dan pemutakhiran tarif farmasi rumah sakit.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02, y: -1 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="openCreateModal"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-5 py-2.5 text-sm font-semibold text-[#ffffff] shadow-sm hover:bg-[#333333] transition-all"
                    >
                        <Plus class="size-4 text-[#beedc0]" />
                        <span>Tambah Obat Baru</span>
                    </motion.button>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Ringkasan Statistik KPI Inventori
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- 1. Total Jenis Obat -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.05 }"
                    class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#333333]/70">Total Katalog</span>
                        <div class="flex size-9 items-center justify-center rounded-full bg-[#beedc0]/40 text-[#065f46]">
                            <Pill class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000]">
                            {{ stats.total_items }}
                        </span>
                        <span class="text-xs text-[#333333]/70">SKU Jenis Obat</span>
                    </div>
                    <div class="mt-2 text-xs text-[#333333]/70">
                        Total stok fisik: <strong class="text-[#000000]">{{ stats.total_stock_units.toLocaleString('id-ID') }}</strong> unit
                    </div>
                </motion.div>

                <!-- 2. Stok Habis (Out of Stock) -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.1 }"
                    class="rounded-[10px] border p-5 shadow-sm transition-all"
                    :class="stats.out_of_stock_count > 0 ? 'border-rose-300 bg-rose-50/50' : 'border-[#333333]/12 bg-[#fffff3]'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider" :class="stats.out_of_stock_count > 0 ? 'text-rose-800' : 'text-[#333333]/70'">
                            Stok Habis (0)
                        </span>
                        <div class="flex size-9 items-center justify-center rounded-full" :class="stats.out_of_stock_count > 0 ? 'bg-rose-100 text-rose-700' : 'bg-[#beedc0]/40 text-[#065f46]'">
                            <ShieldAlert class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="font-['ivypresto-headline'] text-2xl font-bold" :class="stats.out_of_stock_count > 0 ? 'text-rose-700' : 'text-[#000000]'">
                            {{ stats.out_of_stock_count }}
                        </span>
                        <span class="text-xs" :class="stats.out_of_stock_count > 0 ? 'text-rose-700' : 'text-[#333333]/70'">Obat Kosong</span>
                    </div>
                    <div class="mt-2 text-xs" :class="stats.out_of_stock_count > 0 ? 'text-rose-700 font-medium' : 'text-[#333333]/70'">
                        {{ stats.out_of_stock_count > 0 ? 'Perlu tindakan restok segera' : 'Semua obat memiliki stok' }}
                    </div>
                </motion.div>

                <!-- 3. Stok Menipis (Low Stock <= 10) -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.15 }"
                    class="rounded-[10px] border p-5 shadow-sm transition-all"
                    :class="stats.low_stock_count > 0 ? 'border-amber-300 bg-amber-50/50' : 'border-[#333333]/12 bg-[#fffff3]'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider" :class="stats.low_stock_count > 0 ? 'text-amber-800' : 'text-[#333333]/70'">
                            Stok Kritis (≤10)
                        </span>
                        <div class="flex size-9 items-center justify-center rounded-full" :class="stats.low_stock_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-[#beedc0]/40 text-[#065f46]'">
                            <AlertTriangle class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="font-['ivypresto-headline'] text-2xl font-bold" :class="stats.low_stock_count > 0 ? 'text-amber-700' : 'text-[#000000]'">
                            {{ stats.low_stock_count }}
                        </span>
                        <span class="text-xs" :class="stats.low_stock_count > 0 ? 'text-amber-700' : 'text-[#333333]/70'">Obat Menipis</span>
                    </div>
                    <div class="mt-2 text-xs" :class="stats.low_stock_count > 0 ? 'text-amber-700 font-medium' : 'text-[#333333]/70'">
                        {{ stats.low_stock_count > 0 ? 'Disarankan membuat PO restok' : 'Persediaan dalam batas aman' }}
                    </div>
                </motion.div>

                <!-- 4. Estimasi Nilai Inventori -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.2 }"
                    class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#333333]/70">Nilai Inventori</span>
                        <div class="flex size-9 items-center justify-center rounded-full bg-[#beedc0]/40 text-[#065f46]">
                            <PackageCheck class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="font-['ivypresto-headline'] text-xl font-bold text-[#065f46] sm:text-2xl">
                            {{ formatRupiah(stats.total_inventory_value) }}
                        </span>
                    </div>
                    <div class="mt-2 text-xs text-[#333333]/70">
                        Valuasi total obat di gudang farmasi
                    </div>
                </motion.div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Pencarian, Filter Sediaan, & Filter Status
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, delay: 0.25 }"
                class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-4 shadow-sm"
            >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Search Bar -->
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[#333333]/50" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari berdasarkan nama obat atau kode (mis: Paracetamol, MED-PCT)..."
                            class="min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 pl-10 pr-4 text-sm text-[#000000] placeholder:text-[#333333]/40 focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#333333]/40 hover:text-[#000000]"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Filter Dropdown & Pills -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Dropdown Sediaan -->
                        <div class="relative min-w-[140px]">
                            <select
                                v-model="selectedType"
                                class="min-h-[44px] w-full appearance-none rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 py-2 text-xs font-medium text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                            >
                                <option value="">Semua Sediaan</option>
                                <option v-for="t in availableTypes" :key="t" :value="t">
                                    {{ t }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Stok Pills -->
                        <div class="flex items-center rounded-[40.5px] border border-[#333333]/15 bg-[#edede2]/70 p-1 text-xs">
                            <button
                                type="button"
                                @click="selectedStockStatus = 'all'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="selectedStockStatus === 'all' ? 'bg-[#000000] text-[#ffffff] shadow-sm' : 'text-[#333333] hover:text-[#000000]'"
                            >
                                Semua
                            </button>
                            <button
                                type="button"
                                @click="selectedStockStatus = 'out'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="selectedStockStatus === 'out' ? 'bg-rose-600 text-[#ffffff] shadow-sm' : 'text-[#333333] hover:text-rose-700'"
                            >
                                Habis (0)
                            </button>
                            <button
                                type="button"
                                @click="selectedStockStatus = 'low'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="selectedStockStatus === 'low' ? 'bg-amber-600 text-[#ffffff] shadow-sm' : 'text-[#333333] hover:text-amber-700'"
                            >
                                Kritis (≤10)
                            </button>
                            <button
                                type="button"
                                @click="selectedStockStatus = 'available'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="selectedStockStatus === 'available' ? 'bg-[#065f46] text-[#ffffff] shadow-sm' : 'text-[#333333] hover:text-[#065f46]'"
                            >
                                Tersedia
                            </button>
                        </div>

                        <!-- Reset Filter -->
                        <button
                            v-if="searchQuery || selectedType || selectedStockStatus !== 'all'"
                            type="button"
                            @click="resetFilters"
                            class="inline-flex min-h-[44px] items-center gap-1 rounded-[40.5px] px-3 text-xs font-semibold text-[#333333] hover:text-[#000000]"
                        >
                            <RefreshCw class="size-3.5" />
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Tabel Master Data & Inventori Obat
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, delay: 0.3 }"
                class="overflow-hidden rounded-[10px] border border-[#333333]/12 bg-[#fffff3] shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-[#333333]/10 bg-[#edede2]/60 text-[11px] font-bold uppercase tracking-wider text-[#333333]/80">
                                <th class="px-5 py-3.5">Kode & Nama Obat</th>
                                <th class="px-4 py-3.5">Bentuk Sediaan</th>
                                <th class="px-4 py-3.5 text-center">Stok Fisik</th>
                                <th class="px-4 py-3.5">Satuan</th>
                                <th class="px-4 py-3.5 text-right">Harga Satuan</th>
                                <th class="px-4 py-3.5 text-right">Nilai Stok</th>
                                <th class="px-5 py-3.5 text-center">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#333333]/8">
                            <tr
                                v-for="med in medicines.data"
                                :key="med.medicine_id"
                                class="transition-colors hover:bg-[#edede2]/40"
                            >
                                <!-- Kode & Nama -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-9 shrink-0 items-center justify-center rounded-lg font-mono text-xs font-bold"
                                            :class="med.stock <= 0 ? 'bg-rose-100 text-rose-700' : med.stock <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-[#beedc0]/50 text-[#065f46]'"
                                        >
                                            <Pill class="size-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-[#000000]">{{ med.name_medicine }}</div>
                                            <div class="font-mono text-xs font-medium text-[#333333]/70">
                                                {{ med.code_medicine }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tipe / Sediaan -->
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full bg-[#edede2] px-2.5 py-1 text-xs font-medium text-[#000000]">
                                        {{ med.type }}
                                    </span>
                                </td>

                                <!-- Stok Fisik -->
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-[56px] items-center justify-center rounded-full px-2.5 py-1 text-xs font-bold"
                                        :class="med.stock <= 0 ? 'bg-rose-100 text-rose-700 border border-rose-200' : med.stock <= 10 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                    >
                                        {{ med.stock }}
                                    </span>
                                </td>

                                <!-- Satuan -->
                                <td class="px-4 py-4 text-xs font-medium text-[#333333]">
                                    {{ med.unit }}
                                </td>

                                <!-- Harga Satuan -->
                                <td class="px-4 py-4 text-right font-medium text-[#000000]">
                                    {{ formatRupiah(med.price) }}
                                </td>

                                <!-- Nilai Stok -->
                                <td class="px-4 py-4 text-right font-semibold text-[#065f46]">
                                    {{ formatRupiah(Number(med.price) * Number(med.stock)) }}
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Restok / Sesuaikan Stok -->
                                        <motion.button
                                            type="button"
                                            :whileHover="{ scale: 1.05 }"
                                            :whileTap="{ scale: 0.95 }"
                                            @click="openStockModal(med)"
                                            title="Sesuaikan Stok / Restok"
                                            class="inline-flex min-h-[36px] items-center gap-1 rounded-[40.5px] border border-[#065f46]/30 bg-[#beedc0]/40 px-3 py-1.5 text-xs font-semibold text-[#065f46] hover:bg-[#beedc0] transition-all"
                                        >
                                            <PackagePlus class="size-3.5" />
                                            <span>Stok</span>
                                        </motion.button>

                                        <!-- Tombol Edit -->
                                        <motion.button
                                            type="button"
                                            :whileHover="{ scale: 1.05 }"
                                            :whileTap="{ scale: 0.95 }"
                                            @click="openEditModal(med)"
                                            title="Edit Data Obat"
                                            class="inline-flex min-h-[36px] size-9 items-center justify-center rounded-full border border-[#333333]/15 bg-[#ffffff] text-[#333333] hover:bg-[#edede2] hover:text-[#000000] transition-all"
                                        >
                                            <Edit3 class="size-3.5" />
                                        </motion.button>

                                        <!-- Tombol Hapus -->
                                        <motion.button
                                            type="button"
                                            :whileHover="{ scale: 1.05 }"
                                            :whileTap="{ scale: 0.95 }"
                                            @click="openDeleteModal(med)"
                                            title="Hapus Obat"
                                            class="inline-flex min-h-[36px] size-9 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </motion.button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="medicines.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-[#333333]/60">
                                    <Pill class="mx-auto size-8 text-[#333333]/30" />
                                    <div class="mt-2 font-medium">Tidak ada data obat yang sesuai dengan pencarian.</div>
                                    <button
                                        v-if="searchQuery || selectedType || selectedStockStatus !== 'all'"
                                        type="button"
                                        @click="resetFilters"
                                        class="mt-2 text-xs font-semibold text-[#065f46] hover:underline"
                                    >
                                        Hapus filter pencarian
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ═══════════════════════════════════════════════════════════════
                     Pagination
                     ═══════════════════════════════════════════════════════════════ -->
                <div
                    v-if="medicines.total > medicines.per_page"
                    class="flex flex-col items-center justify-between gap-3 border-t border-[#333333]/10 bg-[#edede2]/40 px-5 py-4 sm:flex-row"
                >
                    <div class="text-xs text-[#333333]/70">
                        Menampilkan <strong>{{ medicines.data.length }}</strong> dari <strong>{{ medicines.total }}</strong> obat
                    </div>
                    <div class="flex items-center gap-1.5">
                        <template v-for="(link, i) in medicines.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex min-h-[36px] items-center justify-center rounded-[40.5px] px-3.5 text-xs font-medium transition-all"
                                :class="link.active ? 'bg-[#000000] text-[#ffffff] font-bold shadow-sm' : 'bg-[#ffffff] text-[#333333] border border-[#333333]/15 hover:bg-[#edede2]'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex min-h-[36px] items-center justify-center rounded-[40.5px] px-3.5 text-xs text-[#333333]/40"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </motion.div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         MODAL 1: Tambah / Edit Master Obat
         ═══════════════════════════════════════════════════════════════ -->
    <div
        v-if="isFormModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/50 p-4 backdrop-blur-sm"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="{ duration: 0.2 }"
            class="w-full max-w-lg rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-xl"
        >
            <div class="flex items-center justify-between border-b border-[#333333]/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#065f46]">
                        <Pill class="size-4" />
                    </div>
                    <div>
                        <h3 class="font-['ivypresto-headline'] text-lg font-bold text-[#000000]">
                            {{ isEditing ? 'Edit Data Obat Farmasi' : 'Tambah Obat Baru ke Katalog' }}
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            {{ isEditing ? 'Perbarui informasi dan harga obat' : 'Masukkan rincian spesifikasi obat farmasi' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeFormModal"
                    class="rounded-full p-1.5 text-[#333333]/60 hover:bg-[#edede2] hover:text-[#000000]"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form @submit.prevent="submitMedicineForm" class="mt-5 space-y-4 font-['Rubik'] text-sm">
                <!-- Kode Obat -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                        Kode Obat (SKU) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="medicineForm.code_medicine"
                        type="text"
                        placeholder="Contoh: MED-PCT-500"
                        class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 font-mono text-sm uppercase text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                        required
                    />
                    <p v-if="medicineForm.errors.code_medicine" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ medicineForm.errors.code_medicine }}
                    </p>
                </div>

                <!-- Nama Obat -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                        Nama Obat & Dosis <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="medicineForm.name_medicine"
                        type="text"
                        placeholder="Contoh: Paracetamol 500 mg"
                        class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-sm text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                        required
                    />
                    <p v-if="medicineForm.errors.name_medicine" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ medicineForm.errors.name_medicine }}
                    </p>
                </div>

                <!-- Grid Tipe & Satuan -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                            Bentuk Sediaan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="medicineForm.type"
                            class="mt-1 min-h-[44px] w-full appearance-none rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-sm text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                            required
                        >
                            <option value="Tablet">Tablet</option>
                            <option value="Kapsul">Kapsul</option>
                            <option value="Sirup">Sirup</option>
                            <option value="Salep">Salep</option>
                            <option value="Tetes">Tetes</option>
                            <option value="Inhaler">Inhaler</option>
                            <option value="Injeksi">Injeksi</option>
                            <option value="Suspensi">Suspensi</option>
                        </select>
                        <p v-if="medicineForm.errors.type" class="mt-1 text-xs text-rose-600 font-medium">
                            {{ medicineForm.errors.type }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                            Satuan Penjualan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="medicineForm.unit"
                            class="mt-1 min-h-[44px] w-full appearance-none rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-sm text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                            required
                        >
                            <option value="Strip">Strip</option>
                            <option value="Botol">Botol</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Kapsul">Kapsul</option>
                            <option value="Tube">Tube</option>
                            <option value="Vial">Vial</option>
                            <option value="Ampul">Ampul</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Sachet">Sachet</option>
                        </select>
                        <p v-if="medicineForm.errors.unit" class="mt-1 text-xs text-rose-600 font-medium">
                            {{ medicineForm.errors.unit }}
                        </p>
                    </div>
                </div>

                <!-- Grid Stok & Harga -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                            {{ isEditing ? 'Stok Fisik Saat Ini' : 'Stok Awal' }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="medicineForm.stock"
                            type="number"
                            min="0"
                            class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-sm text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                            required
                        />
                        <p v-if="medicineForm.errors.stock" class="mt-1 text-xs text-rose-600 font-medium">
                            {{ medicineForm.errors.stock }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                            Harga Satuan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="medicineForm.price"
                            type="number"
                            min="0"
                            step="500"
                            class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-sm font-semibold text-[#065f46] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                            required
                        />
                        <p v-if="medicineForm.errors.price" class="mt-1 text-xs text-rose-600 font-medium">
                            {{ medicineForm.errors.price }}
                        </p>
                    </div>
                </div>

                <!-- Tombol Submit & Batal -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#333333]/10">
                    <button
                        type="button"
                        @click="closeFormModal"
                        class="min-h-[44px] rounded-[40.5px] border border-[#333333]/20 bg-transparent px-5 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="medicineForm.processing"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#333333] disabled:opacity-50"
                    >
                        <span v-if="medicineForm.processing">Menyimpan...</span>
                        <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Tambah Obat' }}</span>
                    </button>
                </div>
            </form>
        </motion.div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         MODAL 2: Penyesuaian / Restok Cepat Inventori
         ═══════════════════════════════════════════════════════════════ -->
    <div
        v-if="isStockModalOpen && adjustingMedicine"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/50 p-4 backdrop-blur-sm"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :exit="{ opacity: 0, scale: 0.95 }"
            :transition="{ duration: 0.2 }"
            class="w-full max-w-md rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-xl"
        >
            <div class="flex items-center justify-between border-b border-[#333333]/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#065f46]">
                        <PackagePlus class="size-4" />
                    </div>
                    <div>
                        <h3 class="font-['ivypresto-headline'] text-base font-bold text-[#000000]">
                            Penyesuaian Stok Obat
                        </h3>
                        <p class="text-xs text-[#333333]/70 font-mono">
                            {{ adjustingMedicine.code_medicine }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeStockModal"
                    class="rounded-full p-1.5 text-[#333333]/60 hover:bg-[#edede2] hover:text-[#000000]"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- Detail Obat Terpilih -->
            <div class="mt-4 rounded-[10px] bg-[#edede2]/60 p-3.5 text-xs text-[#333333]">
                <div class="font-bold text-sm text-[#000000]">{{ adjustingMedicine.name_medicine }}</div>
                <div class="mt-1 flex items-center justify-between">
                    <span>Sediaan: {{ adjustingMedicine.type }} ({{ adjustingMedicine.unit }})</span>
                    <span>Stok Saat Ini: <strong class="text-sm font-bold text-[#000000]">{{ adjustingMedicine.stock }}</strong></span>
                </div>
            </div>

            <form @submit.prevent="submitStockAdjustment" class="mt-4 space-y-4 font-['Rubik'] text-sm">
                <!-- Tipe Aksi Penyesuaian -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                        Jenis Penyesuaian
                    </label>
                    <div class="mt-1.5 grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="stockForm.type = 'add'"
                            class="min-h-[44px] flex flex-col items-center justify-center rounded-[10px] border p-2 text-xs font-semibold transition-all"
                            :class="stockForm.type === 'add' ? 'border-[#065f46] bg-[#beedc0]/50 text-[#065f46]' : 'border-[#333333]/15 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'"
                        >
                            <PackagePlus class="size-4 mb-0.5" />
                            <span>Tambah (+)</span>
                        </button>
                        <button
                            type="button"
                            @click="stockForm.type = 'subtract'"
                            class="min-h-[44px] flex flex-col items-center justify-center rounded-[10px] border p-2 text-xs font-semibold transition-all"
                            :class="stockForm.type === 'subtract' ? 'border-rose-600 bg-rose-50 text-rose-700' : 'border-[#333333]/15 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'"
                        >
                            <PackageMinus class="size-4 mb-0.5" />
                            <span>Kurangi (-)</span>
                        </button>
                        <button
                            type="button"
                            @click="stockForm.type = 'set'"
                            class="min-h-[44px] flex flex-col items-center justify-center rounded-[10px] border p-2 text-xs font-semibold transition-all"
                            :class="stockForm.type === 'set' ? 'border-[#000000] bg-[#000000] text-[#ffffff]' : 'border-[#333333]/15 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'"
                        >
                            <Layers class="size-4 mb-0.5" />
                            <span>Atur Nilai (=)</span>
                        </button>
                    </div>
                </div>

                <!-- Jumlah Unit -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                        Jumlah Unit ({{ adjustingMedicine.unit }}) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model.number="stockForm.amount"
                        type="number"
                        min="1"
                        class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-base font-bold text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                        required
                    />
                    <p v-if="stockForm.errors.amount" class="mt-1 text-xs text-rose-600 font-medium">
                        {{ stockForm.errors.amount }}
                    </p>
                </div>

                <!-- Catatan / Alasan -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#333333]/80">
                        Catatan / Alasan (Opsional)
                    </label>
                    <input
                        v-model="stockForm.notes"
                        type="text"
                        placeholder="Contoh: Penerimaan barang dari PBF, Koreksi Opname..."
                        class="mt-1 min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 text-xs text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:outline-none focus:ring-1 focus:ring-[#000000]"
                    />
                </div>

                <!-- Live Preview Hasil Akhir -->
                <div class="rounded-[10px] border border-[#beedc0] bg-[#beedc0]/30 p-3 text-xs flex items-center justify-between">
                    <span class="font-medium text-[#065f46]">Estimasi Stok Setelah Penyesuaian:</span>
                    <span class="font-bold text-sm text-[#065f46]">
                        {{ previewResultStock }} {{ adjustingMedicine.unit }}
                    </span>
                </div>

                <!-- Tombol Submit & Batal -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#333333]/10">
                    <button
                        type="button"
                        @click="closeStockModal"
                        class="min-h-[44px] rounded-[40.5px] border border-[#333333]/20 bg-transparent px-5 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="stockForm.processing"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#333333] disabled:opacity-50"
                    >
                        <span v-if="stockForm.processing">Memproses...</span>
                        <span v-else>Konfirmasi Penyesuaian</span>
                    </button>
                </div>
            </form>
        </motion.div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         MODAL 3: Konfirmasi Hapus Obat (Custom Evergreen Notification)
         ═══════════════════════════════════════════════════════════════ -->
    <div
        v-if="isDeleteModalOpen && deletingMedicine"
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
                    <div class="flex size-10 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                        <Trash2 class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-['ivypresto-headline'] text-base font-bold text-[#000000]">
                            Konfirmasi Hapus Obat
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            Tindakan ini tidak dapat dibatalkan
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeDeleteModal"
                    class="rounded-full p-1.5 text-[#333333]/60 hover:bg-[#edede2] hover:text-[#000000]"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- Medicine Detail Card -->
            <div class="rounded-[10px] bg-[#edede2]/60 p-4 text-xs space-y-2.5 text-[#333333]">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-[#333333]/70">Nama Obat</span>
                        <div class="font-bold text-sm text-[#000000]">
                            {{ deletingMedicine.name_medicine }}
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-[#000000] px-2.5 py-0.5 font-mono text-[11px] font-bold text-[#ffffff]">
                        {{ deletingMedicine.code_medicine }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 border-t border-[#333333]/10 pt-2 text-[11px]">
                    <div>
                        <span class="text-[#333333]/70">Bentuk Sediaan:</span>
                        <div class="font-semibold text-[#000000]">
                            {{ deletingMedicine.type }} ({{ deletingMedicine.unit }})
                        </div>
                    </div>
                    <div>
                        <span class="text-[#333333]/70">Harga Satuan:</span>
                        <div class="font-semibold text-[#065f46]">
                            {{ formatRupiah(deletingMedicine.price) }}
                        </div>
                    </div>
                </div>

                <!-- Warning Alert jika stok > 0 -->
                <div
                    v-if="deletingMedicine.stock > 0"
                    class="rounded-[8px] border border-amber-300 bg-amber-50 p-2.5 text-[11px] text-amber-900 space-y-1"
                >
                    <div class="font-bold flex items-center gap-1.5">
                        <AlertTriangle class="size-3.5 text-amber-700" />
                        <span>Peringatan Stok Aktif: {{ deletingMedicine.stock }} {{ deletingMedicine.unit }}</span>
                    </div>
                    <p class="text-[10px] leading-relaxed text-amber-800">
                        Obat ini masih memiliki stok fisik tercatat di gudang farmasi. Menghapus data ini akan menghilangkannya dari master inventori.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="closeDeleteModal"
                    :disabled="isDeleting"
                    class="min-h-[44px] rounded-[40.5px] border border-[#333333]/20 bg-transparent px-5 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] disabled:opacity-50"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="confirmDeleteMedicine"
                    :disabled="isDeleting"
                    class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-rose-600 px-6 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-rose-700 disabled:opacity-50"
                >
                    <Loader2 v-if="isDeleting" class="size-4 animate-spin text-[#ffffff]" />
                    <Trash2 v-else class="size-4 text-[#ffffff]" />
                    <span>{{ isDeleting ? 'Menghapus...' : 'Hapus Obat Permanen' }}</span>
                </button>
            </div>
        </motion.div>
    </div>
</template>
