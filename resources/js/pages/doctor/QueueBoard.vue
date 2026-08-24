<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    Bell,
    BellRing,
    Calendar,
    CheckCircle2,
    Clock,
    FileText,
    Forward,
    LayoutGrid,
    Monitor,
    Radio,
    RotateCw,
    Sparkles,
    Stethoscope,
    ToggleLeft,
    ToggleRight,
    User,
    UserCheck,
    Users,
    Volume2,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ConsultationModal from '@/components/ConsultationModal.vue';

interface Patient {
    patient_id: number;
    name: string;
    resident_n?: string;
    gender?: string;
    number_phone?: string;
}

interface Appointment {
    appointment_id: number;
    queue_number: string;
    appointment_date: string;
    complaint: string | null;
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled';
    patient?: Patient;
}

interface DoctorSchedule {
    doctor_schedule_id: number;
    start_time: string;
    end_time: string;
    day: string;
    quota_day?: number;
    doctor?: {
        name: string;
        specialization?: { name_specialization?: string };
    };
    poli?: { name_poli?: string; name?: string };
    room?: { name_room?: string };
}

interface AvailableDate {
    date: string;
    label: string;
    count: number;
    is_today: boolean;
}

const props = defineProps<{
    schedules: DoctorSchedule[];
    allSchedules?: DoctorSchedule[];
    selectedSchedule: DoctorSchedule | null;
    appointments: Appointment[];
    availableDates: AvailableDate[];
    selectedDate: string;
    todayDate: string;
    currentDate: string;
    isDoctor?: boolean;
    doctorName?: string;
}>();

const isAutoNext = ref(false);
const isCallingAction = ref(false);
const isConsultationModalOpen = ref(false);
const activeConsultationAppointment = ref<any>(null);

const openConsultationModal = (appointment: any) => {
    activeConsultationAppointment.value = {
        ...appointment,
        doctorSchedule: props.selectedSchedule,
    };
    isConsultationModalOpen.value = true;
};

const handleConsultationSuccess = () => {
    router.reload();
};

// Pasien yang sedang aktif diperiksa di dalam ruangan
const currentCalling = computed(() => {
    return props.appointments.find((item) => item.status === 'in_progress');
});

// Pasien yang masih menunggu panggilan
const waitingList = computed(() => {
    return props.appointments.filter((item) =>
        ['pending', 'confirmed'].includes(item.status),
    );
});

// Pasien yang sudah selesai diperiksa
const completedList = computed(() => {
    return props.appointments.filter((item) => item.status === 'completed');
});

const handleScheduleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    router.get(
        '/doctor/queue',
        {
            schedule_id: target.value,
            date: props.selectedDate,
        },
        { preserveState: true },
    );
};

const handleDateSelect = (dateStr: string) => {
    router.get(
        '/doctor/queue',
        {
            schedule_id: props.selectedSchedule?.doctor_schedule_id,
            date: dateStr,
        },
        { preserveState: true },
    );
};

const handleCustomDateChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.value) {
        handleDateSelect(target.value);
    }
};

// Panggil pasien (atau panggil ulang)
const handleCall = (appointmentId: number) => {
    isCallingAction.value = true;
    router.patch(
        `/doctor/queue/${appointmentId}/call`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                setTimeout(() => {
                    isCallingAction.value = false;
                }, 1000);
            },
        },
    );
};

// Panggil pasien terdepan dari daftar tunggu
const handleCallNext = () => {
    if (waitingList.value.length > 0) {
        handleCall(waitingList.value[0].appointment_id);
    }
};

// Selesaikan konsultasi pasien
const handleComplete = (appointmentId: number) => {
    const nextPatient = waitingList.value[0];
    router.patch(
        `/doctor/queue/${appointmentId}/complete`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Jika mode otomatis aktif dan ada antrean berikutnya, langsung panggil otomatis
                if (isAutoNext.value && nextPatient) {
                    setTimeout(() => {
                        handleCall(nextPatient.appointment_id);
                    }, 800);
                }
            },
        },
    );
};

const handleSkip = (appointmentId: number) => {
    router.patch(
        `/doctor/queue/${appointmentId}/skip`,
        {},
        { preserveScroll: true },
    );
};

const toggleAutoNext = () => {
    isAutoNext.value = !isAutoNext.value;
    localStorage.setItem(
        'doctor_auto_next',
        isAutoNext.value ? 'true' : 'false',
    );
};

const formatDateLabel = (dateStr: string): string => {
    if (!dateStr) {
        return '-';
    }

    const clean = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
    const parts = clean.split('-');

    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    return dateStr;
};

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

const handleGlobalKeydown = (e: KeyboardEvent) => {
    // F2: Panggil antrean berikutnya / panggil ulang antrean aktif
    if (e.key === 'F2') {
        e.preventDefault();

        if (currentCalling.value) {
            handleCall(currentCalling.value.appointment_id);
        } else if (waitingList.value.length > 0) {
            handleCall(waitingList.value[0].appointment_id);
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown);
    const savedAutoNext = localStorage.getItem('doctor_auto_next');

    if (savedAutoNext === 'true') {
        isAutoNext.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>

<template>
    <Head title="Papan Antrean Dokter - Hospital Population" />

    <div
        class="min-h-screen w-full space-y-6 bg-[#edede2] p-4 font-['Rubik'] text-[#000000] sm:p-6 lg:p-8"
    >
        <!-- Top Bar Header -->
        <header
            class="sticky top-0 z-30 -mt-4 mb-6 border-b border-[#333333]/10 bg-[#edede2]/90 py-3 backdrop-blur-md sm:-mt-6 lg:-mt-8"
        >
            <div
                class="mx-auto flex max-w-[1350px] flex-col justify-between gap-4 px-2 sm:px-4 md:flex-row md:items-center"
            >
                <div class="flex items-center gap-3">
                    <Link
                        href="/staff"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-[#333333]/15 bg-[#fffff3] text-[#000000] transition-colors hover:bg-[#beedc0]"
                        title="Kembali ke Dashboard Staf"
                    >
                        <ArrowLeft class="size-5" />
                    </Link>
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#beedc0]"
                    >
                        <AppLogoIcon
                            class="size-7 fill-current text-[#000000]"
                        />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="block font-['ivypresto-headline'] text-xl leading-tight font-bold tracking-tight text-[#000000]"
                            >
                                Panel Panggilan Antrean
                            </span>
                            <span
                                v-if="isDoctor"
                                class="inline-flex items-center gap-1 rounded-full border border-[#333333]/15 bg-[#beedc0] px-2.5 py-0.5 text-[11px] font-semibold text-[#000000]"
                            >
                                <Stethoscope class="size-3" />
                                Dokter Praktik
                            </span>
                        </div>
                        <span
                            class="block text-xs tracking-wide text-[#333333]"
                        >
                            {{ currentDate }}
                        </span>
                    </div>
                </div>

                <!-- Kontrol Navigasi & Pilihan Jadwal -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Toggle Panggil Otomatis -->
                    <button
                        type="button"
                        @click="toggleAutoNext"
                        class="inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-[40.5px] border px-3.5 py-1.5 text-xs font-semibold shadow-xs transition-all"
                        :class="
                            isAutoNext
                                ? 'border-[#333333]/30 bg-[#beedc0] text-[#000000]'
                                : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'
                        "
                        title="Setelah selesai konsultasi, sistem langsung memanggil pasien antrean berikutnya"
                    >
                        <component
                            :is="isAutoNext ? ToggleRight : ToggleLeft"
                            class="size-4"
                        />
                        <span
                            >Otomatis Panggil Berikutnya:
                            <strong>{{
                                isAutoNext ? 'ON' : 'OFF'
                            }}</strong></span
                        >
                    </button>

                    <Link
                        href="/staff"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                    >
                        <LayoutGrid class="size-3.5" />
                        <span>Dashboard Staf</span>
                    </Link>

                    <Link
                        href="/display"
                        target="_blank"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                    >
                        <Monitor class="size-3.5" />
                        <span>Layar TV Antrean</span>
                    </Link>

                    <!-- Dropdown Pilih Jadwal Dokter -->
                    <div class="flex items-center gap-2">
                        <select
                            :value="selectedSchedule?.doctor_schedule_id"
                            @change="handleScheduleChange"
                            class="min-h-[40px] max-w-[280px] truncate rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-xs font-medium text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none sm:max-w-[320px]"
                        >
                            <option
                                v-for="sch in schedules"
                                :key="sch.doctor_schedule_id"
                                :value="sch.doctor_schedule_id"
                            >
                                {{ formatDoctorName(sch.doctor?.name) }} -
                                {{ sch.poli?.name_poli || sch.poli?.name }}
                                (Hari {{ sch.day }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="mx-auto max-w-[1350px] space-y-6">
            <!-- Filter Tanggal & Ringkasan Jadwal Terpilih -->
            <div
                class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5 shadow-sm"
            >
                <div
                    class="flex flex-col justify-between gap-4 border-b border-[#333333]/10 pb-4 lg:flex-row lg:items-center"
                >
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                            >
                                {{
                                    selectedSchedule?.poli?.name_poli ||
                                    selectedSchedule?.poli?.name ||
                                    'Poliklinik'
                                }}
                            </span>
                            <span
                                class="rounded-full border border-[#333333]/15 bg-[#ffffff] px-2.5 py-0.5 text-xs font-medium text-[#333333]"
                            >
                                {{
                                    selectedSchedule?.room?.name_room ||
                                    'Ruang Periksa'
                                }}
                            </span>
                            <span
                                class="rounded-full border border-[#333333]/15 bg-[#ffffff] px-2.5 py-0.5 text-xs font-medium text-[#333333]"
                            >
                                Kuota:
                                {{ selectedSchedule?.quota_day ?? 30 }} Pasien
                            </span>
                        </div>
                        <h2
                            class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000]"
                        >
                            {{
                                formatDoctorName(selectedSchedule?.doctor?.name)
                            }}
                        </h2>
                        <p class="text-xs text-[#333333]">
                            {{
                                selectedSchedule?.doctor?.specialization
                                    ?.name_specialization || 'Spesialis'
                            }}
                            · Praktik Hari {{ selectedSchedule?.day }} ({{
                                selectedSchedule?.start_time.substring(0, 5)
                            }}
                            -
                            {{ selectedSchedule?.end_time.substring(0, 5) }}
                            WIB)
                        </p>
                    </div>

                    <!-- Filter Tanggal Antrean Cerdas -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="mr-1 text-xs font-semibold text-[#333333]"
                            >Filter Tanggal:</span
                        >

                        <!-- Tombol Hari Ini -->
                        <button
                            type="button"
                            @click="handleDateSelect(todayDate)"
                            class="min-h-[36px] rounded-[40.5px] border px-3.5 py-1.5 text-xs font-semibold transition-colors"
                            :class="
                                selectedDate === todayDate
                                    ? 'border-[#000000] bg-[#000000] text-white'
                                    : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            Hari Ini
                        </button>

                        <!-- Tab Tanggal yang Memiliki Reservasi Aktif -->
                        <button
                            v-for="d in availableDates.filter(
                                (item) => item.date !== todayDate,
                            )"
                            :key="d.date"
                            type="button"
                            @click="handleDateSelect(d.date)"
                            class="inline-flex min-h-[36px] items-center gap-1.5 rounded-[40.5px] border px-3.5 py-1.5 text-xs font-semibold transition-colors"
                            :class="
                                selectedDate === d.date
                                    ? 'border-[#000000] bg-[#000000] text-white'
                                    : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            <span>{{ d.label }}</span>
                            <span
                                class="py-0.2 rounded-full px-1.5 text-[10px]"
                                :class="
                                    selectedDate === d.date
                                        ? 'bg-[#beedc0] text-[#000000]'
                                        : 'bg-[#edede2] text-[#000000]'
                                "
                            >
                                {{ d.count }}
                            </span>
                        </button>

                        <!-- Tombol Semua Antrean -->
                        <button
                            type="button"
                            @click="handleDateSelect('all')"
                            class="min-h-[36px] rounded-[40.5px] border px-3.5 py-1.5 text-xs font-semibold transition-colors"
                            :class="
                                selectedDate === 'all'
                                    ? 'border-[#000000] bg-[#000000] text-white'
                                    : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            Semua Tanggal
                        </button>

                        <!-- Input Kalender Kustom -->
                        <input
                            type="date"
                            :value="selectedDate !== 'all' ? selectedDate : ''"
                            @change="handleCustomDateChange"
                            class="min-h-[36px] rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                            title="Pilih Tanggal Tertentu"
                        />
                    </div>
                </div>

                <!-- Ringkasan Statistik Singkat -->
                <div class="grid grid-cols-1 gap-4 pt-1 sm:grid-cols-3">
                    <div
                        class="flex items-center gap-3.5 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-3.5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#beedc0]"
                        >
                            <Users class="size-5 text-[#000000]" />
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-medium text-[#333333]/70"
                                >Total Pasien Terdaftar</span
                            >
                            <span
                                class="font-mono text-xl font-bold text-[#000000]"
                                >{{ appointments.length }} Pasien</span
                            >
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3.5 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-3.5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100"
                        >
                            <Clock class="size-5 text-amber-800" />
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-medium text-[#333333]/70"
                                >Menunggu Giliran</span
                            >
                            <span
                                class="font-mono text-xl font-bold text-amber-800"
                                >{{ waitingList.length }} Pasien</span
                            >
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3.5 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/60 p-3.5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100"
                        >
                            <CheckCircle2 class="size-5 text-blue-800" />
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-medium text-[#333333]/70"
                                >Selesai Pemeriksaan</span
                            >
                            <span
                                class="font-mono text-xl font-bold text-blue-800"
                                >{{ completedList.length }} Pasien</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Sorotan Utama: Pasien Yang Sedang Diperiksa -->
            <div
                class="rounded-[10px] border-2 border-[#000000] bg-[#fffff3] p-6 shadow-sm sm:p-8"
            >
                <div
                    class="flex items-center justify-between border-b border-[#333333]/10 pb-4"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-3.5 w-3.5 animate-pulse rounded-full bg-emerald-500"
                        ></span>
                        <span
                            class="text-xs font-bold tracking-wider text-[#333333] uppercase"
                            >Ruang Konsultasi Aktif</span
                        >
                    </div>
                    <span
                        class="rounded-full bg-[#beedc0] px-3 py-1 text-xs font-semibold text-[#000000]"
                    >
                        {{
                            selectedSchedule?.room?.name_room || 'Ruang Periksa'
                        }}
                    </span>
                </div>

                <!-- Konten Pasien Aktif -->
                <div
                    v-if="currentCalling"
                    class="flex flex-col justify-between gap-6 py-6 lg:flex-row lg:items-center"
                >
                    <div class="flex items-start gap-6 sm:items-center">
                        <div
                            class="flex min-w-[130px] flex-col items-center justify-center rounded-[10px] bg-[#000000] p-5 text-center text-white shadow-inner"
                        >
                            <span
                                class="text-[11px] font-semibold tracking-wider text-[#beedc0] uppercase"
                                >Nomor Urut</span
                            >
                            <span
                                class="mt-1 font-mono text-4xl font-extrabold"
                                >{{ currentCalling.queue_number }}</span
                            >
                        </div>

                        <div class="space-y-1.5">
                            <h2
                                class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000] sm:text-3xl"
                            >
                                {{ currentCalling.patient?.name }}
                            </h2>
                            <div
                                class="flex flex-wrap gap-4 text-xs text-[#333333]"
                            >
                                <span
                                    ><strong>Tanggal Kunjungan:</strong>
                                    {{
                                        formatDateLabel(
                                            currentCalling.appointment_date,
                                        )
                                    }}</span
                                >
                                <span
                                    ><strong>NIK:</strong>
                                    {{
                                        currentCalling.patient?.resident_n ||
                                        '-'
                                    }}</span
                                >
                                <span
                                    ><strong>Kelamin:</strong>
                                    {{
                                        currentCalling.patient?.gender || '-'
                                    }}</span
                                >
                                <span
                                    ><strong>Telepon:</strong>
                                    {{
                                        currentCalling.patient?.number_phone ||
                                        '-'
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="currentCalling.complaint"
                                class="mt-2 rounded-[6px] bg-[#edede2] p-3 text-xs text-[#333333]"
                            >
                                <strong>Keluhan Pasien:</strong>
                                {{ currentCalling.complaint }}
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Dokter (Bisa Dipanggil Berulang Kali) -->
                    <div class="flex shrink-0 flex-wrap items-center gap-3">
                        <!-- Tombol Buka EMR & Resep -->
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.97 }"
                            @click="openConsultationModal(currentCalling)"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-[#222222]"
                            title="Buka form pemeriksaan SOAP, tanda vital, resep obat & riwayat pasien"
                        >
                            <FileText class="size-4 text-[#beedc0]" />
                            <span>Periksa & Rekam Medis (EMR)</span>
                        </motion.button>

                        <!-- Tombol Panggil Ulang Pasien -->
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.97 }"
                            @click="handleCall(currentCalling.appointment_id)"
                            :disabled="isCallingAction"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] border-2 border-[#000000] bg-[#beedc0] px-5 py-2 text-xs font-bold text-[#000000] shadow-xs transition-all hover:bg-[#a6e5a8] disabled:opacity-50"
                            title="Panggil ulang nomor antrean ini dan bunyikan suara di layar TV"
                        >
                            <BellRing
                                class="size-4 text-[#000000]"
                                :class="
                                    isCallingAction
                                        ? 'animate-spin'
                                        : 'animate-bounce'
                                "
                            />
                            <span>{{
                                isCallingAction
                                    ? 'Memanggil...'
                                    : 'Panggil Ulang'
                            }}</span>
                        </motion.button>

                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            @click="handleSkip(currentCalling.appointment_id)"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                        >
                            <Forward class="size-4" />
                            <span>Lewati</span>
                        </motion.button>

                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            @click="
                                handleComplete(currentCalling.appointment_id)
                            "
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-5 py-2.5 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                        >
                            <UserCheck class="size-4" />
                            <span>Selesai Saja</span>
                        </motion.button>
                    </div>
                </div>

                <!-- Kosong: Belum Ada Pasien Yang Dipanggil -->
                <div v-else class="space-y-3 py-10 text-center">
                    <p class="text-base font-semibold text-[#000000]">
                        Ruang periksa sedang kosong.
                    </p>
                    <p class="text-xs text-[#333333]/70">
                        Pilih salah satu nomor antrean dari daftar tunggu di
                        bawah atau tekan tombol panggil terdepan.
                    </p>

                    <div v-if="waitingList.length > 0">
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.97 }"
                            @click="handleCallNext"
                            class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-[#222222]"
                        >
                            <Bell
                                class="size-4 animate-bounce text-[#beedc0]"
                            />
                            <span
                                >Panggil Antrean Terdepan ({{
                                    waitingList[0]?.queue_number
                                }}
                                - {{ waitingList[0]?.patient?.name }})</span
                            >
                        </motion.button>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Tunggu Pasien -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3
                        class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]"
                    >
                        Daftar Antrean Menunggu
                    </h3>
                    <span class="text-xs font-medium text-[#333333]"
                        >{{ waitingList.length }} Pasien Siap Dipanggil</span
                    >
                </div>

                <div
                    v-if="waitingList.length > 0"
                    class="overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3]"
                >
                    <table class="w-full text-left text-xs text-[#333333]">
                        <thead
                            class="border-b border-[#333333]/10 bg-[#edede2] text-[11px] font-semibold text-[#000000] uppercase"
                        >
                            <tr>
                                <th class="px-5 py-3.5">No. Antrean</th>
                                <th class="px-5 py-3.5">Tanggal</th>
                                <th class="px-5 py-3.5">Nama Pasien</th>
                                <th class="px-5 py-3.5">Keluhan Utama</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#333333]/10">
                            <tr
                                v-for="item in waitingList"
                                :key="item.appointment_id"
                                class="transition-colors hover:bg-[#edede2]/40"
                            >
                                <td
                                    class="px-5 py-4 font-mono text-sm font-bold text-[#000000]"
                                >
                                    {{ item.queue_number }}
                                </td>
                                <td
                                    class="px-5 py-4 font-medium text-[#333333]"
                                >
                                    {{ formatDateLabel(item.appointment_date) }}
                                </td>
                                <td
                                    class="px-5 py-4 font-semibold text-[#000000]"
                                >
                                    {{ item.patient?.name }}
                                </td>
                                <td
                                    class="max-w-xs truncate px-5 py-4 text-[#333333]"
                                >
                                    {{ item.complaint || '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-[11px] font-medium text-amber-900"
                                    >
                                        Menunggu
                                    </span>
                                </td>
                                <td class="space-x-2 px-5 py-4 text-right">
                                    <motion.button
                                        type="button"
                                        :whileHover="{ scale: 1.03 }"
                                        :whileTap="{ scale: 0.97 }"
                                        @click="openConsultationModal(item)"
                                        class="inline-flex min-h-[36px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-3.5 py-1.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#beedc0]"
                                        title="Buka Rekam Medis & Riwayat Pasien"
                                    >
                                        <FileText class="size-3.5" />
                                        <span>EMR</span>
                                    </motion.button>

                                    <motion.button
                                        type="button"
                                        :whileHover="{ scale: 1.03 }"
                                        :whileTap="{ scale: 0.97 }"
                                        @click="handleCall(item.appointment_id)"
                                        class="inline-flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#333333]"
                                    >
                                        <Bell class="size-3.5 text-[#beedc0]" />
                                        <span>Panggil</span>
                                    </motion.button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State Daftar Tunggu -->
                <div
                    v-else
                    class="rounded-[10px] border border-dashed border-[#333333]/20 bg-[#fffff3] py-12 text-center"
                >
                    <CheckCircle2 class="mx-auto size-8 text-[#000000]/40" />
                    <p class="mt-2 text-sm font-medium text-[#000000]">
                        Tidak ada pasien dalam antrean tunggu untuk tanggal
                        terpilih.
                    </p>
                    <p class="text-xs text-[#333333]/70">
                        Gunakan tab filter tanggal di atas atau pilih tanggal
                        lain untuk melihat data reservasi jadwal mendatang.
                    </p>
                </div>
            </div>
        </main>

        <!-- Modal Konsultasi Medis SOAP, E-Prescription & Riwayat Klinis Pasien -->
        <ConsultationModal
            :open="isConsultationModalOpen"
            :appointment="activeConsultationAppointment"
            @update:open="isConsultationModalOpen = $event"
            @success="handleConsultationSuccess"
        />
    </div>
</template>
