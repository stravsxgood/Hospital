<script setup lang="ts">
/**
 * @file AdminLayout.vue — Super Admin Responsive Master Layout
 * @description Layout terdedikasi Super Admin dengan Mobile Off-Canvas Drawer, Desktop Sidebar,
 *              Top Header responsif, Breadcrumbs, Flash Messages, dan Touch Targets >= 44px.
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Breakpoints: Mobile (< 640px), Tablet (640px - 1024px), Desktop (>= 1024px).
 *  - Touch target minimal 44x44px untuk seluruh kontrol interaktif.
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
    Shield,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    TrendingUp,
    UserCheck,
    Users,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { Toaster } from '@/components/ui/sonner'

export interface BreadcrumbItem {
    title: string
    href?: string
}

const props = withDefaults(
    defineProps<{
        title?: string
        breadcrumbs?: BreadcrumbItem[]
    }>(),
    {
        title: 'Super Admin - Hospital Population',
        breadcrumbs: () => [],
    }
)

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const flash = computed(() => (page.props.flash as any) || {})

// ═══════════════════════════════════════════════════════════════
// Mobile Drawer State & Auto-close Listeners
// ═══════════════════════════════════════════════════════════════
const isMounted = ref(false)
const isMobileDrawerOpen = ref(false)

const openMobileDrawer = () => {
    isMobileDrawerOpen.value = true
    document.body.style.overflow = 'hidden'
}

const closeMobileDrawer = () => {
    isMobileDrawerOpen.value = false
    document.body.style.overflow = ''
}

// Auto-close on Escape key
const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileDrawerOpen.value) {
        closeMobileDrawer()
    }
}

// Auto-close on Inertia navigation
const removeNavigateListener = router.on('navigate', () => {
    closeMobileDrawer()
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

// ═══════════════════════════════════════════════════════════════
// Navigation Items & Active Route Helper
// ═══════════════════════════════════════════════════════════════
interface NavItem {
    label: string
    href: string
    icon: any
    activePattern: string
    badge?: string
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
        title: 'Akses Portal SIMRS',
        items: [
            {
                label: 'Dashboard Staf / Kasir',
                href: '/staff',
                icon: LayoutGrid,
                activePattern: '/staff',
            },
            {
                label: 'Panggilan Antrean Dokter',
                href: '/doctor/queue',
                icon: Stethoscope,
                activePattern: '/doctor/queue',
            },
            {
                label: 'Portal Layanan Pasien',
                href: '/',
                icon: Home,
                activePattern: '/patient',
            },
        ],
    },
])

const isRouteActive = (pattern: string): boolean => {
    const currentPath = page.url.split('?')[0]

    if (pattern === '/admin/dashboard') {
        return currentPath === '/admin/dashboard' || currentPath === '/admin'
    }

    return currentPath.startsWith(pattern)
}

const handleLogout = () => {
    router.post('/logout')
}
</script>

<template>
    <div class="min-h-screen bg-[#edede2] font-['Rubik'] text-[#000000] antialiased selection:bg-[#beedc0] selection:text-[#065f46]">
        <Head :title="title" />

        <!-- ═══════════════════════════════════════════════════════════════
             1. Mobile & Tablet Top Navigation Header (< 1024px)
             ═══════════════════════════════════════════════════════════════ -->
        <header class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-[#000000]/10 bg-[#fffff3]/95 px-4 backdrop-blur-md lg:hidden">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button (Min touch target 44px) -->
                <button
                    type="button"
                    @click="openMobileDrawer"
                    aria-label="Buka Menu Navigasi"
                    class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl border border-[#000000]/10 bg-[#fffff3] text-[#000000] hover:bg-[#edede2] active:scale-95 transition-all cursor-pointer"
                >
                    <Menu class="size-5 text-[#065f46]" />
                </button>

                <!-- Brand Logo & Title -->
                <Link href="/admin/dashboard" class="flex items-center gap-2.5">
                    <div class="size-8 rounded-full bg-[#beedc0] text-[#065f46] flex items-center justify-center shadow-xs">
                        <AppLogoIcon class="size-5" />
                    </div>
                    <div class="flex flex-col">
                        <span class="font-['ivypresto-headline'] text-sm font-bold tracking-tight text-[#000000] leading-none">
                            Hospital Population
                        </span>
                        <span class="text-[10px] font-bold text-[#065f46] uppercase tracking-wider mt-0.5">
                            Super Admin
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Header Right: User Role Badge -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-[#065f46] px-2.5 py-1 text-[11px] font-bold text-[#ffffff]">
                    <ShieldCheck class="size-3 text-[#beedc0]" />
                    <span class="max-w-[100px] truncate sm:max-w-none">{{ authUser?.name || 'Super Admin' }}</span>
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
                    <div class="flex items-center justify-between border-b border-[#000000]/10 pb-4">
                        <Link href="/admin/dashboard" @click="closeMobileDrawer" class="flex items-center gap-2.5">
                            <div class="size-9 rounded-full bg-[#beedc0] text-[#065f46] flex items-center justify-center shadow-xs">
                                <AppLogoIcon class="size-5" />
                            </div>
                            <div>
                                <h2 class="font-['ivypresto-headline'] text-base font-bold text-[#000000]">
                                    Hospital SIMRS
                                </h2>
                                <p class="text-[11px] font-bold text-[#065f46] uppercase">Master Governance</p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            @click="closeMobileDrawer"
                            aria-label="Tutup Menu"
                            class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl text-[#000000]/60 hover:bg-[#edede2] hover:text-[#000000] transition-colors cursor-pointer"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Drawer Nav Items -->
                    <nav aria-label="Navigasi Menu Mobile" class="my-4 flex-1 space-y-5 overflow-y-auto pr-1">
                        <div v-for="group in adminNavGroups" :key="group.title" class="space-y-1.5">
                            <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-[#065f46]">
                                {{ group.title }}
                            </div>
                            <div class="space-y-1">
                                <Link
                                    v-for="item in group.items"
                                    :key="item.href"
                                    :href="item.href"
                                    @click="closeMobileDrawer"
                                    class="min-h-[44px] flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all"
                                    :class="isRouteActive(item.activePattern)
                                        ? 'bg-[#065f46] text-[#ffffff] shadow-xs'
                                        : 'text-[#000000]/80 hover:bg-[#edede2] hover:text-[#000000]'"
                                >
                                    <component :is="item.icon" class="size-4 shrink-0" :class="isRouteActive(item.activePattern) ? 'text-[#beedc0]' : 'text-[#065f46]'" />
                                    <span>{{ item.label }}</span>
                                </Link>
                            </div>
                        </div>
                    </nav>

                    <!-- Drawer Footer (User & Logout) -->
                    <div class="border-t border-[#000000]/10 pt-4 space-y-3">
                        <div class="flex items-center gap-3 px-2">
                            <div class="size-9 rounded-full bg-[#065f46] text-[#ffffff] font-bold flex items-center justify-center text-xs">
                                {{ authUser?.name ? authUser.name.charAt(0).toUpperCase() : 'A' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-xs text-[#000000] truncate">{{ authUser?.name }}</div>
                                <div class="text-[11px] text-[#000000]/70 truncate">{{ authUser?.email }}</div>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="handleLogout"
                            aria-label="Keluar dari SIMRS"
                            class="min-h-[44px] w-full flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-800 hover:bg-rose-100 transition-colors cursor-pointer"
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
            <aside class="hidden lg:flex lg:w-64 xl:w-72 shrink-0 flex-col justify-between border-r border-[#000000]/10 bg-[#fffff3] p-5 shadow-xs sticky top-0 h-screen">
                <!-- Sidebar Brand Header -->
                <div class="space-y-6">
                    <Link href="/admin/dashboard" class="flex items-center gap-3">
                        <motion.div
                            :whileHover="{ scale: 1.08, rotate: 3 }"
                            :whileTap="{ scale: 0.95 }"
                            class="size-10 rounded-2xl bg-[#beedc0] text-[#065f46] flex items-center justify-center shadow-xs"
                        >
                            <AppLogoIcon class="size-6" />
                        </motion.div>
                        <div class="flex flex-col">
                            <span class="font-['ivypresto-headline'] text-base font-bold tracking-tight text-[#000000] leading-none">
                                Hospital Population
                            </span>
                            <span class="text-[11px] font-bold text-[#065f46] uppercase tracking-wider mt-1">
                                Super Admin Console
                            </span>
                        </div>
                    </Link>

                    <!-- Nav Groups -->
                    <nav aria-label="Navigasi Menu Utama" class="space-y-5 overflow-y-auto max-h-[calc(100vh-220px)] pr-1">
                        <div v-for="group in adminNavGroups" :key="group.title" class="space-y-1.5">
                            <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-[#065f46]">
                                {{ group.title }}
                            </div>
                            <div class="space-y-1">
                                <Link
                                    v-for="item in group.items"
                                    :key="item.href"
                                    :href="item.href"
                                    class="min-h-[44px] flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all duration-150"
                                    :class="isRouteActive(item.activePattern)
                                        ? 'bg-[#065f46] text-[#ffffff] shadow-xs'
                                        : 'text-[#000000]/80 hover:bg-[#edede2] hover:text-[#000000]'"
                                >
                                    <component :is="item.icon" class="size-4 shrink-0" :class="isRouteActive(item.activePattern) ? 'text-[#beedc0]' : 'text-[#065f46]'" />
                                    <span class="truncate">{{ item.label }}</span>
                                </Link>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Desktop Sidebar Footer -->
                <div class="border-t border-[#000000]/10 pt-4 space-y-3">
                    <div class="flex items-center gap-3 px-2 py-1">
                        <div class="size-9 rounded-full bg-[#065f46] text-[#ffffff] font-bold flex items-center justify-center text-xs shadow-xs">
                            {{ authUser?.name ? authUser.name.charAt(0).toUpperCase() : 'A' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-xs text-[#000000] truncate">{{ authUser?.name }}</div>
                            <div class="text-[11px] text-[#000000]/70 truncate">{{ authUser?.email }}</div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="handleLogout"
                        aria-label="Keluar Sistem"
                        class="min-h-[44px] w-full flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-800 hover:bg-rose-100 transition-colors cursor-pointer"
                    >
                        <LogOut class="size-4" />
                        <span>Keluar Sistem</span>
                    </button>
                </div>
            </aside>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. Main Content Area
                 ═══════════════════════════════════════════════════════════════ -->
            <main class="flex-1 min-w-0 bg-[#edede2] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <!-- Breadcrumbs & Quick Header on Desktop -->
                <nav v-if="breadcrumbs && breadcrumbs.length > 0" aria-label="Breadcrumb" class="mb-6 hidden sm:flex items-center gap-2 text-xs font-medium text-[#000000]/70">
                    <Link href="/admin/dashboard" class="hover:text-[#065f46] transition-colors flex items-center gap-1">
                        <Home class="size-3.5" />
                        <span>Super Admin</span>
                    </Link>
                    <template v-for="(b, idx) in breadcrumbs" :key="idx">
                        <ChevronRight class="size-3 text-[#000000]/40" />
                        <Link v-if="b.href && idx < breadcrumbs.length - 1" :href="b.href" class="hover:text-[#065f46]">
                            {{ b.title }}
                        </Link>
                        <span v-else class="font-bold text-[#000000]">{{ b.title }}</span>
                    </template>
                </nav>

                <!-- Flash Message Alerts -->
                <div v-if="flash?.success || flash?.error" class="mb-6">
                    <motion.div
                        :initial="{ opacity: 0, y: -10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        class="flex items-center justify-between rounded-2xl p-4 text-xs sm:text-sm font-semibold shadow-xs"
                        :class="flash?.success ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-rose-100 text-rose-900 border border-rose-300'"
                    >
                        <div class="flex items-center gap-2.5">
                            <CheckCircle2 v-if="flash?.success" class="size-5 text-emerald-700 shrink-0" />
                            <AlertCircle v-else class="size-5 text-rose-700 shrink-0" />
                            <span>{{ flash?.success || flash?.error }}</span>
                        </div>
                    </motion.div>
                </div>

                <!-- Page Slot Content -->
                <slot />
            </main>
        </div>

        <Toaster />
    </div>
</template>
