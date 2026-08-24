<script setup lang="ts">
/**
 * AppSidebar.vue — Main Application Sidebar
 *
 * The primary navigation sidebar for Hospital Population.
 * Renders distinct menu groups based on user roles:
 *   - Doctor/Staff: Medical Management (Dashboard + Panggilan Antrean)
 *   - Patient: Public Services (Jadwal Dokter + Antrean Saya)
 */
import { Link, usePage } from '@inertiajs/vue3';
import type { Calendar} from '@lucide/vue';
import { Activity, BookOpen, Building2, GraduationCap, Home, LayoutGrid, Pill, Receipt, Shield, ShieldCheck, Ticket, TrendingUp, Tv, Users } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';

declare const route: any;

/* ── Reactive state from Inertia page props ── */
const page = usePage();
const user = computed(() => page.props.auth?.user);

const isNurse = computed(() => Boolean(user.value?.nurse));
const isNurseTetap = computed(() => isNurse.value && (Boolean(user.value?.nurse?.is_tetap) || user.value?.nurse?.type === 'tetap'));
const isNurseKoas = computed(() => isNurse.value && (Boolean(user.value?.nurse?.is_koas) || user.value?.nurse?.type === 'koas'));
const isDoctor = computed(() => user.value?.role === 'doctor' || Boolean(user.value?.is_doctor) || Boolean(user.value?.doctor));
const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'super-admin');
const isStaffOrDoctor = computed(() => isNurse.value || isDoctor.value || isAdmin.value);

/** Cek apakah user adalah pasien */
const isPatient = computed(() => !isStaffOrDoctor.value);

/** Menu Khusus Super Administrator */
const adminMenuItems = computed<MenuItem[]>(() => [
    {
        label: 'Dashboard Eksekutif',
        icon: TrendingUp,
        href: '/admin/dashboard',
        activePattern: 'admin.dashboard',
        fallbackUrl: '/admin/dashboard',
        exact: true,
        tooltip: 'Tata Kelola & Finansial Eksekutif',
    },
    {
        label: 'Manajemen Pengguna',
        icon: Users,
        href: '/admin/users',
        activePattern: 'admin.users.*',
        fallbackUrl: '/admin/users',
        exact: false,
        tooltip: 'Provisioning Dokter & Staf',
    },
    {
        label: 'Fasilitas & Jadwal',
        icon: Building2,
        href: '/admin/polis',
        activePattern: 'admin.polis.*',
        fallbackUrl: '/admin/polis',
        exact: false,
        tooltip: 'Poliklinik, Ruangan & Jadwal DPJP',
    },
    {
        label: 'Audit Akses Global',
        icon: ShieldCheck,
        href: '/admin/audit-logs',
        activePattern: 'admin.audit-logs.*',
        fallbackUrl: '/admin/audit-logs',
        exact: false,
        tooltip: 'Jejak Audit EMR UU PDP',
    },
]);

/** URL tujuan dashboard staf / dokter */
const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : (isDoctor.value ? '/doctor/queue' : '/staff')
);

/** Helper untuk active check */
const isRouteActive = (pattern: string, fallbackUrl: string, exact: boolean = false): boolean => {
    const currentPath = page.url.split('?')[0];

    // Jika menggunakan helper route() dari Ziggy
    if (typeof route === 'function' && route().current) {
        if (exact || fallbackUrl === '/staff') {
            // Khusus dashboard staf, hanya aktif jika persis pada route staff/staff.dashboard dan bukan sub-halaman
            return (route().current('staff') || route().current('staff.dashboard')) && 
                   !route().current('staff.billing.*') && 
                   !route().current('staff.medicines.*') &&
                   !route().current('staff.audit-logs.*');
        }

        return route().current(pattern);
    }

    // Fallback pencocokan URL pathname
    if (exact || fallbackUrl === '/staff') {
        return currentPath === '/staff' || currentPath === '/staff/dashboard';
    }

    if (fallbackUrl === '/') {
        return currentPath === '/';
    }

    return currentPath.startsWith(fallbackUrl);
};

interface MenuItem {
    label: string;
    icon: typeof Calendar;
    href: string;
    activePattern: string;
    fallbackUrl: string;
    exact?: boolean;
    tooltip: string;
}

/** Menu khusus Staf / Dokter / Koas dengan RBAC */
const staffMenuItems = computed<MenuItem[]>(() => {
    const items: MenuItem[] = [
        {
            label: isDoctor.value ? 'Dashboard Dokter' : 'Dashboard Staf',
            icon: LayoutGrid,
            href: '/staff',
            activePattern: 'staff.dashboard',
            fallbackUrl: '/staff',
            exact: true,
            tooltip: isDoctor.value ? 'Dashboard Dokter' : 'Dashboard Staf',
        },
    ];

    // Menu "Panggilan Antrean" & "Supervisi Koas" HANYA tampil jika login sebagai DOKTER
    if (isDoctor.value) {
        items.push({
            label: 'Panggilan Antrean',
            icon: Activity,
            href: '/doctor/queue',
            activePattern: 'doctor.queue.*',
            fallbackUrl: '/doctor/queue',
            exact: false,
            tooltip: 'Antrean & Konsultasi Dokter',
        });
        items.push({
            label: 'Supervisi Koas',
            icon: GraduationCap,
            href: '/doctor/supervision',
            activePattern: 'doctor.supervision.*',
            fallbackUrl: '/doctor/supervision',
            exact: false,
            tooltip: 'Supervisi Klinis Mahasiswa',
        });
    }

    // Menu "Logbook Klinis" HANYA tampil jika login sebagai Mahasiswa Koas
    if (isNurseKoas.value) {
        items.push({
            label: 'Logbook Klinis',
            icon: BookOpen,
            href: '/koas/logbook',
            activePattern: 'koas.logbook.*',
            fallbackUrl: '/koas/logbook',
            exact: false,
            tooltip: 'Logbook Kasus Klinis Digital',
        });
    }

    // Menu Farmasi, Kasir, & Audit Trail HANYA tampil jika login sebagai Perawat Tetap (Pekerja) / Admin
    if (isNurseTetap.value || isAdmin.value) {
        items.push({
            label: 'Inventori Obat',
            icon: Pill,
            href: '/staff/medicines',
            activePattern: 'staff.medicines.*',
            fallbackUrl: '/staff/medicines',
            exact: false,
            tooltip: 'Inventori & Stok Obat',
        });
        items.push({
            label: 'Kasir & Billing',
            icon: Receipt,
            href: '/staff/billing',
            activePattern: 'staff.billing.*',
            fallbackUrl: '/staff/billing',
            exact: false,
            tooltip: 'Kasir & Pembayaran',
        });
        items.push({
            label: 'Audit Rekam Medis',
            icon: ShieldCheck,
            href: '/staff/audit-logs',
            activePattern: 'staff.audit-logs.*',
            fallbackUrl: '/staff/audit-logs',
            exact: false,
            tooltip: 'Jejak Audit UU PDP',
        });
    }

    return items;
});

/** Menu khusus Pasien */
const patientMenuItems = computed<MenuItem[]>(() => [
    {
        label: 'Dashboard Pasien',
        icon: LayoutGrid,
        href: '/patient/dashboard',
        activePattern: 'patient.dashboard*',
        fallbackUrl: '/patient/dashboard',
        exact: false,
        tooltip: 'Dashboard Pasien',
    },
    {
        label: 'Antrean Saya',
        icon: Ticket,
        href: '/my-appointments',
        activePattern: 'my*',
        fallbackUrl: '/my-appointments',
        exact: false,
        tooltip: 'Antrean Saya',
    },
    {
        label: 'Beranda Utama',
        icon: Home,
        href: '/',
        activePattern: 'welcome',
        fallbackUrl: '/',
        exact: true,
        tooltip: 'Kembali ke Halaman Beranda',
    },
]);
</script>

<template>
    <Sidebar
        collapsible="icon"
        class="border-r border-[#333333]/15 bg-[#edede2] text-[#000000]"
    >
        <!-- ═══════════════════════════════════════════════════════════════
             Brand Identity & Logo
             ═══════════════════════════════════════════════════════════════ -->
        <SidebarHeader class="border-b border-[#333333]/10 bg-[#edede2] p-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        tooltip="Hospital Population"
                        class="rounded-[10px] transition-colors duration-150 hover:bg-[#fffff3]"
                    >
                        <Link
                            :href="isStaffOrDoctor ? (isDoctor ? '/doctor/queue' : '/staff') : '/'"
                            class="flex items-center gap-3"
                        >
                            <motion.div
                                :initial="{ opacity: 0, scale: 0.8 }"
                                :animate="{ opacity: 1, scale: 1 }"
                                :whileHover="{ scale: 1.08, rotate: 3 }"
                                :whileTap="{ scale: 0.95 }"
                                :transition="{ duration: 0.25, ease: 'easeOut' }"
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0] shadow-[0_0_0_3px_rgba(190,237,192,0.3)]"
                            >
                                <AppLogoIcon class="h-6 w-6 text-[#000000]" />
                            </motion.div>

                            <div class="flex flex-col text-left group-data-[collapsible=icon]:hidden">
                                <span class="font-['ivypresto-headline'] text-[16px] font-semibold leading-[1.4] tracking-tight text-[#000000]">
                                    Hospital Population
                                </span>
                                <span class="font-['Rubik'] text-[12px] font-medium leading-[1.6] text-[#333333]">
                                    {{ isStaffOrDoctor ? 'Panel Tenaga Medis' : 'Portal Layanan Pasien' }}
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <SidebarMenu v-if="isStaffOrDoctor && page.props.currentTeam" class="mt-2">
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- ═══════════════════════════════════════════════════════════════
             Navigation Menu
             ═══════════════════════════════════════════════════════════════ -->
        <SidebarContent class="bg-[#edede2] px-3 py-4 space-y-5">

            <!-- ── 0. Khusus Super Admin / Tata Kelola Eksekutif ── -->
            <motion.div
                v-if="isAdmin"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut' }"
            >
                <SidebarGroup>
                    <SidebarGroupLabel
                        class="mb-2 flex items-center gap-2 px-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#333333]/70"
                    >
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#065f46]" aria-hidden="true" />
                        <span>Super Administrator</span>
                    </SidebarGroupLabel>

                    <div class="mx-3 mb-2 h-px bg-[#333333]/8" aria-hidden="true" />

                    <SidebarGroupContent>
                        <SidebarMenu class="space-y-1">
                            <SidebarMenuItem
                                v-for="(item, index) in adminMenuItems"
                                :key="item.label"
                            >
                                <motion.div
                                    :initial="{ opacity: 0, x: -10 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.05 + index * 0.05 }"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :tooltip="item.tooltip"
                                        class="w-full min-h-[44px] rounded-[40.5px] px-4 py-2.5 font-['Rubik'] text-[14px] font-medium transition-all duration-150"
                                        :class="isRouteActive(item.activePattern, item.fallbackUrl, item.exact)
                                            ? 'bg-[#065f46] text-[#ffffff] font-semibold border-l-[3px] border-[#beedc0] shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:bg-[#054d38] hover:text-[#ffffff]'
                                            : 'border border-transparent text-[#333333] hover:border-[#333333]/12 hover:bg-[#fffff3] hover:text-[#000000]'"
                                    >
                                        <Link :href="item.href" class="flex items-center gap-3">
                                            <motion.div
                                                :whileHover="{ x: 2, scale: 1.1 }"
                                                :whileTap="{ scale: 0.9 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                                class="flex items-center justify-center"
                                            >
                                                <component :is="item.icon" class="size-[18px]" />
                                            </motion.div>
                                            <motion.span
                                                :whileHover="{ x: 2 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                            >
                                                {{ item.label }}
                                            </motion.span>
                                        </Link>
                                    </SidebarMenuButton>
                                </motion.div>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </motion.div>

            <!-- ── 1. Khusus Staf / Dokter ── -->
            <motion.div
                v-if="isStaffOrDoctor"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut', delay: 0.05 }"
            >
                <SidebarGroup>
                    <SidebarGroupLabel
                        class="mb-2 flex items-center gap-2 px-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#333333]/70"
                    >
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#beedc0]" aria-hidden="true" />
                        <span>Manajemen Medis</span>
                    </SidebarGroupLabel>

                    <div class="mx-3 mb-2 h-px bg-[#333333]/8" aria-hidden="true" />

                    <SidebarGroupContent>
                        <SidebarMenu class="space-y-1">
                            <SidebarMenuItem
                                v-for="(item, index) in staffMenuItems"
                                :key="item.label"
                            >
                                <motion.div
                                    :initial="{ opacity: 0, x: -10 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.1 + index * 0.06 }"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :tooltip="item.tooltip"
                                        class="w-full min-h-[44px] rounded-[40.5px] px-4 py-2.5 font-['Rubik'] text-[14px] font-medium transition-all duration-150"
                                        :class="isRouteActive(item.activePattern, item.fallbackUrl, item.exact)
                                            ? 'bg-[#000000] text-[#ffffff] font-semibold border-l-[3px] border-[#beedc0] shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:bg-[#333333] hover:text-[#ffffff]'
                                            : 'border border-transparent text-[#333333] hover:border-[#333333]/12 hover:bg-[#fffff3] hover:text-[#000000]'"
                                    >
                                        <Link :href="item.href" class="flex items-center gap-3">
                                            <motion.div
                                                :whileHover="{ x: 2, scale: 1.1 }"
                                                :whileTap="{ scale: 0.9 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                                class="flex items-center justify-center"
                                            >
                                                <component :is="item.icon" class="size-[18px]" />
                                            </motion.div>
                                            <motion.span
                                                :whileHover="{ x: 2 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                            >
                                                {{ item.label }}
                                            </motion.span>
                                        </Link>
                                    </SidebarMenuButton>
                                </motion.div>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </motion.div>

            <!-- ── 2. Khusus Pasien (Hanya dirender jika bukan dokter) ── -->
            <motion.div
                v-if="isPatient"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut', delay: 0.05 }"
            >
                <SidebarGroup>
                    <SidebarGroupLabel
                        class="mb-2 flex items-center gap-2 px-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#333333]/70"
                    >
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#beedc0]" aria-hidden="true" />
                        <span>Layanan Pasien</span>
                    </SidebarGroupLabel>

                    <div class="mx-3 mb-2 h-px bg-[#333333]/8" aria-hidden="true" />

                    <SidebarGroupContent>
                        <SidebarMenu class="space-y-1">
                            <SidebarMenuItem
                                v-for="(item, index) in patientMenuItems"
                                :key="item.label"
                            >
                                <motion.div
                                    :initial="{ opacity: 0, x: -10 }"
                                    :animate="{ opacity: 1, x: 0 }"
                                    :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.1 + index * 0.06 }"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :tooltip="item.tooltip"
                                        class="w-full min-h-[44px] rounded-[40.5px] px-4 py-2.5 font-['Rubik'] text-[14px] font-medium transition-all duration-150"
                                        :class="isRouteActive(item.activePattern, item.fallbackUrl, item.exact)
                                            ? 'bg-[#000000] text-[#ffffff] font-semibold border-l-[3px] border-[#beedc0] shadow-[0_1px_3px_rgba(0,0,0,0.08)] hover:bg-[#333333] hover:text-[#ffffff]'
                                            : 'border border-transparent text-[#333333] hover:border-[#333333]/12 hover:bg-[#fffff3] hover:text-[#000000]'"
                                    >
                                        <Link :href="item.href" class="flex items-center gap-3">
                                            <motion.div
                                                :whileHover="{ x: 2, scale: 1.1 }"
                                                :whileTap="{ scale: 0.9 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                                class="flex items-center justify-center"
                                            >
                                                <component :is="item.icon" class="size-[18px]" />
                                            </motion.div>
                                            <motion.span
                                                :whileHover="{ x: 2 }"
                                                :transition="{ duration: 0.15, ease: 'easeOut' }"
                                            >
                                                {{ item.label }}
                                            </motion.span>
                                        </Link>
                                    </SidebarMenuButton>
                                </motion.div>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </motion.div>
        </SidebarContent>

        <!-- ═══════════════════════════════════════════════════════════════
             Footer
             ═══════════════════════════════════════════════════════════════ -->
        <SidebarFooter class="border-t border-[#333333]/10 bg-[#edede2] p-3">
            <div class="mx-auto mb-2 h-[2px] w-10 rounded-full bg-[#beedc0]/60 group-data-[collapsible=icon]:w-5" aria-hidden="true" />
            <motion.div
                :initial="{ opacity: 0, y: 8 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut', delay: 0.3 }"
            >
                <NavUser />
            </motion.div>
        </SidebarFooter>
    </Sidebar>
</template>