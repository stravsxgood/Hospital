<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
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
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import ConsultationModal from '@/components/ConsultationModal.vue'

interface Patient {
    patient_id: number
    name: string
    resident_n?: string
    gender?: string
    number_phone?: string
}

interface Appointment {
    appointment_id: number
    queue_number: string
    appointment_date: string
    complaint: string | null
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled'
    patient?: Patient
}

interface DoctorSchedule {
    doctor_schedule_id: number
    start_time: string
    end_time: string
    day: string
    quota_day?: number
    doctor?: { name: string; specialization?: { name_specialization?: string } }
    poli?: { name_poli?: string; name?: string }
    room?: { name_room?: string }
}

interface AvailableDate {
    date: string
    label: string
    count: number
    is_today: boolean
}

const props = defineProps<{
    schedules: DoctorSchedule[]
    allSchedules?: DoctorSchedule[]
    selectedSchedule: DoctorSchedule | null
    appointments: Appointment[]
    availableDates: AvailableDate[]
    selectedDate: string
    todayDate: string
    currentDate: string
    isDoctor?: boolean
    doctorName?: string
}>()

const isAutoNext = ref(false)
const isCallingAction = ref(false)
const isConsultationModalOpen = ref(false)
const activeConsultationAppointment = ref<any>(null)

const openConsultationModal = (appointment: any) => {
    activeConsultationAppointment.value = {
        ...appointment,
        doctorSchedule: props.selectedSchedule,
    }
    isConsultationModalOpen.value = true
}

const handleConsultationSuccess = () => {
    router.reload()
}

// Pasien yang sedang aktif diperiksa di dalam ruangan
const currentCalling = computed(() => {
    return props.appointments.find((item) => item.status === 'in_progress')
})

// Pasien yang masih menunggu panggilan
const waitingList = computed(() => {
    return props.appointments.filter((item) => ['pending', 'confirmed'].includes(item.status))
})

// Pasien yang sudah selesai diperiksa
const completedList = computed(() => {
    return props.appointments.filter((item) => item.status === 'completed')
})

const handleScheduleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement
    router.get('/doctor/queue', { 
        schedule_id: target.value,
        date: props.selectedDate,
    }, { preserveState: true })
}

const handleDateSelect = (dateStr: string) => {
    router.get('/doctor/queue', {
        schedule_id: props.selectedSchedule?.doctor_schedule_id,
        date: dateStr,
    }, { preserveState: true })
}

const handleCustomDateChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.value) {
        handleDateSelect(target.value)
    }
}

// Panggil pasien (atau panggil ulang)
const handleCall = (appointmentId: number) => {
    isCallingAction.value = true
    router.patch(`/doctor/queue/${appointmentId}/call`, {}, { 
        preserveScroll: true,
        onFinish: () => {
            setTimeout(() => {
                isCallingAction.value = false
            }, 1000)
        },
    })
}

// Panggil pasien terdepan dari daftar tunggu
const handleCallNext = () => {
    if (waitingList.value.length > 0) {
        handleCall(waitingList.value[0].appointment_id)
    }
}

// Selesaikan konsultasi pasien
const handleComplete = (appointmentId: number) => {
    const nextPatient = waitingList.value[0]
    router.patch(`/doctor/queue/${appointmentId}/complete`, {}, { 
        preserveScroll: true,
        onSuccess: () => {
            // Jika mode otomatis aktif dan ada antrean berikutnya, langsung panggil otomatis
            if (isAutoNext.value && nextPatient) {
                setTimeout(() => {
                    handleCall(nextPatient.appointment_id)
                }, 800)
            }
        },
    })
}

const handleSkip = (appointmentId: number) => {
    router.patch(`/doctor/queue/${appointmentId}/skip`, {}, { preserveScroll: true })
}

const toggleAutoNext = () => {
    isAutoNext.value = !isAutoNext.value
    localStorage.setItem('doctor_auto_next', isAutoNext.value ? 'true' : 'false')
}

const formatDateLabel = (dateStr: string): string => {
    if (!dateStr) return '-'
    const clean = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr
    const parts = clean.split('-')
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`
    }
    return dateStr
}

const formatDoctorName = (name?: string | null): string => {
    if (!name) return 'Dokter Spesialis'
    const trimmed = name.trim()
    if (/^(dr\.|drg\.|dr\s|drg\s|prof\.|prof\s)/i.test(trimmed)) {
        return trimmed
    }
    return `dr. ${trimmed}`
}

const handleGlobalKeydown = (e: KeyboardEvent) => {
    // F2: Panggil antrean berikutnya / panggil ulang antrean aktif
    if (e.key === 'F2') {
        e.preventDefault()
        if (currentCalling.value) {
            handleCall(currentCalling.value.appointment_id)
        } else if (waitingList.value.length > 0) {
            handleCall(waitingList.value[0].appointment_id)
        }
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown)
    const savedAutoNext = localStorage.getItem('doctor_auto_next')
    if (savedAutoNext === 'true') {
        isAutoNext.value = true
    }
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown)
})
</script>

<template>
    <Head title="Papan Antrean Dokter - Hospital Population" />

    <div class="w-full min-h-screen bg-[#edede2] text-[#000000] font-['Rubik'] p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Top Bar Header -->
        <header class="border-b border-[#333333]/10 bg-[#edede2]/90 backdrop-blur-md sticky top-0 z-30 py-3 -mt-4 sm:-mt-6 lg:-mt-8 mb-6">
            <div class="max-w-[1350px] mx-auto px-2 sm:px-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        href="/staff"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#fffff3] border border-[#333333]/15 text-[#000000] hover:bg-[#beedc0] transition-colors"
                        title="Kembali ke Dashboard Staf"
                    >
                        <ArrowLeft class="size-5" />
                    </Link>
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0] shrink-0">
                        <AppLogoIcon class="size-7 fill-current text-[#000000]" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-['ivypresto-headline'] text-xl font-bold tracking-tight text-[#000000] block leading-tight">
                                Panel Panggilan Antrean
                            </span>
                            <span v-if="isDoctor" class="inline-flex items-center gap-1 rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[11px] font-semibold text-[#000000] border border-[#333333]/15">
                                <Stethoscope class="size-3" />
                                Dokter Praktik
                            </span>
                        </div>
                        <span class="text-xs text-[#333333] tracking-wide block">
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
                        class="min-h-[40px] inline-flex items-center gap-2 rounded-[40.5px] border px-3.5 py-1.5 text-xs font-semibold transition-all cursor-pointer shadow-xs"
                        :class="isAutoNext ? 'bg-[#beedc0] text-[#000000] border-[#333333]/30' : 'bg-[#ffffff] text-[#333333] border-[#333333]/20 hover:bg-[#edede2]'"
                        title="Setelah selesai konsultasi, sistem langsung memanggil pasien antrean berikutnya"
                    >
                        <component :is="isAutoNext ? ToggleRight : ToggleLeft" class="size-4" />
                        <span>Otomatis Panggil Berikutnya: <strong>{{ isAutoNext ? 'ON' : 'OFF' }}</strong></span>
                    </button>

                    <Link
                        href="/staff"
                        class="min-h-[40px] inline-flex items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors"
                    >
                        <LayoutGrid class="size-3.5" />
                        <span>Dashboard Staf</span>
                    </Link>

                    <Link
                        href="/display"
                        target="_blank"
                        class="min-h-[40px] inline-flex items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors"
                    >
                        <Monitor class="size-3.5" />
                        <span>Layar TV Antrean</span>
                    </Link>

                    <!-- Dropdown Pilih Jadwal Dokter -->
                    <div class="flex items-center gap-2">
                        <select
                            :value="selectedSchedule?.doctor_schedule_id"
                            @change="handleScheduleChange"
                            class="min-h-[40px] rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-xs font-medium text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none max-w-[280px] sm:max-w-[320px] truncate"
                        >
                            <option
                                v-for="sch in schedules"
                                :key="sch.doctor_schedule_id"
                                :value="sch.doctor_schedule_id"
                            >
                                {{ formatDoctorName(sch.doctor?.name) }} - {{ sch.poli?.name_poli || sch.poli?.name }} (Hari {{ sch.day }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="max-w-[1350px] mx-auto space-y-6">
            <!-- Filter Tanggal & Ringkasan Jadwal Terpilih -->
            <div class="rounded-[10px] bg-[#fffff3] border border-[#333333]/15 p-5 shadow-sm space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#333333]/10 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]">
                                {{ selectedSchedule?.poli?.name_poli || selectedSchedule?.poli?.name || 'Poliklinik' }}
                            </span>
                            <span class="text-xs font-medium text-[#333333] bg-[#ffffff] border border-[#333333]/15 px-2.5 py-0.5 rounded-full">
                                {{ selectedSchedule?.room?.name_room || 'Ruang Periksa' }}
                            </span>
                            <span class="text-xs font-medium text-[#333333] bg-[#ffffff] border border-[#333333]/15 px-2.5 py-0.5 rounded-full">
                                Kuota: {{ selectedSchedule?.quota_day ?? 30 }} Pasien
                            </span>
                        </div>
                        <h2 class="font-['ivypresto-headline'] text-2xl font-bold text-[#000000]">
                            {{ formatDoctorName(selectedSchedule?.doctor?.name) }}
                        </h2>
                        <p class="text-xs text-[#333333]">
                            {{ selectedSchedule?.doctor?.specialization?.name_specialization || 'Spesialis' }} · Praktik Hari {{ selectedSchedule?.day }} ({{ selectedSchedule?.start_time.substring(0, 5) }} - {{ selectedSchedule?.end_time.substring(0, 5) }} WIB)
                        </p>
                    </div>

                    <!-- Filter Tanggal Antrean Cerdas -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-[#333333] mr-1">Filter Tanggal:</span>
                        
                        <!-- Tombol Hari Ini -->
                        <button
                            type="button"
                            @click="handleDateSelect(todayDate)"
                            class="min-h-[36px] px-3.5 py-1.5 rounded-[40.5px] text-xs font-semibold transition-colors border"
                            :class="selectedDate === todayDate ? 'bg-[#000000] text-white border-[#000000]' : 'bg-[#ffffff] text-[#333333] border-[#333333]/20 hover:bg-[#edede2]'"
                        >
                            Hari Ini
                        </button>

                        <!-- Tab Tanggal yang Memiliki Reservasi Aktif -->
                        <button
                            v-for="d in availableDates.filter(item => item.date !== todayDate)"
                            :key="d.date"
                            type="button"
                            @click="handleDateSelect(d.date)"
                            class="min-h-[36px] px-3.5 py-1.5 rounded-[40.5px] text-xs font-semibold transition-colors border inline-flex items-center gap-1.5"
                            :class="selectedDate === d.date ? 'bg-[#000000] text-white border-[#000000]' : 'bg-[#ffffff] text-[#333333] border-[#333333]/20 hover:bg-[#edede2]'"
                        >
                            <span>{{ d.label }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedDate === d.date ? 'bg-[#beedc0] text-[#000000]' : 'bg-[#edede2] text-[#000000]'">
                                {{ d.count }}
                            </span>
                        </button>

                        <!-- Tombol Semua Antrean -->
                        <button
                            type="button"
                            @click="handleDateSelect('all')"
                            class="min-h-[36px] px-3.5 py-1.5 rounded-[40.5px] text-xs font-semibold transition-colors border"
                            :class="selectedDate === 'all' ? 'bg-[#000000] text-white border-[#000000]' : 'bg-[#ffffff] text-[#333333] border-[#333333]/20 hover:bg-[#edede2]'"
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                    <div class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-full bg-[#beedc0] flex items-center justify-center shrink-0">
                            <Users class="size-5 text-[#000000]" />
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-[#333333]/70 block">Total Pasien Terdaftar</span>
                            <span class="text-xl font-bold font-mono text-[#000000]">{{ appointments.length }} Pasien</span>
                        </div>
                    </div>

                    <div class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                            <Clock class="size-5 text-amber-800" />
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-[#333333]/70 block">Menunggu Giliran</span>
                            <span class="text-xl font-bold font-mono text-amber-800">{{ waitingList.length }} Pasien</span>
                        </div>
                    </div>

                    <div class="rounded-[8px] bg-[#edede2]/60 border border-[#333333]/10 p-3.5 flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                            <CheckCircle2 class="size-5 text-blue-800" />
                        </div>
                        <div>
                            <span class="text-[11px] font-medium text-[#333333]/70 block">Selesai Pemeriksaan</span>
                            <span class="text-xl font-bold font-mono text-blue-800">{{ completedList.length }} Pasien</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Sorotan Utama: Pasien Yang Sedang Diperiksa -->
            <div class="rounded-[10px] bg-[#fffff3] border-2 border-[#000000] p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-[#333333]/10 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3.5 w-3.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs uppercase font-bold tracking-wider text-[#333333]">Ruang Konsultasi Aktif</span>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 bg-[#beedc0] rounded-full text-[#000000]">
                        {{ selectedSchedule?.room?.name_room || 'Ruang Periksa' }}
                    </span>
                </div>

                <!-- Konten Pasien Aktif -->
                <div v-if="currentCalling" class="py-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-start sm:items-center gap-6">
                        <div class="flex flex-col items-center justify-center rounded-[10px] bg-[#000000] text-white p-5 min-w-[130px] text-center shadow-inner">
                            <span class="text-[11px] uppercase tracking-wider text-[#beedc0] font-semibold">Nomor Urut</span>
                            <span class="text-4xl font-extrabold font-mono mt-1">{{ currentCalling.queue_number }}</span>
                        </div>

                        <div class="space-y-1.5">
                            <h2 class="font-['ivypresto-headline'] text-2xl sm:text-3xl font-semibold text-[#000000]">
                                {{ currentCalling.patient?.name }}
                            </h2>
                            <div class="flex flex-wrap gap-4 text-xs text-[#333333]">
                                <span><strong>Tanggal Kunjungan:</strong> {{ formatDateLabel(currentCalling.appointment_date) }}</span>
                                <span><strong>NIK:</strong> {{ currentCalling.patient?.resident_n || '-' }}</span>
                                <span><strong>Kelamin:</strong> {{ currentCalling.patient?.gender || '-' }}</span>
                                <span><strong>Telepon:</strong> {{ currentCalling.patient?.number_phone || '-' }}</span>
                            </div>
                            <div v-if="currentCalling.complaint" class="mt-2 rounded-[6px] bg-[#edede2] p-3 text-xs text-[#333333]">
                                <strong>Keluhan Pasien:</strong> {{ currentCalling.complaint }}
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Dokter (Bisa Dipanggil Berulang Kali) -->
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <!-- Tombol Buka EMR & Resep -->
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.97 }"
                            @click="openConsultationModal(currentCalling)"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#222222] transition-all shadow-md cursor-pointer"
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
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] border-2 border-[#000000] bg-[#beedc0] px-5 py-2 text-xs font-bold text-[#000000] hover:bg-[#a6e5a8] transition-all shadow-xs disabled:opacity-50 cursor-pointer"
                            title="Panggil ulang nomor antrean ini dan bunyikan suara di layar TV"
                        >
                            <BellRing class="size-4 text-[#000000]" :class="isCallingAction ? 'animate-spin' : 'animate-bounce'" />
                            <span>{{ isCallingAction ? 'Memanggil...' : 'Panggil Ulang' }}</span>
                        </motion.button>

                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            @click="handleSkip(currentCalling.appointment_id)"
                            class="min-h-[44px] inline-flex items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 py-2 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors cursor-pointer"
                        >
                            <Forward class="size-4" />
                            <span>Lewati</span>
                        </motion.button>

                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            @click="handleComplete(currentCalling.appointment_id)"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-5 py-2.5 text-xs font-semibold text-[#333333] hover:bg-[#edede2] transition-colors cursor-pointer"
                        >
                            <UserCheck class="size-4" />
                            <span>Selesai Saja</span>
                        </motion.button>
                    </div>
                </div>

                <!-- Kosong: Belum Ada Pasien Yang Dipanggil -->
                <div v-else class="py-10 text-center space-y-3">
                    <p class="text-base font-semibold text-[#000000]">Ruang periksa sedang kosong.</p>
                    <p class="text-xs text-[#333333]/70">Pilih salah satu nomor antrean dari daftar tunggu di bawah atau tekan tombol panggil terdepan.</p>
                    
                    <div v-if="waitingList.length > 0">
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.03 }"
                            :whileTap="{ scale: 0.97 }"
                            @click="handleCallNext"
                            class="min-h-[44px] inline-flex items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-bold text-white hover:bg-[#222222] transition-all shadow-md cursor-pointer"
                        >
                            <Bell class="size-4 text-[#beedc0] animate-bounce" />
                            <span>Panggil Antrean Terdepan ({{ waitingList[0]?.queue_number }} - {{ waitingList[0]?.patient?.name }})</span>
                        </motion.button>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Tunggu Pasien -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000]">
                        Daftar Antrean Menunggu
                    </h3>
                    <span class="text-xs text-[#333333] font-medium">{{ waitingList.length }} Pasien Siap Dipanggil</span>
                </div>

                <div v-if="waitingList.length > 0" class="overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3]">
                    <table class="w-full text-left text-xs text-[#333333]">
                        <thead class="bg-[#edede2] border-b border-[#333333]/10 text-[#000000] uppercase font-semibold text-[11px]">
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
                                class="hover:bg-[#edede2]/40 transition-colors"
                            >
                                <td class="px-5 py-4 font-mono font-bold text-sm text-[#000000]">
                                    {{ item.queue_number }}
                                </td>
                                <td class="px-5 py-4 font-medium text-[#333333]">
                                    {{ formatDateLabel(item.appointment_date) }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#000000]">
                                    {{ item.patient?.name }}
                                </td>
                                <td class="px-5 py-4 max-w-xs truncate text-[#333333]">
                                    {{ item.complaint || '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-medium text-amber-900 border border-amber-300">
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right space-x-2">
                                    <motion.button
                                        type="button"
                                        :whileHover="{ scale: 1.03 }"
                                        :whileTap="{ scale: 0.97 }"
                                        @click="openConsultationModal(item)"
                                        class="min-h-[36px] inline-flex items-center gap-1 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-3.5 py-1.5 text-xs font-semibold text-[#000000] hover:bg-[#beedc0] transition-colors cursor-pointer"
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
                                        class="min-h-[36px] inline-flex items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#333333] transition-colors cursor-pointer"
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
                <div v-else class="text-center py-12 rounded-[10px] border border-dashed border-[#333333]/20 bg-[#fffff3]">
                    <CheckCircle2 class="size-8 mx-auto text-[#000000]/40" />
                    <p class="text-sm font-medium text-[#000000] mt-2">Tidak ada pasien dalam antrean tunggu untuk tanggal terpilih.</p>
                    <p class="text-xs text-[#333333]/70">Gunakan tab filter tanggal di atas atau pilih tanggal lain untuk melihat data reservasi jadwal mendatang.</p>
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