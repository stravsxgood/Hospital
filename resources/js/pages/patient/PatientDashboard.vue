<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowRight,
    Calendar,
    CheckCircle2,
    Clock,
    Plus,
    Stethoscope,
    Ticket,
    UserCheck,
} from '@lucide/vue';
import { motion } from 'motion-v';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Portal Pasien',
                href: '/patient/dashboard',
            },
        ],
    },
});

interface DoctorSchedule {
    doctor_schedule_id: number;
    start_time: string;
    end_time: string;
    doctor?: {
        name: string;
        specialization?: { name_specialization?: string };
    };
    poli?: { name_poli?: string; name?: string };
    room?: { name_room?: string };
}

interface Appointment {
    appointment_id: number;
    queue_number: string;
    appointment_date: string;
    complaint: string | null;
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled';
    doctor_schedule?: DoctorSchedule;
}

defineProps<{
    patientName: string;
    stats: {
        total_visits: number;
        upcoming: number;
        completed: number;
    };
    activeAppointment: Appointment | null;
    recentAppointments: Appointment[];
    availableSchedules: DoctorSchedule[];
    currentDate: string;
}>();

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100/80 text-amber-900 border-amber-200';
        case 'in_progress':
            return 'bg-[#beedc0] text-[#000000] border-[#beedc0] font-bold animate-pulse';
        case 'completed':
            return 'bg-blue-100/80 text-blue-900 border-blue-200';
        case 'cancelled':
            return 'bg-rose-100/80 text-rose-900 border-rose-200';
        default:
            return 'bg-[#edede2] text-[#333333] border-[#000000]/10';
    }
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

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending':
            return 'Menunggu Giliran';
        case 'in_progress':
            return 'Sedang Diperiksa';
        case 'completed':
            return 'Konsultasi Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="Portal Layanan Pasien - Hospital Population" />

    <div
        class="min-h-screen w-full space-y-6 bg-[#edede2] p-4 font-['Rubik'] text-[#000000] sm:p-6 lg:p-8"
    >
        <!-- Banner Sambutan Pasien & Tombol Daftar Baru -->
        <motion.header
            :initial="{ opacity: 0, y: -12 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="flex flex-col items-start justify-between gap-6 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-none sm:p-8 md:flex-row md:items-center"
        >
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3.5 py-1 text-xs font-bold text-[#000000]"
                    >
                        <Activity class="size-3.5 text-[#000000]" />
                        <span>Portal Layanan Pasien</span>
                    </span>
                    <span class="text-xs font-medium text-[#333333]">
                        {{ currentDate }}
                    </span>
                </div>
                <h1
                    class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl lg:text-4xl"
                >
                    Halo, {{ patientName }}
                </h1>
                <p
                    class="max-w-xl text-xs leading-relaxed text-[#333333] sm:text-sm"
                >
                    Kelola antrean poliklinik, pantau nomor antrean aktif secara
                    langsung, dan akses riwayat pemeriksaan medis Anda dalam
                    satu portal terpadu.
                </p>
            </div>

            <motion.div
                :whileHover="{ scale: 1.02 }"
                :whileTap="{ scale: 0.98 }"
                class="w-full sm:w-auto"
            >
                <Link
                    href="/schedule"
                    class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-medium text-[#ffffff] shadow-none transition-all hover:bg-[#1a1a1a] sm:w-auto sm:text-sm"
                >
                    <Plus class="size-4 text-[#beedc0]" />
                    <span>Daftar Antrean Poli</span>
                    <ArrowRight class="size-4 text-[#beedc0]" />
                </Link>
            </motion.div>
        </motion.header>

        <!-- Kartu Tiket Antrean Aktif (Sorotan Utama jika ada tiket aktif) -->
        <motion.section
            v-if="activeAppointment"
            :initial="{ opacity: 0, scale: 0.98 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="{ duration: 0.28, ease: 'easeOut' }"
            class="space-y-6 rounded-3xl border border-[#000000] bg-[#000000] p-6 text-white shadow-none sm:p-8"
        >
            <div
                class="flex items-center justify-between border-b border-white/10 pb-4"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-3 w-3 animate-pulse rounded-full bg-[#beedc0]"
                    ></span>
                    <span
                        class="text-xs font-bold tracking-wider text-[#beedc0] uppercase"
                        >Tiket Antrean Berjalan Anda</span
                    >
                </div>
                <span class="font-mono text-xs text-white/70">{{
                    activeAppointment.appointment_date
                }}</span>
            </div>

            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="flex items-center gap-6">
                    <!-- Nomor Urut -->
                    <div
                        class="flex min-w-[120px] flex-col items-center justify-center rounded-2xl bg-[#fffff3] p-4 text-center text-[#000000]"
                    >
                        <span
                            class="text-[10px] font-bold tracking-wider text-[#333333] uppercase"
                            >No. Antrean</span
                        >
                        <span
                            class="mt-0.5 font-mono text-3xl font-extrabold text-[#000000]"
                            >{{ activeAppointment.queue_number }}</span
                        >
                    </div>

                    <!-- Info Dokter & Poli -->
                    <div class="space-y-1">
                        <span
                            class="inline-block rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-bold text-[#000000]"
                        >
                            {{
                                activeAppointment.doctor_schedule?.poli
                                    ?.name_poli ||
                                activeAppointment.doctor_schedule?.poli?.name ||
                                'Poliklinik'
                            }}
                        </span>
                        <h3
                            class="font-['ivypresto-headline'] text-xl font-semibold text-white sm:text-2xl"
                        >
                            {{
                                formatDoctorName(
                                    activeAppointment.doctor_schedule?.doctor
                                        ?.name,
                                )
                            }}
                        </h3>
                        <p class="text-xs text-white/70">
                            {{
                                activeAppointment.doctor_schedule?.room
                                    ?.name_room || 'Ruang Periksa'
                            }}
                            &bull; Praktik:
                            {{
                                activeAppointment.doctor_schedule?.start_time.substring(
                                    0,
                                    5,
                                )
                            }}
                            -
                            {{
                                activeAppointment.doctor_schedule?.end_time.substring(
                                    0,
                                    5,
                                )
                            }}
                            WIB
                        </p>
                    </div>
                </div>

                <!-- Tombol Aksi Tiket -->
                <div class="flex items-center gap-3">
                    <motion.div
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <Link
                            href="/my-appointments"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-[40.5px] bg-[#beedc0] px-6 py-2.5 text-xs font-bold text-[#000000] shadow-none transition-colors hover:bg-[#a8e6ab] sm:text-sm"
                        >
                            <Ticket class="size-4" />
                            <span>Lihat Detail Karcis</span>
                        </Link>
                    </motion.div>
                </div>
            </div>
        </motion.section>

        <!-- 3 Ringkasan Statistik Pasien -->
        <section
            aria-label="Ringkasan Statistik Pasien"
            class="grid grid-cols-1 gap-4 sm:grid-cols-3"
        >
            <!-- Kartu: Total Kunjungan -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.05 }"
                class="flex items-center justify-between rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-6"
            >
                <div>
                    <span
                        class="block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >Total Kunjungan</span
                    >
                    <span
                        class="mt-1 font-mono text-3xl font-bold text-[#000000]"
                        >{{ stats.total_visits }}</span
                    >
                    <span class="mt-0.5 block text-[11px] text-[#333333]"
                        >Riwayat pendaftaran akun</span
                    >
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                >
                    <Ticket class="size-6 text-[#000000]" />
                </div>
            </motion.div>

            <!-- Kartu: Jadwal Mendatang -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.1 }"
                class="flex items-center justify-between rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-6"
            >
                <div>
                    <span
                        class="block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >Jadwal Mendatang</span
                    >
                    <span
                        class="mt-1 font-mono text-3xl font-bold text-[#000000]"
                        >{{ stats.upcoming }}</span
                    >
                    <span class="mt-0.5 block text-[11px] text-[#333333]"
                        >Reservasi belum berlangsung</span
                    >
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-900"
                >
                    <Clock class="size-6 text-amber-900" />
                </div>
            </motion.div>

            <!-- Kartu: Konsultasi Selesai -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.15 }"
                class="flex items-center justify-between rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-6"
            >
                <div>
                    <span
                        class="block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >Konsultasi Selesai</span
                    >
                    <span
                        class="mt-1 font-mono text-3xl font-bold text-[#000000]"
                        >{{ stats.completed }}</span
                    >
                    <span class="mt-0.5 block text-[11px] text-[#333333]"
                        >Pemeriksaan medis tuntas</span
                    >
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-900"
                >
                    <CheckCircle2 class="size-6 text-blue-900" />
                </div>
            </motion.div>
        </section>

        <!-- Bagian Dua Kolom: Riwayat Terakhir & Jadwal Hari Ini -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Kolom Kiri: Riwayat Kunjungan Pasien -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.2 }"
                class="space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-none lg:col-span-2"
            >
                <div
                    class="flex items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <div>
                        <h3
                            class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                        >
                            Riwayat Kunjungan Terakhir
                        </h3>
                        <p class="text-xs text-[#333333]">
                            Daftar pendaftaran antrean dan janji temu medis
                            Anda.
                        </p>
                    </div>
                    <Link
                        href="/my-appointments"
                        class="text-xs font-bold text-[#000000] hover:underline"
                    >
                        Lihat Semua Antrean &rarr;
                    </Link>
                </div>

                <div
                    v-if="recentAppointments && recentAppointments.length > 0"
                    class="overflow-hidden rounded-2xl border border-[#000000]/10"
                >
                    <div class="scrollbar-thin overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead
                                class="border-b border-[#000000]/10 bg-[#edede2]/80 text-[11px] font-bold tracking-wider text-[#000000] uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-3 sm:px-5">
                                        No. Antrean
                                    </th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">
                                        Poliklinik & Dokter
                                    </th>
                                    <th class="px-4 py-3 text-right sm:px-5">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-[#000000]/5 font-medium text-[#000000]"
                            >
                                <tr
                                    v-for="item in recentAppointments"
                                    :key="item.appointment_id"
                                    class="transition-colors hover:bg-[#edede2]/30"
                                >
                                    <td
                                        class="px-4 py-3.5 font-mono text-sm font-bold text-[#000000] sm:px-5"
                                    >
                                        {{ item.queue_number }}
                                    </td>
                                    <td
                                        class="px-4 py-3.5 font-mono text-xs text-[#333333]"
                                    >
                                        {{ item.appointment_date }}
                                    </td>
                                    <td class="px-4 py-3.5 text-xs">
                                        <span
                                            class="block font-bold text-[#000000]"
                                        >
                                            {{
                                                item.doctor_schedule?.poli
                                                    ?.name_poli ||
                                                item.doctor_schedule?.poli
                                                    ?.name ||
                                                'Poliklinik'
                                            }}
                                        </span>
                                        <span
                                            class="text-[11px] text-[#333333]"
                                        >
                                            {{
                                                formatDoctorName(
                                                    item.doctor_schedule?.doctor
                                                        ?.name,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right sm:px-5">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-bold',
                                                getStatusBadgeClass(
                                                    item.status,
                                                ),
                                            ]"
                                        >
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="py-12 text-center text-xs text-[#333333]">
                    Anda belum memiliki riwayat pendaftaran antrean poliklinik.
                </div>
            </motion.div>

            <!-- Kolom Kanan: Jadwal Praktik Poliklinik Hari Ini -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.25 }"
                class="flex flex-col justify-between space-y-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-none"
            >
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between border-b border-[#000000]/10 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <Stethoscope class="size-4 text-[#000000]" />
                            <h3
                                class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                            >
                                Jadwal Dokter
                            </h3>
                        </div>
                        <span
                            class="rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3 py-0.5 text-[11px] font-bold text-[#000000]"
                        >
                            Hari Ini
                        </span>
                    </div>

                    <div v-if="availableSchedules.length > 0" class="space-y-3">
                        <div
                            v-for="sch in availableSchedules"
                            :key="sch.doctor_schedule_id"
                            class="space-y-1 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-3.5"
                        >
                            <span
                                class="block text-xs font-bold text-[#000000]"
                                >{{ formatDoctorName(sch.doctor?.name) }}</span
                            >
                            <span class="block text-[11px] text-[#333333]">
                                {{ sch.poli?.name_poli || sch.poli?.name }} ({{
                                    sch.room?.name_room || 'Ruang Konsultasi'
                                }})
                            </span>
                            <span
                                class="block font-mono text-[10px] text-[#333333]"
                            >
                                Jam: {{ sch.start_time.substring(0, 5) }} -
                                {{ sch.end_time.substring(0, 5) }} WIB
                            </span>
                        </div>
                    </div>

                    <p v-else class="py-6 text-center text-xs text-[#333333]">
                        Tidak ada jadwal poliklinik yang tersedia saat ini.
                    </p>
                </div>

                <motion.div
                    :whileHover="{ scale: 1.02 }"
                    :whileTap="{ scale: 0.98 }"
                >
                    <Link
                        href="/schedule"
                        class="mt-4 inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-5 py-2.5 text-xs font-medium text-[#ffffff] shadow-none transition-colors hover:bg-[#1a1a1a]"
                    >
                        <span>Lihat Semua Jadwal & Daftar</span>
                        <ArrowRight class="size-3.5 text-[#beedc0]" />
                    </Link>
                </motion.div>
            </motion.div>
        </div>
    </div>
</template>
