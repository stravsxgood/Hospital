<script setup lang="ts">
/**
 * @file Specializations/Index.vue
 * @description Public Layanan & Sub-Spesialisasi Medis page for Hospital Population.
 * Features an editorial Siloam-inspired design, interactive sub-specialization switcher,
 * conditions treated catalog, medical procedures showcase, doctor schedule integration with BookingModal,
 * FAQ accordion, and Evergreen design system compliance.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import BookingModal from '@/components/BookingModal.vue'
import TicketSuccessModal from '@/components/TicketSuccessModal.vue'
import type { TicketData } from '@/components/TicketSuccessModal.vue'
import type {
    Doctor,
    DoctorSchedule,
    SpecializationDetail,
    SpecializationTabItem,
} from '@/types'

// Wajib mandiri tanpa sidebar default
defineOptions({
    layout: undefined,
})

/* ═══════════════════════════════════════════════════════════════
   Props & Backend Data
   ═══════════════════════════════════════════════════════════════ */
const props = withDefaults(
    defineProps<{
        specializations: SpecializationTabItem[]
        currentSpecialization: SpecializationDetail
        schedules?: DoctorSchedule[]
        doctors?: Doctor[]
    }>(),
    {
        schedules: () => [],
        doctors: () => [],
    },
)

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)
const isStaffUser = computed(() => {
    const role = currentUser.value?.role
    return ['doctor', 'nurse', 'admin'].includes(role || '') || Boolean(currentUser.value?.is_doctor)
})

/* ═══════════════════════════════════════════════════════════════
   Reactive State for Search, Filters, Accordion, Modals & Drawer
   ═══════════════════════════════════════════════════════════════ */
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

const doctorSearchQuery = ref('')
const selectedDayFilter = ref('Semua')
const openFaqIndex = ref<number | null>(0)

// State Modal Booking & Karcis Antrean
const isBookingModalOpen = ref(false)
const selectedSchedule = ref<DoctorSchedule | null>(null)
const isTicketModalOpen = ref(false)
const activeTicket = ref<TicketData | null>(null)

/**
 * Filtered schedules for the active specialization and optional search
 */
const filteredSchedules = computed(() => {
    let list = props.schedules

    // Filter by practice day
    if (selectedDayFilter.value !== 'Semua') {
        list = list.filter((sch) => {
            const day = sch.day || sch.day_of_week || ''
            return day.toLowerCase() === selectedDayFilter.value.toLowerCase()
        })
    }

    // Filter by doctor name or poli
    if (doctorSearchQuery.value.trim()) {
        const query = doctorSearchQuery.value.toLowerCase().trim()
        list = list.filter((sch) => {
            const docName = sch.doctor?.name?.toLowerCase() || ''
            const poliName = sch.poli?.name?.toLowerCase() || sch.poli?.name_poli?.toLowerCase() || ''
            const specName =
                typeof sch.doctor?.specialization === 'string'
                    ? sch.doctor.specialization.toLowerCase()
                    : sch.doctor?.specialization?.name_specialization?.toLowerCase() ||
                      sch.doctor?.specialization?.name?.toLowerCase() ||
                      ''
            return docName.includes(query) || poliName.includes(query) || specName.includes(query)
        })
    }

    return list
})

/**
 * Unique days list for schedule day filter
 */
const availableDays = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']

/**
 * Switch active specialization via router visit
 */
const changeSpecialization = (slug: string) => {
    isMegaMenuOpen.value = false
    router.visit(`/specializations?slug=${slug}`, {
        preserveScroll: false,
    })
}

/**
 * Smooth scroll helper with sticky header offset
 */
const scrollToSection = (sectionId: string) => {
    isMegaMenuOpen.value = false
    setTimeout(() => {
        const element = document.getElementById(sectionId)
        if (element) {
            const headerOffset = 90
            const elementPosition = element.getBoundingClientRect().top
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth',
            })
        }
    }, 50)
}

/**
 * Handle booking button click
 */
const handleBookSchedule = (sch: DoctorSchedule) => {
    if (!currentUser.value) {
        // Redirect guest to login with message
        router.visit('/login')
        return
    }

    selectedSchedule.value = sch
    isBookingModalOpen.value = true
}

/**
 * Handle successful appointment booking from BookingModal
 */
const handleBookingSuccess = (appointmentData: any) => {
    isBookingModalOpen.value = false

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
            'Poliklinik Terpadu',
        appointment_date: appointmentData.appointment_date || new Date().toISOString().split('T')[0],
        patient_name: appointmentData.patient?.name || currentUser.value?.name || 'Pasien',
        resident_n: appointmentData.patient?.resident_n || '',
    }

    isTicketModalOpen.value = true
}

/**
 * Toggle FAQ accordion item
 */
const toggleFaq = (index: number) => {
    if (openFaqIndex.value === index) {
        openFaqIndex.value = null
    } else {
        openFaqIndex.value = index
    }
}

/**
 * Helper to jump to polyclinic team page from mega menu
 */
const jumpToPoliTeam = (poli: string) => {
    isMegaMenuOpen.value = false
    router.visit('/teams?poli=' + encodeURIComponent(poli))
}

/**
 * Helper to jump to doctor search with poli preselected
 */
const jumpToDoctorSearch = (poli?: string) => {
    isMegaMenuOpen.value = false
    if (poli) {
        router.visit('/schedule-guest?poli=' + encodeURIComponent(poli))
    } else {
        router.visit('/schedule-guest')
    }
}

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
    <Head :title="`${currentSpecialization.name} — Hospital Population`">
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

                    <!-- Trigger Mega Menu Layanan & Spesialisasi (Active Section) -->
                    <div
                        class="relative"
                        @mouseenter="isMegaMenuOpen = true"
                        @mouseleave="isMegaMenuOpen = false"
                    >
                        <button
                            type="button"
                            @click="isMegaMenuOpen = !isMegaMenuOpen"
                            class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-[#000000] bg-[#000000] text-white shadow-sm transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer focus:outline-none"
                        >
                            <span class="size-1.5 rounded-full bg-[#beedc0] animate-pulse shrink-0" />
                            <Activity class="size-4 text-[#beedc0] shrink-0" />
                            <span class="whitespace-nowrap">Layanan &amp; Spesialisasi</span>
                            <ChevronDown
                                class="size-3.5 transition-transform duration-200 shrink-0 text-white"
                                :class="{ 'rotate-180': isMegaMenuOpen }"
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
                                        @click="isMobileMenuOpen = false; router.visit('/teams?poli=' + encodeURIComponent(poli))"
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
                                class="min-h-[44px] w-full flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] bg-[#edede2] transition-colors cursor-pointer"
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
             3. BREADCRUMBS & SUB-SPECIALIZATION TAB SWITCHER
             ═══════════════════════════════════════════════════════════ -->
        <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-4">
            <!-- Breadcrumbs Nav -->
            <div class="flex items-center gap-2 text-xs text-[#333333]/70">
                <Link href="/" class="hover:text-[#000000] hover:underline">Beranda</Link>
                <span>/</span>
                <Link href="/specializations" class="hover:text-[#000000] hover:underline">Layanan &amp; Spesialisasi</Link>
                <span>/</span>
                <span class="font-semibold text-[#000000] truncate max-w-[200px] sm:max-w-md">
                    {{ currentSpecialization.short_name }}
                </span>
            </div>

            <!-- Horizontal Tab Switcher Pills -->
            <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-2 shadow-sm flex items-center gap-2 overflow-x-auto scrollbar-none">
                <span class="text-xs font-bold text-[#000000] uppercase tracking-wider px-3 whitespace-nowrap hidden sm:inline">
                    Pilih Spesialisasi:
                </span>
                <button
                    v-for="spec in specializations"
                    :key="spec.slug"
                    type="button"
                    @click="changeSpecialization(spec.slug)"
                    :class="[
                        'min-h-[40px] px-4 py-2 rounded-[40.5px] text-xs font-semibold transition-all whitespace-nowrap cursor-pointer flex items-center gap-2 shrink-0',
                        currentSpecialization.slug === spec.slug
                            ? 'bg-[#000000] text-white shadow-md'
                            : 'bg-white border border-[#333333]/15 text-[#333333] hover:bg-[#edede2] hover:text-[#000000]'
                    ]"
                >
                    <span class="size-2 rounded-full" :class="currentSpecialization.slug === spec.slug ? 'bg-[#beedc0]' : 'bg-[#333333]/30'" />
                    <span>{{ spec.short_name }}</span>
                </button>
            </div>
        </section>

        <main class="space-y-12 sm:space-y-16 pb-20 pt-4">

            <!-- ═══════════════════════════════════════════════════════
                 4. EDITORIAL HERO BANNER
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div
                    :initial="{ opacity: 0, y: 15 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25, ease: 'easeOut' }"
                    class="rounded-[12px] bg-[#fffff3] border border-[#333333]/15 p-6 sm:p-10 lg:p-12 space-y-8 shadow-sm"
                >
                    <!-- Top Category & Badges -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#333333]/10 pb-4">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-1 text-xs font-bold text-[#000000]">
                                <Activity class="size-3.5" />
                                {{ currentSpecialization.category }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#000000] px-3 py-1 text-xs font-semibold text-white">
                                <Award class="size-3 text-[#beedc0]" />
                                {{ currentSpecialization.badge }}
                            </span>
                        </div>

                        <span class="text-xs font-mono font-semibold text-[#333333]/70">
                            {{ currentSpecialization.tagline }}
                        </span>
                    </div>

                    <!-- Headline & Summary -->
                    <div class="space-y-4 max-w-4xl">
                        <h1 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-[#000000] leading-tight">
                            {{ currentSpecialization.name }}
                        </h1>

                        <p class="text-sm sm:text-base text-[#333333] leading-relaxed">
                            {{ currentSpecialization.description }}
                        </p>
                    </div>

                    <!-- Clinical Philosophy Quote -->
                    <div
                        v-if="currentSpecialization.quote"
                        class="rounded-[8px] bg-[#edede2]/70 border-l-4 border-[#000000] p-4 sm:p-5 text-xs sm:text-sm italic font-serif text-[#000000] leading-relaxed flex items-start gap-3"
                    >
                        <Quote class="size-5 text-[#000000] shrink-0 mt-0.5 opacity-60" />
                        <div>
                            <span>{{ currentSpecialization.quote }}</span>
                            <span class="block text-right font-sans font-semibold text-[11px] text-[#333333] mt-1.5">
                                — Tim Konsultan Subspesialis Hospital Population
                            </span>
                        </div>
                    </div>

                    <!-- 4 Quick Metric Cards Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                        <div
                            v-for="metric in currentSpecialization.metrics"
                            :key="metric.label"
                            class="bg-white p-4 rounded-[8px] border border-[#333333]/10 space-y-1 shadow-2xs flex flex-col justify-between"
                        >
                            <span class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold text-[#000000] block">
                                {{ metric.value }}
                            </span>
                            <div>
                                <h4 class="font-semibold text-xs text-[#000000] leading-snug">
                                    {{ metric.label }}
                                </h4>
                                <p class="text-[11px] text-[#333333]/70 mt-0.5">
                                    {{ metric.desc }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button
                            type="button"
                            @click="scrollToSection('tim-dokter')"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs sm:text-sm font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer whitespace-nowrap"
                        >
                            <Stethoscope class="size-4 text-[#beedc0]" />
                            <span>Lihat Jadwal Praktik Dokter</span>
                            <ArrowRight class="size-4" />
                        </button>

                        <a
                            href="https://wa.me/6281100000000?text=Halo%20Hospital%20Population,%20saya%20ingin%20konsultasi%20layanan%20spesialisasi"
                            target="_blank"
                            rel="noopener"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] border border-[#333333]/20 bg-white px-6 py-2.5 text-xs sm:text-sm font-semibold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer whitespace-nowrap"
                        >
                            <MessageSquare class="size-4 text-[#000000]" />
                            <span>Konsultasi Cepat via WhatsApp</span>
                        </a>
                    </div>
                </motion.div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 5. PENYAKIT & GEJALA YANG DITANGANI (Conditions Treated)
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                        <HeartPulse class="size-3.5" />
                        Indikasi Medis &amp; Diagnosis
                    </span>
                    <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl font-semibold text-[#000000]">
                        Kondisi &amp; Penyakit yang Ditangani
                    </h2>
                    <p class="text-xs sm:text-sm text-[#333333] leading-relaxed">
                        Evaluasi klinis menyeluruh dan protokol penanganan berbasis bukti untuk meredakan gejala dan menyembuhkan akar penyakit.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <motion.div
                        v-for="(cond, idx) in currentSpecialization.conditions"
                        :key="cond.title"
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -3 }"
                        :transition="{ duration: 0.22, ease: 'easeOut', delay: idx * 0.04 }"
                        class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-6 flex flex-col justify-between space-y-4 shadow-2xs hover:border-[#000000] transition-colors"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-[#333333]/70 uppercase tracking-wider">
                                    {{ cond.category }}
                                </span>
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                                        cond.severity === 'Gawat Darurat' || cond.severity === 'Kritis'
                                            ? 'bg-red-100 text-red-700 border border-red-200'
                                            : cond.severity === 'Sangat Tinggi' || cond.severity === 'Tinggi'
                                            ? 'bg-amber-100 text-amber-800 border border-amber-200'
                                            : 'bg-[#beedc0] text-[#000000]'
                                    ]"
                                >
                                    {{ cond.severity }}
                                </span>
                            </div>

                            <h3 class="font-['ivypresto-headline'] font-serif text-xl font-semibold text-[#000000] leading-snug">
                                {{ cond.title }}
                            </h3>

                            <p class="text-xs text-[#333333]/85 leading-relaxed">
                                {{ cond.desc }}
                            </p>

                            <!-- Symptoms Checklist -->
                            <div class="pt-3 border-t border-[#333333]/10 space-y-2">
                                <span class="text-[11px] font-bold text-[#000000] block">Gejala Klinis Umum:</span>
                                <ul class="space-y-1.5">
                                    <li
                                        v-for="symp in cond.symptoms"
                                        :key="symp"
                                        class="flex items-start gap-2 text-xs text-[#333333]"
                                    >
                                        <CheckCircle2 class="size-3.5 text-[#000000] shrink-0 mt-0.5" />
                                        <span>{{ symp }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="button"
                                @click="scrollToSection('tim-dokter')"
                                class="w-full min-h-[40px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-4 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer"
                            >
                                <span>Konsultasikan Kondisi Ini</span>
                                <ChevronRight class="size-3.5" />
                            </button>
                        </div>
                    </motion.div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 6. PROSEDUR & TINDAKAN MEDIS UNGGULAN
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="rounded-[12px] bg-[#fffff3] border border-[#333333]/15 p-6 sm:p-10 space-y-8 shadow-sm">
                    <div class="max-w-2xl space-y-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                            <Sparkles class="size-3.5" />
                            Fasilitas Diagnostik &amp; Intervensi
                        </span>
                        <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl font-semibold text-[#000000]">
                            Prosedur &amp; Tindakan Medis Unggulan
                        </h2>
                        <p class="text-xs sm:text-sm text-[#333333] leading-relaxed">
                            Penerapan teknologi diagnostik termutakhir dengan standar keselamatan pasien internasional untuk hasil penanganan optimal.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="proc in currentSpecialization.procedures"
                            :key="proc.title"
                            class="rounded-[10px] bg-white border border-[#333333]/15 p-6 space-y-4 flex flex-col justify-between hover:border-[#000000] transition-colors"
                        >
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#333333]/70">
                                        <Clock class="size-3" />
                                        {{ proc.duration }}
                                    </span>
                                    <span class="rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[10px] font-bold text-[#000000]">
                                        {{ proc.category }}
                                    </span>
                                </div>

                                <h3 class="font-['ivypresto-headline'] font-serif text-xl font-semibold text-[#000000] leading-snug">
                                    {{ proc.title }}
                                </h3>

                                <p class="text-xs text-[#333333]/85 leading-relaxed">
                                    {{ proc.desc }}
                                </p>

                                <!-- Benefits list -->
                                <div class="space-y-1.5 pt-2 border-t border-[#333333]/10">
                                    <span class="text-[11px] font-bold text-[#000000] block">Keunggulan Tindakan:</span>
                                    <ul class="space-y-1.5 text-xs text-[#333333]">
                                        <li
                                            v-for="ben in proc.benefits"
                                            :key="ben"
                                            class="flex items-start gap-2"
                                        >
                                            <CheckCircle2 class="size-3.5 text-[#000000] shrink-0 mt-0.5" />
                                            <span>{{ ben }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 7. TIM DOKTER SPESIALIS & JADWAL TERINTEGRASI (#tim-dokter)
                 ═══════════════════════════════════════════════════════ -->
            <section id="tim-dokter" class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                        <Stethoscope class="size-3.5" />
                        Tenaga Medis Ahli
                    </span>
                    <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl font-semibold text-[#000000]">
                        Tim Dokter Spesialis &amp; Jadwal Praktik
                    </h2>
                    <p class="text-xs sm:text-sm text-[#333333]">
                        Pilih jadwal praktik yang sesuai dengan waktu Anda dan lakukan reservasi tiket antrean online secara instan.
                    </p>
                </div>

                <!-- Filter & Search Bar untuk Jadwal -->
                <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-4 sm:p-5 shadow-sm space-y-3">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-md">
                            <input
                                v-model="doctorSearchQuery"
                                type="text"
                                placeholder="Cari nama dokter atau poliklinik..."
                                class="w-full min-h-[44px] pl-10 pr-9 rounded-[7px] border border-[#333333]/20 bg-white text-xs sm:text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none placeholder:text-[#333333]/50"
                            />
                            <Search class="size-4 text-[#333333]/60 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <button
                                v-if="doctorSearchQuery"
                                type="button"
                                @click="doctorSearchQuery = ''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#333333]/60 hover:text-[#000000]"
                            >
                                <X class="size-4" />
                            </button>
                        </div>

                        <!-- Filter Hari Praktik -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                            <button
                                v-for="day in availableDays"
                                :key="day"
                                type="button"
                                @click="selectedDayFilter = day"
                                :class="[
                                    'min-h-[36px] px-3 py-1 rounded-[40.5px] text-xs font-medium transition-colors whitespace-nowrap cursor-pointer',
                                    selectedDayFilter === day
                                        ? 'bg-[#000000] text-white font-semibold shadow-sm'
                                        : 'bg-white border border-[#333333]/15 text-[#333333] hover:bg-[#edede2]'
                                ]"
                            >
                                {{ day }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid Kartu Jadwal Dokter -->
                <div
                    v-if="filteredSchedules.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <motion.div
                        v-for="sch in filteredSchedules"
                        :key="sch.doctor_schedule_id || sch.id"
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -2 }"
                        :transition="{ duration: 0.2, ease: 'easeOut' }"
                        class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-6 flex flex-col justify-between space-y-5 shadow-2xs hover:border-[#000000] transition-colors"
                    >
                        <div class="space-y-4">
                            <!-- Doctor Header with Avatar -->
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#beedc0] border border-[#333333]/10 shrink-0 font-bold text-[#000000] text-base">
                                    {{ sch.doctor?.name?.replace(/^(dr\.|drg\.|Prof\.|dr\s)/i, '').trim().charAt(0) || 'D' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-['ivypresto-headline'] font-serif text-lg font-bold text-[#000000] leading-snug truncate">
                                        {{ sch.doctor?.name || 'Dokter Spesialis' }}
                                    </h3>
                                    <span class="text-xs text-[#333333] font-medium block truncate">
                                        {{ typeof sch.doctor?.specialization === 'string' ? sch.doctor.specialization : sch.doctor?.specialization?.name_specialization || currentSpecialization.category }}
                                    </span>
                                    <span v-if="sch.doctor?.sip_number" class="text-[10px] text-[#333333]/60 font-mono block">
                                        SIP: {{ sch.doctor.sip_number }}
                                    </span>
                                </div>
                            </div>

                            <!-- Schedule & Poli Details -->
                            <div class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#333333]/70 font-medium">Poliklinik:</span>
                                    <span class="font-semibold text-[#000000] truncate max-w-[160px]">
                                        {{ sch.poli?.name || sch.poli?.name_poli || 'Poli Terpadu' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[#333333]/70 font-medium">Ruangan:</span>
                                    <span class="font-semibold text-[#000000]">
                                        {{ sch.room?.name || sch.room?.name_room || 'Ruang Konsultasi' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-[#333333]/10">
                                    <span class="text-[#333333]/70 font-medium">Hari &amp; Jam Praktik:</span>
                                    <span class="font-bold text-[#000000] flex items-center gap-1">
                                        <Clock class="size-3 text-[#000000]" />
                                        {{ sch.day || sch.day_of_week }}, {{ sch.start_time?.substring(0, 5) }} – {{ sch.end_time?.substring(0, 5) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Action Button -->
                        <div>
                            <button
                                type="button"
                                @click="handleBookSchedule(sch)"
                                class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer whitespace-nowrap"
                            >
                                <Ticket class="size-3.5 text-[#beedc0]" />
                                <span>{{ currentUser ? 'Ambil Nomor Antrean' : 'Masuk untuk Ambil Antrean' }}</span>
                            </button>
                        </div>
                    </motion.div>
                </div>

                <!-- Empty State for Schedule Filter -->
                <div
                    v-else
                    class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-12 text-center space-y-4 max-w-md mx-auto"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#edede2] mx-auto text-[#000000]">
                        <Calendar class="size-6 text-[#333333]" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-['ivypresto-headline'] font-serif text-xl font-semibold text-[#000000]">
                            Tidak Ada Jadwal Praktik Sesuai Filter
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            Coba ubah filter hari atau bersihkan kata kunci pencarian dokter.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="selectedDayFilter = 'Semua'; doctorSearchQuery = '';"
                        class="min-h-[40px] inline-flex items-center justify-center rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors cursor-pointer"
                    >
                        Tampilkan Semua Jadwal
                    </button>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 8. FAQ & EDUKASI MEDIS (Interactive Accordion)
                 ═══════════════════════════════════════════════════════ -->
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                        <HelpCircle class="size-3.5" />
                        Panduan Pasien &amp; Edukasi
                    </span>
                    <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl font-semibold text-[#000000]">
                        Pertanyaan Sering Diajukan
                    </h2>
                    <p class="text-xs sm:text-sm text-[#333333]">
                        Informasi praktis seputar persiapan konsultasi, penjaminan BPJS/asuransi, dan prosedur pemeriksaan.
                    </p>
                </div>

                <div class="max-w-3xl mx-auto space-y-3">
                    <div
                        v-for="(faq, fIndex) in currentSpecialization.faqs"
                        :key="faq.q"
                        class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 overflow-hidden transition-all shadow-2xs"
                    >
                        <button
                            type="button"
                            @click="toggleFaq(fIndex)"
                            class="w-full p-5 text-left flex items-center justify-between gap-4 font-semibold text-sm text-[#000000] hover:bg-[#edede2]/40 transition-colors cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#beedc0] text-xs font-bold text-[#000000] shrink-0">
                                    Q
                                </span>
                                <span>{{ faq.q }}</span>
                            </span>
                            <ChevronDown
                                class="size-4 text-[#000000] shrink-0 transition-transform duration-200"
                                :class="{ 'rotate-180': openFaqIndex === fIndex }"
                            />
                        </button>

                        <div
                            v-show="openFaqIndex === fIndex"
                            class="px-5 pb-5 pt-1 text-xs sm:text-sm text-[#333333] leading-relaxed border-t border-[#333333]/10 bg-white/70 animate-in fade-in duration-150"
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
            <section class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-[12px] bg-[#000000] text-white p-8 sm:p-12 text-center space-y-6">
                    <div class="max-w-2xl mx-auto space-y-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                            Layanan Kesehatan Holistik &amp; Terpercaya
                        </span>
                        <h2 class="font-['ivypresto-headline'] font-serif text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight">
                            Mulai Konsultasi Bersama Dokter Spesialis Kami
                        </h2>
                        <p class="text-xs sm:text-sm text-white/80 max-w-lg mx-auto leading-relaxed">
                            Kesehatan Anda dan keluarga adalah prioritas utama kami. Hubungi layanan gawat darurat atau ambil antrean poliklinik secara daring.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <button
                            type="button"
                            @click="scrollToSection('tim-dokter')"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-white px-7 py-3 text-xs sm:text-sm font-semibold text-[#000000] hover:bg-[#edede2] transition-colors shadow-sm cursor-pointer"
                        >
                            <Stethoscope class="size-4 text-[#000000]" />
                            <span>Ambil Nomor Antrean Dokter</span>
                            <ArrowRight class="size-4" />
                        </button>

                        <a
                            href="tel:1500181"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] border border-white/30 bg-transparent px-7 py-3 text-xs sm:text-sm font-semibold text-white hover:bg-white/10 transition-colors"
                        >
                            <PhoneCall class="size-4 text-[#beedc0]" />
                            <span>IGD 24 Jam: 1-500-181</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- ═══════════════════════════════════════════════════════════
             10. FOOTER RUMAH SAKIT MULTI-KOLOM (Siloam Style)
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
                                <Link href="/specializations" class="hover:underline hover:text-[#000000] font-semibold text-[#000000]">
                                    Sub-Spesialisasi Medis
                                </Link>
                            </li>
                            <li>
                                <Link href="/patient-story" class="hover:underline hover:text-[#000000]">
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
