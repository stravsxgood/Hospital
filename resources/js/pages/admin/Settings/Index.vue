<script setup lang="ts">
/**
 * @file Index.vue (Super Admin System Settings Management)
 * @description Pengaturan Konfigurasi Dinamis SIMRS Hospital Population:
 *              - Layar Antrean Monitor TV (DisplayBoard)
 *              - Parameter Operasional & Ambang Batas Inaktivitas Akun
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
    CheckCircle2,
    Clock,
    Eye,
    Globe,
    HelpCircle,
    Layers,
    Loader2,
    Radio,
    Save,
    Settings,
    Shield,
    ShieldCheck,
    Sliders,
    Sparkles,
    Tv,
    Users,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, reactive, ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface SettingItem {
    id: number;
    key: string;
    value: string | null;
    group: string;
}

const props = defineProps<{
    settings: Record<string, SettingItem[]>;
}>();

// Flat form state map
const formData = reactive<Record<string, string>>({});

// Inisialisasi formData dari props
Object.values(props.settings).forEach((groupItems) => {
    groupItems.forEach((item) => {
        formData[item.key] = item.value ?? '';
    });
});

const isSubmitting = ref(false);
const saveSuccess = ref(false);
const saveError = ref<string | null>(null);

const activeTab = ref<'display' | 'operational'>('display');

const handleSubmit = () => {
    isSubmitting.value = true;
    saveSuccess.value = false;
    saveError.value = null;

    router.put(
        '/admin/settings',
        { settings: { ...formData } },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                saveSuccess.value = true;
                setTimeout(() => {
                    saveSuccess.value = false;
                }, 4000);
            },
            onError: (errors) => {
                isSubmitting.value = false;
                saveError.value =
                    Object.values(errors)[0] ||
                    'Gagal menyimpan konfigurasi. Silakan periksa kembali.';
            },
        },
    );
};
</script>

<template>
    <AdminLayout
        title="Pengaturan Sistem - Super Admin SIMRS"
        :breadcrumbs="[
            { title: 'Dashboard Eksekutif', href: '/admin/dashboard' },
            { title: 'Pengaturan Sistem', href: '/admin/settings' },
        ]"
    >
        <div class="mx-auto max-w-6xl space-y-6">
            <!-- ═══════════════════════════════════════════════════════════════
                 1. Header Banner
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.header
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:flex-row sm:items-center sm:justify-between sm:p-7"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#beedc0] bg-[#beedc0]/40 px-3.5 py-1 text-xs font-bold text-[#000000]"
                        >
                            <Settings class="size-3.5 text-[#000000]" />
                            <span>Konfigurasi Sistem Global</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        Pengaturan & Parameter Operasional
                    </h1>
                    <p class="font-['Rubik'] text-xs text-[#333333] sm:text-sm">
                        Kelola parameter display antrean ruang tunggu, ambang
                        batas inaktivitas akun, dan preferensi SIMRS lainnya.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <motion.button
                        type="button"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                        :disabled="isSubmitting"
                        @click="handleSubmit"
                        class="inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-7 py-2.5 font-['Rubik'] text-sm font-medium text-[#ffffff] shadow-none transition-all hover:bg-[#1a1a1a] disabled:opacity-50"
                    >
                        <Loader2
                            v-if="isSubmitting"
                            class="size-4 animate-spin text-[#beedc0]"
                        />
                        <Save v-else class="size-4 text-[#beedc0]" />
                        <span>{{
                            isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'
                        }}</span>
                    </motion.button>
                </div>
            </motion.header>

            <!-- Feedback Alerts -->
            <div v-if="saveSuccess" class="space-y-2 font-['Rubik']">
                <motion.div
                    :initial="{ opacity: 0, y: -8 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="flex items-center gap-2 rounded-2xl border border-[#beedc0] bg-[#beedc0]/40 p-4 text-xs font-medium text-[#000000] shadow-none sm:text-sm"
                >
                    <CheckCircle2 class="size-5 shrink-0 text-[#000000]" />
                    <span
                        >Pengaturan sistem berhasil disimpan dan cache telah
                        diperbarui.</span
                    >
                </motion.div>
            </div>

            <div v-if="saveError" class="space-y-2 font-['Rubik']">
                <motion.div
                    :initial="{ opacity: 0, y: -8 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-medium text-rose-900 shadow-none sm:text-sm"
                >
                    <AlertCircle class="size-5 shrink-0 text-rose-600" />
                    <span>{{ saveError }}</span>
                </motion.div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 2. Navigation Tabs (Mobile-Friendly)
                 ═══════════════════════════════════════════════════════════════ -->
            <div
                class="flex scrollbar-none items-center gap-2 overflow-x-auto border-b border-[#000000]/10 pb-2 font-['Rubik']"
            >
                <button
                    type="button"
                    @click="activeTab = 'display'"
                    :class="
                        activeTab === 'display'
                            ? 'bg-[#000000] text-[#ffffff] shadow-none'
                            : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                    "
                    class="flex min-h-[42px] cursor-pointer items-center justify-center gap-2 rounded-full px-5 py-2 text-xs font-medium transition-all sm:text-sm"
                >
                    <Tv class="size-4" />
                    <span>Layar Monitor Antrean (DisplayBoard)</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'operational'"
                    :class="
                        activeTab === 'operational'
                            ? 'bg-[#000000] text-[#ffffff] shadow-none'
                            : 'border border-[#000000]/10 bg-[#fffff3] text-[#000000]/80 hover:bg-[#edede2]'
                    "
                    class="flex min-h-[42px] cursor-pointer items-center justify-center gap-2 rounded-full px-5 py-2 text-xs font-medium transition-all sm:text-sm"
                >
                    <Sliders class="size-4" />
                    <span>Parameter Operasional & SDM</span>
                </button>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════
                 3. Form Settings Sections
                 ═══════════════════════════════════════════════════════════════ -->
            <form
                @submit.prevent="handleSubmit"
                class="space-y-6 font-['Rubik']"
            >
                <!-- Tab 1: Display Monitor Settings -->
                <div v-show="activeTab === 'display'" class="space-y-6">
                    <div
                        class="space-y-6 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-7"
                    >
                        <div class="border-b border-[#000000]/10 pb-4">
                            <h2
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Konfigurasi Layar Antrean TV Ruang Tunggu
                            </h2>
                            <p class="text-xs text-[#333333]">
                                Pengaturan ini langsung mengontrol tampilan
                                publik di halaman
                                <code
                                    class="rounded bg-[#edede2] px-1.5 py-0.5 font-mono text-[#000000]"
                                    >/display</code
                                >.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Hospital Name on Display -->
                            <div class="space-y-2">
                                <label
                                    for="setting_hospital_name"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Nama Rumah Sakit di Header Display
                                </label>
                                <input
                                    id="setting_hospital_name"
                                    v-model="formData['display.hospital_name']"
                                    type="text"
                                    placeholder="Contoh: Rumah Sakit Hospital Population"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                />
                                <p class="text-[11px] text-[#333333]">
                                    Ditampilkan pada navbar bagian atas layar
                                    antrean TV.
                                </p>
                            </div>

                            <!-- Scroll Speed -->
                            <div class="space-y-2">
                                <label
                                    for="setting_scroll_speed"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Interval Refresh / Transisi Polling (ms)
                                </label>
                                <input
                                    id="setting_scroll_speed"
                                    v-model="formData['display.scroll_speed']"
                                    type="number"
                                    min="1000"
                                    max="30000"
                                    step="500"
                                    placeholder="5000"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                />
                                <p class="text-[11px] text-[#333333]">
                                    Waktu jeda polling data antrean baru
                                    (standar: 5000 ms = 5 detik).
                                </p>
                            </div>

                            <!-- Show Patient Name -->
                            <div class="space-y-2">
                                <label
                                    for="setting_show_patient_name"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Privasi Nama Pasien (UU PDP)
                                </label>
                                <select
                                    id="setting_show_patient_name"
                                    v-model="
                                        formData['display.show_patient_name']
                                    "
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                >
                                    <option value="true">
                                        Tampilkan Nama Lengkap Pasien
                                    </option>
                                    <option value="false">
                                        Sensor Nama (Inisial Saja / Sesuai UU
                                        PDP)
                                    </option>
                                </select>
                                <p class="text-[11px] text-[#333333]">
                                    Pilih opsi penyensoran jika diwajibkan oleh
                                    standar privasi klinik.
                                </p>
                            </div>

                            <!-- Display Theme -->
                            <div class="space-y-2">
                                <label
                                    for="setting_display_theme"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Tema Warna Layar Display
                                </label>
                                <select
                                    id="setting_display_theme"
                                    v-model="formData['display.theme']"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                >
                                    <option value="evergreen">
                                        Evergreen (Linen & Ink Black) - Standar
                                        SIMRS
                                    </option>
                                    <option value="dark">
                                        Dark Clinic (Kontras Tinggi TV)
                                    </option>
                                </select>
                                <p class="text-[11px] text-[#333333]">
                                    Skema palet visual yang diterapkan pada
                                    DisplayBoard.
                                </p>
                            </div>

                            <!-- Running Announcement Text -->
                            <div class="space-y-2 md:col-span-2">
                                <label
                                    for="setting_announcement"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Teks Pengumuman Berjalan (Running Text
                                    Footer)
                                </label>
                                <textarea
                                    id="setting_announcement"
                                    v-model="
                                        formData['display.announcement_text']
                                    "
                                    rows="3"
                                    placeholder="Selamat datang di Rumah Sakit Hospital Population. Mohon menunggu panggilan antrean Anda..."
                                    class="w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] p-4 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                ></textarea>
                                <p class="text-[11px] text-[#333333]">
                                    Pesan berjalan yang dapat dibaca pengunjung
                                    di layar monitor ruang tunggu.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Operational & Staff Lifecycle Settings -->
                <div v-show="activeTab === 'operational'" class="space-y-6">
                    <div
                        class="space-y-6 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-7"
                    >
                        <div class="border-b border-[#000000]/10 pb-4">
                            <h2
                                class="font-['ivypresto-headline'] text-lg font-bold text-[#000000] sm:text-xl"
                            >
                                Parameter Operasional & Tata Kelola Akun
                            </h2>
                            <p class="text-xs text-[#333333]">
                                Parameter siklus hidup akun pengguna dan kuota
                                pendaftaran harian.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Inactive Threshold Days -->
                            <div class="space-y-2">
                                <label
                                    for="setting_inactive_days"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Batas Inaktivitas Akun Pengguna (Hari)
                                </label>
                                <input
                                    id="setting_inactive_days"
                                    v-model="
                                        formData[
                                            'operational.inactive_threshold_days'
                                        ]
                                    "
                                    type="number"
                                    min="7"
                                    max="365"
                                    placeholder="90"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                />
                                <p class="text-[11px] text-[#333333]">
                                    Akun tanpa aktivitas login melebihi batas
                                    hari ini akan diproses oleh perintah
                                    <code
                                        class="rounded bg-[#edede2] px-1.5 py-0.5 font-mono text-[#000000]"
                                        >php artisan
                                        admin:cleanup-inactive-users</code
                                    >.
                                </p>
                            </div>

                            <!-- Default Quota per Day -->
                            <div class="space-y-2">
                                <label
                                    for="setting_default_quota"
                                    class="block text-xs font-medium text-[#000000] uppercase"
                                >
                                    Kuota Pasien Default per Jadwal Praktik
                                </label>
                                <input
                                    id="setting_default_quota"
                                    v-model="
                                        formData[
                                            'operational.default_quota_per_day'
                                        ]
                                    "
                                    type="number"
                                    min="1"
                                    max="200"
                                    placeholder="20"
                                    class="min-h-[44px] w-full rounded-xl border border-[#000000]/15 bg-[#ffffff] px-4 py-2.5 text-sm text-[#000000] transition-colors focus:border-[#000000] focus:outline-none"
                                />
                                <p class="text-[11px] text-[#333333]">
                                    Batas kuota pendaftaran awal saat jadwal
                                    dokter baru dibuat oleh Super Admin.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
