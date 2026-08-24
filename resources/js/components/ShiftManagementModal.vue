<script setup lang="ts">
/**
 * ShiftManagementModal.vue
 *
 * Komponen dialog operasional Kasir Rumah Sakit:
 * 1. Buka Sesi Shift Kasir Baru (Pagi / Siang / Malam) dengan Modal Kas Awal
 * 2. Monitoring Penerimaan Kas & Non-Tunai Real-time
 * 3. Tutup Shift & Rekonsiliasi Otomatis (Uang Fisik vs Sistem = Selisih Kas)
 * 4. Cetak Rekapitulasi Shift Kasir
 *
 * Mengikuti DESIGN.md (Evergreen Theme) & GEMINI.md:
 * - Minimum touch target 44px
 * - Tipografi ivypresto-headline & Rubik
 * - Micro-animasi via motion-v
 */
import {
    AlertCircle,
    AlertTriangle,
    Calculator,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    DollarSign,
    Lock,
    Printer,
    Receipt,
    RefreshCw,
    Sun,
    Moon,
    Sunrise,
    Unlock,
    User,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import { computed, onMounted, ref, watch } from 'vue';

interface ActiveShiftData {
    cashier_shift_id: number;
    shift_name: string;
    opened_at: string;
    opening_cash: number | string;
    status: 'open' | 'closed';
    notes?: string | null;
}

interface LiveStats {
    total_cash: number;
    total_qris: number;
    total_revenue: number;
    expected_cash: number;
    transaction_count: number;
}

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'shift-changed'): void;
}>();

// State
const isLoading = ref(false);
const isSubmitting = ref(false);
const hasActiveShift = ref(false);
const activeShift = ref<ActiveShiftData | null>(null);
const liveStats = ref<LiveStats | null>(null);
const errorMessage = ref<string | null>(null);
const successMessage = ref<string | null>(null);

// Form Buka Shift
const openForm = ref({
    shift_name: 'Pagi',
    opening_cash: 500000,
    notes: '',
});

// Form Tutup Shift
const closeForm = ref({
    closing_cash_actual: 0,
    notes: '',
});

const shiftOptions = [
    { label: 'Shift Pagi (07:00 - 14:00)', value: 'Pagi', icon: Sunrise },
    { label: 'Shift Siang (14:00 - 21:00)', value: 'Siang', icon: Sun },
    { label: 'Shift Malam (21:00 - 07:00)', value: 'Malam', icon: Moon },
];

// Perhitungan Selisih Kas Real-Time (Discrepancy)
const calculatedDiscrepancy = computed(() => {
    if (!liveStats.value) {
        return 0;
    }

    const actual = Number(closeForm.value.closing_cash_actual || 0);
    const expected = Number(liveStats.value.expected_cash || 0);

    return actual - expected;
});

const discrepancyStatus = computed(() => {
    const diff = calculatedDiscrepancy.value;

    if (diff === 0) {
        return {
            label: 'Kas Sesuai / Balance',
            color: 'bg-emerald-100 text-emerald-900 border-emerald-300',
            badge: 'Rp 0 (Cocok)',
        };
    }

    if (diff > 0) {
        return {
            label: 'Kelebihan Kas Fisik',
            color: 'bg-amber-100 text-amber-900 border-amber-300',
            badge: `+ Rp ${diff.toLocaleString('id-ID')}`,
        };
    }

    return {
        label: 'Kekurangan Kas Fisik (Selisih Negatif)',
        color: 'bg-rose-100 text-rose-900 border-rose-300',
        badge: `- Rp ${Math.abs(diff).toLocaleString('id-ID')}`,
    };
});

// Fetch Shift Status & Live Stats dari Backend
const fetchCurrentShift = async () => {
    isLoading.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.get('/staff/cashier-shifts/current');

        if (response.data?.status) {
            hasActiveShift.value = response.data.has_shift;

            if (response.data.has_shift && response.data.data) {
                activeShift.value = response.data.data.shift;
                liveStats.value = response.data.data.live_stats;
                // Set default input kas fisik sama dengan expected
                closeForm.value.closing_cash_actual =
                    response.data.data.live_stats.expected_cash;
            } else {
                activeShift.value = null;
                liveStats.value = null;
            }
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal memuat status shift kasir.';
    } finally {
        isLoading.value = false;
    }
};

// Buka Shift Kasir Baru
const submitOpenShift = async () => {
    isSubmitting.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
        const response = await axios.post(
            '/staff/cashier-shifts/open',
            openForm.value,
        );

        if (response.data?.status) {
            successMessage.value = response.data.message;
            emit('shift-changed');
            await fetchCurrentShift();
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal membuka sesi shift kasir.';
    } finally {
        isSubmitting.value = false;
    }
};

// Tutup Shift Kasir & Rekonsiliasi
const submitCloseShift = async () => {
    isSubmitting.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
        const response = await axios.post('/staff/cashier-shifts/close', {
            closing_cash_actual: closeForm.value.closing_cash_actual,
            notes: closeForm.value.notes,
        });

        if (response.data?.status) {
            successMessage.value = response.data.message;
            emit('shift-changed');
            setTimeout(() => {
                emit('update:open', false);
            }, 1200);
        }
    } catch (err: any) {
        errorMessage.value =
            err.response?.data?.message || 'Gagal menutup shift kasir.';
    } finally {
        isSubmitting.value = false;
    }
};

const formatCurrency = (val: number | string): string => {
    return Number(val || 0).toLocaleString('id-ID');
};

const formatDate = (dateStr?: string | null): string => {
    if (!dateStr) {
        return '-';
    }

    const d = new Date(dateStr);

    return d.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            fetchCurrentShift();
            successMessage.value = null;
            errorMessage.value = null;
        }
    },
);

onMounted(() => {
    if (props.open) {
        fetchCurrentShift();
    }
});
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#000000]/60 p-3 font-['Rubik'] backdrop-blur-xs sm:p-4"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95, y: 15 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.2, ease: 'easeOut' }"
            class="my-auto flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[12px] border border-[#333333]/20 bg-[#fffff3] text-[#000000] shadow-2xl"
        >
            <!-- Header Modal -->
            <header
                class="flex shrink-0 items-center justify-between gap-3 border-b border-[#333333]/15 bg-[#edede2] px-5 py-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#333333]/15 bg-[#beedc0]"
                    >
                        <Calculator class="size-5 text-[#000000]" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3
                                class="font-['ivypresto-headline'] text-xl leading-tight font-bold text-[#000000]"
                            >
                                Sesi & Rekonsiliasi Shift Kasir
                            </h3>
                            <span
                                class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-bold"
                                :class="
                                    hasActiveShift
                                        ? 'border-emerald-300 bg-emerald-100 text-emerald-800'
                                        : 'border-neutral-300 bg-neutral-100 text-neutral-600'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        hasActiveShift
                                            ? 'animate-pulse bg-emerald-600'
                                            : 'bg-neutral-400'
                                    "
                                ></span>
                                {{
                                    hasActiveShift
                                        ? `Shift ${activeShift?.shift_name} Aktif`
                                        : 'Shift Belum Dibuka'
                                }}
                            </span>
                        </div>
                        <p class="text-xs text-[#333333]/70">
                            Pengelolaan kas awal, rekapitulasi penerimaan, dan
                            penutupan shift kasir
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('update:open', false)"
                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border border-[#333333]/20 bg-[#ffffff] text-[#333333] transition-colors hover:bg-rose-50 hover:text-rose-600"
                >
                    <X class="size-4" />
                </button>
            </header>

            <!-- Loading State -->
            <div v-if="isLoading" class="space-y-3 py-16 text-center">
                <RefreshCw class="mx-auto size-8 animate-spin text-[#000000]" />
                <p class="text-xs font-medium text-[#333333]">
                    Memeriksa data shift kasir aktif...
                </p>
            </div>

            <!-- Body Modal -->
            <div v-else class="flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">
                <!-- Alert Feedback -->
                <div
                    v-if="errorMessage"
                    class="flex items-center gap-2 rounded-[8px] border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700"
                >
                    <AlertCircle class="size-4 shrink-0" />
                    <span>{{ errorMessage }}</span>
                </div>
                <div
                    v-if="successMessage"
                    class="flex items-center gap-2 rounded-[8px] border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800"
                >
                    <CheckCircle2 class="size-4 shrink-0 text-emerald-600" />
                    <span class="font-semibold">{{ successMessage }}</span>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     MODE 1: BUKA SHIFT KASIR BARU
                     ═══════════════════════════════════════════════════════════ -->
                <div v-if="!hasActiveShift" class="space-y-4">
                    <div
                        class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-[#333333]/10 pb-3"
                        >
                            <Unlock class="size-4 text-[#000000]" />
                            <h4 class="text-sm font-bold text-[#000000]">
                                Form Pembukaan Shift Kasir
                            </h4>
                        </div>

                        <div class="space-y-4 text-xs">
                            <!-- Pilihan Shift -->
                            <div class="space-y-1.5">
                                <label class="font-bold text-[#000000]"
                                    >Pilih Jadwal Shift
                                    <span class="text-rose-600">*</span></label
                                >
                                <div
                                    class="grid grid-cols-1 gap-2.5 sm:grid-cols-3"
                                >
                                    <button
                                        v-for="opt in shiftOptions"
                                        :key="opt.value"
                                        type="button"
                                        @click="openForm.shift_name = opt.value"
                                        class="flex min-h-[44px] cursor-pointer items-center gap-2.5 rounded-[8px] border p-3 text-left transition-all"
                                        :class="
                                            openForm.shift_name === opt.value
                                                ? 'border-[#000000] bg-[#000000] font-semibold text-white shadow-sm'
                                                : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:border-[#333333]/40'
                                        "
                                    >
                                        <component
                                            :is="opt.icon"
                                            class="size-4 shrink-0"
                                            :class="
                                                openForm.shift_name ===
                                                opt.value
                                                    ? 'text-[#beedc0]'
                                                    : 'text-[#333333]'
                                            "
                                        />
                                        <span>{{ opt.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Kas Modal Awal (Cash Float) -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-[#000000]"
                                        >Kas Modal Awal di Laci Kasir (Rp)
                                        <span class="text-rose-600"
                                            >*</span
                                        ></label
                                    >
                                    <span class="text-[11px] text-[#333333]/60"
                                        >Uang kembalian awal</span
                                    >
                                </div>
                                <div class="relative">
                                    <span
                                        class="absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-bold text-[#333333]"
                                        >Rp</span
                                    >
                                    <input
                                        v-model.number="openForm.opening_cash"
                                        type="number"
                                        step="10000"
                                        min="0"
                                        placeholder="500000"
                                        class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] py-2 pr-3 pl-10 font-mono text-sm font-bold text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                    />
                                </div>
                            </div>

                            <!-- Catatan Pembukaan -->
                            <div class="space-y-1.5">
                                <label class="font-bold text-[#000000]"
                                    >Catatan Khusus (Opsional)</label
                                >
                                <input
                                    v-model="openForm.notes"
                                    type="text"
                                    placeholder="Contoh: Pecahan uang 10rb dan 5rb lengkap..."
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     MODE 2: MONITORING & TUTUP SHIFT KASIR (REKONSILIASI)
                     ═══════════════════════════════════════════════════════════ -->
                <div v-else class="space-y-4">
                    <!-- Ringkasan Live Penerimaan Shift -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div
                            class="space-y-1 rounded-[8px] border border-[#333333]/15 bg-[#ffffff] p-3"
                        >
                            <span class="block text-[11px] text-[#333333]/70"
                                >Kas Awal Float</span
                            >
                            <span
                                class="block font-mono text-sm font-bold text-[#000000] sm:text-base"
                            >
                                Rp
                                {{
                                    formatCurrency(
                                        activeShift?.opening_cash || 0,
                                    )
                                }}
                            </span>
                        </div>

                        <div
                            class="space-y-1 rounded-[8px] border border-[#333333]/15 bg-[#ffffff] p-3"
                        >
                            <span class="block text-[11px] text-[#333333]/70"
                                >Tunai Masuk</span
                            >
                            <span
                                class="block font-mono text-sm font-bold text-emerald-700 sm:text-base"
                            >
                                + Rp
                                {{ formatCurrency(liveStats?.total_cash || 0) }}
                            </span>
                        </div>

                        <div
                            class="space-y-1 rounded-[8px] border border-[#333333]/15 bg-[#ffffff] p-3"
                        >
                            <span class="block text-[11px] text-[#333333]/70"
                                >QRIS / Non-Tunai</span
                            >
                            <span
                                class="block font-mono text-sm font-bold text-blue-700 sm:text-base"
                            >
                                Rp
                                {{ formatCurrency(liveStats?.total_qris || 0) }}
                            </span>
                        </div>

                        <div
                            class="space-y-1 rounded-[8px] border border-[#333333]/15 bg-[#beedc0]/40 p-3"
                        >
                            <span
                                class="block text-[11px] font-bold text-[#000000]"
                                >Total Omset Shift</span
                            >
                            <span
                                class="block font-mono text-sm font-bold text-[#000000] sm:text-base"
                            >
                                Rp
                                {{
                                    formatCurrency(
                                        liveStats?.total_revenue || 0,
                                    )
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Kotak Target Kas di Laci vs Kas Fisik Nyata -->
                    <div
                        class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-[#333333]/10 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <Lock class="size-4 text-[#000000]" />
                                <h4 class="text-sm font-bold text-[#000000]">
                                    Rekonsiliasi Kas Akhir Shift
                                </h4>
                            </div>
                            <span class="text-xs text-[#333333]/70"
                                >Dibuka sejak:
                                {{ formatDate(activeShift?.opened_at) }}</span
                            >
                        </div>

                        <div
                            class="grid grid-cols-1 gap-4 text-xs md:grid-cols-2"
                        >
                            <!-- Sistem: Kas Fisik yang Seharusnya Ada -->
                            <div
                                class="space-y-2 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-3.5"
                            >
                                <span
                                    class="block text-xs font-bold text-[#000000]"
                                    >Target Kas Sistem di Laci:</span
                                >
                                <div
                                    class="font-mono text-xl font-bold text-[#000000]"
                                >
                                    Rp
                                    {{
                                        formatCurrency(
                                            liveStats?.expected_cash || 0,
                                        )
                                    }}
                                </div>
                                <p class="text-[11px] text-[#333333]/70">
                                    Formula: Kas Awal (Rp
                                    {{
                                        formatCurrency(
                                            activeShift?.opening_cash || 0,
                                        )
                                    }}) + Tunai Masuk (Rp
                                    {{
                                        formatCurrency(
                                            liveStats?.total_cash || 0,
                                        )
                                    }})
                                </p>
                            </div>

                            <!-- Input Kas Fisik Hasil Hitungan Tangan Kasir -->
                            <div class="space-y-2">
                                <label class="block font-bold text-[#000000]">
                                    Hasil Hitung Kas Fisik Nyata (Rp)
                                    <span class="text-rose-600">*</span>
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-bold text-[#333333]"
                                        >Rp</span
                                    >
                                    <input
                                        v-model.number="
                                            closeForm.closing_cash_actual
                                        "
                                        type="number"
                                        min="0"
                                        class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] py-2 pr-3 pl-10 font-mono text-base font-bold text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                    />
                                </div>
                                <span
                                    class="block text-[11px] text-[#333333]/60"
                                    >Hitung seluruh lembaran uang di laci kasir
                                    saat ini.</span
                                >
                            </div>
                        </div>

                        <!-- Status Selisih Kas (Discrepancy) -->
                        <div
                            class="flex flex-col justify-between gap-2 rounded-[8px] border p-3.5 sm:flex-row sm:items-center"
                            :class="discrepancyStatus.color"
                        >
                            <div class="flex items-center gap-2">
                                <AlertTriangle
                                    v-if="calculatedDiscrepancy !== 0"
                                    class="size-4 shrink-0"
                                />
                                <CheckCircle2
                                    v-else
                                    class="size-4 shrink-0 text-emerald-600"
                                />
                                <div>
                                    <span class="block text-xs font-bold">{{
                                        discrepancyStatus.label
                                    }}</span>
                                    <span class="text-[11px]"
                                        >Selisih Fisik - Sistem</span
                                    >
                                </div>
                            </div>
                            <span class="font-mono text-base font-bold">{{
                                discrepancyStatus.badge
                            }}</span>
                        </div>

                        <!-- Catatan Penutupan -->
                        <div class="space-y-1 text-xs">
                            <label class="font-bold text-[#000000]"
                                >Catatan Penutupan Shift / Berita Acara
                                Selisih</label
                            >
                            <textarea
                                v-model="closeForm.notes"
                                rows="2"
                                placeholder="Tuliskan keterangan jika terdapat selisih atau catatan serah terima ke petugas shift berikutnya..."
                                class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-2.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal Actions -->
            <footer
                class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-[#333333]/15 bg-[#edede2] px-5 py-3.5"
            >
                <button
                    type="button"
                    @click="fetchCurrentShift"
                    class="inline-flex min-h-[40px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-3.5 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                >
                    <RefreshCw
                        class="size-3.5"
                        :class="{ 'animate-spin': isLoading }"
                    />
                    <span>Perbarui Data</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="min-h-[44px] cursor-pointer rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                    >
                        Tutup
                    </button>

                    <!-- Tombol Buka Shift Baru -->
                    <motion.button
                        v-if="!hasActiveShift"
                        type="button"
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                        @click="submitOpenShift"
                        :disabled="isSubmitting || openForm.opening_cash < 0"
                        class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 text-xs font-bold text-white shadow-md hover:bg-[#222222] disabled:opacity-50"
                    >
                        <RefreshCw
                            v-if="isSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <Unlock v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            isSubmitting
                                ? 'Membuka Shift...'
                                : `Buka Sesi Shift ${openForm.shift_name}`
                        }}</span>
                    </motion.button>

                    <!-- Tombol Tutup Shift & Rekonsiliasi -->
                    <motion.button
                        v-else
                        type="button"
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                        @click="submitCloseShift"
                        :disabled="isSubmitting"
                        class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 text-xs font-bold text-white shadow-md hover:bg-[#222222] disabled:opacity-50"
                    >
                        <RefreshCw
                            v-if="isSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <Lock v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            isSubmitting
                                ? 'Merekonsiliasi...'
                                : 'Tutup Shift & Rekonsiliasi'
                        }}</span>
                    </motion.button>
                </div>
            </footer>
        </motion.div>
    </div>
</template>
