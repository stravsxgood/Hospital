<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { ref } from 'vue';
import type { Ref } from 'vue';

interface Props {
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion?: string;
    phpVersion?: string;
}

const props = withDefaults(defineProps<Props>(), {
    canLogin: true,
    canRegister: true,
    laravelVersion: '',
    phpVersion: '',
});

const isMobileMenuOpen: Ref<boolean> = ref(false);
const showPrivacyNotice: Ref<boolean> = ref(false);
const showTermsNotice: Ref<boolean> = ref(false);

const toggleMobileMenu = (): void => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

interface PoliItem {
    id: number;
    name: string;
    doctorCount: number;
    description: string;
    isOpenToday: boolean;
    scheduleNote: string;
}

const poliList: Ref<PoliItem[]> = ref([
    {
        id: 1,
        name: 'Poli Umum',
        doctorCount: 4,
        description:
            'Pemeriksaan kesehatan umum, diagnosis keluhan harian, dan rujukan lanjutan.',
        isOpenToday: true,
        scheduleNote: 'Senin - Sabtu (08.00 - 20.00)',
    },
    {
        id: 2,
        name: 'Poli Spesialis Anak',
        doctorCount: 3,
        description:
            'Pemantauan tumbuh kembang anak, imunisasi terpadu, dan konsultasi pediatrik.',
        isOpenToday: true,
        scheduleNote: 'Senin - Jumat (09.00 - 16.00)',
    },
    {
        id: 3,
        name: 'Poli Gigi dan Mulut',
        doctorCount: 2,
        description:
            'Perawatan konservasi gigi, pembersihan karang, penambalan, dan cabut gigi.',
        isOpenToday: true,
        scheduleNote: 'Senin - Sabtu (08.30 - 15.00)',
    },
    {
        id: 4,
        name: 'Poli Kebidanan dan Kandungan',
        doctorCount: 3,
        description:
            'Pemeriksaan ultrasonografi kehamilan, konsultasi antenatal, dan fertilitas.',
        isOpenToday: true,
        scheduleNote: 'Senin - Sabtu (08.00 - 17.00)',
    },
    {
        id: 5,
        name: 'Poli Penyakit Dalam',
        doctorCount: 4,
        description:
            'Penanganan komprehensif hipertensi, diabetes, ginjal, dan penyakit metabolik.',
        isOpenToday: true,
        scheduleNote: 'Senin - Sabtu (09.00 - 18.00)',
    },
    {
        id: 6,
        name: 'Poli Mata',
        doctorCount: 2,
        description:
            'Uji refraksi penglihatan, pemeriksaan tekanan bola mata, dan penanganan katarak.',
        isOpenToday: false,
        scheduleNote: 'Buka kembali besok pukul 08.30',
    },
]);

interface StepItem {
    number: string;
    title: string;
    description: string;
}

const steps: StepItem[] = [
    {
        number: '1',
        title: 'Pilih Layanan dan Poliklinik',
        description:
            'Tentukan klinik spesialis dan jadwal dokter yang bertugas sesuai kebutuhan pengobatan Anda.',
    },
    {
        number: '2',
        title: 'Ambil Nomor Antrean Digital',
        description:
            'Sistem menerbitkan tiket nomor periksa dan perkiraan jam panggilan langsung ke akun Anda.',
    },
    {
        number: '3',
        title: 'Konfirmasi Kedatangan di Loket',
        description:
            'Tiba 15 menit sebelum perkiraan panggilan untuk verifikasi berkas dan pengukuran tanda vital.',
    },
    {
        number: '4',
        title: 'Pemeriksaan dan Farmasi',
        description:
            'Konsultasi bersama dokter di ruang periksa, dilanjutkan pengambilan obat di apotek rumah sakit.',
    },
];
</script>

<template>
    <Head title="Pusat Layanan dan Pendaftaran Pasien RS Harmoni Sehat" />

    <div
        class="min-h-screen bg-[#edede2] font-['Rubik'] text-[#000000] antialiased selection:bg-[#beedc0] selection:text-[#000000]"
    >
        <!-- Bar Informasi Darurat dan Jam Operasional -->
        <aside
            class="bg-[#000000] px-4 py-2.5 text-xs text-white sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-2.5"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="inline-block h-2 w-2 rounded-full bg-[#beedc0]"
                    ></span>
                    <span class="font-medium"
                        >Unit Gawat Darurat (UGD) 24 Jam:</span
                    >
                    <a
                        href="tel:1500911"
                        class="rounded px-1.5 py-0.5 font-bold text-[#beedc0] underline hover:text-white focus:ring-2 focus:ring-[#beedc0] focus:outline-none"
                    >
                        1500-911
                    </a>
                </div>
                <div class="text-[#edede2]/80">
                    Layanan Poliklinik: Senin - Sabtu (07.30 - 21.00 WIB)
                </div>
            </div>
        </aside>

        <!-- Navigasi Utama -->
        <header
            class="sticky top-0 z-40 border-b border-[#333333]/15 bg-[#edede2]/95 backdrop-blur-xs"
        >
            <div
                class="mx-auto flex max-w-[1200px] items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8"
            >
                <!-- Identitas Rumah Sakit -->
                <Link
                    href="/"
                    class="flex items-center gap-3 rounded-lg focus:ring-2 focus:ring-[#000000] focus:outline-none"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-[#000000] text-[#beedc0]"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>
                    <div>
                        <span
                            class="block font-['DM_Serif_Display'] text-xl leading-none font-normal tracking-tight text-[#000000] sm:text-2xl"
                        >
                            RS Harmoni Sehat
                        </span>
                        <span class="text-xs text-[#333333]">
                            Sistem Pendaftaran Pasien
                        </span>
                    </div>
                </Link>

                <!-- Navigasi Desktop -->
                <nav
                    class="hidden items-center gap-6 text-sm font-medium text-[#333333] md:flex"
                >
                    <a
                        href="#layanan"
                        class="rounded px-2 py-1 transition-colors hover:text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                    >
                        Layanan Poliklinik
                    </a>
                    <a
                        href="#alur"
                        class="rounded px-2 py-1 transition-colors hover:text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                    >
                        Alur Berobat
                    </a>
                    <a
                        href="#operasional"
                        class="rounded px-2 py-1 transition-colors hover:text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                    >
                        Informasi Operasional
                    </a>
                    <a
                        href="#kontak"
                        class="rounded px-2 py-1 transition-colors hover:text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                    >
                        Kontak dan Lokasi
                    </a>
                </nav>

                <!-- Tombol Masuk / Daftar Desktop -->
                <div class="hidden items-center gap-3 md:flex">
                    <template v-if="props.canLogin">
                        <Link
                            href="/login"
                            class="inline-flex h-11 min-h-[44px] items-center justify-center rounded-[40.5px] border border-[#000000] bg-transparent px-5 text-sm font-medium text-[#000000] transition-colors hover:bg-[#fffff3] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        >
                            Masuk Akun
                        </Link>
                    </template>
                    <template v-if="props.canRegister">
                        <Link
                            href="/register"
                            class="inline-flex h-11 min-h-[44px] items-center justify-center rounded-[40.5px] bg-[#000000] px-6 text-sm font-medium text-white transition-colors hover:bg-[#1a1a1a] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        >
                            Daftar Pasien Baru
                        </Link>
                    </template>
                </div>

                <!-- Tombol Navigasi Seluler -->
                <div class="flex md:hidden">
                    <button
                        type="button"
                        @click="toggleMobileMenu"
                        class="inline-flex h-11 min-h-[44px] w-11 items-center justify-center rounded-[10px] border border-[#333333]/20 bg-[#fffff3] text-[#000000] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        :aria-expanded="isMobileMenuOpen"
                        aria-label="Buka menu navigasi"
                    >
                        <svg
                            v-if="!isMobileMenuOpen"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Drawer Menu Seluler -->
            <div
                v-if="isMobileMenuOpen"
                class="border-t border-[#333333]/15 bg-[#fffff3] px-4 py-5 md:hidden"
            >
                <nav
                    class="flex flex-col gap-2 text-base font-medium text-[#000000]"
                >
                    <a
                        href="#layanan"
                        @click="isMobileMenuOpen = false"
                        class="flex min-h-[44px] items-center rounded-lg px-3 hover:bg-[#edede2]"
                    >
                        Layanan Poliklinik
                    </a>
                    <a
                        href="#alur"
                        @click="isMobileMenuOpen = false"
                        class="flex min-h-[44px] items-center rounded-lg px-3 hover:bg-[#edede2]"
                    >
                        Alur Berobat
                    </a>
                    <a
                        href="#operasional"
                        @click="isMobileMenuOpen = false"
                        class="flex min-h-[44px] items-center rounded-lg px-3 hover:bg-[#edede2]"
                    >
                        Informasi Operasional
                    </a>
                    <a
                        href="#kontak"
                        @click="isMobileMenuOpen = false"
                        class="flex min-h-[44px] items-center rounded-lg px-3 hover:bg-[#edede2]"
                    >
                        Kontak dan Lokasi
                    </a>
                </nav>

                <div
                    class="mt-4 flex flex-col gap-2.5 border-t border-[#333333]/15 pt-4"
                >
                    <Link
                        v-if="props.canLogin"
                        href="/login"
                        class="flex h-11 min-h-[44px] w-full items-center justify-center rounded-[40.5px] border border-[#000000] bg-transparent text-sm font-medium text-[#000000]"
                    >
                        Masuk Akun Pasien
                    </Link>
                    <Link
                        v-if="props.canRegister"
                        href="/register"
                        class="flex h-11 min-h-[44px] w-full items-center justify-center rounded-[40.5px] bg-[#000000] text-sm font-medium text-white"
                    >
                        Daftar Pasien Baru
                    </Link>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section Editorial -->
            <section
                class="px-4 pt-12 pb-16 sm:px-6 sm:pt-16 sm:pb-20 lg:px-8 lg:pt-20 lg:pb-24"
            >
                <div class="mx-auto max-w-[1200px]">
                    <div class="mx-auto max-w-[760px] text-center">
                        <span
                            class="inline-flex items-center gap-2 rounded-[46px] border border-[#333333]/15 bg-[#fffff3] px-3.5 py-1 text-xs font-semibold text-[#000000]"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-[#beedc0]"
                            ></span>
                            Pusat Pendaftaran Mandiri Pasien Rawat Jalan
                        </span>

                        <h1
                            class="mt-6 font-['DM_Serif_Display'] text-4xl leading-[1.18] font-normal tracking-tight text-[#000000] sm:text-5xl lg:text-6xl"
                        >
                            Pelayanan Medis yang Tenang, Tertib, dan Terencana
                        </h1>

                        <p
                            class="mx-auto mt-6 max-w-[620px] text-base leading-[1.8] text-[#333333] sm:text-lg"
                        >
                            Daftarkan diri Anda ke klinik rawat jalan langsung
                            dari rumah. Pantau nomor antrean secara transparan
                            dan tiba di rumah sakit tepat pada jam periksa Anda.
                        </p>

                        <div
                            class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                        >
                            <Link
                                href="/register"
                                class="inline-flex h-12 min-h-[44px] w-full items-center justify-center rounded-[40.5px] bg-[#000000] px-8 text-base font-medium text-white transition-colors hover:bg-[#1a1a1a] focus:ring-2 focus:ring-[#000000] focus:outline-none sm:w-auto"
                            >
                                Daftar Berobat Sekarang
                            </Link>
                            <a
                                href="#layanan"
                                class="inline-flex h-12 min-h-[44px] w-full items-center justify-center rounded-[40.5px] border border-[#000000] bg-transparent px-7 text-base font-medium text-[#000000] transition-colors hover:bg-[#fffff3] focus:ring-2 focus:ring-[#000000] focus:outline-none sm:w-auto"
                            >
                                Lihat Jadwal Poliklinik
                            </a>
                        </div>
                    </div>

                    <!-- Kartu Panduan Cepat Kedatangan -->
                    <div
                        class="mt-14 rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 sm:p-8"
                    >
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div
                                class="space-y-1.5 border-b border-[#333333]/10 pb-4 md:border-r md:border-b-0 md:pr-6 md:pb-0"
                            >
                                <span
                                    class="inline-block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                                >
                                    Pendaftaran Cepat
                                </span>
                                <h2
                                    class="font-['DM_Serif_Display'] text-xl text-[#000000]"
                                >
                                    Nomor Antrean dari Rumah
                                </h2>
                                <p
                                    class="text-sm leading-relaxed text-[#333333]"
                                >
                                    Pilih klinik dan dokter tanpa perlu
                                    mengantre dari pagi di loket fisik rumah
                                    sakit.
                                </p>
                            </div>

                            <div
                                class="space-y-1.5 border-b border-[#333333]/10 pb-4 md:border-r md:border-b-0 md:pr-6 md:pb-0"
                            >
                                <span
                                    class="inline-block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                                >
                                    Pantauan Waktu
                                </span>
                                <h2
                                    class="font-['DM_Serif_Display'] text-xl text-[#000000]"
                                >
                                    Estimasi Jam Konsultasi
                                </h2>
                                <p
                                    class="text-sm leading-relaxed text-[#333333]"
                                >
                                    Sistem memperbarui urutan panggilan periksa
                                    sehingga waktu istirahat pasien terjaga.
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <span
                                    class="inline-block text-xs font-semibold tracking-wider text-[#333333] uppercase"
                                >
                                    Integrasi Resep
                                </span>
                                <h2
                                    class="font-['DM_Serif_Display'] text-xl text-[#000000]"
                                >
                                    Pengambilan Obat Terpadu
                                </h2>
                                <p
                                    class="text-sm leading-relaxed text-[#333333]"
                                >
                                    Resep dokter diteruskan langsung ke unit
                                    farmasi untuk memangkas waktu tunggu tebus
                                    obat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seksi Layanan Poliklinik -->
            <section
                id="layanan"
                class="border-t border-[#333333]/15 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"
            >
                <div class="mx-auto max-w-[1200px]">
                    <div
                        class="flex flex-col justify-between gap-4 md:flex-row md:items-end"
                    >
                        <div>
                            <span
                                class="text-xs font-semibold tracking-wider text-[#333333] uppercase"
                            >
                                Poliklinik Rawat Jalan
                            </span>
                            <h2
                                class="mt-2 font-['DM_Serif_Display'] text-3xl font-normal text-[#000000] sm:text-4xl"
                            >
                                Layanan Dokter Spesialis
                            </h2>
                            <p
                                class="mt-2 max-w-[620px] text-sm leading-relaxed text-[#333333] sm:text-base"
                            >
                                Jadwal praktik diperbarui setiap hari kerja
                                sesuai kehadiran dokter penanggung jawab
                                pelayanan.
                            </p>
                        </div>
                        <Link
                            href="/register"
                            class="inline-flex h-11 min-h-[44px] items-center text-sm font-semibold text-[#000000] underline hover:text-[#333333]"
                        >
                            Daftar ke Poli Pilihan Anda
                        </Link>
                    </div>

                    <!-- Grid Kartu Poliklinik -->
                    <div
                        class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <motion.article
                            v-for="poli in poliList"
                            :key="poli.id"
                            :initial="{ opacity: 0, y: 12 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :whileHover="{ y: -2 }"
                            :transition="{ duration: 0.2, ease: 'easeOut' }"
                            class="flex flex-col justify-between rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6"
                        >
                            <div>
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="rounded-[46px] border border-[#333333]/15 bg-[#edede2] px-3 py-0.5 text-xs font-medium text-[#000000]"
                                    >
                                        {{ poli.name }}
                                    </span>
                                    <span
                                        :class="
                                            poli.isOpenToday
                                                ? 'bg-[#beedc0] text-[#000000]'
                                                : 'bg-[#edede2] text-[#333333]'
                                        "
                                        class="rounded-[46px] px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{
                                            poli.isOpenToday
                                                ? 'Praktik Hari Ini'
                                                : 'Tutup'
                                        }}
                                    </span>
                                </div>

                                <h3
                                    class="mt-4 font-['DM_Serif_Display'] text-2xl font-normal text-[#000000]"
                                >
                                    {{ poli.name }}
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-relaxed text-[#333333]"
                                >
                                    {{ poli.description }}
                                </p>
                            </div>

                            <div class="mt-6 border-t border-[#333333]/10 pt-4">
                                <div class="text-xs text-[#333333]">
                                    {{ poli.scheduleNote }}
                                </div>
                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-medium text-[#000000]"
                                    >
                                        {{ poli.doctorCount }} Dokter Bertugas
                                    </span>
                                    <Link
                                        href="/register"
                                        class="inline-flex min-h-[44px] items-center rounded-lg px-2 text-xs font-semibold text-[#000000] underline hover:text-[#333333] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                                    >
                                        Pilih Jadwal
                                    </Link>
                                </div>
                            </div>
                        </motion.article>
                    </div>
                </div>
            </section>

            <!-- Seksi Alur Berobat Pasien -->
            <section
                id="alur"
                class="border-t border-[#333333]/15 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"
            >
                <div class="mx-auto max-w-[1200px]">
                    <div class="mx-auto max-w-[640px] text-center">
                        <span
                            class="text-xs font-semibold tracking-wider text-[#333333] uppercase"
                        >
                            Panduan Pasien
                        </span>
                        <h2
                            class="mt-2 font-['DM_Serif_Display'] text-3xl font-normal text-[#000000] sm:text-4xl"
                        >
                            Empat Langkah Sederhana Menuju Ruang Periksa
                        </h2>
                        <p class="mt-2 text-sm text-[#333333] sm:text-base">
                            Dirancang agar pasien dan keluarga dapat menjalani
                            pemeriksaan tanpa kebingungan alur di lokasi.
                        </p>
                    </div>

                    <div
                        class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="st in steps"
                            :key="st.number"
                            class="rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0] text-base font-bold text-[#000000]"
                            >
                                {{ st.number }}
                            </div>
                            <h3
                                class="mt-4 font-['DM_Serif_Display'] text-xl font-normal text-[#000000]"
                            >
                                {{ st.title }}
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-[#333333]"
                            >
                                {{ st.description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-10 text-center">
                        <Link
                            href="/register"
                            class="inline-flex h-12 min-h-[44px] items-center justify-center rounded-[40.5px] bg-[#000000] px-8 text-base font-medium text-white transition-colors hover:bg-[#1a1a1a] focus:ring-2 focus:ring-[#000000] focus:outline-none"
                        >
                            Mulai Pendaftaran Pasien
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Seksi Informasi Operasional Nyata -->
            <section
                id="operasional"
                class="border-t border-[#333333]/15 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"
            >
                <div class="mx-auto max-w-[1200px]">
                    <div
                        class="rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 sm:p-10"
                    >
                        <div
                            class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-12"
                        >
                            <div>
                                <span
                                    class="text-xs font-semibold tracking-wider text-[#333333] uppercase"
                                >
                                    Operasional dan Fasilitas
                                </span>
                                <h2
                                    class="mt-2 font-['DM_Serif_Display'] text-3xl font-normal text-[#000000]"
                                >
                                    Standar Pelayanan Rawat Jalan RS Harmoni
                                    Sehat
                                </h2>
                                <p
                                    class="mt-3 text-sm leading-relaxed text-[#333333]"
                                >
                                    Kami melayani pasien mandiri maupun peserta
                                    asuransi rekanan. Seluruh proses
                                    pendaftaran, rekam medis, dan pengambilan
                                    resep terhubung secara terpusat untuk
                                    meminimalisasi duplikasi berkas.
                                </p>

                                <div
                                    class="mt-6 space-y-3 text-sm text-[#000000]"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 h-2 w-2 rounded-full bg-[#000000]"
                                        ></div>
                                        <span
                                            >Loket Check-in Mandiri tersedia di
                                            lobi utama lantai 1.</span
                                        >
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 h-2 w-2 rounded-full bg-[#000000]"
                                        ></div>
                                        <span
                                            >Apotek Rawat Jalan beroperasi
                                            hingga pasien poliklinik terakhir
                                            selesai.</span
                                        >
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 h-2 w-2 rounded-full bg-[#000000]"
                                        ></div>
                                        <span
                                            >Laboratorium dan radiologi cito
                                            siaga 24 jam untuk pemeriksaan
                                            mendesak.</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-[10px] border border-[#333333]/15 bg-[#edede2] p-6"
                            >
                                <h3
                                    class="font-['DM_Serif_Display'] text-xl text-[#000000]"
                                >
                                    Jadwal Layanan Utama
                                </h3>
                                <dl class="mt-4 space-y-3 text-xs sm:text-sm">
                                    <div
                                        class="flex justify-between border-b border-[#333333]/15 pb-2"
                                    >
                                        <dt class="text-[#333333]">
                                            Instalasi Gawat Darurat (UGD)
                                        </dt>
                                        <dd
                                            class="font-semibold text-[#000000]"
                                        >
                                            24 Jam Non-Stop
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-[#333333]/15 pb-2"
                                    >
                                        <dt class="text-[#333333]">
                                            Poliklinik Spesialis (Senin - Jumat)
                                        </dt>
                                        <dd
                                            class="font-semibold text-[#000000]"
                                        >
                                            08.00 - 20.00 WIB
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-[#333333]/15 pb-2"
                                    >
                                        <dt class="text-[#333333]">
                                            Poliklinik Spesialis (Sabtu)
                                        </dt>
                                        <dd
                                            class="font-semibold text-[#000000]"
                                        >
                                            08.00 - 15.00 WIB
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-[#333333]/15 pb-2"
                                    >
                                        <dt class="text-[#333333]">
                                            Laboratorium Rutin
                                        </dt>
                                        <dd
                                            class="font-semibold text-[#000000]"
                                        >
                                            07.00 - 21.00 WIB
                                        </dd>
                                    </div>
                                    <div class="flex justify-between pt-1">
                                        <dt class="text-[#333333]">
                                            Farmasi Rawat Jalan
                                        </dt>
                                        <dd
                                            class="font-semibold text-[#000000]"
                                        >
                                            07.30 - 21.30 WIB
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer Kontak dan Legalitas -->
        <footer
            id="kontak"
            class="border-t border-[#333333]/15 bg-[#fffff3] px-4 py-12 sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto grid max-w-[1200px] grid-cols-1 gap-8 border-b border-[#333333]/15 pb-10 md:grid-cols-12"
            >
                <div class="space-y-3 md:col-span-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#000000] text-[#beedc0]"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </div>
                        <span
                            class="font-['DM_Serif_Display'] text-xl font-normal text-[#000000]"
                        >
                            RS Harmoni Sehat
                        </span>
                    </div>
                    <p class="max-w-sm text-sm leading-relaxed text-[#333333]">
                        Layanan kesehatan terpadu dan ramah untuk seluruh
                        anggota keluarga. Menjamin kepastian waktu dan
                        keteraturan jadwal dokter.
                    </p>
                    <div class="text-xs text-[#333333]">
                        Jalan Kesehatan Harmoni Nomor 88, Kota Surabaya, Jawa
                        Timur
                    </div>
                </div>

                <div class="space-y-2.5 md:col-span-3">
                    <h4
                        class="text-xs font-semibold tracking-wider text-[#000000] uppercase"
                    >
                        Navigasi Layanan
                    </h4>
                    <ul class="space-y-2 text-xs text-[#333333]">
                        <li>
                            <a
                                href="#layanan"
                                class="hover:text-[#000000] hover:underline"
                                >Jadwal Poliklinik</a
                            >
                        </li>
                        <li>
                            <a
                                href="#alur"
                                class="hover:text-[#000000] hover:underline"
                                >Alur Pendaftaran</a
                            >
                        </li>
                        <li>
                            <a
                                href="#operasional"
                                class="hover:text-[#000000] hover:underline"
                                >Jam Operasional</a
                            >
                        </li>
                        <li>
                            <Link
                                href="/login"
                                class="hover:text-[#000000] hover:underline"
                                >Portal Akun Pasien</Link
                            >
                        </li>
                    </ul>
                </div>

                <div class="space-y-2.5 md:col-span-4">
                    <h4
                        class="text-xs font-semibold tracking-wider text-[#000000] uppercase"
                    >
                        Pusat Bantuan Pasien
                    </h4>
                    <p class="text-xs text-[#333333]">
                        Untuk pertanyaan pendaftaran atau konfirmasi rujukan
                        dokter:
                    </p>
                    <div class="space-y-1.5 text-xs">
                        <div>
                            <span class="font-medium text-[#000000]"
                                >Telepon Informasi:</span
                            >
                            <span class="ml-1.5 text-[#333333]"
                                >(031) 555-0199</span
                            >
                        </div>
                        <div>
                            <span class="font-medium text-[#000000]"
                                >Panggilan Gawat Darurat:</span
                            >
                            <a
                                href="tel:1500911"
                                class="ml-1.5 font-bold text-[#000000] underline"
                                >1500-911</a
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mx-auto flex max-w-[1200px] flex-col items-center justify-between gap-3 pt-6 text-xs text-[#333333] sm:flex-row"
            >
                <p>
                    &copy; {{ new Date().getFullYear() }} RS Harmoni Sehat.
                    Seluruh hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        @click="showPrivacyNotice = true"
                        class="cursor-pointer hover:text-[#000000] hover:underline focus:outline-none"
                    >
                        Kebijakan Privasi Rekam Medis
                    </button>
                    <button
                        type="button"
                        @click="showTermsNotice = true"
                        class="cursor-pointer hover:text-[#000000] hover:underline focus:outline-none"
                    >
                        Ketentuan Layanan
                    </button>
                </div>
            </div>
        </footer>

        <!-- Modal Informasi Kebijakan Privasi Rekam Medis -->
        <div
            v-if="showPrivacyNotice"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/40 p-4 backdrop-blur-xs"
            @click.self="showPrivacyNotice = false"
            @keydown.escape="showPrivacyNotice = false"
            tabindex="0"
        >
            <div
                class="w-full max-w-md rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000]"
            >
                <h3 class="font-['DM_Serif_Display'] text-xl">
                    Kebijakan Privasi Rekam Medis Pasien
                </h3>
                <p class="mt-3 text-xs leading-relaxed text-[#333333]">
                    Seluruh data pendaftaran, rekam medis elektronik, dan
                    diagnosis pasien di RS Harmoni Sehat dilindungi sesuai
                    dengan Undang-Undang Kesehatan dan standar kerahasiaan medis
                    yang berlaku. Data hanya dapat diakses oleh tenaga medis
                    yang bersangkutan dan pasien secara langsung.
                </p>
                <div class="mt-6 text-right">
                    <button
                        type="button"
                        @click="showPrivacyNotice = false"
                        class="inline-flex h-11 min-h-[44px] items-center justify-center rounded-[40.5px] bg-[#000000] px-6 text-xs font-semibold text-white hover:bg-[#1a1a1a]"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Ketentuan Layanan Pasien -->
        <div
            v-if="showTermsNotice"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#000000]/40 p-4 backdrop-blur-xs"
            @click.self="showTermsNotice = false"
            @keydown.escape="showTermsNotice = false"
            tabindex="0"
        >
            <div
                class="w-full max-w-md rounded-[10px] border border-[#333333]/15 bg-[#fffff3] p-6 text-[#000000]"
            >
                <h3 class="font-['DM_Serif_Display'] text-xl">
                    Ketentuan Layanan dan Pendaftaran
                </h3>
                <p class="mt-3 text-xs leading-relaxed text-[#333333]">
                    Nomor antrean digital berlaku pada tanggal dan sesi poli
                    yang dipilih. Pasien diharapkan tiba di lokasi
                    sekurang-kurangnya 15 menit sebelum estimasi waktu panggilan
                    untuk konfirmasi identitas dan verifikasi berkas jaminan
                    kesehatan.
                </p>
                <div class="mt-6 text-right">
                    <button
                        type="button"
                        @click="showTermsNotice = false"
                        class="inline-flex h-11 min-h-[44px] items-center justify-center rounded-[40.5px] bg-[#000000] px-6 text-xs font-semibold text-white hover:bg-[#1a1a1a]"
                    >
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
