<script setup lang="ts">
/**
 * @file AdminLayout.vue : Super Admin Responsive Master Layout
 * @description Layout terdedikasi Super Admin dengan Mobile Off-Canvas Drawer, Desktop Sidebar,
 *              Top Header responsif, Breadcrumbs, Flash Messages, dan Touch Targets >= 44px.
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Breakpoints: Mobile (< 640px), Tablet (640px - 1024px), Desktop (>= 1024px).
 *  - Touch target minimal 44x44px untuk seluruh kontrol interaktif.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    Bell,
    Building2,
    Calendar,
    CheckCircle2,
    ChevronRight,
    DoorClosed,
    FileText,
    GraduationCap,
    Home,
    KeyRound,
    LayoutGrid,
    LogOut,
    Menu,
    Pill,
    Receipt,
    Search,
    Settings,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    TrendingUp,
    UserCheck,
    Users,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

export interface BreadcrumbItem {
    title: string;
    href?: string;
}

const props = withDefaults(
    defineProps<{
        title?: string;
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        title: 'Super Admin - Hospital Population',
        breadcrumbs: () => [],
    },
);

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const flash = computed(() => (page.props.flash as any) || {});

// ═══════════════════════════════════════════════════════════════
// Mobile Drawer State & Auto-close Listeners
// ═══════════════════════════════════════════════════════════════
const isMounted = ref(false);
const isMobileDrawerOpen = ref(false);

const openMobileDrawer = () => {
    isMobileDrawerOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeMobileDrawer = () => {
    isMobileDrawerOpen.value = false;
    document.body.style.overflow = '';
};

// Auto-close on Escape key
const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileDrawerOpen.value) {
        closeMobileDrawer();
    }
};

// Auto-close on Inertia navigation
const removeNavigateListener = router.on('navigate', () => {
    closeMobileDrawer();
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

// ═══════════════════════════════════════════════════════════════
// Navigation Items & Active Route Helper
// ═══════════════════════════════════════════════════════════════
interface NavItem {
    label: string;
    href: string;
    icon: any;
    activePattern: string;
    badge?: string;
}

const adminNavGroups = computed(() => [
    {
        title: 'Tata Kelola Eksekutif',
        items: [
            {
                label: 'Dashboard Eksekutif',
                href: '/admin/dashboard',
                icon: TrendingUp,
                activePattern: '/admin/dashboard',
            },
        ],
    },
    {
        title: 'Manajemen Pengguna & SDM',
        items: [
            {
                label: 'Direktori & Provisioning',
                href: '/admin/users',
                icon: Users,
                activePattern: '/admin/users',
            },
        ],
    },
    {
        title: 'Fasilitas & Penjadwalan',
        items: [
            {
                label: 'Poliklinik & Jadwal DPJP',
                href: '/admin/polis',
                icon: Building2,
                activePattern: '/admin/polis',
            },
        ],
    },
    {
        title: 'Kepatuhan & Keamanan',
        items: [
            {
                label: 'Audit Akses Global (UU PDP)',
                href: '/admin/audit-logs',
                icon: ShieldCheck,
                activePattern: '/admin/audit-logs',
            },
        ],
    },
    {
        title: 'Konfigurasi Sistem',
        items: [
            {
                label: 'Pengaturan Sistem',
                href: '/admin/settings',
                icon: Settings,
                activePattern: '/admin/settings',
            },
        ],
    },
]);

const isRouteActive = (pattern: string): boolean => {
    const currentPath = page.url.split('?')[0];

    if (pattern === '/admin/dashboard') {
        return currentPath === '/admin/dashboard' || currentPath === '/admin';
    }

    return currentPath.startsWith(pattern);
};

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div
        class="min-h-screen bg-[#edede2] font-['Rubik'] text-[#000000] antialiased selection:bg-[#beedc0] selection:text-[#065f46]"
    >
        <Head :title="title" />

        <!-- ═══════════════════════════════════════════════════════════════
             1. Mobile & Tablet Top Navigation Header (< 1024px)
             ═══════════════════════════════════════════════════════════════ -->
        <header
            class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-[#000000]/10 bg-[#fffff3]/95 px-4 backdrop-blur-md lg:hidden"
        >
            <div class="flex items-center gap-3">
                <!-- Hamburger Button (Min touch target 44px) -->
                <button
                    type="button"
                    @click="openMobileDrawer"
                    aria-label="Buka Menu Navigasi"
                    class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl border border-[#000000]/10 bg-[#fffff3] text-[#000000] transition-all hover:bg-[#edede2] active:scale-95"
                >
                    <Menu class="size-5 text-[#065f46]" />
                </button>

                <!-- Brand Logo & Title -->
                <Link href="/admin/dashboard" class="flex items-center gap-2.5">
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-[#beedc0] text-[#000000] shadow-xs"
                    >
                        <AppLogoIcon class="size-5" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="font-['ivypresto-headline'] text-sm leading-none font-bold tracking-tight text-[#000000]"
                        >
                            Hospital Population
                        </span>
                        <span
                            class="mt-0.5 text-[10px] font-bold tracking-wider text-[#333333] uppercase"
                        >
                            Super Admin
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Header Right: User Role Badge -->
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3 py-1 text-[11px] font-bold text-[#000000]"
                >
                    <ShieldCheck class="size-3 text-[#000000]" />
                    <span class="max-w-[100px] truncate sm:max-w-none">{{
                        authUser?.name || 'Super Admin'
                    }}</span>
                </span>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════════════
             2. Mobile Slide-in Drawer (< 1024px)
             ═══════════════════════════════════════════════════════════════ -->
        <Teleport to="body" v-if="isMounted">
            <!-- Backdrop -->
            <Transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isMobileDrawerOpen"
                    @click="closeMobileDrawer"
                    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
                    aria-hidden="true"
                ></div>
            </Transition>

            <!-- Drawer Container -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <aside
                    v-if="isMobileDrawerOpen"
                    class="fixed inset-y-0 left-0 z-50 flex w-full max-w-[280px] flex-col justify-between border-r border-[#000000]/10 bg-[#fffff3] p-4 shadow-2xl lg:hidden"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu Super Admin"
                >
                    <!-- Drawer Header -->
                    <div
                        class="flex items-center justify-between border-b border-[#000000]/10 pb-4"
                    >
                        <Link
                            href="/admin/dashboard"
                            @click="closeMobileDrawer"
                            class="flex items-center gap-2.5"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-[#000000] shadow-xs"
                            >
                                <AppLogoIcon class="size-5" />
                            </div>
                            <div>
                                <h2
                                    class="font-['ivypresto-headline'] text-base font-bold text-[#000000]"
                                >
                                    Hospital SIMRS
                                </h2>
                                <p
                                    class="text-[11px] font-bold text-[#333333] uppercase"
                                >
                                    Master Governance
                                </p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            @click="closeMobileDrawer"
                            aria-label="Tutup Menu"
                            class="flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-xl text-[#000000]/60 transition-colors hover:bg-[#edede2] hover:text-[#000000]"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Drawer Nav Items -->
                    <nav
                        aria-label="Navigasi Menu Mobile"
                        class="my-4 flex-1 space-y-5 overflow-y-auto pr-1"
                    >
                        <div
                            v-for="group in adminNavGroups"
                            :key="group.title"
                            class="space-y-1.5"
                        >
                            <div
                                class="px-3 text-[10px] font-extrabold tracking-wider text-[#333333] uppercase"
                            >
                                {{ group.title }}
                            </div>
                            <div class="space-y-1">
                                <Link
                                    v-for="item in group.items"
                                    :key="item.href"
                                    :href="item.href"
                                    @click="closeMobileDrawer"
                                    class="flex min-h-[44px] items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all"
                                    :class="
                                        isRouteActive(item.activePattern)
                                            ? 'bg-[#000000] text-[#ffffff] shadow-xs'
                                            : 'text-[#000000]/80 hover:bg-[#edede2] hover:text-[#000000]'
                                    "
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-4 shrink-0"
                                        :class="
                                            isRouteActive(item.activePattern)
                                                ? 'text-[#beedc0]'
                                                : 'text-[#000000]'
                                        "
                                    />
                                    <span>{{ item.label }}</span>
                                </Link>
                            </div>
                        </div>
                    </nav>

                    <!-- Drawer Footer (User & Logout) -->
                    <div class="space-y-3 border-t border-[#000000]/10 pt-4">
                        <div class="flex items-center gap-3 px-2">
                            <div
                                class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-xs font-bold text-[#000000]"
                            >
                                {{
                                    authUser?.name
                                        ? authUser.name.charAt(0).toUpperCase()
                                        : 'A'
                                }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-xs font-bold text-[#000000]"
                                >
                                    {{ authUser?.name }}
                                </div>
                                <div
                                    class="truncate text-[11px] text-[#333333]"
                                >
                                    {{ authUser?.email }}
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="handleLogout"
                            aria-label="Keluar dari SIMRS"
                            class="flex min-h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-800 transition-colors hover:bg-rose-100"
                        >
                            <LogOut class="size-4" />
                            <span>Keluar dari SIMRS</span>
                        </button>
                    </div>
                </aside>
            </Transition>
        </Teleport>

        <!-- ═══════════════════════════════════════════════════════════════
             3. Desktop App Layout (lg:flex)
             ═══════════════════════════════════════════════════════════════ -->
        <div class="flex min-h-screen">
            <!-- Desktop Persistent Left Sidebar (lg:w-64 xl:w-72) -->
            <aside
                class="sticky top-0 hidden h-screen shrink-0 flex-col justify-between border-r border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs lg:flex lg:w-64 xl:w-72"
            >
                <!-- Sidebar Brand Header -->
                <div class="space-y-6">
                    <Link
                        href="/admin/dashboard"
                        class="flex items-center gap-3"
                    >
                        <motion.div
                            :whileHover="{ scale: 1.08, rotate: 3 }"
                            :whileTap="{ scale: 0.95 }"
                            class="flex size-10 items-center justify-center rounded-2xl bg-[#beedc0] text-[#000000] shadow-xs"
                        >
                            <AppLogoIcon class="size-6" />
                        </motion.div>
                        <div class="flex flex-col">
                            <span
                                class="font-['ivypresto-headline'] text-base leading-none font-bold tracking-tight text-[#000000]"
                            >
                                Hospital Population
                            </span>
                            <span
                                class="mt-1 text-[11px] font-bold tracking-wider text-[#333333] uppercase"
                            >
                                Super Admin Console
                            </span>
                        </div>
                    </Link>

                    <!-- Nav Groups -->
                    <nav
                        aria-label="Navigasi Menu Utama"
                        class="max-h-[calc(100vh-220px)] space-y-5 overflow-y-auto pr-1"
                    >
                        <div
                            v-for="group in adminNavGroups"
                            :key="group.title"
                            class="space-y-1.5"
                        >
                            <div
                                class="px-3 text-[10px] font-extrabold tracking-wider text-[#333333] uppercase"
                            >
                                {{ group.title }}
                            </div>
                            <div class="space-y-1">
                                <Link
                                    v-for="item in group.items"
                                    :key="item.href"
                                    :href="item.href"
                                    class="flex min-h-[44px] items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all duration-150"
                                    :class="
                                        isRouteActive(item.activePattern)
                                            ? 'bg-[#000000] text-[#ffffff] shadow-xs'
                                            : 'text-[#000000]/80 hover:bg-[#edede2] hover:text-[#000000]'
                                    "
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-4 shrink-0"
                                        :class="
                                            isRouteActive(item.activePattern)
                                                ? 'text-[#beedc0]'
                                                : 'text-[#000000]'
                                        "
                                    />
                                    <span class="truncate">{{
                                        item.label
                                    }}</span>
                                </Link>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Desktop Sidebar Footer -->
                <div class="space-y-3 border-t border-[#000000]/10 pt-4">
                    <div class="flex items-center gap-3 px-2 py-1">
                        <div
                            class="flex size-9 items-center justify-center rounded-full bg-[#beedc0] text-xs font-bold text-[#000000] shadow-xs"
                        >
                            {{
                                authUser?.name
                                    ? authUser.name.charAt(0).toUpperCase()
                                    : 'A'
                            }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div
                                class="truncate text-xs font-bold text-[#000000]"
                            >
                                {{ authUser?.name }}
                            </div>
                            <div class="truncate text-[11px] text-[#333333]">
                                {{ authUser?.email }}
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="handleLogout"
                        aria-label="Keluar Sistem"
                        class="flex min-h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-800 transition-colors hover:bg-rose-100"
                    >
                        <LogOut class="size-4" />
                        <span>Keluar Sistem</span>
                    </button>
                </div>
            </aside>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. Main Content Area
                 ═══════════════════════════════════════════════════════════════ -->
            <main
                class="min-w-0 flex-1 bg-[#edede2] px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
            >
                <!-- Breadcrumbs & Quick Header on Desktop -->
                <nav
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                    aria-label="Breadcrumb"
                    class="mb-6 hidden items-center gap-2 text-xs font-medium text-[#000000]/70 sm:flex"
                >
                    <Link
                        href="/admin/dashboard"
                        class="flex items-center gap-1 transition-colors hover:text-[#065f46]"
                    >
                        <Home class="size-3.5" />
                        <span>Super Admin</span>
                    </Link>
                    <template v-for="(b, idx) in breadcrumbs" :key="idx">
                        <ChevronRight class="size-3 text-[#000000]/40" />
                        <Link
                            v-if="b.href && idx < breadcrumbs.length - 1"
                            :href="b.href"
                            class="hover:text-[#065f46]"
                        >
                            {{ b.title }}
                        </Link>
                        <span v-else class="font-bold text-[#000000]">{{
                            b.title
                        }}</span>
                    </template>
                </nav>

                <!-- Page Slot Content -->
                <slot />
            </main>
        </div>
    </div>
</template>
