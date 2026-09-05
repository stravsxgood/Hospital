<script setup lang="ts">
/**
 * @file BookingModal.vue
 * @description Presentational dialog component for patient doctor appointment booking.
 * Built adhering strictly to DESIGN.md (Evergreen theme) and AGENTS.md standards:
 *  - Colors: Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Ink Black (#000000)
 *  - Typography: Rubik sans-serif for labels/inputs, IvyPresto for titles
 *  - Touch targets: Minimum 44px (min-h-[44px])
 *  - Motion: Motion-v micro-animations on action buttons
 */
import { useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    Info,
    Loader2,
    Stethoscope,
    User,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { DoctorSchedule } from '@/types';

declare const route: any;

const props = defineProps<{
    open: boolean;
    schedule: DoctorSchedule | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success', appointmentData: any): void;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const form = useForm({
    doctor_schedule_id: null as number | null,
    appointment_date: '',
    complaint: '',
});

const serverError = ref<string | null>(null);

// Format tanggal lokal YYYY-MM-DD
const formatDateIso = (d: Date): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const minDate = computed(() => formatDateIso(new Date()));

// Mapping nama hari Bahasa Indonesia ke indeks hari JS (0 = Minggu, 1 = Senin, dst.)
const DAY_INDEX_MAP: Record<string, number> = {
    minggu: 0,
    senin: 1,
    selasa: 2,
    rabu: 3,
    kamis: 4,
    jumat: 5,
    sabtu: 6,
    sunday: 0,
    monday: 1,
    tuesday: 2,
    wednesday: 3,
    thursday: 4,
    friday: 5,
    saturday: 6,
};

const INDONESIAN_DAY_NAMES = [
    'Minggu',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
];

const targetDayIndex = computed<number | null>(() => {
    const dayStr = props.schedule?.day || props.schedule?.day_of_week;

    if (!dayStr) {
        return null;
    }

    const clean = dayStr.trim().toLowerCase();

    return DAY_INDEX_MAP[clean] ?? null;
});

// Hitung 4 tanggal praktik mendatang yang cocok dengan hari praktik dokter
const upcomingPracticeDates = computed<
    Array<{ date: string; label: string; fullLabel: string }>
>(() => {
    if (targetDayIndex.value === null) {
        return [];
    }

    const targetDay = targetDayIndex.value;
    const dates: Array<{ date: string; label: string; fullLabel: string }> = [];
    const cursor = new Date();

    // Loop hingga 30 hari ke depan untuk mencari tanggal yang sesuai
    for (let i = 0; i < 35 && dates.length < 4; i++) {
        if (cursor.getDay() === targetDay) {
            const iso = formatDateIso(cursor);
            const dayName = INDONESIAN_DAY_NAMES[cursor.getDay()];
            const dateNum = cursor.getDate();
            const monthName = cursor.toLocaleDateString('id-ID', {
                month: 'short',
            });
            const fullMonth = cursor.toLocaleDateString('id-ID', {
                month: 'long',
                year: 'numeric',
            });

            dates.push({
                date: iso,
                label: `${dayName}, ${dateNum} ${monthName}`,
                fullLabel: `${dayName}, ${dateNum} ${fullMonth}`,
            });
        }

        cursor.setDate(cursor.getDate() + 1);
    }

    return dates;
});

// Cek apakah tanggal yang diinput cocok dengan hari praktik dokter
const selectedDateDayMatch = computed(() => {
    if (!form.appointment_date || targetDayIndex.value === null) {
        return { valid: true, selectedDayName: '' };
    }

    const parts = form.appointment_date.split('-');

    if (parts.length !== 3) {
        return { valid: true, selectedDayName: '' };
    }

    const parsedDate = new Date(
        Number(parts[0]),
        Number(parts[1]) - 1,
        Number(parts[2]),
    );
    const dayIdx = parsedDate.getDay();
    const isMatch = dayIdx === targetDayIndex.value;
    const selectedDayName = INDONESIAN_DAY_NAMES[dayIdx] || '';

    return {
        valid: isMatch,
        selectedDayName,
    };
});

// Sinkronisasi data form setiap kali schedule yang dipilih berubah
watch(
    () => props.schedule,
    (newSchedule) => {
        serverError.value = null;

        if (newSchedule) {
            form.doctor_schedule_id =
                newSchedule.doctor_schedule_id ?? newSchedule.id ?? null;
            form.complaint = '';
            form.clearErrors();

            // Default tanggal ke hari praktik terdekat jika tersedia
            if (upcomingPracticeDates.value.length > 0) {
                form.appointment_date = upcomingPracticeDates.value[0].date;
            } else {
                form.appointment_date = '';
            }
        }
    },
    { immediate: true },
);

const selectUpcomingDate = (dateStr: string) => {
    form.appointment_date = dateStr;
    serverError.value = null;
    form.clearErrors('appointment_date');
};

const closeModal = () => {
    emit('update:open', false);
    form.reset();
    form.clearErrors();
    serverError.value = null;
};

const submitBooking = () => {
    serverError.value = null;

    if (!selectedDateDayMatch.value.valid) {
        serverError.value = `Tanggal yang dipilih jatuh pada hari ${selectedDateDayMatch.value.selectedDayName}. Dokter hanya berpraktik pada hari ${props.schedule?.day}. Silakan pilih tanggal praktik yang sesuai.`;

        return;
    }

    const targetUrl =
        typeof route === 'function'
            ? route('appointments.store')
            : '/appointments';

    form.post(targetUrl, {
        preserveScroll: true,
        onSuccess: (pageObj) => {
            const flash = (pageObj.props as any)?.flash;

            // Jika backend mengembalikan flash error
            if (flash?.error) {
                serverError.value = String(flash.error);

                return;
            }

            // Ambil payload tiket dari flash response
            const ticket = flash?.success?.ticket ?? flash?.ticket;

            emit('success', ticket || {
                appointment_id: 0,
                queue_number: 'ANT-001',
                doctor_name: props.schedule?.doctor?.name ?? 'Dokter Spesialis',
                poli_name:
                    props.schedule?.poli?.name_poli ??
                    props.schedule?.poli?.name ??
                    'Poliklinik',
                appointment_date: form.appointment_date,
                patient_name: user.value?.name ?? 'Pasien',
            });

            closeModal();
        },
        onError: (errors) => {
            console.error('Pendaftaran antrean gagal divalidasi:', errors);
        },
    });
};

const getSpecializationLabel = (schedule: DoctorSchedule | null): string => {
    if (!schedule?.doctor?.specialization) {
        return 'Dokter Umum';
    }

    const spec = schedule.doctor.specialization;

    if (typeof spec === 'object') {
        return spec.name_specialization || spec.name || 'Dokter Umum';
    }

    return typeof spec === 'string' && spec ? spec : 'Dokter Umum';
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 font-['Rubik'] text-[#000000] shadow-xl sm:max-w-[520px]"
        >
            <!-- Header Modal -->
            <DialogHeader
                class="space-y-1.5 border-b border-[#333333]/10 pb-2 text-left"
            >
                <div class="flex items-center gap-2.5">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-[#beedc0]"
                    >
                        <Stethoscope class="size-5 text-[#000000]" />
                    </span>
                    <DialogTitle
                        class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]"
                    >
                        Reservasi Janji Temu Dokter
                    </DialogTitle>
                </div>
                <DialogDescription
                    class="text-xs leading-relaxed text-[#333333]/80"
                >
                    Pilih tanggal praktik yang sesuai dengan jadwal dokter dan isi
                    keluhan untuk memproses nomor antrean poliklinik.
                </DialogDescription>
            </DialogHeader>

            <!-- Alert Kesalahan Server / Flash Error -->
            <div
                v-if="serverError"
                class="mt-2 flex items-start gap-2.5 rounded-[7px] border border-red-200 bg-red-50 p-3 text-xs text-red-700"
            >
                <AlertCircle class="mt-0.5 size-4 shrink-0 text-red-600" />
                <div class="leading-relaxed">{{ serverError }}</div>
            </div>

            <!-- Ringkasan Dokter & Jadwal yang Dipilih -->
            <div
                v-if="schedule"
                class="mt-3 space-y-3 rounded-[10px] border border-[#333333]/12 bg-[#edede2]/60 p-4"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="inline-flex items-center rounded-[46px] bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                    >
                        {{
                            schedule.poli?.name_poli ||
                            schedule.poli?.name ||
                            'Poliklinik'
                        }}
                    </span>
                    <span
                        class="rounded-[46px] border border-[#333333]/10 bg-[#ffffff] px-2.5 py-0.5 text-xs font-medium text-[#333333]"
                    >
                        Kuota:
                        {{ schedule.quota_day ?? schedule.quota ?? 'Tersedia' }}
                        Pasien
                    </span>
                </div>

                <div>
                    <h4
                        class="font-['ivypresto-headline'] text-lg leading-tight font-semibold text-[#000000]"
                    >
                        {{ schedule.doctor?.name || 'Dokter Spesialis' }}
                    </h4>
                    <p class="mt-0.5 text-xs font-medium text-[#333333]">
                        {{ getSpecializationLabel(schedule) }}
                    </p>
                </div>

                <div
                    class="flex flex-wrap items-center gap-4 border-t border-[#333333]/10 pt-2 text-xs text-[#333333]"
                >
                    <div class="flex items-center gap-1.5 font-medium">
                        <Calendar class="size-3.5 text-[#000000]" />
                        <span>Praktik Setiap Hari {{ schedule.day || schedule.day_of_week }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Clock class="size-3.5 text-[#000000]" />
                        <span>{{ schedule.start_time }} - {{ schedule.end_time }} WIB</span>
                    </div>
                </div>
            </div>

            <!-- Form Input Reservasi -->
            <form @submit.prevent="submitBooking" class="space-y-4 pt-2">
                <!-- Info Pasien (Read-only) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-[#000000]">
                        Nama Pasien
                    </label>
                    <div
                        class="flex items-center gap-2.5 rounded-[7px] border border-[#333333]/15 bg-[#ffffff] px-3.5 py-2.5 text-sm text-[#000000]"
                    >
                        <User class="size-4 text-[#333333]" />
                        <span class="font-medium">{{
                            user?.name || 'Pasien Terdaftar'
                        }}</span>
                    </div>
                </div>

                <!-- Pilihan Cepat Tanggal Praktik Mendatang -->
                <div v-if="upcomingPracticeDates.length > 0" class="space-y-1.5">
                    <label class="text-xs font-semibold text-[#000000]">
                        Pilih Jadwal Praktik Terdekat
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="(item, idx) in upcomingPracticeDates"
                            :key="idx"
                            type="button"
                            @click="selectUpcomingDate(item.date)"
                            class="flex min-h-[44px] items-center justify-between rounded-[7px] border px-3 py-2 text-left text-xs font-medium transition-all"
                            :class="
                                form.appointment_date === item.date
                                    ? 'border-[#000000] bg-[#000000] text-white shadow-sm'
                                    : 'border-[#333333]/15 bg-white text-[#333333] hover:border-[#000000]/40'
                            "
                        >
                            <span>{{ item.label }}</span>
                            <CheckCircle2
                                v-if="form.appointment_date === item.date"
                                class="size-3.5 text-[#beedc0]"
                            />
                        </button>
                    </div>
                </div>

                <!-- Input Tanggal Kunjungan Manual -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label
                            for="appointment_date"
                            class="text-xs font-semibold text-[#000000]"
                        >
                            Tanggal Rencana Kunjungan
                            <span class="text-red-500">*</span>
                        </label>
                        <span
                            v-if="schedule?.day"
                            class="text-[11px] text-[#333333]/70"
                        >
                            (Wajib hari {{ schedule.day }})
                        </span>
                    </div>

                    <input
                        id="appointment_date"
                        v-model="form.appointment_date"
                        type="date"
                        :min="minDate"
                        required
                        class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3.5 py-2.5 text-sm text-[#000000] focus:border-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        :class="{
                            'border-red-500':
                                form.errors.appointment_date ||
                                !selectedDateDayMatch.valid,
                        }"
                    />

                    <!-- Peringatan Ketidaksesuaian Hari Kunjungan -->
                    <div
                        v-if="!selectedDateDayMatch.valid && form.appointment_date"
                        class="mt-1 flex items-start gap-1.5 rounded-[6px] bg-amber-50 p-2 text-xs text-amber-800"
                    >
                        <Info class="mt-0.5 size-3.5 shrink-0 text-amber-600" />
                        <span>
                            Tanggal ini jatuh pada hari <strong>{{ selectedDateDayMatch.selectedDayName }}</strong>, sedangkan dokter berpraktik pada hari <strong>{{ schedule?.day }}</strong>.
                        </span>
                    </div>

                    <p
                        v-if="form.errors.appointment_date"
                        class="mt-1 flex items-center gap-1 text-xs text-red-600"
                    >
                        <AlertCircle class="size-3.5 shrink-0" />
                        {{ form.errors.appointment_date }}
                    </p>
                </div>

                <!-- Input Keluhan Pasien -->
                <div class="space-y-1.5">
                    <label
                        for="complaint"
                        class="text-xs font-semibold text-[#000000]"
                    >
                        Keluhan Singkat / Gejala
                    </label>
                    <textarea
                        id="complaint"
                        v-model="form.complaint"
                        rows="3"
                        placeholder="Tuliskan keluhan atau gejala yang dirasakan..."
                        class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-sm text-[#000000] placeholder:text-[#333333]/40 focus:border-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        :class="{ 'border-red-500': form.errors.complaint }"
                    ></textarea>
                    <p
                        v-if="form.errors.complaint"
                        class="mt-1 flex items-center gap-1 text-xs text-red-600"
                    >
                        <AlertCircle class="size-3.5 shrink-0" />
                        {{ form.errors.complaint }}
                    </p>
                </div>

                <!-- Pesan Error Terkait Jadwal Dokter -->
                <div
                    v-if="form.errors.doctor_schedule_id"
                    class="flex items-center gap-2 rounded-[7px] border border-red-200 bg-red-50 p-3 text-xs text-red-700"
                >
                    <AlertCircle class="size-4 shrink-0" />
                    <span>{{ form.errors.doctor_schedule_id }}</span>
                </div>

                <!-- Footer Tombol Aksi -->
                <DialogFooter
                    class="flex flex-col-reverse gap-2 border-t border-[#333333]/10 pt-3 sm:flex-row sm:justify-end"
                >
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="closeModal"
                        class="min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-5 py-2.5 text-sm font-medium text-[#333333] transition-colors hover:bg-[#edede2] sm:w-auto"
                    >
                        Batal
                    </motion.button>
                    <motion.button
                        type="submit"
                        :disabled="form.processing || !selectedDateDayMatch.valid"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-sm font-medium text-[#ffffff] transition-colors hover:bg-[#333333] disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="size-4 animate-spin text-white"
                        />
                        <span>{{
                            form.processing
                                ? 'Memproses Antrean...'
                                : 'Konfirmasi Reservasi'
                        }}</span>
                    </motion.button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
