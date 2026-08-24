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
import { useForm, usePage } from '@inertiajs/vue3'
import { AlertCircle, Calendar, Clock, Loader2, Stethoscope, User } from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import type { DoctorSchedule } from '@/types'

declare const route: any

const props = defineProps<{
    open: boolean
    schedule: DoctorSchedule | null
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'success', appointmentData: any): void
}>()

const page = usePage()
const user = computed(() => page.props.auth?.user)

const form = useForm({
    doctor_schedule_id: null as number | null,
    appointment_date: '',
    complaint: '',
})

// Sinkronisasi data form setiap kali schedule yang dipilih berubah
watch(
    () => props.schedule,
    (newSchedule) => {
        if (newSchedule) {
            form.doctor_schedule_id = newSchedule.doctor_schedule_id ?? newSchedule.id ?? null
            form.appointment_date = ''
            form.complaint = ''
            form.clearErrors()
        }
    },
    { immediate: true },
)

const closeModal = () => {
    emit('update:open', false)
    form.reset()
    form.clearErrors()
}

const submitBooking = () => {
    const targetUrl = typeof route === 'function' ? route('appointments.store') : '/appointments'

    form.post(targetUrl, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        },
    })
}

const getSpecializationLabel = (schedule: DoctorSchedule | null): string => {
    if (!schedule?.doctor?.specialization) {
return 'Dokter Umum'
}

    const spec = schedule.doctor.specialization

    if (typeof spec === 'object') {
        return spec.name_specialization || spec.name || 'Dokter Umum'
    }

    return typeof spec === 'string' && spec ? spec : 'Dokter Umum'
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[520px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000] shadow-xl rounded-[10px] font-['Rubik']">
            <!-- Header Modal -->
            <DialogHeader class="space-y-1.5 text-left pb-2 border-b border-[#333333]/10">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#beedc0]">
                        <Stethoscope class="size-5 text-[#000000]" />
                    </span>
                    <DialogTitle class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                        Reservasi Antrean
                    </DialogTitle>
                </div>
                <DialogDescription class="text-xs text-[#333333]/80 leading-relaxed">
                    Pastikan jadwal dokter dan data keluhan yang dimasukkan sudah sesuai sebelum melakukan konfirmasi.
                </DialogDescription>
            </DialogHeader>

            <!-- Ringkasan Dokter & Jadwal yang Dipilih -->
            <div v-if="schedule" class="rounded-[10px] border border-[#333333]/12 bg-[#edede2]/60 p-4 space-y-3 mt-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center rounded-[46px] bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]">
                        {{ schedule.poli?.name_poli || schedule.poli?.name || 'Poliklinik' }}
                    </span>
                    <span class="text-xs font-medium text-[#333333] bg-[#ffffff] border border-[#333333]/10 px-2.5 py-0.5 rounded-[46px]">
                        Kuota: {{ schedule.quota_day ?? schedule.quota ?? 'Tersedia' }} Pasien
                    </span>
                </div>

                <div>
                    <h4 class="font-['ivypresto-headline'] font-semibold text-lg text-[#000000] leading-tight">
                        {{ schedule.doctor?.name || 'Dokter Spesialis' }}
                    </h4>
                    <p class="text-xs font-medium text-[#333333] mt-0.5">
                        {{ getSpecializationLabel(schedule) }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-1 border-t border-[#333333]/10 text-xs text-[#333333]">
                    <div class="flex items-center gap-1.5">
                        <Calendar class="size-3.5 text-[#000000]" />
                        <span>Hari {{ schedule.day || schedule.day_of_week }}</span>
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
                    <label class="text-xs font-semibold text-[#000000]">Nama Pasien Terdaftar</label>
                    <div class="flex items-center gap-2.5 rounded-[7px] border border-[#333333]/15 bg-[#ffffff] px-3.5 py-2.5 text-sm text-[#000000]">
                        <User class="size-4 text-[#333333]" />
                        <span class="font-medium">{{ user?.name || 'Pasien' }}</span>
                    </div>
                </div>

                <!-- Input Tanggal Kunjungan -->
                <div class="space-y-1.5">
                    <label for="appointment_date" class="text-xs font-semibold text-[#000000]">
                        Tanggal Rencana Kunjungan <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="appointment_date"
                        v-model="form.appointment_date"
                        type="date"
                        required
                        class="w-full min-h-[44px] rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3.5 py-2.5 text-sm text-[#000000] focus:border-[#000000] focus:outline-none focus:ring-2 focus:ring-[#000000]"
                        :class="{ 'border-red-500': form.errors.appointment_date }"
                    />
                    <p v-if="form.errors.appointment_date" class="text-xs text-red-600 flex items-center gap-1 mt-1">
                        <AlertCircle class="size-3.5 shrink-0" />
                        {{ form.errors.appointment_date }}
                    </p>
                </div>

                <!-- Input Keluhan Pasien -->
                <div class="space-y-1.5">
                    <label for="complaint" class="text-xs font-semibold text-[#000000]">
                        Keluhan Singkat / Gejala
                    </label>
                    <textarea
                        id="complaint"
                        v-model="form.complaint"
                        rows="3"
                        placeholder="Tuliskan keluhan atau gejala yang dirasakan..."
                        class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-sm text-[#000000] placeholder:text-[#333333]/40 focus:border-[#000000] focus:outline-none focus:ring-2 focus:ring-[#000000]"
                        :class="{ 'border-red-500': form.errors.complaint }"
                    ></textarea>
                    <p v-if="form.errors.complaint" class="text-xs text-red-600 flex items-center gap-1 mt-1">
                        <AlertCircle class="size-3.5 shrink-0" />
                        {{ form.errors.complaint }}
                    </p>
                </div>

                <!-- Pesan Error Global Backend -->
                <div v-if="form.errors.doctor_schedule_id" class="rounded-[7px] bg-red-50 p-3 text-xs text-red-700 border border-red-200 flex items-center gap-2">
                    <AlertCircle class="size-4 shrink-0" />
                    <span>{{ form.errors.doctor_schedule_id }}</span>
                </div>

                <!-- Footer Tombol Aksi -->
                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end pt-3 border-t border-[#333333]/10">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="closeModal"
                        class="min-h-[44px] w-full sm:w-auto rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-5 py-2.5 text-sm font-medium text-[#333333] hover:bg-[#edede2] transition-colors"
                    >
                        Batal
                    </motion.button>
                    <motion.button
                        type="submit"
                        :disabled="form.processing"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-sm font-medium text-[#ffffff] hover:bg-[#333333] disabled:opacity-50 transition-colors"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin text-white" />
                        <span>{{ form.processing ? 'Memproses...' : 'Konfirmasi Reservasi' }}</span>
                    </motion.button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>