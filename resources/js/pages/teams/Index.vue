<script setup lang="ts">
/**
 * @file teams/Index.vue
 * @description Public Poliklinik & Medical Team page (/teams) for Hospital Population.
 * Allows visitors to explore each polyclinic department, medical specialist team, nursing staff,
 * examination rooms, service scope, and live doctor schedule booking via BookingModal.
 * Adheres strictly to DESIGN.md (Evergreen design system) and AGENTS.md rules.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    Ambulance,
    ArrowRight,
    Award,
    Bed,
    Building2,
    Calendar,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    Clock,
    Droplets,
    Eye,
    FileText,
    Heart,
    HeartHandshake,
    HeartPulse,
    HelpCircle,
    Hospital,
    Info,
    LogIn,
    LogOut,
    Mail,
    MapPin,
    Menu,
    MessageSquare,
    Phone,
    PhoneCall,
    Quote,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    Ticket,
    Tv,
    User,
    UserCheck,
    UserPlus,
    Users,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import BookingModal from '@/components/BookingModal.vue';
import TicketSuccessModal from '@/components/TicketSuccessModal.vue';
import type { TicketData } from '@/components/TicketSuccessModal.vue';
import type {
    Doctor,
    DoctorSchedule,
    Poli,
    PoliTabItem,
    PoliTeamDetail,
} from '@/types';

// Wajib mandiri tanpa sidebar default
defineOptions({
    layout: undefined,
});

const defaultPoliFallback: PoliTeamDetail = {
    code: 'POL-UM',
    slug: 'poli-umum',
    name: 'Poli Umum',
    short_name: 'Umum',
    badge: 'Pelayanan Medis Primer Terpadu',
    floor: 'Lantai 1, Gedung Utama',
    tagline:
        'Pemeriksaan kesehatan menyeluruh dan penanganan keluhan medis dasar dengan pendekatan preventif dan kuratif.',
    description:
        'Poliklinik Umum menyediakan layanan konsultasi medis komprehensif untuk berbagai keluhan kesehatan harian, skrining awal penyakit, konsultasi gaya hidup sehat, hingga rujukan terarah ke dokter subspesialis jika diperlukan.',
    icon_name: 'Stethoscope',
    head_doctor: 'dr. Hendra Gunawan',
    head_doctor_title: 'Kepala Poliklinik Umum & Dokter Layanan Primer',
    head_nurse: 'Ns. Ratna Sari, S.Kep',
    head_nurse_title: 'Kepala Ruangan Perawat Poliklinik Umum',
    operating_hours: 'Senin - Sabtu: 08:00 - 20:00 WIB',
    metrics: [
        {
            value: '99.2%',
            label: 'Kepuasan Pasien',
            desc: 'Survei Evaluasi Mutu KARS 2026',
        },
        {
            value: '15 Mnt',
            label: 'Rata-rata Waktu Tunggu',
            desc: 'Sistem Terintegrasi E-Karcis SIMRS',
        },
        {
            value: '12+',
            label: 'Dokter Spesialis & Paramedis',
            desc: 'Tenaga Medis Bersertifikat Aktif',
        },
    ],
    rooms: [
        {
            name: 'Ruang Konsultasi Medis 1',
            code: 'R.101',
            desc: 'Pemeriksaan umum dan konsultasi preventif',
        },
        {
            name: 'Ruang Tindakan Ringan',
            code: 'R.102',
            desc: 'Perawatan luka, injeksi, dan nebulisasi',
        },
    ],
    scope_services: [
        'Konsultasi keluhan umum & infeksi pernapasan / pencernaan',
        'Pemeriksaan tanda vital, gula darah, & kolesterol sewaktu',
        'Surat Keterangan Sehat & Bebas Narkoba resmi rumah sakit',
        'Vaksinasi dewasa (Influenza, Hepatitis, Tifoid, Meningitis)',
        'Perawatan luka ringan & pertolongan pertama ambulatoir',
        'Konsultasi rujukan terpadu ke 9 poliklinik spesialis lanjutan',
    ],
    team_doctors: [
        {
            name: 'dr. Hendra Gunawan',
            role: 'Kepala Poliklinik Umum',
            specialty: 'Dokter Umum & Kedokteran Keluarga',
            sip: 'SIP.503/441/DPJP-UM/2026',
            experience: '12 Tahun Pengalaman',
            schedule: 'Senin - Jumat: 08:00 - 15:00',
            badge: 'DPJP Utama',
        },
    ],
    team_nurses: [
        {
            name: 'Ns. Ratna Sari, S.Kep',
            role: 'Kepala Perawat Poli',
            str: 'STR.1988273910283',
            cert: 'ACLS / BLS Certified',
        },
    ],
    faqs: [
        {
            q: 'Apakah saya bisa langsung datang ke Poli Umum tanpa reservasi online?',
            a: 'Bisa, loket admisi rawat jalan tetap melayani pendaftaran on-site. Namun, kami menyarankan pendaftaran online agar waktu tunggu lebih efisien.',
        },
        {
            q: 'Dokumen apa yang perlu dibawa saat kunjungan pertama kali?',
            a: 'Cukup bawa kartu identitas (KTP/SIM/Paspor) serta kartu asuransi atau kartu BPJS aktif jika menggunakan penjaminan kesehatan.',
        },
    ],
};

/* ═══════════════════════════════════════════════════════════════
   Props & Backend Data
   ═══════════════════════════════════════════════════════════════ */
const props = withDefaults(
    defineProps<{
        polis?: PoliTabItem[];
        currentPoli?: PoliTeamDetail;
        schedules?: DoctorSchedule[];
        dbDoctors?: Doctor[];
        dbNurses?: any[];
        dbPoli?: Poli | null;
        teams?: any[];
    }>(),
    {
        polis: () => [],
        currentPoli: undefined,
        schedules: () => [],
        dbDoctors: () => [],
        dbNurses: () => [],
        dbPoli: null,
        teams: () => [],
    },
);

const safeCurrentPoli = computed(
    () => props.currentPoli || defaultPoliFallback,
);

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isStaffUser = computed(() => {
    const role = currentUser.value?.role;

    return (
        ['doctor', 'nurse', 'admin'].includes(role || '') ||
        Boolean(currentUser.value?.is_doctor)
    );
});

/* ═══════════════════════════════════════════════════════════════
   Mega Menu & Mobile Drawer State
   ═══════════════════════════════════════════════════════════════ */
const isMegaMenuOpen = ref(false);
const isMounted = ref(false);
const isMobileMenuOpen = ref(false);
const isMobilePoliOpen = ref(false);
const isMobileCoEOpen = ref(false);
const isMobileFacilitiesOpen = ref(false);
const doctorSearchQuery = ref('');
const selectedDayFilter = ref('Semua');
const openFaqIndex = ref<number | null>(0);

const openMobileMenu = () => {
    isMobileMenuOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileMenuOpen.value) {
        closeMobileMenu();
    }
};

const removeNavigateListener = router.on('navigate', () => {
    closeMobileMenu();
});

onMounted(() => {
    isMounted.value = true;
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    removeNavigateListener();
    document.body.style.overflow = '';
});

const handleLogout = () => {
    closeMobileMenu();
    router.post('/logout');
};

// State Modal Booking & Karcis Antrean
const isBookingModalOpen = ref(false);
const selectedSchedule = ref<DoctorSchedule | null>(null);
const isTicketModalOpen = ref(false);
const activeTicket = ref<TicketData | null>(null);

/**
 * Filtered schedules for the active polyclinic department and search query
 */
const filteredSchedules = computed(() => {
    let list = props.schedules;

    // Filter by poli matching safeCurrentPoli
    if (safeCurrentPoli.value) {
        const poliShort = safeCurrentPoli.value.short_name.toLowerCase();
        const poliName = safeCurrentPoli.value.name.toLowerCase();
        list = list.filter((sch) => {
            const schPoli = (
                sch.poli?.name ||
                sch.poli?.name_poli ||
                ''
            ).toLowerCase();

            return (
                schPoli.includes(poliShort) ||
                poliName.includes(schPoli) ||
                list.length <= 4
            );
        });
    }

    // Filter by practice day
    if (selectedDayFilter.value !== 'Semua') {
        list = list.filter((sch) => {
            const day = sch.day || sch.day_of_week || '';

            return day.toLowerCase() === selectedDayFilter.value.toLowerCase();
        });
    }

    // Filter by doctor name or specialty
    if (doctorSearchQuery.value.trim()) {
        const query = doctorSearchQuery.value.toLowerCase().trim();
        list = list.filter((sch) => {
            const docName = sch.doctor?.name?.toLowerCase() || '';
            const specName =
                typeof sch.doctor?.specialization === 'string'
                    ? sch.doctor.specialization.toLowerCase()
                    : sch.doctor?.specialization?.name_specialization?.toLowerCase() ||
                      '';

            return docName.includes(query) || specName.includes(query);
        });
    }

    return list;
});

const availableDays = [
    'Semua',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu',
];

/**
 * Switch active polyclinic via router visit
 */
const changePoli = (poliNameOrSlug: string) => {
    isMegaMenuOpen.value = false;
    router.visit(`/teams?poli=${encodeURIComponent(poliNameOrSlug)}`, {
        preserveScroll: false,
    });
};

/**
 * Smooth scroll helper with sticky header offset
 */
const scrollToSection = (sectionId: string) => {
    isMegaMenuOpen.value = false;
    setTimeout(() => {
        const element = document.getElementById(sectionId);

        if (element) {
            const headerOffset = 90;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition =
                elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth',
            });
        }
    }, 50);
};

/**
 * Handle booking button click
 */
const handleBookSchedule = (sch: DoctorSchedule) => {
    if (!currentUser.value) {
        router.visit('/login');

        return;
    }

    selectedSchedule.value = sch;
    isBookingModalOpen.value = true;
};

/**
 * Handle successful appointment booking from BookingModal
 */
const handleBookingSuccess = (appointmentData: any) => {
    isBookingModalOpen.value = false;

    activeTicket.value = {
        appointment_id: appointmentData.appointment_id || appointmentData.id,
        queue_number: appointmentData.queue_number || 'A-01',
        doctor_name:
            appointmentData.doctor_schedule?.doctor?.name ||
            selectedSchedule.value?.doctor?.name ||
            'Dokter Spesialis',
        poli_name:
            appointmentData.doctor_schedule?.poli?.name ||
            appointmentData.doctor_schedule?.poli?.name_poli ||
            selectedSchedule.value?.poli?.name ||
            safeCurrentPoli.value.name,
        appointment_date:
            appointmentData.appointment_date ||
            new Date().toISOString().split('T')[0],
        patient_name:
            appointmentData.patient?.name ||
            currentUser.value?.name ||
            'Pasien',
        resident_n: appointmentData.patient?.resident_n || '',
    };

    isTicketModalOpen.value = true;
};

/**
 * Toggle FAQ accordion item
 */
const toggleFaq = (index: number) => {
    if (openFaqIndex.value === index) {
        openFaqIndex.value = null;
    } else {
        openFaqIndex.value = index;
    }
};

/**
 * Helper to jump to polyclinic team page from mega menu
 */
const jumpToPoliTeam = (poli: string) => {
    isMegaMenuOpen.value = false;
    router.visit('/teams?poli=' + encodeURIComponent(poli));
};

/**
 * Static Data for Navbar Mega Menu (Matches Welcome.vue)
 */
const centersOfExcellence = [
    {
        title: 'Pusat Jantung & Vaskular',
        subtitle: 'Cardiology & Vascular Center',
        icon: HeartPulse,
    },
    {
        title: 'Kesehatan Ibu & Anak',
        subtitle: 'Women & Children Health Center',
        icon: Heart,
    },
    {
        title: 'Bedah Ortopedi & Sendi',
        subtitle: 'Orthopedic & Joint Center',
        icon: Activity,
    },
    {
        title: 'Pusat Onkologi & Kanker',
        subtitle: 'Integrated Cancer Care',
        icon: ShieldAlert,
    },
    {
        title: 'Saraf & Brain Spine Care',
        subtitle: 'Neurology & Neurosurgery Center',
        icon: Sparkles,
    },
    {
        title: 'Penyakit Dalam & Saluran Cerna',
        subtitle: 'Digestive & Internal Medicine',
        icon: Droplets,
    },
];

const hospitalFacilities = [
    {
        title: 'Laboratorium Otomatis 24 Jam',
        icon: FileText,
    },
    {
        title: 'Radiologi & Imaging Modern',
        icon: Building2,
    },
    {
        title: 'Farmasi & Apotek Terpadu 24 Jam',
        icon: ShieldCheck,
    },
    {
        title: 'Armada Ambulans Emergency',
        icon: Ambulance,
    },
];
</script>

<template>
    <Head :title="`${safeCurrentPoli.name} | Tim Medis & Fasilitas Poliklinik`">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Rubik:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="min-h-screen bg-[#edede2] font-['Rubik'] text-[#000000] antialiased selection:bg-[#beedc0] selection:text-[#000000]"
    >
        <!-- ═══════════════════════════════════════════════════════════
             1. TOP UTILITY & EMERGENCY BAR (Siloam Style)
             ═══════════════════════════════════════════════════════════ -->
        <div
            class="border-b border-[#333333]/40 bg-[#000000] px-4 py-2 text-xs text-white sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto flex max-w-[1200px] scrollbar-none flex-nowrap items-center justify-between gap-4 overflow-x-auto"
            >
                <!-- Left: Emergency hotline & IGD info -->
                <div
                    class="flex shrink-0 items-center gap-4 whitespace-nowrap text-white/90 sm:gap-6"
                >
                    <a
                        href="tel:1500181"
                        class="inline-flex items-center gap-1.5 font-bold whitespace-nowrap transition-colors hover:text-[#beedc0]"
                    >
                        <span
                            class="flex h-2 w-2 shrink-0 animate-ping rounded-full bg-red-500"
                        />
                        <PhoneCall class="size-3.5 shrink-0 text-[#beedc0]" />
                        <span class="whitespace-nowrap"
                            >IGD &amp; Ambulans 24 Jam:
                            <strong>1-500-181</strong></span
                        >
                    </a>
                    <span class="hidden text-[#333333] sm:inline">|</span>
                    <a
                        href="https://wa.me/6281100000000"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 whitespace-nowrap transition-colors hover:text-[#beedc0]"
                    >
                        <MessageSquare
                            class="size-3.5 shrink-0 text-[#beedc0]"
                        />
                        <span class="whitespace-nowrap"
                            >WhatsApp Care: 0811-0000-0000</span
                        >
                    </a>
                </div>

                <!-- Right: Quick Portal Links -->
                <div
                    class="flex shrink-0 items-center gap-4 text-[11px] whitespace-nowrap text-white/80"
                >
                    <span
                        class="inline-flex items-center gap-1 font-semibold whitespace-nowrap text-[#beedc0]"
                    >
                        <Award class="size-3.5 shrink-0" />
                        <span>Akreditasi KARS Paripurna</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Backdrop Overlay untuk Mega Menu Desktop -->
        <div
            v-if="isMegaMenuOpen"
            class="fixed inset-0 z-30 bg-black/15 backdrop-blur-[1px] transition-opacity"
            @click="isMegaMenuOpen = false"
        />

        <!-- ═══════════════════════════════════════════════════════════
             2. MAIN NAVBAR DENGAN MEGA MENU (Matches Welcome.vue)
             ═══════════════════════════════════════════════════════════ -->
        <motion.header
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="sticky top-0 z-40 border-b border-[#333333]/15 bg-[#edede2]/95 backdrop-blur-md transition-all"
        >
            <div
                class="mx-auto flex h-20 max-w-[1200px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
            >
                <!-- Brand Logo & Identity -->
                <Link href="/" class="group flex shrink-0 items-center gap-3">
                    <motion.div
                        :whileHover="{ scale: 1.05 }"
                        :whileTap="{ scale: 0.95 }"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-[#beedc0]"
                    >
                        <AppLogoIcon
                            class="size-7 fill-current text-[#000000]"
                        />
                    </motion.div>
                    <div class="flex flex-col justify-center">
                        <span
                            class="font-['ivypresto-headline'] font-serif text-xl leading-tight font-bold tracking-tight whitespace-nowrap text-[#000000] sm:text-2xl"
                        >
                            Hospital Population
                        </span>
                        <span
                            class="text-[10px] font-medium tracking-wider whitespace-nowrap text-[#333333] uppercase sm:text-[11px]"
                        >
                            Pelayanan Kesehatan Terpadu
                        </span>
                    </div>
                </Link>

                <!-- Navigation Links (Desktop) -->
                <nav
                    class="hidden items-center gap-1.5 text-sm font-medium whitespace-nowrap text-[#000000] lg:flex xl:gap-2"
                >
                    <!-- Button Cari Dokter (Links to /schedule-guest) -->
                    <Link
                        href="/schedule-guest"
                        class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-transparent px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-[#333333] transition-all duration-200 hover:border-[#333333]/15 hover:bg-[#fffff3] hover:text-[#000000] sm:px-4 sm:text-sm"
                    >
                        <Stethoscope class="size-4 shrink-0 text-[#000000]" />
                        <span class="whitespace-nowrap">Cari Dokter</span>
                    </Link>

                    <!-- Trigger Mega Menu Layanan & Spesialisasi (Active Section) -->
                    <div
                        class="relative"
                        @mouseenter="isMegaMenuOpen = true"
                        @mouseleave="isMegaMenuOpen = false"
                    >
                        <button
                            type="button"
                            @click="isMegaMenuOpen = !isMegaMenuOpen"
                            class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-[#000000] bg-[#000000] px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-all duration-200 focus:outline-none sm:px-4 sm:text-sm"
                        >
                            <span
                                class="size-1.5 shrink-0 animate-pulse rounded-full bg-[#beedc0]"
                            />
                            <Activity class="size-4 shrink-0 text-[#beedc0]" />
                            <span class="whitespace-nowrap"
                                >Layanan &amp; Spesialisasi</span
                            >
                            <ChevronDown
                                class="size-3.5 shrink-0 text-white transition-transform duration-200"
                                :class="{ 'rotate-180': isMegaMenuOpen }"
                            />
                        </button>

                        <!-- MEGA MENU DROPDOWN PANEL (with hover bridge pt-2 - 3 Columns) -->
                        <div
                            v-show="isMegaMenuOpen"
                            class="absolute top-full left-1/2 z-50 w-[820px] -translate-x-1/2 animate-in pt-2 duration-150 fade-in slide-in-from-top-2"
                        >
                            <div
                                class="space-y-5 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 text-xs text-[#000000] shadow-2xl"
                            >
                                <div class="grid grid-cols-3 gap-6">
                                    <!-- Col 1: Poliklinik Spesialis (Navigasi ke /teams?poli=...) -->
                                    <div class="space-y-3">
                                        <div
                                            class="flex items-center justify-between border-b border-[#333333]/10 pb-1.5 whitespace-nowrap"
                                        >
                                            <span
                                                class="text-xs font-bold tracking-wider whitespace-nowrap text-[#000000] uppercase"
                                            >
                                                Poliklinik Spesialis
                                            </span>
                                            <span
                                                class="font-mono text-[10px] whitespace-nowrap text-[#333333]/60"
                                                >Tim &amp; Fasilitas</span
                                            >
                                        </div>
                                        <ul class="space-y-1">
                                            <li
                                                v-for="poli in [
                                                    'Poli Umum',
                                                    'Poli Penyakit Dalam',
                                                    'Poli Anak & Tumbuh Kembang',
                                                    'Poli Jantung & Pembuluh Darah',
                                                    'Poli Kebidanan & Kandungan',
                                                    'Poli Bedah & Ortopedi',
                                                    'Poli Gigi & Mulut',
                                                    'Poli Mata',
                                                    'Poli THT',
                                                    'Poli Saraf',
                                                ]"
                                                :key="poli"
                                            >
                                                <button
                                                    type="button"
                                                    @click="
                                                        jumpToPoliTeam(poli)
                                                    "
                                                    class="group flex w-full cursor-pointer items-center justify-between rounded-[6px] px-2.5 py-1.5 text-left whitespace-nowrap text-[#333333] transition-colors hover:bg-[#edede2] hover:text-[#000000]"
                                                    :class="{
                                                        'bg-[#edede2] font-bold text-[#000000]':
                                                            safeCurrentPoli.name
                                                                .toLowerCase()
                                                                .includes(
                                                                    poli.toLowerCase(),
                                                                ) ||
                                                            safeCurrentPoli.short_name
                                                                .toLowerCase()
                                                                .includes(
                                                                    poli.toLowerCase(),
                                                                ),
                                                    }"
                                                >
                                                    <span
                                                        class="font-medium whitespace-nowrap transition-transform group-hover:translate-x-0.5"
                                                        >{{ poli }}</span
                                                    >
                                                    <ChevronRight
                                                        class="size-3 shrink-0 text-[#333333]/40 group-hover:text-[#000000]"
                                                    />
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Col 2: Pusat Layanan Unggulan (CoE) -->
                                    <div class="space-y-3">
                                        <div
                                            class="flex items-center justify-between border-b border-[#333333]/10 pb-1.5 whitespace-nowrap"
                                        >
                                            <span
                                                class="text-xs font-bold tracking-wider whitespace-nowrap text-[#000000] uppercase"
                                            >
                                                Pusat Unggulan (CoE)
                                            </span>
                                            <span
                                                class="font-mono text-[10px] whitespace-nowrap text-[#333333]/60"
                                                >Subspesialis</span
                                            >
                                        </div>
                                        <ul class="space-y-2">
                                            <li
                                                v-for="center in centersOfExcellence"
                                                :key="center.title"
                                            >
                                                <Link
                                                    href="/#pusat-unggulan"
                                                    @click="
                                                        isMegaMenuOpen = false
                                                    "
                                                    class="group flex w-full cursor-pointer items-center gap-2.5 rounded-[6px] p-2 text-left whitespace-nowrap transition-colors hover:bg-[#edede2]"
                                                >
                                                    <span
                                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                                    >
                                                        <component
                                                            :is="center.icon"
                                                            class="size-3.5"
                                                        />
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <span
                                                            class="block truncate font-semibold whitespace-nowrap text-[#000000] group-hover:underline"
                                                        >
                                                            {{ center.title }}
                                                        </span>
                                                        <span
                                                            class="block truncate text-[10px] whitespace-nowrap text-[#333333]/70"
                                                        >
                                                            {{
                                                                center.subtitle
                                                            }}
                                                        </span>
                                                    </div>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Col 3: Fasilitas Penunjang & Diagnostik -->
                                    <div
                                        class="flex flex-col justify-between space-y-3 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-4"
                                    >
                                        <div class="space-y-3">
                                            <div
                                                class="border-b border-[#333333]/15 pb-1.5 whitespace-nowrap"
                                            >
                                                <span
                                                    class="block text-xs font-bold tracking-wider whitespace-nowrap text-[#000000] uppercase"
                                                >
                                                    Fasilitas &amp; Penunjang
                                                </span>
                                            </div>
                                            <ul class="space-y-2">
                                                <li
                                                    v-for="fac in hospitalFacilities"
                                                    :key="fac.title"
                                                >
                                                    <Link
                                                        href="/#fasilitas"
                                                        @click="
                                                            isMegaMenuOpen = false
                                                        "
                                                        class="group flex w-full cursor-pointer items-center gap-2 rounded-[6px] p-2 text-left whitespace-nowrap transition-colors hover:bg-[#fffff3]"
                                                    >
                                                        <component
                                                            :is="fac.icon"
                                                            class="size-3.5 shrink-0 text-[#000000]"
                                                        />
                                                        <span
                                                            class="truncate font-medium whitespace-nowrap text-[#333333] group-hover:text-[#000000]"
                                                        >
                                                            {{ fac.title }}
                                                        </span>
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                        <div
                                            class="border-t border-[#333333]/10 pt-3"
                                        >
                                            <Link
                                                href="/#fasilitas"
                                                @click="isMegaMenuOpen = false"
                                                class="flex cursor-pointer items-center justify-between text-xs font-bold whitespace-nowrap text-[#000000] hover:underline"
                                            >
                                                <span
                                                    >Lihat Seluruh
                                                    Fasilitas</span
                                                >
                                                <ChevronRight
                                                    class="size-3.5 shrink-0"
                                                />
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom Quick Action Banner inside Mega Menu -->
                                <div
                                    class="flex items-center justify-between gap-4 border-t border-[#333333]/10 pt-3 text-xs whitespace-nowrap text-[#333333]"
                                >
                                    <span class="whitespace-nowrap"
                                        >Ingin melihat profil spesialisasi dan
                                        teknologi tindakan medis?</span
                                    >
                                    <Link
                                        href="/specializations"
                                        @click="isMegaMenuOpen = false"
                                        class="inline-flex shrink-0 cursor-pointer items-center gap-1 font-semibold whitespace-nowrap text-[#000000] underline underline-offset-4 hover:text-[#333333]"
                                    >
                                        <span>Buka Sub-Spesialisasi Medis</span>
                                        <ArrowRight class="size-3.5 shrink-0" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Lokasi Klinik -->
                    <Link
                        href="/clinic-location"
                        class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-transparent px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-[#333333] transition-all duration-200 hover:border-[#333333]/15 hover:bg-[#fffff3] hover:text-[#000000] sm:px-4 sm:text-sm"
                    >
                        <MapPin class="size-4 shrink-0 text-[#000000]" />
                        <span class="whitespace-nowrap">Lokasi Klinik</span>
                    </Link>

                    <!-- Link Cerita Pasien -->
                    <Link
                        href="/patient-story"
                        class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-transparent px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-[#333333] transition-all duration-200 hover:border-[#333333]/15 hover:bg-[#fffff3] hover:text-[#000000] sm:px-4 sm:text-sm"
                    >
                        <HeartHandshake
                            class="size-4 shrink-0 text-[#000000]"
                        />
                        <span class="whitespace-nowrap">Cerita Pasien</span>
                    </Link>
                </nav>

                <!-- Dynamic Auth Buttons (Desktop & Tablet) -->
                <div
                    class="hidden shrink-0 items-center gap-2.5 whitespace-nowrap sm:flex"
                >
                    <!-- If User is Authenticated -->
                    <template v-if="currentUser">
                        <Link
                            :href="
                                isStaffUser ? '/staff' : '/patient/dashboard'
                            "
                            class="inline-flex min-h-[44px] items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333]"
                        >
                            <User class="size-3.5 shrink-0 text-[#beedc0]" />
                            <span class="whitespace-nowrap">{{
                                isStaffUser ? 'Dashboard Staf' : 'Portal Pasien'
                            }}</span>
                        </Link>
                    </template>

                    <!-- If User is Guest -->
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-flex min-h-[44px] items-center gap-1.5 rounded-[40.5px] border border-[#000000] bg-transparent px-4 py-2 text-xs font-semibold whitespace-nowrap text-[#000000] transition-colors hover:bg-[#000000] hover:text-white sm:px-5"
                        >
                            <LogIn class="size-3.5 shrink-0" />
                            <span class="whitespace-nowrap">Masuk</span>
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex min-h-[44px] items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333] sm:px-6"
                        >
                            <UserPlus
                                class="size-3.5 shrink-0 text-[#beedc0]"
                            />
                            <span class="whitespace-nowrap">Daftar Akun</span>
                        </Link>
                    </template>
                </div>

                <!-- Mobile & iPad Hamburger Toggle (< lg) -->
                <div class="flex items-center gap-2 lg:hidden">
                    <button
                        type="button"
                        @click="openMobileMenu"
                        aria-label="Buka Menu Navigasi"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl border border-[#333333]/20 bg-[#fffff3] text-[#000000] shadow-xs transition-all hover:bg-[#edede2] active:scale-95"
                    >
                        <Menu class="size-6 text-[#000000]" />
                    </button>
                </div>
            </div>
        </motion.header>

        <!-- ═══════════════════════════════════════════════════════════
             MOBILE & IPAD OFF-CANVAS DRAWER (< lg)
             ═══════════════════════════════════════════════════════════ -->
        <Teleport to="body" v-if="isMounted">
            <!-- Backdrop Overlay -->
            <Transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isMobileMenuOpen"
                    @click="closeMobileMenu"
                    class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs lg:hidden"
                    aria-hidden="true"
                />
            </Transition>

            <!-- Drawer Panel -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <aside
                    v-if="isMobileMenuOpen"
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[340px] flex-col justify-between overflow-y-auto border-l border-[#333333]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-[400px] lg:hidden"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu Navigasi Mobile"
                >
                    <!-- 1. Drawer Header -->
                    <div
                        class="flex items-center justify-between border-b border-[#333333]/10 pb-4"
                    >
                        <Link
                            href="/"
                            @click="closeMobileMenu"
                            class="flex items-center gap-2.5"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-[#333333]/10 bg-[#beedc0]"
                            >
                                <AppLogoIcon
                                    class="size-5 fill-current text-[#000000]"
                                />
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="font-['ivypresto-headline'] font-serif text-base leading-none font-bold text-[#000000]"
                                >
                                    Hospital Population
                                </span>
                                <span
                                    class="mt-0.5 text-[10px] font-semibold tracking-wider text-[#333333]/70 uppercase"
                                >
                                    Pelayanan Terpadu
                                </span>
                            </div>
                        </Link>

                        <button
                            type="button"
                            @click="closeMobileMenu"
                            aria-label="Tutup Menu"
                            class="flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl text-[#000000]/70 transition-colors hover:bg-[#edede2] hover:text-[#000000]"
                        >
                            <X class="size-6" />
                        </button>
                    </div>

                    <!-- 2. Drawer Body (Scrollable navigation list) -->
                    <div class="my-4 flex-1 space-y-4 overflow-y-auto pr-1">
                        <!-- User Card / Auth Buttons -->
                        <div
                            v-if="currentUser"
                            class="space-y-2.5 rounded-2xl border border-[#333333]/10 bg-[#edede2]/60 p-3.5"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-[#000000] text-sm font-bold text-[#beedc0] shadow-xs"
                                >
                                    {{
                                        currentUser.name
                                            ? currentUser.name
                                                  .charAt(0)
                                                  .toUpperCase()
                                            : 'U'
                                    }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="truncate text-xs font-bold text-[#000000]"
                                    >
                                        {{ currentUser.name }}
                                    </div>
                                    <div
                                        class="truncate text-[11px] text-[#333333]/70"
                                    >
                                        {{ currentUser.email }}
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-2 pt-1">
                                <Link
                                    :href="
                                        isStaffUser
                                            ? '/staff'
                                            : '/patient/dashboard'
                                    "
                                    @click="closeMobileMenu"
                                    class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-[#333333]"
                                >
                                    <User class="size-4 text-[#beedc0]" />
                                    <span>{{
                                        isStaffUser
                                            ? 'Buka Dashboard Staf'
                                            : 'Buka Portal Pasien'
                                    }}</span>
                                </Link>
                                <button
                                    type="button"
                                    @click="handleLogout"
                                    class="flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 text-xs font-semibold text-rose-800 transition-colors hover:bg-rose-100"
                                >
                                    <LogOut class="size-3.5" />
                                    <span>Keluar Akun</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-2.5">
                            <Link
                                href="/login"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl border border-[#000000] bg-transparent px-3 py-2 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <LogIn class="size-4" />
                                <span>Masuk</span>
                            </Link>
                            <Link
                                href="/register"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-center gap-1.5 rounded-xl bg-[#000000] px-3 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-[#333333]"
                            >
                                <UserPlus class="size-4 text-[#beedc0]" />
                                <span>Daftar Akun</span>
                            </Link>
                        </div>

                        <!-- Emergency Hotline Banner -->
                        <div
                            class="space-y-2 rounded-2xl border border-red-200 bg-red-50 p-3"
                        >
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="flex items-center gap-1.5 font-bold text-red-800"
                                >
                                    <span
                                        class="flex h-2 w-2 animate-ping rounded-full bg-red-600"
                                    />
                                    <span>IGD & Ambulans 24 Jam</span>
                                </span>
                                <a
                                    href="tel:1500181"
                                    class="font-mono text-xs font-bold text-red-900 underline"
                                >
                                    1-500-181
                                </a>
                            </div>
                            <a
                                href="https://wa.me/6281100000000"
                                target="_blank"
                                rel="noopener"
                                class="flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-emerald-700"
                            >
                                <MessageSquare class="size-3.5" />
                                <span>WhatsApp Care: 0811-0000-0000</span>
                            </a>
                        </div>

                        <!-- Main Nav Items List -->
                        <div class="space-y-1 pt-1">
                            <!-- Cari Dokter -->
                            <Link
                                href="/schedule-guest"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-[#beedc0] text-[#000000]"
                                    >
                                        <Stethoscope class="size-4" />
                                    </div>
                                    <span>Cari Dokter Spesialis</span>
                                </div>
                                <ChevronRight
                                    class="size-4 text-[#333333]/50"
                                />
                            </Link>

                            <!-- Lokasi Klinik -->
                            <Link
                                href="/clinic-location"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-[#edede2] text-[#000000]"
                                    >
                                        <MapPin class="size-4" />
                                    </div>
                                    <span>Lokasi Klinik & Kontak</span>
                                </div>
                                <ChevronRight
                                    class="size-4 text-[#333333]/50"
                                />
                            </Link>

                            <!-- Cerita Pasien -->
                            <Link
                                href="/patient-story"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-pink-100 text-pink-700"
                                    >
                                        <HeartHandshake class="size-4" />
                                    </div>
                                    <span>Cerita & Testimoni Pasien</span>
                                </div>
                                <ChevronRight
                                    class="size-4 text-[#333333]/50"
                                />
                            </Link>
                        </div>

                        <!-- Expandable / Accordion Layanan & Spesialisasi -->
                        <div
                            class="space-y-2 border-t border-[#333333]/10 pt-2"
                        >
                            <div
                                class="px-3 text-[11px] font-bold tracking-wider text-[#333333]/60 uppercase"
                            >
                                Layanan & Spesialisasi
                            </div>

                            <!-- Poliklinik List Accordion -->
                            <div
                                class="overflow-hidden rounded-2xl border border-[#333333]/10 bg-[#edede2]/40"
                            >
                                <button
                                    type="button"
                                    @click="
                                        isMobilePoliOpen = !isMobilePoliOpen
                                    "
                                    class="flex min-h-[44px] w-full cursor-pointer items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2]"
                                >
                                    <div class="flex items-center gap-2">
                                        <Activity
                                            class="size-4 text-[#000000]"
                                        />
                                        <span>Poliklinik Spesialis</span>
                                    </div>
                                    <ChevronDown
                                        class="size-4 transition-transform"
                                        :class="{
                                            'rotate-180': isMobilePoliOpen,
                                        }"
                                    />
                                </button>
                                <div
                                    v-show="isMobilePoliOpen"
                                    class="space-y-1 border-t border-[#333333]/5 px-3 pt-1 pb-3"
                                >
                                    <button
                                        v-for="poli in [
                                            'Poli Umum',
                                            'Poli Penyakit Dalam',
                                            'Poli Anak & Tumbuh Kembang',
                                            'Poli Jantung & Pembuluh Darah',
                                            'Poli Kebidanan & Kandungan',
                                            'Poli Bedah & Ortopedi',
                                            'Poli Gigi & Mulut',
                                            'Poli Mata',
                                            'Poli THT',
                                            'Poli Saraf',
                                        ]"
                                        :key="poli"
                                        type="button"
                                        @click="jumpToPoliTeam(poli)"
                                        class="flex min-h-[38px] w-full cursor-pointer items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000]"
                                    >
                                        <span>{{ poli }}</span>
                                        <ChevronRight
                                            class="size-3 text-[#333333]/40"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Pusat Unggulan Accordion -->
                            <div
                                class="overflow-hidden rounded-2xl border border-[#333333]/10 bg-[#edede2]/40"
                            >
                                <button
                                    type="button"
                                    @click="isMobileCoEOpen = !isMobileCoEOpen"
                                    class="flex min-h-[44px] w-full cursor-pointer items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2]"
                                >
                                    <div class="flex items-center gap-2">
                                        <Award class="size-4 text-[#000000]" />
                                        <span>Pusat Unggulan (CoE)</span>
                                    </div>
                                    <ChevronDown
                                        class="size-4 transition-transform"
                                        :class="{
                                            'rotate-180': isMobileCoEOpen,
                                        }"
                                    />
                                </button>
                                <div
                                    v-show="isMobileCoEOpen"
                                    class="space-y-1 border-t border-[#333333]/5 px-3 pt-1 pb-3"
                                >
                                    <button
                                        v-for="center in centersOfExcellence"
                                        :key="center.title"
                                        type="button"
                                        @click="
                                            isMobileMenuOpen = false;
                                            router.visit('/#pusat-unggulan');
                                        "
                                        class="flex min-h-[38px] w-full cursor-pointer items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000]"
                                    >
                                        <span>{{ center.title }}</span>
                                        <ChevronRight
                                            class="size-3 text-[#333333]/40"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Fasilitas Penunjang Accordion -->
                            <div
                                class="overflow-hidden rounded-2xl border border-[#333333]/10 bg-[#edede2]/40"
                            >
                                <button
                                    type="button"
                                    @click="
                                        isMobileFacilitiesOpen =
                                            !isMobileFacilitiesOpen
                                    "
                                    class="flex min-h-[44px] w-full cursor-pointer items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2]"
                                >
                                    <div class="flex items-center gap-2">
                                        <Hospital
                                            class="size-4 text-[#000000]"
                                        />
                                        <span>Fasilitas & Penunjang</span>
                                    </div>
                                    <ChevronDown
                                        class="size-4 transition-transform"
                                        :class="{
                                            'rotate-180':
                                                isMobileFacilitiesOpen,
                                        }"
                                    />
                                </button>
                                <div
                                    v-show="isMobileFacilitiesOpen"
                                    class="space-y-1 border-t border-[#333333]/5 px-3 pt-1 pb-3"
                                >
                                    <button
                                        v-for="fac in hospitalFacilities"
                                        :key="fac.title"
                                        type="button"
                                        @click="
                                            isMobileMenuOpen = false;
                                            router.visit('/#fasilitas');
                                        "
                                        class="flex min-h-[38px] w-full cursor-pointer items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000]"
                                    >
                                        <span>{{ fac.title }}</span>
                                        <ChevronRight
                                            class="size-3 text-[#333333]/40"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Sub-Spesialisasi Link -->
                            <Link
                                href="/specializations"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] w-full cursor-pointer items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <Sparkles class="size-4 text-[#000000]" />
                                    <span>Sub-Spesialisasi Medis</span>
                                </div>
                                <ChevronRight
                                    class="size-4 text-[#333333]/50"
                                />
                            </Link>
                        </div>
                    </div>

                    <!-- 3. Drawer Footer -->
                    <div class="space-y-2 border-t border-[#333333]/10 pt-4">
                        <div
                            class="flex items-center justify-between text-[11px] text-[#333333]/70"
                        >
                            <span
                                class="flex items-center gap-1 font-semibold text-[#000000]"
                            >
                                <Award class="size-3.5 text-[#000000]" />
                                <span>Akreditasi KARS Paripurna</span>
                            </span>
                            <span>v2.0</span>
                        </div>
                    </div>
                </aside>
            </Transition>
        </Teleport>

        <main class="space-y-12 pt-8 pb-20 sm:space-y-16 sm:pt-12">
            <!-- ═══════════════════════════════════════════════════════
                 4. POLIKLINIK EDITORIAL HERO & LEADERSHIP SPOTLIGHT
                 ═══════════════════════════════════════════════════════ -->
            <section class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
                <motion.div
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25, ease: 'easeOut' }"
                    class="space-y-8 rounded-[12px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm sm:p-10 lg:p-12"
                >
                    <!-- Top Category & Location Badges -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-[#333333]/10 pb-4"
                    >
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-1 text-xs font-bold text-[#000000]"
                            >
                                <Hospital class="size-3.5" />
                                {{ safeCurrentPoli.badge }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#000000] px-3.5 py-1 text-xs font-semibold text-white"
                            >
                                <MapPin class="size-3 text-[#beedc0]" />
                                {{ safeCurrentPoli.floor }}
                            </span>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-[#333333]"
                        >
                            <Clock class="size-3.5 text-[#000000]" />
                            <span>{{
                                safeCurrentPoli.operating_hours ||
                                'Senin - Sabtu: 08:00 - 20:00'
                            }}</span>
                        </span>
                    </div>

                    <!-- Headline & Overview Description -->
                    <div class="max-w-4xl space-y-4">
                        <div class="space-y-1">
                            <span
                                class="block font-mono text-xs font-bold tracking-wider text-[#333333]/70 uppercase"
                            >
                                {{ safeCurrentPoli.code }} • Instalasi Rawat
                                Jalan Terpadu
                            </span>
                            <h1
                                class="font-['ivypresto-headline'] font-serif text-3xl leading-tight font-bold text-[#000000] sm:text-4xl md:text-5xl"
                            >
                                {{ safeCurrentPoli.name }}
                            </h1>
                        </div>

                        <p
                            class="text-sm leading-relaxed text-[#333333] sm:text-base"
                        >
                            {{ safeCurrentPoli.description }}
                        </p>
                    </div>

                    <!-- Leadership Spotlight (Kepala Poli & Perawat Penanggung Jawab) -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Kepala Poli / Dokter Penanggung Jawab -->
                        <div
                            class="flex items-start gap-3.5 rounded-[10px] border border-[#333333]/10 bg-[#edede2]/60 p-4 sm:p-5"
                        >
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#000000] font-serif text-lg font-bold text-white"
                            >
                                {{
                                    (safeCurrentPoli.head_doctor || 'Dokter')
                                        .replace(/^(dr\.|drg\.|Prof\.)/i, '')
                                        .trim()
                                        .charAt(0)
                                }}
                            </div>
                            <div class="min-w-0 flex-1 space-y-0.5">
                                <span
                                    class="block text-[10px] font-bold tracking-wider text-[#333333]/70 uppercase"
                                >
                                    Dokter Penanggung Jawab Unit
                                </span>
                                <h4
                                    class="truncate font-['ivypresto-headline'] font-serif text-base font-bold text-[#000000] sm:text-lg"
                                >
                                    {{
                                        safeCurrentPoli.head_doctor ||
                                        'dr. Penanggung Jawab'
                                    }}
                                </h4>
                                <p class="text-xs leading-snug text-[#333333]">
                                    {{
                                        safeCurrentPoli.head_doctor_title ||
                                        'Kepala Poliklinik & DPJP Utama'
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Perawat Penanggung Jawab Unit -->
                        <div
                            class="flex items-start gap-3.5 rounded-[10px] border border-[#333333]/10 bg-[#edede2]/60 p-4 sm:p-5"
                        >
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-[#beedc0] font-serif text-lg font-bold text-[#000000]"
                            >
                                {{
                                    (safeCurrentPoli.head_nurse || 'Perawat')
                                        .replace(/^(Ns\.|Bdn\.)/i, '')
                                        .trim()
                                        .charAt(0)
                                }}
                            </div>
                            <div class="min-w-0 flex-1 space-y-0.5">
                                <span
                                    class="block text-[10px] font-bold tracking-wider text-[#333333]/70 uppercase"
                                >
                                    Perawat Kepala Poliklinik
                                </span>
                                <h4
                                    class="truncate font-['ivypresto-headline'] font-serif text-base font-bold text-[#000000] sm:text-lg"
                                >
                                    {{
                                        safeCurrentPoli.head_nurse ||
                                        'Ns. Penanggung Jawab'
                                    }}
                                </h4>
                                <p class="text-xs leading-snug text-[#333333]">
                                    {{
                                        safeCurrentPoli.head_nurse_title ||
                                        'Kepala Ruang Rawat Jalan'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Quick Metric Cards Grid -->
                    <div class="grid grid-cols-2 gap-4 pt-2 lg:grid-cols-4">
                        <div
                            v-for="metric in safeCurrentPoli.metrics || [
                                {
                                    label: 'Kapasitas Pasien',
                                    value: '40/Hari',
                                    desc: 'Pelayanan prima',
                                },
                                {
                                    label: 'Waktu Tunggu',
                                    value: '< 15 Menit',
                                    desc: 'Efisiensi antrean',
                                },
                                {
                                    label: 'Ruang Tindakan',
                                    value: '3 Unit',
                                    desc: 'Fasilitas terpadu',
                                },
                                {
                                    label: 'Kepuasan Pasien',
                                    value: '99.2%',
                                    desc: 'Indeks kepuasan',
                                },
                            ]"
                            :key="metric.label"
                            class="flex flex-col justify-between space-y-1 rounded-[8px] border border-[#333333]/10 bg-white p-4 shadow-2xs"
                        >
                            <span
                                class="block font-['ivypresto-headline'] font-serif text-2xl font-bold text-[#000000] sm:text-3xl"
                            >
                                {{ metric.value }}
                            </span>
                            <div>
                                <h4
                                    class="text-xs leading-snug font-semibold text-[#000000]"
                                >
                                    {{ metric.label }}
                                </h4>
                                <p class="mt-0.5 text-[11px] text-[#333333]/70">
                                    {{ metric.desc }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button
                            type="button"
                            @click="scrollToSection('jadwal-dokter-poli')"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333] sm:text-sm"
                        >
                            <Stethoscope class="size-4 text-[#beedc0]" />
                            <span>Lihat Dokter &amp; Ambil Antrean</span>
                            <ArrowRight class="size-4" />
                        </button>

                        <button
                            type="button"
                            @click="scrollToSection('cakupan-layanan')"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-[#333333]/20 bg-white px-6 py-2.5 text-xs font-semibold whitespace-nowrap text-[#000000] transition-colors hover:bg-[#edede2] sm:text-sm"
                        >
                            <Building2 class="size-4 text-[#000000]" />
                            <span>Cakupan Layanan &amp; Ruang Periksa</span>
                        </button>
                    </div>
                </motion.div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 5. CAKUPAN LAYANAN & RUANGAN PEMERIKSAAN (#cakupan-layanan)
                 ═══════════════════════════════════════════════════════ -->
            <section
                id="cakupan-layanan"
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Left: Cakupan Tindakan & Layanan Medis -->
                    <div
                        class="space-y-6 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-2xs sm:p-8"
                    >
                        <div
                            class="space-y-1 border-b border-[#333333]/10 pb-3"
                        >
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                            >
                                <Sparkles class="size-3.5" />
                                Standar Pelayanan Klinis
                            </span>
                            <h3
                                class="font-['ivypresto-headline'] font-serif text-2xl font-bold text-[#000000]"
                            >
                                Cakupan Layanan &amp; Tindakan Medis
                            </h3>
                        </div>

                        <ul class="space-y-3">
                            <li
                                v-for="scope in safeCurrentPoli.scope_services ||
                                []"
                                :key="scope"
                                class="flex items-start gap-2.5 text-xs text-[#333333] sm:text-sm"
                            >
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0 text-[#000000]"
                                />
                                <span>{{ scope }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Right: Ruangan & Fasilitas Poliklinik -->
                    <div
                        class="space-y-6 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-2xs sm:p-8"
                    >
                        <div
                            class="space-y-1 border-b border-[#333333]/10 pb-3"
                        >
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#000000] px-3 py-0.5 text-xs font-semibold text-white"
                            >
                                <Building2 class="size-3.5 text-[#beedc0]" />
                                Fasilitas Fisik
                            </span>
                            <h3
                                class="font-['ivypresto-headline'] font-serif text-2xl font-bold text-[#000000]"
                            >
                                Ruang Periksa &amp; Tindakan Khusus
                            </h3>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="room in safeCurrentPoli.rooms || []"
                                :key="room.code"
                                class="flex items-start justify-between gap-3 rounded-[8px] border border-[#333333]/15 bg-white p-3.5 shadow-2xs transition-colors hover:border-[#000000]"
                            >
                                <div class="min-w-0 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                                        >
                                            {{ room.name }}
                                        </span>
                                        <span
                                            class="rounded-[4px] bg-[#edede2] px-2 py-0.5 font-mono text-[10px] font-bold text-[#000000]"
                                        >
                                            {{ room.code }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs leading-relaxed text-[#333333]"
                                    >
                                        {{ room.desc }}
                                    </p>
                                </div>
                                <span
                                    class="mt-1.5 size-2 shrink-0 rounded-full bg-emerald-500"
                                    title="Aktif Beroperasi"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 6. JADWAL DOKTER & RESERVASI POLIKLINIK
                 ═══════════════════════════════════════════════════════ -->
            <section
                id="jadwal-dokter-poli"
                class="mx-auto max-w-[1200px] scroll-mt-24 space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <!-- Section Header & Filter Controls -->
                <div
                    class="flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl space-y-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            <Calendar class="size-3.5" />
                            Jadwal Praktik Dokter Terpadu
                        </span>
                        <h2
                            class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl"
                        >
                            Dokter Spesialis {{ safeCurrentPoli.name }}
                        </h2>
                        <p class="text-xs text-[#333333] sm:text-sm">
                            Pilih jadwal praktik dokter penanggung jawab
                            pelayanan (DPJP) untuk konsultasi tatap muka atau
                            ambil tiket antrean.
                        </p>
                    </div>

                    <!-- Search & Filter Bar -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative min-w-[220px]">
                            <Search
                                class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#333333]/50"
                            />
                            <input
                                v-model="doctorSearchQuery"
                                type="text"
                                placeholder="Cari nama dokter..."
                                class="w-full rounded-[40.5px] border border-[#333333]/20 bg-white py-2 pr-4 pl-10 text-xs placeholder-[#333333]/40 transition-colors focus:border-[#000000] focus:ring-0"
                            />
                        </div>

                        <select
                            v-model="selectedDayFilter"
                            class="rounded-[40.5px] border border-[#333333]/20 bg-white py-2 pr-8 pl-4 text-xs transition-colors focus:border-[#000000] focus:ring-0"
                        >
                            <option
                                v-for="day in [
                                    'Semua',
                                    'Senin',
                                    'Selasa',
                                    'Rabu',
                                    'Kamis',
                                    'Jumat',
                                    'Sabtu',
                                    'Minggu',
                                ]"
                                :key="day"
                                :value="day"
                            >
                                {{ day === 'Semua' ? 'Semua Hari' : day }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Schedule Cards Grid jika data live ditemukan -->
                <div
                    v-if="filteredSchedules.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <motion.div
                        v-for="sch in filteredSchedules"
                        :key="sch.id || sch.doctor_schedule_id"
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -2 }"
                        :whileTap="{ scale: 0.985 }"
                        :transition="{ duration: 0.2, ease: 'easeOut' }"
                        class="flex flex-col justify-between space-y-5 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-2xs transition-colors hover:border-[#000000]"
                    >
                        <div class="space-y-4">
                            <!-- Doctor Avatar & Name -->
                            <div class="flex items-start gap-3.5">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#000000] font-serif text-base font-bold text-white"
                                >
                                    {{
                                        (sch.doctor?.name || 'Dokter')
                                            .replace(
                                                /^(dr\.|drg\.|Prof\.)/i,
                                                '',
                                            )
                                            .trim()
                                            .charAt(0)
                                    }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="truncate font-['ivypresto-headline'] font-serif text-base font-bold text-[#000000]"
                                    >
                                        {{
                                            sch.doctor?.name ||
                                            'Dokter Spesialis'
                                        }}
                                    </h3>
                                    <span
                                        class="block truncate text-xs font-medium text-[#333333]"
                                    >
                                        {{
                                            typeof sch.doctor
                                                ?.specialization === 'string'
                                                ? sch.doctor.specialization
                                                : sch.doctor?.specialization
                                                      ?.name_specialization ||
                                                  safeCurrentPoli.name
                                        }}
                                    </span>
                                    <span
                                        v-if="sch.doctor?.sip_number"
                                        class="block font-mono text-[10px] text-[#333333]/60"
                                    >
                                        SIP: {{ sch.doctor.sip_number }}
                                    </span>
                                </div>
                            </div>

                            <!-- Schedule & Poli Details -->
                            <div
                                class="space-y-2 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-3.5 text-xs"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#333333]/70"
                                        >Poliklinik:</span
                                    >
                                    <span
                                        class="max-w-[160px] truncate font-semibold text-[#000000]"
                                    >
                                        {{
                                            sch.poli?.name ||
                                            sch.poli?.name_poli ||
                                            safeCurrentPoli.name
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#333333]/70"
                                        >Ruangan:</span
                                    >
                                    <span class="font-semibold text-[#000000]">
                                        {{
                                            sch.room?.name ||
                                            sch.room?.name_room ||
                                            safeCurrentPoli.rooms[0]?.code ||
                                            'Ruang Konsultasi'
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between border-t border-[#333333]/10 pt-1"
                                >
                                    <span class="font-medium text-[#333333]/70"
                                        >Hari &amp; Jam Praktik:</span
                                    >
                                    <span
                                        class="flex items-center gap-1 font-bold text-[#000000]"
                                    >
                                        <Clock class="size-3 text-[#000000]" />
                                        {{ sch.day || sch.day_of_week }},
                                        {{ sch.start_time?.substring(0, 5) }} –
                                        {{ sch.end_time?.substring(0, 5) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Action Button -->
                        <div>
                            <button
                                type="button"
                                @click="handleBookSchedule(sch)"
                                class="inline-flex min-h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333]"
                            >
                                <Ticket class="size-3.5 text-[#beedc0]" />
                                <span>{{
                                    currentUser
                                        ? 'Ambil Nomor Antrean'
                                        : 'Masuk untuk Ambil Antrean'
                                }}</span>
                            </button>
                        </div>
                    </motion.div>
                </div>

                <!-- Fallback jika jadwal database belum difilter: tampilkan tim dokter poliklinik -->
                <div
                    v-else
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="doc in safeCurrentPoli.team_doctors || []"
                        :key="doc.name"
                        class="flex flex-col justify-between space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-2xs"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#beedc0] font-bold text-[#000000]"
                                >
                                    {{
                                        doc.name
                                            .replace(
                                                /^(dr\.|drg\.|Prof\.)/i,
                                                '',
                                            )
                                            .trim()
                                            .charAt(0)
                                    }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="truncate font-['ivypresto-headline'] font-serif text-base font-bold text-[#000000]"
                                    >
                                        {{ doc.name }}
                                    </h4>
                                    <span
                                        class="block text-xs font-medium text-[#333333]"
                                    >
                                        {{
                                            doc.specialty ||
                                            safeCurrentPoli.name
                                        }}
                                    </span>
                                    <span
                                        class="block font-mono text-[10px] text-[#333333]/60"
                                    >
                                        SIP: {{ doc.sip || 'SIP.DPJP/2026' }} •
                                        {{ doc.experience || 'Praktik Klinis' }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="space-y-1 rounded-[8px] bg-[#edede2]/60 p-3 text-xs"
                            >
                                <span
                                    class="block text-[11px] font-bold text-[#000000]"
                                    >Jadwal Praktik:</span
                                >
                                <span class="block text-[#333333]">{{
                                    doc.schedule || 'Senin - Jumat'
                                }}</span>
                            </div>
                        </div>

                        <Link
                            href="/schedule-guest"
                            class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] text-xs font-semibold text-white transition-colors hover:bg-[#333333]"
                        >
                            <span>Lihat Seluruh Slot Konsultasi</span>
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 7. TIM PERAWAT & PARAMEDIS POLIKLINIK
                 ═══════════════════════════════════════════════════════ -->
            <section
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div
                    class="space-y-8 rounded-[12px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm sm:p-10"
                >
                    <div class="max-w-2xl space-y-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            <HeartHandshake class="size-3.5" />
                            Pelayanan Keperawatan Berstandar
                        </span>
                        <h2
                            class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl"
                        >
                            Tim Perawat &amp; Paramedis
                            {{ safeCurrentPoli.name }}
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-[#333333] sm:text-sm"
                        >
                            Didukung perawat profesional dengan Surat Tanda
                            Registrasi (STR) aktif dan sertifikasi
                            kegawatdaruratan klinis berstandar KARS.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div
                            v-for="nurse in safeCurrentPoli.team_nurses || []"
                            :key="nurse.name"
                            class="flex flex-col justify-between space-y-3 rounded-[10px] border border-[#333333]/15 bg-white p-5 shadow-2xs transition-colors hover:border-[#000000]"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#edede2] text-sm font-bold text-[#000000]"
                                    >
                                        <Users class="size-4" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4
                                            class="truncate text-xs font-semibold text-[#000000] sm:text-sm"
                                        >
                                            {{ nurse.name }}
                                        </h4>
                                        <span
                                            class="block text-[11px] font-medium text-[#333333]/70"
                                        >
                                            {{
                                                nurse.role ||
                                                'Perawat Pelaksana'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="space-y-1 border-t border-[#333333]/10 pt-2 text-xs"
                                >
                                    <span
                                        class="block font-mono text-[10px] text-[#333333]/60"
                                    >
                                        STR: {{ nurse.str || 'STR.PRW/2026' }}
                                    </span>
                                    <span
                                        class="block text-[11px] text-[#333333]"
                                    >
                                        <strong>Kompetensi:</strong>
                                        {{
                                            nurse.cert || 'BLS / ACLS Certified'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <span
                                class="inline-flex items-center gap-1 self-start rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                            >
                                <ShieldCheck class="size-3" />
                                Terverifikasi Medis
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 8. FAQ & EDUKASI POLIKLINIK (Interactive Accordion)
                 ═══════════════════════════════════════════════════════ -->
            <section
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div class="mx-auto max-w-2xl space-y-2 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                    >
                        <HelpCircle class="size-3.5" />
                        Panduan Pasien Poliklinik
                    </span>
                    <h2
                        class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl"
                    >
                        Pertanyaan Seputar {{ safeCurrentPoli.name }}
                    </h2>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Informasi pendaftaran, persyaratan berkas, dan ketentuan
                        konsultasi di poliklinik ini.
                    </p>
                </div>

                <div class="mx-auto max-w-3xl space-y-3">
                    <div
                        v-for="(faq, fIndex) in safeCurrentPoli.faqs || []"
                        :key="faq.q"
                        class="overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3] shadow-2xs transition-all"
                    >
                        <button
                            type="button"
                            @click="toggleFaq(fIndex)"
                            class="flex w-full cursor-pointer items-center justify-between gap-4 p-5 text-left text-sm font-semibold text-[#000000] transition-colors hover:bg-[#edede2]/40"
                        >
                            <span class="flex items-center gap-2.5">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#beedc0] text-xs font-bold text-[#000000]"
                                >
                                    Q
                                </span>
                                <span>{{ faq.q }}</span>
                            </span>
                            <ChevronDown
                                class="size-4 shrink-0 text-[#000000] transition-transform duration-200"
                                :class="{
                                    'rotate-180': openFaqIndex === fIndex,
                                }"
                            />
                        </button>

                        <div
                            v-show="openFaqIndex === fIndex"
                            class="animate-in border-t border-[#333333]/10 bg-white/70 px-5 pt-1 pb-5 text-xs leading-relaxed text-[#333333] duration-150 fade-in sm:text-sm"
                        >
                            <p class="pl-8.5">
                                {{ faq.a }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 9. BOTTOM CALL TO ACTION BANNER (Evergreen Black Pill)
                 ═══════════════════════════════════════════════════════ -->
            <section class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
                <div
                    class="space-y-6 rounded-[12px] bg-[#000000] p-8 text-center text-white sm:p-12"
                >
                    <div class="mx-auto max-w-2xl space-y-3">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            Pelayanan Rawat Jalan Paripurna
                        </span>
                        <h2
                            class="font-['ivypresto-headline'] font-serif text-3xl leading-tight font-semibold sm:text-4xl lg:text-5xl"
                        >
                            Jadwalkan Kunjungan ke {{ safeCurrentPoli.name }}
                        </h2>
                        <p
                            class="mx-auto max-w-lg text-xs leading-relaxed text-white/80 sm:text-sm"
                        >
                            Ambil nomor antrean poliklinik secara instan atau
                            konsultasikan kebutuhan rujukan Anda bersama tim
                            customer care kami.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-3"
                    >
                        <button
                            type="button"
                            @click="scrollToSection('jadwal-dokter-poli')"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-white px-7 py-3 text-xs font-semibold text-[#000000] shadow-sm transition-colors hover:bg-[#edede2] sm:text-sm"
                        >
                            <Ticket class="size-4 text-[#000000]" />
                            <span>Ambil Nomor Antrean Sekarang</span>
                            <ArrowRight class="size-4" />
                        </button>

                        <a
                            href="https://wa.me/6281100000000"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] border border-white/30 bg-transparent px-7 py-3 text-xs font-semibold text-white transition-colors hover:bg-white/10 sm:text-sm"
                        >
                            <MessageSquare class="size-4 text-[#beedc0]" />
                            <span>WhatsApp Care: 0811-0000-0000</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- ═══════════════════════════════════════════════════════════
             10. FOOTER RUMAH SAKIT MULTI-KOLOM (Siloam Style)
             ═══════════════════════════════════════════════════════════ -->
        <footer
            class="border-t border-[#333333]/15 bg-[#fffff3] pt-14 pb-8 text-[#000000]"
        >
            <div class="mx-auto max-w-[1200px] space-y-12 px-4 sm:px-6 lg:px-8">
                <!-- Main 4-Column Footer -->
                <div
                    class="grid grid-cols-1 gap-8 text-xs md:grid-cols-2 lg:grid-cols-5"
                >
                    <!-- Col 1 & 2: Hospital Identity & Mission -->
                    <div class="space-y-4 lg:col-span-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#beedc0]"
                            >
                                <AppLogoIcon
                                    class="size-6 fill-current text-[#000000]"
                                />
                            </div>
                            <div>
                                <span
                                    class="block font-['ivypresto-headline'] font-serif text-xl leading-tight font-bold text-[#000000]"
                                >
                                    Hospital Population
                                </span>
                                <span
                                    class="block text-[11px] font-medium text-[#333333]"
                                >
                                    Sistem Pelayanan &amp; Jadwal Praktik
                                    Terpadu
                                </span>
                            </div>
                        </div>

                        <p
                            class="max-w-sm text-xs leading-relaxed text-[#333333]/80"
                        >
                            Rumah sakit berstandar internasional yang
                            mengutamakan keselamatan pasien, keunggulan klinis,
                            dan kemudahan akses digital bagi seluruh lapisan
                            masyarakat.
                        </p>

                        <div class="space-y-1.5 text-xs text-[#333333]">
                            <div class="flex items-center gap-2">
                                <Award class="size-4 text-[#000000]" />
                                <span
                                    >Terakreditasi KARS Paripurna (Kementerian
                                    Kesehatan RI)</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <ShieldCheck class="size-4 text-[#000000]" />
                                <span
                                    >Sertifikasi ISO 9001:2015 Mutu
                                    Pelayanan</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Layanan Medis -->
                    <div class="space-y-3">
                        <span
                            class="block border-b border-[#333333]/15 pb-1.5 text-xs font-bold tracking-wider text-[#000000] uppercase"
                        >
                            Layanan Medis
                        </span>
                        <ul class="space-y-2 text-[#333333]">
                            <li>
                                <Link
                                    href="/teams"
                                    class="font-semibold text-[#000000] hover:text-[#000000] hover:underline"
                                >
                                    Tim Medis &amp; Poliklinik
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/schedule-guest"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Jadwal Praktik Dokter
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/specializations"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Sub-Spesialisasi Medis
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/patient-story"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Kisah &amp; Cerita Pasien
                                </Link>
                            </li>
                            <li>
                                <a
                                    href="tel:1500181"
                                    class="font-bold text-red-600 hover:underline"
                                >
                                    IGD 24 Jam: 1-500-181
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Col 4: Informasi Pasien -->
                    <div class="space-y-3">
                        <span
                            class="block border-b border-[#333333]/15 pb-1.5 text-xs font-bold tracking-wider text-[#000000] uppercase"
                        >
                            Informasi Pasien
                        </span>
                        <ul class="space-y-2 text-[#333333]">
                            <li>
                                <Link
                                    href="/login"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Portal Masuk Pasien
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/register"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Daftar Akun Baru
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Col 5: Kontak & Lokasi Darurat -->
                    <div class="space-y-3">
                        <span
                            class="block border-b border-[#333333]/15 pb-1.5 text-xs font-bold tracking-wider text-[#000000] uppercase"
                        >
                            Kontak &amp; Lokasi
                        </span>
                        <div class="space-y-2.5 text-[#333333]">
                            <div class="flex items-start gap-2">
                                <MapPin
                                    class="mt-0.5 size-4 shrink-0 text-[#000000]"
                                />
                                <span
                                    >Jl. Kesehatan Utama No. 88, Kawasan Medis
                                    Terpadu</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone class="size-4 shrink-0 text-[#000000]" />
                                <span>Hotline: (021) 500-1818</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Mail class="size-4 shrink-0 text-[#000000]" />
                                <span>care@hospitalpopulation.com</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock class="size-4 shrink-0 text-[#000000]" />
                                <span>Poliklinik: 08.00 – 21.00 WIB</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Copyright & Legal Strip -->
                <div
                    class="flex flex-col items-center justify-between gap-3 border-t border-[#333333]/10 pt-6 text-xs text-[#333333]/70 sm:flex-row"
                >
                    <p>
                        © 2026 Hospital Population. Seluruh hak cipta dilindungi
                        undang-undang.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="hover:underline"
                            >Kebijakan Privasi</a
                        >
                        <span>•</span>
                        <a href="#" class="hover:underline"
                            >Syarat &amp; Ketentuan</a
                        >
                        <span>•</span>
                        <a href="#" class="hover:underline">Hak Pasien</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Modal Dialog Reservasi Antrean Dokter -->
        <BookingModal
            v-model:open="isBookingModalOpen"
            :schedule="selectedSchedule"
            @success="handleBookingSuccess"
        />

        <!-- Modal Dialog Karcis Antrean Sukses -->
        <TicketSuccessModal
            v-model:open="isTicketModalOpen"
            :ticket="activeTicket"
        />
    </div>
</template>
