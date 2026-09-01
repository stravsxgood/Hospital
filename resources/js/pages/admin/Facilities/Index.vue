<script setup lang="ts">
/**
 * @file Index.vue (Master Fasilitas Poliklinik, Ruangan & Jadwal Praktik DPJP)
 * @description Tata Kelola Master Poliklinik, Ruangan Periksa, dan Penjadwalan Praktik Harian DPJP dengan Kuota Antrean.
 *              100% Responsif untuk Mobile (<640px), Tablet/iPad (640-1024px), dan Desktop (>1024px).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    Building2,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    DoorClosed,
    Edit3,
    Layers,
    Loader2,
    MapPin,
    Plus,
    Search,
    ShieldCheck,
    Stethoscope,
    Trash2,
    Users,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface PoliItem {
    poli_id: number;
    kode_poli: string;
    name_poli: string;
    location: string;
    status: 'Aktif' | 'Nonaktif';
    schedules_count?: number;
    doctors_count?: number;
}

interface RoomItem {
    room_id: number;
    code_room: string;
    name_room: string;
    type_room: string;
    floor: number;
    schedules_count?: number;
}

interface DoctorScheduleItem {
    doctor_schedule_id: number;
    doctor_id: number;
    poli_id: number;
    room_id: number;
    day: string;
    start_time: string;
    end_time: string;
    quota_day: number;
    status: 'Aktif' | 'Libur';
    doctor?: {
        doctor_id: number;
        name: string;
        specialization?: { name_specialization: string };
    };
    poli?: { poli_id: number; name_poli: string; kode_poli: string };
    room?: { room_id: number; name_room: string; code_room: string };
}

interface DoctorOption {
    doctor_id: number;
    name: string;
}

const props = defineProps<{
    polis: PoliItem[];
    rooms: RoomItem[];
    schedules?: {
        data: DoctorScheduleItem[];
        total: number;
    };
    doctors?: DoctorOption[];
}>();

const activeTab = ref<'polis' | 'schedules' | 'rooms'>('polis');

// ═══════════════════════════════════════════════════════════════
// Poli CRUD State & Modal
// ═══════════════════════════════════════════════════════════════
const isPoliModalOpen = ref(false);
const isPoliSubmitting = ref(false);
const editingPoliId = ref<number | null>(null);
const poliErrors = ref<Record<string, string>>({});
const poliForm = ref({
    kode_poli: '',
    name_poli: '',
    location: '',
    status: 'Aktif',
});

const openCreatePoliModal = () => {
    editingPoliId.value = null;
    poliErrors.value = {};
    poliForm.value = {
        kode_poli: '',
        name_poli: '',
        location: '',
        status: 'Aktif',
    };
    isPoliModalOpen.value = true;
};

const openEditPoliModal = (poli: PoliItem) => {
    editingPoliId.value = poli.poli_id;
    poliErrors.value = {};
    poliForm.value = {
        kode_poli: poli.kode_poli,
        name_poli: poli.name_poli,
        location: poli.location,
        status: poli.status,
    };
    isPoliModalOpen.value = true;
};

const handleSavePoli = async () => {
    poliErrors.value = {};
    isPoliSubmitting.value = true;

    try {
        if (editingPoliId.value) {
            await axios.put(
                `/admin/polis/${editingPoliId.value}`,
                poliForm.value,
            );
        } else {
            await axios.post('/admin/polis', poliForm.value);
        }

        isPoliModalOpen.value = false;
        router.reload();
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            poliErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [
                    k,
                    (v as string[])[0],
                ]),
            );
        } else {
            alert(
                err.response?.data?.message ||
                    'Gagal menyimpan data Poliklinik.',
            );
        }
    } finally {
        isPoliSubmitting.value = false;
    }
};

const handleDeletePoli = async (poli: PoliItem) => {
    if (
        !confirm(
            `Hapus Poliklinik "${poli.name_poli}"? Tindakan ini aman jika tidak ada jadwal aktif.`,
        )
    ) {
        return;
    }

    try {
        await axios.delete(`/admin/polis/${poli.poli_id}`);
        router.reload();
    } catch (err: any) {
        alert(err.response?.data?.message || 'Gagal menghapus poliklinik.');
    }
};

// ═══════════════════════════════════════════════════════════════
// Schedule CRUD State & Modal
// ═══════════════════════════════════════════════════════════════
const isScheduleModalOpen = ref(false);
const isScheduleSubmitting = ref(false);
const editingScheduleId = ref<number | null>(null);
const scheduleErrors = ref<Record<string, string>>({});
const scheduleForm = ref({
    doctor_id: '',
    poli_id: '',
    room_id: '',
    day: 'Senin',
    start_time: '08:00',
    end_time: '14:00',
    quota_day: 20,
    status: 'Aktif',
});

const openCreateScheduleModal = () => {
    editingScheduleId.value = null;
    scheduleErrors.value = {};
    scheduleForm.value = {
        doctor_id: props.doctors?.[0]?.doctor_id
            ? String(props.doctors[0].doctor_id)
            : '',
        poli_id: props.polis[0]?.poli_id ? String(props.polis[0].poli_id) : '',
        room_id: props.rooms[0]?.room_id ? String(props.rooms[0].room_id) : '',
        day: 'Senin',
        start_time: '08:00',
        end_time: '14:00',
        quota_day: 20,
        status: 'Aktif',
    };
    isScheduleModalOpen.value = true;
};

const openEditScheduleModal = (s: DoctorScheduleItem) => {
    editingScheduleId.value = s.doctor_schedule_id;
    scheduleErrors.value = {};
    scheduleForm.value = {
        doctor_id: String(s.doctor_id),
        poli_id: String(s.poli_id),
        room_id: String(s.room_id),
        day: s.day,
        start_time: s.start_time.substring(0, 5),
        end_time: s.end_time.substring(0, 5),
        quota_day: s.quota_day,
        status: s.status,
    };
    isScheduleModalOpen.value = true;
};

const handleSaveSchedule = async () => {
    scheduleErrors.value = {};
    isScheduleSubmitting.value = true;

    try {
        if (editingScheduleId.value) {
            await axios.put(
                `/admin/schedules/${editingScheduleId.value}`,
                scheduleForm.value,
            );
        } else {
            await axios.post('/admin/schedules', scheduleForm.value);
        }

        isScheduleModalOpen.value = false;
        router.reload();
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            scheduleErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [
                    k,
                    (v as string[])[0],
                ]),
            );
        } else {
            alert(
                err.response?.data?.message ||
                    'Gagal menyimpan jadwal praktik.',
            );
        }
    } finally {
        isScheduleSubmitting.value = false;
    }
};

const handleDeleteSchedule = async (s: DoctorScheduleItem) => {
    if (!confirm(`Hapus jadwal praktik ${s.doctor?.name} (${s.day})?`)) {
        return;
    }

    try {
        await axios.delete(`/admin/schedules/${s.doctor_schedule_id}`);
        router.reload();
    } catch (err: any) {
        alert(err.response?.data?.message || 'Gagal menghapus jadwal praktik.');
    }
};
</script>

<template>
    <AdminLayout
        title="Master Fasilitas & Jadwal Praktik - Super Admin"
        :breadcrumbs="[{ title: 'Fasilitas & Jadwal', href: '/admin/polis' }]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header & Actions (Mobile-first responsive)
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
                            <Building2 class="size-3.5 text-[#000000]" />
                            <span>Master Fasilitas & Operasional</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Tata Kelola Poliklinik & Jadwal DPJP
                    </h1>
                    <p class="font-['Rubik'] text-xs text-[#333333] sm:text-sm">
                        Konfigurasi unit layanan poliklinik, pemetaan ruangan
                        periksa, dan alokasi kuota antrean praktik dokter.
                    </p>
                </div>

                <div
                    class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center"
                >
                    <button
                        v-if="activeTab === 'polis'"
                        type="button"
                        @click="openCreatePoliModal"
                        class="inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 font-['Rubik'] text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a] sm:text-sm"
                    >
                        <Plus class="size-4 text-[#beedc0]" />
                        <span>Tambah Poliklinik</span>
                    </button>

                    <button
                        v-else-if="activeTab === 'schedules'"
                        type="button"
                        @click="openCreateScheduleModal"
                        class="inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 font-['Rubik'] text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a] sm:text-sm"
                    >
                        <Plus class="size-4 text-[#beedc0]" />
                        <span>Tambah Jadwal DPJP</span>
                    </button>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Navigation Tabs (Horizontal Scrollable)
                 ═══════════════════════════════════════════════════════════════ -->
            <div
                class="flex scrollbar-none items-center gap-2 overflow-x-auto border-b border-[#000000]/10 pb-2 font-['Rubik']"
                role="tablist"
                aria-label="Navigasi Kategori Fasilitas"
            >
                <button
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'polis'"
                    @click="activeTab = 'polis'"
                    class="flex min-h-[42px] shrink-0 cursor-pointer items-center gap-2 rounded-full px-5 py-2 text-xs font-medium transition-all"
                    :class="
                        activeTab === 'polis'
                            ? 'bg-[#000000] text-[#ffffff] shadow-none'
                            : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                    "
                >
                    <Building2 class="size-4" />
                    <span>Unit Poliklinik ({{ polis.length }})</span>
                </button>

                <button
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'schedules'"
                    @click="activeTab = 'schedules'"
                    class="flex min-h-[42px] shrink-0 cursor-pointer items-center gap-2 rounded-full px-5 py-2 text-xs font-medium transition-all"
                    :class="
                        activeTab === 'schedules'
                            ? 'bg-[#000000] text-[#ffffff] shadow-none'
                            : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                    "
                >
                    <Calendar class="size-4" />
                    <span>Jadwal Praktik DPJP</span>
                </button>

                <button
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'rooms'"
                    @click="activeTab = 'rooms'"
                    class="flex min-h-[42px] shrink-0 cursor-pointer items-center gap-2 rounded-full px-5 py-2 text-xs font-medium transition-all"
                    :class="
                        activeTab === 'rooms'
                            ? 'bg-[#000000] text-[#ffffff] shadow-none'
                            : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                    "
                >
                    <DoorClosed class="size-4" />
                    <span>Ruangan Periksa ({{ rooms.length }})</span>
                </button>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Tab Content: Poliklinik Master Table
                 ═══════════════════════════════════════════════════════════════ -->
            <section
                v-if="activeTab === 'polis'"
                aria-labelledby="polis-table-heading"
                class="overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#fffff3] font-['Rubik'] shadow-none"
            >
                <h2 id="polis-table-heading" class="sr-only">
                    Tabel Data Unit Poliklinik
                </h2>
                <div class="scrollbar-thin overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead
                            class="border-b border-[#000000]/10 bg-[#edede2]/80 text-[11px] font-bold tracking-wider text-[#000000] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3.5 sm:px-6">
                                    Kode & Nama Poliklinik
                                </th>
                                <th class="px-4 py-3.5">
                                    Lokasi Gedung / Lantai
                                </th>
                                <th class="px-4 py-3.5 text-center">
                                    Jadwal Terhubung
                                </th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-right sm:px-6">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-[#000000]/5 font-medium text-[#000000]"
                        >
                            <tr
                                v-for="poli in polis"
                                :key="poli.poli_id"
                                class="transition-colors hover:bg-[#edede2]/30"
                            >
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="font-bold text-[#000000]">
                                        {{ poli.name_poli }}
                                    </div>
                                    <div
                                        class="font-mono text-xs text-[#333333]"
                                    >
                                        {{ poli.kode_poli }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-[#333333]">
                                    <div class="flex items-center gap-1.5">
                                        <MapPin
                                            class="size-3.5 shrink-0 text-[#000000]"
                                        />
                                        <span>{{ poli.location }}</span>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3.5 text-center font-mono text-xs font-bold text-[#000000]"
                                >
                                    {{ poli.schedules_count ?? 0 }} Jadwal
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span
                                        :class="
                                            poli.status === 'Aktif'
                                                ? 'border border-[#beedc0] bg-[#beedc0]/40 text-[#000000]'
                                                : 'border border-[#000000]/10 bg-[#edede2] text-[#333333]'
                                        "
                                        class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-bold"
                                    >
                                        {{ poli.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right sm:px-6">
                                    <div
                                        class="flex items-center justify-end gap-1.5 sm:gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="openEditPoliModal(poli)"
                                            :aria-label="`Edit Poliklinik ${poli.name_poli}`"
                                            :title="`Edit ${poli.name_poli}`"
                                            class="flex min-h-[38px] min-w-[38px] cursor-pointer items-center justify-center rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]"
                                        >
                                            <Edit3
                                                class="size-4 text-[#000000]"
                                            />
                                        </button>
                                        <button
                                            type="button"
                                            @click="handleDeletePoli(poli)"
                                            :aria-label="`Hapus Poliklinik ${poli.name_poli}`"
                                            :title="`Hapus ${poli.name_poli}`"
                                            class="flex min-h-[38px] min-w-[38px] cursor-pointer items-center justify-center rounded-[40.5px] border border-rose-200 bg-rose-50 text-rose-900 hover:bg-rose-100"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. Tab Content: Jadwal Praktik DPJP
                 ═══════════════════════════════════════════════════════════════ -->
            <section
                v-else-if="activeTab === 'schedules'"
                aria-labelledby="schedules-table-heading"
                class="overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#fffff3] font-['Rubik'] shadow-none"
            >
                <h2 id="schedules-table-heading" class="sr-only">
                    Tabel Data Jadwal Praktik Dokter DPJP
                </h2>
                <div class="scrollbar-thin overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead
                            class="border-b border-[#000000]/10 bg-[#edede2]/80 text-[11px] font-bold tracking-wider text-[#000000] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3.5 sm:px-6">Dokter DPJP</th>
                                <th class="px-4 py-3.5">Poliklinik & Ruang</th>
                                <th class="px-4 py-3.5">Hari & Jam Praktik</th>
                                <th class="px-4 py-3.5 text-center">
                                    Kuota Harian
                                </th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-right sm:px-6">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-[#000000]/5 font-medium text-[#000000]"
                        >
                            <tr
                                v-for="s in schedules?.data || []"
                                :key="s.doctor_schedule_id"
                                class="transition-colors hover:bg-[#edede2]/30"
                            >
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="font-bold text-[#000000]">
                                        {{ s.doctor?.name || '-' }}
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-[#333333]"
                                    >
                                        {{
                                            s.doctor?.specialization
                                                ?.name_specialization ||
                                            'Spesialis'
                                        }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <div class="font-bold text-[#000000]">
                                        {{ s.poli?.name_poli || '-' }}
                                    </div>
                                    <div class="text-[#333333]">
                                        {{ s.room?.name_room || '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <div class="font-bold text-[#000000]">
                                        {{ s.day }}
                                    </div>
                                    <div class="font-mono text-[#333333]">
                                        {{ s.start_time.substring(0, 5) }} -
                                        {{ s.end_time.substring(0, 5) }} WIB
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3.5 text-center font-mono text-xs font-bold text-[#000000]"
                                >
                                    {{ s.quota_day }} Pasien
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span
                                        :class="
                                            s.status === 'Aktif'
                                                ? 'border border-[#beedc0] bg-[#beedc0]/40 text-[#000000]'
                                                : 'border border-rose-200 bg-rose-100/70 text-rose-900'
                                        "
                                        class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-bold"
                                    >
                                        {{ s.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right sm:px-6">
                                    <div
                                        class="flex items-center justify-end gap-1.5 sm:gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="openEditScheduleModal(s)"
                                            :aria-label="`Edit Jadwal Praktik ${s.doctor?.name} hari ${s.day}`"
                                            :title="`Edit Jadwal ${s.doctor?.name}`"
                                            class="flex min-h-[38px] min-w-[38px] cursor-pointer items-center justify-center rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]"
                                        >
                                            <Edit3
                                                class="size-4 text-[#000000]"
                                            />
                                        </button>
                                        <button
                                            type="button"
                                            @click="handleDeleteSchedule(s)"
                                            :aria-label="`Hapus Jadwal Praktik ${s.doctor?.name} hari ${s.day}`"
                                            :title="`Hapus Jadwal ${s.doctor?.name}`"
                                            class="flex min-h-[38px] min-w-[38px] cursor-pointer items-center justify-center rounded-[40.5px] border border-rose-200 bg-rose-50 text-rose-900 hover:bg-rose-100"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    !schedules?.data ||
                                    schedules.data.length === 0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="py-10 text-center font-medium text-[#333333]"
                                >
                                    Belum ada jadwal praktik dokter yang
                                    terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 5. Tab Content: Ruangan Periksa Master Table
                 ═══════════════════════════════════════════════════════════════ -->
            <section
                v-else-if="activeTab === 'rooms'"
                aria-labelledby="rooms-table-heading"
                class="overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#fffff3] font-['Rubik'] shadow-none"
            >
                <h2 id="rooms-table-heading" class="sr-only">
                    Tabel Data Ruangan Periksa
                </h2>
                <div class="scrollbar-thin overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead
                            class="border-b border-[#000000]/10 bg-[#edede2]/80 text-[11px] font-bold tracking-wider text-[#000000] uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3.5 sm:px-6">
                                    Kode & Nama Ruangan
                                </th>
                                <th class="px-4 py-3.5">Tipe Ruangan</th>
                                <th class="px-4 py-3.5 text-center">
                                    Lantai Gedung
                                </th>
                                <th class="px-4 py-3.5 text-center">
                                    Jadwal Praktik Terhubung
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-[#000000]/5 font-medium text-[#000000]"
                        >
                            <tr
                                v-for="r in rooms"
                                :key="r.room_id"
                                class="transition-colors hover:bg-[#edede2]/30"
                            >
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="font-bold text-[#000000]">
                                        {{ r.name_room }}
                                    </div>
                                    <div
                                        class="font-mono text-xs text-[#333333]"
                                    >
                                        {{ r.code_room }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3.5 text-xs font-medium text-[#333333]"
                                >
                                    {{ r.type_room }}
                                </td>
                                <td
                                    class="px-4 py-3.5 text-center font-mono text-xs font-bold text-[#000000]"
                                >
                                    Lantai {{ r.floor }}
                                </td>
                                <td
                                    class="px-4 py-3.5 text-center font-mono text-xs font-bold text-[#000000]"
                                >
                                    {{ r.schedules_count ?? 0 }} Jadwal
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Tambah / Edit Poliklinik (Mobile Bottom Sheet)
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="isPoliModalOpen"
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#000000]/60 p-0 backdrop-blur-xs sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="poli-modal-title"
        >
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex max-h-[88vh] w-full max-w-full flex-col space-y-4 overflow-hidden rounded-t-3xl border border-[#000000]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-md sm:rounded-3xl sm:p-7"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <h2
                        id="poli-modal-title"
                        class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                    >
                        {{
                            editingPoliId
                                ? 'Edit Data Poliklinik'
                                : 'Tambah Poliklinik Baru'
                        }}
                    </h2>
                    <button
                        type="button"
                        @click="isPoliModalOpen = false"
                        aria-label="Tutup Dialog Poliklinik"
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="flex-1 space-y-3.5 overflow-y-auto pr-1 font-['Rubik'] text-xs sm:text-sm"
                >
                    <div>
                        <label
                            for="poli-form-code"
                            class="mb-1 block font-medium text-[#000000]"
                            >Kode Poliklinik *</label
                        >
                        <input
                            id="poli-form-code"
                            v-model="poliForm.kode_poli"
                            type="text"
                            placeholder="Contoh: POL-GIGI"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="poliErrors.kode_poli"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ poliErrors.kode_poli }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="poli-form-name"
                            class="mb-1 block font-medium text-[#000000]"
                            >Nama Poliklinik *</label
                        >
                        <input
                            id="poli-form-name"
                            v-model="poliForm.name_poli"
                            type="text"
                            placeholder="Contoh: Poli Gigi & Mulut"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="poliErrors.name_poli"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ poliErrors.name_poli }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="poli-form-location"
                            class="mb-1 block font-medium text-[#000000]"
                            >Lokasi Gedung / Lantai *</label
                        >
                        <input
                            id="poli-form-location"
                            v-model="poliForm.location"
                            type="text"
                            placeholder="Contoh: Lantai 2 Gedung B"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        />
                        <p
                            v-if="poliErrors.location"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ poliErrors.location }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="poli-form-status"
                            class="mb-1 block font-medium text-[#000000]"
                            >Status Operasional *</label
                        >
                        <select
                            id="poli-form-status"
                            v-model="poliForm.status"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus:border-[#000000] focus:outline-none sm:text-xs"
                        >
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-[#000000]/10 pt-3"
                >
                    <button
                        type="button"
                        @click="isPoliModalOpen = false"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleSavePoli"
                        :disabled="isPoliSubmitting"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-medium text-[#ffffff] hover:bg-[#1a1a1a]"
                    >
                        <Loader2
                            v-if="isPoliSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <span>Simpan Data</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             Modal Tambah / Edit Jadwal Praktik DPJP (Mobile Bottom Sheet)
             ═══════════════════════════════════════════════════════════════ -->
        <div
            v-if="isScheduleModalOpen"
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#000000]/60 p-0 backdrop-blur-xs sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="schedule-modal-title"
        >
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex max-h-[88vh] w-full max-w-full flex-col space-y-4 overflow-hidden rounded-t-3xl border border-[#000000]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-lg sm:rounded-3xl sm:p-8"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <h2
                        id="schedule-modal-title"
                        class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                    >
                        {{
                            editingScheduleId
                                ? 'Edit Jadwal Praktik DPJP'
                                : 'Tambah Jadwal Praktik DPJP Baru'
                        }}
                    </h2>
                    <button
                        type="button"
                        @click="isScheduleModalOpen = false"
                        aria-label="Tutup Dialog Jadwal"
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="flex-1 space-y-3.5 overflow-y-auto pr-1 font-['Rubik'] text-xs sm:text-sm"
                >
                    <div>
                        <label
                            for="schedule-form-doctor"
                            class="mb-1 block font-medium text-[#000000]"
                            >Dokter DPJP *</label
                        >
                        <select
                            id="schedule-form-doctor"
                            v-model="scheduleForm.doctor_id"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
                        >
                            <option
                                v-for="doc in doctors"
                                :key="doc.doctor_id"
                                :value="String(doc.doctor_id)"
                            >
                                {{ doc.name }}
                            </option>
                        </select>
                        <p
                            v-if="scheduleErrors.doctor_id"
                            class="mt-1 text-xs font-semibold text-rose-700"
                        >
                            {{ scheduleErrors.doctor_id }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                for="schedule-form-poli"
                                class="mb-1 block font-medium text-[#000000]"
                                >Poliklinik *</label
                            >
                            <select
                                id="schedule-form-poli"
                                v-model="scheduleForm.poli_id"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
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
                                for="schedule-form-room"
                                class="mb-1 block font-medium text-[#000000]"
                                >Ruangan *</label
                            >
                            <select
                                id="schedule-form-room"
                                v-model="scheduleForm.room_id"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
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
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label
                                for="schedule-form-day"
                                class="mb-1 block font-medium text-[#000000]"
                                >Hari *</label
                            >
                            <select
                                id="schedule-form-day"
                                v-model="scheduleForm.day"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
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
                        <div>
                            <label
                                for="schedule-form-start-time"
                                class="mb-1 block font-medium text-[#000000]"
                                >Mulai *</label
                            >
                            <input
                                id="schedule-form-start-time"
                                v-model="scheduleForm.start_time"
                                type="time"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-2 text-base text-[#000000] sm:text-xs"
                            />
                        </div>
                        <div>
                            <label
                                for="schedule-form-end-time"
                                class="mb-1 block font-medium text-[#000000]"
                                >Selesai *</label
                            >
                            <input
                                id="schedule-form-end-time"
                                v-model="scheduleForm.end_time"
                                type="time"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-2 text-base text-[#000000] sm:text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                for="schedule-form-quota"
                                class="mb-1 block font-medium text-[#000000]"
                                >Kuota Pasien / Hari *</label
                            >
                            <input
                                id="schedule-form-quota"
                                v-model.number="scheduleForm.quota_day"
                                type="number"
                                min="1"
                                max="100"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
                            />
                        </div>
                        <div>
                            <label
                                for="schedule-form-status"
                                class="mb-1 block font-medium text-[#000000]"
                                >Status Praktik *</label
                            >
                            <select
                                id="schedule-form-status"
                                v-model="scheduleForm.status"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] sm:text-xs"
                            >
                                <option value="Aktif">Aktif</option>
                                <option value="Libur">Libur</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-[#000000]/10 pt-3"
                >
                    <button
                        type="button"
                        @click="isScheduleModalOpen = false"
                        class="min-h-[44px] rounded-[40.5px] border border-[#000000]/15 px-5 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleSaveSchedule"
                        :disabled="isScheduleSubmitting"
                        class="flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-medium text-[#ffffff] shadow-none hover:bg-[#1a1a1a]"
                    >
                        <Loader2
                            v-if="isScheduleSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <span>Simpan Jadwal</span>
                    </button>
                </div>
            </motion.div>
        </div>
    </AdminLayout>
</template>
