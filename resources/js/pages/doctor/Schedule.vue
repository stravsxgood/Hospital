<script setup lang="ts">
/**
 * @file Schedule.vue
 * @description Dedicated Doctor Practice Schedule Page for Hospital Population.
 * Features full filtering (Keyword, Polyclinic, Day), booking integration, and Siloam-inspired Evergreen design system.
 * Accessible for both guests (/schedule-guest) and authenticated patients.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Activity,
    Ambulance,
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Award,
    Bed,
    Building2,
    Calendar,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
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
    MapPin,
    Menu,
    MessageSquare,
    PhoneCall,
    Search,
    ShieldAlert,
    ShieldCheck,
    SlidersHorizontal,
    Sparkles,
    Stethoscope,
    Ticket,
    Tv,
    User,
    UserPlus,
    X,
} from '@lucide/vue';
import {
    columnFilteringFeature,
    createFilteredRowModel,
    createPaginatedRowModel,
    createSortedRowModel,
    globalFilteringFeature,
    rowPaginationFeature,
    rowSortingFeature,
    tableFeatures
    
    
} from '@tanstack/table-core';
import type {ColumnFiltersState, SortingState} from '@tanstack/table-core';
import { createTableHook } from '@tanstack/vue-table';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import BookingModal from '@/components/BookingModal.vue';
import TicketSuccessModal from '@/components/TicketSuccessModal.vue';
import type { TicketData } from '@/components/TicketSuccessModal.vue';
import type { DoctorSchedule } from '@/types';

defineOptions({ layout: undefined });

const props = defineProps<{
    schedules: DoctorSchedule[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isStaffUser = computed(() => {
    const role = currentUser.value?.role

    return ['doctor', 'nurse', 'admin'].includes(role || '') || Boolean(currentUser.value?.is_doctor)
});

// Search & Filter State
const searchQuery = ref('');
const selectedPoli = ref('Semua');
const selectedDay = ref('Semua');
const selectedSort = ref('default');

// State Mega Menu & Mobile Drawer
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

// State Modal Reservasi & Karcis
const isBookingModalOpen = ref(false);
const selectedSchedule = ref<DoctorSchedule | null>(null);
const isTicketModalOpen = ref(false);
const activeTicket = ref<TicketData | null>(null);

// Static Data for Navbar Mega Menu
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

/**
 * Quick jump to Poliklinik & Medical Team Profile page (/teams?poli=...)
 */
const jumpToPoliTeam = (poliName: string) => {
    isMegaMenuOpen.value = false;
    router.visit('/teams?poli=' + encodeURIComponent(poliName));
};

/**
 * Filter by polyclinic from Mega Menu dropdown
 */
const filterByPoli = (poliName: string) => {
    isMegaMenuOpen.value = false;
    selectedPoli.value = poliName;
};

watch(
    () => (page.props as any).flash?.success,
    (flashSuccess) => {
        if (flashSuccess?.ticket) {
            activeTicket.value = flashSuccess.ticket;
            setTimeout(() => {
                isTicketModalOpen.value = true;
            }, 100);
        }
    },
    { deep: true },
);

const daysList = [
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
 * List Poliklinik unik dari data jadwal
 */
const availablePolis = computed(() => {
    const list = new Set<string>();
    (props.schedules || []).forEach((s) => {
        const pName = s.poli?.name_poli || s.poli?.name;

        if (pName) {
list.add(pName);
}
    });

    return ['Semua', ...Array.from(list)];
});

/**
 * Parse URL Query Parameters on mount
 */
onMounted(() => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const q = urlParams.get('search');
        const p = urlParams.get('poli');
        const d = urlParams.get('day');

        if (q) {
searchQuery.value = q;
}

        if (p) {
            const matched = availablePolis.value.find(
                (item) =>
                    item.toLowerCase() === p.toLowerCase() ||
                    item.toLowerCase().includes(p.toLowerCase()) ||
                    p.toLowerCase().includes(item.toLowerCase()),
            );
            selectedPoli.value = matched || p;
        }

        if (d && daysList.includes(d)) {
            selectedDay.value = d;
        }
    }
});

/**
 * Helper to resolve doctor specialization name
 */
const getSpecializationName = (schedule: DoctorSchedule): string => {
    const spec = schedule.doctor?.specialization;

    if (typeof spec === 'object' && spec !== null) {
        return spec.name_specialization || spec.name || 'Dokter Spesialis';
    }

    return typeof spec === 'string' && spec ? spec : 'Dokter Spesialis';
};

/**
 * Helper to ensure doctor name does not have duplicated 'dr.' or 'drg.'
 */
const formatDoctorName = (name?: string | null): string => {
    if (!name) {
return 'Dokter Spesialis';
}

    const trimmed = name.trim();

    if (/^(dr\.|drg\.|dr\s|drg\s|prof\.|prof\s)/i.test(trimmed)) {
        return trimmed;
    }

    return `dr. ${trimmed}`;
};

// ==========================================
// TANSTACK TABLE IMPLEMENTATION
// ==========================================

const features = tableFeatures({
    globalFilteringFeature,
    columnFilteringFeature,
    rowSortingFeature,
    rowPaginationFeature,
    filteredRowModel: createFilteredRowModel(),
    sortedRowModel: createSortedRowModel(),
    paginatedRowModel: createPaginatedRowModel(),
});

const { useAppTable, createAppColumnHelper } = createTableHook({
    features,
});

/**
 * Custom global filter function matching doctor name, spec, poli, or room
 */
const globalFilterFn = (row: any, columnId: string, filterValue: any): boolean => {
    if (!filterValue) {
return true;
}

    const search = String(filterValue).toLowerCase().trim();
    const docName = String(row.original?.doctor?.name || '').toLowerCase();
    const specName = String(getSpecializationName(row.original)).toLowerCase();
    const poliName = String(row.original?.poli?.name_poli || row.original?.poli?.name || '').toLowerCase();
    const roomName = String(row.original?.room?.name_room || row.original?.room?.name || '').toLowerCase();

    return (
        docName.includes(search) ||
        specName.includes(search) ||
        poliName.includes(search) ||
        roomName.includes(search)
    );
};

/**
 * Custom column filter for polyclinic
 */
const poliFilterFn = (row: any, columnId: string, filterValue: any): boolean => {
    if (!filterValue || filterValue === 'Semua') {
return true;
}

    const poliName = String(row.original?.poli?.name_poli || row.original?.poli?.name || '').toLowerCase();

    return poliName === String(filterValue).toLowerCase();
};

/**
 * Custom column filter for day
 */
const dayFilterFn = (row: any, columnId: string, filterValue: any): boolean => {
    if (!filterValue || filterValue === 'Semua') {
return true;
}

    const itemDay = String(row.original?.day || row.original?.day_of_week || '').toLowerCase();

    return itemDay === String(filterValue).toLowerCase();
};

const columnHelper = createAppColumnHelper<DoctorSchedule>();

const columns = columnHelper.columns([
    columnHelper.accessor((row) => row.doctor?.name || '', {
        id: 'doctorName',
        header: 'Nama Dokter',
    }),
    columnHelper.accessor((row) => getSpecializationName(row), {
        id: 'specialization',
        header: 'Spesialisasi',
    }),
    columnHelper.accessor((row) => row.poli?.name_poli || row.poli?.name || '', {
        id: 'poli',
        header: 'Poliklinik',
        filterFn: poliFilterFn,
    }),
    columnHelper.accessor((row) => row.day || row.day_of_week || '', {
        id: 'day',
        header: 'Hari',
        filterFn: dayFilterFn,
    }),
    columnHelper.accessor((row) => `${row.start_time || ''} - ${row.end_time || ''}`, {
        id: 'time',
        header: 'Jam Praktik',
    }),
    columnHelper.accessor((row) => row.room?.name_room || row.room?.name || '', {
        id: 'room',
        header: 'Ruang',
    }),
]);

const sorting = ref<SortingState>([]);
const pagination = ref({
    pageIndex: 0,
    pageSize: 12,
});

const columnFilters = computed<ColumnFiltersState>(() => {
    const filters: ColumnFiltersState = [];

    if (selectedPoli.value && selectedPoli.value !== 'Semua') {
        filters.push({ id: 'poli', value: selectedPoli.value });
    }

    if (selectedDay.value && selectedDay.value !== 'Semua') {
        filters.push({ id: 'day', value: selectedDay.value });
    }

    return filters;
});

const table = useAppTable<DoctorSchedule>({
    get data() {
        return props.schedules || [];
    },
    columns,
    state: {
        get globalFilter() {
            return searchQuery.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get sorting() {
            return sorting.value;
        },
        get pagination() {
            return pagination.value;
        },
    },
    onGlobalFilterChange: (updaterOrValue) => {
        searchQuery.value =
            typeof updaterOrValue === 'function'
                ? updaterOrValue(searchQuery.value)
                : updaterOrValue;
        pagination.value.pageIndex = 0;
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value =
            typeof updaterOrValue === 'function'
                ? updaterOrValue(sorting.value)
                : updaterOrValue;
    },
    onPaginationChange: (updaterOrValue) => {
        pagination.value =
            typeof updaterOrValue === 'function'
                ? updaterOrValue(pagination.value)
                : updaterOrValue;
    },
    globalFilterFn,
});

// Watch sorting selection change
watch(selectedSort, (newVal) => {
    if (newVal === 'doctorNameAsc') {
        sorting.value = [{ id: 'doctorName', desc: false }];
    } else if (newVal === 'doctorNameDesc') {
        sorting.value = [{ id: 'doctorName', desc: true }];
    } else if (newVal === 'poliAsc') {
        sorting.value = [{ id: 'poli', desc: false }];
    } else if (newVal === 'timeAsc') {
        sorting.value = [{ id: 'time', desc: false }];
    } else {
        sorting.value = [];
    }

    pagination.value.pageIndex = 0;
});

// Watch filter changes to reset page index
watch([selectedPoli, selectedDay, searchQuery], () => {
    pagination.value.pageIndex = 0;
});

/**
 * Smart windowed page numbers with ellipsis (e.g. 1, 2, ..., 5, 6, 7, ..., 20)
 * Prevents horizontal layout overflow when there are many pages.
 */
const visiblePages = computed<(number | string)[]>(() => {
    const total = table.getPageCount();
    const current = pagination.value.pageIndex + 1;

    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const pages: (number | string)[] = [];

    // Always include first page
    pages.push(1);

    if (current > 3) {
        pages.push('...');
    }

    // Determine window start and end surrounding current page
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);

    for (let i = start; i <= end; i++) {
        if (!pages.includes(i)) {
            pages.push(i);
        }
    }

    if (current < total - 2) {
        if (!pages.includes('...')) {
            pages.push('...');
        }
    }

    // Always include last page
    if (!pages.includes(total)) {
        pages.push(total);
    }

    return pages;
});

/**
 * Reset all active filters
 */
const resetFilters = () => {
    searchQuery.value = '';
    selectedPoli.value = 'Semua';
    selectedDay.value = 'Semua';
    selectedSort.value = 'default';
    sorting.value = [];
    pagination.value.pageIndex = 0;
};

/**
 * Handles appointment reservation action
 */
const handleBookClick = (schedule: DoctorSchedule) => {
    if (!currentUser.value) {
        router.visit('/login');

        return;
    }

    selectedSchedule.value = schedule;
    isBookingModalOpen.value = true;
};

/**
 * Handler after appointment successfully created
 */
const handleBookingSuccess = (ticket: TicketData | null) => {
    if (ticket) {
        activeTicket.value = ticket;
        isTicketModalOpen.value = true;
    }
};
</script>

<template>
    <Head title="Jadwal Praktik Dokter Spesialis - Hospital Population" />

    <div
        class="min-h-screen bg-[#edede2] text-[#000000] font-['Rubik'] antialiased selection:bg-[#beedc0] selection:text-[#000000]"
    >
        <!-- 1. Top Emergency Bar -->
        <div
            class="bg-[#000000] text-white text-xs py-2 px-4 sm:px-6 lg:px-8 border-b border-[#333333]/40"
        >
            <div
                class="max-w-[1200px] mx-auto flex items-center justify-between gap-4 flex-nowrap overflow-x-auto scrollbar-none"
            >
                <div
                    class="flex items-center gap-4 sm:gap-6 text-white/90 shrink-0 whitespace-nowrap"
                >
                    <a
                        href="tel:1500181"
                        class="inline-flex items-center gap-1.5 font-bold hover:text-[#beedc0] transition-colors whitespace-nowrap"
                    >
                        <span
                            class="flex h-2 w-2 rounded-full bg-red-500 animate-ping shrink-0"
                        />
                        <PhoneCall class="size-3.5 text-[#beedc0] shrink-0" />
                        <span class="whitespace-nowrap"
                            >IGD &amp; Ambulans 24 Jam:
                            <strong>1-500-181</strong></span
                        >
                    </a>
                    <span class="hidden sm:inline text-[#333333]">|</span>
                    <a
                        href="https://wa.me/6281100000000"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 hover:text-[#beedc0] transition-colors whitespace-nowrap"
                    >
                        <MessageSquare
                            class="size-3.5 text-[#beedc0] shrink-0"
                        />
                        <span class="whitespace-nowrap"
                            >WhatsApp Care: 0811-0000-0000</span
                        >
                    </a>
                </div>

                <div
                    class="flex items-center gap-4 text-[11px] text-white/80 shrink-0 whitespace-nowrap"
                >
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

        <!-- 2. Sticky Header Navbar -->
        <motion.header
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="sticky top-0 z-40 bg-[#edede2]/95 backdrop-blur-md border-b border-[#333333]/15 transition-all"
        >
            <div
                class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4"
            >
                <!-- Brand & Back Button -->
                <div class="flex items-center gap-4 shrink-0">
                    <Link
                        href="/"
                        class="flex items-center gap-3 group shrink-0"
                    >
                        <motion.div
                            :whileHover="{ scale: 1.05 }"
                            :whileTap="{ scale: 0.95 }"
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0] border border-[#333333]/10 shrink-0"
                        >
                            <AppLogoIcon
                                class="size-7 fill-current text-[#000000]"
                            />
                        </motion.div>
                        <div class="flex flex-col justify-center">
                            <span
                                class="font-['ivypresto-headline'] font-serif text-xl sm:text-2xl font-bold tracking-tight text-[#000000] leading-tight whitespace-nowrap"
                            >
                                Hospital Population
                            </span>
                            <span
                                class="text-[10px] sm:text-[11px] text-[#333333] tracking-wider uppercase font-medium whitespace-nowrap"
                            >
                                Pelayanan Kesehatan Terpadu
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden lg:flex items-center gap-1.5 xl:gap-2 text-sm font-medium text-[#000000] whitespace-nowrap">
                    <!-- Button Cari Dokter (Active Page) -->
                    <Link
                        href="/schedule-guest"
                        class="min-h-[40px] px-3.5 sm:px-4 py-2 rounded-[40.5px] text-xs sm:text-sm font-semibold border border-[#000000] bg-[#000000] text-white shadow-sm transition-all duration-200 inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <span class="size-2 rounded-full bg-[#beedc0] animate-pulse shrink-0" />
                        <Stethoscope class="size-4 text-[#beedc0] shrink-0" />
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
                    <template v-if="currentUser">
                        <Link
                            :href="isStaffUser ? '/staff' : '/patient/dashboard'"
                            class="min-h-[44px] inline-flex items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-5 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm whitespace-nowrap"
                        >
                            <User class="size-3.5 text-[#beedc0] shrink-0" />
                            <span class="whitespace-nowrap">{{ isStaffUser ? 'Dashboard Staf' : 'Portal Pasien' }}</span>
                        </Link>
                    </template>
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
                            <UserPlus
                                class="size-3.5 text-[#beedc0] shrink-0"
                            />
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
                                class="min-h-[44px] flex items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#000000] bg-[#edede2] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-[#000000] text-[#beedc0] flex items-center justify-center">
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

        <!-- Main Content -->
        <main
            class="mx-auto max-w-[1200px] space-y-10 px-4 py-8 sm:px-6 md:py-12 lg:px-8 pb-24"
        >
            <!-- Hero / Editorial Headline Block -->
            <div class="mx-auto max-w-3xl space-y-4 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-[46px] bg-[#fffff3] border border-[#333333]/20 px-4 py-1 text-xs font-semibold text-[#000000] whitespace-nowrap"
                >
                    <Stethoscope class="size-3.5 text-[#000000] shrink-0" />
                    <span class="whitespace-nowrap"
                        >Pusat Layanan Dokter Spesialis</span
                    >
                </span>
                <h1
                    class="font-['ivypresto-headline'] font-serif text-4xl sm:text-5xl md:text-[54px] font-semibold text-[#000000] leading-tight"
                >
                    Jadwal Praktik Dokter Terpadu
                </h1>
                <p
                    class="text-base sm:text-lg text-[#333333] leading-relaxed max-w-2xl mx-auto"
                >
                    Temukan jadwal dokter spesialis terbaik kami dan lakukan
                    reservasi nomor antrean rawat jalan secara mandiri, cepat,
                    dan transparan.
                </p>
            </div>

            <!-- Search & Filters Container Card (Powered by TanStack Table) -->
            <motion.div
                :initial="{ opacity: 0, y: 15 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut' }"
                class="rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5 sm:p-7 shadow-sm space-y-5"
            >
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Search Input: TanStack Global Search -->
                    <div class="md:col-span-5 relative">
                        <label
                            for="sched-search"
                            class="text-[11px] font-semibold text-[#333333] uppercase tracking-wider block mb-1"
                        >
                            Dokter / Keluhan / Spesialisasi
                        </label>
                        <div class="relative">
                            <input
                                id="sched-search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama dokter, spesialisasi, poli, ruang..."
                                class="min-h-[44px] w-full pl-10 pr-9 rounded-[7px] border border-[#333333]/20 bg-white text-xs sm:text-sm text-[#000000] placeholder:text-[#333333]/50 focus:ring-2 focus:ring-[#000000] focus:outline-none"
                            />
                            <Search
                                class="size-4 text-[#333333]/60 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                @click="searchQuery = ''"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 rounded-full text-[#333333]/50 hover:text-[#000000] hover:bg-[#edede2] transition-colors"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Dropdown Poliklinik: TanStack Column Filter -->
                    <div class="md:col-span-3">
                        <label
                            for="sched-poli"
                            class="text-[11px] font-semibold text-[#333333] uppercase tracking-wider block mb-1"
                        >
                            Pilihan Poliklinik
                        </label>
                        <div class="relative">
                            <select
                                id="sched-poli"
                                v-model="selectedPoli"
                                class="min-h-[44px] w-full px-3.5 pr-8 rounded-[7px] border border-[#333333]/20 bg-white text-xs sm:text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none appearance-none cursor-pointer"
                            >
                                <option
                                    v-for="poli in availablePolis"
                                    :key="poli"
                                    :value="poli"
                                >
                                    {{
                                        poli === 'Semua'
                                            ? 'Semua Poliklinik'
                                            : poli
                                    }}
                                </option>
                            </select>
                            <ChevronDown
                                class="size-4 text-[#333333]/60 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            />
                        </div>
                    </div>

                    <!-- Dropdown Urutkan: TanStack Sorting -->
                    <div class="md:col-span-2">
                        <label
                            for="sched-sort"
                            class="text-[11px] font-semibold text-[#333333] uppercase tracking-wider block mb-1"
                        >
                            Urutkan
                        </label>
                        <div class="relative">
                            <select
                                id="sched-sort"
                                v-model="selectedSort"
                                class="min-h-[44px] w-full px-3.5 pr-8 rounded-[7px] border border-[#333333]/20 bg-white text-xs sm:text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none appearance-none cursor-pointer"
                            >
                                <option value="default">Urutan Standar</option>
                                <option value="doctorNameAsc">Dokter (A - Z)</option>
                                <option value="doctorNameDesc">Dokter (Z - A)</option>
                                <option value="poliAsc">Poliklinik (A - Z)</option>
                                <option value="timeAsc">Jam Praktik Pagi</option>
                            </select>
                            <ChevronDown
                                class="size-4 text-[#333333]/60 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            />
                        </div>
                    </div>

                    <!-- Reset Filter Button -->
                    <div class="md:col-span-2 flex items-end">
                        <button
                            type="button"
                            @click="resetFilters"
                            class="min-h-[44px] w-full inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-white px-4 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors whitespace-nowrap cursor-pointer"
                        >
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>

                <!-- Day Filter Buttons & Stats -->
                <div
                    class="pt-3 border-t border-[#333333]/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
                >
                    <div
                        class="flex items-center gap-1.5 overflow-x-auto w-full pb-1 scrollbar-none"
                    >
                        <span
                            class="text-[11px] font-semibold text-[#333333] whitespace-nowrap mr-1"
                            >Hari Praktik:</span
                        >
                        <motion.button
                            v-for="day in daysList"
                            :key="day"
                            type="button"
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            @click="selectedDay = day"
                            :class="[
                                'min-h-[36px] px-3.5 py-1.5 rounded-[46px] text-xs font-medium whitespace-nowrap transition-colors cursor-pointer',
                                selectedDay === day
                                    ? 'bg-[#000000] text-white font-semibold shadow-sm'
                                    : 'bg-white border border-[#333333]/15 text-[#333333] hover:bg-[#edede2]',
                            ]"
                        >
                            {{ day }}
                        </motion.button>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-[#333333]/80 whitespace-nowrap shrink-0">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#edede2] px-2.5 py-1 text-[11px] font-medium text-[#000000]"
                        >
                            <span>Menampilkan</span>
                            <strong class="font-bold text-[#000000]">{{ table.getRowModel().rows.length }}</strong>
                            <span>dari</span>
                            <strong class="font-bold text-[#000000]">{{ table.getFilteredRowModel().rows.length }}</strong>
                            <span>jadwal</span>
                        </span>
                    </div>
                </div>
            </motion.div>

            <!-- Schedules Grid (Rendered from TanStack Table Row Model) -->
            <div
                v-if="table.getRowModel().rows.length > 0"
                class="space-y-8"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <motion.div
                        v-for="row in table.getRowModel().rows"
                        :key="row.original.doctor_schedule_id ?? row.original.id"
                        :initial="{ opacity: 0, y: 15 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :whileHover="{ scale: 1.015, y: -2 }"
                        :transition="{ duration: 0.2, ease: 'easeOut' }"
                        class="flex flex-col justify-between rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5 sm:p-6 shadow-sm hover:border-[#000000] transition-colors"
                    >
                        <div class="space-y-4">
                            <!-- Badges: Poli & Day -->
                            <div
                                class="flex items-center justify-between gap-2 whitespace-nowrap"
                            >
                                <span
                                    class="inline-flex items-center gap-1 rounded-[46px] bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000] whitespace-nowrap"
                                >
                                    <HeartPulse
                                        class="size-3 text-[#000000] shrink-0"
                                    />
                                    <span class="whitespace-nowrap">{{
                                        row.original.poli?.name_poli ||
                                        row.original.poli?.name ||
                                        'Poliklinik'
                                    }}</span>
                                </span>
                                <span
                                    class="rounded-[46px] border border-[#333333]/15 bg-white px-2.5 py-0.5 text-xs font-medium text-[#333333] whitespace-nowrap"
                                >
                                    {{ row.original.day || row.original.day_of_week }}
                                </span>
                            </div>

                            <!-- Doctor Info -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#beedc0] text-[#000000] font-bold text-sm border border-[#333333]/10"
                                >
                                    {{
                                        row.original.doctor?.name
                                            ? (row.original.doctor.name.replace(/^(dr\.|drg\.|dr|drg|prof\.|prof)\s*/i, '').trim().charAt(0).toUpperCase() || 'D')
                                            : 'D'
                                    }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="font-['ivypresto-headline'] font-serif text-lg sm:text-xl font-semibold text-[#000000] leading-snug truncate"
                                    >
                                        {{ formatDoctorName(row.original.doctor?.name) }}
                                    </h3>
                                    <span
                                        class="text-xs text-[#333333]/80 block mt-0.5 truncate whitespace-nowrap"
                                    >
                                        {{ getSpecializationName(row.original) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Practice Time & Room Details -->
                            <div
                                class="grid grid-cols-2 gap-2 text-xs pt-3 border-t border-[#333333]/10"
                            >
                                <div
                                    class="bg-white p-2.5 rounded-[6px] border border-[#333333]/10"
                                >
                                    <span
                                        class="text-[10px] text-[#333333]/70 font-medium flex items-center gap-1"
                                    >
                                        <Clock class="size-3 shrink-0" />
                                        <span>Jam Praktik</span>
                                    </span>
                                    <span
                                        class="font-bold text-[#000000] font-mono mt-0.5 block whitespace-nowrap"
                                    >
                                        {{
                                            row.original.start_time
                                                ? row.original.start_time.substring(0, 5)
                                                : '-'
                                        }}
                                        –
                                        {{
                                            row.original.end_time
                                                ? row.original.end_time.substring(0, 5)
                                                : '-'
                                        }}
                                        WIB
                                    </span>
                                </div>
                                <div
                                    class="bg-white p-2.5 rounded-[6px] border border-[#333333]/10"
                                >
                                    <span
                                        class="text-[10px] text-[#333333]/70 font-medium flex items-center gap-1"
                                    >
                                        <MapPin class="size-3 shrink-0" />
                                        <span>Ruang Poli</span>
                                    </span>
                                    <span
                                        class="font-bold text-[#000000] truncate mt-0.5 block whitespace-nowrap"
                                    >
                                        {{
                                            row.original.room?.name_room ||
                                            row.original.room?.name ||
                                            'Ruang Poli'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button: Direct Booking / Login -->
                        <div class="mt-5 pt-2">
                            <motion.button
                                type="button"
                                :whileHover="{ scale: 1.02 }"
                                :whileTap="{ scale: 0.98 }"
                                @click="handleBookClick(row.original)"
                                class="min-h-[44px] w-full inline-flex items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors whitespace-nowrap shadow-sm cursor-pointer"
                            >
                                <Ticket class="size-3.5 text-[#beedc0] shrink-0" />
                                <span class="whitespace-nowrap">{{
                                    currentUser
                                        ? 'Ambil Nomor Antrean'
                                        : 'Masuk untuk Ambil Antrean'
                                }}</span>
                            </motion.button>
                        </div>
                    </motion.div>
                </div>

                <!-- TanStack Table Pagination Bar -->
                <div
                    v-if="table.getPageCount() > 1"
                    class="flex flex-col lg:flex-row items-center justify-between gap-4 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-4 sm:p-5 shadow-sm overflow-hidden"
                >
                    <!-- Left: Info -->
                    <div class="text-xs text-[#333333] font-medium text-center sm:text-left">
                        Halaman <strong class="text-[#000000]">{{ pagination.pageIndex + 1 }}</strong> dari <strong class="text-[#000000]">{{ table.getPageCount() }}</strong>
                        <span class="hidden sm:inline text-[#333333]/60 ml-1">({{ table.getFilteredRowModel().rows.length }} total jadwal ditemukan)</span>
                    </div>

                    <!-- Center: Navigation Page Controls -->
                    <div class="flex items-center flex-wrap justify-center gap-1.5 max-w-full">
                        <!-- First Page Shortcut -->
                        <button
                            type="button"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.setPageIndex(0)"
                            title="Halaman Pertama"
                            class="hidden sm:inline-flex min-h-[38px] min-w-[38px] px-2 rounded-[40.5px] border border-[#333333]/20 bg-white text-xs font-semibold text-[#000000] hover:bg-[#edede2] disabled:opacity-30 disabled:pointer-events-none transition-colors items-center justify-center cursor-pointer"
                        >
                            <ChevronsLeft class="size-4" />
                        </button>

                        <!-- Previous Page -->
                        <button
                            type="button"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                            class="min-h-[38px] px-3 rounded-[40.5px] border border-[#333333]/20 bg-white text-xs font-medium text-[#000000] hover:bg-[#edede2] disabled:opacity-30 disabled:pointer-events-none transition-colors inline-flex items-center gap-1 cursor-pointer"
                        >
                            <ChevronLeft class="size-4" />
                            <span class="hidden sm:inline">Sebelumnya</span>
                        </button>

                        <!-- Dynamic Numbered Page Buttons with Ellipsis -->
                        <div class="flex items-center gap-1 flex-wrap justify-center">
                            <template v-for="(item, idx) in visiblePages" :key="idx">
                                <span
                                    v-if="item === '...'"
                                    class="min-w-[26px] text-center text-xs font-bold text-[#333333]/50 select-none"
                                >
                                    &hellip;
                                </span>
                                <button
                                    v-else
                                    type="button"
                                    @click="table.setPageIndex(Number(item) - 1)"
                                    :class="[
                                        'min-h-[38px] min-w-[38px] px-2.5 rounded-[40.5px] text-xs font-semibold transition-colors cursor-pointer',
                                        pagination.pageIndex === Number(item) - 1
                                            ? 'bg-[#000000] text-white shadow-sm font-bold'
                                            : 'bg-white border border-[#333333]/15 text-[#333333] hover:bg-[#edede2]',
                                    ]"
                                >
                                    {{ item }}
                                </button>
                            </template>
                        </div>

                        <!-- Next Page -->
                        <button
                            type="button"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                            class="min-h-[38px] px-3 rounded-[40.5px] border border-[#333333]/20 bg-white text-xs font-medium text-[#000000] hover:bg-[#edede2] disabled:opacity-30 disabled:pointer-events-none transition-colors inline-flex items-center gap-1 cursor-pointer"
                        >
                            <span class="hidden sm:inline">Berikutnya</span>
                            <ChevronRight class="size-4" />
                        </button>

                        <!-- Last Page Shortcut -->
                        <button
                            type="button"
                            :disabled="!table.getCanNextPage()"
                            @click="table.setPageIndex(table.getPageCount() - 1)"
                            title="Halaman Terakhir"
                            class="hidden sm:inline-flex min-h-[38px] min-w-[38px] px-2 rounded-[40.5px] border border-[#333333]/20 bg-white text-xs font-semibold text-[#000000] hover:bg-[#edede2] disabled:opacity-30 disabled:pointer-events-none transition-colors items-center justify-center cursor-pointer"
                        >
                            <ChevronsRight class="size-4" />
                        </button>
                    </div>

                    <!-- Right: Page Size Selector -->
                    <div class="flex items-center gap-2 text-xs text-[#333333]">
                        <span class="whitespace-nowrap">Tampilkan:</span>
                        <select
                            :value="pagination.pageSize"
                            @change="table.setPageSize(Number(($event.target as HTMLSelectElement).value))"
                            class="min-h-[36px] px-2.5 py-1 rounded-[6px] border border-[#333333]/20 bg-white text-xs text-[#000000] font-medium focus:ring-2 focus:ring-[#000000] focus:outline-none cursor-pointer"
                        >
                            <option :value="12">12</option>
                            <option :value="24">24</option>
                            <option :value="48">48</option>
                            <option :value="96">96</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="text-center py-16 px-6 rounded-[10px] border border-dashed border-[#333333]/20 bg-[#fffff3] max-w-lg mx-auto space-y-4"
            >
                <div
                    class="w-14 h-14 rounded-full bg-[#beedc0] flex items-center justify-center mx-auto"
                >
                    <Calendar class="size-7 text-[#000000]" />
                </div>
                <h3
                    class="font-['ivypresto-headline'] font-serif text-2xl font-semibold text-[#000000]"
                >
                    Tidak Ada Jadwal Dokter Ditemukan
                </h3>
                <p class="text-xs sm:text-sm text-[#333333] leading-relaxed">
                    Tidak ditemukan jadwal praktik yang cocok dengan filter atau
                    kata kunci pencarian Anda.
                </p>
                <button
                    type="button"
                    @click="resetFilters"
                    class="min-h-[44px] inline-flex items-center justify-center rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold text-white hover:bg-[#333333] transition-colors whitespace-nowrap cursor-pointer"
                >
                    Reset Filter Pencarian
                </button>
            </div>
        </main>

        <!-- 1. Modal Input Reservasi Pasien -->
        <BookingModal
            v-model:open="isBookingModalOpen"
            :schedule="selectedSchedule"
            @success="handleBookingSuccess"
        />

        <!-- 2. Modal Karcis Tiket Antrean (Notifikasi Sukses Bertema) -->
        <TicketSuccessModal
            v-model:open="isTicketModalOpen"
            :ticket="activeTicket"
        />
    </div>
</template>
