<script setup lang="ts">
/**
 * @file Welcome.vue
 * @description Landing page for Hospital Population web platform inspired by modern
 * hospital portals (Siloam Hospitals style) with strict adherence to DESIGN.md (Evergreen theme).
 *
 * Key Highlights:
 *  - Layout: Standalone layout without sidebar (defineOptions({ layout: null }))
 *  - Motion: Motion-V interactive transitions and micro-animations
 *  - Colors: Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Ink Black (#000000)
 *  - Typography: IvyPresto Headline display serif and Rubik body sans-serif
 *  - Siloam-inspired Modules:
 *      * Top Utility & Emergency Bar (24h Hotline, Emergency WhatsApp, Display TV link)
 *      * Main Header with Mega Menu (Poliklinik, Pusat Unggulan, Penunjang, Rawat Inap)
 *      * Dynamic Auth Controls (Masuk/Daftar vs Antrean Saya/Dashboard)
 *      * Hero Section (Headline & Pelayanan Terpadu)
 *      * 4 Quick Access Service Cards (Pendaftaran Online, IGD 24 Jam, Jadwal Spesialis, Rawat Inap)
 *      * 6 Centers of Excellence (Pusat Layanan Unggulan)
 *      * Medical Facilities Showcase (Laboratorium, Radiologi, Farmasi, Ambulans)
 *      * Patient Care & FAQ Guide
 *      * Comprehensive Multi-Column Hospital Footer
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Activity,
    Ambulance,
    ArrowRight,
    Award,
    Bed,
    Building2,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    Clock,
    Droplets,
    FileText,
    Heart,
    HeartHandshake,
    HeartPulse,
    HelpCircle,
    Hospital,
    LogIn,
    LogOut,
    Mail,
    MapPin,
    Menu,
    MessageSquare,
    Phone,
    PhoneCall,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    Ticket,
    Tv,
    User,
    UserPlus,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { DoctorSchedule } from '@/types';

// Wajib mandiri tanpa sidebar default
defineOptions({
    layout: undefined,
});

/* ═══════════════════════════════════════════════════════════════
   Props & Backend Data
   ═══════════════════════════════════════════════════════════════ */
const props = withDefaults(
    defineProps<{
        schedules?: DoctorSchedule[];
    }>(),
    {
        schedules: () => [],
    },
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
const isMounted = ref(false);
const isMegaMenuOpen = ref(false);
const isMobileMenuOpen = ref(false);
const isMobilePoliOpen = ref(false);
const isMobileCoEOpen = ref(false);
const isMobileFacilitiesOpen = ref(false);

const openMobileMenu = () => {
    isMobileMenuOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

// Auto-close on Escape key
const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileMenuOpen.value) {
        closeMobileMenu();
    }
};

// Auto-close on Inertia navigation
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

/**
 * Quick jump to Poliklinik & Medical Team Profile page (/teams?poli=...)
 */
const jumpToPoliTeam = (poliName: string) => {
    isMegaMenuOpen.value = false;
    closeMobileMenu();
    router.visit('/teams?poli=' + encodeURIComponent(poliName));
};

/**
 * Quick jump to Doctor Catalog page with preselected poli / keyword
 */
const jumpToDoctorSearch = (poliOrKeyword?: string) => {
    isMegaMenuOpen.value = false;
    closeMobileMenu();

    if (poliOrKeyword) {
        router.visit(
            '/schedule-guest?poli=' + encodeURIComponent(poliOrKeyword),
        );
    } else {
        router.visit('/schedule-guest');
    }
};

/**
 * Smooth scroll helper with sticky header offset
 */
const scrollToSection = (sectionId: string) => {
    isMegaMenuOpen.value = false;
    closeMobileMenu();
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

/* ═══════════════════════════════════════════════════════════════
   Static Data for Siloam Style Sections
   ═══════════════════════════════════════════════════════════════ */

const quickActionCards = [
    {
        title: 'Pendaftaran Antrean Online',
        subtitle: 'Reservasi nomor antrean dokter tanpa menunggu di loket',
        icon: Ticket,
        badge: 'Cepat & Praktis',
        actionText: 'Ambil Antrean',
        href: '/schedule-guest',
    },
    {
        title: 'IGD & Ambulans 24 Jam',
        subtitle: 'Penanganan gawat darurat medis cepat & armada siaga',
        icon: Ambulance,
        badge: 'Hotline 1-500-181',
        actionText: 'Hubungi IGD',
        isEmergency: true,
        href: 'tel:1500181',
    },
    {
        title: 'Jadwal Dokter Spesialis',
        subtitle: 'Cek profil dan ketersediaan praktik dokter spesialis',
        icon: Stethoscope,
        badge: 'Praktik Setiap Hari',
        actionText: 'Lihat Jadwal',
        href: '/schedule-guest',
    },
    {
        title: 'Layanan Rawat Inap & Kamar',
        subtitle: 'Informasi fasilitas kamar VIP, VVIP, ICU, & estimasi biaya',
        icon: Bed,
        badge: 'Fasilitas Modern',
        actionText: 'Cek Fasilitas',
        targetId: 'fasilitas',
    },
];

const centersOfExcellence = [
    {
        title: 'Pusat Jantung & Vaskular',
        subtitle: 'Cardiology & Vascular Center',
        description:
            'Layanan komprehensif penanganan penyakit jantung dengan fasilitas Cath Lab canggih, intervensi koroner, dan rehabilitasi jantung terpadu.',
        icon: HeartPulse,
        specialties: [
            'Kateterisasi Jantung',
            'Bedah Bypass (CABG)',
            'Ekokardiografi 4D',
            'Rehabilitasi Jantung',
        ],
    },
    {
        title: 'Kesehatan Ibu & Anak',
        subtitle: 'Women & Children Health Center',
        description:
            'Perawatan menyeluruh masa kehamilan, persalinan nyaman, tumbuh kembang anak, hingga unit intensif NICU & PICU berstandar tinggi.',
        icon: Heart,
        specialties: [
            'Persalinan Nyaman (ERACS)',
            'Klinik Fertilitas',
            'NICU / PICU Level 3',
            'Skrining Genetik Janin',
        ],
    },
    {
        title: 'Bedah Ortopedi & Sendi',
        subtitle: 'Orthopedic & Joint Center',
        description:
            'Spesialisasi tulang dan persendian dengan teknologi artroskopi minimal invasif, penggantian sendi panggul/lutut, dan penanganan cedera olahraga.',
        icon: Activity,
        specialties: [
            'Total Knee/Hip Replacement',
            'Artroskopi Sendi',
            'Tulang Belakang (Spine)',
            'Sport Medicine & Fisioterapi',
        ],
    },
    {
        title: 'Pusat Onkologi & Kanker',
        subtitle: 'Integrated Cancer Care',
        description:
            'Pusat deteksi dini dan terapi kanker terpadu dengan tim multi-disiplin dokter onkologi medik, bedah, kemoterapi modern, dan pendampingan psikologis.',
        icon: ShieldAlert,
        specialties: [
            'Kemoterapi Terpadu',
            'Deteksi Dini Kanker',
            'Bedah Onkologi Mikro',
            'Palliative & Supportive Care',
        ],
    },
    {
        title: 'Saraf & Brain Spine Care',
        subtitle: 'Neurology & Neurosurgery Center',
        description:
            'Pusat penanganan komprehensif stroke akut (Stroke Unit 24 Jam), bedah mikro saraf, epilepsi, dan manajemen nyeri saraf tulang belakang.',
        icon: Sparkles,
        specialties: [
            'Stroke Emergency Unit 24 Jam',
            'Mikro-bedah Saraf',
            'EEG & EMG Digital',
            'Intervensi Nyeri Saraf',
        ],
    },
    {
        title: 'Penyakit Dalam & Saluran Cerna',
        subtitle: 'Digestive & Internal Medicine',
        description:
            'Diagnosis dan penanganan penyakit metabolik, diabetes, gangguan liver, serta prosedur endoskopi saluran cerna dengan teknologi visual beresolusi tinggi.',
        icon: Droplets,
        specialties: [
            'Endoskopi & Kolonoskopi',
            'Klinik Diabetes & Tiroid',
            'Hemodialisa Modern',
            'Klinik Hepatologi',
        ],
    },
];

const hospitalFacilities = [
    {
        title: 'Laboratorium Otomatis 24 Jam',
        desc: 'Pemeriksaan darah lengkap, imunologi, patologi anatomi, dan mikrobiologi dengan akurasi teruji dan hasil cepat.',
        icon: FileText,
    },
    {
        title: 'Radiologi & Imaging Modern',
        desc: 'Fasilitas MRI 1.5 Tesla, CT-Scan 128 Slice, USG 4D, Mammografi digital, dan Panoramic Dental X-Ray.',
        icon: Building2,
    },
    {
        title: 'Farmasi & Apotek Terpadu 24 Jam',
        desc: 'Jaminan keaslian obat 100%, layanan konseling apoteker klinis, peracikan steril, dan pengantaran obat ke rumah.',
        icon: ShieldCheck,
    },
    {
        title: 'Armada Ambulans Emergency',
        desc: 'Ambulans gawat darurat dilengkapi ventilator transport, monitor defibrilator, dan tim paramedis bersertifikasi.',
        icon: Ambulance,
    },
];

const patientFaqs = [
    {
        q: 'Bagaimana cara mendaftar antrean dokter secara online?',
        a: 'Anda cukup memilih dokter dan poliklinik tujuan pada katalog jadwal di atas, lalu klik tombol "Ambil Nomor Antrean". Jika belum memiliki akun, silakan buat akun gratis terlebih dahulu.',
    },
    {
        q: 'Apakah bisa menggunakan asuransi kesehatan / BPJS?',
        a: 'Ya, Hospital Population melayani pasien umum, BPJS Kesehatan (dengan rujukan aktif), serta lebih dari 50+ asuransi swasta terkemuka dengan sistem klaim cashless yang cepat.',
    },
    {
        q: 'Berapa jam sebelum waktu praktik saya harus tiba di rumah sakit?',
        a: 'Kami menyarankan pasien tiba 15–20 menit sebelum jam praktik dimulai untuk melakukan konfirmasi kehadiran mandiri atau melalui loket admisi poliklinik.',
    },
    {
        q: 'Bagaimana jika saya memerlukan pertolongan gawat darurat?',
        a: 'Instalasi Gawat Darurat (IGD) kami beroperasi 24 jam setiap hari tanpa perlu perjanjian terlebih dahulu. Anda dapat langsung datang atau menghubungi hotline darurat di 1-500-181.',
    },
];
</script>

<template>
    <Head
        title="Hospital Population — Pelayanan Kesehatan Terpadu & Berstandar Internasional"
    >
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
                    <Link
                        href="/display"
                        class="inline-flex items-center gap-1 font-medium whitespace-nowrap transition-colors hover:text-[#beedc0]"
                    >
                        <Tv class="size-3 shrink-0 text-[#beedc0]" />
                        <span class="whitespace-nowrap">Layar Antrean TV</span>
                    </Link>
                    <span class="text-[#333333]">|</span>
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
             2. MAIN NAVBAR DENGAN MEGA MENU (Siloam Style)
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

                    <!-- Trigger Mega Menu Layanan & Spesialisasi -->
                    <div
                        class="relative"
                        @mouseenter="isMegaMenuOpen = true"
                        @mouseleave="isMegaMenuOpen = false"
                    >
                        <button
                            type="button"
                            @click="isMegaMenuOpen = !isMegaMenuOpen"
                            class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border px-3.5 py-2 text-xs font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none sm:px-4 sm:text-sm"
                            :class="
                                isMegaMenuOpen
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-sm'
                                    : 'border-transparent text-[#333333] hover:border-[#333333]/15 hover:bg-[#fffff3] hover:text-[#000000]'
                            "
                        >
                            <Activity
                                class="size-4 shrink-0"
                                :class="
                                    isMegaMenuOpen
                                        ? 'text-[#beedc0]'
                                        : 'text-[#000000]'
                                "
                            />
                            <span class="whitespace-nowrap"
                                >Layanan &amp; Spesialisasi</span
                            >
                            <ChevronDown
                                class="size-3.5 shrink-0 transition-transform duration-200"
                                :class="{
                                    'rotate-180 text-white': isMegaMenuOpen,
                                }"
                            />
                        </button>

                        <!-- MEGA MENU DROPDOWN PANEL (with hover bridge pt-2) -->
                        <div
                            v-show="isMegaMenuOpen"
                            class="absolute top-full left-1/2 z-50 w-[820px] -translate-x-1/2 animate-in pt-2 duration-150 fade-in slide-in-from-top-2"
                        >
                            <div
                                class="space-y-5 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 text-xs text-[#000000] shadow-2xl"
                            >
                                <div class="grid grid-cols-3 gap-6">
                                    <!-- Col 1: Poliklinik Spesialis -->
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
                                                >Rawat Jalan</span
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
                                                <button
                                                    type="button"
                                                    @click="
                                                        scrollToSection(
                                                            'pusat-unggulan',
                                                        )
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
                                                </button>
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
                                                    <button
                                                        type="button"
                                                        @click="
                                                            scrollToSection(
                                                                'fasilitas',
                                                            )
                                                        "
                                                        class="flex w-full cursor-pointer items-center gap-2 text-left font-medium whitespace-nowrap text-[#000000] hover:underline"
                                                    >
                                                        <component
                                                            :is="fac.icon"
                                                            class="size-3.5 shrink-0 text-[#000000]"
                                                        />
                                                        <span
                                                            class="truncate whitespace-nowrap"
                                                            >{{
                                                                fac.title
                                                            }}</span
                                                        >
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Dropdown Action Buttons: Atas Bawah (Vertical Stack) -->
                                        <div
                                            class="flex flex-col gap-2 border-t border-[#333333]/15 pt-3"
                                        >
                                            <!-- Button Atas: Buka Monitor TV Antrean -->
                                            <Link
                                                href="/display"
                                                @click="isMegaMenuOpen = false"
                                                class="inline-flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-3.5 py-2 text-[11px] font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333]"
                                            >
                                                <Tv
                                                    class="size-3.5 shrink-0 text-[#beedc0]"
                                                />
                                                <span class="whitespace-nowrap"
                                                    >Buka Monitor TV
                                                    Antrean</span
                                                >
                                            </Link>

                                            <!-- Button Bawah: Panduan Pasien & BPJS -->
                                            <button
                                                type="button"
                                                @click="scrollToSection('faq')"
                                                class="inline-flex min-h-[40px] w-full cursor-pointer items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-3.5 py-2 text-[11px] font-semibold whitespace-nowrap text-[#000000] shadow-sm transition-colors hover:bg-[#edede2]"
                                            >
                                                <HelpCircle
                                                    class="size-3.5 shrink-0 text-[#000000]"
                                                />
                                                <span class="whitespace-nowrap"
                                                    >Panduan Pasien &amp;
                                                    BPJS</span
                                                >
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom Quick Action Banner inside Mega Menu -->
                                <div
                                    class="flex items-center justify-between gap-4 border-t border-[#333333]/10 pt-3 text-xs whitespace-nowrap text-[#333333]"
                                >
                                    <span class="whitespace-nowrap"
                                        >Ingin langsung mencari dokter spesialis
                                        sesuai waktu Anda?</span
                                    >
                                    <button
                                        type="button"
                                        @click="jumpToDoctorSearch()"
                                        class="inline-flex shrink-0 cursor-pointer items-center gap-1 font-semibold whitespace-nowrap text-[#000000] underline underline-offset-4 hover:text-[#333333]"
                                    >
                                        <span
                                            >Buka Seluruh Jadwal Praktik ({{
                                                schedules.length
                                            }}
                                            Dokter)</span
                                        >
                                        <ArrowRight class="size-3.5 shrink-0" />
                                    </button>
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

                            <!-- Layar Antrean TV -->
                            <Link
                                href="/display"
                                @click="closeMobileMenu"
                                class="flex min-h-[44px] items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-[#000000] text-[#beedc0]"
                                    >
                                        <Tv class="size-4" />
                                    </div>
                                    <span>Layar Antrean TV Monitor</span>
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
                                            scrollToSection('pusat-unggulan')
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
                                        @click="scrollToSection('fasilitas')"
                                        class="flex min-h-[38px] w-full cursor-pointer items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000]"
                                    >
                                        <span>{{ fac.title }}</span>
                                        <ChevronRight
                                            class="size-3 text-[#333333]/40"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- FAQ & Panduan Pasien Link -->
                            <button
                                type="button"
                                @click="scrollToSection('faq')"
                                class="flex min-h-[44px] w-full cursor-pointer items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <div class="flex items-center gap-3">
                                    <HelpCircle class="size-4 text-[#000000]" />
                                    <span>Panduan Pasien & BPJS (FAQ)</span>
                                </div>
                                <ChevronRight
                                    class="size-4 text-[#333333]/50"
                                />
                            </button>
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

        <main class="space-y-16 pb-20 sm:space-y-24">
            <!-- ═══════════════════════════════════════════════════════
                 3. HERO SECTION (Siloam Style)
                 ═══════════════════════════════════════════════════════ -->
            <section
                class="mx-auto max-w-[1200px] space-y-8 px-4 pt-8 sm:px-6 sm:pt-14 lg:px-8"
            >
                <!-- Headline block -->
                <div class="mx-auto max-w-3xl space-y-4 text-center">
                    <motion.span
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.22, ease: 'easeOut' }"
                        class="inline-flex items-center gap-2 rounded-[46px] border border-[#333333]/20 bg-[#fffff3] px-4 py-1.5 text-xs font-semibold tracking-wider whitespace-nowrap text-[#000000] uppercase"
                    >
                        <Sparkles class="size-3.5 shrink-0 text-[#000000]" />
                        <span class="whitespace-nowrap"
                            >Pelayanan Medis Berstandar Internasional</span
                        >
                    </motion.span>

                    <motion.h1
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{
                            duration: 0.25,
                            ease: 'easeOut',
                            delay: 0.05,
                        }"
                        class="font-['ivypresto-headline'] font-serif text-3xl leading-tight font-semibold text-[#000000] sm:text-4xl md:text-5xl lg:text-6xl"
                    >
                        Kesehatan Terbaik,
                        <span
                            class="inline rounded-full bg-[#beedc0] px-3.5 py-0.5"
                            >Lebih Dekat</span
                        >
                        dengan Keluarga Anda.
                    </motion.h1>

                    <motion.p
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{
                            duration: 0.22,
                            ease: 'easeOut',
                            delay: 0.1,
                        }"
                        class="mx-auto max-w-2xl text-base leading-relaxed text-[#333333] sm:text-lg"
                    >
                        Akses jadwal dokter spesialis, reservasi nomor antrean
                        rawat jalan secara mandiri, dan pantau proses konsultasi
                        Anda dengan mudah dan tenang.
                    </motion.p>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 4. QUICK ACCESS SERVICE CARDS (4 Cards Siloam Style)
                 ═══════════════════════════════════════════════════════ -->
            <section class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4"
                >
                    <motion.div
                        v-for="(card, index) in quickActionCards"
                        :key="card.title"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.02, y: -3 }"
                        :transition="{
                            duration: 0.22,
                            ease: 'easeOut',
                            delay: index * 0.05,
                        }"
                        class="group flex flex-col justify-between space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5 transition-all hover:border-[#000000]"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span
                                    :class="
                                        card.isEmergency
                                            ? 'bg-red-100 text-red-700'
                                            : 'bg-[#beedc0] text-[#000000]'
                                    "
                                    class="flex h-10 w-10 items-center justify-center rounded-full"
                                >
                                    <component :is="card.icon" class="size-5" />
                                </span>
                                <span
                                    class="rounded-[46px] border border-[#333333]/10 bg-white px-2.5 py-0.5 text-[10px] font-bold text-[#333333]"
                                >
                                    {{ card.badge }}
                                </span>
                            </div>

                            <div>
                                <h3
                                    class="font-['ivypresto-headline'] font-serif text-lg leading-snug font-semibold text-[#000000]"
                                >
                                    {{ card.title }}
                                </h3>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-[#333333]/80"
                                >
                                    {{ card.subtitle }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <a
                                v-if="card.href"
                                :href="card.href"
                                class="inline-flex min-h-[42px] w-full items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-[#333333]"
                            >
                                <span>{{ card.actionText }}</span>
                                <ArrowRight class="size-3.5" />
                            </a>
                            <button
                                v-else
                                type="button"
                                @click="
                                    scrollToSection(
                                        card.targetId || 'jadwal-dokter',
                                    )
                                "
                                class="inline-flex min-h-[42px] w-full items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-[#333333]"
                            >
                                <span>{{ card.actionText }}</span>
                                <ArrowRight class="size-3.5" />
                            </button>
                        </div>
                    </motion.div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 5. CENTERS OF EXCELLENCE (Pusat Unggulan Siloam Style)
                 ═══════════════════════════════════════════════════════ -->
            <section
                id="pusat-unggulan"
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div class="mx-auto max-w-2xl space-y-2 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                    >
                        <Award class="size-3.5" />
                        Centers of Excellence
                    </span>
                    <h2
                        class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl lg:text-5xl"
                    >
                        Pusat Layanan Medis Unggulan
                    </h2>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Didukung oleh tim dokter subspesialis berpengalaman,
                        teknologi medis terkini, dan protokol perawatan
                        berstandar global.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <motion.div
                        v-for="(coe, index) in centersOfExcellence"
                        :key="coe.title"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -3 }"
                        :transition="{
                            duration: 0.25,
                            ease: 'easeOut',
                            delay: index * 0.05,
                        }"
                        class="flex flex-col justify-between space-y-5 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6"
                    >
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0]"
                                >
                                    <component
                                        :is="coe.icon"
                                        class="size-5 text-[#000000]"
                                    />
                                </span>
                                <span
                                    class="font-mono text-[11px] font-semibold text-[#333333]/60"
                                    >0{{ index + 1 }}</span
                                >
                            </div>

                            <div>
                                <h3
                                    class="font-['ivypresto-headline'] font-serif text-xl leading-snug font-semibold text-[#000000]"
                                >
                                    {{ coe.title }}
                                </h3>
                                <span
                                    class="mt-0.5 block text-xs font-semibold text-[#333333]/70"
                                >
                                    {{ coe.subtitle }}
                                </span>
                            </div>

                            <p
                                class="text-xs leading-relaxed text-[#333333]/80"
                            >
                                {{ coe.description }}
                            </p>

                            <!-- Sub-layanan checklist -->
                            <div
                                class="space-y-1.5 border-t border-[#333333]/10 pt-2"
                            >
                                <div
                                    v-for="sub in coe.specialties"
                                    :key="sub"
                                    class="flex items-center gap-2 text-[11px] text-[#000000]"
                                >
                                    <CheckCircle2
                                        class="size-3 shrink-0 text-[#000000]"
                                    />
                                    <span>{{ sub }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button
                                type="button"
                                @click="jumpToDoctorSearch(coe.title)"
                                class="inline-flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-4 py-2 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#edede2]"
                            >
                                <span>Konsultasi Dokter Terkait</span>
                                <ChevronRight class="size-3.5" />
                            </button>
                        </div>
                    </motion.div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 7. FASILITAS & PENUNJANG MEDIS (#fasilitas)
                 ═══════════════════════════════════════════════════════ -->
            <section
                id="fasilitas"
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div
                    class="space-y-8 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 sm:p-10 lg:p-12"
                >
                    <div class="max-w-2xl space-y-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            <Hospital class="size-3.5" />
                            Fasilitas Modern & Penunjang Medis
                        </span>
                        <h2
                            class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl"
                        >
                            Infrastruktur Diagnostik Terlengkap
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-[#333333] sm:text-sm"
                        >
                            Mendukung ketepatan diagnosis dan kecepatan
                            penanganan medis dengan standar akreditasi rumah
                            sakit tertinggi.
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="item in hospitalFacilities"
                            :key="item.title"
                            class="flex flex-col justify-between space-y-3 rounded-[8px] border border-[#333333]/10 bg-white p-5"
                        >
                            <div class="space-y-2.5">
                                <span
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[#beedc0]"
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-5 text-[#000000]"
                                    />
                                </span>
                                <h3
                                    class="text-sm font-semibold text-[#000000]"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    class="text-xs leading-relaxed text-[#333333]/80"
                                >
                                    {{ item.desc }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 8. PATIENT GUIDE & FAQ SECTION (#faq)
                 ═══════════════════════════════════════════════════════ -->
            <section
                id="faq"
                class="mx-auto max-w-[1200px] space-y-8 px-4 sm:px-6 lg:px-8"
            >
                <div class="mx-auto max-w-2xl space-y-2 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                    >
                        <HelpCircle class="size-3.5" />
                        Healthpedia & Panduan Pasien
                    </span>
                    <h2
                        class="font-['ivypresto-headline'] font-serif text-3xl font-semibold text-[#000000] sm:text-4xl"
                    >
                        Pertanyaan Sering Diajukan
                    </h2>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Informasi praktis untuk kenyamanan kunjungan dan
                        pengobatan Anda di Hospital Population.
                    </p>
                </div>

                <div
                    class="mx-auto grid max-w-4xl grid-cols-1 gap-4 md:grid-cols-2"
                >
                    <div
                        v-for="faq in patientFaqs"
                        :key="faq.q"
                        class="space-y-2 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5"
                    >
                        <h3
                            class="flex items-start gap-2 text-sm font-semibold text-[#000000]"
                        >
                            <span class="font-bold text-[#000000]">Q:</span>
                            <span>{{ faq.q }}</span>
                        </h3>
                        <p
                            class="pl-5 text-xs leading-relaxed text-[#333333]/80"
                        >
                            {{ faq.a }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 9. CALL TO ACTION BANNER (Evergreen Black Pill)
                 ═══════════════════════════════════════════════════════ -->
            <section class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
                <div
                    class="space-y-6 rounded-[10px] bg-[#000000] p-8 text-center text-white sm:p-12"
                >
                    <div class="mx-auto max-w-2xl space-y-3">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            Registrasi Mudah & Cepat
                        </span>
                        <h2
                            class="font-['ivypresto-headline'] font-serif text-3xl leading-tight font-semibold sm:text-4xl lg:text-5xl"
                        >
                            Mulai Konsultasi Sehat Anda Hari Ini
                        </h2>
                        <p
                            class="mx-auto max-w-lg text-xs leading-relaxed text-white/80 sm:text-sm"
                        >
                            Buat akun untuk memantau rekam antrean keluarga,
                            memilih dokter spesialis terbaik, dan menikmati
                            kemudahan pembayaran medis.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-3"
                    >
                        <Link
                            v-if="!currentUser"
                            href="/register"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-white px-7 py-3 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#edede2] sm:text-sm"
                        >
                            <span>Daftar Akun Pasien Gratis</span>
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            href="/schedule-guest"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] border border-white/30 bg-transparent px-7 py-3 text-xs font-semibold text-white transition-colors hover:bg-white/10 sm:text-sm"
                        >
                            <span>Pilih Dokter Spesialis</span>
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <!-- ═══════════════════════════════════════════════════════════
             10. FOOTER RUMAH SAKIT MULTI-KOLOM (Siloam Style)
             ═══════════════════════════════════════════════════════ -->
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
                                    Sistem Pelayanan & Jadwal Praktik Terpadu
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
                                    href="/schedule-guest"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Rawat Jalan & Poliklinik
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
                                <button
                                    type="button"
                                    @click="scrollToSection('pusat-unggulan')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Pusat Jantung & Kardiologi
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    @click="scrollToSection('pusat-unggulan')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Pusat Ibu & Anak
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    @click="scrollToSection('pusat-unggulan')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Ortopedi & Tulang
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    @click="scrollToSection('fasilitas')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Medical Check-Up (MCU)
                                </button>
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
                                    href="/display"
                                    class="font-semibold text-[#000000] hover:text-[#000000] hover:underline"
                                >
                                    Layar Monitor Antrean TV
                                </Link>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    @click="scrollToSection('faq')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Panduan Pendaftaran Online
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    @click="scrollToSection('faq')"
                                    class="hover:text-[#000000] hover:underline"
                                >
                                    Mitra Asuransi & BPJS
                                </button>
                            </li>
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
                            Kontak & Lokasi
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
                            >Syarat & Ketentuan</a
                        >
                        <span>•</span>
                        <a href="#" class="hover:underline">Hak Pasien</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
