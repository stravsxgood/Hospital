<script setup lang="ts">
/**
 * @file TicketSuccessModal.vue
 * @description Presentational modal displaying patient queue ticket slip upon successful booking.
 * Designed strictly following DESIGN.md (Evergreen theme) and GEMINI.md motion guidelines.
 */
import { Link } from '@inertiajs/vue3'
import { CheckCircle2, Printer, Ticket } from '@lucide/vue'
import { motion } from 'motion-v'
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

export interface TicketData {
    appointment_id?: number
    queue_number: string
    doctor_name: string
    poli_name: string
    appointment_date: string
    patient_name: string
    resident_n?: string
}

defineProps<{
    open: boolean
    ticket: TicketData | null
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
}>()

const handlePrint = () => {
    window.print()
}

const formatDoctorName = (name?: string | null): string => {
    if (!name) return '-'
    const trimmed = name.trim()
    if (/^(dr\.|drg\.|dr\s|drg\s|prof\.|prof\s)/i.test(trimmed)) {
        return trimmed
    }
    return `dr. ${trimmed}`
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[480px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000] shadow-2xl rounded-[10px] font-['Rubik']">
            <!-- Header Notifikasi -->
            <DialogHeader class="text-center space-y-2 pb-2">
                <motion.div
                    :initial="{ scale: 0.8, opacity: 0 }"
                    :animate="{ scale: 1, opacity: 1 }"
                    :transition="{ duration: 0.25, ease: 'easeOut' }"
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#beedc0]"
                >
                    <CheckCircle2 class="size-6 text-[#000000]" />
                </motion.div>
                <DialogTitle class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                    Reservasi Berhasil Dibuat
                </DialogTitle>
                <p class="text-xs text-[#333333]/80 leading-relaxed">
                    Simpan atau cetak nomor antrean ini untuk ditunjukkan kepada petugas saat tiba di poliklinik.
                </p>
            </DialogHeader>

            <!-- Karcis Tiket Antrean (Slip Style) -->
            <motion.div
                v-if="ticket"
                id="printable-ticket"
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut' }"
                class="mt-3 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-5 shadow-sm space-y-4 relative overflow-hidden"
            >
                <!-- Watermark Background Accent -->
                <div class="absolute -right-6 -bottom-6 opacity-5 pointer-events-none" aria-hidden="true">
                    <Ticket class="size-36 text-[#000000]" />
                </div>

                <!-- Bagian Atas Karcis: Nomor Antrean & Poliklinik -->
                <div class="text-center pb-4 border-b border-dashed border-[#333333]/20 space-y-1">
                    <span class="inline-flex items-center rounded-[46px] bg-[#beedc0] px-3.5 py-0.5 text-xs font-semibold text-[#000000]">
                        {{ ticket.poli_name || 'Poliklinik' }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-extrabold tracking-wider text-[#000000] font-mono py-1.5">
                        {{ ticket.queue_number }}
                    </div>
                    <span class="text-[11px] text-[#333333]/70 uppercase font-medium tracking-wide block">
                        Nomor Urut Panggilan
                    </span>
                </div>

                <!-- Rincian Data Pasien & Dokter -->
                <div class="space-y-2.5 text-xs text-[#333333]">
                    <div class="flex items-center justify-between">
                        <span class="text-[#333333]/70">Nama Pasien</span>
                        <span class="font-semibold text-[#000000]">{{ ticket.patient_name }}</span>
                    </div>

                    <div v-if="ticket.resident_n" class="flex items-center justify-between">
                        <span class="text-[#333333]/70">NIK Pasien</span>
                        <span class="font-medium text-[#000000]">{{ ticket.resident_n }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#333333]/70">Dokter Pemeriksa</span>
                        <span class="font-semibold text-[#000000]">{{ formatDoctorName(ticket.doctor_name) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#333333]/70">Tanggal Kunjungan</span>
                        <span class="font-semibold text-[#000000]">{{ ticket.appointment_date }}</span>
                    </div>
                </div>

                <!-- Catatan Kehadiran -->
                <div class="rounded-[7px] bg-[#edede2] p-2.5 text-[11px] leading-relaxed text-[#333333]">
                    * Harap datang 15 menit sebelum jam praktik dimulai dan lakukan konfirmasi kehadiran di loket pendaftaran.
                </div>
            </motion.div>

            <!-- Tombol Aksi Karcis -->
            <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between pt-4 border-t border-[#333333]/10">
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.02 }"
                    :whileTap="{ scale: 0.98 }"
                    @click="emit('update:open', false)"
                    class="min-h-[44px] w-full sm:w-auto rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-medium text-[#333333] hover:bg-[#edede2] transition-colors"
                >
                    Tutup
                </motion.button>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="handlePrint"
                        class="min-h-[44px] flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors"
                    >
                        <Printer class="size-3.5 text-[#000000]" />
                        <span>Cetak Tiket</span>
                    </motion.button>

                    <motion.div
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        class="flex-1 sm:flex-initial"
                    >
                        <Link
                            href="/my-appointments"
                            class="min-h-[44px] inline-flex w-full items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-semibold text-white hover:bg-[#333333] transition-colors"
                        >
                            <span>Lihat Antrean</span>
                        </Link>
                    </motion.div>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>