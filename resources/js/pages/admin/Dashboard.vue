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
import { Head, Link } from '@inertiajs/vue3';
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
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface FinancialData {
    today_revenue: number;
    week_revenue: number;
    month_revenue: number;
    revenue_by_method: Array<{
        method: string;
        label: string;
        total: number;
        count: number;
    }>;
    monthly_trend: Array<{
        month: string;
        revenue: number;
    }>;
}

interface MorbidityItem {
    diagnosis: string;
    case_count: number;
}

interface ClinicMatrixItem {
    poli_id: number;
    name_poli: string;
    kode_poli: string;
    location: string;
    doctor_name: string;
    room_name: string;
    waiting_count: number;
    is_active_today: boolean;
}

interface StaffStats {
    total_users: number;
    total_doctors: number;
    doctors_active: number;
    total_nurses: number;
    nurses_tetap: number;
    nurses_koas: number;
    total_patients: number;
    total_polis: number;
}

const props = defineProps<{
    financial: FinancialData;
    morbidity: {
        top_diagnoses: MorbidityItem[];
        today_consultations_count: number;
    };
    operational: {
        today_active_queues: number;
        today_completed_queues: number;
        doctors_on_duty_count: number;
        clinic_matrix: ClinicMatrixItem[];
    };
    staff_stats: StaffStats;
}>();

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

// Cari nilai tertinggi pendapatan bulanan untuk bar chart scale
const maxMonthlyRevenue = computed(() => {
    if (!props.financial.monthly_trend.length) {
        return 1;
    }

    return Math.max(...props.financial.monthly_trend.map((m) => m.revenue), 1);
});
</script>

<template>
    <AdminLayout
        title="Dashboard Eksekutif - Super Admin SIMRS"
        :breadcrumbs="[
            { title: 'Dashboard Eksekutif', href: '/admin/dashboard' },
        ]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header & Quick Actions (Mobile-first Stacking)
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.header
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between sm:p-7"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#065f46] px-3 py-1 text-xs font-bold text-[#ffffff]"
                        >
                            <ShieldCheck class="size-3.5" />
                            <span>Konsol Tata Kelola Eksekutif</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Tata Kelola & Agregasi SIMRS
                    </h1>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Monitoring real-time pendapatan kasir & gateway Xendit,
                        statistik morbiditas pasien, dan tata kelola fasilitas.
                    </p>
                </div>

                <div
                    class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center"
                >
                    <Link
                        href="/admin/users"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-[#065f46] px-5 py-2.5 text-xs font-bold text-[#ffffff] shadow-xs transition-colors hover:bg-[#054d38] sm:text-sm"
                    >
                        <Users class="size-4 text-[#beedc0]" />
                        <span>Manajemen Pengguna</span>
                    </Link>

                    <Link
                        href="/admin/polis"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-[#000000]/15 bg-[#fffff3] px-4 py-2.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#edede2] sm:text-sm"
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
                <h2 id="kpi-overview-heading" class="sr-only">
                    Ringkasan Indikator Kinerja Utama (KPI)
                </h2>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4"
                >
                    <!-- Pendapatan Hari Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        class="flex flex-col justify-between space-y-3 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                >Pendapatan Hari Ini</span
                            >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-[#beedc0] text-[#065f46]"
                            >
                                <DollarSign class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div
                                class="truncate font-mono text-2xl font-bold text-[#065f46]"
                            >
                                {{ formatRupiah(financial.today_revenue) }}
                            </div>
                            <div class="mt-1 text-[11px] text-[#000000]/70">
                                Terakumulasi dari tagihan kasir & QRIS hari ini
                            </div>
                        </div>
                    </motion.div>

                    <!-- Pendapatan Minggu Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.05 }"
                        class="flex flex-col justify-between space-y-3 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                >Pendapatan Minggu Ini</span
                            >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl border border-blue-200 bg-blue-100 text-blue-900"
                            >
                                <Calendar class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div
                                class="truncate font-mono text-2xl font-bold text-[#000000]"
                            >
                                {{ formatRupiah(financial.week_revenue) }}
                            </div>
                            <div class="mt-1 text-[11px] text-[#000000]/70">
                                Total transaksi 7 hari kalender berjalan
                            </div>
                        </div>
                    </motion.div>

                    <!-- Pendapatan Bulan Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1 }"
                        class="flex flex-col justify-between space-y-3 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                >Pendapatan Bulan Ini</span
                            >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl border border-purple-200 bg-purple-100 text-purple-900"
                            >
                                <TrendingUp class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div
                                class="truncate font-mono text-2xl font-bold text-purple-950"
                            >
                                {{ formatRupiah(financial.month_revenue) }}
                            </div>
                            <div class="mt-1 text-[11px] text-[#000000]/70">
                                Gross revenue kalender bulan ini
                            </div>
                        </div>
                    </motion.div>

                    <!-- Antrean Aktif Hari Ini -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.15 }"
                        class="flex flex-col justify-between space-y-3 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                >Beban Antrean Hari Ini</span
                            >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100 text-emerald-900"
                            >
                                <Activity class="size-4.5" />
                            </div>
                        </div>
                        <div>
                            <div
                                class="font-mono text-2xl font-bold text-[#065f46]"
                            >
                                {{ operational.today_active_queues }} Pasien
                            </div>
                            <div class="mt-1 text-[11px] text-[#000000]/70">
                                {{ operational.today_completed_queues }} pasien
                                telah selesai diperiksa
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
                <section
                    class="flex min-h-[300px] flex-col justify-between space-y-5 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sm:min-h-[340px] sm:p-7 lg:col-span-2"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="font-serif text-lg font-bold text-[#000000]"
                            >
                                Trend Pendapatan 6 Bulan Terakhir
                            </h2>
                            <p class="text-xs text-[#333333]">
                                Grafik agregasi performa finansial SIMRS
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 self-start rounded-full bg-[#beedc0] px-3 py-1 text-xs font-bold text-[#065f46] sm:self-auto"
                        >
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
                            <div
                                class="flex items-center justify-between text-xs font-medium"
                            >
                                <span class="text-[#000000]/80">{{
                                    trend.month
                                }}</span>
                                <span
                                    class="font-mono font-bold text-[#065f46]"
                                    >{{ formatRupiah(trend.revenue) }}</span
                                >
                            </div>
                            <div
                                class="h-3.5 w-full overflow-hidden rounded-full bg-[#edede2]"
                            >
                                <div
                                    class="h-full rounded-full bg-[#065f46] transition-all duration-500"
                                    :style="{
                                        width: `${Math.max((trend.revenue / maxMonthlyRevenue) * 100, 3)}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Breakdown Berdasarkan Metode Pembayaran -->
                <section
                    class="flex min-h-[300px] flex-col justify-between space-y-5 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sm:min-h-[340px] sm:p-7"
                >
                    <div class="space-y-1">
                        <h2 class="font-serif text-lg font-bold text-[#000000]">
                            Metode Pembayaran
                        </h2>
                        <p class="text-xs text-[#333333]">
                            Distribusi kas masuk Tunai vs Digital
                        </p>
                    </div>

                    <div
                        class="my-auto space-y-3"
                        v-if="financial.revenue_by_method.length > 0"
                    >
                        <div
                            v-for="item in financial.revenue_by_method"
                            :key="item.method"
                            class="flex items-center justify-between rounded-2xl border border-[#000000]/5 bg-[#edede2]/50 p-3.5"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-[#000000]/10 bg-[#fffff3] text-[#065f46]"
                                >
                                    <QrCode
                                        v-if="item.method === 'xendit_qris'"
                                        class="size-4"
                                    />
                                    <CreditCard
                                        v-else-if="item.method === 'edc'"
                                        class="size-4"
                                    />
                                    <Wallet v-else class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <div
                                        class="truncate text-xs font-bold text-[#000000]"
                                    >
                                        {{ item.label }}
                                    </div>
                                    <div class="text-[11px] text-[#000000]/70">
                                        {{ item.count }} transaksi
                                    </div>
                                </div>
                            </div>
                            <div
                                class="ml-2 shrink-0 text-right font-mono text-xs font-bold text-[#065f46]"
                            >
                                {{ formatRupiah(item.total) }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="py-6 text-center text-xs text-[#000000]/70"
                        v-else
                    >
                        Belum ada transaksi lunas tercatat.
                    </div>

                    <div
                        class="border-t border-[#000000]/10 pt-2 text-center text-xs text-[#000000]/70"
                    >
                        Integrasi Xendit Payment Gateway API v2
                    </div>
                </section>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. Laporan Morbiditas & Matriks Antrean Poliklinik
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top 10 Morbiditas Kasus (Diagnosis EMR) -->
                <section
                    class="space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sm:p-7"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <h2
                                class="font-serif text-lg font-bold text-[#000000]"
                            >
                                Laporan Morbiditas (Top 10 Diagnosis)
                            </h2>
                            <p class="text-xs text-[#333333]">
                                Frekuensi penyakit terbanyak dari resume medis
                                pasien
                            </p>
                        </div>
                        <HeartPulse class="size-5 shrink-0 text-rose-600" />
                    </div>

                    <div
                        class="space-y-2.5"
                        v-if="morbidity.top_diagnoses.length > 0"
                    >
                        <div
                            v-for="(diag, idx) in morbidity.top_diagnoses"
                            :key="diag.diagnosis"
                            class="flex items-center justify-between rounded-2xl border border-[#000000]/5 bg-[#edede2]/40 p-3 text-xs"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#065f46] font-mono text-[10px] font-bold text-[#ffffff]"
                                >
                                    {{ idx + 1 }}
                                </span>
                                <span
                                    class="truncate font-semibold text-[#000000]"
                                    >{{ diag.diagnosis }}</span
                                >
                            </div>
                            <span
                                class="ml-2 inline-flex shrink-0 items-center gap-1 rounded-full bg-[#beedc0] px-2.5 py-0.5 font-mono text-xs font-bold text-[#065f46]"
                            >
                                {{ diag.case_count }} kasus
                            </span>
                        </div>
                    </div>
                    <div
                        class="py-8 text-center text-xs text-[#000000]/70"
                        v-else
                    >
                        Belum ada data diagnosa rekam medis.
                    </div>
                </section>

                <!-- Matriks Poliklinik Hari Ini -->
                <section
                    class="flex flex-col justify-between space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sm:p-7"
                >
                    <div class="space-y-1">
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <h2
                                class="font-serif text-lg font-bold text-[#000000]"
                            >
                                Matriks Poliklinik & Antrean
                            </h2>
                            <span
                                class="self-start rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 font-mono text-xs font-bold text-blue-900 sm:self-auto"
                            >
                                {{ operational.doctors_on_duty_count }} Dokter
                                Praktik Hari Ini
                            </span>
                        </div>
                        <p class="text-xs text-[#333333]">
                            Status kehadiran dokter dan beban antrean ruang
                            periksa
                        </p>
                    </div>

                    <div class="my-auto space-y-2.5">
                        <div
                            v-for="clinic in operational.clinic_matrix"
                            :key="clinic.poli_id"
                            class="flex items-center justify-between rounded-2xl border p-3.5 text-xs transition-all"
                            :class="
                                clinic.is_active_today
                                    ? 'border-emerald-300 bg-[#fffff3] ring-1 ring-emerald-100'
                                    : 'border-[#000000]/5 bg-[#edede2]/40 opacity-75'
                            "
                        >
                            <div class="min-w-0 space-y-0.5 pr-2">
                                <div
                                    class="flex items-center gap-2 truncate font-bold text-[#000000]"
                                >
                                    <span>{{ clinic.name_poli }}</span>
                                    <span
                                        class="text-[11px] font-normal text-[#000000]/70"
                                        >({{ clinic.location }})</span
                                    >
                                </div>
                                <div
                                    class="truncate font-medium text-[#065f46]"
                                >
                                    {{ clinic.doctor_name }} •
                                    {{ clinic.room_name }}
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span
                                    v-if="clinic.is_active_today"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-900"
                                >
                                    <span
                                        class="size-1.5 animate-pulse rounded-full bg-emerald-600"
                                    ></span>
                                    <span
                                        >{{ clinic.waiting_count }} Pasien</span
                                    >
                                </span>
                                <span
                                    v-else
                                    class="text-xs font-medium text-[#000000]/65"
                                    >Libur</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-1 border-t border-[#000000]/10 pt-2 text-xs text-[#333333] sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span
                            >Total Antrean Aktif:
                            <strong class="text-[#000000]">{{
                                operational.today_active_queues
                            }}</strong></span
                        >
                        <span
                            >Selesai Dilayani:
                            <strong class="text-[#065f46]">{{
                                operational.today_completed_queues
                            }}</strong></span
                        >
                    </div>
                </section>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 5. Demografi SDM & Master Data Counts
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="sdm-demographics-heading">
                <h2 id="sdm-demographics-heading" class="sr-only">
                    Demografi SDM & Master Data Rumah Sakit
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <div
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs"
                    >
                        <div
                            class="truncate text-xs font-semibold text-[#333333] uppercase"
                        >
                            Dokter DPJP
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#065f46]"
                        >
                            {{ staff_stats.total_doctors }}
                        </div>
                        <div class="text-[11px] text-[#000000]/70">
                            {{ staff_stats.doctors_active }} status aktif
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs"
                    >
                        <div
                            class="truncate text-xs font-semibold text-[#333333] uppercase"
                        >
                            Perawat Tetap
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ staff_stats.nurses_tetap }}
                        </div>
                        <div class="text-[11px] text-[#000000]/70">
                            Pekerja & Kasir POS
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-amber-300 bg-amber-50 p-4 text-center shadow-xs"
                    >
                        <div
                            class="truncate text-xs font-semibold text-amber-900 uppercase"
                        >
                            Dokter Muda (Koas)
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-amber-900"
                        >
                            {{ staff_stats.nurses_koas }}
                        </div>
                        <div class="text-[11px] text-amber-900/80">
                            Intern Logbook Klinis
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-xs"
                    >
                        <div
                            class="truncate text-xs font-semibold text-[#333333] uppercase"
                        >
                            Pasien Terdaftar
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ staff_stats.total_patients }}
                        </div>
                        <div class="text-[11px] text-[#000000]/70">
                            Master NIK Pasien
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
