<script setup lang="ts">
/**
 * @file Index.vue (Medical Record Access Audit Trail)
 * @description Viewer Log Jejak Audit Akses Rekam Medis (UU PDP No. 27/2022 & Permenkes No. 24/2022).
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
    Download,
    Eye,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Filter,
    Globe,
    Lock,
    Printer,
    Search,
    Shield,
    ShieldCheck,
    User,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { ref } from 'vue'

interface AuditLogItem {
    audit_log_id: number
    medical_record_id: number
    user_id: number
    action: 'view' | 'create' | 'update' | 'export_pdf' | 'print'
    ip_address: string | null
    user_agent: string | null
    payload_diff: Record<string, any> | null
    created_at: string
    user?: {
        id: number
        name: string
        email: string
        role?: string
    }
    medical_record?: {
        medical_record_id: number
        patient_id: number
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
const selectedPayloadModal = ref<AuditLogItem | null>(null)

const applyFilters = () => {
    router.get(
        '/staff/audit-logs',
        {
            search: searchInput.value || undefined,
            action: selectedAction.value || undefined,
        },
        { preserveState: true, replace: true }
    )
}

const actionStyles: Record<string, { label: string; badge: string; icon: any }> = {
    view: { label: 'Buka / Lihat EMR', badge: 'bg-blue-50 text-blue-800 border-blue-200', icon: Eye },
    create: { label: 'Pembuatan SOAP', badge: 'bg-emerald-50 text-emerald-800 border-emerald-200', icon: FileCheck },
    update: { label: 'Pembaruan Data', badge: 'bg-purple-50 text-purple-800 border-purple-200', icon: Activity },
    export_pdf: { label: 'Ekspor PDF Resume', badge: 'bg-amber-50 text-amber-800 border-amber-200', icon: Download },
    print: { label: 'Cetak Dokumen', badge: 'bg-neutral-100 text-neutral-800 border-neutral-300', icon: Printer },
}
</script>

<template>
    <div class="min-h-screen bg-[#edede2] px-4 py-8 font-['Rubik'] text-[#000000] sm:px-6 lg:px-8">
        <Head title="Audit Trail Rekam Medis (UU PDP) - Hospital Population" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header Banner -->
            <motion.div
                :initial="{ opacity: 0, y: -15 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-2xl border border-[#000000]/15 bg-[#fffff3] p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-1 text-xs font-bold text-[#065f46]">
                            <ShieldCheck class="size-3.5" />
                            <span>Kepatuhan UU PDP No. 27/2022 & Permenkes No. 24/2022</span>
                        </span>
                    </div>
                    <h1 class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl">
                        Jejak Audit Akses Rekam Medis Pasien (EMR)
                    </h1>
                    <p class="text-xs text-[#000000]/60 sm:text-sm">
                        Catatan audit immutable yang merekam setiap aktivitas pembukaan riwayat klinis, pembuatan SOAP, dan ekspor resume medis.
                    </p>
                </div>
            </motion.div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-sm">
                    <div class="text-xs font-semibold text-[#000000]/60 uppercase">Total Log Akses</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-[#000000]">{{ stats.total_access }}</div>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-blue-800 uppercase">Dilihat Hari Ini</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-blue-800">{{ stats.views_today }}</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-emerald-800 uppercase">Rekam Medis Dibuat Hari Ini</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-emerald-800">{{ stats.creates_today }}</div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 shadow-sm">
                    <div class="text-xs font-semibold text-amber-800 uppercase">Ekspor PDF Hari Ini</div>
                    <div class="mt-1 text-2xl font-bold font-mono text-amber-800">{{ stats.exports_today }}</div>
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
                        placeholder="Cari nama tenaga medis, pasien, NIK, atau alamat IP..."
                        class="min-h-[44px] w-full rounded-lg border border-[#000000]/15 bg-[#edede2]/40 pl-9 pr-4 text-sm text-[#000000] placeholder-[#000000]/40 focus:border-[#065f46] focus:outline-none"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="selectedAction"
                        @change="applyFilters"
                        class="min-h-[44px] rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-2 text-xs font-medium text-[#000000] focus:border-[#065f46] focus:outline-none"
                    >
                        <option value="">Semua Tindakan Audit</option>
                        <option value="view">Melihat / Membuka EMR</option>
                        <option value="create">Pembuatan SOAP Baru</option>
                        <option value="export_pdf">Ekspor / Cetak PDF</option>
                    </select>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="rounded-2xl border border-[#000000]/10 bg-[#fffff3] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-[#edede2]/70 text-[#000000] border-b border-[#000000]/10 uppercase tracking-wider text-[11px] font-bold">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Waktu Akses (WIB)</th>
                                <th class="py-3.5 px-4">Tenaga Medis / Pengakses</th>
                                <th class="py-3.5 px-4">Tindakan</th>
                                <th class="py-3.5 px-4">Pasien Terkait</th>
                                <th class="py-3.5 px-4">Alamat IP & Jaringan</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Rincian Metadata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#000000]/5 text-[#000000]/90 font-medium">
                            <tr
                                v-for="log in logs.data"
                                :key="log.audit_log_id"
                                class="hover:bg-[#edede2]/30 transition-colors"
                            >
                                <td class="py-4 px-4 sm:px-6 font-mono text-xs text-[#000000]/70">
                                    {{ new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }) }}
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-bold text-[#000000]">{{ log.user?.name || 'Sistem' }}</div>
                                    <div class="text-xs text-[#000000]/60">{{ log.user?.email }}</div>
                                </td>

                                <td class="py-4 px-4">
                                    <span
                                        v-if="actionStyles[log.action]"
                                        :class="actionStyles[log.action].badge"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    >
                                        <component :is="actionStyles[log.action].icon" class="size-3" />
                                        <span>{{ actionStyles[log.action].label }}</span>
                                    </span>
                                    <span v-else class="text-xs font-mono uppercase">{{ log.action }}</span>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-bold text-[#000000]">
                                        {{ log.medical_record?.patient?.name || 'Pasien' }}
                                    </div>
                                    <div class="text-xs text-[#000000]/50 font-mono">
                                        NIK: {{ log.medical_record?.patient?.resident_n || '-' }}
                                    </div>
                                </td>

                                <td class="py-4 px-4 font-mono text-xs text-[#065f46]">
                                    <div class="flex items-center gap-1.5">
                                        <Globe class="size-3 text-[#065f46]/60" />
                                        <span>{{ log.ip_address || '127.0.0.1' }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <button
                                        v-if="log.payload_diff"
                                        type="button"
                                        @click="selectedPayloadModal = log"
                                        class="min-h-[36px] inline-flex items-center gap-1 rounded-lg border border-[#000000]/15 bg-[#fffff3] px-3 py-1.5 text-xs font-medium text-[#000000] hover:bg-[#edede2] cursor-pointer transition-colors"
                                    >
                                        <FileText class="size-3 text-[#065f46]" />
                                        <span>Metadata</span>
                                    </button>
                                    <span v-else class="text-xs text-[#000000]/40">-</span>
                                </td>
                            </tr>

                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-[#000000]/50">
                                    <Shield class="size-10 text-[#000000]/20 mx-auto mb-2" />
                                    <span>Tidak ada catatan audit yang cocok dengan filter.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Detail Metadata Diff -->
        <div v-if="selectedPayloadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#000000]/50 backdrop-blur-xs">
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="w-full max-w-lg rounded-3xl border border-[#000000]/15 bg-[#fffff3] p-6 sm:p-8 shadow-2xl space-y-4"
            >
                <div class="flex items-center justify-between border-b border-[#000000]/10 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="size-9 rounded-xl bg-[#beedc0] text-[#065f46] flex items-center justify-center">
                            <FileText class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-serif text-base font-bold text-[#000000]">Metadata Log Audit</h3>
                            <p class="text-xs text-[#000000]/60">Log ID #{{ selectedPayloadModal.audit_log_id }}</p>
                        </div>
                    </div>
                    <button type="button" @click="selectedPayloadModal = null" class="p-1.5 text-[#000000]/50 hover:text-[#000000]">
                        <X class="size-5" />
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="font-bold text-[#000000] mb-1">User-Agent Klien:</div>
                        <div class="rounded-xl bg-[#edede2]/60 p-3 text-[11px] font-mono break-all text-[#000000]/70 border border-[#000000]/10">
                            {{ selectedPayloadModal.user_agent || 'Unknown Client' }}
                        </div>
                    </div>

                    <div>
                        <div class="font-bold text-[#000000] mb-1">Payload / Parameter Audit:</div>
                        <pre class="rounded-xl bg-[#000000] text-[#beedc0] p-3 text-[11px] font-mono overflow-x-auto border border-[#000000]">{{ JSON.stringify(selectedPayloadModal.payload_diff, null, 2) }}</pre>
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="button"
                        @click="selectedPayloadModal = null"
                        class="min-h-[44px] w-full rounded-xl bg-[#000000] text-xs font-bold text-[#ffffff] hover:bg-[#333333] transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </motion.div>
        </div>
    </div>
</template>
