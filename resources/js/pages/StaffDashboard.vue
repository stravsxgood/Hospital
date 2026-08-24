<script setup lang="ts">
/**
 * @file StaffDashboard.vue
 * @description Pusat Komando Operasional & Meja Depan SIMRS (Front-Office, Farmasi, & Billing POS).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - 1. Panggilan Suara Antrean (Audio Calling) EKSKLUSIF hanya untuk Dokter (/doctor/queue).
 *  - 2. Tanggung Jawab Utama Staf:
 *      * Front-Office: Verifikasi kedatangan dan konfirmasi antrean pasien (Check-in).
 *      * Farmasi / Apotek: Antrean pemenuhan resep elektronik, monitoring stok obat kritis, dan link inventori.
 *      * Kasir & Billing: Point-of-sale billing tagihan pasien dan link pembayaran POS.
 *      * Dashboard Operasional: Agregasi KPI real-time, matriks poli, dan peringatan obat.
 *  - 3. Desain Sistem Evergreen:
 *      * Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *      * Typography: IvyPresto Headline serif + Rubik sans.
 *      * Motion-V untuk micro-interactions & feedback interaktif.
 *      * Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowRight,
    ArrowUpRight,
    BarChart3,
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Download,
    Eye,
    FileText,
    Filter,
    HeartPulse,
    Layers,
    Monitor,
    Package,
    PackageCheck,
    PackagePlus,
    Percent,
    PieChart,
    Pill,
    PlusCircle,
    Receipt,
    RefreshCw,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Stethoscope,
    Tv,
    User,
    UserCheck,
    Users,
    X,
} from '@lucide/vue';
import axios from 'axios';
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

/* ═══════════════════════════════════════════════════════════════
   TypeScript Interfaces & Data Contracts
   ═══════════════════════════════════════════════════════════════ */
export interface ClinicStatus {
    schedule_id: number;
    doctor_name: string;
    specialization: string;
    poli_name: string;
    room_name: string;
    start_time: string;
    end_time: string;
    current_calling: string | null;
    waiting_count: number;
    completed_count: number;
    total_patients: number;
    quota_day: number;
}

export interface WeeklyTrendItem {
    day: string;
    date: string;
    count: number;
}

export interface PoliDistributionItem {
    poli_name: string;
    count: number;
    percent: number;
}

export interface PrescriptionItemData {
    prescription_item_id: number;
    medicine_id: number;
    quantity: number;
    dosage: string;
    notes?: string;
    medicine?: {
        medicine_id: number;
        name_medicine: string;
        type: string;
        unit: string;
        stock: number;
    };
}

export interface PrescriptionData {
    prescription_id: number;
    prescription_number: string;
    status: 'menunggu' | 'diproses' | 'selesai';
    notes?: string;
    created_at: string;
    medical_record?: {
        medical_record_id: number;
        patient?: {
            name: string;
            resident_n?: string;
            gender?: string;
        };
        doctor?: {
            name: string;
            specialization?: {
                name_specialization?: string;
            };
        };
        reservation?: {
            doctor_schedule?: {
                poli?: {
                    name_poli?: string;
                    name?: string;
                };
            };
        };
    };
    items: PrescriptionItemData[];
}

export interface CriticalMedicineData {
    medicine_id: number;
    code_medicine: string;
    name_medicine: string;
    type: string;
    stock: number;
    unit: string;
    price: string | number;
}

export interface AppointmentItem {
    appointment_id: number;
    queue_number: string;
    appointment_date: string;
    complaint: string | null;
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled';
    updated_at: string;
    patient?: {
        name: string;
        resident_n?: string;
        gender?: string;
        phone_number?: string;
    };
    doctor_schedule?: {
        doctor_schedule_id?: number;
        doctor?: {
            doctor_id?: number;
            name: string;
            specialization?: {
                name_specialization?: string;
            };
        };
        poli?: {
            poli_id?: number;
            name_poli?: string;
            name?: string;
        };
        room?: {
            room_id?: number;
            name_room?: string;
        };
    };
    billing?: {
        billing_id: number;
        status: string;
        total_amount: number;
    };
    medical_record?: {
        medical_record_id: number;
        prescription?: {
            prescription_id: number;
            status: string;
        };
    };
}

/* ═══════════════════════════════════════════════════════════════
   Component Props
   ═══════════════════════════════════════════════════════════════ */
const props = defineProps<{
    stats: {
        total: number;
        total_upcoming?: number;
        total_all?: number;
        waiting_confirmation?: number;
        confirmed?: number;
        in_progress?: number;
        completed?: number;
        cancelled?: number;
        pending_prescriptions?: number;
        out_of_stock_medicines?: number;
        low_stock_medicines?: number;
        unpaid_billings?: number;
        today_revenue?: number;
        quota_percentage?: number;
    };
    todayQueue?: AppointmentItem[];
    pendingPrescriptions?: PrescriptionData[];
    criticalMedicines?: CriticalMedicineData[];
    clinicMatrix?: ClinicStatus[];
    weeklyTrend?: WeeklyTrendItem[];
    poliDistribution?: PoliDistributionItem[];
    currentDate: string;
    selectedDate?: string;
}>();

/* ═══════════════════════════════════════════════════════════════
   Auth Context & Role Detection
   ═══════════════════════════════════════════════════════════════ */
const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const isDoctor = computed(() => {
    const role = authUser.value?.role;

    return (
        role === 'doctor' ||
        Boolean(authUser.value?.is_doctor) ||
        Boolean(authUser.value?.doctor)
    );
});

const isNurse = computed(() => {
    const role = authUser.value?.role;

    return role === 'nurse' || Boolean(authUser.value?.nurse);
});

const isNurseKoas = computed(() => {
    return (
        isNurse.value &&
        (authUser.value?.nurse?.type === 'koas' ||
            Boolean(authUser.value?.nurse?.is_koas))
    );
});

const isNurseTetap = computed(() => {
    return isNurse.value && !isNurseKoas.value;
});

const isAdmin = computed(() => authUser.value?.role === 'admin');

// Informasi Dokter Login
const doctorProfile = computed(
    () =>
        authUser.value?.doctor as
            | {
                  doctor_id?: number;
                  name?: string;
                  sip_number?: string;
                  specialization_name?: string;
              }
            | null
            | undefined,
);
const doctorName = computed<string>(() => {
    if (doctorProfile.value?.name) {
        return doctorProfile.value.name;
    }

    if (isDoctor.value && authUser.value?.name) {
        return authUser.value.name;
    }

    return 'Dokter Spesialis';
});

// Jadwal Praktik Aktif Khusus Dokter yang Sedang Login
const doctorActiveClinic = computed(() => {
    if (!isDoctor.value || !props.clinicMatrix) {
        return null;
    }

    return (
        props.clinicMatrix.find((c) => {
            if (doctorProfile.value?.name) {
                return c.doctor_name
                    .toLowerCase()
                    .includes(doctorProfile.value.name.toLowerCase());
            }

            if (authUser.value?.name) {
                return c.doctor_name
                    .toLowerCase()
                    .includes(authUser.value.name.toLowerCase());
            }

            return false;
        }) ?? null
    );
});

// Format Rupiah
const formatRupiah = (val: number | string | null | undefined): string => {
    const num = typeof val === 'string' ? parseFloat(val) : val || 0;

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

/* ═══════════════════════════════════════════════════════════════
   Search & Filter Table State
   ═══════════════════════════════════════════════════════════════ */
const searchQuery = ref('');
const selectedStatusFilter = ref<
    'all' | 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled'
>('all');
const selectedPoliFilter = ref<string>('all');
const onlyMyClinicPatients = ref(false);
const isRefreshing = ref(false);

const handleRefresh = () => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            setTimeout(() => {
                isRefreshing.value = false;
            }, 400);
        },
    });
};

// Filtered Queue List
const filteredAppointments = computed(() => {
    const list = props.todayQueue || [];

    return list.filter((item) => {
        // 1. Filter Khusus Dokter Sendiri
        if (
            isDoctor.value &&
            onlyMyClinicPatients.value &&
            doctorActiveClinic.value
        ) {
            if (
                item.doctor_schedule?.doctor_schedule_id !==
                doctorActiveClinic.value.schedule_id
            ) {
                return false;
            }
        }

        // 2. Filter Status
        if (
            selectedStatusFilter.value !== 'all' &&
            item.status !== selectedStatusFilter.value
        ) {
            return false;
        }

        // 3. Filter Poli
        if (selectedPoliFilter.value !== 'all') {
            const poliName =
                item.doctor_schedule?.poli?.name_poli ||
                item.doctor_schedule?.poli?.name;

            if (poliName !== selectedPoliFilter.value) {
                return false;
            }
        }

        // 4. Filter Query Search
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase().trim();
            const patientName = item.patient?.name?.toLowerCase() || '';
            const residentN = item.patient?.resident_n?.toLowerCase() || '';
            const queueNum = item.queue_number?.toLowerCase() || '';
            const docName =
                item.doctor_schedule?.doctor?.name?.toLowerCase() || '';

            return (
                patientName.includes(q) ||
                residentN.includes(q) ||
                queueNum.includes(q) ||
                docName.includes(q)
            );
        }

        return true;
    });
});

// Daftar Pilihan Poliklinik untuk Filter
const availablePolis = computed(() => {
    const list = props.todayQueue || [];
    const polis = new Set<string>();
    list.forEach((item) => {
        const name =
            item.doctor_schedule?.poli?.name_poli ||
            item.doctor_schedule?.poli?.name;

        if (name) {
            polis.add(name);
        }
    });

    return Array.from(polis);
});

/* ═══════════════════════════════════════════════════════════════
   Action Handlers: Front-Office Check-in & Pharmacy Dispensation
   ═══════════════════════════════════════════════════════════════ */
const actionLoadingId = ref<number | null>(null);
const prescriptionLoadingId = ref<number | null>(null);

// 1. Front-Office Check-in (Konfirmasi Kedatangan Pasien)
const confirmArrival = (appointment: AppointmentItem) => {
    actionLoadingId.value = appointment.appointment_id;
    router.post(
        `/staff/reservations/${appointment.appointment_id}/confirm-arrival`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                actionLoadingId.value = null;
            },
        },
    );
};

// 2. Pharmacy: Mulai Meracik Resep
const processPrescription = (rx: PrescriptionData) => {
    prescriptionLoadingId.value = rx.prescription_id;
    router.post(
        `/staff/prescriptions/${rx.prescription_id}/process`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                prescriptionLoadingId.value = null;
            },
        },
    );
};

// 3. Pharmacy: Selesai Meracik Resep & Potong Stok
const completePrescription = (rx: PrescriptionData) => {
    prescriptionLoadingId.value = rx.prescription_id;
    router.post(
        `/staff/prescriptions/${rx.prescription_id}/complete`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                prescriptionLoadingId.value = null;
            },
        },
    );
};

// Status Badge Styling Helper
const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return {
                label: 'Menunggu Check-in',
                classes: 'bg-amber-100 text-amber-800 border-amber-200',
                dot: 'bg-amber-500',
            };
        case 'confirmed':
            return {
                label: 'Dikonfirmasi (Hadir)',
                classes: 'bg-blue-100 text-blue-800 border-blue-200',
                dot: 'bg-blue-500',
            };
        case 'in_progress':
            return {
                label: 'Sedang Diperiksa',
                classes:
                    'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold animate-pulse',
                dot: 'bg-emerald-600',
            };
        case 'completed':
            return {
                label: 'Selesai',
                classes: 'bg-[#edede2] text-[#333333] border-[#333333]/20',
                dot: 'bg-[#333333]',
            };
        case 'cancelled':
            return {
                label: 'Dibatalkan',
                classes: 'bg-rose-100 text-rose-700 border-rose-200',
                dot: 'bg-rose-500',
            };
        default:
            return {
                label: status,
                classes: 'bg-neutral-100 text-neutral-700 border-neutral-200',
                dot: 'bg-neutral-400',
            };
    }
};

// Langganan instan ke Laravel Echo Private Channel 'pharmacy' untuk notifikasi resep masuk
onMounted(() => {
    try {
        import('@/echo').then(({ echo }) => {
            echo.private('pharmacy')
                .listen('PrescriptionCreatedEvent', () => {
                    router.reload({ only: ['pendingPrescriptions', 'stats'] });
                })
                .listen('.PrescriptionCreatedEvent', () => {
                    router.reload({ only: ['pendingPrescriptions', 'stats'] });
                });
        });
    } catch (e) {
        console.warn('Echo pharmacy channel error:', e);
    }
});

onBeforeUnmount(() => {
    try {
        import('@/echo').then(({ echo }) => {
            echo.leave('pharmacy');
        });
    } catch (e) {}
});
</script>

<template>
    <Head title="Pusat Komando Operasional Medis - Hospital Population" />

    <div
        class="min-h-screen bg-[#edede2] px-4 py-6 font-['Rubik'] text-[#000000] sm:px-6 lg:px-8"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Dynamic Hero Banner & Role Context
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: -15 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.28, ease: 'easeOut' }"
                class="relative overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm sm:p-8"
            >
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Sapaan & Status Role -->
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Badge Role Aktif -->
                            <span
                                v-if="isDoctor"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#065f46] px-3 py-1 text-xs font-bold text-[#ffffff]"
                            >
                                <Stethoscope class="size-3.5" />
                                <span>Dokter Spesialis</span>
                            </span>
                            <span
                                v-else-if="isNurseTetap"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-1 text-xs font-bold text-[#065f46]"
                            >
                                <ShieldCheck class="size-3.5" />
                                <span>Staf & Perawat Tetap (Pekerja)</span>
                            </span>
                            <span
                                v-else-if="isNurseKoas"
                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800"
                            >
                                <HeartPulse class="size-3.5" />
                                <span>Dokter Muda / Koas</span>
                            </span>

                            <span class="text-xs font-medium text-[#333333]/70">
                                {{ currentDate }}
                            </span>
                        </div>

                        <!-- Judul Sapaan Dinamis -->
                        <h1
                            class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl lg:text-4xl"
                        >
                            <template v-if="isDoctor">
                                Selamat Bertugas, {{ doctorName }}
                            </template>
                            <template v-else>
                                Pusat Komando Operasional & Meja Depan
                            </template>
                        </h1>

                        <p
                            class="max-w-2xl text-xs leading-relaxed text-[#333333]/80 sm:text-sm"
                        >
                            <template v-if="isDoctor">
                                Kelola konsultasi medis pasien, periksa riwayat
                                rekam medis SOAP, dan terbitkan resep elektronik
                                langsung ke bagian farmasi.
                            </template>
                            <template v-else>
                                Verifikasi kedatangan pasien (check-in), antrean
                                peracikan resep obat, manajemen kasir & billing
                                POS, dan pemantauan stok farmasi.
                            </template>
                        </p>
                    </div>

                    <!-- Quick CTA Buttons -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- CTA Dokter: Masuk ke Ruang Pemeriksaan EMR -->
                        <motion.div
                            v-if="isDoctor"
                            :whileHover="{ scale: 1.03, y: -2 }"
                            :whileTap="{ scale: 0.97 }"
                            class="w-full sm:w-auto"
                        >
                            <Link
                                :href="
                                    doctorActiveClinic
                                        ? `/doctor/queue?schedule_id=${doctorActiveClinic.schedule_id}`
                                        : '/doctor/queue'
                                "
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-bold text-[#ffffff] shadow-md transition-all hover:bg-[#333333] sm:w-auto sm:text-sm"
                            >
                                <Activity class="size-4 text-[#beedc0]" />
                                <span>Papan Konsultasi Pasien</span>
                                <ArrowRight class="size-4 text-[#beedc0]" />
                            </Link>
                        </motion.div>

                        <!-- CTA Staf: Inventori Obat Farmasi -->
                        <motion.div
                            v-if="isNurseTetap || isAdmin"
                            :whileHover="{ scale: 1.03, y: -2 }"
                            :whileTap="{ scale: 0.97 }"
                            class="w-full sm:w-auto"
                        >
                            <Link
                                href="/staff/medicines"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] bg-[#065f46] px-5 py-2.5 text-xs font-bold text-[#ffffff] shadow-sm transition-all hover:bg-[#054d38] sm:w-auto sm:text-sm"
                            >
                                <Pill class="size-4 text-[#beedc0]" />
                                <span>Inventori Obat</span>
                                <ArrowUpRight class="size-3.5 text-[#beedc0]" />
                            </Link>
                        </motion.div>

                        <!-- CTA Staf: Kasir & Billing POS -->
                        <motion.div
                            v-if="isNurseTetap || isAdmin"
                            :whileHover="{ scale: 1.03, y: -2 }"
                            :whileTap="{ scale: 0.97 }"
                            class="w-full sm:w-auto"
                        >
                            <Link
                                href="/staff/billing"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-[40.5px] border border-[#333333]/20 bg-[#fffff3] px-5 py-2.5 text-xs font-bold text-[#000000] shadow-sm transition-all hover:bg-[#edede2] sm:w-auto sm:text-sm"
                            >
                                <Receipt class="size-4 text-[#065f46]" />
                                <span>Kasir & Billing (POS)</span>
                                <ArrowUpRight class="size-3.5 text-[#000000]" />
                            </Link>
                        </motion.div>

                        <!-- CTA Publik Layar TV -->
                        <motion.div
                            :whileHover="{ scale: 1.03, y: -2 }"
                            :whileTap="{ scale: 0.97 }"
                        >
                            <Link
                                href="/display"
                                target="_blank"
                                class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] border border-[#333333]/15 bg-[#edede2]/60 px-4 py-2.5 text-xs font-bold text-[#333333] transition-all hover:bg-[#edede2] hover:text-[#000000]"
                            >
                                <Tv class="size-4 text-[#065f46]" />
                                <span class="hidden sm:inline"
                                    >Layar TV Ruang Tunggu</span
                                >
                                <span class="sm:hidden">Layar TV</span>
                            </Link>
                        </motion.div>

                        <!-- Tombol Refresh Data -->
                        <motion.button
                            type="button"
                            :whileHover="{ scale: 1.05 }"
                            :whileTap="{ scale: 0.95 }"
                            @click="handleRefresh"
                            :disabled="isRefreshing"
                            class="inline-flex size-11 min-h-[44px] items-center justify-center rounded-full border border-[#333333]/15 bg-[#fffff3] text-[#333333] transition-all hover:bg-[#edede2] hover:text-[#000000]"
                            title="Segarkan Data Dashboard"
                        >
                            <RefreshCw
                                class="size-4"
                                :class="
                                    isRefreshing
                                        ? 'animate-spin text-[#065f46]'
                                        : ''
                                "
                            />
                        </motion.button>
                    </div>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Grid Metrik KPI Operasional & Farmasi
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
                <!-- 1. Total Pasien Hari Ini -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.05 }"
                    class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider text-[#333333]/70 uppercase"
                            >Pasien Hari Ini</span
                        >
                        <div
                            class="flex size-7 items-center justify-center rounded-full bg-[#beedc0]/40 text-[#065f46]"
                        >
                            <Users class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-['ivypresto-headline'] text-2xl font-bold text-[#000000]"
                    >
                        {{ stats.total }}
                    </div>
                    <div class="mt-1 text-[11px] text-[#333333]/70">
                        {{ stats.confirmed ?? 0 }} hadir / siap periksa
                    </div>
                </motion.div>

                <!-- 2. Menunggu Check-in Front-Office -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.08 }"
                    class="rounded-[10px] border p-4 shadow-sm transition-all"
                    :class="
                        (stats.waiting_confirmation ?? 0) > 0
                            ? 'border-amber-300 bg-amber-50/60'
                            : 'border-[#333333]/12 bg-[#fffff3]'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider uppercase"
                            :class="
                                (stats.waiting_confirmation ?? 0) > 0
                                    ? 'text-amber-900'
                                    : 'text-[#333333]/70'
                            "
                        >
                            Perlu Check-in
                        </span>
                        <div
                            class="flex size-7 items-center justify-center rounded-full"
                            :class="
                                (stats.waiting_confirmation ?? 0) > 0
                                    ? 'bg-amber-100 text-amber-800'
                                    : 'bg-[#beedc0]/40 text-[#065f46]'
                            "
                        >
                            <UserCheck class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-['ivypresto-headline'] text-2xl font-bold"
                        :class="
                            (stats.waiting_confirmation ?? 0) > 0
                                ? 'text-amber-800'
                                : 'text-[#000000]'
                        "
                    >
                        {{ stats.waiting_confirmation ?? 0 }}
                    </div>
                    <div
                        class="mt-1 text-[11px]"
                        :class="
                            (stats.waiting_confirmation ?? 0) > 0
                                ? 'font-medium text-amber-800'
                                : 'text-[#333333]/70'
                        "
                    >
                        Belum verifikasi meja depan
                    </div>
                </motion.div>

                <!-- 3. Antrean Resep Farmasi -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.11 }"
                    class="rounded-[10px] border p-4 shadow-sm transition-all"
                    :class="
                        (stats.pending_prescriptions ?? 0) > 0
                            ? 'border-blue-300 bg-blue-50/60'
                            : 'border-[#333333]/12 bg-[#fffff3]'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider uppercase"
                            :class="
                                (stats.pending_prescriptions ?? 0) > 0
                                    ? 'text-blue-900'
                                    : 'text-[#333333]/70'
                            "
                        >
                            Resep Farmasi
                        </span>
                        <div
                            class="flex size-7 items-center justify-center rounded-full"
                            :class="
                                (stats.pending_prescriptions ?? 0) > 0
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-[#beedc0]/40 text-[#065f46]'
                            "
                        >
                            <Pill class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-['ivypresto-headline'] text-2xl font-bold"
                        :class="
                            (stats.pending_prescriptions ?? 0) > 0
                                ? 'text-blue-800'
                                : 'text-[#000000]'
                        "
                    >
                        {{ stats.pending_prescriptions ?? 0 }}
                    </div>
                    <div
                        class="mt-1 text-[11px]"
                        :class="
                            (stats.pending_prescriptions ?? 0) > 0
                                ? 'font-medium text-blue-800'
                                : 'text-[#333333]/70'
                        "
                    >
                        Resep menunggu diracik
                    </div>
                </motion.div>

                <!-- 4. Peringatan Obat Kritis -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.14 }"
                    class="rounded-[10px] border p-4 shadow-sm transition-all"
                    :class="
                        (stats.out_of_stock_medicines ?? 0) +
                            (stats.low_stock_medicines ?? 0) >
                        0
                            ? 'border-rose-300 bg-rose-50/60'
                            : 'border-[#333333]/12 bg-[#fffff3]'
                    "
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider uppercase"
                            :class="
                                (stats.out_of_stock_medicines ?? 0) +
                                    (stats.low_stock_medicines ?? 0) >
                                0
                                    ? 'text-rose-900'
                                    : 'text-[#333333]/70'
                            "
                        >
                            Obat Kritis
                        </span>
                        <div
                            class="flex size-7 items-center justify-center rounded-full"
                            :class="
                                (stats.out_of_stock_medicines ?? 0) +
                                    (stats.low_stock_medicines ?? 0) >
                                0
                                    ? 'bg-rose-100 text-rose-700'
                                    : 'bg-[#beedc0]/40 text-[#065f46]'
                            "
                        >
                            <ShieldAlert class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-['ivypresto-headline'] text-2xl font-bold"
                        :class="
                            (stats.out_of_stock_medicines ?? 0) +
                                (stats.low_stock_medicines ?? 0) >
                            0
                                ? 'text-rose-700'
                                : 'text-[#000000]'
                        "
                    >
                        {{
                            (stats.out_of_stock_medicines ?? 0) +
                            (stats.low_stock_medicines ?? 0)
                        }}
                    </div>
                    <div
                        class="mt-1 text-[11px]"
                        :class="
                            (stats.out_of_stock_medicines ?? 0) +
                                (stats.low_stock_medicines ?? 0) >
                            0
                                ? 'font-medium text-rose-700'
                                : 'text-[#333333]/70'
                        "
                    >
                        {{ stats.out_of_stock_medicines ?? 0 }} kosong ·
                        {{ stats.low_stock_medicines ?? 0 }} menipis
                    </div>
                </motion.div>

                <!-- 5. Tagihan Kasir Belum Lunas -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.17 }"
                    class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider text-[#333333]/70 uppercase"
                            >Tagihan Unpaid</span
                        >
                        <div
                            class="flex size-7 items-center justify-center rounded-full bg-[#beedc0]/40 text-[#065f46]"
                        >
                            <Receipt class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-['ivypresto-headline'] text-2xl font-bold text-[#000000]"
                    >
                        {{ stats.unpaid_billings ?? 0 }}
                    </div>
                    <div class="mt-1 text-[11px] text-[#333333]/70">
                        Menunggu kasir / POS
                    </div>
                </motion.div>

                <!-- 6. Pendapatan Kasir Hari Ini -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.22, delay: 0.2 }"
                    class="rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold tracking-wider text-[#333333]/70 uppercase"
                            >Kasir Lunas</span
                        >
                        <div
                            class="flex size-7 items-center justify-center rounded-full bg-[#beedc0]/40 text-[#065f46]"
                        >
                            <DollarSign class="size-3.5" />
                        </div>
                    </div>
                    <div
                        class="mt-2 truncate font-['ivypresto-headline'] text-lg font-bold text-[#065f46]"
                    >
                        {{ formatRupiah(stats.today_revenue ?? 0) }}
                    </div>
                    <div class="mt-1 text-[11px] text-[#333333]/70">
                        Penerimaan hari ini
                    </div>
                </motion.div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. SEKSI FARMASI & APOTEK: Resep Masuk & Peringatan Stok
                 ═══════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Kolom 1 & 2: Antrean Resep Elektronik Farmasi -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25, delay: 0.22 }"
                    class="space-y-4 rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-5 shadow-sm lg:col-span-2"
                >
                    <div
                        class="flex flex-col gap-2 border-b border-[#333333]/10 pb-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex size-8 items-center justify-center rounded-full bg-[#beedc0] text-[#065f46]"
                            >
                                <Pill class="size-4" />
                            </div>
                            <div>
                                <h3
                                    class="font-['ivypresto-headline'] text-base font-bold text-[#000000]"
                                >
                                    Antrean Resep Farmasi & Apotek
                                </h3>
                                <p class="text-xs text-[#333333]/70">
                                    Resep elektronik yang diterbitkan dokter
                                    untuk disiapkan dan diserahkan ke pasien
                                </p>
                            </div>
                        </div>

                        <Link
                            v-if="isNurseTetap || isAdmin"
                            href="/staff/medicines"
                            class="inline-flex min-h-[36px] items-center gap-1.5 rounded-[40.5px] border border-[#333333]/15 bg-[#edede2]/60 px-3.5 py-1.5 text-xs font-semibold text-[#000000] transition-all hover:bg-[#edede2]"
                        >
                            <span>Buka Inventori</span>
                            <ArrowUpRight class="size-3.5 text-[#065f46]" />
                        </Link>
                    </div>

                    <!-- List Resep Menunggu / Diproses -->
                    <div
                        v-if="
                            pendingPrescriptions &&
                            pendingPrescriptions.length > 0
                        "
                        class="space-y-3"
                    >
                        <div
                            v-for="rx in pendingPrescriptions"
                            :key="rx.prescription_id"
                            class="rounded-[10px] border border-[#333333]/12 bg-[#edede2]/40 p-4 transition-all hover:border-[#333333]/30"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-mono text-xs font-bold text-[#000000]"
                                        >
                                            #{{ rx.prescription_number }}
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold"
                                            :class="
                                                rx.status === 'diproses'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : 'bg-amber-100 text-amber-800'
                                            "
                                        >
                                            {{
                                                rx.status === 'diproses'
                                                    ? 'Sedang Diracik'
                                                    : 'Menunggu Farmasi'
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="text-sm font-bold text-[#000000]"
                                    >
                                        {{
                                            rx.medical_record?.patient?.name ??
                                            'Pasien Rawat Jalan'
                                        }}
                                        <span
                                            class="text-xs font-normal text-[#333333]/70"
                                        >
                                            (Poli:
                                            {{
                                                rx.medical_record?.reservation
                                                    ?.doctor_schedule?.poli
                                                    ?.name_poli ??
                                                rx.medical_record?.reservation
                                                    ?.doctor_schedule?.poli
                                                    ?.name ??
                                                'Umum'
                                            }}
                                            ·
                                            {{
                                                rx.medical_record?.doctor
                                                    ?.name ?? 'Dokter'
                                            }})
                                        </span>
                                    </div>

                                    <!-- Daftar Obat yang Diresepkan -->
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span
                                            v-for="item in rx.items"
                                            :key="item.prescription_item_id"
                                            class="inline-flex items-center gap-1 rounded-[6px] border border-[#333333]/10 bg-[#fffff3] px-2 py-1 text-xs text-[#000000]"
                                        >
                                            <strong class="font-semibold">{{
                                                item.medicine?.name_medicine ??
                                                'Obat'
                                            }}</strong>
                                            <span
                                                class="font-bold text-[#065f46]"
                                                >x{{ item.quantity }}</span
                                            >
                                            <span
                                                class="text-[10px] text-[#333333]/70"
                                                >({{ item.dosage }})</span
                                            >
                                        </span>
                                    </div>

                                    <p
                                        v-if="rx.notes"
                                        class="text-xs text-[#333333]/80 italic"
                                    >
                                        Catatan: "{{ rx.notes }}"
                                    </p>
                                </div>

                                <!-- Tombol Aksi Farmasi (Hanya Staf Tetap) -->
                                <div
                                    v-if="isNurseTetap || isAdmin"
                                    class="flex shrink-0 items-center gap-2 pt-2 sm:pt-0"
                                >
                                    <motion.button
                                        v-if="rx.status === 'menunggu'"
                                        type="button"
                                        :whileHover="{ scale: 1.03 }"
                                        :whileTap="{ scale: 0.97 }"
                                        @click="processPrescription(rx)"
                                        :disabled="
                                            prescriptionLoadingId ===
                                            rx.prescription_id
                                        "
                                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] border border-blue-300 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-800 hover:bg-blue-100 disabled:opacity-50"
                                    >
                                        <Clock class="size-3.5" />
                                        <span>{{
                                            prescriptionLoadingId ===
                                            rx.prescription_id
                                                ? 'Memproses...'
                                                : 'Mulai Racik'
                                        }}</span>
                                    </motion.button>

                                    <motion.button
                                        type="button"
                                        :whileHover="{ scale: 1.03 }"
                                        :whileTap="{ scale: 0.97 }"
                                        @click="completePrescription(rx)"
                                        :disabled="
                                            prescriptionLoadingId ===
                                            rx.prescription_id
                                        "
                                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#333333] disabled:opacity-50"
                                    >
                                        <PackageCheck
                                            class="size-3.5 text-[#beedc0]"
                                        />
                                        <span>{{
                                            prescriptionLoadingId ===
                                            rx.prescription_id
                                                ? 'Menyimpan...'
                                                : 'Selesai & Potong Stok'
                                        }}</span>
                                    </motion.button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resep Kosong -->
                    <div
                        v-else
                        class="rounded-[10px] bg-[#edede2]/30 p-8 text-center text-xs text-[#333333]/60"
                    >
                        <PackageCheck
                            class="mx-auto size-7 text-[#065f46]/60"
                        />
                        <div class="mt-2 font-medium">
                            Semua resep dokter hari ini telah selesai diproses
                            farmasi.
                        </div>
                    </div>
                </motion.div>

                <!-- Kolom 3: Peringatan Stok Obat Kritis -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.25, delay: 0.25 }"
                    class="space-y-4 rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#333333]/10 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <ShieldAlert class="size-4 text-rose-600" />
                            <h3
                                class="font-['ivypresto-headline'] text-base font-bold text-[#000000]"
                            >
                                Peringatan Stok Obat
                            </h3>
                        </div>
                        <span class="text-[11px] font-semibold text-rose-700">
                            Stok ≤ 10
                        </span>
                    </div>

                    <div
                        v-if="criticalMedicines && criticalMedicines.length > 0"
                        class="space-y-2.5"
                    >
                        <div
                            v-for="med in criticalMedicines"
                            :key="med.medicine_id"
                            class="flex items-center justify-between rounded-[8px] border p-2.5 text-xs transition-colors"
                            :class="
                                med.stock <= 0
                                    ? 'border-rose-300 bg-rose-50/60'
                                    : 'border-amber-200 bg-amber-50/50'
                            "
                        >
                            <div class="space-y-0.5">
                                <div class="font-bold text-[#000000]">
                                    {{ med.name_medicine }}
                                </div>
                                <div
                                    class="font-mono text-[10px] text-[#333333]/70"
                                >
                                    {{ med.code_medicine }} · {{ med.type }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex min-w-[40px] items-center justify-center rounded-full px-2 py-0.5 font-bold"
                                    :class="
                                        med.stock <= 0
                                            ? 'bg-rose-200 text-rose-800'
                                            : 'bg-amber-200 text-amber-900'
                                    "
                                >
                                    {{ med.stock }} {{ med.unit }}
                                </span>
                            </div>
                        </div>

                        <Link
                            v-if="isNurseTetap || isAdmin"
                            href="/staff/medicines"
                            class="mt-2 inline-flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-2 text-xs font-bold text-[#ffffff] transition-all hover:bg-[#333333]"
                        >
                            <PackagePlus class="size-3.5 text-[#beedc0]" />
                            <span>Buka Restok Inventori</span>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="rounded-[10px] bg-[#edede2]/30 p-6 text-center text-xs text-[#333333]/60"
                    >
                        <CheckCircle2 class="mx-auto size-6 text-[#065f46]" />
                        <div class="mt-2 font-medium">
                            Semua stok obat farmasi dalam jumlah aman.
                        </div>
                    </div>
                </motion.div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 4. MEJA DEPAN (FRONT-OFFICE): Verifikasi & Check-in Pasien
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: 15 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.28, delay: 0.28 }"
                class="space-y-4 overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-5 shadow-sm sm:p-6"
            >
                <div
                    class="flex flex-col gap-4 border-b border-[#333333]/10 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="font-['ivypresto-headline'] text-xl font-bold tracking-tight text-[#000000]"
                        >
                            Meja Depan & Verifikasi Kedatangan Pasien
                        </h2>
                        <p class="text-xs text-[#333333]/70">
                            Konfirmasi kedatangan pasien yang telah mendaftar
                            online / kiosk agar dokter dapat memanggil pasien di
                            ruang periksa
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Toggle Khusus Dokter -->
                        <button
                            v-if="isDoctor && doctorActiveClinic"
                            type="button"
                            @click="
                                onlyMyClinicPatients = !onlyMyClinicPatients
                            "
                            class="inline-flex min-h-[40px] items-center gap-1.5 rounded-[40.5px] border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="
                                onlyMyClinicPatients
                                    ? 'border-[#065f46] bg-[#beedc0] text-[#065f46]'
                                    : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            <Stethoscope class="size-3.5" />
                            <span>{{
                                onlyMyClinicPatients
                                    ? 'Poli Saya Saja'
                                    : 'Semua Pasien'
                            }}</span>
                        </button>
                    </div>
                </div>

                <!-- Search & Filters -->
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#333333]/50"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari berdasarkan nama pasien, NIK, nomor antrean, atau dokter..."
                            class="min-h-[44px] w-full rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 pr-4 pl-10 text-xs text-[#000000] placeholder:text-[#333333]/40 focus:border-[#000000] focus:bg-[#ffffff] focus:ring-1 focus:ring-[#000000] focus:outline-none sm:text-sm"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute top-1/2 right-3.5 -translate-y-1/2 text-[#333333]/40 hover:text-[#000000]"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Filter Options -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Dropdown Poli -->
                        <div
                            v-if="availablePolis.length > 1"
                            class="relative min-w-[140px]"
                        >
                            <select
                                v-model="selectedPoliFilter"
                                class="min-h-[44px] w-full appearance-none rounded-[40.5px] border border-[#333333]/20 bg-[#edede2]/60 px-4 py-2 text-xs font-medium text-[#000000] focus:border-[#000000] focus:bg-[#ffffff] focus:ring-1 focus:ring-[#000000] focus:outline-none"
                            >
                                <option value="all">Semua Poliklinik</option>
                                <option
                                    v-for="poli in availablePolis"
                                    :key="poli"
                                    :value="poli"
                                >
                                    {{ poli }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter Pills -->
                        <div
                            class="flex items-center rounded-[40.5px] border border-[#333333]/15 bg-[#edede2]/70 p-1 text-xs"
                        >
                            <button
                                type="button"
                                @click="selectedStatusFilter = 'all'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="
                                    selectedStatusFilter === 'all'
                                        ? 'bg-[#000000] text-[#ffffff] shadow-sm'
                                        : 'text-[#333333] hover:text-[#000000]'
                                "
                            >
                                Semua
                            </button>
                            <button
                                type="button"
                                @click="selectedStatusFilter = 'pending'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="
                                    selectedStatusFilter === 'pending'
                                        ? 'bg-amber-600 text-[#ffffff] shadow-sm'
                                        : 'text-[#333333] hover:text-amber-700'
                                "
                            >
                                Perlu Check-in
                            </button>
                            <button
                                type="button"
                                @click="selectedStatusFilter = 'confirmed'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="
                                    selectedStatusFilter === 'confirmed'
                                        ? 'bg-blue-600 text-[#ffffff] shadow-sm'
                                        : 'text-[#333333] hover:text-blue-700'
                                "
                            >
                                Hadir
                            </button>
                            <button
                                type="button"
                                @click="selectedStatusFilter = 'in_progress'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="
                                    selectedStatusFilter === 'in_progress'
                                        ? 'bg-[#065f46] text-[#ffffff] shadow-sm'
                                        : 'text-[#333333] hover:text-[#065f46]'
                                "
                            >
                                Diperiksa
                            </button>
                            <button
                                type="button"
                                @click="selectedStatusFilter = 'completed'"
                                class="rounded-[40.5px] px-3 py-1.5 font-medium transition-all"
                                :class="
                                    selectedStatusFilter === 'completed'
                                        ? 'bg-[#333333] text-[#ffffff] shadow-sm'
                                        : 'text-[#333333] hover:text-[#000000]'
                                "
                            >
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabel Antrean Pasien Hari Ini -->
                <div
                    class="overflow-x-auto rounded-[8px] border border-[#333333]/10"
                >
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-[#333333]/10 bg-[#edede2]/60 text-[11px] font-bold tracking-wider text-[#333333]/80 uppercase"
                            >
                                <th class="px-4 py-3.5 text-center">
                                    No. Antrean
                                </th>
                                <th class="px-4 py-3.5">Nama Pasien & NIK</th>
                                <th class="px-4 py-3.5">
                                    Poli & Dokter Tujuan
                                </th>
                                <th class="px-4 py-3.5">Keluhan Pasien</th>
                                <th class="px-4 py-3.5 text-center">
                                    Status Kehadiran
                                </th>
                                <th class="px-4 py-3.5 text-center">
                                    Aksi Operasional
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#333333]/8">
                            <tr
                                v-for="apt in filteredAppointments"
                                :key="apt.appointment_id"
                                class="transition-colors hover:bg-[#edede2]/40"
                            >
                                <!-- No Antrean -->
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-[50px] items-center justify-center rounded-full bg-[#000000] px-3 py-1 font-mono text-xs font-bold text-[#ffffff] shadow-sm"
                                    >
                                        {{ apt.queue_number }}
                                    </span>
                                </td>

                                <!-- Pasien -->
                                <td class="px-4 py-4">
                                    <div class="font-bold text-[#000000]">
                                        {{ apt.patient?.name ?? 'Nama Pasien' }}
                                    </div>
                                    <div
                                        class="font-mono text-xs text-[#333333]/70"
                                    >
                                        NIK:
                                        {{ apt.patient?.resident_n ?? '-' }} ·
                                        {{ apt.patient?.gender ?? '-' }}
                                    </div>
                                </td>

                                <!-- Poli & Dokter -->
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-[#000000]">
                                        {{
                                            apt.doctor_schedule?.poli
                                                ?.name_poli ??
                                            apt.doctor_schedule?.poli?.name ??
                                            'Poliklinik'
                                        }}
                                    </div>
                                    <div class="text-xs text-[#333333]/70">
                                        {{
                                            apt.doctor_schedule?.doctor?.name ??
                                            'Dokter'
                                        }}
                                        <span class="text-[10px] text-[#065f46]"
                                            >({{
                                                apt.doctor_schedule?.room
                                                    ?.name_room ?? 'R. Periksa'
                                            }})</span
                                        >
                                    </div>
                                </td>

                                <!-- Keluhan -->
                                <td
                                    class="max-w-[200px] truncate px-4 py-4 text-xs text-[#333333]/80"
                                >
                                    {{
                                        apt.complaint ||
                                        'Pemeriksaan rawat jalan rutin'
                                    }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="
                                            getStatusBadge(apt.status).classes
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                getStatusBadge(apt.status).dot
                                            "
                                        />
                                        <span>{{
                                            getStatusBadge(apt.status).label
                                        }}</span>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-4 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <!-- Tombol Konfirmasi Check-in (Untuk Status Pending) -->
                                        <motion.button
                                            v-if="apt.status === 'pending'"
                                            type="button"
                                            :whileHover="{ scale: 1.05 }"
                                            :whileTap="{ scale: 0.95 }"
                                            @click="confirmArrival(apt)"
                                            :disabled="
                                                actionLoadingId ===
                                                apt.appointment_id
                                            "
                                            class="inline-flex min-h-[38px] items-center gap-1.5 rounded-[40.5px] bg-[#000000] px-4 py-1.5 text-xs font-bold text-[#ffffff] shadow-sm hover:bg-[#333333] disabled:opacity-50"
                                        >
                                            <UserCheck
                                                class="size-3.5 text-[#beedc0]"
                                            />
                                            <span>{{
                                                actionLoadingId ===
                                                apt.appointment_id
                                                    ? 'Memproses...'
                                                    : 'Konfirmasi Check-in'
                                            }}</span>
                                        </motion.button>

                                        <!-- Status Sudah Hadir -->
                                        <span
                                            v-else-if="
                                                apt.status === 'confirmed'
                                            "
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700"
                                        >
                                            <CheckCircle2 class="size-4" />
                                            <span>Siap Diperiksa</span>
                                        </span>

                                        <!-- Selesai Diperiksa & Billing -->
                                        <template
                                            v-else-if="
                                                apt.status === 'completed'
                                            "
                                        >
                                            <Link
                                                v-if="apt.billing"
                                                :href="`/staff/billing/${apt.billing.billing_id}`"
                                                class="inline-flex min-h-[36px] items-center gap-1 rounded-[40.5px] border border-[#065f46]/30 bg-[#beedc0]/40 px-3 py-1 text-xs font-bold text-[#065f46] hover:bg-[#beedc0]"
                                            >
                                                <Receipt class="size-3.5" />
                                                <span>{{
                                                    apt.billing.status ===
                                                    'paid'
                                                        ? 'Invoice Lunas'
                                                        : 'Bayar Kasir'
                                                }}</span>
                                            </Link>
                                            <span
                                                v-else
                                                class="text-xs text-[#333333]/60"
                                                >Selesai</span
                                            >
                                        </template>

                                        <span
                                            v-else
                                            class="text-xs text-[#333333]/60"
                                            >-</span
                                        >
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty Filter State -->
                            <tr v-if="filteredAppointments.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-sm text-[#333333]/60"
                                >
                                    <UserCheck
                                        class="mx-auto size-8 text-[#333333]/30"
                                    />
                                    <div class="mt-2 font-medium">
                                        Tidak ada pasien yang sesuai dengan
                                        filter pencarian.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 5. Matriks Poliklinik & Beban Ruangan Dokter
                 ═══════════════════════════════════════════════════════════════ -->
            <div
                v-if="clinicMatrix && clinicMatrix.length > 0"
                class="space-y-3"
            >
                <div class="flex items-center justify-between">
                    <h3
                        class="font-['ivypresto-headline'] text-lg font-bold text-[#000000]"
                    >
                        Status Matriks Poliklinik Hari Ini
                    </h3>
                    <span class="text-xs text-[#333333]/70">
                        {{ clinicMatrix.length }} Ruang Praktik Aktif
                    </span>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <motion.div
                        v-for="clinic in clinicMatrix"
                        :key="clinic.schedule_id"
                        :initial="{ opacity: 0, scale: 0.98 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.2 }"
                        class="space-y-3 rounded-[10px] border border-[#333333]/12 bg-[#fffff3] p-4 shadow-sm"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <span
                                    class="inline-flex items-center rounded-full bg-[#beedc0]/50 px-2.5 py-0.5 text-[11px] font-bold text-[#065f46]"
                                >
                                    {{ clinic.poli_name }}
                                </span>
                                <h4
                                    class="mt-1.5 text-sm font-bold text-[#000000]"
                                >
                                    {{ clinic.doctor_name }}
                                </h4>
                                <p class="text-xs text-[#333333]/70">
                                    {{ clinic.specialization }} ·
                                    {{ clinic.room_name }}
                                </p>
                            </div>
                            <span
                                class="font-mono text-xs font-semibold text-[#333333]/70"
                            >
                                {{ clinic.start_time }} - {{ clinic.end_time }}
                            </span>
                        </div>

                        <div
                            class="grid grid-cols-3 gap-2 border-t border-[#333333]/10 pt-3 text-center text-xs"
                        >
                            <div class="rounded-[6px] bg-[#edede2]/60 p-2">
                                <div class="text-[10px] text-[#333333]/70">
                                    Dipanggil
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-sm font-bold text-[#065f46]"
                                >
                                    {{ clinic.current_calling || '-' }}
                                </div>
                            </div>
                            <div class="rounded-[6px] bg-[#edede2]/60 p-2">
                                <div class="text-[10px] text-[#333333]/70">
                                    Menunggu
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-sm font-bold text-amber-700"
                                >
                                    {{ clinic.waiting_count }}
                                </div>
                            </div>
                            <div class="rounded-[6px] bg-[#edede2]/60 p-2">
                                <div class="text-[10px] text-[#333333]/70">
                                    Selesai
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-sm font-bold text-[#000000]"
                                >
                                    {{ clinic.completed_count }}
                                </div>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </div>
</template>
