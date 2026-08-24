<script setup lang="ts">
/**
 * @file Index.vue (Doctor DPJP Supervision)
 * @description Portal Supervisi & Dual Sign-off Kasus Klinis Mahasiswa Koas oleh Dokter DPJP.
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
    AlertTriangle,
    Award,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    FileCheck,
    FileText,
    GraduationCap,
    HeartPulse,
    Loader2,
    MessageSquare,
    Search,
    ShieldCheck,
    Stethoscope,
    User,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';

interface ClinicalLogbookSupervisionItem {
    clinical_logbook_id: number;
    nurse_id: number;
    patient_id: number;
    doctor_id: number;
    medical_record_id: number | null;
    activity_type:
        | 'anamnesis'
        | 'physical_exam'
        | 'procedure_assistance'
        | 'case_discussion';
    case_title: string;
    clinical_findings: string;
    procedure_performed: string | null;
    learning_reflection: string;
    supervisor_feedback: string | null;
    score: number | null;
    status: 'draft' | 'submitted' | 'approved' | 'revision_needed';
    submitted_at: string | null;
    reviewed_at: string | null;
    created_at: string;
    nurse?: {
        nurse_id: number;
        name: string;
        registration_number: string;
        institute?: string;
    };
    patient?: {
        patient_id: number;
        name: string;
        resident_n: string;
        gender?: string;
        birthday_date?: string;
    };
    medical_record?: {
        medical_record_id: number;
        assessment: string;
        subjective: string;
        objective: any;
    };
}

const props = defineProps<{
    logbooks: {
        data: ClinicalLogbookSupervisionItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        status?: string;
        activity_type?: string;
        search?: string;
    };
    stats: {
        total_assigned: number;
        pending_review: number;
        approved: number;
        revision_needed: number;
    };
}>();

// Filter State
const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedActivityType = ref(props.filters.activity_type || '');

const applyFilters = () => {
    router.get(
        '/doctor/supervision',
        {
            search: searchInput.value || undefined,
            status: selectedStatus.value || undefined,
            activity_type: selectedActivityType.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

// Modal Review & Sign-Off State
const isReviewModalOpen = ref(false);
const isSubmitting = ref(false);
const selectedLogbook = ref<ClinicalLogbookSupervisionItem | null>(null);
const reviewErrors = ref<Record<string, string>>({});

const reviewForm = ref({
    status: 'approved',
    supervisor_feedback: '',
    score: 85,
});

const openReviewModal = (item: ClinicalLogbookSupervisionItem) => {
    selectedLogbook.value = item;
    reviewErrors.value = {};
    reviewForm.value = {
        status:
            item.status === 'revision_needed' ? 'revision_needed' : 'approved',
        supervisor_feedback: item.supervisor_feedback || '',
        score: item.score ?? 85,
    };
    isReviewModalOpen.value = true;
};

const closeReviewModal = () => {
    isReviewModalOpen.value = false;
    selectedLogbook.value = null;
};

const submitReview = async () => {
    if (!selectedLogbook.value) {
        return;
    }

    reviewErrors.value = {};
    isSubmitting.value = true;

    try {
        await axios.post(
            `/doctor/supervision/${selectedLogbook.value.clinical_logbook_id}/review`,
            reviewForm.value,
        );
        closeReviewModal();
        router.reload();
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            reviewErrors.value = Object.fromEntries(
                Object.entries(err.response.data.errors).map(([k, v]) => [
                    k,
                    (v as string[])[0],
                ]),
            );
        } else {
            alert(err.response?.data?.message || 'Gagal menyimpan supervisi.');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// Helper Activity Type Labels
const activityLabels: Record<
    string,
    { label: string; icon: any; color: string }
> = {
    anamnesis: {
        label: 'Anamnesis Pasien',
        icon: Stethoscope,
        color: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    },
    physical_exam: {
        label: 'Pemeriksaan Fisik',
        icon: HeartPulse,
        color: 'bg-blue-50 text-blue-800 border-blue-200',
    },
    procedure_assistance: {
        label: 'Asistensi Tindakan',
        icon: Activity,
        color: 'bg-purple-50 text-purple-800 border-purple-200',
    },
    case_discussion: {
        label: 'Diskusi Kasus',
        icon: MessageSquare,
        color: 'bg-amber-50 text-amber-800 border-amber-200',
    },
};

// Helper Status Badges
const statusBadges: Record<
    string,
    { label: string; badge: string; dot: string }
> = {
    draft: {
        label: 'Draft',
        badge: 'bg-neutral-100 text-neutral-600 border-neutral-200',
        dot: 'bg-neutral-400',
    },
    submitted: {
        label: 'Menunggu Verifikasi DPJP',
        badge: 'bg-amber-50 text-amber-800 border-amber-300 animate-pulse font-bold',
        dot: 'bg-amber-500',
    },
    approved: {
        label: 'Telah Disetujui (Sign-off)',
        badge: 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold',
        dot: 'bg-emerald-600',
    },
    revision_needed: {
        label: 'Perlu Revisi Mahasiswa',
        badge: 'bg-rose-50 text-rose-800 border-rose-300 font-bold',
        dot: 'bg-rose-600',
    },
};
</script>

<template>
    <div
        class="min-h-screen bg-[#edede2] px-4 py-8 font-['Rubik'] text-[#000000] sm:px-6 lg:px-8"
    >
        <Head title="Supervisi Klinis Koas - Hospital Population" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header Banner -->
            <motion.div
                :initial="{ opacity: 0, y: -15 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-2xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#065f46] px-3 py-1 text-xs font-bold text-[#ffffff]"
                        >
                            <Stethoscope class="size-3.5" />
                            <span>Portal Supervisi Dokter DPJP</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Supervisi & Evaluasi Koas (Dual Sign-off)
                    </h1>
                    <p class="text-xs text-[#000000]/60 sm:text-sm">
                        Verifikasi keakuratan temuan klinis, berikan umpan balik
                        edukatif, nilai kompetensi mahasiswa, dan tandatangani
                        logbook.
                    </p>
                </div>
            </motion.div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    class="rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div
                        class="text-xs font-semibold text-[#000000]/60 uppercase"
                    >
                        Total Logbook Ditugaskan
                    </div>
                    <div
                        class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                    >
                        {{ stats.total_assigned }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 shadow-sm"
                >
                    <div class="text-xs font-semibold text-amber-800 uppercase">
                        Menunggu Review
                    </div>
                    <div
                        class="mt-1 font-mono text-2xl font-bold text-amber-800"
                    >
                        {{ stats.pending_review }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 shadow-sm"
                >
                    <div
                        class="text-xs font-semibold text-emerald-800 uppercase"
                    >
                        Telah Disetujui
                    </div>
                    <div
                        class="mt-1 font-mono text-2xl font-bold text-emerald-800"
                    >
                        {{ stats.approved }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 shadow-sm"
                >
                    <div class="text-xs font-semibold text-rose-800 uppercase">
                        Diminta Revisi
                    </div>
                    <div
                        class="mt-1 font-mono text-2xl font-bold text-rose-800"
                    >
                        {{ stats.revision_needed }}
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div
                class="flex flex-col gap-3 rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#000000]/40"
                    />
                    <input
                        v-model="searchInput"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari nama mahasiswa koas, NIM, judul kasus, atau pasien..."
                        class="min-h-[44px] w-full rounded-lg border border-[#000000]/15 bg-[#edede2]/40 pr-4 pl-9 text-sm text-[#000000] placeholder-[#000000]/40 focus:border-[#065f46] focus:outline-none"
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
                        <option value="procedure_assistance">
                            Asistensi Tindakan
                        </option>
                        <option value="case_discussion">Diskusi Kasus</option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="min-h-[44px] rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-2 text-xs font-medium text-[#000000] focus:border-[#065f46] focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="submitted">Menunggu Review</option>
                        <option value="approved">Disetujui</option>
                        <option value="revision_needed">Perlu Revisi</option>
                    </select>
                </div>
            </div>

            <!-- List Logbook Masuk untuk Supervisi -->
            <div class="space-y-4" v-if="logbooks.data.length > 0">
                <motion.div
                    v-for="item in logbooks.data"
                    :key="item.clinical_logbook_id"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="flex flex-col justify-between gap-4 rounded-2xl border bg-[#fffff3] p-6 shadow-sm transition-all"
                    :class="
                        item.status === 'submitted'
                            ? 'border-amber-300 ring-2 ring-amber-100'
                            : 'border-[#000000]/10 hover:border-[#065f46]/30'
                    "
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="flex-1 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Badge Activity -->
                                <span
                                    v-if="activityLabels[item.activity_type]"
                                    :class="
                                        activityLabels[item.activity_type].color
                                    "
                                    class="inline-flex items-center gap-1 rounded-md border px-2.5 py-0.5 text-xs font-semibold"
                                >
                                    <component
                                        :is="
                                            activityLabels[item.activity_type]
                                                .icon
                                        "
                                        class="size-3.5"
                                    />
                                    <span>{{
                                        activityLabels[item.activity_type].label
                                    }}</span>
                                </span>

                                <!-- Badge Status -->
                                <span
                                    v-if="statusBadges[item.status]"
                                    :class="statusBadges[item.status].badge"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs"
                                >
                                    <span
                                        :class="statusBadges[item.status].dot"
                                        class="size-1.5 rounded-full"
                                    ></span>
                                    <span>{{
                                        statusBadges[item.status].label
                                    }}</span>
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

                            <h3
                                class="font-serif text-base font-bold text-[#000000] sm:text-lg"
                            >
                                {{ item.case_title }}
                            </h3>

                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#000000]/60"
                            >
                                <span
                                    >Mahasiswa Koas:
                                    <strong class="text-[#065f46]">{{
                                        item.nurse?.name || 'Mahasiswa'
                                    }}</strong>
                                    (NIM:
                                    {{
                                        item.nurse?.registration_number || '-'
                                    }})</span
                                >
                                <span>•</span>
                                <span
                                    >Pasien:
                                    <strong class="text-[#000000]">{{
                                        item.patient?.name || 'Pasien'
                                    }}</strong></span
                                >
                                <span>•</span>
                                <span
                                    >Diajukan:
                                    {{
                                        item.submitted_at
                                            ? new Date(
                                                  item.submitted_at,
                                              ).toLocaleDateString('id-ID', {
                                                  dateStyle: 'medium',
                                              })
                                            : '-'
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Action Button: Evaluasi & Sign-off -->
                        <div
                            class="flex items-center gap-2 self-end sm:self-start"
                        >
                            <motion.button
                                type="button"
                                :whileHover="{ scale: 1.02 }"
                                :whileTap="{ scale: 0.98 }"
                                @click="openReviewModal(item)"
                                class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-xl bg-[#065f46] px-5 py-2.5 text-xs font-bold text-[#ffffff] shadow-sm transition-colors hover:bg-[#054d38]"
                            >
                                <FileCheck class="size-4 text-[#beedc0]" />
                                <span>{{
                                    item.status === 'approved'
                                        ? 'Ubah Evaluasi'
                                        : 'Supervisi & Sign-off'
                                }}</span>
                            </motion.button>
                        </div>
                    </div>

                    <!-- Clinical Findings & Reflection -->
                    <div
                        class="space-y-2 rounded-xl border border-[#000000]/5 bg-[#edede2]/50 p-4 text-xs text-[#000000]/80"
                    >
                        <div>
                            <span class="font-bold text-[#000000]"
                                >Temuan Klinis Mahasiswa:
                            </span>
                            <span>{{ item.clinical_findings }}</span>
                        </div>
                        <div v-if="item.procedure_performed">
                            <span class="font-bold text-[#000000]"
                                >Tindakan Medis Dilakukan:
                            </span>
                            <span>{{ item.procedure_performed }}</span>
                        </div>
                        <div>
                            <span class="font-bold text-[#065f46]"
                                >Refleksi Pembelajaran:
                            </span>
                            <span class="text-[#000000]/70 italic"
                                >"{{ item.learning_reflection }}"</span
                            >
                        </div>
                        <div
                            v-if="item.supervisor_feedback"
                            class="border-t border-[#000000]/10 pt-2 text-purple-900"
                        >
                            <span class="font-bold"
                                >Feedback DPJP Sebelumnya:
                            </span>
                            <span>{{ item.supervisor_feedback }}</span>
                        </div>
                    </div>
                </motion.div>
            </div>

            <!-- Empty State -->
            <div
                class="rounded-2xl border border-dashed border-[#000000]/20 bg-[#fffff3] p-12 text-center"
                v-else
            >
                <GraduationCap class="mx-auto mb-3 size-12 text-[#000000]/30" />
                <h3 class="font-serif text-lg font-bold text-[#000000]">
                    Tidak Ada Logbook Menunggu Supervisi
                </h3>
                <p class="mx-auto mt-1 max-w-md text-xs text-[#000000]/60">
                    Semua kasus klinis yang diajukan oleh mahasiswa koas di
                    bawah bimbingan Anda telah selesai ditinjau.
                </p>
            </div>
        </div>

        <!-- Modal Supervisi & Dual Sign-off DPJP -->
        <div
            v-if="isReviewModalOpen && selectedLogbook"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/50 p-4 backdrop-blur-xs"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="max-h-[90vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-2xl sm:p-8"
            >
                <div
                    class="flex items-center justify-between border-b border-[#000000]/10 pb-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-[#beedc0] text-[#065f46]"
                        >
                            <FileCheck class="size-5" />
                        </div>
                        <div>
                            <h2
                                class="font-serif text-lg font-bold text-[#000000]"
                            >
                                Supervisi & Evaluasi Kasus Klinis
                            </h2>
                            <p class="text-xs text-[#000000]/60">
                                Mahasiswa:
                                <strong class="text-[#065f46]">{{
                                    selectedLogbook.nurse?.name
                                }}</strong>
                                ({{
                                    selectedLogbook.nurse?.registration_number
                                }})
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeReviewModal"
                        class="p-2 text-[#000000]/50 hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- Ringkasan Kasus Koas -->
                <div
                    class="space-y-2 rounded-xl border border-[#000000]/10 bg-[#edede2]/60 p-4 text-xs"
                >
                    <div class="font-serif text-sm font-bold text-[#000000]">
                        {{ selectedLogbook.case_title }}
                    </div>
                    <div class="text-[#000000]/70">
                        Pasien: {{ selectedLogbook.patient?.name }} (NIK:
                        {{ selectedLogbook.patient?.resident_n }})
                    </div>
                    <div class="text-[#000000]/90">
                        <strong>Temuan: </strong>
                        {{ selectedLogbook.clinical_findings }}
                    </div>
                    <div class="text-[#000000]/90 italic">
                        <strong>Refleksi: </strong> "{{
                            selectedLogbook.learning_reflection
                        }}"
                    </div>
                </div>

                <!-- Form Evaluasi DPJP -->
                <div class="space-y-4 text-xs sm:text-sm">
                    <!-- Keputusan Status Sign-off -->
                    <div>
                        <label class="mb-2 block font-bold text-[#000000]"
                            >Keputusan Supervisi DPJP *</label
                        >
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                :class="
                                    reviewForm.status === 'approved'
                                        ? 'border-[#065f46] bg-[#beedc0]/20 ring-1 ring-[#065f46]'
                                        : 'border-[#000000]/15 bg-[#fffff3]'
                                "
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition-all"
                            >
                                <input
                                    type="radio"
                                    v-model="reviewForm.status"
                                    value="approved"
                                    class="accent-[#065f46]"
                                />
                                <div>
                                    <div class="font-bold text-[#065f46]">
                                        Setujui (Approved)
                                    </div>
                                    <div class="text-xs text-[#000000]/60">
                                        Dual sign-off selesai & valid
                                    </div>
                                </div>
                            </label>

                            <label
                                :class="
                                    reviewForm.status === 'revision_needed'
                                        ? 'border-rose-500 bg-rose-50 ring-1 ring-rose-500'
                                        : 'border-[#000000]/15 bg-[#fffff3]'
                                "
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition-all"
                            >
                                <input
                                    type="radio"
                                    v-model="reviewForm.status"
                                    value="revision_needed"
                                    class="accent-rose-600"
                                />
                                <div>
                                    <div class="font-bold text-rose-800">
                                        Minta Revisi
                                    </div>
                                    <div class="text-xs text-[#000000]/60">
                                        Mahasiswa wajib memperbaiki
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Skor Nilai Kompetensi -->
                    <div>
                        <label class="mb-1 block font-bold text-[#000000]"
                            >Skor Nilai Kasus (0 - 100)</label
                        >
                        <input
                            v-model.number="reviewForm.score"
                            type="number"
                            min="0"
                            max="100"
                            class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2.5 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                        />
                        <p
                            v-if="reviewErrors.score"
                            class="mt-1 text-xs text-rose-600"
                        >
                            {{ reviewErrors.score }}
                        </p>
                    </div>

                    <!-- Feedback & Catatan Edukatif DPJP -->
                    <div>
                        <label class="mb-1 block font-bold text-[#000000]"
                            >Catatan Evaluasi & Umpan Balik Klinis *</label
                        >
                        <textarea
                            v-model="reviewForm.supervisor_feedback"
                            rows="4"
                            placeholder="Tuliskan masukan diagnostik, penalaran klinis, atau perbaikan teknik tindakan untuk mahasiswa koas..."
                            class="w-full rounded-xl border border-[#000000]/15 bg-[#fffff3] p-3 text-xs text-[#000000] focus:border-[#065f46] focus:outline-none"
                        ></textarea>
                        <p
                            v-if="reviewErrors.supervisor_feedback"
                            class="mt-1 text-xs text-rose-600"
                        >
                            {{ reviewErrors.supervisor_feedback }}
                        </p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div
                    class="flex items-center justify-end gap-3 border-t border-[#000000]/10 pt-4"
                >
                    <button
                        type="button"
                        @click="closeReviewModal"
                        class="min-h-[44px] rounded-xl border border-[#000000]/15 px-5 py-2.5 text-xs font-semibold text-[#000000]/70 transition-colors hover:bg-[#edede2]"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="submitReview"
                        :disabled="isSubmitting"
                        class="flex min-h-[44px] items-center gap-2 rounded-xl bg-[#065f46] px-6 py-2.5 text-xs font-bold text-[#ffffff] shadow-sm transition-colors hover:bg-[#054d38]"
                    >
                        <Loader2
                            v-if="isSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <CheckCircle2 v-else class="size-4 text-[#beedc0]" />
                        <span>Simpan Supervisi & Sign-off</span>
                    </button>
                </div>
            </motion.div>
        </div>
    </div>
</template>
