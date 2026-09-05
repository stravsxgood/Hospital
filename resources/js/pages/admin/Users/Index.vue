<script setup lang="ts">
/**
 * @file Index.vue (Super Admin User Management & Provisioning)
 * @description Direktori Pengguna SIMRS, Provisioning Dokter DPJP & Perawat/Koas, Toggle Status Aman, & Reset Password.
 *              100% Responsif untuk Mobile (<640px), Tablet/iPad (640-1024px), dan Desktop (>1024px).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    Building2,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    Copy,
    Edit3,
    Eye,
    FileText,
    GraduationCap,
    HeartPulse,
    KeyRound,
    Loader2,
    Lock,
    Plus,
    RefreshCw,
    Search,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    ToggleLeft,
    ToggleRight,
    Trash2,
    UserCheck,
    UserPlus,
    Users,
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
    tableFeatures,
} from '@tanstack/table-core';
import type { ColumnFiltersState, SortingState } from '@tanstack/table-core';
import { createTableHook } from '@tanstack/vue-table';
import axios from 'axios';
import { motion } from 'motion-v';
import { computed, onMounted, ref, watch } from 'vue';
import ResponsiveTable from '@/components/admin/ResponsiveTable.vue';
import Pagination from '@/components/Pagination.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface SpecializationItem {
    specialization_id: number;
    code_specialization: string;
    name_specialization: string;
}

interface PoliOption {
    poli_id: number;
    name_poli: string;
    kode_poli: string;
}

interface RoomOption {
    room_id: number;
    name_room: string;
    code_room: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
    roles: Array<{ name: string }>;
    doctor?: {
        doctor_id: number;
        name: string;
        sip_number: string;
        gender: string;
        status: string;
        specialization?: { name_specialization: string };
    };
    nurse?: {
        nurse_id: number;
        name: string;
        registration_number: string | null;
        type: 'tetap' | 'koas';
        institute: string | null;
        gender: string;
    };
    patient?: {
        patient_id: number;
        resident_n: string;
        name: string;
        gender: string;
        status: string;
    };
}

const props = defineProps<{
    users: {
        data: UserItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    all_users?: UserItem[];
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
    stats: {
        total_users: number;
        total_doctors: number;
        total_nurses_tetap: number;
        total_nurses_koas: number;
        total_inactive: number;
    };
    specializations: SpecializationItem[];
    polis: PoliOption[];
    rooms: RoomOption[];
}>();

// ═══════════════════════════════════════════════════════════════
// TANSTACK TABLE IMPLEMENTATION (Dynamic Client-Side Search & Pagination)
// ═══════════════════════════════════════════════════════════════
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

const allUsersData = computed<UserItem[]>(() => {
    if (props.all_users && Array.isArray(props.all_users)) {
        return props.all_users;
    }

    return props.users?.data || [];
});

// Search & Filter state
const searchInput = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const sorting = ref<SortingState>([]);
const pagination = ref({
    pageIndex: 0,
    pageSize: 10,
});

const columnFilters = computed<ColumnFiltersState>(() => {
    const filters: ColumnFiltersState = [];

    if (selectedRole.value && selectedRole.value !== 'all') {
        filters.push({ id: 'role', value: selectedRole.value });
    }

    if (selectedStatus.value && selectedStatus.value !== 'all') {
        filters.push({ id: 'status', value: selectedStatus.value });
    }

    return filters;
});

const roleFilterFn = (row: any, columnId: string, filterValue: any): boolean => {
    if (!filterValue || filterValue === 'all') {
        return true;
    }

    const u = row.original as UserItem;

    if (!u) {
        return true;
    }

    const role = u.role?.toLowerCase() || '';
    const hasRole = (r: string) => {
        return (u.roles || []).some((ro) => ro.name.toLowerCase() === r.toLowerCase());
    };

    if (filterValue === 'doctor') {
        return role === 'doctor' || hasRole('dpjp-doctor') || Boolean(u.doctor);
    }

    if (filterValue === 'nurse_tetap') {
        return u.nurse?.type === 'tetap';
    }

    if (filterValue === 'koas') {
        return u.nurse?.type === 'koas';
    }

    if (filterValue === 'nurse') {
        return role === 'nurse' || Boolean(u.nurse);
    }

    if (filterValue === 'admin' || filterValue === 'super-admin') {
        return role === 'admin' || role === 'super-admin' || hasRole('super-admin');
    }

    if (filterValue === 'patient') {
        return role === 'patient' || (!u.doctor && !u.nurse && role !== 'admin');
    }

    return true;
};

const statusFilterFn = (row: any, columnId: string, filterValue: any): boolean => {
    if (!filterValue || filterValue === 'all') {
        return true;
    }

    const u = row.original as UserItem;

    if (!u) {
        return true;
    }

    if (filterValue === 'active') {
        return u.is_active === true;
    }

    if (filterValue === 'inactive') {
        return u.is_active === false;
    }

    return true;
};

const globalFilterFn = (
    row: any,
    columnId: string,
    filterValue: any,
): boolean => {
    if (!filterValue) {
        return true;
    }

    const search = String(filterValue).toLowerCase().trim();

    if (!search) {
        return true;
    }

    const u = row.original as UserItem;

    if (!u) {
        return false;
    }

    const name = String(u.name || '').toLowerCase();
    const email = String(u.email || '').toLowerCase();
    const role = String(u.role || '').toLowerCase();
    const docName = String(u.doctor?.name || '').toLowerCase();
    const sip = String(u.doctor?.sip_number || '').toLowerCase();
    const spec = String(u.doctor?.specialization?.name_specialization || '').toLowerCase();
    const nurseName = String(u.nurse?.name || '').toLowerCase();
    const regNum = String(u.nurse?.registration_number || '').toLowerCase();
    const institute = String(u.nurse?.institute || '').toLowerCase();

    return (
        name.includes(search) ||
        email.includes(search) ||
        role.includes(search) ||
        docName.includes(search) ||
        sip.includes(search) ||
        spec.includes(search) ||
        nurseName.includes(search) ||
        regNum.includes(search) ||
        institute.includes(search)
    );
};

const columnHelper = createAppColumnHelper<UserItem>();

const columns = columnHelper.columns([
    columnHelper.accessor('name', {
        id: 'name',
        header: 'Pengguna & Akun',
    }),
    columnHelper.accessor('email', {
        id: 'email',
    }),
    columnHelper.accessor('role', {
        id: 'role',
        header: 'Role & Akses',
        filterFn: roleFilterFn,
    }),
    columnHelper.accessor(
        (row) => {
            if (row.doctor) {
                return `${row.doctor.name} ${row.doctor.sip_number} ${row.doctor.specialization?.name_specialization || ''}`;
            }

            if (row.nurse) {
                return `${row.nurse.name} ${row.nurse.registration_number || ''} ${row.nurse.institute || ''}`;
            }

            return '';
        },
        {
            id: 'medicalDetails',
            header: 'Detail Tenaga Medis',
        },
    ),
    columnHelper.accessor((row) => (row.is_active ? 'active' : 'inactive'), {
        id: 'status',
        header: 'Status',
        filterFn: statusFilterFn,
    }),
]);

const table = useAppTable<UserItem>({
    get data() {
        return allUsersData.value;
    },
    columns,
    state: {
        get globalFilter() {
            return searchInput.value;
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
        searchInput.value =
            typeof updaterOrValue === 'function'
                ? updaterOrValue(searchInput.value)
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

const setRoleTab = (tab: string) => {
    selectedRole.value = tab;
    pagination.value.pageIndex = 0;
};

const handleSearch = () => {
    pagination.value.pageIndex = 0;
};

// Sync initial page from URL query
onMounted(() => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const pageParam = parseInt(urlParams.get('page') || '1', 10);

        if (pageParam > 1) {
            pagination.value.pageIndex = pageParam - 1;
        }
    }
});

// Sync page changes to URL without browser refresh
watch(
    () => pagination.value.pageIndex,
    (newIdx) => {
        if (typeof window !== 'undefined') {
            const pageNum = newIdx + 1;
            const url = new URL(window.location.href);

            if (pageNum > 1) {
                url.searchParams.set('page', String(pageNum));
            } else {
                url.searchParams.delete('page');
            }

            window.history.replaceState({}, '', url.toString());
        }
    },
);

// ═══════════════════════════════════════════════════════════════
// Modal Provisioning Dokter DPJP
// ═══════════════════════════════════════════════════════════════
const isDoctorModalOpen = ref(false);
const isDoctorSubmitting = ref(false);
const doctorErrors = ref<Record<string, string>>({});
const doctorGeneralError = ref<string | null>(null);
const doctorForm = ref({
    name: '',
    email: '',
    password: '',
    specialization_id: '',
    sip_number: '',
    gender: 'Laki-laki',
    number_phone: '',
    alamat: '',
    join_date: new Date().toISOString().split('T')[0],
    create_schedule: false,
    poli_id: '',
    room_id: '',
    day: 'Senin',
    start_time: '08:00',
    end_time: '14:00',
    quota_day: 20,
});

const openDoctorModal = () => {
    doctorErrors.value = {};
    doctorGeneralError.value = null;
    doctorForm.value = {
        name: '',
        email: '',
        password: '',
        specialization_id: props.specializations[0]?.specialization_id
            ? String(props.specializations[0].specialization_id)
            : '',
        sip_number: '',
        gender: 'Laki-laki',
        number_phone: '',
        alamat: '',
        join_date: new Date().toISOString().split('T')[0],
        create_schedule: false,
        poli_id: props.polis[0]?.poli_id ? String(props.polis[0].poli_id) : '',
        room_id: props.rooms[0]?.room_id ? String(props.rooms[0].room_id) : '',
        day: 'Senin',
        start_time: '08:00',
        end_time: '14:00',
        quota_day: 20,
    };
    isDoctorModalOpen.value = true;
};

const handleCreateDoctor = async () => {
    doctorErrors.value = {};
    doctorGeneralError.value = null;
    isDoctorSubmitting.value = true;

    try {
        const res = await axios.post('/admin/users/doctors', doctorForm.value);
        isDoctorModalOpen.value = false;

        if (res.data?.temp_password) {
            showResetSuccessModal(
                doctorForm.value.name,
                doctorForm.value.email,
                res.data.temp_password,
            );
        }

        router.reload();
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            doctorErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [
                    k,
                    (v as string[])[0],
                ]),
            );
        } else {
            doctorGeneralError.value =
                err.response?.data?.message || 'Gagal mendaftarkan dokter.';
        }
    } finally {
        isDoctorSubmitting.value = false;
    }
};

// ═══════════════════════════════════════════════════════════════
// Modal Provisioning Staf / Perawat / Koas
// ═══════════════════════════════════════════════════════════════
const isNurseModalOpen = ref(false);
const isNurseSubmitting = ref(false);
const nurseErrors = ref<Record<string, string>>({});
const nurseGeneralError = ref<string | null>(null);
const nurseForm = ref({
    name: '',
    email: '',
    password: '',
    type: 'tetap',
    registration_number: '',
    gender: 'Perempuan',
    institute: '',
    date_start: '',
    date_end: '',
});

const openNurseModal = () => {
    nurseErrors.value = {};
    nurseGeneralError.value = null;
    nurseForm.value = {
        name: '',
        email: '',
        password: '',
        type: 'tetap',
        registration_number: '',
        gender: 'Perempuan',
        institute: '',
        date_start: '',
        date_end: '',
    };
    isNurseModalOpen.value = true;
};

const handleCreateNurse = async () => {
    nurseErrors.value = {};
    nurseGeneralError.value = null;
    isNurseSubmitting.value = true;

    try {
        const res = await axios.post('/admin/users/nurses', nurseForm.value);
        isNurseModalOpen.value = false;

        if (res.data?.temp_password) {
            showResetSuccessModal(
                nurseForm.value.name,
                nurseForm.value.email,
                res.data.temp_password,
            );
        }

        router.reload();
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            nurseErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [
                    k,
                    (v as string[])[0],
                ]),
            );
        } else {
            nurseGeneralError.value =
                err.response?.data?.message ||
                'Gagal mendaftarkan perawat/staf.';
        }
    } finally {
        isNurseSubmitting.value = false;
    }
};

// ═══════════════════════════════════════════════════════════════
// Modal Toggle Status Aman & Reset Password
// ═══════════════════════════════════════════════════════════════
const statusTargetUser = ref<UserItem | null>(null);
const isTogglingStatus = ref(false);
const statusErrorMessage = ref<string | null>(null);

const openToggleStatusModal = (user: UserItem) => {
    statusTargetUser.value = user;
    statusErrorMessage.value = null;
};

const confirmToggleStatus = async () => {
    if (!statusTargetUser.value) {
        return;
    }

    isTogglingStatus.value = true;
    statusErrorMessage.value = null;

    try {
        await axios.patch(
            `/admin/users/${statusTargetUser.value.id}/toggle-status`,
        );
        statusTargetUser.value = null;
        router.reload();
    } catch (err: any) {
        statusErrorMessage.value =
            err.response?.data?.message || 'Gagal mengubah status akun.';
    } finally {
        isTogglingStatus.value = false;
    }
};

// ═══════════════════════════════════════════════════════════════
// Modal Konfirmasi Hapus Akun Nonaktif (Soft-Deletes)
// ═══════════════════════════════════════════════════════════════
const deleteTargetUser = ref<UserItem | null>(null);
const isDeletingUser = ref(false);
const deleteErrorMessage = ref<string | null>(null);

const openDeleteModal = (user: UserItem) => {
    deleteTargetUser.value = user;
    deleteErrorMessage.value = null;
};

const confirmDeleteUser = () => {
    if (!deleteTargetUser.value) {
        return;
    }

    isDeletingUser.value = true;
    deleteErrorMessage.value = null;

    const userId = deleteTargetUser.value.id;

    router.delete(`/admin/users/${userId}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteTargetUser.value = null;
        },
        onError: (errors) => {
            deleteErrorMessage.value =
                (Object.values(errors)[0] as string) ||
                'Gagal menghapus akun pengguna.';
        },
        onFinish: () => {
            isDeletingUser.value = false;
        },
    });
};

// ═══════════════════════════════════════════════════════════════
// Modal Konfirmasi Hapus Data Pasien Secara Permanen (Hard Delete)
// ═══════════════════════════════════════════════════════════════
const isPatientUser = (user: UserItem): boolean => {
    return (
        user.role === 'patient' ||
        Boolean(user.patient) ||
        (!user.doctor && !user.nurse && user.role !== 'admin' && user.role !== 'super-admin')
    );
};

const forceDeleteTargetUser = ref<UserItem | null>(null);
const isForceDeleting = ref(false);
const forceDeleteErrorMessage = ref<string | null>(null);
const confirmConsent = ref(false);

const openForceDeleteModal = (user: UserItem) => {
    forceDeleteTargetUser.value = user;
    forceDeleteErrorMessage.value = null;
    confirmConsent.value = false;
};

const confirmForceDeleteUser = () => {
    if (!forceDeleteTargetUser.value) {
        return;
    }

    isForceDeleting.value = true;
    forceDeleteErrorMessage.value = null;

    const userId = forceDeleteTargetUser.value.id;

    router.delete(`/admin/users/${userId}/force`, {
        preserveScroll: true,
        onSuccess: () => {
            forceDeleteTargetUser.value = null;
        },
        onError: (errors) => {
            forceDeleteErrorMessage.value =
                (Object.values(errors)[0] as string) ||
                'Gagal menghapus data pasien secara permanen.';
        },
        onFinish: () => {
            isForceDeleting.value = false;
        },
    });
};

// Password Reset Confirmation & Success Modal
const resetTargetUser = ref<UserItem | null>(null);
const isResettingPassword = ref(false);
const resetErrorMessage = ref<string | null>(null);

const openResetPasswordModal = (user: UserItem) => {
    resetTargetUser.value = user;
    resetErrorMessage.value = null;
};

const resetResultModal = ref<{
    name: string;
    email: string;
    pass: string;
} | null>(null);
const copied = ref(false);

const showResetSuccessModal = (name: string, email: string, pass: string) => {
    resetResultModal.value = { name, email, pass };
    copied.value = false;
};

const confirmResetPassword = async () => {
    if (!resetTargetUser.value) {
        return;
    }

    isResettingPassword.value = true;
    resetErrorMessage.value = null;

    try {
        const user = resetTargetUser.value;
        const res = await axios.post(`/admin/users/${user.id}/reset-password`);
        const tempPass = res.data?.temporary_password || 'Hospital2026!';

        resetTargetUser.value = null;
        showResetSuccessModal(user.name, user.email, tempPass);
    } catch (err: any) {
        resetErrorMessage.value =
            err.response?.data?.message || 'Gagal mereset password pengguna.';
    } finally {
        isResettingPassword.value = false;
    }
};

const copyToClipboard = () => {
    if (!resetResultModal.value) {
        return;
    }

    navigator.clipboard.writeText(resetResultModal.value.pass);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};
</script>

<template>
    <AdminLayout
        title="Manajemen Pengguna & Tenaga Medis - Super Admin"
        :breadcrumbs="[{ title: 'Manajemen Pengguna', href: '/admin/users' }]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header & Provisioning Actions
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
                            <span>Direktori Pengguna & Tenaga Medis</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Provisioning & Tata Kelola Akun
                    </h1>
                    <p class="font-['Rubik'] text-xs text-[#333333] sm:text-sm">
                        Kelola akun dokter DPJP, staf perawat tetap, dokter muda
                        (koas), dan hak akses Spatie dengan aman.
                    </p>
                </div>

                <div
                    class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center"
                >
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="openDoctorModal"
                        class="inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 font-['Rubik'] text-xs font-medium text-[#ffffff] shadow-none transition-colors hover:bg-[#1a1a1a] sm:text-sm"
                    >
                        <Stethoscope class="size-4 text-[#beedc0]" />
                        <span>Tambah Dokter DPJP</span>
                    </motion.button>

                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="openNurseModal"
                        class="inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] px-5 py-2.5 font-['Rubik'] text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:text-sm"
                    >
                        <UserPlus class="size-4 text-[#000000]" />
                        <span>Tambah Staf / Koas</span>
                    </motion.button>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Metric Quick Cards (Grid responsif)
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="user-stats-overview-heading">
                <h2 id="user-stats-overview-heading" class="sr-only">
                    Statistik Ringkas Pengguna
                </h2>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5"
                >
                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Total Pengguna
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_users }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Dokter DPJP
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_doctors }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Perawat Tetap
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_nurses_tetap }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Dokter Muda
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_nurses_koas }}
                        </div>
                    </div>

                    <div
                        class="col-span-2 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none sm:col-span-1"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Akun Nonaktif
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_inactive }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Filter Tabs & Search Bar (Responsive Table Component)
                 ═══════════════════════════════════════════════════════════════ -->
            <ResponsiveTable
                :is-empty="table.getRowModel().rows.length === 0"
                empty-message="Tidak ada pengguna yang sesuai dengan filter pencarian."
            >
                <!-- Filters Slot -->
                <template #filters>
                    <div class="space-y-3">
                        <!-- Role Tabs (Horizontal scrollable on mobile) -->
                        <div
                            class="flex scrollbar-none items-center gap-2 overflow-x-auto pb-2"
                            role="tablist"
                            aria-label="Filter berdasarkan Peran"
                        >
                            <button
                                v-for="tab in [
                                    { id: 'all', label: 'Semua' },
                                    { id: 'doctor', label: 'Dokter DPJP' },
                                    {
                                        id: 'nurse_tetap',
                                        label: 'Perawat Tetap',
                                    },
                                    { id: 'koas', label: 'Dokter Muda (Koas)' },
                                    { id: 'admin', label: 'Super Admin' },
                                    { id: 'patient', label: 'Pasien' },
                                ]"
                                :key="tab.id"
                                type="button"
                                role="tab"
                                :aria-selected="selectedRole === tab.id"
                                @click="setRoleTab(tab.id)"
                                class="min-h-[40px] shrink-0 cursor-pointer rounded-full px-4 py-2 text-xs font-medium transition-all"
                                :class="
                                    selectedRole === tab.id
                                        ? 'bg-[#000000] text-[#ffffff] shadow-none'
                                        : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                                "
                            >
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- Search and Status Select -->
                        <div
                            class="flex flex-col items-stretch justify-between gap-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-3 shadow-none sm:flex-row sm:items-center sm:p-4"
                        >
                            <div class="relative flex-1">
                                <label for="user-search-input" class="sr-only"
                                    >Cari Pengguna Berdasarkan Nama, Email, SIP,
                                    STR, NIM</label
                                >
                                <Search
                                    class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#000000]/60"
                                />
                                <input
                                    id="user-search-input"
                                    v-model="searchInput"
                                    @keyup.enter="handleSearch"
                                    type="text"
                                    placeholder="Cari nama, email, SIP dokter, STR/NIM..."
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] pr-4 pl-10 text-xs text-[#000000] placeholder-[#000000]/50 focus:border-[#000000] focus:outline-none sm:text-sm"
                                />
                            </div>

                            <div class="flex items-center gap-2.5">
                                <label for="user-status-filter" class="sr-only"
                                    >Filter Berdasarkan Status Akun</label
                                >
                                <select
                                    id="user-status-filter"
                                    v-model="selectedStatus"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2 text-xs font-medium text-[#000000] focus:border-[#000000] focus:outline-none sm:w-auto"
                                >
                                    <option value="all">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>

                                <button
                                    type="button"
                                    @click="handleSearch"
                                    aria-label="Terapkan Filter Pencarian"
                                    class="min-h-[44px] shrink-0 rounded-[40.5px] bg-[#000000] px-5 text-xs font-medium text-[#ffffff] hover:bg-[#1a1a1a]"
                                >
                                    Cari
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Table Header Slot -->
                <template #header>
                    <tr>
                        <th class="px-4 py-3.5 sm:px-6">Pengguna & Akun</th>
                        <th class="px-4 py-3.5">Role & Akses</th>
                        <th class="px-4 py-3.5">Detail Tenaga Medis</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right sm:px-6">Aksi</th>
                    </tr>
                </template>

                <!-- Table Body Rows -->
                <tr
                    v-for="row in table.getRowModel().rows"
                    :key="row.original.id"
                    class="transition-colors hover:bg-[#edede2]/30"
                >
                    <!-- Nama & Email -->
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="font-bold text-[#000000]">{{ row.original.name }}</div>
                        <div
                            class="max-w-[180px] truncate text-xs text-[#333333] sm:max-w-none"
                        >
                            {{ row.original.email }}
                        </div>
                    </td>

                    <!-- Role Badge -->
                    <td class="px-4 py-3.5">
                        <span
                            v-if="row.original.doctor || row.original.role === 'doctor'"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3 py-0.5 text-xs font-bold text-[#000000]"
                        >
                            <Stethoscope class="size-3 text-[#000000]" />
                            <span>Dokter DPJP</span>
                        </span>
                        <span
                            v-else-if="row.original.nurse?.type === 'tetap'"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#000000]/10 bg-[#edede2] px-3 py-0.5 text-xs font-bold text-[#000000]"
                        >
                            <ShieldCheck class="size-3 text-[#000000]" />
                            <span>Perawat Tetap</span>
                        </span>
                        <span
                            v-else-if="row.original.nurse?.type === 'koas'"
                            class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-100/70 px-3 py-0.5 text-xs font-bold text-amber-900"
                        >
                            <GraduationCap class="size-3 text-amber-900" />
                            <span>Dokter Muda (Koas)</span>
                        </span>
                        <span
                            v-else-if="
                                row.original.role === 'admin' || row.original.role === 'super-admin'
                            "
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#000000] px-3 py-0.5 text-xs font-bold text-[#ffffff]"
                        >
                            <Shield class="size-3 text-[#beedc0]" />
                            <span>Super Admin</span>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full border border-[#000000]/10 bg-[#edede2] px-3 py-0.5 text-xs font-medium text-[#333333]"
                        >
                            <span>Pasien</span>
                        </span>
                    </td>

                    <!-- Detail Tenaga Medis -->
                    <td class="px-4 py-3.5 text-xs">
                        <div v-if="row.original.doctor" class="space-y-0.5">
                            <div class="font-bold text-[#000000]">
                                {{
                                    row.original.doctor.specialization
                                        ?.name_specialization || 'Spesialis'
                                 }}
                            </div>
                            <div class="font-mono text-[11px] text-[#333333]">
                                SIP: {{ row.original.doctor.sip_number }}
                            </div>
                        </div>
                        <div v-else-if="row.original.nurse" class="space-y-0.5">
                            <div class="font-medium text-[#000000]">
                                {{ row.original.nurse.institute || 'RS Utama' }}
                            </div>
                            <div class="font-mono text-[11px] text-[#333333]">
                                {{ row.original.nurse.type === 'koas' ? 'NIM' : 'STR' }}:
                                {{ row.original.nurse.registration_number || '-' }}
                            </div>
                        </div>
                        <span v-else class="text-[#333333]">-</span>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-3.5 text-center">
                        <span
                            :class="
                                row.original.is_active
                                    ? 'border-[#beedc0] bg-[#beedc0]/40 text-[#000000]'
                                    : 'border-rose-200 bg-rose-100/70 text-rose-900'
                            "
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-0.5 text-xs font-bold"
                        >
                            <span
                                :class="
                                    row.original.is_active ? 'bg-[#000000]' : 'bg-rose-600'
                                "
                                class="size-1.5 rounded-full"
                            ></span>
                            <span>{{
                                row.original.is_active ? 'Aktif' : 'Nonaktif'
                            }}</span>
                        </span>
                    </td>

                    <!-- Aksi -->
                    <td class="px-4 py-3.5 text-right sm:px-6">
                        <div
                            class="flex items-center justify-end gap-1.5 sm:gap-2"
                        >
                            <!-- Reset Password -->
                            <button
                                type="button"
                                @click="openResetPasswordModal(row.original)"
                                :aria-label="`Reset Password Pengguna ${row.original.name}`"
                                :title="`Reset Password ${row.original.name}`"
                                class="inline-flex min-h-[38px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] px-3 py-1.5 text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:px-3.5"
                            >
                                <KeyRound class="size-3.5 text-[#000000]" />
                                <span class="hidden sm:inline">Reset Pass</span>
                            </button>

                            <!-- Toggle Status -->
                            <button
                                type="button"
                                @click="openToggleStatusModal(row.original)"
                                :aria-label="`${row.original.is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun'} ${row.original.name}`"
                                :title="
                                    row.original.is_active
                                        ? 'Nonaktifkan Akun'
                                        : 'Aktifkan Akun'
                                "
                                class="inline-flex min-h-[38px] cursor-pointer items-center gap-1 rounded-[40.5px] border px-3 py-1.5 text-xs font-medium transition-colors sm:px-3.5"
                                :class="
                                    row.original.is_active
                                        ? 'border-rose-200 bg-rose-50 text-rose-900 hover:bg-rose-100'
                                        : 'border-[#beedc0] bg-[#beedc0]/30 text-[#000000] hover:bg-[#beedc0]/50'
                                "
                            >
                                <ToggleLeft v-if="row.original.is_active" class="size-4" />
                                <ToggleRight v-else class="size-4" />
                                <span class="hidden sm:inline">{{
                                    row.original.is_active ? 'Nonaktifkan' : 'Aktifkan'
                                }}</span>
                            </button>

                            <!-- Hapus Akun (Hanya Ditampilkan untuk Akun Nonaktif) -->
                            <button
                                v-if="!row.original.is_active"
                                type="button"
                                @click="openDeleteModal(row.original)"
                                :aria-label="`Hapus Akun Pengguna ${row.original.name}`"
                                :title="`Hapus Akun ${row.original.name}`"
                                class="inline-flex min-h-[38px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-rose-300 bg-rose-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-rose-700 sm:px-3.5 shadow-xs"
                            >
                                <Trash2 class="size-3.5 text-white" />
                                <span class="hidden sm:inline">Hapus Akun</span>
                            </button>

                            <!-- Hapus Permanen Data Pasien (Hard Delete) -->
                            <button
                                v-if="isPatientUser(row.original)"
                                type="button"
                                @click="openForceDeleteModal(row.original)"
                                :aria-label="`Hapus Data Pasien ${row.original.name} Secara Permanen`"
                                :title="`Hapus Data Pasien ${row.original.name} Secara Permanen`"
                                class="inline-flex min-h-[38px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-red-300 bg-red-700 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-red-800 sm:px-3.5 shadow-xs"
                            >
                                <Trash2 class="size-3.5 text-white" />
                                <span class="hidden sm:inline">Hapus Permanen</span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Pagination Slot -->
                <template #pagination v-if="allUsersData.length > 0">
                    <Pagination
                        :table="table"
                        :pagination="users"
                        item-name="pengguna"
                    />
                </template>
            </ResponsiveTable>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Pendaftaran Dokter DPJP Baru (Mobile Bottom Sheet)
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="isDoctorModalOpen"
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#000000]/60 p-0 backdrop-blur-xs sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="doctor-modal-title"
        >
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex max-h-[88vh] w-full max-w-full flex-col space-y-4 overflow-hidden rounded-t-3xl border border-[#000000]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-2xl sm:rounded-3xl sm:p-8"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                        >
                            <Stethoscope class="size-5" />
                        </div>
                        <div>
                            <h2
                                id="doctor-modal-title"
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Pendaftaran Dokter DPJP
                            </h2>
                            <p
                                class="font-['Rubik'] text-[11px] text-[#333333] sm:text-xs"
                            >
                                Provisioning akun dokter dan penetapan jadwal
                                praktik
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isDoctorModalOpen = false"
                        aria-label="Tutup Dialog Pendaftaran Dokter"
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div
                    class="flex-1 space-y-4 overflow-y-auto pr-1 font-['Rubik'] text-xs sm:text-sm"
                >
                    <div
                        v-if="doctorGeneralError"
                        class="rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs font-semibold text-rose-800"
                    >
                        {{ doctorGeneralError }}
                    </div>
                    <!-- Nama & Email -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label
                                for="doctor-form-name"
                                class="mb-1 block font-medium text-[#000000]"
                                >Nama Lengkap & Gelar *</label
                            >
                            <input
                                id="doctor-form-name"
                                v-model="doctorForm.name"
                                type="text"
                                placeholder="Contoh: dr. Andrea Wijaya, Sp.PD"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            />
                            <p
                                v-if="doctorErrors.name"
                                class="mt-1 text-xs font-semibold text-rose-700"
                            >
                                {{ doctorErrors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="doctor-form-email"
                                class="mb-1 block font-medium text-[#000000]"
                                >Alamat Email Login *</label
                            >
                            <input
                                id="doctor-form-email"
                                v-model="doctorForm.email"
                                type="email"
                                placeholder="dokter@hospital.com"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            />
                            <p
                                v-if="doctorErrors.email"
                                class="mt-1 text-xs font-semibold text-rose-700"
                            >
                                {{ doctorErrors.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Spesialisasi & Nomor SIP -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label
                                for="doctor-form-specialization"
                                class="mb-1 block font-medium text-[#000000]"
                                >Bidang Spesialisasi *</label
                            >
                            <select
                                id="doctor-form-specialization"
                                v-model="doctorForm.specialization_id"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            >
                                <option
                                    v-for="sp in specializations"
                                    :key="sp.specialization_id"
                                    :value="String(sp.specialization_id)"
                                >
                                    {{ sp.name_specialization }}
                                </option>
                            </select>
                            <p
                                v-if="doctorErrors.specialization_id"
                                class="mt-1 text-xs font-semibold text-rose-700"
                            >
                                {{ doctorErrors.specialization_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="doctor-form-sip"
                                class="mb-1 block font-medium text-[#000000]"
                                >Nomor SIP (Surat Izin Praktik) *</label
                            >
                            <input
                                id="doctor-form-sip"
                                v-model="doctorForm.sip_number"
                                type="text"
                                placeholder="Contoh: SIP.503/123/2026"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            />
                            <p
                                v-if="doctorErrors.sip_number"
                                class="mt-1 text-xs font-semibold text-rose-700"
                            >
                                {{ doctorErrors.sip_number }}
                            </p>
                        </div>
                    </div>

                    <!-- Gender & No HP -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label
                                for="doctor-form-gender"
                                class="mb-1 block font-medium text-[#000000]"
                                >Jenis Kelamin *</label
                            >
                            <select
                                id="doctor-form-gender"
                                v-model="doctorForm.gender"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            >
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="doctor-form-phone"
                                class="mb-1 block font-medium text-[#000000]"
                                >No. WhatsApp / HP</label
                            >
                            <input
                                id="doctor-form-phone"
                                v-model="doctorForm.number_phone"
                                type="text"
                                placeholder="081234567890"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            />
                        </div>
                    </div>

                    <!-- Password Khusus / Default -->
                    <div>
                        <label
                            for="doctor-form-password"
                            class="mb-1 block font-medium text-[#000000]"
                            >Password Sementara (Default: Hospital2026!)</label
                        >
                        <input
                            id="doctor-form-password"
                            v-model="doctorForm.password"
                            type="text"
                            placeholder="Hospital2026!"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                    </div>

                    <!-- Inisialisasi Jadwal Praktik Opsional -->
                    <div class="space-y-3 border-t border-[#000000]/10 pt-3">
                        <label
                            for="doctor-form-create-schedule"
                            class="flex cursor-pointer items-center gap-2 font-medium text-[#000000]"
                        >
                            <input
                                id="doctor-form-create-schedule"
                                type="checkbox"
                                v-model="doctorForm.create_schedule"
                                class="size-4 rounded accent-[#000000]"
                            />
                            <span>Buat Jadwal Praktik Perdana Otomatis</span>
                        </label>

                        <div
                            v-if="doctorForm.create_schedule"
                            class="grid grid-cols-1 gap-3 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-4 sm:grid-cols-3"
                        >
                            <div>
                                <label
                                    for="doctor-form-poli"
                                    class="mb-1 block font-medium text-[#000000]"
                                    >Poliklinik</label
                                >
                                <select
                                    id="doctor-form-poli"
                                    v-model="doctorForm.poli_id"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-2 text-xs"
                                >
                                    <option
                                        v-for="p in polis"
                                        :key="p.poli_id"
                                        :value="String(p.poli_id)"
                                    >
                                        {{ p.name_poli }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="doctor-form-room"
                                    class="mb-1 block font-medium text-[#000000]"
                                    >Ruangan</label
                                >
                                <select
                                    id="doctor-form-room"
                                    v-model="doctorForm.room_id"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-2 text-xs"
                                >
                                    <option
                                        v-for="r in rooms"
                                        :key="r.room_id"
                                        :value="String(r.room_id)"
                                    >
                                        {{ r.name_room }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="doctor-form-day"
                                    class="mb-1 block font-medium text-[#000000]"
                                    >Hari Praktik</label
                                >
                                <select
                                    id="doctor-form-day"
                                    v-model="doctorForm.day"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-2 text-xs"
                                >
                                    <option
                                        v-for="d in [
                                            'Senin',
                                            'Selasa',
                                            'Rabu',
                                            'Kamis',
                                            'Jumat',
                                            'Sabtu',
                                            'Minggu',
                                        ]"
                                        :key="d"
                                        :value="d"
                                    >
                                        {{ d }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-[#000000]/10 pt-3"
                >
                    <button
                        type="button"
                        @click="isDoctorModalOpen = false"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2.5 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="handleCreateDoctor"
                        :disabled="isDoctorSubmitting"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a]"
                    >
                        <Loader2
                            v-if="isDoctorSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <CheckCircle2 v-else class="size-4 text-[#beedc0]" />
                        <span>Daftarkan Dokter DPJP</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Pendaftaran Staf / Perawat / Koas Baru
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="isNurseModalOpen"
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#000000]/60 p-0 backdrop-blur-xs sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="nurse-modal-title"
        >
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex max-h-[88vh] w-full max-w-full flex-col space-y-4 overflow-hidden rounded-t-3xl border border-[#000000]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-lg sm:rounded-3xl sm:p-8"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                        >
                            <UserPlus class="size-5" />
                        </div>
                        <div>
                            <h2
                                id="nurse-modal-title"
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Pendaftaran Staf / Koas
                            </h2>
                            <p
                                class="font-['Rubik'] text-[11px] text-[#333333] sm:text-xs"
                            >
                                Penetapan perawat tetap atau dokter muda magang
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isNurseModalOpen = false"
                        aria-label="Tutup Dialog Pendaftaran Staf"
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="flex-1 space-y-4 overflow-y-auto pr-1 font-['Rubik'] text-xs sm:text-sm"
                >
                    <div
                        v-if="nurseGeneralError"
                        class="rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs font-semibold text-rose-800"
                    >
                        {{ nurseGeneralError }}
                    </div>
                    <!-- Tipe Penugasan -->
                    <div>
                        <span class="mb-1 block font-medium text-[#000000]"
                            >Tipe Penugasan *</span
                        >
                        <div
                            class="grid grid-cols-2 gap-3"
                            role="radiogroup"
                            aria-label="Pilihan Tipe Penugasan Tenaga Medis"
                        >
                            <label
                                for="nurse-type-tetap"
                                :class="
                                    nurseForm.type === 'tetap'
                                        ? 'border-[#000000] bg-[#beedc0]/30 ring-1 ring-[#000000]'
                                        : 'border-[#000000]/15 bg-[#ffffff]'
                                "
                                class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded-xl border p-3"
                            >
                                <input
                                    id="nurse-type-tetap"
                                    type="radio"
                                    v-model="nurseForm.type"
                                    value="tetap"
                                    class="accent-[#000000]"
                                />
                                <div>
                                    <div
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        Perawat Tetap
                                    </div>
                                    <div class="text-[11px] text-[#333333]">
                                        Akses Kasir & POS
                                    </div>
                                </div>
                            </label>

                            <label
                                for="nurse-type-koas"
                                :class="
                                    nurseForm.type === 'koas'
                                        ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500'
                                        : 'border-[#000000]/15 bg-[#ffffff]'
                                "
                                class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded-xl border p-3"
                            >
                                <input
                                    id="nurse-type-koas"
                                    type="radio"
                                    v-model="nurseForm.type"
                                    value="koas"
                                    class="accent-amber-600"
                                />
                                <div>
                                    <div
                                        class="text-xs font-bold text-amber-900"
                                    >
                                        Dokter Muda
                                    </div>
                                    <div class="text-[11px] text-[#333333]">
                                        Logbook Klinis
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Nama & Email -->
                    <div>
                        <label
                            for="nurse-form-name"
                            class="mb-1 block font-medium text-[#000000]"
                            >Nama Lengkap *</label
                        >
                        <input
                            id="nurse-form-name"
                            v-model="nurseForm.name"
                            type="text"
                            placeholder="Nama tenaga medis"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="nurseErrors.name"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ nurseErrors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="nurse-form-email"
                            class="mb-1 block font-medium text-[#000000]"
                            >Alamat Email Login *</label
                        >
                        <input
                            id="nurse-form-email"
                            v-model="nurseForm.email"
                            type="email"
                            placeholder="staf@hospital.com"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="nurseErrors.email"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ nurseErrors.email }}
                        </p>
                    </div>

                    <!-- STR atau NIM & Jenis Kelamin -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <label
                                for="nurse-form-reg"
                                class="mb-1 block font-medium text-[#000000]"
                            >
                                {{
                                    nurseForm.type === 'koas'
                                        ? 'NIM Mahasiswa *'
                                        : 'Nomor STR Perawat'
                                }}
                            </label>
                            <input
                                id="nurse-form-reg"
                                v-model="nurseForm.registration_number"
                                type="text"
                                :placeholder="
                                    nurseForm.type === 'koas'
                                        ? 'NIM-FK-2026'
                                        : 'STR-NURSE-001'
                                "
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            />
                        </div>

                        <div>
                            <label
                                for="nurse-form-gender"
                                class="mb-1 block font-medium text-[#000000]"
                                >Jenis Kelamin *</label
                            >
                            <select
                                id="nurse-form-gender"
                                v-model="nurseForm.gender"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                            >
                                <option value="Perempuan">Perempuan</option>
                                <option value="Laki-laki">Laki-laki</option>
                            </select>
                        </div>
                    </div>

                    <!-- Institusi (Universitas untuk Koas) -->
                    <div v-if="nurseForm.type === 'koas'">
                        <label
                            for="nurse-form-institute"
                            class="mb-1 block font-medium text-[#000000]"
                            >Fakultas / Universitas Asal *</label
                        >
                        <input
                            id="nurse-form-institute"
                            v-model="nurseForm.institute"
                            type="text"
                            placeholder="Contoh: Fakultas Kedokteran Universitas Negeri"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="nurseErrors.institute"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ nurseErrors.institute }}
                        </p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-[#000000]/10 pt-3"
                >
                    <button
                        type="button"
                        @click="isNurseModalOpen = false"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2.5 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="handleCreateNurse"
                        :disabled="isNurseSubmitting"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a]"
                    >
                        <Loader2
                            v-if="isNurseSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <CheckCircle2 v-else class="size-4 text-[#beedc0]" />
                        <span>Daftarkan Akun</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Konfirmasi Toggle Status Aman
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="statusTargetUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="status-toggle-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full"
                        :class="
                            statusTargetUser.is_active
                                ? 'border border-rose-200 bg-rose-100 text-rose-900'
                                : 'border border-[#beedc0] bg-[#beedc0]/40 text-[#000000]'
                        "
                    >
                        <ShieldAlert
                            v-if="statusTargetUser.is_active"
                            class="size-5"
                        />
                        <ShieldCheck v-else class="size-5" />
                    </div>
                    <div>
                        <h2
                            id="status-toggle-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                        >
                            {{
                                statusTargetUser.is_active
                                    ? 'Nonaktifkan Akun Pengguna'
                                    : 'Aktifkan Kembali Akun'
                            }}
                        </h2>
                        <p class="font-['Rubik'] text-xs text-[#333333]">
                            {{ statusTargetUser.name }}
                        </p>
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-4 font-['Rubik'] text-xs text-[#000000]/80"
                >
                    <p v-if="statusTargetUser.is_active">
                        Menonaktifkan akun akan memblokir hak login pengguna.
                        Jika pengguna adalah <strong>Dokter DPJP</strong>,
                        seluruh jadwal praktiknya akan otomatis diliburkan.
                        Rekam medis dan tagihan pasien yang terhubung tetap
                        <strong>tersimpan aman</strong>.
                    </p>
                    <p v-else>
                        Mengaktifkan kembali akun akan memulihkan akses login
                        sistem dan status tenaga medis terkait.
                    </p>
                </div>

                <div
                    v-if="statusErrorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs font-semibold text-rose-800"
                >
                    {{ statusErrorMessage }}
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="statusTargetUser = null"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="confirmToggleStatus"
                        :disabled="isTogglingStatus"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] px-5 py-2 text-xs font-medium text-[#ffffff] shadow-none"
                        :class="
                            statusTargetUser.is_active
                                ? 'bg-rose-600 hover:bg-rose-700'
                                : 'bg-[#000000] hover:bg-[#1a1a1a]'
                        "
                    >
                        <Loader2
                            v-if="isTogglingStatus"
                            class="size-4 animate-spin text-[#ffffff]"
                        />
                        <span>{{
                            statusTargetUser.is_active
                                ? 'Ya, Nonaktifkan'
                                : 'Ya, Aktifkan'
                        }}</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Konfirmasi Hapus Akun Pengguna Nonaktif (Soft-Deletes)
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="deleteTargetUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-user-modal-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full border border-rose-200 bg-rose-100 text-rose-900"
                    >
                        <Trash2 class="size-5 text-rose-600" />
                    </div>
                    <div>
                        <h2
                            id="delete-user-modal-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                        >
                            Hapus Akun Pengguna
                        </h2>
                        <p class="font-['Rubik'] text-xs text-[#333333]">
                            {{ deleteTargetUser.name }} ({{ deleteTargetUser.email }})
                        </p>
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-2xl border border-rose-200 bg-rose-50/70 p-4 font-['Rubik'] text-xs text-rose-900"
                >
                    <p class="font-medium">
                        Akun ini akan dihapus dari direktori aktif. Seluruh data rekam medis historis akan tetap dipertahankan.
                    </p>
                    <p class="text-[11px] text-rose-700">
                        Penghapusan menggunakan metode <em>soft-delete</em> sehingga audit log dan relasi data klinis tidak hilang.
                    </p>
                </div>

                <div
                    v-if="deleteErrorMessage"
                    class="rounded-xl border border-rose-300 bg-rose-100 p-2.5 text-xs font-semibold text-rose-900"
                >
                    {{ deleteErrorMessage }}
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="deleteTargetUser = null"
                        :disabled="isDeletingUser"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2] disabled:opacity-50"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="confirmDeleteUser"
                        :disabled="isDeletingUser"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-rose-600 px-5 py-2 text-xs font-medium text-white shadow-none hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isDeletingUser"
                            class="size-4 animate-spin text-white"
                        />
                        <Trash2 v-else class="size-4 text-white" />
                        <span>{{ isDeletingUser ? 'Menghapus...' : 'Ya, Hapus Akun' }}</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Konfirmasi Hapus Data Pasien Secara Permanen (Hard Delete)
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="forceDeleteTargetUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/70 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="force-delete-patient-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-red-300 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-full border border-red-300 bg-red-100 text-red-700"
                    >
                        <AlertTriangle class="size-6 text-red-700" />
                    </div>
                    <div>
                        <h2
                            id="force-delete-patient-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-red-950 sm:text-lg"
                        >
                            Hapus Permanen Data Pasien
                        </h2>
                        <p class="font-['Rubik'] text-xs text-[#333333]">
                            {{ forceDeleteTargetUser.name }} ({{ forceDeleteTargetUser.email }})
                        </p>
                    </div>
                </div>

                <!-- Danger Warning Card -->
                <div
                    class="space-y-2 rounded-2xl border border-red-300 bg-red-50 p-4 font-['Rubik'] text-xs text-red-950"
                >
                    <p class="font-bold flex items-center gap-1.5 text-red-800">
                        <AlertCircle class="size-4 shrink-0 text-red-700" />
                        Tindakan Ini Tidak Dapat Dibatalkan!
                    </p>
                    <p class="text-[11px] leading-relaxed text-red-900/90">
                        Seluruh data pasien ini termasuk <strong>rekam medis, riwayat pemeriksaan, antrean poli, dan tagihan</strong> akan dihapus secara <strong>permanen dari basis data</strong> (Hard Delete).
                    </p>
                    <p v-if="forceDeleteTargetUser.patient?.resident_n" class="text-[11px] font-mono text-red-800 font-semibold">
                        NIK: {{ forceDeleteTargetUser.patient.resident_n }}
                    </p>
                </div>

                <!-- Checkbox Confirmation -->
                <label class="flex items-start gap-2.5 rounded-xl border border-[#000000]/10 bg-[#edede2]/40 p-3 text-xs font-medium text-[#000000] cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="confirmConsent"
                        class="mt-0.5 size-4 rounded border-gray-300 text-red-700 focus:ring-red-600"
                    />
                    <span>Saya memahami bahwa penghapusan ini bersifat permanen dan data yang dihapus tidak dapat dipulihkan.</span>
                </label>

                <div
                    v-if="forceDeleteErrorMessage"
                    class="rounded-xl border border-red-300 bg-red-100 p-2.5 text-xs font-semibold text-red-900"
                >
                    {{ forceDeleteErrorMessage }}
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="forceDeleteTargetUser = null"
                        :disabled="isForceDeleting"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2] disabled:opacity-50"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="confirmForceDeleteUser"
                        :disabled="isForceDeleting || !confirmConsent"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-red-700 px-5 py-2 text-xs font-semibold text-white shadow-none hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isForceDeleting"
                            class="size-4 animate-spin text-white"
                        />
                        <Trash2 v-else class="size-4 text-white" />
                        <span>{{ isForceDeleting ? 'Menghapus Permanen...' : 'Hapus Permanen Sekarang' }}</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Konfirmasi Reset Password
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="resetTargetUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-confirm-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full border border-[#beedc0] bg-[#beedc0]/40 text-[#000000]"
                    >
                        <KeyRound class="size-5 text-[#000000]" />
                    </div>
                    <div>
                        <h2
                            id="reset-confirm-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                        >
                            Konfirmasi Reset Password
                        </h2>
                        <p class="font-['Rubik'] text-xs text-[#333333]">
                            {{ resetTargetUser.name }}
                        </p>
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-4 font-['Rubik'] text-xs text-[#000000]/80"
                >
                    <p>
                        Apakah Anda yakin ingin mereset password untuk akun
                        <strong class="text-[#000000]">{{
                            resetTargetUser.email
                        }}</strong
                        >?
                    </p>
                    <p class="text-[#333333]">
                        Sistem akan menghasilkan
                        <strong>password sementara baru</strong>. Kredensial
                        baru akan ditampilkan setelah konfirmasi agar dapat
                        disalin langsung.
                    </p>
                    <div
                        v-if="resetErrorMessage"
                        class="mt-2 rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs font-semibold text-rose-800"
                    >
                        {{ resetErrorMessage }}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="resetTargetUser = null"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="confirmResetPassword"
                        :disabled="isResettingPassword"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a] disabled:opacity-60"
                    >
                        <Loader2
                            v-if="isResettingPassword"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <KeyRound v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            isResettingPassword
                                ? 'Mereset...'
                                : 'Ya, Reset Password'
                        }}</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Hasil Kredensial Password Baru
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="resetResultModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-pass-title"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                    >
                        <KeyRound class="size-5" />
                    </div>
                    <div>
                        <h2
                            id="reset-pass-title"
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                        >
                            Kredensial Password Baru
                        </h2>
                        <p class="font-['Rubik'] text-xs text-[#333333]">
                            {{ resetResultModal.name }} ({{
                                resetResultModal.email
                            }})
                        </p>
                    </div>
                </div>

                <div class="space-y-1 rounded-2xl bg-[#000000] p-4 text-center">
                    <div
                        class="text-[11px] font-medium tracking-wider text-[#beedc0] uppercase"
                    >
                        Password Akses SIMRS
                    </div>
                    <div
                        class="font-mono text-xl font-bold tracking-wider text-[#ffffff] select-all"
                    >
                        {{ resetResultModal.pass }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="copyToClipboard"
                        class="flex min-h-[44px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a]"
                    >
                        <Check v-if="copied" class="size-4 text-[#beedc0]" />
                        <Copy v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            copied ? 'Berhasil Disalin!' : 'Salin Password'
                        }}</span>
                    </button>

                    <button
                        type="button"
                        @click="resetResultModal = null"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Tutup
                    </button>
                </div>
            </motion.div>
        </div>
    </AdminLayout>
</template>
