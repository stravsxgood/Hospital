<script setup lang="ts">
/**
 * @file Location.vue
 * @description Direktori Lokasi Klinik & Jaringan Rumah Sakit Publik (Hospital Population).
 * Terinspirasi dari direktori klinik Siloam Hospitals dengan tema desain "Evergreen".
 * Menyediakan filter cerdas (pencarian teks, wilayah kota, tipe fasilitas), profil cabang,
 * serta aksi cepat (Lihat Jadwal Dokter, Petunjuk Arah Google Maps, dan Kontak WhatsApp).
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
    ExternalLink,
    FileText,
    Heart,
    HeartHandshake,
    HeartPulse,
    HelpCircle,
    Hospital,
    LogIn,
    LogOut,
    MapPin,
    Menu,
    MessageSquare,
    Navigation,
    Phone,
    PhoneCall,
    RefreshCw,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    Ticket,
    Tv,
    User,
    UserPlus,
    X
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import type { ClinicBranchItem, ClinicLocationProps } from '@/types/hospital'

defineOptions({ layout: undefined })

const props = defineProps<ClinicLocationProps>()

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)
const isStaffUser = computed(() => {
    const role = currentUser.value?.role

    return ['doctor', 'nurse', 'admin'].includes(role || '') || Boolean(currentUser.value?.is_doctor)
})

// State Filter Pintar
const searchQuery = ref('')
const selectedCity = ref('Semua Wilayah')
const selectedType = ref('Semua')

// State Mega Menu & Mobile Drawer
const isMounted = ref(false)
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

/**
 * Quick jump to Poliklinik & Medical Team Profile page (/teams?poli=...)
 */
const jumpToPoliTeam = (poliName: string) => {
    isMegaMenuOpen.value = false
    closeMobileMenu()
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
 * Jump to Doctor Schedule with clinic name prefilled
 */
const jumpToClinicSchedule = (clinicName: string) => {
    router.visit('/schedule-guest?search=' + encodeURIComponent(clinicName))
}

/**
 * Reset all active filters
 */
const resetFilters = () => {
    searchQuery.value = ''
    selectedCity.value = 'Semua Wilayah'
    selectedType.value = 'Semua'
}

/**
 * Filtered clinics computation
 */
const filteredClinics = computed(() => {
    return (props.clinics || []).filter((clinic) => {
        // Text search (name, address, city, province, available polis)
        const q = searchQuery.value.toLowerCase().trim()
        const matchSearch =
            !q ||
            clinic.name.toLowerCase().includes(q) ||
            clinic.address.toLowerCase().includes(q) ||
            clinic.city.toLowerCase().includes(q) ||
            clinic.province.toLowerCase().includes(q) ||
            clinic.available_polis.some((p) => p.toLowerCase().includes(q))

        // City filter
        const matchCity =
            selectedCity.value === 'Semua Wilayah' ||
            clinic.city.toLowerCase() === selectedCity.value.toLowerCase()

        // Facility type filter
        let matchType = true

        if (selectedType.value === 'IGD 24 Jam') {
            matchType = clinic.emergency_24h === true
        } else if (selectedType.value !== 'Semua') {
            matchType =
                clinic.facility_type.toLowerCase() === selectedType.value.toLowerCase() ||
                clinic.category_badge.toLowerCase().includes(selectedType.value.toLowerCase())
        }

        return matchSearch && matchCity && matchType
    })
})

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
]

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
    <Head title="Lokasi Klinik & Jaringan Rumah Sakit - Hospital Population" />

    <div class="min-h-screen bg-[#edede2] text-[#000000] font-['Rubik'] antialiased selection:bg-[#beedc0] selection:text-[#000000]">

        <!-- ═══════════════════════════════════════════════════════════
             1. TOP UTILITY & EMERGENCY BAR
             ═══════════════════════════════════════════════════════════ -->
        <div class="bg-[#000000] text-white text-xs py-2 px-4 sm:px-6 lg:px-8 border-b border-[#333333]/40">
            <div class="max-w-[1200px] mx-auto flex items-center justify-between gap-4 flex-nowrap overflow-x-auto scrollbar-none">
                <!-- Left: Emergency & Contact Hotline -->
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
                    <!-- Link Cari Dokter -->
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

                        <!-- MEGA MENU DROPDOWN PANEL (with hover bridge pt-2) -->
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
                                    <span class="whitespace-nowrap">Ingin melihat profil spesialisasi dan teknologi tindakan medis?</span>
                                    <Link
                                        href="/specializations"
                                        @click="isMegaMenuOpen = false"
                                        class="font-semibold text-[#000000] underline underline-offset-4 hover:text-[#333333] inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shrink-0"
                                    >
                                        <span>Buka Sub-Spesialisasi Medis</span>
                                        <ArrowRight class="size-3.5 shrink-0" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Lokasi Klinik (Active Page) -->
                    <Link
                        href="/clinic-location"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-[#000000] bg-[#000000] text-white shadow-sm transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <span class="size-1.5 rounded-full bg-[#beedc0] animate-pulse shrink-0" />
                        <MapPin class="size-4 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap">Lokasi Klinik</span>
                    </Link>

                    <!-- Link Cerita Pasien -->
                    <Link
                        href="/patient-story"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-transparent text-[#333333] hover:text-[#000000] hover:bg-[#fffff3] hover:border-[#333333]/15 transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <HeartHandshake class="size-4 text-[#000000] shrink-0" />
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
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-[#000000] text-[#beedc0] flex items-center justify-center">
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
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] hover:bg-[#edede2] transition-colors"
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

        <!-- ═══════════════════════════════════════════════════════════
             3. HERO SECTION & EDITORIAL HEADING (Evergreen Siloam Style)
             ═══════════════════════════════════════════════════════════ -->
        <section class="relative pt-10 pb-8 sm:pt-14 sm:pb-12 border-b border-[#333333]/10 bg-[#edede2]">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Tag & Headline -->
                <div class="max-w-3xl space-y-4">
                    <motion.div
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3 }"
                        class="inline-flex items-center gap-2 rounded-full border border-[#333333]/15 bg-[#beedc0] px-3.5 py-1 text-xs font-semibold text-[#000000]"
                    >
                        <Hospital class="size-3.5 shrink-0" />
                        <span>Direktori Jaringan Rumah Sakit &amp; Klinik</span>
                    </motion.div>

                    <motion.h1
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.35, delay: 0.05 }"
                        class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-[#000000] leading-[1.15]"
                    >
                        Temukan Fasilitas Kesehatan Terdekat untuk Anda &amp; Keluarga
                    </motion.h1>

                    <motion.p
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.35, delay: 0.1 }"
                        class="text-sm sm:text-base text-[#333333] leading-relaxed"
                    >
                        Jaringan rumah sakit utama, klinik spesialis eksekutif, dan fasilitas kesehatan primer kami tersebar di berbagai kota strategis di Indonesia dengan dukungan tenaga medis profesional, fasilitas diagnostik modern, dan layanan IGD 24 Jam.
                    </motion.p>
                </div>

                <!-- 4 Highlights Metric Cards -->
                <motion.div
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.35, delay: 0.15 }"
                    class="grid grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 shadow-sm space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]/70">Jaringan Nasional</span>
                            <Building2 class="size-4 text-[#000000]" />
                        </div>
                        <p class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000]">10+ Cabang</p>
                        <p class="text-[11px] text-[#333333]/80">RS Utama &amp; Klinik Pratama/Utama</p>
                    </div>

                    <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 shadow-sm space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]/70">Layanan Darurat</span>
                            <PhoneCall class="size-4 text-red-600" />
                        </div>
                        <p class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000]">24 Jam</p>
                        <p class="text-[11px] text-[#333333]/80">IGD &amp; Ambulans Cepat Siaga</p>
                    </div>

                    <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 shadow-sm space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]/70">Dokter Spesialis</span>
                            <Stethoscope class="size-4 text-[#000000]" />
                        </div>
                        <p class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000]">500+ Dokter</p>
                        <p class="text-[11px] text-[#333333]/80">Tenaga Medis Ber-SIP Aktif</p>
                    </div>

                    <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 shadow-sm space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#333333]/70">Akreditasi Mutu</span>
                            <Award class="size-4 text-[#000000]" />
                        </div>
                        <p class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000]">Paripurna</p>
                        <p class="text-[11px] text-[#333333]/80">Standar Tertinggi KARS &amp; JCI</p>
                    </div>
                </motion.div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════
             4. SMART FILTER WIDGET (Siloam Style Filter Bar)
             ═══════════════════════════════════════════════════════════ -->
        <section class="sticky top-20 z-30 bg-[#edede2]/95 backdrop-blur-md border-b border-[#333333]/15 py-4 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Text Search Input (Col 1-7) -->
                    <div class="md:col-span-7 relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 size-4 text-[#333333]/60" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama klinik, poliklinik, atau nama jalan/area..."
                            class="min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#fffff3] pl-11 pr-10 py-2.5 text-xs sm:text-sm text-[#000000] placeholder:text-[#333333]/60 focus:border-[#000000] focus:outline-none focus:ring-1 focus:ring-[#000000] transition-all shadow-sm"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 size-6 rounded-full bg-[#edede2] flex items-center justify-center text-[#333333] hover:text-[#000000] transition-colors"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>

                    <!-- City Select Dropdown (Col 8-12) -->
                    <div class="md:col-span-5 relative">
                        <MapPin class="absolute left-4 top-1/2 -translate-y-1/2 size-4 text-[#333333]/60 pointer-events-none" />
                        <select
                            v-model="selectedCity"
                            class="min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#fffff3] pl-11 pr-10 py-2.5 text-xs sm:text-sm font-medium text-[#000000] focus:border-[#000000] focus:outline-none focus:ring-1 focus:ring-[#000000] transition-all shadow-sm appearance-none cursor-pointer"
                        >
                            <option v-for="city in cities" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 size-4 text-[#333333]/60 pointer-events-none" />
                    </div>
                </div>

                <!-- Quick Filter Chips & Result Counter -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <!-- Chip Pills -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="typeItem in facilityTypes"
                            :key="typeItem"
                            type="button"
                            @click="selectedType = typeItem"
                            :class="[
                                'min-h-[36px] px-3.5 py-1.5 rounded-[40.5px] text-xs font-semibold transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5',
                                selectedType === typeItem
                                    ? 'bg-[#000000] text-white shadow-sm'
                                    : 'bg-[#fffff3] border border-[#333333]/15 text-[#333333] hover:bg-[#edede2] hover:text-[#000000]'
                            ]"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="selectedType === typeItem ? 'bg-[#beedc0]' : 'bg-[#333333]/30'"
                            />
                            <span>{{ typeItem }}</span>
                        </button>
                    </div>

                    <!-- Reset & Result Counter -->
                    <div class="flex items-center gap-3 text-xs text-[#333333]">
                        <span class="font-medium whitespace-nowrap">
                            Menampilkan <strong>{{ filteredClinics.length }}</strong> dari <strong>{{ clinics.length }}</strong> lokasi
                        </span>
                        <button
                            v-if="searchQuery || selectedCity !== 'Semua Wilayah' || selectedType !== 'Semua'"
                            type="button"
                            @click="resetFilters"
                            class="inline-flex items-center gap-1 text-[#000000] font-semibold underline underline-offset-2 hover:text-[#333333] cursor-pointer whitespace-nowrap"
                        >
                            <RefreshCw class="size-3" />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════
             5. CLINIC DIRECTORY CARDS GRID
             ═══════════════════════════════════════════════════════════ -->
        <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-12">

            <!-- Cards Grid (2 Columns on Desktop) -->
            <div v-if="filteredClinics.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                <motion.article
                    v-for="(clinic, idx) in filteredClinics"
                    :key="clinic.id"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, delay: Math.min(idx * 0.05, 0.3) }"
                    class="rounded-[12px] bg-[#fffff3] border border-[#333333]/15 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between group"
                >
                    <!-- Top Media & Status Banner -->
                    <div class="relative h-44 sm:h-52 w-full overflow-hidden bg-neutral-100">
                        <img
                            :src="clinic.image_url"
                            :alt="clinic.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />

                        <!-- Top Badges Overlay -->
                        <div class="absolute top-3.5 left-3.5 right-3.5 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#000000]/80 backdrop-blur-md px-3 py-1 text-[11px] font-semibold text-white border border-white/20">
                                <Building2 class="size-3 text-[#beedc0]" />
                                <span>{{ clinic.facility_type }}</span>
                            </span>

                            <span
                                v-if="clinic.emergency_24h"
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-950/80 backdrop-blur-md px-3 py-1 text-[11px] font-bold text-emerald-300 border border-emerald-500/30"
                            >
                                <span class="size-2 rounded-full bg-emerald-400 animate-pulse" />
                                <span>IGD 24 Jam Siaga</span>
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 rounded-full bg-neutral-900/80 backdrop-blur-md px-3 py-1 text-[11px] font-medium text-neutral-300 border border-white/10"
                            >
                                <span>Buka Sesuai Jadwal</span>
                            </span>
                        </div>

                        <!-- Bottom Information inside Media -->
                        <div class="absolute bottom-3 left-3.5 right-3.5 text-white">
                            <span class="text-[11px] font-mono text-[#beedc0] block uppercase tracking-wider">
                                {{ clinic.category_badge }}
                            </span>
                            <h2 class="font-['ivypresto-headline'] font-serif text-lg sm:text-xl font-bold tracking-tight text-white line-clamp-1">
                                {{ clinic.name }}
                            </h2>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-5 sm:p-6 space-y-5 flex-1 flex flex-col justify-between">
                        <div class="space-y-4">
                            <!-- Location Note / Landmark -->
                            <div class="flex items-start gap-2 text-xs text-[#333333]">
                                <MapPin class="size-4 text-[#000000] shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-medium text-[#000000] leading-snug">{{ clinic.address }}</p>
                                    <p class="text-[11px] text-[#333333]/75 mt-0.5">{{ clinic.distance_info }}</p>
                                </div>
                            </div>

                            <!-- Operating Hours -->
                            <div class="flex items-start gap-2 text-xs text-[#333333] pt-1">
                                <Clock class="size-4 text-[#000000] shrink-0 mt-0.5" />
                                <div>
                                    <span class="font-semibold text-[#000000] block">Jam Layanan:</span>
                                    <span class="text-[11px] text-[#333333]/85">{{ clinic.operating_hours }}</span>
                                </div>
                            </div>

                            <!-- Facility Capacity Badges -->
                            <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px]">
                                <span class="inline-flex items-center gap-1 rounded-[6px] bg-[#edede2] px-2.5 py-1 font-semibold text-[#000000] border border-[#333333]/10">
                                    <Stethoscope class="size-3 text-[#000000]" />
                                    <span>{{ clinic.doctor_count }} Dokter Praktik</span>
                                </span>
                                <span
                                    v-if="clinic.bed_capacity > 0"
                                    class="inline-flex items-center gap-1 rounded-[6px] bg-[#edede2] px-2.5 py-1 font-semibold text-[#000000] border border-[#333333]/10"
                                >
                                    <Bed class="size-3 text-[#000000]" />
                                    <span>{{ clinic.bed_capacity }} Tempat Tidur</span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-[6px] bg-[#beedc0] px-2.5 py-1 font-bold text-[#000000] border border-[#333333]/10">
                                    <Award class="size-3 text-[#000000]" />
                                    <span>KARS Paripurna</span>
                                </span>
                            </div>

                            <!-- Available Polyclinics Tags -->
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#333333]/70 block">
                                    Poliklinik Tersedia di Cabang Ini:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="poli in clinic.available_polis.slice(0, 5)"
                                        :key="poli"
                                        class="inline-flex items-center rounded-full bg-white border border-[#333333]/15 px-2.5 py-0.5 text-[11px] text-[#333333]"
                                    >
                                        {{ poli }}
                                    </span>
                                    <span
                                        v-if="clinic.available_polis.length > 5"
                                        class="inline-flex items-center rounded-full bg-[#edede2] border border-[#333333]/15 px-2 py-0.5 text-[10px] font-semibold text-[#000000]"
                                    >
                                        +{{ clinic.available_polis.length - 5 }} Lainnya
                                    </span>
                                </div>
                            </div>

                            <!-- Featured Diagnostic Facilities -->
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#333333]/70 block">
                                    Fasilitas Unggulan:
                                </span>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] text-[#333333]/90">
                                    <li v-for="fac in clinic.featured_facilities.slice(0, 4)" :key="fac" class="flex items-center gap-1.5">
                                        <CheckCircle2 class="size-3 text-emerald-600 shrink-0" />
                                        <span class="truncate">{{ fac }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Card Action Buttons (Min touch target 44px) -->
                        <div class="pt-5 border-t border-[#333333]/10 space-y-2.5">
                            <!-- Main Action: Lihat Jadwal Dokter -->
                            <motion.button
                                type="button"
                                :whileHover="{ scale: 1.01 }"
                                :whileTap="{ scale: 0.98 }"
                                @click="jumpToClinicSchedule(clinic.name)"
                                class="min-h-[44px] w-full inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-5 py-2.5 text-xs sm:text-sm font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer whitespace-nowrap"
                            >
                                <Stethoscope class="size-4 text-[#beedc0] shrink-0" />
                                <span>Lihat Jadwal Dokter Cabang Ini</span>
                                <ArrowRight class="size-3.5 shrink-0" />
                            </motion.button>

                            <!-- Secondary Actions: Petunjuk Arah & Kontak Telepon/WA -->
                            <div class="grid grid-cols-2 gap-2">
                                <a
                                    :href="clinic.google_maps_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-3 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer whitespace-nowrap"
                                >
                                    <Navigation class="size-3.5 text-[#000000] shrink-0" />
                                    <span>Petunjuk Arah</span>
                                    <ExternalLink class="size-3 text-[#333333]/50 shrink-0" />
                                </a>

                                <a
                                    :href="'https://wa.me/' + clinic.whatsapp.replace(/[^0-9]/g, '')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/15 bg-[#beedc0] px-3 py-2 text-xs font-bold text-[#000000] hover:bg-[#aee6b1] transition-colors cursor-pointer whitespace-nowrap shadow-sm"
                                >
                                    <MessageSquare class="size-3.5 text-[#000000] shrink-0" />
                                    <span>Hubungi WA</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </motion.article>
            </div>

            <!-- ═══════════════════════════════════════════════════════
                 6. EMPTY STATE (Ketika filter tidak cocok)
                 ═══════════════════════════════════════════════════════ -->
            <div
                v-else
                class="rounded-[12px] bg-[#fffff3] border border-[#333333]/15 p-8 sm:p-12 text-center max-w-xl mx-auto space-y-4 shadow-sm"
            >
                <div class="size-14 rounded-full bg-[#edede2] border border-[#333333]/10 flex items-center justify-center mx-auto text-[#333333]">
                    <Search class="size-6" />
                </div>
                <div class="space-y-1.5">
                    <h3 class="font-['ivypresto-headline'] font-serif text-xl font-bold text-[#000000]">
                        Tidak Ada Cabang yang Cocok
                    </h3>
                    <p class="text-xs sm:text-sm text-[#333333]/80">
                        Kami tidak menemukan lokasi fasilitas kesehatan untuk kata kunci "<strong>{{ searchQuery }}</strong>" di wilayah "<strong>{{ selectedCity }}</strong>".
                    </p>
                </div>
                <button
                    type="button"
                    @click="resetFilters"
                    class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer"
                >
                    <RefreshCw class="size-3.5 text-[#beedc0]" />
                    <span>Tampilkan Semua Cabang</span>
                </button>
            </div>

            <!-- ═══════════════════════════════════════════════════════
                 7. EMERGENCY 24-HOUR CALL CENTER BANNER
                 ═══════════════════════════════════════════════════════ -->
            <section class="rounded-[14px] bg-[#000000] text-white p-6 sm:p-10 border border-[#333333]/30 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 size-64 bg-[#beedc0]/10 rounded-full blur-3xl pointer-events-none" />
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    <div class="lg:col-span-8 space-y-2">
                        <div class="inline-flex items-center gap-2 text-xs font-bold text-[#beedc0] tracking-wider uppercase">
                            <span class="size-2 rounded-full bg-red-500 animate-ping" />
                            <span>Layanan Gawat Darurat &amp; Evakuasi Medis</span>
                        </div>
                        <h3 class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-white tracking-tight">
                            Butuh Bantuan Medis Cepat atau Penjemputan Ambulans?
                        </h3>
                        <p class="text-xs sm:text-sm text-white/80 leading-relaxed max-w-2xl">
                            Tim paramedis IGD dan armada ambulans darurat kami siaga 24 jam penuh di seluruh jaringan rumah sakit utama kami untuk menangani kondisi kritis dengan standar keselamatan tertinggi.
                        </p>
                    </div>

                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        <a
                            href="tel:1500181"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-red-600 hover:bg-red-700 text-white px-6 py-3 text-xs sm:text-sm font-bold transition-colors shadow-lg cursor-pointer whitespace-nowrap"
                        >
                            <PhoneCall class="size-4 shrink-0" />
                            <span>Panggil IGD: 1-500-181</span>
                        </a>

                        <a
                            href="https://wa.me/6281100000000"
                            target="_blank"
                            rel="noopener"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#beedc0] hover:bg-[#aee6b1] text-[#000000] px-6 py-3 text-xs sm:text-sm font-bold transition-colors shadow-md cursor-pointer whitespace-nowrap"
                        >
                            <MessageSquare class="size-4 shrink-0 text-[#000000]" />
                            <span>Chat WhatsApp Care</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- ═══════════════════════════════════════════════════════════
             8. PUBLIC FOOTER (Matches Evergreen Style)
             ═══════════════════════════════════════════════════════════ -->
        <footer class="bg-[#fffff3] border-t border-[#333333]/15 pt-12 pb-8 text-[#000000]">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <!-- Brand Column (Col 1-4) -->
                    <div class="md:col-span-4 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#beedc0] border border-[#333333]/10">
                                <AppLogoIcon class="size-6 fill-current text-[#000000]" />
                            </div>
                            <span class="font-['ivypresto-headline'] font-serif text-xl font-bold tracking-tight text-[#000000]">
                                Hospital Population
                            </span>
                        </div>
                        <p class="text-xs text-[#333333] leading-relaxed">
                            Sistem Manajemen Pelayanan Kesehatan &amp; Poliklinik Terpadu. Menghadirkan kemudahan reservasi antrean, konsultasi dokter spesialis, serta akses informasi rekam medis yang aman dan transparan bagi seluruh masyarakat Indonesia.
                        </p>
                        <div class="text-xs text-[#333333] space-y-1">
                            <p class="font-semibold text-[#000000]">Kantor Pusat Manajemen:</p>
                            <p>Jl. Siloam No. 6, Lippo Karawaci, Tangerang, Banten 15811</p>
                            <p>Call Center 24 Jam: <strong>1-500-181</strong></p>
                        </div>
                    </div>

                    <!-- Column 2: Layanan & Fasilitas (Col 5-7) -->
                    <div class="md:col-span-3 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#000000] border-b border-[#333333]/10 pb-1">
                            Layanan Rumah Sakit
                        </h4>
                        <ul class="space-y-2 text-xs text-[#333333]">
                            <li><Link href="/schedule-guest" class="hover:underline hover:text-[#000000]">Jadwal Praktik Dokter</Link></li>
                            <li><Link href="/specializations" class="hover:underline hover:text-[#000000]">Sub-Spesialisasi Medis</Link></li>
                            <li><Link href="/teams?poli=Poli+Umum" class="hover:underline hover:text-[#000000]">Tim Medis Poliklinik</Link></li>
                            <li><Link href="/clinic-location" class="hover:underline font-semibold text-[#000000]">Direktori Lokasi Klinik</Link></li>
                            <li><Link href="/display" class="hover:underline hover:text-[#000000]">Layar Antrean TV</Link></li>
                            <li><Link href="/patient-story" class="hover:underline hover:text-[#000000]">Cerita Pasien Inspiratif</Link></li>
                        </ul>
                    </div>

                    <!-- Column 3: Poliklinik Unggulan (Col 8-10) -->
                    <div class="md:col-span-3 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#000000] border-b border-[#333333]/10 pb-1">
                            Poliklinik Unggulan
                        </h4>
                        <ul class="space-y-2 text-xs text-[#333333]">
                            <li><Link href="/teams?poli=Poli+Jantung+%26+Pembuluh+Darah" class="hover:underline hover:text-[#000000]">Poli Jantung &amp; Vaskular</Link></li>
                            <li><Link href="/teams?poli=Poli+Bedah+%26+Ortopedi" class="hover:underline hover:text-[#000000]">Poli Bedah &amp; Ortopedi</Link></li>
                            <li><Link href="/teams?poli=Poli+Anak+%26+Tumbuh+Kembang" class="hover:underline hover:text-[#000000]">Poli Anak &amp; Tumbuh Kembang</Link></li>
                            <li><Link href="/teams?poli=Poli+Penyakit+Dalam" class="hover:underline hover:text-[#000000]">Poli Penyakit Dalam</Link></li>
                            <li><Link href="/teams?poli=Poli+Kebidanan+%26+Kandungan" class="hover:underline hover:text-[#000000]">Poli Kebidanan &amp; Kandungan</Link></li>
                        </ul>
                    </div>

                    <!-- Column 4: Akreditasi & Sertifikasi (Col 11-12) -->
                    <div class="md:col-span-2 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#000000] border-b border-[#333333]/10 pb-1">
                            Akreditasi Mutu
                        </h4>
                        <div class="space-y-2 text-xs text-[#333333]">
                            <div class="p-2.5 rounded-[8px] bg-[#edede2] border border-[#333333]/10 space-y-1">
                                <span class="font-bold text-[#000000] block text-[11px]">KARS Paripurna</span>
                                <span class="text-[10px] text-[#333333]/70 block">Standar Pelayanan Medis Tertinggi</span>
                            </div>
                            <div class="p-2.5 rounded-[8px] bg-[#edede2] border border-[#333333]/10 space-y-1">
                                <span class="font-bold text-[#000000] block text-[11px]">Kemenkes RI</span>
                                <span class="text-[10px] text-[#333333]/70 block">Izin Operasional Resmi Rumah Sakit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright Bottom -->
                <div class="pt-6 border-t border-[#333333]/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#333333]">
                    <p>&copy; 2026 Hospital Population. Hak Cipta Dilindungi Undang-Undang.</p>
                    <div class="flex items-center gap-4 text-xs">
                        <Link href="/#faq" class="hover:underline">Panduan Pasien</Link>
                        <span>&middot;</span>
                        <Link href="/#fasilitas" class="hover:underline">Fasilitas</Link>
                        <span>&middot;</span>
                        <a href="https://wa.me/6281100000000" target="_blank" rel="noopener" class="hover:underline font-semibold text-[#000000]">Kontak Bantuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
