<script setup lang="ts">
/**
 * @file Dashboard.vue (Super Admin Executive Governance & Financial Dashboard)
 * @description Pusat Tata Kelola Eksekutif, Agregasi Finansial POS/Xendit, Morbiditas Klinis,
 *              Matriks Operasional, serta Manajemen Playlist Video Layar Display TV Antrean.
 *              100% Responsif untuk Mobile (<640px), Tablet/iPad (640-1024px), dan Desktop (>1024px).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    ExternalLink,
    Eye,
    FileText,
    FileVideo,
    Film,
    GraduationCap,
    HeartPulse,
    Layers,
    ListOrdered,
    Loader2,
    PieChart,
    Play,
    Plus,
    Power,
    QrCode,
    Receipt,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    Trash2,
    TrendingUp,
    Tv,
    UploadCloud,
    Users,
    Wallet,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';
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
    total_inactive?: number;
    users_never_logged_in?: number;
    users_last_7_days?: number;
}

interface DisplayVideoItem {
    id: number;
    title: string;
    file_path: string;
    order: number;
    is_active: boolean;
    youtube_id?: string | null;
    embed_url?: string;
    video_url: string;
    thumbnail_url?: string;
    file_size_formatted: string;
    source_type?: string;
    is_youtube?: boolean;
    created_at?: string;
}

const props = withDefaults(
    defineProps<{
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
        display_videos?: DisplayVideoItem[];
    }>(),
    {
        display_videos: () => [],
    },
);

const activeTab = ref<'overview' | 'display_videos'>('overview');

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

// ═══════════════════════════════════════════════════════════════
// Video Upload & Playlist Management (Dual Method: YouTube & File 800MB)
// ═══════════════════════════════════════════════════════════════
const videoUploadForm = useForm({
    source_type: 'youtube' as 'youtube' | 'file',
    title: '',
    youtube_url: '',
    video_file: null as File | null,
    order: (props.display_videos?.length || 0) + 1,
    is_active: true,
});

const isTogglingVideoId = ref<number | null>(null);
const deleteTargetVideo = ref<DisplayVideoItem | null>(null);
const isDeletingVideo = ref(false);

const localVideoFile = ref<File | null>(null);
const localVideoPreviewUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const isDraggingFile = ref(false);
const fileSizeError = ref<string | null>(null);

const extractedYoutubeId = computed(() => {
    const val = videoUploadForm.youtube_url.trim();

    if (!val) {
        return null;
    }

    const iframeMatch = val.match(/src=["']([^"']+)["']/i);
    const target = iframeMatch ? iframeMatch[1] : val;

    const match = target.match(
        /(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i,
    );

    if (match) {
        return match[1];
    }

    if (/^[a-zA-Z0-9_-]{11}$/.test(target)) {
        return target;
    }

    return null;
});

const livePreviewEmbedUrl = computed(() => {
    if (extractedYoutubeId.value) {
        return `https://www.youtube.com/embed/${extractedYoutubeId.value}`;
    }

    return null;
});

const formatBytes = (bytes: number): string => {
    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    return (bytes / 1024).toFixed(1) + ' KB';
};

const handleFileSelected = (file: File | null) => {
    fileSizeError.value = null;

    if (!file) {
        localVideoFile.value = null;
        videoUploadForm.video_file = null;

        if (localVideoPreviewUrl.value) {
            URL.revokeObjectURL(localVideoPreviewUrl.value);
            localVideoPreviewUrl.value = null;
        }

        return;
    }

    const maxSize = 800 * 1024 * 1024; // 800 MB

    if (file.size > maxSize) {
        fileSizeError.value = `Ukuran file (${(file.size / (1024 * 1024)).toFixed(1)} MB) melebihi batas maksimal 800 MB.`;

        return;
    }

    localVideoFile.value = file;
    videoUploadForm.video_file = file;

    // Auto-fill judul jika masih kosong
    if (!videoUploadForm.title) {
        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, '');
        videoUploadForm.title = nameWithoutExt.replace(/[-_]/g, ' ');
    }

    if (localVideoPreviewUrl.value) {
        URL.revokeObjectURL(localVideoPreviewUrl.value);
    }

    localVideoPreviewUrl.value = URL.createObjectURL(file);
};

const onFileInputChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        handleFileSelected(target.files[0]);
    }
};

const onFileDrop = (event: DragEvent) => {
    isDraggingFile.value = false;

    if (event.dataTransfer && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];

        if (
            file.type.startsWith('video/') ||
            /\.(mp4|webm|ogg|mov|m4v)$/i.test(file.name)
        ) {
            handleFileSelected(file);
        } else {
            fileSizeError.value =
                'Format file tidak didukung. Harap pilih file video (MP4, WebM, OGG, MOV, M4V).';
        }
    }
};

const removeSelectedFile = () => {
    handleFileSelected(null);

    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const isSubmitDisabled = computed(() => {
    if (videoUploadForm.processing) {
        return true;
    }

    if (!videoUploadForm.title.trim()) {
        return true;
    }

    if (videoUploadForm.source_type === 'youtube') {
        return !videoUploadForm.youtube_url.trim() || !extractedYoutubeId.value;
    } else {
        return !localVideoFile.value || !!fileSizeError.value;
    }
});

const handleUploadVideo = () => {
    videoUploadForm.post('/admin/display-videos', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            videoUploadForm.reset();
            removeSelectedFile();
            videoUploadForm.order = (props.display_videos?.length || 0) + 1;
        },
    });
};

const handleToggleVideoStatus = (video: DisplayVideoItem) => {
    isTogglingVideoId.value = video.id;
    router.patch(
        `/admin/display-videos/${video.id}/toggle`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isTogglingVideoId.value = null;
            },
        },
    );
};

const openDeleteVideoModal = (video: DisplayVideoItem) => {
    deleteTargetVideo.value = video;
};

const confirmDeleteVideo = () => {
    if (!deleteTargetVideo.value) {
        return;
    }

    isDeletingVideo.value = true;
    router.delete(`/admin/display-videos/${deleteTargetVideo.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeletingVideo.value = false;
            deleteTargetVideo.value = null;
        },
    });
};
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
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:flex-row sm:items-center sm:justify-between sm:p-7"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3.5 py-1 text-xs font-bold text-[#000000]"
                        >
                            <ShieldCheck class="size-3.5 text-[#000000]" />
                            <span>Konsol Tata Kelola Eksekutif</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Tata Kelola & Agregasi SIMRS
                    </h1>
                    <p class="font-['Rubik'] text-xs text-[#333333] sm:text-sm">
                        Monitoring real-time pendapatan kasir & gateway Xendit,
                        morbiditas pasien, dan manajemen video playlist layar
                        monitor antrean TV.
                    </p>
                </div>

                <div
                    class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center"
                >
                    <a
                        href="/display"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] border border-[#000000]/15 bg-[#edede2] px-5 py-2.5 font-['Rubik'] text-xs font-semibold text-[#000000] transition-colors hover:bg-[#beedc0] sm:text-sm"
                        title="Buka Layar Monitor Antrean TV di Tab Baru"
                    >
                        <ExternalLink class="size-4 text-[#000000]" />
                        <span>Buka Layar TV (/display)</span>
                    </a>

                    <Link
                        href="/admin/users"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 font-['Rubik'] text-xs font-medium text-[#ffffff] shadow-none transition-colors hover:bg-[#1a1a1a] sm:text-sm"
                    >
                        <Users class="size-4 text-[#beedc0]" />
                        <span>Manajemen Pengguna</span>
                    </Link>

                    <Link
                        href="/admin/polis"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] px-5 py-2.5 font-['Rubik'] text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:text-sm"
                    >
                        <Building2 class="size-4 text-[#000000]" />
                        <span>Fasilitas & Jadwal</span>
                    </Link>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Tab Switcher (Overview vs Display & Video Settings)
                 ═══════════════════════════════════════════════════════════════ -->
            <div
                class="flex flex-wrap items-center gap-2.5 border-b border-[#000000]/10 pb-3"
            >
                <button
                    type="button"
                    @click="activeTab = 'overview'"
                    class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] px-5 py-2 font-['Rubik'] text-xs font-semibold transition-all sm:text-sm"
                    :class="
                        activeTab === 'overview'
                            ? 'bg-[#000000] text-[#ffffff] shadow-xs'
                            : 'border border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]'
                    "
                >
                    <Activity
                        class="size-4"
                        :class="
                            activeTab === 'overview'
                                ? 'text-[#beedc0]'
                                : 'text-[#000000]'
                        "
                    />
                    <span>Ikhtisar Eksekutif & Finansial</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'display_videos'"
                    class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] px-5 py-2 font-['Rubik'] text-xs font-semibold transition-all sm:text-sm"
                    :class="
                        activeTab === 'display_videos'
                            ? 'bg-[#000000] text-[#ffffff] shadow-xs'
                            : 'border border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]'
                    "
                >
                    <Tv
                        class="size-4"
                        :class="
                            activeTab === 'display_videos'
                                ? 'text-[#beedc0]'
                                : 'text-[#000000]'
                        "
                    />
                    <span>Display & Video Settings</span>
                    <span
                        v-if="display_videos.length > 0"
                        class="ml-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                        :class="
                            activeTab === 'display_videos'
                                ? 'bg-[#beedc0] text-[#000000]'
                                : 'bg-[#000000]/10 text-[#000000]'
                        "
                    >
                        {{ display_videos.length }}
                    </span>
                </button>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 TAB 1: IKHTISAR EKSEKUTIF & FINANSIAL
                 ═══════════════════════════════════════════════════════════════ -->
            <div v-if="activeTab === 'overview'" class="space-y-6">
                <!-- 2. Executive KPI Cards Grid (sm:grid-cols-2 xl:grid-cols-4) -->
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
                            class="flex flex-col justify-between space-y-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                    >Pendapatan Hari Ini</span
                                >
                                <div
                                    class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                >
                                    <DollarSign class="size-4.5" />
                                </div>
                            </div>
                            <div>
                                <div
                                    class="truncate font-mono text-2xl font-bold text-[#000000]"
                                >
                                    {{ formatRupiah(financial.today_revenue) }}
                                </div>
                                <div class="mt-1 text-[11px] text-[#333333]">
                                    Terakumulasi dari tagihan kasir & QRIS hari
                                    ini
                                </div>
                            </div>
                        </motion.div>

                        <!-- Pendapatan Minggu Ini -->
                        <motion.div
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.05 }"
                            class="flex flex-col justify-between space-y-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                    >Pendapatan Minggu Ini</span
                                >
                                <div
                                    class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                >
                                    <TrendingUp class="size-4.5" />
                                </div>
                            </div>
                            <div>
                                <div
                                    class="truncate font-mono text-2xl font-bold text-[#000000]"
                                >
                                    {{ formatRupiah(financial.week_revenue) }}
                                </div>
                                <div class="mt-1 text-[11px] text-[#333333]">
                                    Akumulasi transaksi sejak awal pekan
                                </div>
                            </div>
                        </motion.div>

                        <!-- Antrean Hari Ini -->
                        <motion.div
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.1 }"
                            class="flex flex-col justify-between space-y-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                    >Antrean Berjalan</span
                                >
                                <div
                                    class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                >
                                    <Activity class="size-4.5" />
                                </div>
                            </div>
                            <div>
                                <div
                                    class="font-mono text-2xl font-bold text-[#000000]"
                                >
                                    {{ operational.today_active_queues }}
                                    <span
                                        class="text-xs font-normal text-[#333333]"
                                        >pasien</span
                                    >
                                </div>
                                <div class="mt-1 text-[11px] text-[#333333]">
                                    {{ operational.today_completed_queues }}
                                    antrean telah selesai diperiksa
                                </div>
                            </div>
                        </motion.div>

                        <!-- Dokter Bertugas -->
                        <motion.div
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ delay: 0.15 }"
                            class="flex flex-col justify-between space-y-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                                    >Dokter Praktik Hari Ini</span
                                >
                                <div
                                    class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                >
                                    <Stethoscope class="size-4.5" />
                                </div>
                            </div>
                            <div>
                                <div
                                    class="font-mono text-2xl font-bold text-[#000000]"
                                >
                                    {{ operational.doctors_on_duty_count }}
                                    <span
                                        class="text-xs font-normal text-[#333333]"
                                        >DPJP On-Duty</span
                                    >
                                </div>
                                <div class="mt-1 text-[11px] text-[#333333]">
                                    Dari total
                                    {{ staff_stats.total_doctors }} dokter
                                    spesialis terdaftar
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </section>

                <!-- 3. Financial Breakdown & Trend Chart Section -->
                <section
                    class="grid grid-cols-1 gap-6 lg:grid-cols-12"
                    aria-labelledby="financial-section-heading"
                >
                    <h2 id="financial-section-heading" class="sr-only">
                        Rincian Finansial & Tren Pendapatan
                    </h2>

                    <!-- Tren Pendapatan 6 Bulan Terakhir -->
                    <div
                        class="flex flex-col justify-between space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-6 lg:col-span-8"
                    >
                        <div
                            class="flex items-center justify-between border-b border-[#000000]/10 pb-4"
                        >
                            <div>
                                <h3
                                    class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                                >
                                    Tren Pendapatan 6 Bulan Terakhir
                                </h3>
                                <p
                                    class="font-['Rubik'] text-xs text-[#333333]"
                                >
                                    Rekonsiliasi transaksi lunas seluruh loket &
                                    kasir
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-[#beedc0] px-3 py-1 font-mono text-xs font-semibold text-[#000000]"
                            >
                                Total Bulan Ini:
                                {{ formatRupiah(financial.month_revenue) }}
                            </span>
                        </div>

                        <!-- CSS/SVG Pure Bar Chart -->
                        <div class="pt-4">
                            <div
                                class="grid h-48 grid-cols-6 items-end gap-2 border-b border-[#000000]/15 pb-2 sm:gap-4"
                            >
                                <div
                                    v-for="(
                                        item, idx
                                    ) in financial.monthly_trend"
                                    :key="idx"
                                    class="group flex h-full flex-col items-center justify-end gap-1.5"
                                >
                                    <div
                                        class="max-w-full truncate font-mono text-[10px] font-medium text-[#000000] opacity-0 transition-opacity group-hover:opacity-100 sm:text-xs"
                                    >
                                        {{ formatRupiah(item.revenue) }}
                                    </div>
                                    <div
                                        class="w-full max-w-[48px] origin-bottom rounded-t-lg bg-[#000000] transition-all group-hover:scale-y-105 hover:bg-[#beedc0]"
                                        :style="{
                                            height: `${Math.max((item.revenue / maxMonthlyRevenue) * 100, 4)}%`,
                                        }"
                                    ></div>
                                    <span
                                        class="w-full truncate text-center text-[10px] font-medium text-[#333333] sm:text-xs"
                                    >
                                        {{ item.month }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribusi Metode Pembayaran -->
                    <div
                        class="flex flex-col justify-between space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-6 lg:col-span-4"
                    >
                        <div class="border-b border-[#000000]/10 pb-4">
                            <h3
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Metode Pembayaran
                            </h3>
                            <p class="font-['Rubik'] text-xs text-[#333333]">
                                Agregasi kanal pelunasan tagihan
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(
                                    method, idx
                                ) in financial.revenue_by_method"
                                :key="idx"
                                class="flex items-center justify-between rounded-xl border border-[#000000]/5 bg-[#ffffff] p-3 text-xs"
                            >
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex size-7 items-center justify-center rounded-lg bg-[#beedc0] text-[#000000]"
                                    >
                                        <CreditCard
                                            v-if="method.method.includes('edc')"
                                            class="size-3.5"
                                        />
                                        <QrCode
                                            v-else-if="
                                                method.method.includes('qris')
                                            "
                                            class="size-3.5"
                                        />
                                        <Wallet v-else class="size-3.5" />
                                    </div>
                                    <div>
                                        <div
                                            class="font-semibold text-[#000000]"
                                        >
                                            {{ method.label }}
                                        </div>
                                        <div class="text-[10px] text-[#333333]">
                                            {{ method.count }} transaksi
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="font-mono font-bold text-[#000000]"
                                    >
                                        {{ formatRupiah(method.total) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Clinical Morbidity & Operational Matrix -->
                <section
                    class="grid grid-cols-1 gap-6 lg:grid-cols-12"
                    aria-labelledby="operations-heading"
                >
                    <h2 id="operations-heading" class="sr-only">
                        Morbiditas Klinis & Matriks Poliklinik
                    </h2>

                    <!-- Top 10 Diagnosis (Morbiditas) -->
                    <div
                        class="flex flex-col space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-6 lg:col-span-5"
                    >
                        <div class="border-b border-[#000000]/10 pb-4">
                            <div class="flex items-center gap-2">
                                <HeartPulse class="size-4 text-[#000000]" />
                                <h3
                                    class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                                >
                                    Top 10 Diagnosis Klinis
                                </h3>
                            </div>
                            <p class="font-['Rubik'] text-xs text-[#333333]">
                                Pola morbiditas pasien dari rekam medis (SOAP)
                            </p>
                        </div>

                        <div
                            v-if="morbidity.top_diagnoses.length > 0"
                            class="max-h-72 space-y-2.5 overflow-y-auto pr-1"
                        >
                            <div
                                v-for="(item, idx) in morbidity.top_diagnoses"
                                :key="idx"
                                class="flex items-center justify-between rounded-xl bg-[#edede2]/50 p-2.5 text-xs"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <span
                                        class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#000000] text-[10px] font-bold text-[#ffffff]"
                                    >
                                        {{ idx + 1 }}
                                    </span>
                                    <span
                                        class="truncate font-medium text-[#000000]"
                                    >
                                        {{ item.diagnosis }}
                                    </span>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[11px] font-bold text-[#000000]"
                                >
                                    {{ item.case_count }} kasus
                                </span>
                            </div>
                        </div>
                        <div
                            v-else
                            class="py-8 text-center text-xs text-[#333333]"
                        >
                            Belum ada data diagnosa rekam medis.
                        </div>
                    </div>

                    <!-- Matriks Poliklinik & Ruangan -->
                    <div
                        class="flex flex-col space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-6 lg:col-span-7"
                    >
                        <div
                            class="flex items-center justify-between border-b border-[#000000]/10 pb-4"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <Building2 class="size-4 text-[#000000]" />
                                    <h3
                                        class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                                    >
                                        Matriks Kesiapan Poliklinik
                                    </h3>
                                </div>
                                <p
                                    class="font-['Rubik'] text-xs text-[#333333]"
                                >
                                    Status dokter jaga dan antrean per
                                    poliklinik
                                </p>
                            </div>
                            <Link
                                href="/admin/schedules"
                                class="text-xs font-semibold text-[#000000] hover:underline"
                            >
                                Kelola Jadwal &rarr;
                            </Link>
                        </div>

                        <div
                            class="grid max-h-72 grid-cols-1 gap-3 overflow-y-auto pr-1 sm:grid-cols-2"
                        >
                            <div
                                v-for="poli in operational.clinic_matrix"
                                :key="poli.poli_id"
                                class="space-y-2 rounded-2xl border border-[#000000]/10 bg-[#ffffff] p-3.5"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        {{ poli.name_poli }}
                                    </span>
                                    <span
                                        :class="
                                            poli.is_active_today
                                                ? 'bg-[#beedc0] text-[#000000]'
                                                : 'bg-neutral-200 text-neutral-600'
                                        "
                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                    >
                                        {{
                                            poli.is_active_today
                                                ? 'Buka'
                                                : 'Tutup'
                                        }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-[#333333]">
                                    <div>{{ poli.doctor_name }}</div>
                                    <div class="text-[10px] text-[#333333]/70">
                                        {{ poli.room_name }} ({{
                                            poli.location
                                        }})
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-between border-t border-[#000000]/5 pt-1.5 text-[11px]"
                                >
                                    <span class="text-[#333333]">Antrean:</span>
                                    <span
                                        class="font-mono font-bold text-[#000000]"
                                    >
                                        {{ poli.waiting_count }} pasien
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. User & Personnel Aggregates Summary -->
                <section
                    class="space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-6"
                    aria-labelledby="users-summary-heading"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#000000]/10 pb-4"
                    >
                        <div class="flex items-center gap-2">
                            <Users class="size-4 text-[#000000]" />
                            <h3
                                id="users-summary-heading"
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Agregasi Tenaga Medis & Pengguna SIMRS
                            </h3>
                        </div>
                        <Link
                            href="/admin/users"
                            class="text-xs font-semibold text-[#000000] hover:underline"
                        >
                            Buka Manajemen Pengguna &rarr;
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                        <div
                            class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-none"
                        >
                            <div
                                class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                            >
                                Dokter DPJP
                            </div>
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                            >
                                {{ staff_stats.total_doctors }}
                            </div>
                            <div class="text-[11px] text-[#333333]">
                                {{ staff_stats.doctors_active }} status aktif
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-none"
                        >
                            <div
                                class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                            >
                                Perawat Tetap
                            </div>
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                            >
                                {{ staff_stats.nurses_tetap }}
                            </div>
                            <div class="text-[11px] text-[#333333]">
                                Pekerja & Kasir POS
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-none"
                        >
                            <div
                                class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                            >
                                Dokter Muda (Koas)
                            </div>
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                            >
                                {{ staff_stats.nurses_koas }}
                            </div>
                            <div class="text-[11px] text-[#333333]">
                                Intern Logbook Klinis
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 text-center shadow-none"
                        >
                            <div
                                class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                            >
                                Pasien Terdaftar
                            </div>
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                            >
                                {{ staff_stats.total_patients }}
                            </div>
                            <div class="text-[11px] text-[#333333]">
                                Master NIK Pasien
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 TAB 2: DISPLAY & VIDEO PLAYLIST SETTINGS
                 ═══════════════════════════════════════════════════════════════ -->
            <div v-else class="space-y-6">
                <!-- Grid: Upload Form + Quick Summary -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <!-- Form Upload Video Baru -->
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        class="space-y-5 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-none sm:p-7 lg:col-span-7"
                    >
                        <div class="border-b border-[#000000]/10 pb-4">
                            <div class="flex items-center gap-2">
                                <Film class="size-5 text-[#000000]" />
                                <h2
                                    class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                                >
                                    Tambah Video ke Playlist Layar TV
                                </h2>
                            </div>
                            <p
                                class="mt-0.5 font-['Rubik'] text-xs text-[#333333]"
                            >
                                Pilih metode input via Link/Embed YouTube atau
                                unggah file video fisik lokal (maksimal 800 MB).
                            </p>
                        </div>

                        <!-- Method Switcher Tabs -->
                        <div class="space-y-1.5 font-['Rubik']">
                            <label
                                class="block text-xs font-semibold text-[#000000]"
                            >
                                Metode Sumber Video *
                            </label>
                            <div
                                class="grid grid-cols-2 gap-2 rounded-2xl border border-[#000000]/10 bg-[#edede2] p-1.5"
                            >
                                <button
                                    type="button"
                                    @click="
                                        videoUploadForm.source_type = 'youtube'
                                    "
                                    :class="
                                        videoUploadForm.source_type ===
                                        'youtube'
                                            ? 'border border-[#000000]/15 bg-[#ffffff] font-bold text-[#000000] shadow-xs'
                                            : 'border border-transparent font-medium text-[#333333] hover:text-[#000000]'
                                    "
                                    class="flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs transition-all"
                                >
                                    <svg
                                        class="size-4 shrink-0 text-red-600"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                                        />
                                    </svg>
                                    <span class="truncate">YouTube</span>
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        videoUploadForm.source_type = 'file'
                                    "
                                    :class="
                                        videoUploadForm.source_type === 'file'
                                            ? 'border border-[#000000]/15 bg-[#ffffff] font-bold text-[#000000] shadow-xs'
                                            : 'border border-transparent font-medium text-[#333333] hover:text-[#000000]'
                                    "
                                    class="flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs transition-all"
                                >
                                    <UploadCloud
                                        class="size-4 shrink-0 text-emerald-700"
                                    />
                                    <span class="truncate">Upload File</span>
                                </button>
                            </div>
                        </div>

                        <form
                            @submit.prevent="handleUploadVideo"
                            class="space-y-4 font-['Rubik'] text-xs"
                        >
                            <!-- Judul Video -->
                            <div>
                                <label
                                    for="video-title"
                                    class="mb-1 block font-medium text-[#000000]"
                                >
                                    Judul Video Edukasi / Promosi *
                                </label>
                                <input
                                    id="video-title"
                                    v-model="videoUploadForm.title"
                                    type="text"
                                    placeholder="Contoh: Edukasi Pola Hidup Sehat & Alur Rawat Jalan"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#000000] focus:outline-none"
                                />
                                <p
                                    v-if="videoUploadForm.errors.title"
                                    class="mt-1 text-xs font-semibold text-rose-700"
                                >
                                    {{ videoUploadForm.errors.title }}
                                </p>
                            </div>

                            <!-- METODE 1: Link YouTube / Embed Iframe -->
                            <div
                                v-if="videoUploadForm.source_type === 'youtube'"
                                class="space-y-3"
                            >
                                <div>
                                    <label
                                        for="video-youtube-url"
                                        class="mb-1 block font-medium text-[#000000]"
                                    >
                                        Link Video YouTube / Kode Embed
                                        &lt;iframe&gt; *
                                    </label>
                                    <textarea
                                        id="video-youtube-url"
                                        v-model="videoUploadForm.youtube_url"
                                        rows="2"
                                        placeholder="Contoh: https://www.youtube.com/watch?v=zVgKnfN9i34 atau tempelkan kode <iframe> dari YouTube"
                                        class="w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-3 font-mono text-xs text-[#000000] focus:border-[#000000] focus:outline-none"
                                    ></textarea>
                                    <p
                                        v-if="
                                            videoUploadForm.errors.youtube_url
                                        "
                                        class="mt-1 text-xs font-semibold text-rose-700"
                                    >
                                        {{ videoUploadForm.errors.youtube_url }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-[#333333]">
                                        Mendukung link standar (youtube.com),
                                        link singkat (youtu.be), YouTube Shorts,
                                        maupun kode embed &lt;iframe&gt;.
                                    </p>
                                </div>

                                <!-- Live Preview Box YouTube -->
                                <div
                                    v-if="livePreviewEmbedUrl"
                                    class="space-y-1.5 rounded-2xl border border-[#000000]/10 bg-[#edede2]/50 p-3"
                                >
                                    <div
                                        class="flex items-center gap-1.5 text-xs font-bold text-[#000000]"
                                    >
                                        <CheckCircle2
                                            class="size-3.5 text-emerald-600"
                                        />
                                        <span>Pratinjau Video YouTube:</span>
                                    </div>
                                    <div
                                        class="aspect-video w-full max-w-sm overflow-hidden rounded-xl bg-black"
                                    >
                                        <iframe
                                            :src="livePreviewEmbedUrl"
                                            class="h-full w-full border-0"
                                            allow="
                                                accelerometer;
                                                autoplay;
                                                clipboard-write;
                                                encrypted-media;
                                                gyroscope;
                                                picture-in-picture;
                                            "
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                </div>
                            </div>

                            <!-- METODE 2: Upload File Video Fisik (Maks. 800 MB) -->
                            <div v-else class="space-y-3">
                                <label
                                    class="mb-1 block font-medium text-[#000000]"
                                >
                                    Pilih Berkas File Video (Maks. 800 MB) *
                                </label>

                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-m4v,.mp4,.webm,.ogg,.mov,.m4v"
                                    class="hidden"
                                    @change="onFileInputChange"
                                />

                                <!-- Dropzone Box -->
                                <div
                                    v-if="!localVideoFile"
                                    @click="fileInputRef?.click()"
                                    @dragover.prevent="isDraggingFile = true"
                                    @dragleave.prevent="isDraggingFile = false"
                                    @drop.prevent="onFileDrop"
                                    :class="[
                                        'flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition-all',
                                        isDraggingFile
                                            ? 'border-[#000000] bg-[#beedc0]/30'
                                            : 'border-[#000000]/20 bg-[#ffffff] hover:border-[#000000]/40 hover:bg-[#edede2]/40',
                                    ]"
                                >
                                    <div
                                        class="mb-2 flex size-12 items-center justify-center rounded-2xl bg-[#edede2] text-[#000000]"
                                    >
                                        <UploadCloud
                                            class="size-6 text-[#000000]"
                                        />
                                    </div>
                                    <p class="text-xs font-bold text-[#000000]">
                                        Klik untuk memilih file atau seret file
                                        video ke sini
                                    </p>
                                    <p class="mt-1 text-[11px] text-[#333333]">
                                        Format: MP4, WebM, OGG, MOV, M4V &bull;
                                        Ukuran Maksimal: <strong>800 MB</strong>
                                    </p>
                                </div>

                                <!-- Selected File Info & Preview -->
                                <div v-else class="space-y-3">
                                    <div
                                        class="flex items-center justify-between rounded-2xl border border-[#000000]/15 bg-[#ffffff] p-3.5 shadow-xs"
                                    >
                                        <div
                                            class="flex items-center gap-3 overflow-hidden"
                                        >
                                            <div
                                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#beedc0] text-[#000000]"
                                            >
                                                <FileVideo class="size-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-xs font-bold text-[#000000]"
                                                >
                                                    {{ localVideoFile.name }}
                                                </p>
                                                <p
                                                    class="text-[11px] text-[#333333]"
                                                >
                                                    Ukuran:
                                                    <strong>{{
                                                        formatBytes(
                                                            localVideoFile.size,
                                                        )
                                                    }}</strong>
                                                    / 800 MB
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeSelectedFile"
                                            class="flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-neutral-900"
                                            title="Hapus file terpilih"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </div>

                                    <!-- Local Video Player Preview -->
                                    <div
                                        v-if="localVideoPreviewUrl"
                                        class="space-y-1.5 rounded-2xl border border-[#000000]/10 bg-[#edede2]/50 p-3"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-xs font-bold text-[#000000]"
                                        >
                                            <CheckCircle2
                                                class="size-3.5 text-emerald-600"
                                            />
                                            <span>Pratinjau Video Lokal:</span>
                                        </div>
                                        <div
                                            class="aspect-video w-full max-w-sm overflow-hidden rounded-xl bg-black"
                                        >
                                            <video
                                                :src="localVideoPreviewUrl"
                                                controls
                                                preload="metadata"
                                                class="h-full w-full object-cover"
                                            ></video>
                                        </div>
                                    </div>
                                </div>

                                <!-- Error Messages -->
                                <p
                                    v-if="fileSizeError"
                                    class="mt-1 text-xs font-semibold text-rose-700"
                                >
                                    {{ fileSizeError }}
                                </p>
                                <p
                                    v-if="videoUploadForm.errors.video_file"
                                    class="mt-1 text-xs font-semibold text-rose-700"
                                >
                                    {{ videoUploadForm.errors.video_file }}
                                </p>

                                <!-- Upload Progress Bar -->
                                <div
                                    v-if="videoUploadForm.progress"
                                    class="space-y-1 rounded-xl border border-[#000000]/10 bg-[#ffffff] p-3"
                                >
                                    <div
                                        class="flex items-center justify-between text-xs font-semibold text-[#000000]"
                                    >
                                        <span>Mengunggah File Video...</span>
                                        <span
                                            >{{
                                                videoUploadForm.progress
                                                    .percentage
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-2 w-full overflow-hidden rounded-full bg-[#edede2]"
                                    >
                                        <div
                                            class="h-full bg-[#000000] transition-all duration-200"
                                            :style="{
                                                width: `${videoUploadForm.progress.percentage}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Urutan Putar & Status Awal -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        for="video-order"
                                        class="mb-1 block font-medium text-[#000000]"
                                    >
                                        Urutan Pemutaran (Nomor Playlist)
                                    </label>
                                    <input
                                        id="video-order"
                                        v-model="videoUploadForm.order"
                                        type="number"
                                        min="0"
                                        class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#000000] focus:outline-none"
                                    />
                                    <p
                                        v-if="videoUploadForm.errors.order"
                                        class="mt-1 text-xs font-semibold text-rose-700"
                                    >
                                        {{ videoUploadForm.errors.order }}
                                    </p>
                                </div>

                                <div class="flex items-center pt-6">
                                    <label
                                        class="flex cursor-pointer items-center gap-2.5"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="videoUploadForm.is_active"
                                            class="size-4.5 rounded accent-[#000000]"
                                        />
                                        <span
                                            class="font-medium text-[#000000]"
                                        >
                                            Langsung Aktifkan di TV
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    :disabled="isSubmitDisabled"
                                    class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-medium text-[#ffffff] shadow-none transition-colors hover:bg-[#1a1a1a] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Loader2
                                        v-if="videoUploadForm.processing"
                                        class="size-4 animate-spin text-[#beedc0]"
                                    />
                                    <CheckCircle2
                                        v-else
                                        class="size-4 text-[#beedc0]"
                                    />
                                    <span>{{
                                        videoUploadForm.processing
                                            ? 'Menyimpan...'
                                            : 'Simpan ke Playlist TV'
                                    }}</span>
                                </button>
                            </div>
                        </form>
                    </motion.div>

                    <!-- Banner Informasi Layar Display TV -->
                    <div
                        class="flex flex-col justify-between space-y-4 rounded-3xl border border-[#000000]/10 bg-[#000000] p-6 text-[#ffffff] shadow-xl sm:p-7 lg:col-span-5"
                    >
                        <div class="space-y-3">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-[#beedc0] text-[#000000]"
                            >
                                <Tv class="size-6" />
                            </div>
                            <h3
                                class="font-['ivypresto-headline'] text-2xl font-bold text-[#ffffff]"
                            >
                                Layar Monitor TV Ruang Tunggu
                            </h3>
                            <p
                                class="font-['Rubik'] text-xs leading-relaxed text-[#ffffff]/80"
                            >
                                Fitur Auto-Looping Video Playlist mendukung
                                pemutaran video edukasi melalui streaming link
                                YouTube maupun file video lokal resolusi tinggi
                                hingga 800 MB secara berurutan dan bersiklus
                                tanpa jeda.
                            </p>
                            <ul
                                class="space-y-2 border-t border-[#ffffff]/15 pt-3 font-['Rubik'] text-xs text-[#ffffff]/90"
                            >
                                <li class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="size-4 text-[#beedc0]"
                                    />
                                    <span
                                        >Mendukung streaming YouTube Iframe
                                        API</span
                                    >
                                </li>
                                <li class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="size-4 text-[#beedc0]"
                                    />
                                    <span
                                        >Mendukung upload file video lokal
                                        hingga <strong>800 MB</strong> (MP4,
                                        WebM, MOV)</span
                                    >
                                </li>
                                <li class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="size-4 text-[#beedc0]"
                                    />
                                    <span
                                        >Fitur Smart Audio Ducking saat
                                        panggilan antrean berlangsung</span
                                    >
                                </li>
                                <li class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="size-4 text-[#beedc0]"
                                    />
                                    <span
                                        >Auto-cycle video berikutnya saat durasi
                                        berakhir</span
                                    >
                                </li>
                            </ul>
                        </div>

                        <a
                            href="/display"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] bg-[#beedc0] px-6 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#a5e6a8]"
                        >
                            <ExternalLink class="size-4" />
                            <span>Buka Preview Layar TV (/display)</span>
                        </a>
                    </div>
                </div>

                <!-- Grid Inventori Video Playlist -->
                <section
                    class="space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-none sm:p-7"
                >
                    <div
                        class="flex flex-col gap-2 border-b border-[#000000]/10 pb-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <Film class="size-5 text-[#000000]" />
                                <h3
                                    class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                                >
                                    Daftar Playlist Video TV Antrean
                                </h3>
                            </div>
                            <p class="font-['Rubik'] text-xs text-[#333333]">
                                Urutan dan status aktif video yang sedang tayang
                                di layar ruang tunggu
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-full bg-[#beedc0] px-3.5 py-1 text-xs font-bold text-[#000000]"
                            >
                                {{
                                    display_videos.filter((v) => v.is_active)
                                        .length
                                }}
                                Video Tayang Aktif
                            </span>
                        </div>
                    </div>

                    <!-- Video Grid Cards -->
                    <div
                        v-if="display_videos.length > 0"
                        class="grid grid-cols-1 gap-5 pt-2 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <div
                            v-for="video in display_videos"
                            :key="video.id"
                            class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#000000]/10 bg-[#ffffff] p-4 transition-all hover:border-[#000000]/25"
                        >
                            <div class="space-y-3">
                                <!-- Mini Video Player Preview (YouTube Iframe) -->
                                <div
                                    class="relative aspect-video w-full overflow-hidden rounded-xl bg-[#000000]"
                                >
                                    <iframe
                                        v-if="video.youtube_id"
                                        :src="`https://www.youtube.com/embed/${video.youtube_id}`"
                                        class="h-full w-full border-0"
                                        allow="
                                            accelerometer;
                                            autoplay;
                                            clipboard-write;
                                            encrypted-media;
                                            gyroscope;
                                            picture-in-picture;
                                        "
                                        allowfullscreen
                                    ></iframe>
                                    <video
                                        v-else
                                        :src="video.video_url"
                                        controls
                                        preload="metadata"
                                        class="h-full w-full object-cover"
                                    ></video>
                                </div>

                                <div>
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <div
                                            class="flex flex-wrap items-center gap-1.5"
                                        >
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-[#edede2] px-2 py-0.5 text-[10px] font-bold text-[#000000]"
                                            >
                                                <ListOrdered class="size-3" />
                                                <span
                                                    >Urutan #{{
                                                        video.order
                                                    }}</span
                                                >
                                            </span>
                                            <span
                                                v-if="video.youtube_id"
                                                class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-700"
                                            >
                                                <svg
                                                    class="size-3 shrink-0 text-red-600"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                                                    />
                                                </svg>
                                                <span>YouTube</span>
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800"
                                            >
                                                <FileVideo class="size-3" />
                                                <span>File Lokal</span>
                                            </span>
                                        </div>
                                        <span
                                            :class="
                                                video.is_active
                                                    ? 'bg-[#beedc0] text-[#000000]'
                                                    : 'bg-neutral-200 text-neutral-700'
                                            "
                                            class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        >
                                            {{
                                                video.is_active
                                                    ? 'Tayang Aktif'
                                                    : 'Nonaktif'
                                            }}
                                        </span>
                                    </div>
                                    <h4
                                        class="mt-2 line-clamp-1 font-['ivypresto-headline'] text-base font-bold text-[#000000]"
                                    >
                                        {{ video.title }}
                                    </h4>
                                    <p
                                        class="mt-0.5 font-['Rubik'] text-[11px] text-[#333333]"
                                    >
                                        Ukuran/Format:
                                        <strong>{{
                                            video.file_size_formatted
                                        }}</strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Tombol Aksi Item Video -->
                            <div
                                class="mt-4 flex items-center justify-between border-t border-[#000000]/10 pt-3"
                            >
                                <button
                                    type="button"
                                    @click="handleToggleVideoStatus(video)"
                                    :disabled="isTogglingVideoId === video.id"
                                    class="inline-flex min-h-[38px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border px-3 py-1.5 text-xs font-semibold transition-colors"
                                    :class="
                                        video.is_active
                                            ? 'border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]'
                                            : 'border-[#000000] bg-[#000000] text-[#ffffff] hover:bg-[#1a1a1a]'
                                    "
                                >
                                    <Loader2
                                        v-if="isTogglingVideoId === video.id"
                                        class="size-3.5 animate-spin"
                                    />
                                    <Power v-else class="size-3.5" />
                                    <span>{{
                                        video.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</span>
                                </button>

                                <button
                                    type="button"
                                    @click="openDeleteVideoModal(video)"
                                    class="inline-flex min-h-[38px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-100"
                                    title="Hapus Video dari Playlist"
                                >
                                    <Trash2 class="size-3.5" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State Video Playlist -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center p-12 text-center"
                    >
                        <Film class="size-12 text-[#000000]/25" />
                        <h4
                            class="mt-3 font-['ivypresto-headline'] text-lg font-bold text-[#000000]"
                        >
                            Belum Ada Video di Playlist
                        </h4>
                        <p
                            class="mt-1 max-w-sm font-['Rubik'] text-xs text-[#333333]"
                        >
                            Masukkan link video YouTube di atas untuk mulai
                            menayangkan video edukasi di layar TV antrean.
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Konfirmasi Hapus Video
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="deleteTargetVideo"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-video-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full border border-rose-200 bg-rose-100 text-rose-700"
                    >
                        <Trash2 class="size-5" />
                    </div>
                    <div>
                        <h2
                            id="delete-video-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                        >
                            Hapus Video dari Playlist
                        </h2>
                        <p
                            class="line-clamp-1 font-['Rubik'] text-xs text-[#333333]"
                        >
                            {{ deleteTargetVideo.title }}
                        </p>
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-4 font-['Rubik'] text-xs text-[#000000]/80"
                >
                    <p>
                        Apakah Anda yakin ingin menghapus video
                        <strong class="text-[#000000]">{{
                            deleteTargetVideo.title
                        }}</strong
                        >?
                    </p>
                    <p class="text-[#333333]">
                        File fisik video akan dihapus permanen dari media
                        penyimpanan server dan dikeluarkan dari seluruh rotasi
                        layar TV antrean.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="deleteTargetVideo = null"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="confirmDeleteVideo"
                        :disabled="isDeletingVideo"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-rose-600 px-6 py-2 text-xs font-medium text-[#ffffff] shadow-none hover:bg-rose-700 disabled:opacity-60"
                    >
                        <Loader2
                            v-if="isDeletingVideo"
                            class="size-4 animate-spin text-[#ffffff]"
                        />
                        <Trash2 v-else class="size-4 text-[#ffffff]" />
                        <span>{{
                            isDeletingVideo ? 'Menghapus...' : 'Ya, Hapus Video'
                        }}</span>
                    </button>
                </div>
            </motion.div>
        </div>
    </AdminLayout>
</template>
