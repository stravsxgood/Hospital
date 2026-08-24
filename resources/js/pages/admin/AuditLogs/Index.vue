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
import { Head, Link, router } from '@inertiajs/vue3'
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
} from '@lucide/vue'
import { motion } from 'motion-v'
import { ref } from 'vue'
import ResponsiveTable from '@/components/admin/ResponsiveTable.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface AuditLogItem {
    audit_log_id: number
    user_id: number
    medical_record_id: number | null
    action: 'view' | 'create' | 'update' | 'export_pdf' | 'print'
    ip_address: string
    user_agent: string
    metadata: Record<string, any> | null
    created_at: string
    user?: {
        id: number
        name: string
        email: string
        role: string
    }
    medical_record?: {
        medical_record_id: number
        assessment: string
        patient?: {
            patient_id: number
            name: string
            resident_n: string
        }
        doctor?: {
            doctor_id: number
            name: string
        }
    }
}

const props = defineProps<{
    logs: {
        data: AuditLogItem[]
        current_page: number
        last_page: number
        total: number
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
    filters: {
        action?: string
        search?: string
    }
    stats: {
        total_access: number
        views_today: number
        creates_today: number
        exports_today: number
    }
}>()

const searchInput = ref(props.filters.search || '')
const selectedAction = ref(props.filters.action || '')

const applyFilters = () => {
    router.get(
        '/admin/audit-logs',
        {
            search: searchInput.value || undefined,
            action: selectedAction.value || undefined,
        },
        { preserveState: true, replace: true }
    )
}

const selectedLogForInspect = ref<AuditLogItem | null>(null)
const inspectLog = (log: AuditLogItem) => {
    selectedLogForInspect.value = log
}

const formatDate = (iso: string) => {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
}
</script>

<template>
    <AdminLayout
        title="Audit Jejak Akses EMR Global - Super Admin"
        :breadcrumbs="[{ title: 'Audit Akses Global', href: '/admin/audit-logs' }]"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.header
                :initial="{ opacity: 0, y: -12 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 sm:p-7 shadow-xs sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#065f46] px-3 py-1 text-xs font-bold text-[#ffffff]">
                            <ShieldCheck class="size-3.5" />
                            <span>Kepatuhan UU PDP No. 27/2022</span>
                        </span>
                    </div>
                    <h1 class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl">
                        Audit Jejak Akses EMR Global
                    </h1>
                    <p class="text-xs text-[#333333] sm:text-sm">
                        Log audit otomatis tak-terubah (immutable) merekam setiap inspeksi, pembuatan, modifikasi, dan ekspor data klinis.
                    </p>
                </div>
            </motion.header>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Metric Quick Cards
                 ═══════════════════════════════════════════════════════════════ -->
            <section aria-labelledby="audit-stats-overview-heading">
                <h2 id="audit-stats-overview-heading" class="sr-only">Statistik Ringkas Log Audit</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-xs">
                        <div class="text-xs font-semibold text-[#333333] uppercase truncate">Total Event Akses</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-[#000000]">{{ stats.total_access }}</div>
                    </div>

                    <div class="rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-xs">
                        <div class="text-xs font-semibold text-[#333333] uppercase truncate">Inspeksi Hari Ini</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-[#065f46]">{{ stats.views_today }}</div>
                    </div>

                    <div class="rounded-3xl border border-blue-200 bg-blue-50/60 p-4 shadow-xs">
                        <div class="text-xs font-semibold text-blue-900 uppercase truncate">EMR Baru Hari Ini</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-blue-900">{{ stats.creates_today }}</div>
                    </div>

                    <div class="rounded-3xl border border-purple-200 bg-purple-50/60 p-4 shadow-xs">
                        <div class="text-xs font-semibold text-purple-900 uppercase truncate">Ekspor PDF Hari Ini</div>
                        <div class="mt-1 text-2xl font-bold font-mono text-purple-900">{{ stats.exports_today }}</div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Filter Bar & Responsive Table
                 ═══════════════════════════════════════════════════════════════ -->
            <ResponsiveTable :is-empty="logs.data.length === 0" empty-message="Tidak ada catatan jejak audit yang sesuai filter.">
                <!-- Filters Slot -->
                <template #filters>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 rounded-2xl border border-[#000000]/10 bg-[#fffff3] p-3 sm:p-4 shadow-xs">
                        <div class="relative flex-1">
                            <label for="audit-search-input" class="sr-only">Cari Log Berdasarkan Operator, Pasien, NIK, atau IP</label>
                            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-[#000000]/60" />
                            <input
                                id="audit-search-input"
                                v-model="searchInput"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Cari nama operator, pasien, NIK, atau IP address..."
                                class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#edede2]/40 pl-10 pr-4 text-xs sm:text-sm text-[#000000] focus:border-[#065f46] focus:outline-none"
                            />
                        </div>

                        <div class="flex items-center gap-2.5">
                            <label for="audit-action-filter" class="sr-only">Filter Berdasarkan Aktivitas</label>
                            <select
                                id="audit-action-filter"
                                v-model="selectedAction"
                                @change="applyFilters"
                                class="min-h-[44px] w-full sm:w-auto rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3.5 py-2 text-xs font-semibold text-[#000000] focus:border-[#065f46] focus:outline-none"
                            >
                                <option value="">Semua Aktivitas</option>
                                <option value="view">Lihat Rekam Medis (View)</option>
                                <option value="create">Buat SOAP / EMR (Create)</option>
                                <option value="update">Ubah Data (Update)</option>
                                <option value="export_pdf">Ekspor Dokumen PDF</option>
                                <option value="print">Cetak Bukti (Print)</option>
                            </select>

                            <button
                                type="button"
                                @click="applyFilters"
                                aria-label="Terapkan Filter Audit"
                                class="min-h-[44px] px-4 rounded-xl bg-[#065f46] text-xs font-bold text-[#ffffff] hover:bg-[#054d38] shrink-0"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Table Header Slot -->
                <template #header>
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Waktu Kejadian</th>
                        <th class="py-3.5 px-4">Operator Akses</th>
                        <th class="py-3.5 px-4 text-center">Aksi / Operasi</th>
                        <th class="py-3.5 px-4">Target Pasien / EMR</th>
                        <th class="py-3.5 px-4">IP & Jaringan</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Inspeksi</th>
                    </tr>
                </template>

                <!-- Table Body Rows -->
                <tr v-for="log in logs.data" :key="log.audit_log_id" class="hover:bg-[#edede2]/30 transition-colors">
                    <!-- Waktu -->
                    <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-[#000000]/80">
                        {{ formatDate(log.created_at) }}
                    </td>

                    <!-- Operator -->
                    <td class="py-3.5 px-4 text-xs">
                        <div class="font-bold text-[#000000]">{{ log.user?.name || 'Sistem' }}</div>
                        <div class="text-[11px] text-[#000000]/70">{{ log.user?.email || '-' }} ({{ log.user?.role }})</div>
                    </td>

                    <!-- Aksi -->
                    <td class="py-3.5 px-4 text-center">
                        <span
                            v-if="log.action === 'view'"
                            class="inline-flex items-center gap-1 rounded-full bg-blue-100 border border-blue-300 px-2.5 py-0.5 text-xs font-bold text-blue-900"
                        >
                            <Eye class="size-3" />
                            <span>Lihat EMR</span>
                        </span>
                        <span
                            v-else-if="log.action === 'create'"
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 border border-emerald-300 px-2.5 py-0.5 text-xs font-bold text-emerald-900"
                        >
                            <FileCheck class="size-3" />
                            <span>Buat Rekam</span>
                        </span>
                        <span
                            v-else-if="log.action === 'export_pdf'"
                            class="inline-flex items-center gap-1 rounded-full bg-purple-100 border border-purple-300 px-2.5 py-0.5 text-xs font-bold text-purple-900"
                        >
                            <Download class="size-3" />
                            <span>Ekspor PDF</span>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-neutral-100 border border-neutral-300 px-2.5 py-0.5 text-xs font-bold text-neutral-800"
                        >
                            <span>{{ log.action }}</span>
                        </span>
                    </td>

                    <!-- Target Pasien -->
                    <td class="py-3.5 px-4 text-xs">
                        <div v-if="log.medical_record?.patient" class="space-y-0.5">
                            <div class="font-bold text-[#065f46]">{{ log.medical_record.patient.name }}</div>
                            <div class="text-[11px] text-[#000000]/70 font-mono">NIK: {{ log.medical_record.patient.resident_n }}</div>
                        </div>
                        <span v-else class="text-[#000000]/60">EMR #{{ log.medical_record_id || '-' }}</span>
                    </td>

                    <!-- IP Address -->
                    <td class="py-3.5 px-4 font-mono text-xs text-[#000000]/80">
                        {{ log.ip_address }}
                    </td>

                    <!-- Inspeksi Modal -->
                    <td class="py-3.5 px-4 sm:px-6 text-right">
                        <button
                            type="button"
                            @click="inspectLog(log)"
                            :aria-label="`Inspeksi detail log audit #${log.audit_log_id}`"
                            class="min-h-[38px] inline-flex items-center gap-1 rounded-xl border border-[#000000]/15 bg-[#fffff3] px-3 py-1.5 text-xs font-semibold text-[#000000] hover:bg-[#edede2] cursor-pointer"
                        >
                            <Eye class="size-3.5 text-[#065f46]" />
                            <span>Detail</span>
                        </button>
                    </td>
                </tr>

                <!-- Pagination Slot -->
                <template #pagination v-if="logs.total > 0">
                    <nav aria-label="Navigasi Halaman Log Audit" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#000000]/70">
                        <div>
                            Menampilkan halaman <strong class="text-[#000000]">{{ logs.current_page }}</strong> dari <strong class="text-[#000000]">{{ logs.last_page }}</strong> (Total {{ logs.total }} catatan audit)
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="(link, idx) in logs.links" :key="idx">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :aria-label="`Buka halaman ${link.label}`"
                                    class="min-h-[36px] min-w-[36px] flex items-center justify-center rounded-lg px-3 py-1 font-semibold transition-colors"
                                    :class="link.active ? 'bg-[#065f46] text-[#ffffff]' : 'bg-[#edede2]/60 hover:bg-[#edede2] text-[#000000]'"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="min-h-[36px] min-w-[36px] flex items-center justify-center rounded-lg px-3 py-1 text-[#000000]/40"
                                />
                            </template>
                        </div>
                    </nav>
                </template>
            </ResponsiveTable>
        </div>

        <!-- Modal Inspeksi Metadata Audit (Mobile Bottom Sheet) -->
        <div v-if="selectedLogForInspect" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-[#000000]/60 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="audit-inspect-title">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="w-full max-w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-5 sm:p-7 shadow-2xl space-y-4 max-h-[88vh] flex flex-col overflow-hidden"
            >
                <div class="flex items-center justify-between border-b border-[#000000]/10 pb-3 shrink-0">
                    <h2 id="audit-inspect-title" class="font-serif text-base font-bold text-[#000000]">
                        Metadata Log Audit #{{ selectedLogForInspect.audit_log_id }}
                    </h2>
                    <button
                        type="button"
                        @click="selectedLogForInspect = null"
                        aria-label="Tutup Dialog Inspeksi Log"
                        class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl text-[#000000]/70 hover:text-[#000000]"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div class="space-y-3 text-xs overflow-y-auto pr-1 flex-1">
                    <div class="p-3.5 rounded-2xl bg-[#edede2]/50 border border-[#000000]/5 space-y-1.5">
                        <div><strong>Operator:</strong> {{ selectedLogForInspect.user?.name }} ({{ selectedLogForInspect.user?.email }})</div>
                        <div><strong>IP Address:</strong> {{ selectedLogForInspect.ip_address }}</div>
                        <div class="break-all"><strong>User Agent:</strong> {{ selectedLogForInspect.user_agent }}</div>
                    </div>

                    <div>
                        <div class="font-bold text-[#000000] mb-1">Payload JSON Metadata:</div>
                        <pre class="p-3 rounded-2xl bg-[#000000] text-[#beedc0] font-mono text-[11px] overflow-x-auto max-h-60">{{ JSON.stringify(selectedLogForInspect.metadata || {}, null, 2) }}</pre>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-[#000000]/10 shrink-0">
                    <button
                        type="button"
                        @click="selectedLogForInspect = null"
                        class="min-h-[44px] px-6 py-2 rounded-xl bg-[#065f46] text-xs font-bold text-[#ffffff] hover:bg-[#054d38]"
                    >
                        Tutup
                    </button>
                </div>
            </motion.div>
        </div>
    </AdminLayout>
</template>
