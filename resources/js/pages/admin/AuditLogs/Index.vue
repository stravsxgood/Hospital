<script setup lang="ts">
/**
 * @file Index.vue (Super Admin Global Regulatory Access Audit Logs)
 * @description Pemantauan Rekam Jejak Akses EMR Pasien Global (Kepatuhan UU PDP No. 27/2022 & Permenkes No. 24/2022).
 *              100% Responsif untuk Mobile (<640px), Tablet/iPad (640-1024px), dan Desktop (>1024px).
 *
 * Sesuai panduan DESIGN.md (Evergreen Theme) & GEMINI.md:
 *  - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 *  - Typography: IvyPresto Headline serif + Rubik sans.
 *  - Motion-V untuk micro-interactions & feedback interaktif.
 *  - Target sentuh ramah minimal 44px (min-h-[44px]).
 */
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    Building2,
    Calendar,
    Clock,
    Download,
    Eye,
    FileCheck,
    FileText,
    Globe,
    Layers,
    Lock,
    Printer,
    RefreshCw,
    Search,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    User,
    Users,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { ref } from 'vue';
import ResponsiveTable from '@/components/admin/ResponsiveTable.vue';
import Pagination from '@/components/Pagination.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface AuditLogItem {
    audit_log_id: number;
    user_id: number;
    medical_record_id: number | null;
    action: 'view' | 'create' | 'update' | 'export_pdf' | 'print';
    ip_address: string;
    user_agent: string;
    metadata: Record<string, any> | null;
    created_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
    medical_record?: {
        medical_record_id: number;
        assessment: string;
        patient?: {
            patient_id: number;
            name: string;
            resident_n: string;
        };
        doctor?: {
            doctor_id: number;
            name: string;
        };
    };
}

const props = defineProps<{
    logs: {
        data: AuditLogItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        action?: string;
        search?: string;
    };
    stats: {
        total_access: number;
        views_today: number;
        creates_today: number;
        exports_today: number;
    };
}>();

const searchInput = ref(props.filters.search || '');
const selectedAction = ref(props.filters.action || '');

const applyFilters = () => {
    router.get(
        '/admin/audit-logs',
        {
            search: searchInput.value || undefined,
            action: selectedAction.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const selectedLogForInspect = ref<AuditLogItem | null>(null);
const inspectLog = (log: AuditLogItem) => {
    selectedLogForInspect.value = log;
};

const formatDate = (iso: string) => {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};
</script>

<template>
    <AdminLayout
        title="Audit Jejak Akses EMR Global - Super Admin"
        :breadcrumbs="[
            { title: 'Audit Akses Global', href: '/admin/audit-logs' },
        ]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.header
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:flex-row sm:items-center sm:justify-between sm:p-7"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3.5 py-1 text-xs font-bold text-[#000000]"
                        >
                            <ShieldCheck class="size-3.5 text-[#000000]" />
                            <span>Kepatuhan UU PDP No. 27/2022</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Audit Jejak Akses EMR Global
                    </h1>
                    <p class="font-['Rubik'] text-xs text-[#333333] sm:text-sm">
                        Log audit otomatis tak-terubah (immutable) merekam
                        setiap inspeksi, pembuatan, modifikasi, dan ekspor data
                        klinis.
                    </p>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Metric Quick Cards
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="audit-stats-overview-heading">
                <h2 id="audit-stats-overview-heading" class="sr-only">
                    Statistik Ringkas Log Audit
                </h2>
                <div
                    class="grid grid-cols-2 gap-3 font-['Rubik'] sm:grid-cols-4 sm:gap-4"
                >
                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Total Event Akses
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.total_access }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Inspeksi Hari Ini
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.views_today }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            EMR Baru Hari Ini
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.creates_today }}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none"
                    >
                        <div
                            class="truncate text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Ekspor PDF Hari Ini
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-bold text-[#000000]"
                        >
                            {{ stats.exports_today }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Filter Bar & Responsive Table
                 ═══════════════════════════════════════════════════════════════ -->
            <ResponsiveTable
                :is-empty="logs.data.length === 0"
                empty-message="Tidak ada catatan jejak audit yang sesuai filter."
            >
                <!-- Filters Slot -->
                <template #filters>
                    <div
                        class="flex flex-col items-stretch justify-between gap-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-3 font-['Rubik'] shadow-none sm:flex-row sm:items-center sm:p-4"
                    >
                        <div class="relative flex-1">
                            <label for="audit-search-input" class="sr-only"
                                >Cari Log Berdasarkan Operator, Pasien, NIK,
                                atau IP</label
                            >
                            <Search
                                class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#000000]/60"
                            />
                            <input
                                id="audit-search-input"
                                v-model="searchInput"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Cari nama operator, pasien, NIK, atau IP address..."
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] pr-4 pl-10 text-xs text-[#000000] placeholder-[#000000]/50 focus:border-[#000000] focus:outline-none sm:text-sm"
                            />
                        </div>

                        <div class="flex items-center gap-2.5">
                            <label for="audit-action-filter" class="sr-only"
                                >Filter Berdasarkan Aktivitas</label
                            >
                            <select
                                id="audit-action-filter"
                                v-model="selectedAction"
                                @change="applyFilters"
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-3.5 py-2 text-xs font-medium text-[#000000] focus:border-[#000000] focus:outline-none sm:w-auto"
                            >
                                <option value="">Semua Aktivitas</option>
                                <option value="view">
                                    Lihat Rekam Medis (View)
                                </option>
                                <option value="create">
                                    Buat SOAP / EMR (Create)
                                </option>
                                <option value="update">
                                    Ubah Data (Update)
                                </option>
                                <option value="export_pdf">
                                    Ekspor Dokumen PDF
                                </option>
                                <option value="print">
                                    Cetak Bukti (Print)
                                </option>
                            </select>

                            <button
                                type="button"
                                @click="applyFilters"
                                aria-label="Terapkan Filter Audit"
                                class="min-h-[44px] shrink-0 rounded-[40.5px] bg-[#000000] px-5 text-xs font-medium text-[#ffffff] hover:bg-[#1a1a1a]"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Table Header Slot -->
                <template #header>
                    <tr>
                        <th class="px-4 py-3.5 sm:px-6">Waktu Kejadian</th>
                        <th class="px-4 py-3.5">Operator Akses</th>
                        <th class="px-4 py-3.5 text-center">Aksi / Operasi</th>
                        <th class="px-4 py-3.5">Target Pasien / EMR</th>
                        <th class="px-4 py-3.5">IP & Jaringan</th>
                        <th class="px-4 py-3.5 text-right sm:px-6">Inspeksi</th>
                    </tr>
                </template>

                <!-- Table Body Rows -->
                <tr
                    v-for="log in logs.data"
                    :key="log.audit_log_id"
                    class="transition-colors hover:bg-[#edede2]/30"
                >
                    <!-- Waktu -->
                    <td
                        class="px-4 py-3.5 font-mono text-xs text-[#000000] sm:px-6"
                    >
                        {{ formatDate(log.created_at) }}
                    </td>

                    <!-- Operator -->
                    <td class="px-4 py-3.5 text-xs">
                        <div class="font-bold text-[#000000]">
                            {{ log.user?.name || 'Sistem' }}
                        </div>
                        <div class="text-[11px] text-[#333333]">
                            {{ log.user?.email || '-' }} ({{ log.user?.role }})
                        </div>
                    </td>

                    <!-- Aksi -->
                    <td class="px-4 py-3.5 text-center">
                        <span
                            v-if="log.action === 'view'"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-100/70 px-3 py-0.5 text-xs font-bold text-blue-900"
                        >
                            <Eye class="size-3" />
                            <span>Lihat EMR</span>
                        </span>
                        <span
                            v-else-if="log.action === 'create'"
                            class="inline-flex items-center gap-1 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3 py-0.5 text-xs font-bold text-[#000000]"
                        >
                            <FileCheck class="size-3" />
                            <span>Buat Rekam</span>
                        </span>
                        <span
                            v-else-if="log.action === 'export_pdf'"
                            class="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-purple-100/70 px-3 py-0.5 text-xs font-bold text-purple-900"
                        >
                            <Download class="size-3" />
                            <span>Ekspor PDF</span>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full border border-[#000000]/10 bg-[#edede2] px-3 py-0.5 text-xs font-medium text-[#333333]"
                        >
                            <span>{{ log.action }}</span>
                        </span>
                    </td>

                    <!-- Target Pasien -->
                    <td class="px-4 py-3.5 text-xs">
                        <div
                            v-if="log.medical_record?.patient"
                            class="space-y-0.5"
                        >
                            <div class="font-bold text-[#000000]">
                                {{ log.medical_record.patient.name }}
                            </div>
                            <div class="font-mono text-[11px] text-[#333333]">
                                NIK: {{ log.medical_record.patient.resident_n }}
                            </div>
                        </div>
                        <span v-else class="text-[#333333]"
                            >EMR #{{ log.medical_record_id || '-' }}</span
                        >
                    </td>

                    <!-- IP Address -->
                    <td class="px-4 py-3.5 font-mono text-xs text-[#000000]">
                        {{ log.ip_address }}
                    </td>

                    <!-- Inspeksi Modal -->
                    <td class="px-4 py-3.5 text-right sm:px-6">
                        <button
                            type="button"
                            @click="inspectLog(log)"
                            :aria-label="`Inspeksi detail log audit #${log.audit_log_id}`"
                            class="inline-flex min-h-[38px] cursor-pointer items-center gap-1 rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] px-3.5 py-1.5 text-xs font-medium text-[#000000] hover:bg-[#edede2]"
                        >
                            <Eye class="size-3.5 text-[#000000]" />
                            <span>Detail</span>
                        </button>
                    </td>
                </tr>

                <!-- Pagination Slot -->
                <template #pagination v-if="logs.total > 0">
                    <Pagination :pagination="logs" item-name="catatan audit" />
                </template>
            </ResponsiveTable>
        </div>

        <!-- Modal Inspeksi Metadata Audit (Mobile Bottom Sheet) -->
        <div
            v-if="selectedLogForInspect"
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#000000]/60 p-0 backdrop-blur-xs sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="audit-inspect-title"
        >
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex max-h-[88vh] w-full max-w-full flex-col space-y-4 overflow-hidden rounded-t-3xl border border-[#000000]/15 bg-[#fffff3] p-5 shadow-2xl sm:max-w-lg sm:rounded-3xl sm:p-7"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3"
                >
                    <h2
                        id="audit-inspect-title"
                        class="font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                    >
                        Metadata Log Audit #{{
                            selectedLogForInspect.audit_log_id
                        }}
                    </h2>
                    <button
                        type="button"
                        @click="selectedLogForInspect = null"
                        aria-label="Tutup Dialog Inspeksi Log"
                        class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full text-[#000000]/70 hover:bg-[#edede2] hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="flex-1 space-y-3 overflow-y-auto pr-1 font-['Rubik'] text-xs"
                >
                    <div
                        class="space-y-1.5 rounded-2xl border border-[#000000]/10 bg-[#edede2]/40 p-3.5"
                    >
                        <div>
                            <strong>Operator:</strong>
                            {{ selectedLogForInspect.user?.name }} ({{
                                selectedLogForInspect.user?.email
                            }})
                        </div>
                        <div>
                            <strong>IP Address:</strong>
                            {{ selectedLogForInspect.ip_address }}
                        </div>
                        <div class="break-all">
                            <strong>User Agent:</strong>
                            {{ selectedLogForInspect.user_agent }}
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 font-medium text-[#000000]">
                            Payload JSON Metadata:
                        </div>
                        <pre
                            class="max-h-60 overflow-x-auto rounded-2xl bg-[#000000] p-3 font-mono text-[11px] text-[#beedc0]"
                            >{{
                                JSON.stringify(
                                    selectedLogForInspect.metadata || {},
                                    null,
                                    2,
                                )
                            }}</pre>
                    </div>
                </div>

                <div
                    class="flex shrink-0 justify-end border-t border-[#000000]/10 pt-2"
                >
                    <button
                        type="button"
                        @click="selectedLogForInspect = null"
                        class="min-h-[44px] rounded-[40.5px] bg-[#000000] px-6 py-2 text-xs font-medium text-[#ffffff] hover:bg-[#1a1a1a]"
                    >
                        Tutup
                    </button>
                </div>
            </motion.div>
        </div>
    </AdminLayout>
</template>
