<script setup lang="ts">
/**
 * ThermalReceiptModal.vue
 *
 * Komponen cetak struk kuitansi kasir POS (Point of Sale) format thermal ESC/POS.
 * Mendukung ukuran kertas thermal standar 58mm dan 80mm.
 * Dilengkapi dengan CSS print khusus (@media print) bebas margin untuk printer kasir.
 */
import {
    CheckCircle2,
    Copy,
    FileText,
    Printer,
    Receipt,
    RotateCcw,
    X,
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed, ref } from 'vue'

interface BillingReceiptData {
    billing_id: number
    invoice_number: string
    total_amount: number | string
    payment_method?: string | null
    paid_at?: string | null
    created_at?: string
    status: string
    patient?: {
        name: string
        resident_n?: string
    }
    reservation?: {
        queue_number?: string
        doctorSchedule?: {
            doctor?: { name: string }
            poli?: { name_poli?: string; name?: string }
        }
    }
    processedByNurse?: {
        name?: string
        user?: { name: string }
    }
    items?: Array<{
        billing_item_id?: number
        item_name: string
        item_type: string
        quantity: number
        unit_price: number | string
        subtotal: number | string
    }>
}

const props = defineProps<{
    open: boolean
    billing: BillingReceiptData | null
    hospitalInfo?: {
        name: string
        address: string
        phone: string
        unit: string
    }
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
}>()

// Pilihan Lebar Kertas Thermal: 58mm vs 80mm
const paperSize = ref<'58mm' | '80mm'>('58mm')
const isCopied = ref(false)

const hospital = computed(() => ({
    name: props.hospitalInfo?.name || 'HOSPITAL POPULATION',
    address: props.hospitalInfo?.address || 'Jl. Kesehatan No. 123, Jakarta',
    phone: props.hospitalInfo?.phone || '(021) 555-0199',
    unit: props.hospitalInfo?.unit || 'Instalasi Kasir Rawat Jalan',
}))

const formattedDate = computed(() => {
    const raw = props.billing?.paid_at || props.billing?.created_at || new Date().toISOString()
    const d = new Date(raw)
    return d.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
})

const cashierName = computed(() => {
    return props.billing?.processedByNurse?.name ||
        props.billing?.processedByNurse?.user?.name ||
        'Petugas Kasir'
})

const formatCurrency = (val: number | string): string => {
    return Number(val || 0).toLocaleString('id-ID')
}

// Trigger browser print dialog
const printReceipt = () => {
    window.print()
}

const copyRawText = () => {
    if (!props.billing) return
    const divider = paperSize.value === '58mm' ? '--------------------------------' : '------------------------------------------------'
    let text = `${hospital.value.name}\n${hospital.value.address}\nTelp: ${hospital.value.phone}\n${hospital.value.unit}\n`
    text += `${divider}\n`
    text += `No. Faktur : ${props.billing.invoice_number}\n`
    text += `Waktu      : ${formattedDate.value}\n`
    text += `Kasir      : ${cashierName.value}\n`
    text += `Pasien     : ${props.billing.patient?.name || '-'}\n`
    text += `No. Antrean: ${props.billing.reservation?.queue_number || '-'}\n`
    text += `Poli/Dokter: ${props.billing.reservation?.doctorSchedule?.poli?.name_poli || 'Poli'} / ${props.billing.reservation?.doctorSchedule?.doctor?.name || 'Dokter'}\n`
    text += `${divider}\n`
    props.billing.items?.forEach((item) => {
        text += `${item.item_name}\n`
        text += `  ${item.quantity} x Rp ${formatCurrency(item.unit_price)} = Rp ${formatCurrency(item.subtotal)}\n`
    })
    text += `${divider}\n`
    text += `TOTAL AKHIR: Rp ${formatCurrency(props.billing.total_amount)}\n`
    text += `Metode     : ${props.billing.payment_method || 'Tunai'}\n`
    text += `Status     : LUNAS (${props.billing.status.toUpperCase()})\n`
    text += `${divider}\n`
    text += `     TERIMA KASIH ATAS KUNJUNGAN ANDA\n`
    text += `          SEMOGA LEKAS SEMBUH\n`

    navigator.clipboard.writeText(text).then(() => {
        isCopied.value = true
        setTimeout(() => {
            isCopied.value = false
        }, 2000)
    })
}
</script>

<template>
    <div
        v-if="open && billing"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#000000]/60 backdrop-blur-xs font-['Rubik'] overflow-y-auto"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95, y: 15 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.2, ease: 'easeOut' }"
            class="w-full max-w-lg rounded-[12px] border border-[#333333]/20 bg-[#fffff3] text-[#000000] shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh]"
        >
            <!-- Header Modal -->
            <header class="bg-[#edede2] border-b border-[#333333]/15 px-5 py-3.5 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="h-9 w-9 rounded-full bg-[#beedc0] flex items-center justify-center border border-[#333333]/15">
                        <Receipt class="size-5 text-[#000000]" />
                    </div>
                    <div>
                        <h3 class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] leading-tight">
                            Struk Thermal POS
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            Pratinjau Kuitansi Kasir ESC/POS
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Toggle Ukuran Kertas 58mm vs 80mm -->
                    <div class="inline-flex rounded-[40.5px] bg-[#ffffff] border border-[#333333]/20 p-0.5 text-xs font-semibold">
                        <button
                            type="button"
                            @click="paperSize = '58mm'"
                            class="px-2.5 py-1 rounded-[40.5px] transition-colors cursor-pointer"
                            :class="paperSize === '58mm' ? 'bg-[#000000] text-white' : 'text-[#333333] hover:bg-[#edede2]'"
                        >
                            58 mm
                        </button>
                        <button
                            type="button"
                            @click="paperSize = '80mm'"
                            class="px-2.5 py-1 rounded-[40.5px] transition-colors cursor-pointer"
                            :class="paperSize === '80mm' ? 'bg-[#000000] text-white' : 'text-[#333333] hover:bg-[#edede2]'"
                        >
                            80 mm
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="h-8 w-8 rounded-full bg-[#ffffff] border border-[#333333]/20 flex items-center justify-center text-[#333333] hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer"
                    >
                        <X class="size-4" />
                    </button>
                </div>
            </header>

            <!-- Scrollable Receipt Preview Area -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 bg-[#edede2]/50 flex justify-center">
                <!-- Receipt Paper Simulation -->
                <div
                    id="thermal-receipt-print-area"
                    class="bg-[#ffffff] text-[#000000] p-4 sm:p-5 shadow-md border border-[#333333]/15 font-mono text-xs transition-all duration-200"
                    :class="paperSize === '58mm' ? 'w-[300px]' : 'w-[400px]'"
                >
                    <!-- Hospital Brand Header -->
                    <div class="text-center space-y-0.5 pb-2 border-b border-dashed border-[#000000]/40">
                        <h4 class="font-bold text-sm uppercase tracking-wider">{{ hospital.name }}</h4>
                        <p class="text-[11px] text-[#333333]">{{ hospital.address }}</p>
                        <p class="text-[11px] text-[#333333]">Telp: {{ hospital.phone }}</p>
                        <p class="text-[10px] font-semibold text-[#000000]">{{ hospital.unit }}</p>
                    </div>

                    <!-- Meta Transaksi -->
                    <div class="py-2 space-y-1 text-[11px] border-b border-dashed border-[#000000]/40">
                        <div class="flex justify-between">
                            <span>No. Faktur:</span>
                            <span class="font-bold">{{ billing.invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Waktu:</span>
                            <span>{{ formattedDate }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir:</span>
                            <span>{{ cashierName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pasien:</span>
                            <span class="font-bold truncate max-w-[170px] text-right">{{ billing.patient?.name || '-' }}</span>
                        </div>
                        <div v-if="billing.reservation?.queue_number" class="flex justify-between">
                            <span>No. Antrean:</span>
                            <span class="font-bold">{{ billing.reservation.queue_number }}</span>
                        </div>
                        <div v-if="billing.reservation?.doctorSchedule" class="flex justify-between">
                            <span>Poli / Dokter:</span>
                            <span class="truncate max-w-[170px] text-right">
                                {{ billing.reservation.doctorSchedule.poli?.name_poli || 'Poli' }}
                            </span>
                        </div>
                    </div>

                    <!-- Itemized Breakdown -->
                    <div class="py-2 space-y-1.5 text-[11px] border-b border-dashed border-[#000000]/40">
                        <div
                            v-for="(item, idx) in billing.items"
                            :key="item.billing_item_id || idx"
                            class="space-y-0.5"
                        >
                            <div class="font-semibold truncate">{{ item.item_name }}</div>
                            <div class="flex justify-between text-[#333333]">
                                <span>{{ item.quantity }} x Rp {{ formatCurrency(item.unit_price) }}</span>
                                <span class="font-bold text-[#000000]">Rp {{ formatCurrency(item.subtotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total & Payment Method -->
                    <div class="py-2 space-y-1 text-[11px] border-b border-dashed border-[#000000]/40">
                        <div class="flex justify-between font-bold text-xs pt-1">
                            <span>TOTAL TAGIHAN:</span>
                            <span>Rp {{ formatCurrency(billing.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-0.5">
                            <span>Metode Bayar:</span>
                            <span class="font-semibold uppercase">{{ billing.payment_method || 'Tunai' }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-bold">
                            <span>Status:</span>
                            <span>LUNAS (PAID)</span>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="text-center pt-3 text-[10px] text-[#333333] space-y-0.5">
                        <p class="font-semibold uppercase tracking-wider">Terima Kasih Atas Kunjungan Anda</p>
                        <p>Semoga Lekas Sembuh & Sehat Selalu</p>
                        <p class="text-[9px] text-[#333333]/60 pt-1">Struk ini adalah bukti pembayaran sah Rumah Sakit.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <footer class="bg-[#edede2] border-t border-[#333333]/15 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <button
                    type="button"
                    @click="copyRawText"
                    class="min-h-[40px] px-3.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] text-xs font-semibold text-[#333333] hover:bg-[#edede2] inline-flex items-center gap-1.5 cursor-pointer"
                >
                    <Copy class="size-3.5" />
                    <span>{{ isCopied ? 'Teks Disalin!' : 'Salin Teks Struk' }}</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="min-h-[44px] px-4 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] text-xs font-semibold text-[#333333] hover:bg-[#edede2] cursor-pointer"
                    >
                        Tutup
                    </button>

                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                        @click="printReceipt"
                        class="min-h-[44px] px-5 rounded-[40.5px] bg-[#000000] text-white text-xs font-bold hover:bg-[#222222] inline-flex items-center gap-2 shadow-md cursor-pointer"
                    >
                        <Printer class="size-4 text-[#beedc0]" />
                        <span>Cetak Struk POS</span>
                    </motion.button>
                </div>
            </footer>
        </motion.div>
    </div>
</template>

<style>
@media print {
    /* Sembunyikan seluruh elemen halaman web selain area struk */
    body * {
        visibility: hidden !important;
    }

    #thermal-receipt-print-area,
    #thermal-receipt-print-area * {
        visibility: visible !important;
    }

    #thermal-receipt-print-area {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 4mm !important;
        box-shadow: none !important;
        border: none !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: monospace !important;
        font-size: 11pt !important;
    }

    @page {
        margin: 0;
        size: auto;
    }
}
</style>
