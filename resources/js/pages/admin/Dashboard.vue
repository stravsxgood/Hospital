<script setup lang="ts">
/**
 * @file Dashboard.vue (Super Admin Executive Governance & Financial Dashboard)
 * @description Pusat Tata Kelola Eksekutif, Agregasi Finansial POS/Xendit, Morbiditas Klinis, & Matriks Operasional.
 *              100% Responsif untuk Mobile (<640px), Tablet/iPad (640-1024px), dan Desktop (>1024px).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, Link } from '@inertiajs/vue3'
import {
    Activity,
    AlertCircle,
    ArrowUpRight,
    Award,
    Building2,
    Calendar,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Eye,
    FileText,
    GraduationCap,
    HeartPulse,
    Layers,
    PieChart,
    Plus,
    QrCode,
    Receipt,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    TrendingUp,
    Users,
    Wallet,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface FinancialData {
    today_revenue: number
    week_revenue: number
    month_revenue: number
    revenue_by_method: Array<{
        method: string
        label: string
        total: number
        count: number
    }>
    monthly_trend: Array<{
        month: string
        revenue: number
    }>
}

interface MorbidityItem {
    diagnosis: string
    case_count: number
}

interface ClinicMatrixItem {
    poli_id: number
    name_poli: string
    kode_poli: string
    location: string
    doctor_name: string
    room_name: string
    waiting_count: number
    is_active_today: boolean
}

interface StaffStats {
    total_users: number
    total_doctors: number
    doctors_active: number
    total_nurses: number
    nurses_tetap: number
    nurses_koas: number
    total_patients: number
    total_polis: number
}

const props = defineProps<{
    financial: FinancialData
    morbidity: {
        top_diagnoses: MorbidityItem[]
        today_consultations_count: number
    }
    operational: {
        today_active_queues: number
        today_completed_queues: number
        doctors_on_duty_count: number
        clinic_matrix: ClinicMatrixItem[]
    }
    staff_stats: StaffStats
}>()

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val)
}

// Cari nilai tertinggi pendapatan bulanan untuk bar chart scale
const maxMonthlyRevenue = computed(() => {
    if (!props.financial.monthly_trend.length) return 1
    return Math.max(...props.financial.monthly_trend.map((m) => m.revenue), 1)
})
</script>

<template>
    <AdminLayout
        title="Dashboard Eksekutif - Super Admin SIMRS"
        :breadcrumbs="[{ title: 'Dashboard Eksekutif', href: '/admin/dashboard' }]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header & Quick Actions (Mobile-first Stacking)
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.header
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#065f46] px-3 py-1 text-xs font-bold text-[#ffffff]">
                            <ShieldCheck class="size-3.5" />
                            <span>Konsol Tata Kelola Eksekutif</span>
                        </span>
                    </div>
                    <h1 class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl">
                        Tata Kelola & Agregasi SIMRS
                    </h1>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Monitoring real-time pendapatan kasir & gateway Xendit, statistik morbiditas pasien, dan tata kelola fasilitas.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <Link
                        href="/admin/users"
                        class="min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-[#065f46] px-5 py-2.5 text-xs sm:text-sm font-bold text-[#ffffff] shadow-xs hover:bg-[#054d38] transition-colors"
                    >
                        <Users class="size-4 text-[#beedc0]" />
                        <span>Manajemen Pengguna</span>
                    </Link>

                    <Link
                        href="/admin/polis"
                        class="min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl border border-[#000000]/15 bg-[#fffff3] px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#000000] hover:bg-[#edede2] transition-colors"
                    >
                        <Building2 class="size-4 text-[#065f46]" />
                        <span>Fasilitas & Jadwal</span>
                    </Link>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Executive KPI Cards Grid (sm:grid-cols-2 xl:grid-cols-4)
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="kpi-overview-heading">
                <h2 id="kpi-overview-heading" class="sr-only">Ringkasan Indikator Kinerja Utama (KPI)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
                    <!-- Pendapatan Hari Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs flex flex-col justify-between space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]">Pendapatan Hari Ini</span>
                            <div class="size-9 rounded-xl bg-[#beedc0] text-[#065f46] flex items-center justify-center">
                                <DollarSign class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold font-mono text-[#065f46] truncate">
                                {{ formatRupiah(financial.today_revenue) }}
                            </div>
                            <div class="text-[11px] text-[#000000]/70 mt-1">Terakumulasi dari tagihan kasir & QRIS hari ini</div>
                        </div>
                    </motion.div>

                    <!-- Pendapatan Minggu Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.05 }"
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs flex flex-col justify-between space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]">Pendapatan Minggu Ini</span>
                            <div class="size-9 rounded-xl bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center">
                                <Calendar class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold font-mono text-[#000000] truncate">
                                {{ formatRupiah(financial.week_revenue) }}
                            </div>
                            <div class="text-[11px] text-[#000000]/70 mt-1">Total transaksi 7 hari kalender berjalan</div>
                        </div>
                    </motion.div>

                    <!-- Pendapatan Bulan Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1 }"
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs flex flex-col justify-between space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]">Pendapatan Bulan Ini</span>
                            <div class="size-9 rounded-xl bg-purple-100 text-purple-900 border border-purple-200 flex items-center justify-center">
                                <TrendingUp class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold font-mono text-purple-950 truncate">
                                {{ formatRupiah(financial.month_revenue) }}
                            </div>
                            <div class="text-[11px] text-[#000000]/70 mt-1">Gross revenue kalender bulan ini</div>
                        </div>
                    </motion.div>

                    <!-- Antrean Aktif Hari Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.15 }"
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs flex flex-col justify-between space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]">Beban Antrean Hari Ini</span>
                            <div class="size-9 rounded-xl bg-emerald-100 text-emerald-900 border border-emerald-200 flex items-center justify-center">
                                <Activity class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold font-mono text-[#065f46]">
                                {{ operational.today_active_queues }} Pasien
                            </div>
                            <div class="text-[11px] text-[#000000]/70 mt-1">
                                {{ operational.today_completed_queues }} pasien telah selesai diperiksa
                            </div>
                        </div>
                    </motion.div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Visual Trend Finansial & Metode Pembayaran Split
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Trend Pendapatan Bulanan (Bar Visualizer) -->
                <section class="lg:col-span-2 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs space-y-5 flex flex-col justify-between min-h-[300px] sm:min-h-[340px]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="font-serif text-lg font-bold text-[#000000]">Trend Pendapatan 6 Bulan Terakhir</h2>
                            <p class="text-xs text-[#333333]">Grafik agregasi performa finansial SIMRS</p>
                        </div>
                        <span class="inline-flex self-start sm:self-auto items-center gap-1 rounded-full bg-[#beedc0] px-3 py-1 text-xs font-bold text-[#065f46]">
                            <TrendingUp class="size-3.5" />
                            <span>Gross POS & Xendit</span>
                        </span>
                    </div>

                    <div class="space-y-3.5 pt-2">
                        <div
                            v-for="trend in financial.monthly_trend"
                            :key="trend.month"
                            class="space-y-1.5"
                        >
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span class="text-[#000000]/80">{{ trend.month }}</span>
                                <span class="font-mono font-bold text-[#065f46]">{{ formatRupiah(trend.revenue) }}</span>
                            </div>
                            <div class="h-3.5 w-full rounded-full bg-[#edede2] overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-[#065f46] transition-all duration-500"
                                    :style="{ width: `${Math.max((trend.revenue / maxMonthlyRevenue) * 100, 3)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Breakdown Berdasarkan Metode Pembayaran -->
                <section class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs space-y-5 flex flex-col justify-between min-h-[300px] sm:min-h-[340px]">
                    <div class="space-y-1">
                        <h2 class="font-serif text-lg font-bold text-[#000000]">Metode Pembayaran</h2>
                        <p class="text-xs text-[#333333]">Distribusi kas masuk Tunai vs Digital</p>
                    </div>

                    <div class="space-y-3 my-auto" v-if="financial.revenue_by_method.length > 0">
                        <div
                            v-for="item in financial.revenue_by_method"
                            :key="item.method"
                            class="flex items-center justify-between p-3.5 rounded-2xl bg-[#edede2]/50 border border-[#000000]/5"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="size-9 shrink-0 rounded-xl bg-[#fffff3] border border-[#000000]/10 flex items-center justify-center text-[#065f46]">
                                    <QrCode v-if="item.method === 'xendit_qris'" class="size-4" />
                                    <CreditCard v-else-if="item.method === 'edc'" class="size-4" />
                                    <Wallet v-else class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-[#000000] truncate">{{ item.label }}</div>
                                    <div class="text-[11px] text-[#000000]/70">{{ item.count }} transaksi</div>
                                </div>
                            </div>
                            <div class="text-right font-mono font-bold text-xs text-[#065f46] shrink-0 ml-2">
                                {{ formatRupiah(item.total) }}
                            </div>
                        </div>
                    </div>
                    <div class="text-center py-6 text-xs text-[#000000]/70" v-else>
                        Belum ada transaksi lunas tercatat.
                    </div>

                    <div class="pt-2 text-center text-xs text-[#000000]/70 border-t border-[#000000]/10">
                        Integrasi Xendit Payment Gateway API v2
                    </div>
                </section>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. Laporan Morbiditas & Matriks Antrean Poliklinik
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top 10 Morbiditas Kasus (Diagnosis EMR) -->
                <section class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <h2 class="font-serif text-lg font-bold text-[#000000]">Laporan Morbiditas (Top 10 Diagnosis)</h2>
                            <p class="text-xs text-[#333333]">Frekuensi penyakit terbanyak dari resume medis pasien</p>
                        </div>
                        <HeartPulse class="size-5 text-rose-600 shrink-0" />
                    </div>

                    <div class="space-y-2.5" v-if="morbidity.top_diagnoses.length > 0">
                        <div
                            v-for="(diag, idx) in morbidity.top_diagnoses"
                            :key="diag.diagnosis"
                            class="flex items-center justify-between p-3 rounded-2xl bg-[#edede2]/40 border border-[#000000]/5 text-xs"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="size-6 shrink-0 rounded-full bg-[#065f46] text-[#ffffff] font-mono font-bold flex items-center justify-center text-[10px]">
                                    {{ idx + 1 }}
                                </span>
                                <span class="font-semibold text-[#000000] truncate">{{ diag.diagnosis }}</span>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1 font-mono font-bold text-xs bg-[#beedc0] text-[#065f46] px-2.5 py-0.5 rounded-full ml-2">
                                {{ diag.case_count }} kasus
                            </span>
                        </div>
                    </div>
                    <div class="text-center py-8 text-xs text-[#000000]/70" v-else>
                        Belum ada data diagnosa rekam medis.
                    </div>
                </section>

                <!-- Matriks Poliklinik Hari Ini -->
                <section class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs space-y-4 flex flex-col justify-between">
                    <div class="space-y-1">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <h2 class="font-serif text-lg font-bold text-[#000000]">Matriks Poliklinik & Antrean</h2>
                            <span class="self-start sm:self-auto text-xs font-mono font-bold bg-blue-100 text-blue-900 border border-blue-200 px-2.5 py-0.5 rounded-full">
                                {{ operational.doctors_on_duty_count }} Dokter Praktik Hari Ini
                            </span>
                        </div>
                        <p class="text-xs text-[#333333]">Status kehadiran dokter dan beban antrean ruang periksa</p>
                    </div>

                    <div class="space-y-2.5 my-auto">
                        <div
                            v-for="clinic in operational.clinic_matrix"
                            :key="clinic.poli_id"
                            class="flex items-center justify-between p-3.5 rounded-2xl border transition-all text-xs"
                            :class="clinic.is_active_today ? 'bg-[#fffff3] border-emerald-300 ring-1 ring-emerald-100' : 'bg-[#edede2]/40 border-[#000000]/5 opacity-75'"
                        >
                            <div class="space-y-0.5 min-w-0 pr-2">
                                <div class="font-bold text-[#000000] flex items-center gap-2 truncate">
                                    <span>{{ clinic.name_poli }}</span>
                                    <span class="text-[11px] text-[#000000]/70 font-normal">({{ clinic.location }})</span>
                                </div>
                                <div class="text-[#065f46] font-medium truncate">{{ clinic.doctor_name }} • {{ clinic.room_name }}</div>
                            </div>

                            <div class="text-right shrink-0">
                                <span
                                    v-if="clinic.is_active_today"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 border border-emerald-300 px-2.5 py-1 text-xs font-bold text-emerald-900"
                                >
                                    <span class="size-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                    <span>{{ clinic.waiting_count }} Pasien</span>
                                </span>
                                <span v-else class="text-xs font-medium text-[#000000]/65">Libur</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs text-[#333333] border-t border-[#000000]/10">
                        <span>Total Antrean Aktif: <strong class="text-[#000000]">{{ operational.today_active_queues }}</strong></span>
                        <span>Selesai Dilayani: <strong class="text-[#065f46]">{{ operational.today_completed_queues }}</strong></span>
                    </div>
                </section>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 5. Demografi SDM & Master Data Counts
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="sdm-demographics-heading">
                <h2 id="sdm-demographics-heading" class="sr-only">Demografi SDM & Master Data Rumah Sakit</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs">
                        <div class="text-xs font-semibold text-[#333333] uppercase truncate">Dokter DPJP</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-[#065f46]">{{ staff_stats.total_doctors }}</div>
                        <div class="text-[11px] text-[#000000]/70">{{ staff_stats.doctors_active }} status aktif</div>
                    </div>

                    <div class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs">
                        <div class="text-xs font-semibold text-[#333333] uppercase truncate">Perawat Tetap</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-[#000000]">{{ staff_stats.nurses_tetap }}</div>
                        <div class="text-[11px] text-[#000000]/70">Pekerja & Kasir POS</div>
                    </div>

                    <div class="rounded-3xl border border-amber-300 bg-amber-50 p-4 text-center shadow-xs">
                        <div class="text-xs font-semibold text-amber-900 uppercase truncate">Dokter Muda (Koas)</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-amber-900">{{ staff_stats.nurses_koas }}</div>
                        <div class="text-[11px] text-amber-900/80">Intern Logbook Klinis</div>
                    </div>

                    <div class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs">
                        <div class="text-xs font-semibold text-[#333333] uppercase truncate">Pasien Terdaftar</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-[#000000]">{{ staff_stats.total_patients }}</div>
                        <div class="text-[11px] text-[#000000]/70">Master NIK Pasien</div>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
