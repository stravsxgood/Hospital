<script setup lang="ts">
/**
 * @file PatientStory.vue
 * @description Public "Cerita Pasien" (Patient Stories) page for Hospital Population.
 * Features an editorial Siloam-inspired design, interactive category filters, real-time search,
 * a large featured hero story, card catalog grid, and an in-depth story reading modal.
 *
 * Adheres strictly to DESIGN.md (Evergreen theme) and GEMINI.md guidelines.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import {
    Activity,
    Ambulance,
    ArrowRight,
    Award,
    BookOpen,
    Building2,
    Calendar,
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
    Quote,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    Ticket,
    Tv,
    User,
    UserPlus,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import StoryDetailModal from '@/components/StoryDetailModal.vue'
import type { PatientStory } from '@/types'

// Wajib mandiri tanpa sidebar default
defineOptions({
    layout: undefined,
})

/* ═══════════════════════════════════════════════════════════════
   Props & Backend Data
   ═══════════════════════════════════════════════════════════════ */
const props = withDefaults(
    defineProps<{
        featuredStory?: PatientStory | null
        stories?: PatientStory[]
        categories?: string[]
    }>(),
    {
        featuredStory: null,
        stories: () => [],
        categories: () => [
            'Semua',
            'Jantung & Vaskular',
            'Ibu & Anak',
            'Ortopedi',
            'Onkologi',
            'Penyakit Dalam',
            'Bedah Umum',
        ],
    },
)

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)
const isStaffUser = computed(() => {
    const role = currentUser.value?.role

    return ['doctor', 'nurse', 'admin'].includes(role || '') || Boolean(currentUser.value?.is_doctor)
})

/* ═══════════════════════════════════════════════════════════════
   Reactive State for Search, Filters, Modal & Drawer
   ═══════════════════════════════════════════════════════════════ */
const isMounted = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('Semua')
const isMegaMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isMobilePoliOpen = ref(false)
const isMobileCoEOpen = ref(false)
const isMobileFacilitiesOpen = ref(false)

const openMobileMenu = () => {
    isMobileMenuOpen.value = true
    document.body.style.overflow = 'hidden'
}

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
    document.body.style.overflow = ''
}

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileMenuOpen.value) {
        closeMobileMenu()
    }
}

const removeNavigateListener = router.on('navigate', () => {
    closeMobileMenu()
})

onMounted(() => {
    isMounted.value = true
    window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
    removeNavigateListener()
    document.body.style.overflow = ''
})

const handleLogout = () => {
    closeMobileMenu()
    router.post('/logout')
}

// State Modal Detail Cerita
const isDetailModalOpen = ref(false)
const selectedStory = ref<PatientStory | null>(null)

/**
 * Open detail modal with selected story data
 */
const openStoryModal = (story: PatientStory) => {
    selectedStory.value = story
    isDetailModalOpen.value = true
}

/**
 * Filtered stories based on selected category and search keyword
 */
const filteredStories = computed(() => {
    let list = props.stories

    // Filter by Category
    if (selectedCategory.value && selectedCategory.value !== 'Semua') {
        list = list.filter((s) => s.category.toLowerCase() === selectedCategory.value.toLowerCase())
    }

    // Filter by Search Query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim()
        list = list.filter(
            (s) =>
                s.title.toLowerCase().includes(query) ||
                s.patient_name.toLowerCase().includes(query) ||
                s.doctor_name.toLowerCase().includes(query) ||
                s.diagnosis.toLowerCase().includes(query) ||
                s.excerpt.toLowerCase().includes(query) ||
                s.category.toLowerCase().includes(query),
        )
    }

    return list
})

/**
 * Reset all active search filters
 */
const resetFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = 'Semua'
}

/**
 * Quick jump to Poliklinik & Medical Team Profile page (/teams?poli=...)
 */
const jumpToPoliTeam = (poliName: string) => {
    isMegaMenuOpen.value = false
    router.visit('/teams?poli=' + encodeURIComponent(poliName))
}

/**
 * Quick jump to Doctor Catalog page with preselected poli / keyword
 */
const jumpToDoctorSearch = (poliOrKeyword?: string) => {
    isMegaMenuOpen.value = false

    if (poliOrKeyword) {
        router.visit('/schedule-guest?poli=' + encodeURIComponent(poliOrKeyword))
    } else {
        router.visit('/schedule-guest')
    }
}

/**
 * Centers of Excellence list for mega menu (Matches Welcome.vue)
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
]

/**
 * Facilities list for mega menu (Matches Welcome.vue)
 */
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
]
</script>

<template>
    <Head title="Cerita Pasien & Testimoni Medis — Hospital Population">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Rubik:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="min-h-screen bg-[#edede2] text-[#000000] font-['Rubik'] antialiased selection:bg-[#beedc0] selection:text-[#000000]">

        <!-- ═══════════════════════════════════════════════════════════
             1. TOP UTILITY & EMERGENCY BAR (Siloam Style)
             ═══════════════════════════════════════════════════════════ -->
        <div class="bg-[#000000] text-white text-xs py-2 px-4 sm:px-6 lg:px-8 border-b border-[#333333]/40">
            <div class="max-w-[1200px] mx-auto flex items-center justify-between gap-4 flex-nowrap overflow-x-auto scrollbar-none">
                <!-- Left: Emergency hotline & IGD info -->
                <div class="flex items-center gap-4 sm:gap-6 text-white/90 shrink-0 whitespace-nowrap">
                    <a
                        href="tel:1500181"
                        class="inline-flex items-center gap-1.5 font-bold hover:text-[#beedc0] transition-colors whitespace-nowrap"
                    >
                        <span class="flex h-2 w-2 rounded-full bg-red-500 animate-ping shrink-0" />
                        <PhoneCall class="size-3.5 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap">IGD &amp; Ambulans 24 Jam: <strong>1-500-181</strong></span>
                    </a>
                    <span class="hidden sm:inline text-[#333333]">|</span>
                    <a
                        href="https://wa.me/6281100000000"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 hover:text-[#beedc0] transition-colors whitespace-nowrap"
                    >
                        <MessageSquare class="size-3.5 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap">WhatsApp Care: 0811-0000-0000</span>
                    </a>
                </div>

                <!-- Right: Quick Portal Links -->
                <div class="flex items-center gap-4 text-[11px] text-white/80 shrink-0 whitespace-nowrap">
                    <Link
                        href="/display"
                        class="inline-flex items-center gap-1 hover:text-[#beedc0] transition-colors font-medium whitespace-nowrap"
                    >
                        <Tv class="size-3 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap">Layar Antrean TV</span>
                    </Link>
                    <span class="text-[#333333]">|</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-[#beedc0] whitespace-nowrap">
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
            class="sticky top-0 z-40 bg-[#edede2]/95 backdrop-blur-md border-b border-[#333333]/15 transition-all"
        >
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                <!-- Brand Logo & Identity -->
                <Link href="/" class="flex items-center gap-3 group shrink-0">
                    <motion.div
                        :whileHover="{ scale: 1.05 }"
                        :whileTap="{ scale: 0.95 }"
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0] border border-[#333333]/10 shrink-0"
                    >
                        <AppLogoIcon class="size-7 fill-current text-[#000000]" />
                    </motion.div>
                    <div class="flex flex-col justify-center">
                        <span class="font-['ivypresto-headline'] font-serif text-xl sm:text-2xl font-bold tracking-tight text-[#000000] leading-tight whitespace-nowrap">
                            Hospital Population
                        </span>
                        <span class="text-[10px] sm:text-[11px] text-[#333333] tracking-wider uppercase font-medium whitespace-nowrap">
                            Pelayanan Kesehatan Terpadu
                        </span>
                    </div>
                </Link>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden lg:flex items-center gap-1.5 xl:gap-2 text-sm font-medium text-[#000000] whitespace-nowrap">
                    <!-- Button Cari Dokter (Links to /schedule-guest) -->
                    <Link
                        href="/schedule-guest"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-transparent text-[#333333] hover:text-[#000000] hover:bg-[#fffff3] hover:border-[#333333]/15 transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <Stethoscope class="size-4 text-[#000000] shrink-0" />
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
                            class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer focus:outline-none"
                            :class="isMegaMenuOpen ? 'bg-[#000000] text-white border-[#000000] shadow-sm' : 'border-transparent text-[#333333] hover:text-[#000000] hover:bg-[#fffff3] hover:border-[#333333]/15'"
                        >
                            <Activity class="size-4 shrink-0" :class="isMegaMenuOpen ? 'text-[#beedc0]' : 'text-[#000000]'" />
                            <span class="whitespace-nowrap">Layanan &amp; Spesialisasi</span>
                            <ChevronDown
                                class="size-3.5 transition-transform duration-200 shrink-0"
                                :class="{ 'rotate-180 text-white': isMegaMenuOpen }"
                            />
                        </button>

                        <!-- MEGA MENU DROPDOWN PANEL (with hover bridge pt-2 - 3 Columns) -->
                        <div
                            v-show="isMegaMenuOpen"
                            class="absolute top-full left-1/2 -translate-x-1/2 pt-2 w-[820px] z-50 animate-in fade-in slide-in-from-top-2 duration-150"
                        >
                            <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 shadow-2xl p-6 text-xs text-[#000000] space-y-5">
                                <div class="grid grid-cols-3 gap-6">
                                    <!-- Col 1: Poliklinik Spesialis -->
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between border-b border-[#333333]/10 pb-1.5 whitespace-nowrap">
                                            <span class="text-xs font-bold text-[#000000] uppercase tracking-wider whitespace-nowrap">
                                                Poliklinik Spesialis
                                            </span>
                                            <span class="text-[10px] text-[#333333]/60 font-mono whitespace-nowrap">Rawat Jalan</span>
                                        </div>
                                        <ul class="space-y-1">
                                            <li
                                                v-for="poli in ['Poli Umum', 'Poli Penyakit Dalam', 'Poli Anak & Tumbuh Kembang', 'Poli Jantung & Pembuluh Darah', 'Poli Kebidanan & Kandungan', 'Poli Bedah & Ortopedi', 'Poli Gigi & Mulut', 'Poli Mata', 'Poli THT', 'Poli Saraf']"
                                                :key="poli"
                                            >
                                                <button
                                                    type="button"
                                                    @click="jumpToPoliTeam(poli)"
                                                    class="hover:bg-[#edede2] text-[#333333] hover:text-[#000000] text-left w-full px-2.5 py-1.5 rounded-[6px] transition-colors flex items-center justify-between group cursor-pointer whitespace-nowrap"
                                                >
                                                    <span class="font-medium whitespace-nowrap group-hover:translate-x-0.5 transition-transform">{{ poli }}</span>
                                                    <ChevronRight class="size-3 text-[#333333]/40 group-hover:text-[#000000] shrink-0" />
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Col 2: Pusat Layanan Unggulan (CoE) -->
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between border-b border-[#333333]/10 pb-1.5 whitespace-nowrap">
                                            <span class="text-xs font-bold text-[#000000] uppercase tracking-wider whitespace-nowrap">
                                                Pusat Unggulan (CoE)
                                            </span>
                                            <span class="text-[10px] text-[#333333]/60 font-mono whitespace-nowrap">Subspesialis</span>
                                        </div>
                                        <ul class="space-y-2">
                                            <li v-for="center in centersOfExcellence" :key="center.title">
                                                <Link
                                                    href="/#pusat-unggulan"
                                                    @click="isMegaMenuOpen = false"
                                                    class="text-left w-full p-2 rounded-[6px] hover:bg-[#edede2] transition-colors group flex items-center gap-2.5 cursor-pointer whitespace-nowrap"
                                                >
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]">
                                                        <component :is="center.icon" class="size-3.5" />
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <span class="font-semibold text-[#000000] block truncate group-hover:underline whitespace-nowrap">
                                                            {{ center.title }}
                                                        </span>
                                                        <span class="text-[10px] text-[#333333]/70 block truncate whitespace-nowrap">
                                                            {{ center.subtitle }}
                                                        </span>
                                                    </div>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Col 3: Fasilitas Penunjang & Diagnostik -->
                                    <div class="space-y-3 bg-[#edede2]/60 p-4 rounded-[8px] border border-[#333333]/10 flex flex-col justify-between">
                                        <div class="space-y-3">
                                            <div class="border-b border-[#333333]/15 pb-1.5 whitespace-nowrap">
                                                <span class="text-xs font-bold text-[#000000] uppercase tracking-wider block whitespace-nowrap">
                                                    Fasilitas &amp; Penunjang
                                                </span>
                                            </div>
                                            <ul class="space-y-2">
                                                <li v-for="fac in hospitalFacilities" :key="fac.title">
                                                    <Link
                                                        href="/#fasilitas"
                                                        @click="isMegaMenuOpen = false"
                                                        class="text-left w-full hover:underline font-medium text-[#000000] flex items-center gap-2 cursor-pointer whitespace-nowrap"
                                                    >
                                                        <component :is="fac.icon" class="size-3.5 text-[#000000] shrink-0" />
                                                        <span class="truncate whitespace-nowrap">{{ fac.title }}</span>
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Dropdown Action Buttons: Atas Bawah (Vertical Stack) -->
                                        <div class="pt-3 border-t border-[#333333]/15 flex flex-col gap-2">
                                            <!-- Button Atas: Buka Monitor TV Antrean -->
                                            <Link
                                                href="/display"
                                                @click="isMegaMenuOpen = false"
                                                class="min-h-[40px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-3.5 py-2 text-[11px] font-semibold text-white hover:bg-[#333333] transition-colors w-full whitespace-nowrap shadow-sm"
                                            >
                                                <Tv class="size-3.5 shrink-0 text-[#beedc0]" />
                                                <span class="whitespace-nowrap">Buka Monitor TV Antrean</span>
                                            </Link>

                                            <!-- Button Bawah: Panduan Pasien & BPJS -->
                                            <Link
                                                href="/#faq"
                                                @click="isMegaMenuOpen = false"
                                                class="min-h-[40px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-3.5 py-2 text-[11px] font-semibold text-[#000000] hover:bg-[#edede2] transition-colors w-full cursor-pointer whitespace-nowrap shadow-sm"
                                            >
                                                <HelpCircle class="size-3.5 shrink-0 text-[#000000]" />
                                                <span class="whitespace-nowrap">Panduan Pasien &amp; BPJS</span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom Quick Action Banner inside Mega Menu -->
                                <div class="pt-3 border-t border-[#333333]/10 flex items-center justify-between text-xs text-[#333333] whitespace-nowrap gap-4">
                                    <span class="whitespace-nowrap">Ingin langsung mencari dokter spesialis sesuai waktu Anda?</span>
                                    <button
                                        type="button"
                                        @click="jumpToDoctorSearch()"
                                        class="font-semibold text-[#000000] underline underline-offset-4 hover:text-[#333333] inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shrink-0"
                                    >
                                        <span>Buka Seluruh Jadwal Praktik Dokter</span>
                                        <ArrowRight class="size-3.5 shrink-0" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Lokasi Klinik -->
                    <Link
                        href="/clinic-location"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-transparent text-[#333333] hover:text-[#000000] hover:bg-[#fffff3] hover:border-[#333333]/15 transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <MapPin class="size-4 text-[#000000] shrink-0" />
                        <span class="whitespace-nowrap">Lokasi Klinik</span>
                    </Link>

                    <!-- Link Cerita Pasien (Active Page) -->
                    <Link
                        href="/patient-story"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-[#000000] bg-[#000000] text-white shadow-sm transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <span class="size-1.5 rounded-full bg-[#beedc0] animate-pulse shrink-0" />
                        <HeartHandshake class="size-4 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap">Cerita Pasien</span>
                    </Link>
                </nav>

                <!-- Dynamic Auth Buttons (Desktop & Tablet) -->
                <div class="hidden sm:flex items-center gap-2.5 whitespace-nowrap shrink-0">
                    <!-- If User is Authenticated -->
                    <template v-if="currentUser">
                        <Link
                            :href="isStaffUser ? '/staff' : '/patient/dashboard'"
                            class="min-h-[44px] inline-flex items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm whitespace-nowrap"
                        >
                            <User class="size-3.5 text-[#beedc0] shrink-0" />
                            <span class="whitespace-nowrap">{{ isStaffUser ? 'Dashboard Staf' : 'Portal Pasien' }}</span>
                        </Link>
                    </template>

                    <!-- If User is Guest -->
                    <template v-else>
                        <Link
                            href="/login"
                            class="min-h-[44px] inline-flex items-center gap-1.5 rounded-[40.5px] border border-[#000000] bg-transparent px-4 sm:px-5 py-2 text-xs font-semibold text-[#000000] hover:bg-[#000000] hover:text-white transition-colors whitespace-nowrap"
                        >
                            <LogIn class="size-3.5 shrink-0" />
                            <span class="whitespace-nowrap">Masuk</span>
                        </Link>
                        <Link
                            href="/register"
                            class="min-h-[44px] inline-flex items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 sm:px-6 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm whitespace-nowrap"
                        >
                            <UserPlus class="size-3.5 text-[#beedc0] shrink-0" />
                            <span class="whitespace-nowrap">Daftar Akun</span>
                        </Link>
                    </template>
                </div>

                <!-- Mobile & iPad Hamburger Toggle (< lg) -->
                <div class="flex lg:hidden items-center gap-2">
                    <button
                        type="button"
                        @click="openMobileMenu"
                        aria-label="Buka Menu Navigasi"
                        class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl border border-[#333333]/20 bg-[#fffff3] text-[#000000] hover:bg-[#edede2] active:scale-95 transition-all cursor-pointer shadow-xs"
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
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[340px] sm:max-w-[400px] flex-col justify-between border-l border-[#333333]/15 bg-[#fffff3] p-5 shadow-2xl lg:hidden overflow-y-auto"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu Navigasi Mobile"
                >
                    <!-- 1. Drawer Header -->
                    <div class="flex items-center justify-between border-b border-[#333333]/10 pb-4">
                        <Link href="/" @click="closeMobileMenu" class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#beedc0] border border-[#333333]/10">
                                <AppLogoIcon class="size-5 fill-current text-[#000000]" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-['ivypresto-headline'] font-serif text-base font-bold text-[#000000] leading-none">
                                    Hospital Population
                                </span>
                                <span class="text-[10px] text-[#333333]/70 uppercase tracking-wider font-semibold mt-0.5">
                                    Pelayanan Terpadu
                                </span>
                            </div>
                        </Link>

                        <button
                            type="button"
                            @click="closeMobileMenu"
                            aria-label="Tutup Menu"
                            class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000] transition-colors cursor-pointer"
                        >
                            <X class="size-6" />
                        </button>
                    </div>

                    <!-- 2. Drawer Body (Scrollable navigation list) -->
                    <div class="my-4 flex-1 space-y-4 overflow-y-auto pr-1">
                        <!-- User Card / Auth Buttons -->
                        <div v-if="currentUser" class="rounded-2xl bg-[#edede2]/60 p-3.5 border border-[#333333]/10 space-y-2.5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-[#000000] text-[#beedc0] font-bold flex items-center justify-center text-sm shadow-xs">
                                    {{ currentUser.name ? currentUser.name.charAt(0).toUpperCase() : 'U' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs text-[#000000] truncate">{{ currentUser.name }}</div>
                                    <div class="text-[11px] text-[#333333]/70 truncate">{{ currentUser.email }}</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-2 pt-1">
                                <Link
                                    :href="isStaffUser ? '/staff' : '/patient/dashboard'"
                                    @click="closeMobileMenu"
                                    class="min-h-[44px] w-full flex items-center justify-center gap-2 rounded-xl bg-[#000000] px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-[#333333] transition-colors"
                                >
                                    <User class="size-4 text-[#beedc0]" />
                                    <span>{{ isStaffUser ? 'Buka Dashboard Staf' : 'Buka Portal Pasien' }}</span>
                                </Link>
                                <button
                                    type="button"
                                    @click="handleLogout"
                                    class="min-h-[40px] w-full flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 text-xs font-semibold text-rose-800 hover:bg-rose-100 transition-colors"
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
                                class="min-h-[44px] flex items-center justify-center gap-1.5 rounded-xl border border-[#000000] bg-transparent px-3 py-2 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors"
                            >
                                <LogIn class="size-4" />
                                <span>Masuk</span>
                            </Link>
                            <Link
                                href="/register"
                                @click="closeMobileMenu"
                                class="min-h-[44px] flex items-center justify-center gap-1.5 rounded-xl bg-[#000000] px-3 py-2 text-xs font-bold text-white hover:bg-[#333333] transition-colors shadow-xs"
                            >
                                <UserPlus class="size-4 text-[#beedc0]" />
                                <span>Daftar Akun</span>
                            </Link>
                        </div>

                        <!-- Emergency Hotline Banner -->
                        <div class="rounded-2xl bg-red-50 border border-red-200 p-3 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-red-800 flex items-center gap-1.5">
                                    <span class="flex h-2 w-2 rounded-full bg-red-600 animate-ping" />
                                    <span>IGD & Ambulans 24 Jam</span>
                                </span>
                                <a href="tel:1500181" class="font-bold text-red-900 font-mono text-xs underline">
                                    1-500-181
                                </a>
                            </div>
                            <a
                                href="https://wa.me/6281100000000"
                                target="_blank"
                                rel="noopener"
                                class="min-h-[40px] flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold py-2 px-3 hover:bg-emerald-700 transition-colors"
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
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-[#beedc0] text-[#000000] flex items-center justify-center">
                                        <Stethoscope class="size-4" />
                                    </div>
                                    <span>Cari Dokter Spesialis</span>
                                </div>
                                <ChevronRight class="size-4 text-[#333333]/50" />
                            </Link>

                            <!-- Layar Antrean TV -->
                            <Link
                                href="/display"
                                @click="closeMobileMenu"
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-[#000000] text-[#beedc0] flex items-center justify-center">
                                        <Tv class="size-4" />
                                    </div>
                                    <span>Layar Antrean TV Monitor</span>
                                </div>
                                <ChevronRight class="size-4 text-[#333333]/50" />
                            </Link>

                            <!-- Lokasi Klinik -->
                            <Link
                                href="/clinic-location"
                                @click="closeMobileMenu"
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-[#edede2] text-[#000000] flex items-center justify-center">
                                        <MapPin class="size-4" />
                                    </div>
                                    <span>Lokasi Klinik & Kontak</span>
                                </div>
                                <ChevronRight class="size-4 text-[#333333]/50" />
                            </Link>

                            <!-- Cerita Pasien -->
                            <Link
                                href="/patient-story"
                                @click="closeMobileMenu"
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center">
                                        <HeartHandshake class="size-4" />
                                    </div>
                                    <span>Cerita & Testimoni Pasien</span>
                                </div>
                                <ChevronRight class="size-4 text-[#333333]/50" />
                            </Link>
                        </div>

                        <!-- Expandable / Accordion Layanan & Spesialisasi -->
                        <div class="pt-2 border-t border-[#333333]/10 space-y-2">
                            <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-[#333333]/60">
                                Layanan & Spesialisasi
                            </div>

                            <!-- Poliklinik List Accordion -->
                            <div class="rounded-2xl border border-[#333333]/10 bg-[#edede2]/40 overflow-hidden">
                                <button
                                    type="button"
                                    @click="isMobilePoliOpen = !isMobilePoliOpen"
                                    class="min-h-[44px] w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] cursor-pointer"
                                >
                                    <div class="flex items-center gap-2">
                                        <Activity class="size-4 text-[#000000]" />
                                        <span>Poliklinik Spesialis</span>
                                    </div>
                                    <ChevronDown class="size-4 transition-transform" :class="{ 'rotate-180': isMobilePoliOpen }" />
                                </button>
                                <div v-show="isMobilePoliOpen" class="px-3 pb-3 pt-1 space-y-1 border-t border-[#333333]/5">
                                    <button
                                        v-for="poli in ['Poli Umum', 'Poli Penyakit Dalam', 'Poli Anak & Tumbuh Kembang', 'Poli Jantung & Pembuluh Darah', 'Poli Kebidanan & Kandungan', 'Poli Bedah & Ortopedi', 'Poli Gigi & Mulut', 'Poli Mata', 'Poli THT', 'Poli Saraf']"
                                        :key="poli"
                                        type="button"
                                        @click="jumpToPoliTeam(poli)"
                                        class="min-h-[38px] w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000] flex items-center justify-between cursor-pointer"
                                    >
                                        <span>{{ poli }}</span>
                                        <ChevronRight class="size-3 text-[#333333]/40" />
                                    </button>
                                </div>
                            </div>

                            <!-- Pusat Unggulan Accordion -->
                            <div class="rounded-2xl border border-[#333333]/10 bg-[#edede2]/40 overflow-hidden">
                                <button
                                    type="button"
                                    @click="isMobileCoEOpen = !isMobileCoEOpen"
                                    class="min-h-[44px] w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] cursor-pointer"
                                >
                                    <div class="flex items-center gap-2">
                                        <Award class="size-4 text-[#000000]" />
                                        <span>Pusat Unggulan (CoE)</span>
                                    </div>
                                    <ChevronDown class="size-4 transition-transform" :class="{ 'rotate-180': isMobileCoEOpen }" />
                                </button>
                                <div v-show="isMobileCoEOpen" class="px-3 pb-3 pt-1 space-y-1 border-t border-[#333333]/5">
                                    <button
                                        v-for="center in centersOfExcellence"
                                        :key="center.title"
                                        type="button"
                                        @click="isMobileMenuOpen = false; router.visit('/#pusat-unggulan')"
                                        class="min-h-[38px] w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000] flex items-center justify-between cursor-pointer"
                                    >
                                        <span>{{ center.title }}</span>
                                        <ChevronRight class="size-3 text-[#333333]/40" />
                                    </button>
                                </div>
                            </div>

                            <!-- Fasilitas Penunjang Accordion -->
                            <div class="rounded-2xl border border-[#333333]/10 bg-[#edede2]/40 overflow-hidden">
                                <button
                                    type="button"
                                    @click="isMobileFacilitiesOpen = !isMobileFacilitiesOpen"
                                    class="min-h-[44px] w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] cursor-pointer"
                                >
                                    <div class="flex items-center gap-2">
                                        <Hospital class="size-4 text-[#000000]" />
                                        <span>Fasilitas & Penunjang</span>
                                    </div>
                                    <ChevronDown class="size-4 transition-transform" :class="{ 'rotate-180': isMobileFacilitiesOpen }" />
                                </button>
                                <div v-show="isMobileFacilitiesOpen" class="px-3 pb-3 pt-1 space-y-1 border-t border-[#333333]/5">
                                    <button
                                        v-for="fac in hospitalFacilities"
                                        :key="fac.title"
                                        type="button"
                                        @click="isMobileMenuOpen = false; router.visit('/#fasilitas')"
                                        class="min-h-[38px] w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#333333] hover:bg-[#fffff3] hover:text-[#000000] flex items-center justify-between cursor-pointer"
                                    >
                                        <span>{{ fac.title }}</span>
                                        <ChevronRight class="size-3 text-[#333333]/40" />
                                    </button>
                                </div>
                            </div>

                            <!-- Sub-Spesialisasi Link -->
                            <Link
                                href="/specializations"
                                @click="closeMobileMenu"
                                class="min-h-[44px] w-full flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-3">
                                    <Sparkles class="size-4 text-[#000000]" />
                                    <span>Sub-Spesialisasi Medis</span>
                                </div>
                                <ChevronRight class="size-4 text-[#333333]/50" />
                            </Link>
                        </div>
                    </div>

                    <!-- 3. Drawer Footer -->
                    <div class="border-t border-[#333333]/10 pt-4 space-y-2">
                        <div class="flex items-center justify-between text-[11px] text-[#333333]/70">
                            <span class="flex items-center gap-1 font-semibold text-[#000000]">
                                <Award class="size-3.5 text-[#000000]" />
                                <span>Akreditasi KARS Paripurna</span>
                            </span>
                            <span>v2.0</span>
                        </div>
                    </div>
                </aside>
            </Transition>
        </Teleport>

        <main class="space-y-12 sm:space-y-16 pb-20">

            <!-- ═══════════════════════════════════════════════════════
                 3. HERO SECTION (Cerita Pasien & Testimoni)
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 space-y-6">
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <motion.span
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.22, ease: 'easeOut' }"
                        class="inline-flex items-center gap-2 rounded-[46px] border border-[#333333]/20 bg-[#fffff3] px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#000000] whitespace-nowrap"
                    >
                        <Sparkles class="size-3.5 text-[#000000] shrink-0" />
                        <span class="whitespace-nowrap">Kisah Inspiratif &amp; Harapan</span>
                    </motion.span>

                    <motion.h1
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.25, ease: 'easeOut', delay: 0.05 }"
                        class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold text-[#000000] leading-tight"
                    >
                        Perjalanan Menuju <span class="rounded-full bg-[#beedc0] px-3.5 py-0.5 inline">Kesembuhan</span> Bersama Kami.
                    </motion.h1>

                    <motion.p
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.1 }"
                        class="text-sm sm:text-base text-[#333333] leading-relaxed max-w-2xl mx-auto"
                    >
                        Setiap pasien membawa kisah perjuangan dan harapan yang unik. Simak bagaimana tim medis ahli dan teknologi modern Hospital Population hadir menemani setiap langkah menuju pemulihan sejati.
                    </motion.p>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 4. FEATURED STORY CARD (Kisah Utama Pilihan)
                 ═══════════════════════════════════════════════════════ -->
            <section v-if="featuredStory" class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.005 }"
                    :transition="{ duration: 0.25, ease: 'easeOut' }"
                    class="rounded-[12px] bg-[#fffff3] border border-[#333333]/15 overflow-hidden shadow-lg hover:border-[#000000] transition-all grid grid-cols-1 lg:grid-cols-12 gap-0"
                >
                    <!-- Left: Featured Image Banner -->
                    <div class="lg:col-span-6 relative min-h-[300px] lg:min-h-[440px] bg-neutral-900 overflow-hidden">
                        <img
                            :src="featuredStory.image_url || 'https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=1200&q=80'"
                            :alt="featuredStory.title"
                            class="w-full h-full object-cover object-center opacity-90 hover:scale-105 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

                        <!-- Floating Badges over Image -->
                        <div class="absolute top-4 left-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#beedc0] px-3.5 py-1 text-xs font-bold text-[#000000] shadow-sm">
                                Kisah Pilihan Utama
                            </span>
                            <span class="rounded-full bg-white/90 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-[#000000]">
                                {{ featuredStory.category }}
                            </span>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <p class="text-xs text-white/90 flex items-center gap-2 font-medium">
                                <Clock class="size-3.5 text-[#beedc0]" />
                                <span>{{ featuredStory.read_time }}</span>
                                <span>•</span>
                                <span>{{ featuredStory.published_at }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Right: Story Editorial Details -->
                    <div class="lg:col-span-6 p-6 sm:p-8 lg:p-10 flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-[#333333]/70 block">
                                    {{ featuredStory.badge || 'Kisah Inspiratif' }}
                                </span>
                                <h2 class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000] leading-snug">
                                    {{ featuredStory.title }}
                                </h2>
                            </div>

                            <!-- Emotional Quote -->
                            <div
                                v-if="featuredStory.quote"
                                class="rounded-[8px] bg-[#edede2]/60 border-l-4 border-[#000000] p-4 text-xs sm:text-sm italic font-serif text-[#000000] leading-relaxed"
                            >
                                <Quote class="size-4 text-[#000000] mb-1 inline mr-1 opacity-70" />
                                {{ featuredStory.quote }}
                            </div>

                            <p class="text-xs sm:text-sm text-[#333333]/85 leading-relaxed line-clamp-3">
                                {{ featuredStory.excerpt }}
                            </p>

                            <!-- Medical Context Box -->
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#333333]/10 text-xs">
                                <div>
                                    <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
                                        Pasien
                                    </span>
                                    <span class="font-semibold text-[#000000] block truncate">
                                        {{ featuredStory.patient_name }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
                                        Dokter Spesialis
                                    </span>
                                    <span class="font-semibold text-[#000000] block truncate">
                                        {{ featuredStory.doctor_name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Read Action Button -->
                        <div>
                            <button
                                type="button"
                                @click="openStoryModal(featuredStory)"
                                class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs sm:text-sm font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer whitespace-nowrap"
                            >
                                <BookOpen class="size-4 text-[#beedc0]" />
                                <span>Baca Kisah Lengkap</span>
                                <ArrowRight class="size-4" />
                            </button>
                        </div>
                    </div>
                </motion.div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 5. INTERACTIVE FILTER & SEARCH BAR
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 sm:p-6 shadow-sm space-y-4">
                    <!-- Row 1: Search Bar & Info -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-lg">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari cerita berdasarkan nama, dokter, atau diagnosa..."
                                class="w-full min-h-[44px] pl-10 pr-9 rounded-[7px] border border-[#333333]/20 bg-white text-xs sm:text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none placeholder:text-[#333333]/50"
                            />
                            <Search class="size-4 text-[#333333]/60 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <button
                                v-if="searchQuery"
                                type="button"
                                @click="searchQuery = ''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#333333]/60 hover:text-[#000000]"
                            >
                                <X class="size-4" />
                            </button>
                        </div>

                        <div class="text-xs text-[#333333]/70 whitespace-nowrap self-center sm:self-auto font-medium">
                            Menampilkan <strong>{{ filteredStories.length }}</strong> cerita pasien
                        </div>
                    </div>

                    <!-- Row 2: Category Filter Pills -->
                    <div class="pt-2 border-t border-[#333333]/10 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        <span class="text-[11px] font-semibold text-[#333333] whitespace-nowrap mr-1">Kategori:</span>
                        <button
                            v-for="cat in categories"
                            :key="cat"
                            type="button"
                            @click="selectedCategory = cat"
                            :class="[
                                'min-h-[36px] px-3.5 py-1 rounded-[46px] text-xs font-medium transition-colors whitespace-nowrap cursor-pointer',
                                selectedCategory === cat
                                    ? 'bg-[#000000] text-white font-semibold shadow-sm'
                                    : 'bg-white border border-[#333333]/15 text-[#333333] hover:bg-[#edede2]'
                            ]"
                        >
                            {{ cat }}
                        </button>

                        <button
                            v-if="searchQuery || selectedCategory !== 'Semua'"
                            type="button"
                            @click="resetFilters"
                            class="text-xs font-semibold text-[#000000] underline underline-offset-4 hover:text-[#333333] px-2 py-1 whitespace-nowrap cursor-pointer ml-auto"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 6. PATIENT STORIES GRID CATALOG (3 Columns)
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- If stories match filter -->
                <div
                    v-if="filteredStories.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <motion.div
                        v-for="(story, index) in filteredStories"
                        :key="story.id"
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -3 }"
                        :transition="{ duration: 0.22, ease: 'easeOut', delay: index * 0.04 }"
                        class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 overflow-hidden shadow-sm hover:border-[#000000] transition-all flex flex-col justify-between group cursor-pointer"
                        @click="openStoryModal(story)"
                    >
                        <div>
                            <!-- Card Image -->
                            <div class="relative h-48 w-full bg-neutral-100 overflow-hidden">
                                <img
                                    :src="story.image_url || 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80'"
                                    :alt="story.title"
                                    class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                />
                                <div class="absolute top-3 left-3">
                                    <span class="rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[10px] font-bold text-[#000000] shadow-sm">
                                        {{ story.category }}
                                    </span>
                                </div>
                                <div v-if="story.badge" class="absolute bottom-3 right-3">
                                    <span class="rounded-full bg-black/75 backdrop-blur-xs px-2.5 py-0.5 text-[9px] font-medium text-white">
                                        {{ story.badge }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-5 space-y-3">
                                <div class="flex items-center justify-between text-[11px] text-[#333333]/70">
                                    <span class="flex items-center gap-1">
                                        <Clock class="size-3" />
                                        {{ story.read_time }}
                                    </span>
                                    <span>{{ story.published_at }}</span>
                                </div>

                                <h3 class="font-['ivypresto-headline'] font-serif text-lg font-semibold text-[#000000] leading-snug group-hover:underline line-clamp-2">
                                    {{ story.title }}
                                </h3>

                                <p class="text-xs text-[#333333]/80 leading-relaxed line-clamp-3">
                                    {{ story.excerpt }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-5 pb-5 pt-2 border-t border-[#333333]/10 space-y-3">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center justify-between font-semibold text-[#000000]">
                                    <span>{{ story.patient_name }}</span>
                                    <span class="text-[10px] text-[#333333]/60 font-normal">
                                        {{ story.patient_age }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-[#333333]/80 truncate">
                                    {{ story.doctor_name }} • {{ story.poli_name }}
                                </p>
                            </div>

                            <button
                                type="button"
                                @click.stop="openStoryModal(story)"
                                class="w-full min-h-[40px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-4 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer"
                            >
                                <span>Baca Kisah Pasien</span>
                                <ChevronRight class="size-3.5 group-hover:translate-x-0.5 transition-transform" />
                            </button>
                        </div>
                    </motion.div>
                </div>

                <!-- Empty State if no stories match -->
                <div
                    v-else
                    class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-12 text-center space-y-4 max-w-md mx-auto"
                >
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#edede2] mx-auto text-[#000000]">
                        <Search class="size-6 text-[#333333]" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-['ivypresto-headline'] font-serif text-xl font-semibold text-[#000000]">
                            Tidak Ada Cerita Ditemukan
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            Coba ubah kata kunci pencarian atau pilih kategori cerita lainnya.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="resetFilters"
                        class="min-h-[44px] inline-flex items-center justify-center rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors cursor-pointer shadow-sm"
                    >
                        Reset Semua Filter
                    </button>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 7. CALL TO ACTION (CTA) BANNER (Evergreen Black Pill)
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-[10px] bg-[#000000] text-white p-8 sm:p-12 text-center space-y-6">
                    <div class="max-w-2xl mx-auto space-y-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                            Konsultasi Medis Terpercaya
                        </span>
                        <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight">
                            Punya Keluhan Kesehatan Serupa?
                        </h2>
                        <p class="text-xs sm:text-sm text-white/80 max-w-lg mx-auto leading-relaxed">
                            Jangan tunda pemeriksaan Anda. Tim dokter spesialis dan subspesialis Hospital Population siap mendampingi Anda dan keluarga dengan layanan prima.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href="/schedule-guest"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-white px-7 py-3 text-xs sm:text-sm font-semibold text-[#000000] hover:bg-[#edede2] transition-colors shadow-sm"
                        >
                            <Stethoscope class="size-4 text-[#000000]" />
                            <span>Lihat Jadwal Praktik &amp; Ambil Antrean</span>
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <!-- ═══════════════════════════════════════════════════════════
             8. FOOTER RUMAH SAKIT MULTI-KOLOM (Siloam Style)
             ═══════════════════════════════════════════════════════════ -->
        <footer class="border-t border-[#333333]/15 bg-[#fffff3] text-[#000000] pt-14 pb-8">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <!-- Main 4-Column Footer -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 text-xs">
                    <!-- Col 1 & 2: Hospital Identity & Mission -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#beedc0]">
                                <AppLogoIcon class="size-6 fill-current text-[#000000]" />
                            </div>
                            <div>
                                <span class="font-['ivypresto-headline'] font-serif text-xl font-bold text-[#000000] block leading-tight">
                                    Hospital Population
                                </span>
                                <span class="text-[11px] text-[#333333] font-medium block">
                                    Sistem Pelayanan &amp; Jadwal Praktik Terpadu
                                </span>
                            </div>
                        </div>

                        <p class="text-xs text-[#333333]/80 leading-relaxed max-w-sm">
                            Rumah sakit berstandar internasional yang mengutamakan keselamatan pasien, keunggulan klinis, dan kemudahan akses digital bagi seluruh lapisan masyarakat.
                        </p>

                        <div class="space-y-1.5 text-xs text-[#333333]">
                            <div class="flex items-center gap-2">
                                <Award class="size-4 text-[#000000]" />
                                <span>Terakreditasi KARS Paripurna (Kementerian Kesehatan RI)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ShieldCheck class="size-4 text-[#000000]" />
                                <span>Sertifikasi ISO 9001:2015 Mutu Pelayanan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3: Layanan Medis -->
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-[#000000] uppercase tracking-wider block border-b border-[#333333]/15 pb-1.5">
                            Layanan Medis
                        </span>
                        <ul class="space-y-2 text-[#333333]">
                            <li>
                                <Link href="/schedule-guest" class="hover:underline hover:text-[#000000]">
                                    Rawat Jalan &amp; Poliklinik
                                </Link>
                            </li>
                            <li>
                                <Link href="/specializations" class="hover:underline hover:text-[#000000]">
                                    Sub-Spesialisasi Medis
                                </Link>
                            </li>
                            <li>
                                <Link href="/patient-story" class="hover:underline hover:text-[#000000] font-semibold text-[#000000]">
                                    Kisah &amp; Cerita Pasien
                                </Link>
                            </li>
                            <li>
                                <a href="tel:1500181" class="font-bold text-red-600 hover:underline">
                                    IGD 24 Jam: 1-500-181
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Col 4: Informasi Pasien -->
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-[#000000] uppercase tracking-wider block border-b border-[#333333]/15 pb-1.5">
                            Informasi Pasien
                        </span>
                        <ul class="space-y-2 text-[#333333]">
                            <li>
                                <Link href="/display" class="hover:underline hover:text-[#000000] font-semibold text-[#000000]">
                                    Layar Monitor Antrean TV
                                </Link>
                            </li>
                            <li>
                                <Link href="/login" class="hover:underline hover:text-[#000000]">
                                    Portal Masuk Pasien
                                </Link>
                            </li>
                            <li>
                                <Link href="/register" class="hover:underline hover:text-[#000000]">
                                    Daftar Akun Baru
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Col 5: Kontak & Lokasi Darurat -->
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-[#000000] uppercase tracking-wider block border-b border-[#333333]/15 pb-1.5">
                            Kontak &amp; Lokasi
                        </span>
                        <div class="space-y-2.5 text-[#333333]">
                            <div class="flex items-start gap-2">
                                <MapPin class="size-4 text-[#000000] shrink-0 mt-0.5" />
                                <span>Jl. Kesehatan Utama No. 88, Kawasan Medis Terpadu</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone class="size-4 text-[#000000] shrink-0" />
                                <span>Hotline: (021) 500-1818</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Mail class="size-4 text-[#000000] shrink-0" />
                                <span>care@hospitalpopulation.com</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock class="size-4 text-[#000000] shrink-0" />
                                <span>Poliklinik: 08.00 – 21.00 WIB</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Copyright & Legal Strip -->
                <div class="pt-6 border-t border-[#333333]/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#333333]/70">
                    <p>© 2026 Hospital Population. Seluruh hak cipta dilindungi undang-undang.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="hover:underline">Kebijakan Privasi</a>
                        <span>•</span>
                        <a href="#" class="hover:underline">Syarat &amp; Ketentuan</a>
                        <span>•</span>
                        <a href="#" class="hover:underline">Hak Pasien</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Modal Dialog Baca Cerita Pasien Selengkapnya -->
        <StoryDetailModal
            v-model:open="isDetailModalOpen"
            :story="selectedStory"
        />
    </div>
</template>
