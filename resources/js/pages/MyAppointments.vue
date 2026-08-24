<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    Calendar,
    Clock,
    Loader2,
    Printer,
    Ticket,
    Trash2,
    XCircle,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import TicketSuccessModal from '@/components/TicketSuccessModal.vue'
import type {TicketData} from '@/components/TicketSuccessModal.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { home } from '@/routes'

interface AppointmentItem {
    appointment_id: number
    queue_number: string
    appointment_date: string
    complaint: string | null
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled'
    doctor_schedule: {
        start_time: string
        end_time: string
        day: string
        doctor?: {
            name: string
            specialization?: { name_specialization?: string } | string
        }
        poli?: {
            name_poli?: string
            name?: string
        }
        room?: {
            name_room?: string
        }
    }
    patient?: {
        name: string
        resident_n: string
    }
}

const props = defineProps<{
    appointments: AppointmentItem[]
}>()

const activeTab = ref<'active' | 'history'>('active')

// State modal karcis tiket
const isTicketModalOpen = ref(false)
const selectedTicket = ref<TicketData | null>(null)

// State modal konfirmasi pembatalan
const isCancelModalOpen = ref(false)
const selectedCancelItem = ref<AppointmentItem | null>(null)
const isCancelling = ref(false)

// State modal konfirmasi hapus riwayat kunjungan
const isDeleteModalOpen = ref(false)
const selectedDeleteItem = ref<AppointmentItem | null>(null)
const isDeleting = ref(false)

const formatDoctorName = (name?: string | null): string => {
    if (!name) return 'Dokter Spesialis'
    const trimmed = name.trim()
    if (/^(dr\.|drg\.|dr\s|drg\s|prof\.|prof\s)/i.test(trimmed)) {
        return trimmed
    }
    return `dr. ${trimmed}`
}

const activeAppointments = computed(() => {
    return props.appointments.filter((item) =>
        ['pending', 'confirmed', 'in_progress'].includes(item.status),
    )
})

const historyAppointments = computed(() => {
    return props.appointments.filter((item) =>
        ['completed', 'cancelled'].includes(item.status),
    )
})

const displayedAppointments = computed(() => {
    return activeTab.value === 'active'
        ? activeAppointments.value
        : historyAppointments.value
})

const openCancelDialog = (item: AppointmentItem) => {
    selectedCancelItem.value = item
    isCancelModalOpen.value = true
}

const executeCancelAppointment = () => {
    if (!selectedCancelItem.value) {
return
}

    isCancelling.value = true
    router.patch(`/appointments/${selectedCancelItem.value.appointment_id}/cancel`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isCancelling.value = false
            isCancelModalOpen.value = false
            selectedCancelItem.value = null
        },
    })
}

/**
 * Membuka dialog konfirmasi hapus riwayat kunjungan.
 * Hanya berlaku untuk item dengan status 'completed' atau 'cancelled'.
 */
const openDeleteDialog = (item: AppointmentItem) => {
    selectedDeleteItem.value = item
    isDeleteModalOpen.value = true
}

/**
 * Mengirim request DELETE ke backend untuk menghapus riwayat kunjungan.
 * Menggunakan Inertia router.delete agar state halaman diperbarui otomatis.
 */
const executeDeleteAppointment = () => {
    if (!selectedDeleteItem.value) {
return
}

    isDeleting.value = true
    router.delete(`/appointments/${selectedDeleteItem.value.appointment_id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false
            isDeleteModalOpen.value = false
            selectedDeleteItem.value = null
        },
    })
}

const handleOpenTicketModal = (item: AppointmentItem) => {
    selectedTicket.value = {
        appointment_id: item.appointment_id,
        queue_number: item.queue_number,
        doctor_name: item.doctor_schedule?.doctor?.name || 'Dokter Spesialis',
        poli_name: item.doctor_schedule?.poli?.name_poli || item.doctor_schedule?.poli?.name || 'Poliklinik',
        appointment_date: item.appointment_date,
        patient_name: item.patient?.name || 'Pasien',
        resident_n: item.patient?.resident_n,
    }
    isTicketModalOpen.value = true
}

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-900 border-amber-300'
        case 'confirmed':
        case 'in_progress':
            return 'bg-[#beedc0] text-[#000000] border-[#333333]/20'
        case 'completed':
            return 'bg-blue-100 text-blue-900 border-blue-300'
        case 'cancelled':
            return 'bg-red-100 text-red-800 border-red-200'
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending':
            return 'Menunggu Panggilan'
        case 'confirmed':
            return 'Terkonfirmasi'
        case 'in_progress':
            return 'Sedang Diperiksa'
        case 'completed':
            return 'Selesai'
        case 'cancelled':
            return 'Dibatalkan'
        default:
            return status
    }
}
</script>

<template>
    <Head title="Tiket Antrean Saya - Hospital Population" />

    <div class="w-full min-h-screen bg-[#edede2] text-[#000000] font-['Rubik'] p-6 sm:p-8 space-y-6">
        <!-- Header -->
        <motion.header
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="border-b border-[#333333]/10 bg-[#edede2]/80 backdrop-blur-md sticky top-0 z-30"
        >
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <Link :href="home()" class="flex items-center gap-3">
                    <motion.div
                        :whileHover="{ scale: 1.05 }"
                        :whileTap="{ scale: 0.95 }"
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0]"
                    >
                        <AppLogoIcon class="size-7 fill-current text-[#000000]" />
                    </motion.div>
                    <div>
                        <span class="font-['ivypresto-headline'] text-xl font-bold tracking-tight text-[#000000] block leading-tight">
                            Hospital Population
                        </span>
                        <span class="text-xs text-[#333333] tracking-wide block">
                            Daftar Reservasi & Antrean Pasien
                        </span>
                    </div>
                </Link>
            </div>
        </motion.header>

        <!-- Main Content -->
        <main class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14 space-y-8">
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut' }"
                class="space-y-2"
            >
                <h1 class="font-['ivypresto-headline'] text-3xl sm:text-4xl font-semibold text-[#000000]">
                    Tiket Antrean Saya
                </h1>
                <p class="text-sm text-[#333333]">
                    Pantau nomor antrean aktif Anda dan lihat riwayat konsultasi poliklinik terdahulu.
                </p>
            </motion.div>

            <!-- Tab Filter -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.22, delay: 0.05, ease: 'easeOut' }"
                class="flex items-center gap-2 border-b border-[#333333]/15 pb-3"
            >
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.02 }"
                    :whileTap="{ scale: 0.98 }"
                    @click="activeTab = 'active'"
                    :class="[
                        'min-h-[44px] px-5 py-2 rounded-[46px] text-xs sm:text-sm font-semibold transition-all',
                        activeTab === 'active'
                            ? 'bg-[#000000] text-white shadow-sm'
                            : 'bg-[#ffffff] text-[#333333] border border-[#333333]/15 hover:bg-[#edede2]'
                    ]"
                >
                    Antrean Aktif ({{ activeAppointments.length }})
                </motion.button>

                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.02 }"
                    :whileTap="{ scale: 0.98 }"
                    @click="activeTab = 'history'"
                    :class="[
                        'min-h-[44px] px-5 py-2 rounded-[46px] text-xs sm:text-sm font-semibold transition-all',
                        activeTab === 'history'
                            ? 'bg-[#000000] text-white shadow-sm'
                            : 'bg-[#ffffff] text-[#333333] border border-[#333333]/15 hover:bg-[#edede2]'
                    ]"
                >
                    Riwayat Kunjungan ({{ historyAppointments.length }})
                </motion.button>
            </motion.div>

            <!-- Card List Antrean -->
            <div v-if="displayedAppointments.length > 0" class="space-y-4">
                <motion.div
                    v-for="item in displayedAppointments"
                    :key="item.appointment_id"
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :whileHover="{ scale: 1.01, y: -2 }"
                    :transition="{ duration: 0.22, ease: 'easeOut' }"
                    class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6"
                >
                    <!-- Sisi Kiri: Nomor Antrean & Info -->
                    <div class="flex items-start sm:items-center gap-5">
                        <div class="flex flex-col items-center justify-center rounded-[10px] bg-[#ffffff] border border-[#333333]/15 p-4 min-w-[110px] text-center shrink-0">
                            <span class="text-[10px] uppercase font-bold text-[#333333]/70 tracking-wider">Nomor</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-[#000000] font-mono mt-0.5">
                                {{ item.queue_number }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]">
                                    {{ item.doctor_schedule?.poli?.name_poli || 'Poliklinik' }}
                                </span>
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border', getStatusBadgeClass(item.status)]">
                                    {{ getStatusLabel(item.status) }}
                                </span>
                            </div>

                            <h3 class="font-['ivypresto-headline'] text-xl font-semibold text-[#000000]">
                                {{ formatDoctorName(item.doctor_schedule?.doctor?.name) }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-[#333333]/80 pt-1">
                                <span class="flex items-center gap-1">
                                    <Calendar class="size-3.5 text-[#000000]" />
                                    {{ item.appointment_date }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Clock class="size-3.5 text-[#000000]" />
                                    {{ item.doctor_schedule?.start_time }} - {{ item.doctor_schedule?.end_time }} WIB
                                </span>
                            </div>

                            <p v-if="item.complaint" class="text-xs text-[#444440] italic pt-1">
                                "Keluhan: {{ item.complaint }}"
                            </p>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Aksi -->
                    <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.95 }"
                            @click="handleOpenTicketModal(item)"
                            class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors"
                        >
                            <Printer class="size-3.5" />
                            <span>Lihat Karcis</span>
                        </motion.button>

                        <!-- Tombol Batalkan — hanya muncul pada antrean aktif (pending/confirmed) -->
                        <motion.button
                            v-if="['pending', 'confirmed'].includes(item.status)"
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.95 }"
                            @click="openCancelDialog(item)"
                            class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition-colors"
                        >
                            <XCircle class="size-3.5" />
                            <span>Batalkan</span>
                        </motion.button>

                        <!-- Tombol Hapus Riwayat — hanya muncul pada tab riwayat (completed/cancelled) -->
                        <motion.button
                            v-if="['completed', 'cancelled'].includes(item.status)"
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.95 }"
                            @click="openDeleteDialog(item)"
                            class="min-h-[44px] inline-flex items-center justify-center gap-1.5 rounded-[40.5px] border border-[#333333]/15 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition-colors"
                        >
                            <Trash2 class="size-3.5" />
                            <span>Hapus</span>
                        </motion.button>
                    </div>
                </motion.div>
            </div>

            <!-- Empty State -->
            <motion.div
                v-else
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 0.22, ease: 'easeOut' }"
                class="text-center py-16 px-6 bg-[#fffff3] rounded-[10px] border border-dashed border-[#333333]/20 max-w-lg mx-auto space-y-4"
            >
                <div class="w-14 h-14 rounded-full bg-[#beedc0] flex items-center justify-center mx-auto">
                    <Ticket class="size-8 text-[#000000]" />
                </div>
                <h3 class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                    {{ activeTab === 'active' ? 'Tidak Ada Antrean Aktif' : 'Belum Ada Riwayat Kunjungan' }}
                </h3>
                <p class="text-sm text-[#333333]">
                    {{ activeTab === 'active' ? 'Anda belum memiliki tiket antrean yang sedang berjalan hari ini.' : 'Semua antrean yang telah selesai atau dibatalkan akan tercatat di sini.' }}
                </p>
                <Link
                    v-if="activeTab === 'active'"
                    href="/schedule"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-sm font-medium text-white hover:bg-[#333333] transition-colors"
                >
                    Lihat Jadwal Dokter
                </Link>
            </motion.div>
        </main>

        <!-- 1. Modal Slip Tiket -->
        <TicketSuccessModal
            v-model:open="isTicketModalOpen"
            :ticket="selectedTicket"
        />

        <!-- 2. Modal Konfirmasi Pembatalan Antrean Bertema Evergreen -->
        <Dialog :open="isCancelModalOpen" @update:open="isCancelModalOpen = $event">
            <DialogContent class="sm:max-w-[440px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000] shadow-2xl rounded-[10px] font-['Rubik']">
                <DialogHeader class="text-left space-y-2 pb-2 border-b border-[#333333]/10">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-700 shrink-0">
                            <AlertTriangle class="size-5" />
                        </span>
                        <DialogTitle class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                            Batalkan Reservasi?
                        </DialogTitle>
                    </div>
                    <DialogDescription class="text-xs text-[#333333]/80 leading-relaxed pt-1">
                        Tindakan ini akan membatalkan nomor antrean Anda. Kuota dokter akan dialihkan kembali untuk pasien lain.
                    </DialogDescription>
                </DialogHeader>

                <!-- Ringkasan Tiket yang akan dibatalkan -->
                <div v-if="selectedCancelItem" class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 space-y-1.5 text-xs text-[#333333] my-2">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Nomor Antrean:</span>
                        <span class="font-bold text-[#000000] font-mono text-sm">{{ selectedCancelItem.queue_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Dokter:</span>
                        <span class="font-semibold text-[#000000]">{{ formatDoctorName(selectedCancelItem.doctor_schedule?.doctor?.name) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Tanggal:</span>
                        <span class="font-semibold text-[#000000]">{{ selectedCancelItem.appointment_date }}</span>
                    </div>
                </div>

                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end pt-3 border-t border-[#333333]/10">
                    <motion.button
                        type="button"
                        :disabled="isCancelling"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="isCancelModalOpen = false"
                        class="min-h-[44px] w-full sm:w-auto rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors disabled:opacity-50"
                    >
                        Kembali
                    </motion.button>
                    <motion.button
                        type="button"
                        :disabled="isCancelling"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="executeCancelAppointment"
                        class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-red-600 px-5 py-2 text-xs font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        <Loader2 v-if="isCancelling" class="size-3.5 animate-spin" />
                        <span>{{ isCancelling ? 'Membatalkan...' : 'Ya, Batalkan Antrean' }}</span>
                    </motion.button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- 3. Modal Konfirmasi Hapus Riwayat Kunjungan -->
        <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
            <DialogContent class="sm:max-w-[440px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000] shadow-2xl rounded-[10px] font-['Rubik']">
                <DialogHeader class="text-left space-y-2 pb-2 border-b border-[#333333]/10">
                    <div class="flex items-center gap-2.5">
                        <motion.div
                            :initial="{ scale: 0.8, opacity: 0 }"
                            :animate="{ scale: 1, opacity: 1 }"
                            :transition="{ duration: 0.25, ease: 'easeOut' }"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-700 shrink-0"
                        >
                            <Trash2 class="size-5" />
                        </motion.div>
                        <DialogTitle class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                            Hapus Riwayat Kunjungan?
                        </DialogTitle>
                    </div>
                    <DialogDescription class="text-xs text-[#333333]/80 leading-relaxed pt-1">
                        Data riwayat ini akan dihapus secara permanen dan tidak dapat dikembalikan.
                    </DialogDescription>
                </DialogHeader>

                <!-- Ringkasan Riwayat yang akan dihapus -->
                <motion.div
                    v-if="selectedDeleteItem"
                    :initial="{ opacity: 0, y: 8 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.2, ease: 'easeOut' }"
                    class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 space-y-1.5 text-xs text-[#333333] my-2"
                >
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Nomor Antrean:</span>
                        <span class="font-bold text-[#000000] font-mono text-sm">{{ selectedDeleteItem.queue_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Dokter:</span>
                        <span class="font-semibold text-[#000000]">{{ formatDoctorName(selectedDeleteItem.doctor_schedule?.doctor?.name) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Tanggal:</span>
                        <span class="font-semibold text-[#000000]">{{ selectedDeleteItem.appointment_date }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-[#333333]/70">Status:</span>
                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium border', getStatusBadgeClass(selectedDeleteItem.status)]">
                            {{ getStatusLabel(selectedDeleteItem.status) }}
                        </span>
                    </div>
                </motion.div>

                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end pt-3 border-t border-[#333333]/10">
                    <motion.button
                        type="button"
                        :disabled="isDeleting"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="isDeleteModalOpen = false"
                        class="min-h-[44px] w-full sm:w-auto rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors disabled:opacity-50"
                    >
                        Kembali
                    </motion.button>
                    <motion.button
                        type="button"
                        :disabled="isDeleting"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="executeDeleteAppointment"
                        class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-red-600 px-5 py-2 text-xs font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        <Loader2 v-if="isDeleting" class="size-3.5 animate-spin" />
                        <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Riwayat' }}</span>
                    </motion.button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>