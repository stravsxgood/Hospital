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
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';

interface BillingReceiptData {
    billing_id: number;
    invoice_number: string;
    total_amount: number | string;
    payment_method?: string | null;
    paid_at?: string | null;
    created_at?: string;
    status: string;
    patient?: {
        name: string;
        resident_n?: string;
    };
    reservation?: {
        queue_number?: string;
        doctorSchedule?: {
            doctor?: { name: string };
            poli?: { name_poli?: string; name?: string };
        };
    };
    processedByNurse?: {
        name?: string;
        user?: { name: string };
    };
    items?: Array<{
        billing_item_id?: number;
        item_name: string;
        item_type: string;
        quantity: number;
        unit_price: number | string;
        subtotal: number | string;
    }>;
}

const props = defineProps<{
    open: boolean;
    billing: BillingReceiptData | null;
    hospitalInfo?: {
        name: string;
        address: string;
        phone: string;
        unit: string;
    };
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// Pilihan Lebar Kertas Thermal: 58mm vs 80mm
const paperSize = ref<'58mm' | '80mm'>('58mm');
const isCopied = ref(false);

const hospital = computed(() => ({
    name: props.hospitalInfo?.name || 'HOSPITAL POPULATION',
    address: props.hospitalInfo?.address || 'Jl. Kesehatan No. 123, Jakarta',
    phone: props.hospitalInfo?.phone || '(021) 555-0199',
    unit: props.hospitalInfo?.unit || 'Instalasi Kasir Rawat Jalan',
}));

const formattedDate = computed(() => {
    const raw =
        props.billing?.paid_at ||
        props.billing?.created_at ||
        new Date().toISOString();
    const d = new Date(raw);

    return d.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const cashierName = computed(() => {
    return (
        props.billing?.processedByNurse?.name ||
        props.billing?.processedByNurse?.user?.name ||
        'Petugas Kasir'
    );
});

const formatCurrency = (val: number | string): string => {
    return Number(val || 0).toLocaleString('id-ID');
};

// Trigger browser print dialog
const printReceipt = () => {
    window.print();
};

const copyRawText = () => {
    if (!props.billing) {
        return;
    }

    const divider =
        paperSize.value === '58mm'
            ? '--------------------------------'
            : '------------------------------------------------';
    let text = `${hospital.value.name}\n${hospital.value.address}\nTelp: ${hospital.value.phone}\n${hospital.value.unit}\n`;
    text += `${divider}\n`;
    text += `No. Faktur : ${props.billing.invoice_number}\n`;
    text += `Waktu      : ${formattedDate.value}\n`;
    text += `Kasir      : ${cashierName.value}\n`;
    text += `Pasien     : ${props.billing.patient?.name || '-'}\n`;
    text += `No. Antrean: ${props.billing.reservation?.queue_number || '-'}\n`;
    text += `Poli/Dokter: ${props.billing.reservation?.doctorSchedule?.poli?.name_poli || 'Poli'} / ${props.billing.reservation?.doctorSchedule?.doctor?.name || 'Dokter'}\n`;
    text += `${divider}\n`;
    props.billing.items?.forEach((item) => {
        text += `${item.item_name}\n`;
        text += `  ${item.quantity} x Rp ${formatCurrency(item.unit_price)} = Rp ${formatCurrency(item.subtotal)}\n`;
    });
    text += `${divider}\n`;
    text += `TOTAL AKHIR: Rp ${formatCurrency(props.billing.total_amount)}\n`;
    text += `Metode     : ${props.billing.payment_method || 'Tunai'}\n`;
    text += `Status     : LUNAS (${props.billing.status.toUpperCase()})\n`;
    text += `${divider}\n`;
    text += `     TERIMA KASIH ATAS KUNJUNGAN ANDA\n`;
    text += `          SEMOGA LEKAS SEMBUH\n`;

    navigator.clipboard.writeText(text).then(() => {
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    });
};
</script>

<template>
    <div
        v-if="open && billing"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#000000]/60 p-3 font-['Rubik'] backdrop-blur-xs sm:p-4"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95, y: 15 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ duration: 0.2, ease: 'easeOut' }"
            class="my-auto flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-[12px] border border-[#333333]/20 bg-[#fffff3] text-[#000000] shadow-2xl"
        >
            <!-- Header Modal -->
            <header
                class="flex shrink-0 items-center justify-between gap-3 border-b border-[#333333]/15 bg-[#edede2] px-5 py-3.5"
            >
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-[#333333]/15 bg-[#beedc0]"
                    >
                        <Receipt class="size-5 text-[#000000]" />
                    </div>
                    <div>
                        <h3
                            class="font-['ivypresto-headline'] text-lg leading-tight font-bold text-[#000000]"
                        >
                            Struk Thermal POS
                        </h3>
                        <p class="text-xs text-[#333333]/70">
                            Pratinjau Kuitansi Kasir ESC/POS
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Toggle Ukuran Kertas 58mm vs 80mm -->
                    <div
                        class="inline-flex rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] p-0.5 text-xs font-semibold"
                    >
                        <button
                            type="button"
                            @click="paperSize = '58mm'"
                            class="cursor-pointer rounded-[40.5px] px-2.5 py-1 transition-colors"
                            :class="
                                paperSize === '58mm'
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            58 mm
                        </button>
                        <button
                            type="button"
                            @click="paperSize = '80mm'"
                            class="cursor-pointer rounded-[40.5px] px-2.5 py-1 transition-colors"
                            :class="
                                paperSize === '80mm'
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#333333] hover:bg-[#edede2]'
                            "
                        >
                            80 mm
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border border-[#333333]/20 bg-[#ffffff] text-[#333333] transition-colors hover:bg-rose-50 hover:text-rose-600"
                    >
                        <X class="size-4" />
                    </button>
                </div>
            </header>

            <!-- Scrollable Receipt Preview Area -->
            <div
                class="flex flex-1 justify-center overflow-y-auto bg-[#edede2]/50 p-4 sm:p-6"
            >
                <!-- Receipt Paper Simulation -->
                <div
                    id="thermal-receipt-print-area"
                    class="border border-[#333333]/15 bg-[#ffffff] p-4 font-mono text-xs text-[#000000] shadow-md transition-all duration-200 sm:p-5"
                    :class="paperSize === '58mm' ? 'w-[300px]' : 'w-[400px]'"
                >
                    <!-- Hospital Brand Header -->
                    <div
                        class="space-y-0.5 border-b border-dashed border-[#000000]/40 pb-2 text-center"
                    >
                        <h4 class="text-sm font-bold tracking-wider uppercase">
                            {{ hospital.name }}
                        </h4>
                        <p class="text-[11px] text-[#333333]">
                            {{ hospital.address }}
                        </p>
                        <p class="text-[11px] text-[#333333]">
                            Telp: {{ hospital.phone }}
                        </p>
                        <p class="text-[10px] font-semibold text-[#000000]">
                            {{ hospital.unit }}
                        </p>
                    </div>

                    <!-- Meta Transaksi -->
                    <div
                        class="space-y-1 border-b border-dashed border-[#000000]/40 py-2 text-[11px]"
                    >
                        <div class="flex justify-between">
                            <span>No. Faktur:</span>
                            <span class="font-bold">{{
                                billing.invoice_number
                            }}</span>
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
                            <span
                                class="max-w-[170px] truncate text-right font-bold"
                                >{{ billing.patient?.name || '-' }}</span
                            >
                        </div>
                        <div
                            v-if="billing.reservation?.queue_number"
                            class="flex justify-between"
                        >
                            <span>No. Antrean:</span>
                            <span class="font-bold">{{
                                billing.reservation.queue_number
                            }}</span>
                        </div>
                        <div
                            v-if="billing.reservation?.doctorSchedule"
                            class="flex justify-between"
                        >
                            <span>Poli / Dokter:</span>
                            <span class="max-w-[170px] truncate text-right">
                                {{
                                    billing.reservation.doctorSchedule.poli
                                        ?.name_poli || 'Poli'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Itemized Breakdown -->
                    <div
                        class="space-y-1.5 border-b border-dashed border-[#000000]/40 py-2 text-[11px]"
                    >
                        <div
                            v-for="(item, idx) in billing.items"
                            :key="item.billing_item_id || idx"
                            class="space-y-0.5"
                        >
                            <div class="truncate font-semibold">
                                {{ item.item_name }}
                            </div>
                            <div class="flex justify-between text-[#333333]">
                                <span
                                    >{{ item.quantity }} x Rp
                                    {{ formatCurrency(item.unit_price) }}</span
                                >
                                <span class="font-bold text-[#000000]"
                                    >Rp
                                    {{ formatCurrency(item.subtotal) }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Total & Payment Method -->
                    <div
                        class="space-y-1 border-b border-dashed border-[#000000]/40 py-2 text-[11px]"
                    >
                        <div
                            class="flex justify-between pt-1 text-xs font-bold"
                        >
                            <span>TOTAL TAGIHAN:</span>
                            <span
                                >Rp
                                {{ formatCurrency(billing.total_amount) }}</span
                            >
                        </div>
                        <div class="flex justify-between pt-0.5">
                            <span>Metode Bayar:</span>
                            <span class="font-semibold uppercase">{{
                                billing.payment_method || 'Tunai'
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between font-bold text-emerald-700"
                        >
                            <span>Status:</span>
                            <span>LUNAS (PAID)</span>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div
                        class="space-y-0.5 pt-3 text-center text-[10px] text-[#333333]"
                    >
                        <p class="font-semibold tracking-wider uppercase">
                            Terima Kasih Atas Kunjungan Anda
                        </p>
                        <p>Semoga Lekas Sembuh & Sehat Selalu</p>
                        <p class="pt-1 text-[9px] text-[#333333]/60">
                            Struk ini adalah bukti pembayaran sah Rumah Sakit.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <footer
                class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-[#333333]/15 bg-[#edede2] px-5 py-3.5"
            >
                <button
                    type="button"
                    @click="copyRawText"
                    class="inline-flex min-h-[40px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-3.5 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                >
                    <Copy class="size-3.5" />
                    <span>{{
                        isCopied ? 'Teks Disalin!' : 'Salin Teks Struk'
                    }}</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="min-h-[44px] cursor-pointer rounded-[40.5px] border border-[#333333]/20 bg-[#ffffff] px-4 text-xs font-semibold text-[#333333] hover:bg-[#edede2]"
                    >
                        Tutup
                    </button>

                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.03 }"
                        :whileTap="{ scale: 0.97 }"
                        @click="printReceipt"
                        class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] bg-[#000000] px-5 text-xs font-bold text-white shadow-md hover:bg-[#222222]"
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
