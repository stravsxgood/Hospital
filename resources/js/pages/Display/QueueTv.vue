<script setup lang="ts">
/**
 * @file QueueTv.vue
 * @description Layar TV Display Antrean Publik Rumah Sakit (Full-Screen Live Synchronized Queue).
 *
 * Menggunakan Laravel Echo WebSockets (Laravel Reverb) di channel 'queue-display'
 * untuk sinkronisasi zero-latency saat dokter memanggil nomor antrean di ruang periksa.
 * Dilengkapi Web Speech API (TTS bahasa Indonesia id-ID) dan audio chime 4-nada rumah sakit.
 *
 * Desain Sistem Evergreen:
 * - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald (#065f46), Ink Black (#000000).
 * - Typography: IvyPresto Headline serif + Rubik sans.
 * - Animasi Motion-V & Target Sentuh ramah kiosk/layar sentuh >= 44px.
 */
import { Head } from '@inertiajs/vue3';
import {
    Activity,
    Bell,
    CheckCircle2,
    Clock,
    Maximize2,
    Minimize2,
    Radio,
    Sparkles,
    Stethoscope,
    Volume2,
    VolumeX,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import echo from '@/echo';
import {
    announceHospitalQueue,
    buildHospitalAnnouncementText,
    getAudioContext,
    playOpeningChime,
} from '@/lib/queueAudio';

interface CallingPayload {
    appointment_id: number;
    queue_number: string;
    patient_name: string;
    poli_name: string;
    room_name: string;
    doctor_name: string;
    voice_text: string;
    called_at: string;
}

interface ClinicItem {
    schedule_id: number;
    doctor_name: string;
    poli_name: string;
    room_name: string;
    current_calling: string | null;
    patient_name: string | null;
    next_calling: string;
    waiting_count: number;
}

const props = defineProps<{
    initialData?: {
        clinics: ClinicItem[];
        latestCalled: CallingPayload | null;
    };
    currentDate?: string;
}>();

const activeCall = ref<CallingPayload | null>(
    props.initialData?.latestCalled || null,
);
const clinics = ref<ClinicItem[]>(props.initialData?.clinics || []);
const isAudioEnabled = ref(false);
const isFullscreen = ref(false);
const liveClock = ref('');
const isSpeaking = ref(false);
const callHistory = ref<CallingPayload[]>([]);

let clockTimer: any = null;

// Text-to-Speech Engine (Web Speech API with Indonesian Voice & Hospital Chimes)
const speakVoiceText = (text: string) => {
    if (!isAudioEnabled.value) {
        return;
    }

    announceHospitalQueue({
        text,
        rate: 0.8, // Laju lambat, artikulatif, dan tenang khas rumah sakit
        onStart: () => {
            isSpeaking.value = true;
        },
        onEnd: () => {
            isSpeaking.value = false;
        },
        onError: () => {
            isSpeaking.value = false;
        },
    });
};

// Toggle Audio Enable
const toggleAudio = () => {
    isAudioEnabled.value = !isAudioEnabled.value;

    if (isAudioEnabled.value) {
        getAudioContext();
        playOpeningChime();
    }
};

// Toggle Fullscreen
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        document.exitFullscreen().catch(() => {});
        isFullscreen.value = false;
    }
};

// Live Digital Clock
const updateClock = () => {
    const now = new Date();
    liveClock.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

// Handle WebSocket Event
const handleIncomingCall = (payload: CallingPayload) => {
    activeCall.value = payload;

    // Prepend to history
    callHistory.value = [
        payload,
        ...callHistory.value.filter(
            (c) => c.appointment_id !== payload.appointment_id,
        ),
    ].slice(0, 6);

    // Update clinic list active calling if matches
    const targetClinic = clinics.value.find(
        (c) =>
            c.poli_name === payload.poli_name ||
            c.room_name === payload.room_name,
    );

    if (targetClinic) {
        targetClinic.current_calling = payload.queue_number;
        targetClinic.patient_name = payload.patient_name;
    }

    // Voice announcement dengan pemformatan nomor antrean lambat & jelas
    const announcementText = buildHospitalAnnouncementText({
        queueNumber: payload.queue_number,
        patientName: payload.patient_name,
        poliName: payload.poli_name,
        roomName: payload.room_name,
        showPatientName: true,
    });

    speakVoiceText(announcementText);
};

onMounted(() => {
    updateClock();
    clockTimer = setInterval(updateClock, 1000);

    // Inisialisasi daftar suara TTS
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
    }

    // Hubungkan Laravel Echo ke channel public 'queue-display'
    try {
        echo.channel('queue-display')
            .listen('.PatientCalledEvent', (event: CallingPayload) => {
                handleIncomingCall(event);
            })
            .listen('PatientCalledEvent', (event: CallingPayload) => {
                handleIncomingCall(event);
            });
    } catch (e) {
        console.warn('Echo channel subscribe warning:', e);
    }
});

onBeforeUnmount(() => {
    if (clockTimer) {
        clearInterval(clockTimer);
    }

    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }

    try {
        echo.leaveChannel('queue-display');
    } catch (e) {}
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col justify-between bg-[#edede2] font-sans text-[#000000] antialiased select-none"
    >
        <Head title="Display TV Antrean Publik SIMRS" />

        <!-- Header Display TV -->
        <header
            class="flex items-center justify-between border-b border-[#000000]/10 bg-[#fffff3] px-8 py-5 shadow-sm"
        >
            <div class="flex items-center space-x-5">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#065f46]/30 bg-[#beedc0] shadow-inner"
                >
                    <AppLogoIcon class="h-8 w-8 text-[#065f46]" />
                </div>
                <div>
                    <h1
                        class="font-serif text-2xl font-bold tracking-tight text-[#000000] lg:text-3xl"
                    >
                        HOSPITAL POPULATION
                    </h1>
                    <p
                        class="flex items-center gap-2 text-sm font-medium text-[#065f46]"
                    >
                        <span
                            class="h-2.5 w-2.5 animate-pulse rounded-full bg-[#065f46]"
                        ></span>
                        Sistem Informasi Layar Panggilan Antrean Terpadu (Live
                        Real-Time)
                    </p>
                </div>
            </div>

            <!-- Jam, Tanggal, & Kontrol Display -->
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <div
                        class="font-mono text-3xl font-bold tracking-wider text-[#065f46]"
                    >
                        {{ liveClock }}
                    </div>
                    <div
                        class="text-xs font-semibold tracking-widest text-[#000000]/60 uppercase"
                    >
                        {{ currentDate || 'Hari Ini' }}
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <motion.button
                        type="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="toggleAudio"
                        class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-medium transition-colors"
                        :class="
                            isAudioEnabled
                                ? 'border-[#065f46]/30 bg-[#beedc0] text-[#065f46] shadow-sm'
                                : 'border-[#000000]/15 bg-[#fffff3] text-[#000000]/70 hover:bg-[#edede2]'
                        "
                    >
                        <Volume2
                            v-if="isAudioEnabled"
                            class="h-5 w-5 text-[#065f46]"
                        />
                        <VolumeX v-else class="h-5 w-5 text-[#000000]/50" />
                        <span>{{
                            isAudioEnabled ? 'Suara Aktif' : 'Aktifkan Suara'
                        }}</span>
                    </motion.button>

                    <motion.button
                        type="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="toggleFullscreen"
                        class="min-h-[44px] cursor-pointer rounded-xl border border-[#000000]/15 bg-[#fffff3] p-2.5 text-[#000000]/70 transition-colors hover:bg-[#edede2]"
                        title="Mode Layar Penuh"
                    >
                        <Minimize2 v-if="isFullscreen" class="h-5 w-5" />
                        <Maximize2 v-else class="h-5 w-5" />
                    </motion.button>
                </div>
            </div>
        </header>

        <!-- Main Display Content -->
        <main
            class="grid flex-1 grid-cols-1 items-stretch gap-8 p-6 lg:grid-cols-12 lg:p-8"
        >
            <!-- Left Banner: Active Calling Spotlight (7 Cols) -->
            <section class="flex flex-col lg:col-span-7">
                <motion.div
                    :initial="{ opacity: 0, scale: 0.98 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.3 }"
                    class="relative flex flex-1 flex-col justify-between overflow-hidden rounded-3xl border-2 border-[#065f46] bg-[#fffff3] p-8 shadow-xl lg:p-10"
                >
                    <!-- Background Accent Glow -->
                    <div
                        class="pointer-events-none absolute -top-20 -right-20 h-80 w-80 rounded-full bg-[#beedc0]/30 blur-3xl"
                    ></div>

                    <!-- Header Spotlight -->
                    <div
                        class="flex items-center justify-between border-b border-[#000000]/10 pb-6"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="relative flex h-4 w-4">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#065f46] opacity-75"
                                ></span>
                                <span
                                    class="relative inline-flex h-4 w-4 rounded-full bg-[#065f46]"
                                ></span>
                            </span>
                            <span
                                class="text-sm font-bold tracking-widest text-[#065f46] uppercase lg:text-base"
                            >
                                Sedang Dipanggil Saat Ini
                            </span>
                        </div>

                        <div
                            v-if="isSpeaking"
                            class="flex items-center gap-2 rounded-full border border-[#065f46]/20 bg-[#beedc0] px-3 py-1.5 text-xs font-semibold text-[#065f46]"
                        >
                            <Volume2 class="h-4 w-4 animate-bounce" />
                            <span>Pengumuman Suara...</span>
                        </div>
                    </div>

                    <!-- Main Number Presentation -->
                    <div class="my-auto py-8 text-center" v-if="activeCall">
                        <div
                            class="mb-2 text-sm font-semibold tracking-wider text-[#000000]/60 uppercase"
                        >
                            Nomor Antrean Pasien
                        </div>
                        <div
                            class="font-mono text-7xl font-black tracking-tight text-[#065f46] drop-shadow-sm lg:text-9xl"
                        >
                            {{ activeCall.queue_number }}
                        </div>
                        <div
                            class="mt-4 font-serif text-2xl font-bold text-[#000000] lg:text-3xl"
                        >
                            {{ activeCall.patient_name }}
                        </div>
                    </div>

                    <!-- Empty State saat belum ada panggilan -->
                    <div class="my-auto py-12 text-center" v-else>
                        <Bell
                            class="mx-auto mb-4 h-16 w-16 animate-pulse text-[#000000]/20"
                        />
                        <div
                            class="font-serif text-xl font-bold text-[#000000]/70"
                        >
                            Menunggu Pemanggilan Antrean Berikutnya
                        </div>
                        <p class="mt-1 text-sm text-[#000000]/50">
                            Nomor antrean yang dipanggil dokter akan tampil di
                            sini secara real-time.
                        </p>
                    </div>

                    <!-- Destination Footer -->
                    <div
                        class="flex items-center justify-between rounded-2xl border border-[#000000]/10 bg-[#edede2] p-6"
                        v-if="activeCall"
                    >
                        <div>
                            <div
                                class="text-xs font-semibold text-[#000000]/60 uppercase"
                            >
                                Tujuan Poliklinik & Ruang
                            </div>
                            <div
                                class="text-xl font-bold text-[#000000] lg:text-2xl"
                            >
                                {{ activeCall.poli_name }}
                                <span class="text-[#065f46]"
                                    >({{ activeCall.room_name }})</span
                                >
                            </div>
                        </div>
                        <div class="text-right">
                            <div
                                class="text-xs font-semibold text-[#000000]/60 uppercase"
                            >
                                Dokter Pemeriksa
                            </div>
                            <div
                                class="text-base font-semibold text-[#065f46] lg:text-lg"
                            >
                                {{ activeCall.doctor_name }}
                            </div>
                        </div>
                    </div>
                </motion.div>
            </section>

            <!-- Right Panel: Matrix Poliklinik & Riwayat Panggilan (5 Cols) -->
            <section class="flex flex-col gap-6 lg:col-span-5">
                <!-- Riwayat Panggilan Terkini -->
                <div
                    class="flex flex-1 flex-col rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 font-serif text-base font-bold tracking-wide text-[#000000] uppercase"
                    >
                        <Clock class="h-5 w-5 text-[#065f46]" />
                        Riwayat Panggilan Terakhir
                    </h3>

                    <div
                        class="flex-1 space-y-3 overflow-y-auto pr-1"
                        v-if="callHistory.length > 0"
                    >
                        <div
                            v-for="(hist, idx) in callHistory"
                            :key="hist.appointment_id + '-' + idx"
                            class="flex items-center justify-between rounded-xl border border-[#000000]/5 bg-[#edede2]/60 p-3.5"
                        >
                            <div class="flex items-center space-x-3">
                                <span
                                    class="rounded-lg bg-[#beedc0] px-2.5 py-1 font-mono text-lg font-bold text-[#065f46]"
                                >
                                    {{ hist.queue_number }}
                                </span>
                                <div>
                                    <div
                                        class="max-w-[160px] truncate text-sm font-bold text-[#000000]"
                                    >
                                        {{ hist.patient_name }}
                                    </div>
                                    <div class="text-xs text-[#000000]/60">
                                        {{ hist.poli_name }} ·
                                        {{ hist.room_name }}
                                    </div>
                                </div>
                            </div>
                            <span
                                class="font-mono text-xs font-medium text-[#000000]/50"
                            >
                                {{
                                    new Date(hist.called_at).toLocaleTimeString(
                                        'id-ID',
                                        { hour: '2-digit', minute: '2-digit' },
                                    )
                                }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex flex-1 flex-col items-center justify-center p-6 text-center text-[#000000]/40"
                        v-else
                    >
                        <Clock class="mb-2 h-10 w-10 opacity-50" />
                        <span class="text-sm"
                            >Belum ada riwayat panggilan sesi ini</span
                        >
                    </div>
                </div>

                <!-- Informasi Pelayanan RS -->
                <div
                    class="rounded-3xl border border-[#065f46] bg-[#065f46] p-6 text-[#fffff3] shadow-md"
                >
                    <div class="mb-2 flex items-center space-x-3">
                        <Stethoscope class="h-6 w-6 text-[#beedc0]" />
                        <span class="font-serif text-base font-bold"
                            >Tata Tertib Ruang Tunggu</span
                        >
                    </div>
                    <p class="text-xs leading-relaxed text-[#fffff3]/80">
                        Harap perhatikan nomor antrean di layar. Saat nomor Anda
                        dipanggil, mohon segera menuju ruang poli yang tertera
                        dengan membawa kartu berobat / identitas pasien.
                    </p>
                </div>
            </section>
        </main>

        <!-- Footer Running Text / Ticker -->
        <footer
            class="flex items-center overflow-hidden border-t border-[#000000] bg-[#000000] px-6 py-3.5 text-[#fffff3]"
        >
            <div
                class="mr-6 flex items-center gap-2 text-xs font-bold tracking-widest whitespace-nowrap text-[#beedc0] uppercase"
            >
                <Sparkles class="h-4 w-4 text-[#beedc0]" />
                INFORMASI SIMRS:
            </div>
            <div class="relative flex-1 overflow-hidden">
                <div
                    class="animate-marquee text-sm font-medium whitespace-nowrap text-[#fffff3]/90"
                >
                    Selamat datang di Rumah Sakit Hospital Population. Pelayanan
                    rawat jalan beroperasi setiap Senin s/d Sabtu. Pendaftaran
                    online dan informasi jadwal dokter dapat diakses melalui
                    portal pasien. Jagalah kebersihan dan ketenangan di
                    lingkungan rumah sakit.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes marquee {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}

.animate-marquee {
    display: inline-block;
    animation: marquee 25s linear infinite;
}
</style>
