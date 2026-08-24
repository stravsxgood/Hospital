<script setup lang="ts">
/**
 * @file Index.vue (Koas Clinical Logbook)
 * @description Portofolio & Logbook Kasus Klinis Digital Mahasiswa Kedokteran / Dokter Muda (Koas).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, router } from '@inertiajs/vue3'
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    Award,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    Edit3,
    FileText,
    GraduationCap,
    HeartPulse,
    Loader2,
    MessageSquare,
    Plus,
    Search,
    Send,
    ShieldCheck,
    Stethoscope,
    Trash2,
    User,
    X,
} from '@lucide/vue'
import axios from 'axios'
import { motion } from 'motion-v'
import { computed, ref } from 'vue'

interface DoctorOption {
    doctor_id: number
    name: string
    specialization?: { name: string }
}

interface PatientOption {
    patient_id: number
    name: string
    resident_n: string
    gender?: string
    birthday_date?: string
}

interface ClinicalLogbookItem {
    clinical_logbook_id: number
    nurse_id: number
    patient_id: number
    doctor_id: number
    medical_record_id: number | null
    activity_type: 'anamnesis' | 'physical_exam' | 'procedure_assistance' | 'case_discussion'
    case_title: string
    clinical_findings: string
    procedure_performed: string | null
    learning_reflection: string
    supervisor_feedback: string | null
    score: number | null
    status: 'draft' | 'submitted' | 'approved' | 'revision_needed'
    submitted_at: string | null
    reviewed_at: string | null
    created_at: string
    patient?: PatientOption
    doctor?: DoctorOption
}

const props = defineProps<{
    logbooks: {
        data: ClinicalLogbookItem[]
        current_page: number
        last_page: number
        total: number
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
    doctors: DoctorOption[]
    patients: PatientOption[]
    filters: {
        status?: string
        activity_type?: string
        search?: string
    }
    stats: {
        total: number
        draft: number
        submitted: number
        approved: number
        revision_needed: number
    }
}>()

// Filter State
const searchInput = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedActivityType = ref(props.filters.activity_type || '')

const applyFilters = () => {
    router.get(
        '/koas/logbook',
        {
            search: searchInput.value || undefined,
            status: selectedStatus.value || undefined,
            activity_type: selectedActivityType.value || undefined,
        },
        { preserveState: true, replace: true }
    )
}

// Modal Form State (Create / Edit)
const isModalOpen = ref(false)
const isSubmitting = ref(false)
const editingLogbook = ref<ClinicalLogbookItem | null>(null)
const formErrors = ref<Record<string, string>>({})

const form = ref({
    patient_id: '',
    doctor_id: '',
    activity_type: 'anamnesis',
    case_title: '',
    clinical_findings: '',
    procedure_performed: '',
    learning_reflection: '',
    submit_now: false,
})

const openCreateModal = () => {
    editingLogbook.value = null
    formErrors.value = {}
    form.value = {
        patient_id: props.patients[0]?.patient_id ? String(props.patients[0].patient_id) : '',
        doctor_id: props.doctors[0]?.doctor_id ? String(props.doctors[0].doctor_id) : '',
        activity_type: 'anamnesis',
        case_title: '',
        clinical_findings: '',
        procedure_performed: '',
        learning_reflection: '',
        submit_now: false,
    }
    isModalOpen.value = true
}

const openEditModal = (item: ClinicalLogbookItem) => {
    if (item.status === 'approved' || item.status === 'submitted') return
    editingLogbook.value = item
    formErrors.value = {}
    form.value = {
        patient_id: String(item.patient_id),
        doctor_id: String(item.doctor_id),
        activity_type: item.activity_type,
        case_title: item.case_title,
        clinical_findings: item.clinical_findings,
        procedure_performed: item.procedure_performed || '',
        learning_reflection: item.learning_reflection,
        submit_now: false,
    }
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    editingLogbook.value = null
}

const handleSaveLogbook = async (submitToDpjp: boolean) => {
    form.value.submit_now = submitToDpjp
    formErrors.value = {}
    isSubmitting.value = true

    try {
        if (editingLogbook.value) {
            await axios.put(`/koas/logbook/${editingLogbook.value.clinical_logbook_id}`, form.value)
        } else {
            await axios.post('/koas/logbook', form.value)
        }
        closeModal()
        router.reload()
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            formErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [k, (v as string[])[0]])
            )
        } else {
            alert(err.response?.data?.message || 'Gagal menyimpan logbook klinis.')
        }
    } finally {
        isSubmitting.value = false
    }
}

// Modal Detail Feedback & Evaluasi DPJP
const selectedFeedbackItem = ref<ClinicalLogbookItem | null>(null)
const openFeedbackModal = (item: ClinicalLogbookItem) => {
    selectedFeedbackItem.value = item
}

// Submit Direct to DPJP from Table
const handleSubmitDirect = async (item: ClinicalLogbookItem) => {
    if (!confirm(`Ajukan logbook "${item.case_title}" ke DPJP sekarang?`)) return
    try {
        await axios.post(`/koas/logbook/${item.clinical_logbook_id}/submit`)
        router.reload()
    } catch (err: any) {
        alert(err.response?.data?.message || 'Gagal mengajukan logbook.')
    }
}

// Helper Activity Type Labels
const activityLabels: Record<string, { label: string; icon: any; color: string }> = {
    anamnesis: { label: 'Anamnesis Pasien', icon: Stethoscope, color: 'bg-emerald-50 text-emerald-800 border-emerald-200' },
    physical_exam: { label: 'Pemeriksaan Fisik', icon: HeartPulse, color: 'bg-blue-50 text-blue-800 border-blue-200' },
    procedure_assistance: { label: 'Asistensi Tindakan', icon: Activity, color: 'bg-purple-50 text-purple-800 border-purple-200' },
    case_discussion: { label: 'Diskusi Kasus', icon: MessageSquare, color: 'bg-amber-50 text-amber-800 border-amber-200' },
}

// Helper Status Badges
const statusBadges: Record<string, { label: string; badge: string; dot: string }> = {
    draft: { label: 'Draft (Belum Diajukan)', badge: 'bg-neutral-100 text-neutral-700 border-neutral-300', dot: 'bg-neutral-400' },
    submitted: { label: 'Menunggu Review DPJP', badge: 'bg-amber-50 text-amber-800 border-amber-300 animate-pulse', dot: 'bg-amber-500' },
    approved: { label: 'Disetujui DPJP', badge: 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold', dot: 'bg-emerald-600' },
    revision_needed: { label: 'Perlu Revisi', badge: 'bg-rose-50 text-rose-800 border-rose-300 font-bold', dot: 'bg-rose-600' },
}
</script>

<template>
    <div class="min-h-screen bg-[#edede2] px-4 py-8 font-['Rubik'] text-[#000000] sm:px-6 lg:px-8">
        <Head title="Logbook Klinis Digital Koas - Hospital Population" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header & Navigation -->
            <motion.div
                :initial="{ opacity: 0, y: -15 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-2xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-1 text-xs font-bold text-[#065f46]">
                            <GraduationCap class="size-3.5" />
                            <span>Dokter Muda / Mahasiswa Koas</span>
                        </span>
                    </div>
                    <h1 class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl">
                        Logbook Kasus Klinis Digital
                    </h1>
                    <p class="text-xs text-[#000000]/60 sm:text-sm">
                        Dokumentasikan riwayat kasus klinis, asistensi prosedur medis, dan refleksi harian dengan verifikasi resmi DPJP.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="openCreateModal"
                        class="min-h-[44px] inline-flex items-center gap-2 rounded-xl bg-[#065f46] px-5 py-2.5 text-sm font-bold text-[#ffffff] shadow-sm hover:bg-[#054d38] transition-colors cursor-pointer"
                    >
                        <Plus class="size-4 text-[#beedc0]" />
                        <span>Catat Kasus Baru</span>
                    </motion.button>
                </div>
            </motion.div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm">
                    <div class="text-xs font-semibold text-[#000000]/60 uppercase">Total Kasus</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-[#000000]">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm">
                    <div class="text-xs font-semibold text-[#000000]/60 uppercase">Draft Belum Diajukan</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-neutral-600">{{ stats.draft }}</div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-amber-800 uppercase">Menunggu Review DPJP</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-amber-800">{{ stats.submitted }}</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-emerald-800 uppercase">Disetujui & Dinilai</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-emerald-800">{{ stats.approved }}</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-col gap-3 rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-[#000000]/40" />
                    <input
                        v-model="searchInput"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari judul kasus, nama pasien, atau temuan klinis..."
                        class="min-h-[44px] w-full rounded-lg border border-[#000000]/15 bg-[#edede2]/40 pl-9 pr-4 text-sm text-[#000000] placeholder-[#000000]/40 focus:border-[#065f46] focus:outline-none"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <select
                        v-model="selectedActivityType"
                        @change="applyFilters"
                        class="min-h-[44px] rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-2 text-xs font-medium text-[#000000] focus:border-[#065f46] focus:outline-none"
                    >
                        <option value="">Semua Aktivitas Klinis</option>
                        <option value="anamnesis">Anamnesis</option>
                        <option value="physical_exam">Pemeriksaan Fisik</option>
                        <option value="procedure_assistance">Asistensi Tindakan</option>
                        <option value="case_discussion">Diskusi Kasus</option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="min-h-[44px] rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-2 text-xs font-medium text-[#000000] focus:border-[#065f46] focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Menunggu Review</option>
                        <option value="approved">Disetujui</option>
                        <option value="revision_needed">Perlu Revisi</option>
                    </select>
                </div>
            </div>

            <!-- List Timeline Kasus Klinis -->
            <div class="space-y-4" v-if="logbooks.data.length > 0">
                <motion.div
                    v-for="item in logbooks.data"
                    :key="item.clinical_logbook_id"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-sm hover:border-[#065f46]/30 transition-all flex flex-col justify-between gap-4"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Badge Activity Type -->
                                <span
                                    v-if="activityLabels[item.activity_type]"
                                    :class="activityLabels[item.activity_type].color"
                                    class="inline-flex items-center gap-1 rounded-md border px-2.5 py-0.5 text-xs font-semibold"
                                >
                                    <component :is="activityLabels[item.activity_type].icon" class="size-3.5" />
                                    <span>{{ activityLabels[item.activity_type].label }}</span>
                                </span>

                                <!-- Badge Status -->
                                <span
                                    v-if="statusBadges[item.status]"
                                    :class="statusBadges[item.status].badge"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                >
                                    <span :class="statusBadges[item.status].dot" class="size-1.5 rounded-full"></span>
                                    <span>{{ statusBadges[item.status].label }}</span>
                                </span>

                                <!-- Score Badge -->
                                <span
                                    v-if="item.score !== null"
                                    class="inline-flex items-center gap-1 rounded-md bg-[#065f46] px-2.5 py-0.5 text-xs font-bold text-[#ffffff]"
                                >
                                    <Award class="size-3 text-[#beedc0]" />
                                    <span>Nilai: {{ item.score }} / 100</span>
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-[#000000] font-serif sm:text-lg">
                                {{ item.case_title }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#000000]/60">
                                <span>Pasien: <strong class="text-[#000000]">{{ item.patient?.name || 'Pasien' }}</strong> ({{ item.patient?.resident_n || '-' }})</span>
                                <span>•</span>
                                <span>DPJP: <strong class="text-[#065f46]">{{ item.doctor?.name || 'Dokter Spesialis' }}</strong></span>
                                <span>•</span>
                                <span>Tanggal: {{ new Date(item.created_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 self-end sm:self-start">
                            <motion.button
                                v-if="item.supervisor_feedback"
                                type="button"
                                :whileTap="{ scale: 0.95 }"
                                @click="openFeedbackModal(item)"
                                class="min-h-[44px] inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-3.5 py-2 text-xs font-bold text-purple-800 hover:bg-purple-100 transition-colors cursor-pointer"
                            >
                                <MessageSquare class="size-3.5" />
                                <span>Catatan DPJP</span>
                            </motion.button>

                            <motion.button
                                v-if="item.status === 'draft' || item.status === 'revision_needed'"
                                type="button"
                                :whileTap="{ scale: 0.95 }"
                                @click="handleSubmitDirect(item)"
                                class="min-h-[44px] inline-flex items-center gap-1.5 rounded-lg bg-[#065f46] px-3.5 py-2 text-xs font-bold text-[#ffffff] hover:bg-[#054d38] transition-colors cursor-pointer"
                            >
                                <Send class="size-3.5 text-[#beedc0]" />
                                <span>Ajukan</span>
                            </motion.button>

                            <motion.button
                                v-if="item.status === 'draft' || item.status === 'revision_needed'"
                                type="button"
                                :whileTap="{ scale: 0.95 }"
                                @click="openEditModal(item)"
                                class="min-h-[44px] inline-flex items-center gap-1.5 rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-2 text-xs font-medium text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer"
                            >
                                <Edit3 class="size-3.5 text-[#065f46]" />
                                <span>Edit</span>
                            </motion.button>
                        </div>
                    </div>

                    <!-- Clinical Findings & Reflection Excerpt -->
                    <div class="rounded-xl bg-[#edede2]/50 p-4 text-xs text-[#000000]/80 space-y-2 border border-[#000000]/5">
                        <div>
                            <span class="font-bold text-[#000000]">Temuan Klinis: </span>
                            <span>{{ item.clinical_findings }}</span>
                        </div>
                        <div v-if="item.procedure_performed">
                            <span class="font-bold text-[#000000]">Tindakan Medis: </span>
                            <span>{{ item.procedure_performed }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-[#065f46]">Refleksi Pembelajaran: </span>
                            <span class="italic text-[#000000]/70">"{{ item.learning_reflection }}"</span>
                        </div>
                    </div>
                </motion.div>
            </div>

            <!-- Empty State -->
            <div class="rounded-2xl border border-dashed border-[#000000]/20 bg-[#fffff3] p-12 text-center" v-else>
                <BookOpen class="size-12 text-[#000000]/30 mx-auto mb-3" />
                <h3 class="font-serif text-lg font-bold text-[#000000]">Belum Ada Kasus Klinis</h3>
                <p class="text-xs text-[#000000]/60 max-w-md mx-auto mt-1 mb-4">
                    Mulailah mencatat kasus anamnesis, pemeriksaan fisik, atau asistensi tindakan medis selama rotasi stase Anda.
                </p>
                <button
                    type="button"
                    @click="openCreateModal"
                    class="min-h-[44px] inline-flex items-center gap-2 rounded-xl bg-[#065f46] px-5 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#054d38] cursor-pointer"
                >
                    <Plus class="size-4 text-[#beedc0]" />
                    <span>Catat Kasus Pertama</span>
                </button>
            </div>
        </div>

        <!-- Modal Catat / Edit Kasus Klinis -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#000000]/50 backdrop-blur-xs">
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-2xl rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 sm:p-8 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between border-b border-[#000000]/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-[#beedc0] text-[#065f46] flex items-center justify-center">
                            <BookOpen class="size-5" />
                        </div>
                        <div>
                            <h2 class="font-serif text-lg font-bold text-[#000000]">
                                {{ editingLogbook ? 'Edit Logbook Klinis' : 'Catat Kasus Klinis Baru' }}
                            </h2>
                            <p class="text-xs text-[#000000]/60">Isi data kasus dengan lengkap untuk verifikasi DPJP</p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal" class="p-2 text-[#000000]/50 hover:text-[#000000]">
                        <X class="size-5" />
                    </button>
                </div>

                <div class="space-y-4 text-xs sm:text-sm">
                    <!-- Pasien & Dokter DPJP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-[#000000] mb-1">Pasien Kasus *</label>
                            <select
                                v-model="form.patient_id"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                            >
                                <option v-for="p in patients" :key="p.patient_id" :value="String(p.patient_id)">
                                    {{ p.name }} (NIK: {{ p.resident_n }})
                                </option>
                            </select>
                            <p v-if="formErrors.patient_id" class="text-xs text-rose-600 mt-1">{{ formErrors.patient_id }}</p>
                        </div>

                        <div>
                            <label class="block font-bold text-[#000000] mb-1">Dokter DPJP Pembimbing *</label>
                            <select
                                v-model="form.doctor_id"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                            >
                                <option v-for="d in doctors" :key="d.doctor_id" :value="String(d.doctor_id)">
                                    {{ d.name }} ({{ d.specialization?.name || 'Spesialis' }})
                                </option>
                            </select>
                            <p v-if="formErrors.doctor_id" class="text-xs text-rose-600 mt-1">{{ formErrors.doctor_id }}</p>
                        </div>
                    </div>

                    <!-- Jenis Aktivitas & Judul Kasus -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block font-bold text-[#000000] mb-1">Jenis Aktivitas *</label>
                            <select
                                v-model="form.activity_type"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                            >
                                <option value="anamnesis">Anamnesis</option>
                                <option value="physical_exam">Pemeriksaan Fisik</option>
                                <option value="procedure_assistance">Asistensi Tindakan</option>
                                <option value="case_discussion">Diskusi Kasus</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-[#000000] mb-1">Judul / Topik Kasus *</label>
                            <input
                                v-model="form.case_title"
                                type="text"
                                placeholder="Contoh: Pasien STEMI Anteroseptal dengan Syok Kardiogenik"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                            />
                            <p v-if="formErrors.case_title" class="text-xs text-rose-600 mt-1">{{ formErrors.case_title }}</p>
                        </div>
                    </div>

                    <!-- Temuan Klinis -->
                    <div>
                        <label class="block font-bold text-[#000000] mb-1">Temuan Klinis & Hasil Pemeriksaan *</label>
                        <textarea
                            v-model="form.clinical_findings"
                            rows="3"
                            placeholder="Uraikan keluhan utama, tanda vital abnormal, hasil lab/EKG/rontgen penting..."
                            class="w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] p-3 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                        ></textarea>
                        <p v-if="formErrors.clinical_findings" class="text-xs text-rose-600 mt-1">{{ formErrors.clinical_findings }}</p>
                    </div>

                    <!-- Prosedur / Tindakan -->
                    <div>
                        <label class="block font-bold text-[#000000] mb-1">Tindakan / Prosedur yang Dilakukan (Opsional)</label>
                        <input
                            v-model="form.procedure_performed"
                            type="text"
                            placeholder="Contoh: Pemasangan IV line, EKG 12 Lead, Aspirasi Abses..."
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                        />
                    </div>

                    <!-- Refleksi Pembelajaran -->
                    <div>
                        <label class="block font-bold text-[#065f46] mb-1">Refleksi Pembelajaran Mandiri (Learning Reflection) *</label>
                        <textarea
                            v-model="form.learning_reflection"
                            rows="2"
                            placeholder="Apa hal baru yang dipelajari dan apa yang perlu ditingkatkan untuk kasus serupa di masa mendatang?"
                            class="w-full rounded-xl border border-[#065f46]/30 bg-[#beedc0]/15 p-3 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                        ></textarea>
                        <p v-if="formErrors.learning_reflection" class="text-xs text-rose-600 mt-1">{{ formErrors.learning_reflection }}</p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-[#000000]/10">
                    <button
                        type="button"
                        @click="closeModal"
                        class="min-h-[44px] w-full sm:w-auto px-5 py-2.5 rounded-xl border border-[#000000]/15 text-xs font-semibold text-[#000000]/70 hover:bg-[#edede2] transition-colors"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="handleSaveLogbook(false)"
                        :disabled="isSubmitting"
                        class="min-h-[44px] w-full sm:w-auto px-5 py-2.5 rounded-xl border border-[#065f46] bg-[#fffff3] text-xs font-bold text-[#065f46] hover:bg-[#beedc0]/30 transition-colors"
                    >
                        Simpan Draft
                    </button>

                    <button
                        type="button"
                        @click="handleSaveLogbook(true)"
                        :disabled="isSubmitting"
                        class="min-h-[44px] w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#065f46] text-xs font-bold text-[#ffffff] hover:bg-[#054d38] transition-colors flex items-center justify-center gap-2 shadow-sm"
                    >
                        <Loader2 v-if="isSubmitting" class="size-4 animate-spin text-[#beedc0]" />
                        <Send v-else class="size-4 text-[#beedc0]" />
                        <span>Simpan & Ajukan ke DPJP</span>
                    </button>
                </div>
            </motion.div>
        </div>

        <!-- Modal Feedback & Catatan DPJP -->
        <div v-if="selectedFeedbackItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#000000]/50 backdrop-blur-xs">
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-lg rounded-3xl border border-purple-200 bg-[#fffff3] p-6 sm:p-8 shadow-2xl space-y-4"
            >
                <div class="flex items-center justify-between border-b border-[#000000]/10 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="size-9 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center">
                            <Award class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-serif text-base font-bold text-[#000000]">Evaluasi & Feedback DPJP</h3>
                            <p class="text-xs text-[#000000]/60">{{ selectedFeedbackItem.doctor?.name || 'Dokter Spesialis' }}</p>
                        </div>
                    </div>
                    <button type="button" @click="selectedFeedbackItem = null" class="p-1.5 text-[#000000]/50 hover:text-[#000000]">
                        <X class="size-5" />
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div v-if="selectedFeedbackItem.score !== null" class="rounded-xl bg-[#065f46] p-4 text-[#ffffff] flex items-center justify-between">
                        <div>
                            <div class="text-xs text-[#beedc0] uppercase font-bold">Skor Nilai Kasus</div>
                            <div class="text-2xl font-bold font-mono">{{ selectedFeedbackItem.score }} / 100</div>
                        </div>
                        <CheckCircle2 class="size-8 text-[#beedc0]" />
                    </div>

                    <div class="rounded-xl bg-purple-50/60 border border-purple-200 p-4 space-y-1.5">
                        <div class="font-bold text-purple-900">Catatan & Umpan Balik:</div>
                        <p class="text-purple-950 leading-relaxed">{{ selectedFeedbackItem.supervisor_feedback }}</p>
                    </div>

                    <div class="text-right text-xs text-[#000000]/50">
                        Ditinjau pada: {{ selectedFeedbackItem.reviewed_at ? new Date(selectedFeedbackItem.reviewed_at).toLocaleString('id-ID') : '-' }}
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="button"
                        @click="selectedFeedbackItem = null"
                        class="min-h-[44px] w-full rounded-xl bg-[#000000] text-xs font-bold text-[#ffffff] hover:bg-[#333333] transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </motion.div>
        </div>
    </div>
</template>
