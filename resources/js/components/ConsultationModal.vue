<script setup lang="ts">
/**
 * @file ConsultationModal.vue
 * @description Modul Konsultasi Dokter & Rekam Medis Elektronik (EMR).
 * Menyediakan 3 fitur klinis terintegrasi:
 *   1. Pencatatan SOAP Notes (Subjective, Objective/Vital Signs, Assessment, Plan)
 *   2. Dynamic E-Prescription Builder (Penyusunan resep obat & cek stok apotek otomatis)
 *   3. Patient Medical History Timeline Drawer (Riwayat klinis kunjungan terdahulu)
 */
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    Bookmark,
    BookmarkPlus,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    FileText,
    HeartPulse,
    History,
    Info,
    Keyboard,
    Loader2,
    Pill,
    Plus,
    RefreshCw,
    Search,
    ShieldAlert,
    Sparkles,
    Stethoscope,
    Thermometer,
    Trash2,
    User,
    Weight,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import type {
    MedicalRecord,
    Medicine,
    PrescriptionItem,
    VitalSigns,
} from '@/types/hospital';

interface AppointmentPatient {
    patient_id: number;
    name: string;
    resident_n?: string;
    gender?: string;
    birthday_date?: string;
    number_phone?: string;
    address?: string;
}

interface AppointmentContext {
    appointment_id: number;
    queue_number: string;
    appointment_date: string;
    complaint?: string | null;
    patient?: AppointmentPatient;
    doctorSchedule?: {
        doctor_schedule_id?: number;
        day?: string;
        start_time?: string;
        end_time?: string;
        doctor?: {
            name: string;
            specialization?: { name_specialization?: string };
        };
        poli?: { name_poli?: string; name?: string };
        room?: { name_room?: string };
    };
}

interface Icd10Item {
    icd10_diagnosis_id: number;
    code: string;
    name_id: string;
    name_en?: string;
    is_common?: boolean;
}

interface SoapTemplateItem {
    soap_template_id: number;
    doctor_id: number | null;
    template_name: string;
    subjective_template: string | null;
    objective_template: any | null;
    assessment_template: string | null;
    plan_template: string | null;
}

interface SafetyEvaluation {
    has_warnings: boolean;
    has_severe: boolean;
    allergy_alerts: Array<{
        allergen_name: string;
        medicine_name: string;
        severity: string;
        reaction?: string;
        message: string;
    }>;
    interaction_alerts: Array<{
        drug_1: string;
        drug_2: string;
        severity: string;
        mechanism: string;
        advice: string;
    }>;
}

const props = defineProps<{
    open: boolean;
    appointment: AppointmentContext | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success', data: any): void;
}>();

const activeTab = ref<'consultation' | 'history'>('consultation');

const formSubjective = ref('');
const formAssessment = ref('');
const formPlan = ref('');
const formPhysicalCheck = ref('');

const vitals = ref<VitalSigns>({
    systolic: null,
    diastolic: null,
    pulse: null,
    temperature: null,
    respiratory_rate: null,
    weight: null,
    height: null,
    oxygen_saturation: null,
});

const icd10Suggestions = ref<Icd10Item[]>([]);
const isSearchingIcd10 = ref(false);
const showIcd10Dropdown = ref(false);
let icd10DebounceTimeout: any = null;

const soapTemplates = ref<SoapTemplateItem[]>([]);
const isLoadingSoapTemplates = ref(false);
const isSoapTemplateDropdownOpen = ref(false);
const isSaveTemplateModalOpen = ref(false);
const newTemplateName = ref('');
const isSavingTemplate = ref(false);

const safetyEvaluation = ref<SafetyEvaluation>({
    has_warnings: false,
    has_severe: false,
    allergy_alerts: [],
    interaction_alerts: [],
});
const isCheckingSafety = ref(false);
let safetyDebounceTimeout: any = null;

interface FormPrescriptionRow {
    medicine_id: number | null;
    selectedMedicine?: Medicine | null;
    quantity: number;
    dosage: string;
    instructions: string;
    notes: string;
}

const prescriptionRows = ref<FormPrescriptionRow[]>([]);
const prescriptionNotes = ref('');
const availableMedicines = ref<Medicine[]>([]);
const isLoadingMedicines = ref(false);
const medicineSearchQuery = ref('');

const patientHistory = ref<MedicalRecord[]>([]);
const isLoadingHistory = ref(false);
const historyError = ref<string | null>(null);

const isSubmitting = ref(false);
const validationErrors = ref<Record<string, string>>({});
const submitSuccessMessage = ref<string | null>(null);

const dosagePresets = [
    '3 x 1 Tablet Sehari',
    '3 x 1 Kapsul Sehari',
    '2 x 1 Tablet Sehari',
    '2 x 1 Kapsul Sehari',
    '1 x 1 Tablet Sehari',
    '1 x 1 Kapsul Sebelum Tidur',
    '3 x 1 Sendok Takar (5 ml)',
    '1 x 1 Tablet Pagi Hari',
    'Bila Demam / Nyeri (PRN)',
    '1-2 Semprotan Saat Sesak',
];

const instructionPresets = [
    'Sesudah makan',
    'Sebelum makan (perut kosong)',
    'Bersama makanan',
    'Sebelum tidur',
    'Dihabiskan (Antibiotik)',
    'Hanya bila sakit / demam',
    'Kumur dan jangan ditelan',
    'Teteskan pada mata / telinga',
];

const calculatedBmi = computed(() => {
    const w = Number(vitals.value.weight);
    const h = Number(vitals.value.height);

    if (!w || !h || h <= 0) {
        return null;
    }

    const heightInMeters = h / 100;
    const bmiVal = w / (heightInMeters * heightInMeters);

    return Number(bmiVal.toFixed(1));
});

const bmiCategory = computed(() => {
    const bmi = calculatedBmi.value;

    if (!bmi) {
        return null;
    }

    if (bmi < 18.5) {
        return {
            label: 'Underweight (Kurang)',
            color: 'bg-amber-100 text-amber-900 border-amber-300',
        };
    }

    if (bmi < 25.0) {
        return {
            label: 'Normal / Ideal',
            color: 'bg-emerald-100 text-emerald-900 border-emerald-300',
        };
    }

    if (bmi < 30.0) {
        return {
            label: 'Overweight (Berlebih)',
            color: 'bg-orange-100 text-orange-900 border-orange-300',
        };
    }

    return {
        label: 'Obesitas',
        color: 'bg-rose-100 text-rose-900 border-rose-300',
    };
});

const bloodPressureCategory = computed(() => {
    const sys = Number(vitals.value.systolic);
    const dia = Number(vitals.value.diastolic);

    if (!sys || !dia) {
        return null;
    }

    if (sys < 120 && dia < 80) {
        return {
            label: 'Optimal / Normal',
            color: 'text-emerald-700 bg-emerald-50 border-emerald-200',
        };
    }

    if (sys <= 139 || dia <= 89) {
        return {
            label: 'Pra-Hipertensi',
            color: 'text-amber-700 bg-amber-50 border-amber-200',
        };
    }

    return {
        label: 'Hipertensi',
        color: 'text-rose-700 bg-rose-50 border-rose-200',
    };
});

const filteredMedicines = computed(() => {
    if (!medicineSearchQuery.value.trim()) {
        return availableMedicines.value;
    }

    const q = medicineSearchQuery.value.toLowerCase();

    return availableMedicines.value.filter(
        (m) =>
            m.name_medicine.toLowerCase().includes(q) ||
            m.code_medicine.toLowerCase().includes(q) ||
            m.type.toLowerCase().includes(q),
    );
});

const estimatedTotalPrescriptionCost = computed(() => {
    return prescriptionRows.value.reduce((total, row) => {
        if (row.selectedMedicine && row.quantity > 0) {
            const price = Number(row.selectedMedicine.price) || 0;

            return total + price * row.quantity;
        }

        return total;
    }, 0);
});

const fetchMedicines = async () => {
    isLoadingMedicines.value = true;

    try {
        const response = await axios.get('/doctor/medicines');

        if (response.data?.status && Array.isArray(response.data.data)) {
            availableMedicines.value = response.data.data;
        }
    } catch (err) {
        console.error('Gagal memuat katalog obat:', err);
    } finally {
        isLoadingMedicines.value = false;
    }
};

const fetchPatientHistory = async (patientId: number) => {
    isLoadingHistory.value = true;
    historyError.value = null;

    try {
        const response = await axios.get(
            `/doctor/patients/${patientId}/history`,
        );

        if (response.data?.status && Array.isArray(response.data.data)) {
            patientHistory.value = response.data.data;
        }
    } catch (err: any) {
        historyError.value =
            err.response?.data?.message ||
            'Gagal mengambil riwayat medis pasien.';
    } finally {
        isLoadingHistory.value = false;
    }
};

watch(
    () => props.appointment,
    (app) => {
        if (app) {
            formSubjective.value = app.complaint
                ? `Keluhan Utama: ${app.complaint}`
                : '';
            formAssessment.value = '';
            formPlan.value = '';
            formPhysicalCheck.value = '';
            vitals.value = {
                systolic: null,
                diastolic: null,
                pulse: null,
                temperature: 36.5,
                respiratory_rate: 20,
                weight: null,
                height: null,
                oxygen_saturation: 98,
            };
            prescriptionRows.value = [];
            prescriptionNotes.value = '';
            validationErrors.value = {};
            submitSuccessMessage.value = null;
            activeTab.value = 'consultation';

            if (app.patient?.patient_id) {
                fetchPatientHistory(app.patient.patient_id);
            }
        }
    },
    { immediate: true },
);

const searchIcd10 = async (q: string) => {
    isSearchingIcd10.value = true;

    try {
        const response = await axios.get('/api/clinical/icd10', {
            params: { q },
        });

        if (response.data?.status && Array.isArray(response.data.data)) {
            icd10Suggestions.value = response.data.data;
            showIcd10Dropdown.value = true;
        }
    } catch (err) {
        console.error('Gagal memuat diagnosa ICD-10:', err);
    } finally {
        isSearchingIcd10.value = false;
    }
};

const onAssessmentInput = () => {
    clearTimeout(icd10DebounceTimeout);
    const val = formAssessment.value.trim();
    icd10DebounceTimeout = setTimeout(() => {
        searchIcd10(val);
    }, 250);
};

const selectIcd10 = (item: Icd10Item) => {
    formAssessment.value = `${item.code} - ${item.name_id}`;
    showIcd10Dropdown.value = false;
};

const fetchSoapTemplates = async () => {
    isLoadingSoapTemplates.value = true;

    try {
        const response = await axios.get('/api/clinical/soap-templates');

        if (response.data?.status && Array.isArray(response.data.data)) {
            soapTemplates.value = response.data.data;
        }
    } catch (err) {
        console.error('Gagal mengambil template SOAP:', err);
    } finally {
        isLoadingSoapTemplates.value = false;
    }
};

const applySoapTemplate = (tpl: SoapTemplateItem) => {
    if (tpl.subjective_template) {
        formSubjective.value = tpl.subjective_template;
    }

    if (tpl.assessment_template) {
        formAssessment.value = tpl.assessment_template;
    }

    if (tpl.plan_template) {
        formPlan.value = tpl.plan_template;
    }

    if (tpl.objective_template && typeof tpl.objective_template === 'object') {
        const obj = tpl.objective_template;

        if (obj.systolic) {
            vitals.value.systolic = obj.systolic;
        }

        if (obj.diastolic) {
            vitals.value.diastolic = obj.diastolic;
        }

        if (obj.pulse) {
            vitals.value.pulse = obj.pulse;
        }

        if (obj.temperature) {
            vitals.value.temperature = obj.temperature;
        }

        if (obj.respiratory_rate) {
            vitals.value.respiratory_rate = obj.respiratory_rate;
        }

        if (obj.notes) {
            formPhysicalCheck.value = obj.notes;
        }
    }

    isSoapTemplateDropdownOpen.value = false;
};

const saveCurrentAsSoapTemplate = async () => {
    if (!newTemplateName.value.trim()) {
        return;
    }

    isSavingTemplate.value = true;

    try {
        const response = await axios.post('/api/clinical/soap-templates', {
            template_name: newTemplateName.value.trim(),
            subjective_template: formSubjective.value,
            objective_template: {
                systolic: vitals.value.systolic,
                diastolic: vitals.value.diastolic,
                pulse: vitals.value.pulse,
                temperature: vitals.value.temperature,
                respiratory_rate: vitals.value.respiratory_rate,
                notes: formPhysicalCheck.value,
            },
            assessment_template: formAssessment.value,
            plan_template: formPlan.value,
        });

        if (response.data?.status) {
            await fetchSoapTemplates();
            isSaveTemplateModalOpen.value = false;
            newTemplateName.value = '';
        }
    } catch (err) {
        console.error('Gagal menyimpan template SOAP:', err);
    } finally {
        isSavingTemplate.value = false;
    }
};

const evaluatePrescriptionSafety = async () => {
    const validMedicines = prescriptionRows.value
        .filter((r) => r.selectedMedicine)
        .map((r) => ({
            medicine_id: r.selectedMedicine!.medicine_id,
            name:
                r.selectedMedicine!.name_medicine ||
                (r.selectedMedicine as any).name,
            code:
                r.selectedMedicine!.code_medicine ||
                (r.selectedMedicine as any).code,
        }));

    if (validMedicines.length === 0) {
        safetyEvaluation.value = {
            has_warnings: false,
            has_severe: false,
            allergy_alerts: [],
            interaction_alerts: [],
        };

        return;
    }

    isCheckingSafety.value = true;

    try {
        const response = await axios.post('/api/clinical/safety-check', {
            patient_id: props.appointment?.patient?.patient_id || null,
            medicines: validMedicines,
        });

        if (response.data?.status && response.data.data) {
            safetyEvaluation.value = response.data.data;
        }
    } catch (err) {
        console.error('Gagal mengevaluasi keselamatan resep:', err);
    } finally {
        isCheckingSafety.value = false;
    }
};

watch(
    () => prescriptionRows.value.map((r) => r.medicine_id),
    () => {
        clearTimeout(safetyDebounceTimeout);
        safetyDebounceTimeout = setTimeout(() => {
            evaluatePrescriptionSafety();
        }, 300);
    },
    { deep: true },
);

const handleKeydown = (e: KeyboardEvent) => {
    if (!props.open) {
        return;
    }

    if (e.ctrlKey && e.shiftKey && (e.key === 'T' || e.key === 't')) {
        e.preventDefault();
        isSoapTemplateDropdownOpen.value = !isSoapTemplateDropdownOpen.value;
    } else if (e.ctrlKey && e.shiftKey && (e.key === 'P' || e.key === 'p')) {
        e.preventDefault();
        addPrescriptionRow();
    } else if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        submitConsultation();
    } else if (e.key === 'Escape') {
        if (showIcd10Dropdown.value) {
            showIcd10Dropdown.value = false;
        } else if (isSoapTemplateDropdownOpen.value) {
            isSoapTemplateDropdownOpen.value = false;
        } else if (isSaveTemplateModalOpen.value) {
            isSaveTemplateModalOpen.value = false;
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    fetchMedicines();
    fetchSoapTemplates();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const addPrescriptionRow = () => {
    prescriptionRows.value.push({
        medicine_id: null,
        selectedMedicine: null,
        quantity: 1,
        dosage: '3 x 1 Tablet Sehari',
        instructions: 'Sesudah makan',
        notes: '',
    });
};

const removePrescriptionRow = (index: number) => {
    prescriptionRows.value.splice(index, 1);
};

const handleMedicineChange = (rowIndex: number, medicineId: number) => {
    const found = availableMedicines.value.find(
        (m) => m.medicine_id === Number(medicineId),
    );
    prescriptionRows.value[rowIndex].medicine_id = found
        ? found.medicine_id
        : null;
    prescriptionRows.value[rowIndex].selectedMedicine = found || null;

    if (found && prescriptionRows.value[rowIndex].quantity > found.stock) {
        prescriptionRows.value[rowIndex].quantity = Math.max(1, found.stock);
    }
};

const validateForm = (): boolean => {
    const errors: Record<string, string> = {};

    if (!formSubjective.value.trim()) {
        errors.subjective =
            'Catatan keluhan subjektif (Subjective) wajib diisi.';
    }

    if (!formAssessment.value.trim()) {
        errors.assessment = 'Diagnosis medis (Assessment) wajib diisi.';
    }

    if (!formPlan.value.trim()) {
        errors.plan = 'Rencana penatalaksanaan (Plan) wajib diisi.';
    }

    prescriptionRows.value.forEach((row, idx) => {
        if (!row.medicine_id) {
            errors[`prescription_${idx}`] =
                `Pilih obat untuk item ke-${idx + 1}.`;
        } else if (
            row.selectedMedicine &&
            row.quantity > row.selectedMedicine.stock
        ) {
            errors[`prescription_${idx}_stock`] =
                `Stok ${row.selectedMedicine.name_medicine} tidak mencukupi (Tersisa ${row.selectedMedicine.stock} ${row.selectedMedicine.unit}).`;
        }

        if (row.quantity < 1) {
            errors[`prescription_${idx}_qty`] =
                `Jumlah obat ke-${idx + 1} minimal 1.`;
        }
    });

    validationErrors.value = errors;

    return Object.keys(errors).length === 0;
};

const submitConsultation = async () => {
    if (!validateForm()) {
        return;
    }

    if (!props.appointment?.patient?.patient_id) {
        return;
    }

    isSubmitting.value = true;
    submitSuccessMessage.value = null;

    try {
        const payload = {
            patient_id: props.appointment.patient.patient_id,
            reservation_id: props.appointment.appointment_id,
            subjective: formSubjective.value,
            objective: {
                ...vitals.value,
                bmi: calculatedBmi.value,
            },
            assessment: formAssessment.value,
            plan: formPlan.value,
            physical_check: formPhysicalCheck.value || null,
            prescription_notes: prescriptionNotes.value || null,
            prescription_items: prescriptionRows.value
                .filter((row) => row.medicine_id !== null)
                .map((row) => ({
                    medicine_id: row.medicine_id,
                    quantity: row.quantity,
                    dosage: row.dosage,
                    instructions: row.instructions,
                    notes: row.notes || null,
                })),
        };

        const response = await axios.post('/doctor/consultations', payload);

        if (response.data?.status) {
            submitSuccessMessage.value =
                'Konsultasi medis & resep obat berhasil disimpan.';
            emit('success', response.data.data);
            setTimeout(() => {
                emit('update:open', false);
            }, 900);
        }
    } catch (err: any) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            const serverErrors: Record<string, string> = {};

            for (const [key, msg] of Object.entries(err.response.data.errors)) {
                serverErrors[key] = Array.isArray(msg) ? msg[0] : String(msg);
            }

            validationErrors.value = serverErrors;
        } else {
            validationErrors.value = {
                general:
                    err.response?.data?.message ||
                    'Terjadi kesalahan sistem saat menyimpan konsultasi.',
            };
        }
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    emit('update:open', false);
};

const formatDate = (dateStr?: string | null): string => {
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
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#000000]/60 p-2 font-['Rubik'] backdrop-blur-xs sm:p-4"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.96, y: 15 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.22, ease: 'easeOut' }"
            class="my-auto flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[12px] border border-[#333333]/20 bg-[#fffff3] text-[#000000] shadow-2xl"
        >
            <header
                class="flex shrink-0 flex-col justify-between gap-3 border-b border-[#333333]/15 bg-[#edede2] px-5 py-4 sm:flex-row sm:items-center"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#333333]/15 bg-[#beedc0]"
                    >
                        <Stethoscope class="size-6 text-[#000000]" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2
                                class="font-['ivypresto-headline'] text-2xl leading-tight font-bold text-[#000000]"
                            >
                                Rekam Medis & Konsultasi Pasien
                            </h2>
                            <span
                                class="inline-flex items-center rounded-full bg-[#000000] px-2.5 py-0.5 font-mono text-xs font-bold text-white"
                            >
                                {{ appointment?.queue_number || 'ANTREAN' }}
                            </span>
                        </div>
                        <p class="text-xs text-[#333333]">
                            {{
                                appointment?.doctorSchedule?.poli?.name_poli ||
                                'Poliklinik'
                            }}
                            ·
                            {{
                                appointment?.doctorSchedule?.room?.name_room ||
                                'Ruang Periksa'
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div
                        class="inline-flex rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] p-1"
                    >
                        <button
                            type="button"
                            @click="activeTab = 'consultation'"
                            class="inline-flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-[40.5px] px-3.5 py-1.5 text-xs font-semibold transition-all"
                            :class="
                                activeTab === 'consultation'
                                    ? 'bg-[#000000] text-white shadow-xs'
                                    : 'text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            <FileText class="size-3.5" />
                            <span>Pemeriksaan (EMR & Resep)</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'history'"
                            class="inline-flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-[40.5px] px-3.5 py-1.5 text-xs font-semibold transition-all"
                            :class="
                                activeTab === 'history'
                                    ? 'bg-[#000000] text-white shadow-xs'
                                    : 'text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            <History class="size-3.5" />
                            <span>Riwayat Pasien</span>
                            <span
                                class="py-0.2 rounded-full px-1.5 text-[10px]"
                                :class="
                                    activeTab === 'history'
                                        ? 'bg-[#beedc0] text-[#000000]'
                                        : 'bg-[#edede2] text-[#000000]'
                                "
                            >
                                {{ patientHistory.length }}
                            </span>
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-[#333333]/20 bg-[#ffffff] text-[#333333] transition-colors hover:bg-rose-50 hover:text-rose-600"
                        title="Tutup Modal"
                    >
                        <X class="size-5" />
                    </button>
                </div>
            </header>

            <div
                class="flex shrink-0 flex-wrap items-center justify-between gap-4 border-b border-[#333333]/10 bg-[#fffff3] px-5 py-3 text-xs"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-[#333333]/15 bg-[#edede2] font-bold text-[#000000]"
                    >
                        {{ appointment?.patient?.name?.charAt(0) || 'P' }}
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-[#000000]">{{
                            appointment?.patient?.name
                        }}</span>
                        <span class="text-[#333333]/70"
                            >NIK:
                            {{ appointment?.patient?.resident_n || '-' }} ·
                            Kelamin:
                            {{ appointment?.patient?.gender || '-' }}</span
                        >
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-[#333333]">
                    <span v-if="appointment?.patient?.birthday_date">
                        <strong>Tgl Lahir:</strong>
                        {{ formatDate(appointment.patient.birthday_date) }}
                    </span>
                    <span v-if="appointment?.patient?.number_phone">
                        <strong>Telepon:</strong>
                        {{ appointment.patient.number_phone }}
                    </span>
                    <span
                        class="rounded-full bg-[#beedc0] px-2.5 py-0.5 font-medium text-[#000000]"
                    >
                        Tgl Kunjungan:
                        {{ formatDate(appointment?.appointment_date) }}
                    </span>
                </div>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto p-5 sm:p-6">
                <div v-if="activeTab === 'consultation'" class="space-y-6">
                    <div
                        class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-[#333333]/10 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <Activity class="size-4 text-[#000000]" />
                                <h3
                                    class="text-sm font-semibold tracking-wide text-[#000000] uppercase"
                                >
                                    1. Tanda-Tanda Vital & Pengukuran Fisik
                                    (Objective)
                                </h3>
                            </div>
                            <span
                                v-if="bloodPressureCategory"
                                :class="bloodPressureCategory.color"
                                class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                            >
                                TD: {{ bloodPressureCategory.label }}
                            </span>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6"
                        >
                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Sistolik (mmHg)</label
                                >
                                <input
                                    v-model.number="vitals.systolic"
                                    type="number"
                                    placeholder="120"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Diastolik (mmHg)</label
                                >
                                <input
                                    v-model.number="vitals.diastolic"
                                    type="number"
                                    placeholder="80"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Nadi (bpm)</label
                                >
                                <input
                                    v-model.number="vitals.pulse"
                                    type="number"
                                    placeholder="80"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Suhu (°C)</label
                                >
                                <input
                                    v-model.number="vitals.temperature"
                                    type="number"
                                    step="0.1"
                                    placeholder="36.5"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Berat Badan (kg)</label
                                >
                                <input
                                    v-model.number="vitals.weight"
                                    type="number"
                                    step="0.5"
                                    placeholder="65"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Tinggi Badan (cm)</label
                                >
                                <input
                                    v-model.number="vitals.height"
                                    type="number"
                                    placeholder="170"
                                    class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-sm text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 border-t border-[#333333]/10 pt-2 text-xs sm:grid-cols-3"
                        >
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-[#333333]"
                                    >Laju Napas (RR):</span
                                >
                                <input
                                    v-model.number="vitals.respiratory_rate"
                                    type="number"
                                    placeholder="20"
                                    class="w-20 rounded-[7px] border border-[#333333]/20 px-2 py-1 text-xs"
                                />
                                <span class="text-[#333333]/70">x/mnt</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="font-medium text-[#333333]"
                                    >SpO2:</span
                                >
                                <input
                                    v-model.number="vitals.oxygen_saturation"
                                    type="number"
                                    placeholder="98"
                                    class="w-20 rounded-[7px] border border-[#333333]/20 px-2 py-1 text-xs"
                                />
                                <span class="text-[#333333]/70">%</span>
                            </div>

                            <div
                                v-if="calculatedBmi"
                                class="flex items-center gap-2"
                            >
                                <span class="font-semibold text-[#333333]"
                                    >BMI: {{ calculatedBmi }} kg/m²</span
                                >
                                <span
                                    v-if="bmiCategory"
                                    :class="bmiCategory.color"
                                    class="rounded-full border px-2 py-0.5 text-[11px] font-medium"
                                >
                                    {{ bmiCategory.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                    >
                        <div
                            class="flex flex-col justify-between gap-2 border-b border-[#333333]/10 pb-3 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-2">
                                <FileText class="size-4 text-[#000000]" />
                                <h3
                                    class="text-sm font-semibold tracking-wide text-[#000000] uppercase"
                                >
                                    2. Catatan Perkembangan Medis SOAP
                                </h3>
                            </div>

                            <div class="relative flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="
                                        isSoapTemplateDropdownOpen =
                                            !isSoapTemplateDropdownOpen
                                    "
                                    class="inline-flex min-h-[34px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#edede2] px-3 py-1 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#beedc0]"
                                    title="Pilih Template Cepat SOAP (Ctrl + Shift + T)"
                                >
                                    <Bookmark class="size-3.5 text-[#000000]" />
                                    <span>Gunakan Template SOAP</span>
                                    <kbd
                                        class="py-0.2 hidden rounded bg-[#000000] px-1 font-mono text-[9px] text-white md:inline-block"
                                        >Ctrl+Shift+T</kbd
                                    >
                                </button>

                                <button
                                    type="button"
                                    @click="isSaveTemplateModalOpen = true"
                                    class="inline-flex min-h-[34px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                                    title="Simpan Catatan Ini Sebagai Template Baru"
                                >
                                    <BookmarkPlus
                                        class="size-3.5 text-[#333333]"
                                    />
                                    <span class="hidden sm:inline"
                                        >Simpan Preset</span
                                    >
                                </button>

                                <div
                                    v-if="isSoapTemplateDropdownOpen"
                                    class="absolute top-10 right-0 z-50 w-72 space-y-1 rounded-[10px] border border-[#333333]/20 bg-[#fffff3] p-2 shadow-xl sm:w-80"
                                >
                                    <div
                                        class="flex items-center justify-between border-b border-[#333333]/10 px-2.5 py-1.5 text-xs font-bold text-[#000000]"
                                    >
                                        <span>Pilih Template Klinis</span>
                                        <button
                                            @click="
                                                isSoapTemplateDropdownOpen = false
                                            "
                                            class="text-[#333333]/60 hover:text-[#000000]"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>

                                    <div
                                        class="max-h-60 space-y-1 overflow-y-auto py-1"
                                    >
                                        <div
                                            v-for="tpl in soapTemplates"
                                            :key="tpl.soap_template_id"
                                            @click="applySoapTemplate(tpl)"
                                            class="cursor-pointer rounded-[7px] border border-transparent p-2 text-left transition-colors hover:border-[#333333]/15 hover:bg-[#beedc0]/50"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <span
                                                    class="text-xs font-bold text-[#000000]"
                                                    >{{
                                                        tpl.template_name
                                                    }}</span
                                                >
                                                <span
                                                    v-if="tpl.doctor_id"
                                                    class="py-0.2 rounded-full bg-[#000000] px-1.5 text-[9px] font-semibold text-white"
                                                    >Khusus Saya</span
                                                >
                                                <span
                                                    v-else
                                                    class="py-0.2 rounded-full bg-[#edede2] px-1.5 text-[9px] font-semibold text-[#333333]"
                                                    >Sistem</span
                                                >
                                            </div>
                                            <p
                                                v-if="tpl.assessment_template"
                                                class="mt-0.5 truncate text-[11px] text-[#333333]/70"
                                            >
                                                {{ tpl.assessment_template }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="soapTemplates.length === 0"
                                            class="py-4 text-center text-xs text-[#333333]/60"
                                        >
                                            Belum ada template yang tersimpan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        [S] Subjective (Anamnesis & Keluhan)
                                        <span class="text-rose-600">*</span>
                                    </label>
                                    <span class="text-[11px] text-[#333333]/60"
                                        >Keluhan utama, riwayat penyakit</span
                                    >
                                </div>
                                <textarea
                                    v-model="formSubjective"
                                    rows="3"
                                    placeholder="Pasien mengeluhkan demam sejak 3 hari lalu disertai batuk kering..."
                                    class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                    :class="{
                                        'border-rose-500':
                                            validationErrors.subjective,
                                    }"
                                ></textarea>
                                <p
                                    v-if="validationErrors.subjective"
                                    class="flex items-center gap-1 text-xs text-rose-600"
                                >
                                    <AlertCircle class="size-3" />
                                    {{ validationErrors.subjective }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        [O] Objective (Pemeriksaan Fisik Khusus)
                                    </label>
                                    <span class="text-[11px] text-[#333333]/60"
                                        >Kepala, toraks, abdomen,
                                        ekstremitas</span
                                    >
                                </div>
                                <textarea
                                    v-model="formPhysicalCheck"
                                    rows="3"
                                    placeholder="Kepala: normocephalic, Mata: anemis (-), Thorax: vesikuler normal, Abdomen: supel..."
                                    class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                ></textarea>
                            </div>

                            <div class="relative space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        [A] Assessment (Diagnosis & ICD-10)
                                        <span class="text-rose-600">*</span>
                                    </label>
                                    <span class="text-[11px] text-[#333333]/60"
                                        >Ketik nama penyakit/kode</span
                                    >
                                </div>
                                <div class="relative">
                                    <textarea
                                        v-model="formAssessment"
                                        @input="onAssessmentInput"
                                        @focus="searchIcd10(formAssessment)"
                                        rows="3"
                                        placeholder="Contoh: J00 - Nasofaringitis akut / K30 - Dispepsia..."
                                        class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                        :class="{
                                            'border-rose-500':
                                                validationErrors.assessment,
                                        }"
                                    ></textarea>

                                    <div
                                        v-if="
                                            showIcd10Dropdown &&
                                            icd10Suggestions.length > 0
                                        "
                                        class="absolute top-full right-0 left-0 z-50 mt-1 max-h-56 space-y-1 overflow-y-auto rounded-[8px] border border-[#333333]/20 bg-[#ffffff] p-1.5 shadow-xl"
                                    >
                                        <div
                                            class="flex items-center justify-between border-b border-[#333333]/10 px-2 py-1 text-[10px] font-bold tracking-wider text-[#333333]/70 uppercase"
                                        >
                                            <span>Rekomendasi ICD-10 WHO</span>
                                            <button
                                                type="button"
                                                @click="
                                                    showIcd10Dropdown = false
                                                "
                                                class="text-rose-600 hover:text-rose-800"
                                            >
                                                Tutup
                                            </button>
                                        </div>

                                        <div
                                            v-for="item in icd10Suggestions"
                                            :key="item.icd10_diagnosis_id"
                                            @click="selectIcd10(item)"
                                            class="flex cursor-pointer items-start justify-between gap-2 rounded-[6px] border border-transparent p-2 text-left transition-colors hover:border-[#333333]/15 hover:bg-[#beedc0]/60"
                                        >
                                            <div>
                                                <div
                                                    class="flex items-center gap-1.5"
                                                >
                                                    <span
                                                        class="py-0.2 rounded bg-[#edede2] px-1.5 font-mono text-xs font-bold text-[#000000]"
                                                    >
                                                        {{ item.code }}
                                                    </span>
                                                    <span
                                                        class="text-xs font-semibold text-[#000000]"
                                                        >{{
                                                            item.name_id
                                                        }}</span
                                                    >
                                                </div>
                                                <p
                                                    v-if="item.name_en"
                                                    class="mt-0.5 text-[11px] text-[#333333]/60 italic"
                                                >
                                                    {{ item.name_en }}
                                                </p>
                                            </div>
                                            <span
                                                v-if="item.is_common"
                                                class="py-0.2 shrink-0 rounded-full bg-[#beedc0] px-1.5 text-[9px] font-semibold text-[#000000]"
                                            >
                                                Umum
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    v-if="validationErrors.assessment"
                                    class="flex items-center gap-1 text-xs text-rose-600"
                                >
                                    <AlertCircle class="size-3" />
                                    {{ validationErrors.assessment }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-xs font-bold text-[#000000]"
                                    >
                                        [P] Plan (Rencana Terapi & Tindakan)
                                        <span class="text-rose-600">*</span>
                                    </label>
                                    <span class="text-[11px] text-[#333333]/60"
                                        >Instruksi medikasi, edukasi &
                                        kontrol</span
                                    >
                                </div>
                                <textarea
                                    v-model="formPlan"
                                    rows="3"
                                    placeholder="Terapi simptomatik, istirahat cukup, hidrasi 2L/hari, kontrol kembali jika demam > 3 hari."
                                    class="w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] p-3 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                    :class="{
                                        'border-rose-500':
                                            validationErrors.plan,
                                    }"
                                ></textarea>
                                <p
                                    v-if="validationErrors.plan"
                                    class="flex items-center gap-1 text-xs text-rose-600"
                                >
                                    <AlertCircle class="size-3" />
                                    {{ validationErrors.plan }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="space-y-4 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                    >
                        <div
                            class="flex flex-col justify-between gap-2 border-b border-[#333333]/10 pb-3 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-2">
                                <Pill class="size-4 text-[#000000]" />
                                <h3
                                    class="text-sm font-semibold tracking-wide text-[#000000] uppercase"
                                >
                                    3. Resep Obat Elektronik (E-Prescription)
                                </h3>
                                <span
                                    class="py-0.2 inline-flex items-center rounded-full bg-[#beedc0] px-2 text-[11px] font-bold text-[#000000]"
                                >
                                    {{ prescriptionRows.length }} Item Obat
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <motion.button
                                    type="button"
                                    :whileHover="{ scale: 1.02 }"
                                    :whileTap="{ scale: 0.98 }"
                                    @click="addPrescriptionRow"
                                    class="inline-flex min-h-[38px] cursor-pointer items-center gap-1.5 self-start rounded-[40.5px] bg-[#000000] px-4 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#333333] sm:self-auto"
                                >
                                    <Plus class="size-3.5 text-[#beedc0]" />
                                    <span>Tambah Baris Obat</span>
                                    <kbd
                                        class="py-0.2 hidden rounded bg-[#333333] px-1 font-mono text-[9px] text-white md:inline-block"
                                        >Ctrl+Shift+P</kbd
                                    >
                                </motion.button>
                            </div>
                        </div>

                        <div
                            v-if="safetyEvaluation.has_warnings"
                            class="space-y-2 rounded-[8px] border p-3.5 transition-all duration-200"
                            :class="
                                safetyEvaluation.has_severe
                                    ? 'border-rose-300 bg-rose-50 text-rose-900'
                                    : 'border-amber-300 bg-amber-50 text-amber-900'
                            "
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-bold"
                            >
                                <ShieldAlert
                                    v-if="safetyEvaluation.has_severe"
                                    class="size-4 shrink-0 text-rose-600"
                                />
                                <AlertTriangle
                                    v-else
                                    class="size-4 shrink-0 text-amber-600"
                                />
                                <span
                                    >Peringatan Keselamatan Peresepan Klinis
                                    (Clinical Safety Interceptor):</span
                                >
                            </div>

                            <div
                                v-if="
                                    safetyEvaluation.allergy_alerts.length > 0
                                "
                                class="space-y-1 pl-6"
                            >
                                <div
                                    v-for="(
                                        allAlert, aIdx
                                    ) in safetyEvaluation.allergy_alerts"
                                    :key="aIdx"
                                    class="flex items-start gap-1.5 text-xs"
                                >
                                    <span
                                        class="py-0.2 rounded bg-rose-200/70 px-1.5 text-[10px] font-bold text-rose-700"
                                        >ALERGI PASIEN</span
                                    >
                                    <span>{{ allAlert.message }}</span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    safetyEvaluation.interaction_alerts.length >
                                    0
                                "
                                class="space-y-1.5 pl-6"
                            >
                                <div
                                    v-for="(
                                        intAlert, iIdx
                                    ) in safetyEvaluation.interaction_alerts"
                                    :key="iIdx"
                                    class="space-y-0.5 rounded-[6px] border border-[#333333]/10 bg-white/80 p-2 text-xs"
                                >
                                    <div
                                        class="flex items-center gap-2 font-semibold text-[#000000]"
                                    >
                                        <span class="capitalize">{{
                                            intAlert.drug_1
                                        }}</span>
                                        <span>⚡</span>
                                        <span class="capitalize">{{
                                            intAlert.drug_2
                                        }}</span>
                                        <span
                                            class="py-0.2 rounded-full px-1.5 text-[9px] font-bold uppercase"
                                            :class="
                                                intAlert.severity === 'severe'
                                                    ? 'bg-rose-600 text-white'
                                                    : 'bg-amber-200 text-amber-900'
                                            "
                                        >
                                            {{ intAlert.severity }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-[11px] font-medium text-[#333333]"
                                    >
                                        {{ intAlert.mechanism }}
                                    </p>
                                    <p
                                        class="text-[11px] text-[#333333]/80 italic"
                                    >
                                        {{ intAlert.advice }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="prescriptionRows.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="(row, idx) in prescriptionRows"
                                :key="idx"
                                class="space-y-3 rounded-[8px] border border-[#333333]/15 bg-[#edede2]/40 p-3.5"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center gap-1.5 text-xs font-bold text-[#000000]"
                                    >
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-[#000000] text-[10px] text-white"
                                        >
                                            {{ idx + 1 }}
                                        </span>
                                        Item Obat #{{ idx + 1 }}
                                    </span>

                                    <button
                                        type="button"
                                        @click="removePrescriptionRow(idx)"
                                        class="inline-flex cursor-pointer items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-800"
                                    >
                                        <Trash2 class="size-3.5" />
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-3 md:grid-cols-12"
                                >
                                    <div class="space-y-1 md:col-span-4">
                                        <label
                                            class="text-[11px] font-semibold text-[#333333]"
                                            >Nama Obat & Sediaan</label
                                        >
                                        <select
                                            :value="row.medicine_id ?? ''"
                                            @change="
                                                handleMedicineChange(
                                                    idx,
                                                    Number(
                                                        (
                                                            $event.target as HTMLSelectElement
                                                        ).value,
                                                    ),
                                                )
                                            "
                                            class="min-h-[40px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                        >
                                            <option value="" disabled>
                                                -- Pilih Obat dari Apotek --
                                            </option>
                                            <option
                                                v-for="med in availableMedicines"
                                                :key="med.medicine_id"
                                                :value="med.medicine_id"
                                                :disabled="med.stock <= 0"
                                            >
                                                {{ med.name_medicine }} ({{
                                                    med.type
                                                }}) - Stok: {{ med.stock }}
                                                {{ med.unit }}
                                            </option>
                                        </select>

                                        <div
                                            v-if="row.selectedMedicine"
                                            class="flex items-center justify-between pt-0.5 text-[10px] text-[#333333]"
                                        >
                                            <span
                                                >Stok:
                                                <strong
                                                    >{{
                                                        row.selectedMedicine
                                                            .stock
                                                    }}
                                                    {{
                                                        row.selectedMedicine
                                                            .unit
                                                    }}</strong
                                                ></span
                                            >
                                            <span
                                                >Rp
                                                {{
                                                    Number(
                                                        row.selectedMedicine
                                                            .price,
                                                    ).toLocaleString('id-ID')
                                                }}
                                                /
                                                {{
                                                    row.selectedMedicine.unit
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <div class="space-y-1 md:col-span-2">
                                        <label
                                            class="text-[11px] font-semibold text-[#333333]"
                                            >Jumlah</label
                                        >
                                        <input
                                            v-model.number="row.quantity"
                                            type="number"
                                            min="1"
                                            :max="
                                                row.selectedMedicine?.stock ??
                                                100
                                            "
                                            class="min-h-[40px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                        />
                                    </div>

                                    <div class="space-y-1 md:col-span-3">
                                        <label
                                            class="text-[11px] font-semibold text-[#333333]"
                                            >Dosis & Frekuensi</label
                                        >
                                        <input
                                            v-model="row.dosage"
                                            list="dosage-options"
                                            placeholder="3 x 1 Tablet Sehari"
                                            class="min-h-[40px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                        />
                                    </div>

                                    <div class="space-y-1 md:col-span-3">
                                        <label
                                            class="text-[11px] font-semibold text-[#333333]"
                                            >Petunjuk Minum</label
                                        >
                                        <input
                                            v-model="row.instructions"
                                            list="instruction-options"
                                            placeholder="Sesudah makan"
                                            class="min-h-[40px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-2.5 py-1.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                        />
                                    </div>
                                </div>

                                <p
                                    v-if="
                                        validationErrors[
                                            `prescription_${idx}_stock`
                                        ]
                                    "
                                    class="flex items-center gap-1 text-xs text-rose-600"
                                >
                                    <AlertCircle class="size-3 shrink-0" />
                                    {{
                                        validationErrors[
                                            `prescription_${idx}_stock`
                                        ]
                                    }}
                                </p>
                            </div>

                            <datalist id="dosage-options">
                                <option
                                    v-for="d in dosagePresets"
                                    :key="d"
                                    :value="d"
                                />
                            </datalist>
                            <datalist id="instruction-options">
                                <option
                                    v-for="ins in instructionPresets"
                                    :key="ins"
                                    :value="ins"
                                />
                            </datalist>
                        </div>

                        <div
                            v-else
                            class="rounded-[8px] border border-dashed border-[#333333]/20 bg-[#edede2]/20 py-6 text-center"
                        >
                            <Pill class="mx-auto size-6 text-[#333333]/40" />
                            <p class="mt-1 text-xs font-medium text-[#333333]">
                                Belum ada item obat yang diresepkan.
                            </p>
                            <p class="text-[11px] text-[#333333]/60">
                                Klik tombol "+ Tambah Baris Obat" di atas untuk
                                menambahkan resep apotek.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 pt-2 md:grid-cols-2">
                            <div class="space-y-1">
                                <label
                                    class="text-[11px] font-semibold text-[#333333]"
                                    >Catatan Khusus untuk Bagian Farmasi /
                                    Pasien</label
                                >
                                <input
                                    v-model="prescriptionNotes"
                                    type="text"
                                    placeholder="Contoh: Obat sirup dikocok dahulu, antibiotik wajib dihabiskan..."
                                    class="min-h-[38px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-1.5 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                />
                            </div>

                            <div
                                v-if="prescriptionRows.length > 0"
                                class="flex flex-col items-end justify-center rounded-[7px] bg-[#edede2]/60 p-3 text-right"
                            >
                                <span class="text-[11px] text-[#333333]"
                                    >Estimasi Total Biaya Obat:</span
                                >
                                <span
                                    class="font-mono text-base font-bold text-[#000000]"
                                >
                                    Rp
                                    {{
                                        estimatedTotalPrescriptionCost.toLocaleString(
                                            'id-ID',
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div
                        class="flex items-center justify-between border-b border-[#333333]/10 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <History class="size-5 text-[#000000]" />
                            <h3
                                class="text-sm font-semibold tracking-wide text-[#000000] uppercase"
                            >
                                Riwayat Rekam Medis Pasien:
                                {{ appointment?.patient?.name }}
                            </h3>
                        </div>

                        <button
                            type="button"
                            @click="
                                appointment?.patient?.patient_id &&
                                fetchPatientHistory(
                                    appointment.patient.patient_id,
                                )
                            "
                            class="inline-flex min-h-[32px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-3 py-1 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                        >
                            <RefreshCw
                                class="size-3"
                                :class="{ 'animate-spin': isLoadingHistory }"
                            />
                            <span>Perbarui Data</span>
                        </button>
                    </div>

                    <div
                        v-if="isLoadingHistory"
                        class="space-y-2 py-12 text-center"
                    >
                        <Loader2
                            class="mx-auto size-7 animate-spin text-[#000000]"
                        />
                        <p class="text-xs text-[#333333]">
                            Memuat riwayat rekam medis pasien dari database...
                        </p>
                    </div>

                    <div
                        v-else-if="historyError"
                        class="flex items-center gap-2 rounded-[8px] border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700"
                    >
                        <AlertCircle class="size-4 shrink-0" />
                        <span>{{ historyError }}</span>
                    </div>

                    <div
                        v-else-if="patientHistory.length > 0"
                        class="relative space-y-6 pl-6 before:absolute before:top-2 before:bottom-2 before:left-2 before:w-0.5 before:bg-[#333333]/20"
                    >
                        <div
                            v-for="(rec, rIdx) in patientHistory"
                            :key="rec.medical_record_id || rIdx"
                            class="relative space-y-3 rounded-[10px] border border-[#333333]/15 bg-[#ffffff] p-4 shadow-xs sm:p-5"
                        >
                            <span
                                class="absolute top-5 -left-6 flex h-4 w-4 rounded-full border-2 border-[#fffff3] bg-[#000000]"
                            ></span>

                            <div
                                class="flex flex-col justify-between gap-2 border-b border-[#333333]/10 pb-3 sm:flex-row sm:items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <Calendar
                                            class="size-3.5 text-[#000000]"
                                        />
                                        <span
                                            class="text-sm font-bold text-[#000000]"
                                        >
                                            Kunjungan:
                                            {{ formatDate(rec.created_at) }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-[#333333]">
                                        Dokter Pemeriksa:
                                        <strong>{{
                                            rec.doctor?.name ||
                                            'Dokter Spesialis'
                                        }}</strong>
                                        ({{
                                            (rec.doctor?.specialization as any)
                                                ?.name_specialization ||
                                            'Spesialis'
                                        }})
                                    </p>
                                </div>

                                <span
                                    v-if="rec.prescription"
                                    class="inline-flex items-center gap-1 self-start rounded-full border border-[#333333]/15 bg-[#beedc0] px-2.5 py-0.5 text-xs font-semibold text-[#000000] sm:self-auto"
                                >
                                    <Pill class="size-3" />
                                    Resep:
                                    {{ rec.prescription.prescription_number }}
                                </span>
                            </div>

                            <div
                                v-if="rec.objective"
                                class="flex flex-wrap gap-3 rounded-[7px] bg-[#edede2]/60 p-2.5 text-xs text-[#333333]"
                            >
                                <span
                                    v-if="
                                        (rec.objective as any).systolic &&
                                        (rec.objective as any).diastolic
                                    "
                                >
                                    <strong>TD:</strong>
                                    {{ (rec.objective as any).systolic }}/{{
                                        (rec.objective as any).diastolic
                                    }}
                                    mmHg
                                </span>
                                <span v-if="(rec.objective as any).pulse">
                                    <strong>Nadi:</strong>
                                    {{ (rec.objective as any).pulse }} bpm
                                </span>
                                <span v-if="(rec.objective as any).temperature">
                                    <strong>Suhu:</strong>
                                    {{ (rec.objective as any).temperature }} °C
                                </span>
                                <span v-if="(rec.objective as any).weight">
                                    <strong>BB:</strong>
                                    {{ (rec.objective as any).weight }} kg
                                </span>
                                <span v-if="(rec.objective as any).bmi">
                                    <strong>BMI:</strong>
                                    {{ (rec.objective as any).bmi }}
                                </span>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block font-semibold text-[#000000]"
                                        >[S] Keluhan:</span
                                    >
                                    <p
                                        class="rounded-[6px] border border-[#333333]/10 bg-[#fffff3] p-2 text-[#333333]"
                                    >
                                        {{ rec.subjective || '-' }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <span
                                        class="block font-semibold text-[#000000]"
                                        >[A] Diagnosis Kerja:</span
                                    >
                                    <p
                                        class="rounded-[6px] border border-[#333333]/10 bg-[#beedc0]/30 p-2 font-medium text-[#000000]"
                                    >
                                        {{ rec.assessment || '-' }}
                                    </p>
                                </div>

                                <div class="space-y-1 sm:col-span-2">
                                    <span
                                        class="block font-semibold text-[#000000]"
                                        >[P] Rencana Terapi:</span
                                    >
                                    <p
                                        class="rounded-[6px] border border-[#333333]/10 bg-[#fffff3] p-2 text-[#333333]"
                                    >
                                        {{ rec.plan || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="
                                    rec.prescription?.items &&
                                    rec.prescription.items.length > 0
                                "
                                class="space-y-1.5 border-t border-[#333333]/10 pt-2"
                            >
                                <span
                                    class="flex items-center gap-1 text-xs font-semibold text-[#000000]"
                                >
                                    <Pill class="size-3 text-[#000000]" />
                                    Obat yang Diberikan:
                                </span>
                                <div
                                    class="grid grid-cols-1 gap-2 text-xs sm:grid-cols-2"
                                >
                                    <div
                                        v-for="pItem in rec.prescription.items"
                                        :key="pItem.prescription_item_id"
                                        class="rounded-[6px] border border-[#333333]/10 bg-[#fffff3] p-2"
                                    >
                                        <div class="font-bold text-[#000000]">
                                            {{
                                                pItem.medicine?.name_medicine ||
                                                'Obat'
                                            }}
                                            ({{ pItem.quantity }}
                                            {{
                                                pItem.medicine?.unit || 'Item'
                                            }})
                                        </div>
                                        <div class="text-[11px] text-[#333333]">
                                            {{ pItem.dosage }} ·
                                            {{ pItem.instructions }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-[10px] border border-dashed border-[#333333]/20 bg-[#ffffff] py-12 text-center"
                    >
                        <CheckCircle2
                            class="mx-auto size-8 text-[#333333]/40"
                        />
                        <p class="mt-2 text-sm font-semibold text-[#000000]">
                            Belum ada riwayat rekam medis sebelumnya.
                        </p>
                        <p class="text-xs text-[#333333]/60">
                            Ini merupakan kunjungan atau pencatatan EMR pertama
                            bagi pasien ini di sistem rumah sakit.
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        validationErrors.general ||
                        Object.keys(validationErrors).length > 0
                    "
                    class="space-y-1 rounded-[8px] border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700"
                >
                    <div class="flex items-center gap-1.5 font-bold">
                        <AlertCircle class="size-4" />
                        <span
                            >Mohon lengkapi bagian formulir yang bertanda
                            merah:</span
                        >
                    </div>
                    <ul class="list-inside list-disc space-y-0.5 text-[11px]">
                        <li v-for="(err, key) in validationErrors" :key="key">
                            {{ err }}
                        </li>
                    </ul>
                </div>

                <div
                    v-if="submitSuccessMessage"
                    class="flex items-center gap-2 rounded-[8px] border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-800"
                >
                    <CheckCircle2 class="size-4 text-emerald-600" />
                    <span class="font-semibold">{{
                        submitSuccessMessage
                    }}</span>
                </div>
            </div>

            <footer
                class="flex shrink-0 flex-col-reverse justify-between gap-3 border-t border-[#333333]/15 bg-[#edede2] px-5 py-3.5 sm:flex-row sm:items-center"
            >
                <div class="text-xs text-[#333333]">
                    <span
                        >Dokter Pemeriksa:
                        <strong>{{
                            appointment?.doctorSchedule?.doctor?.name ||
                            'Dokter Terdaftar'
                        }}</strong></span
                    >
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2.5">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        @click="closeModal"
                        class="min-h-[44px] cursor-pointer rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-5 py-2 text-xs font-semibold text-[#333333] transition-colors hover:bg-[#edede2]"
                    >
                        Tutup & Kembali
                    </motion.button>

                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                        @click="submitConsultation"
                        :disabled="isSubmitting"
                        class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-bold text-white shadow-md transition-all hover:bg-[#222222] disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <CheckCircle2 v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            isSubmitting
                                ? 'Menyimpan Rekam Medis...'
                                : 'Simpan EMR & Selesaikan Konsultasi'
                        }}</span>
                    </motion.button>
                </div>
            </footer>
        </motion.div>

        <div
            v-if="isSaveTemplateModalOpen"
            class="fixed inset-0 z-60 flex items-center justify-center bg-[#000000]/60 p-4 backdrop-blur-xs"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-md space-y-4 rounded-[12px] border border-[#333333]/20 bg-[#fffff3] p-5 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-[#333333]/10 pb-3"
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#beedc0]"
                        >
                            <BookmarkPlus class="size-4 text-[#000000]" />
                        </div>
                        <h3
                            class="font-['ivypresto-headline'] text-base font-bold text-[#000000]"
                        >
                            Simpan Sebagai Template SOAP
                        </h3>
                    </div>
                    <button
                        @click="isSaveTemplateModalOpen = false"
                        class="text-[#333333] hover:text-[#000000]"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <div class="space-y-3 text-xs text-[#333333]">
                    <p>
                        Isian Subjective, Objective, Assessment, dan Plan saat
                        ini akan disimpan sebagai template cepat khusus akun
                        Anda.
                    </p>

                    <div class="space-y-1">
                        <label class="font-bold text-[#000000]"
                            >Nama / Judul Template
                            <span class="text-rose-600">*</span></label
                        >
                        <input
                            v-model="newTemplateName"
                            type="text"
                            placeholder="Contoh: ISPA Dewasa Ringan / Gastritis Fungsional"
                            class="min-h-[44px] w-full rounded-[7px] border border-[#333333]/20 bg-[#ffffff] px-3 py-2 text-xs text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                            @keyup.enter="saveCurrentAsSoapTemplate"
                        />
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-[#333333]/10 pt-2"
                >
                    <button
                        type="button"
                        @click="isSaveTemplateModalOpen = false"
                        class="min-h-[40px] rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveCurrentAsSoapTemplate"
                        :disabled="isSavingTemplate || !newTemplateName.trim()"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-5 text-xs font-bold text-white hover:bg-[#222222] disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isSavingTemplate"
                            class="size-3.5 animate-spin text-[#beedc0]"
                        />
                        <Check v-else class="size-3.5 text-[#beedc0]" />
                        <span>{{
                            isSavingTemplate
                                ? 'Menyimpan...'
                                : 'Simpan Template'
                        }}</span>
                    </button>
                </div>
            </motion.div>
        </div>
    </div>
</template>
