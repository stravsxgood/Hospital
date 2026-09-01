<script setup lang="ts">
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

// GANTI DENGAN URL DEPLOYMENT GOOGLE APPS SCRIPT ANDA
const GOOGLE_SCRIPT_TTS_URL =
    'https://script.google.com/macros/s/AKfycbyOSe0ooXdH3GTtUePmbj2leqcRYmuVDLMj7aX8pXd1QldUwCJg2WPv-_ofDoPWyqmk/exec';

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

interface LatestCalled {
    appointment_id: number;
    queue_number: string;
    patient_name: string;
    poli_name: string;
    room_name: string;
    doctor_name: string;
    updated_at: string;
}

interface DisplayPayload {
    clinics: ClinicItem[];
    latestCalled: LatestCalled | null;
}

interface DisplayConfig {
    hospital_name?: string;
    scroll_speed?: number;
    show_patient_name?: boolean;
    theme?: string;
    announcement_text?: string;
}

const props = defineProps<{
    initialData: DisplayPayload;
    currentDate: string;
    displayConfig?: DisplayConfig;
}>();

const displayData = ref<DisplayPayload>(props.initialData);
const isAudioEnabled = ref(false);
const isAutoRepeat = ref(true);
const isFullscreen = ref(false);
const liveClock = ref('');
const lastAnnouncedKey = ref<string>('');
const isSpeaking = ref(false);

let pollingTimer: any = null;
let clockTimer: any = null;
let repeatTimeoutTimer: any = null;
let audioCtx: AudioContext | null = null;
let currentAudio: HTMLAudioElement | null = null;

const formatDoctorName = (name?: string | null): string => {
    if (!name) {
        return 'Dokter Spesialis';
    }

    const trimmed = name.trim();

    if (/^(dr\.|drg\.|dr\s|drg\s|prof\.|prof\s)/i.test(trimmed)) {
        return trimmed;
    }

    return `dr. ${trimmed}`;
};

// Inisialisasi AudioContext & Unlock Audio Browser
const getAudioContext = (): AudioContext | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        if (!audioCtx) {
            const AudioContextClass =
                window.AudioContext || (window as any).webkitAudioContext;

            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }

        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        return audioCtx;
    } catch (e) {
        console.warn('Audio Context initialization error:', e);

        return null;
    }
};

// Pembaruan Jam Digital
const updateClock = () => {
    const now = new Date();
    liveClock.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

// 1. Suara Bel "Hospital Chime" (Web Audio API)
const playHospitalChime = () => {
    try {
        const ctx = getAudioContext();

        if (!ctx) {
            return;
        }

        const playTone = (
            freq: number,
            start: number,
            duration: number,
            gainValue = 0.25,
        ) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime + start);

            gain.gain.setValueAtTime(gainValue, ctx.currentTime + start);
            gain.gain.exponentialRampToValueAtTime(
                0.0001,
                ctx.currentTime + start + duration,
            );

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime + start);
            osc.stop(ctx.currentTime + start + duration);
        };

        // Nada Chime 4-Nada Khas Rumah Sakit (C5 -> E5 -> G5 -> C6)
        playTone(523.25, 0.0, 0.5, 0.25);
        playTone(659.25, 0.2, 0.5, 0.25);
        playTone(783.99, 0.4, 0.6, 0.3);
        playTone(1046.5, 0.65, 0.8, 0.35);
    } catch (e) {
        console.warn('Audio chime playback error:', e);
    }
};

// 2. Pemanggilan Suara Kalimat Utuh via Google Apps Script (TTS Audio Stream)
const announceQueue = async (item: LatestCalled, repeatCount = 1) => {
    if (!isAudioEnabled.value || typeof window === 'undefined') {
        return;
    }

    if (repeatTimeoutTimer) {
        clearTimeout(repeatTimeoutTimer);
        repeatTimeoutTimer = null;
    }

    if (currentAudio) {
        currentAudio.pause();
        currentAudio.currentTime = 0;
        currentAudio = null;
    }

    isSpeaking.value = true;

    // Bunyikan bel terlebih dahulu
    playHospitalChime();

    // Format kalimat dengan jeda alami
    const cleanQueue = item.queue_number.replace(/-/g, ' ');
    const patientNamePart = item.patient_name
        ? `, atas nama ${item.patient_name}`
        : '';
    const speechText = `Nomor antrean, ${cleanQueue}${patientNamePart}. Silakan menuju ke ${item.poli_name}, ${item.room_name}.`;

    // Tunggu nada bel selesai berbunyi (1.3 detik)
    setTimeout(async () => {
        try {
            const response = await fetch(
                `${GOOGLE_SCRIPT_TTS_URL}?text=${encodeURIComponent(speechText)}&lang=id`,
            );
            const base64Audio = await response.text();

            if (base64Audio.startsWith('Error')) {
                throw new Error(base64Audio);
            }

            currentAudio = new Audio(`data:audio/mp3;base64,${base64Audio}`);

            currentAudio.onended = () => {
                isSpeaking.value = false;
                currentAudio = null;

                // Ulangi panggilan 1x lagi jika auto-repeat aktif
                if (isAutoRepeat.value && repeatCount === 1) {
                    repeatTimeoutTimer = setTimeout(() => {
                        if (
                            displayData.value.latestCalled?.appointment_id ===
                            item.appointment_id
                        ) {
                            announceQueue(item, 2);
                        }
                    }, 3000);
                }
            };

            currentAudio.onerror = (err) => {
                console.error('Audio playback error:', err);
                isSpeaking.value = false;
                currentAudio = null;
            };

            await currentAudio.play();
        } catch (err) {
            console.error('Gagal memproses panggilan suara:', err);
            isSpeaking.value = false;
        }
    }, 1300);
};

// 3. Polling Data Real-Time (Setiap 2.5 Detik)
const fetchLiveData = async () => {
    try {
        const res = await fetch('/display/live-data');

        if (!res.ok) {
            return;
        }

        const data: DisplayPayload = await res.json();
        displayData.value = data;

        if (data.latestCalled) {
            const currentKey = `${data.latestCalled.appointment_id}_${data.latestCalled.updated_at}`;

            if (currentKey !== lastAnnouncedKey.value) {
                lastAnnouncedKey.value = currentKey;

                if (isAudioEnabled.value) {
                    announceQueue(data.latestCalled, 1);
                }
            }
        }
    } catch (error) {
        console.error('Gagal mengambil pembaruan antrean:', error);
    }
};

// Aktifkan Audio & Buka Kunci AudioContext Browser
const enableAudio = () => {
    isAudioEnabled.value = true;
    localStorage.setItem('display_audio_enabled', 'true');
    getAudioContext();

    if (displayData.value.latestCalled) {
        const currentKey = `${displayData.value.latestCalled.appointment_id}_${displayData.value.latestCalled.updated_at}`;
        lastAnnouncedKey.value = currentKey;
        announceQueue(displayData.value.latestCalled, 1);
    }
};

const toggleAudio = () => {
    if (!isAudioEnabled.value) {
        enableAudio();
    } else {
        isAudioEnabled.value = false;
        localStorage.setItem('display_audio_enabled', 'false');

        if (repeatTimeoutTimer) {
            clearTimeout(repeatTimeoutTimer);
            repeatTimeoutTimer = null;
        }

        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
        }
    }
};

const toggleAutoRepeat = () => {
    isAutoRepeat.value = !isAutoRepeat.value;
    localStorage.setItem(
        'display_auto_repeat',
        isAutoRepeat.value ? 'true' : 'false',
    );
};

// Uji Coba Suara Manual (Test Audio)
const testAudio = () => {
    getAudioContext();
    isAudioEnabled.value = true;
    const testItem: LatestCalled = displayData.value.latestCalled || {
        appointment_id: 0,
        queue_number: 'A-001',
        patient_name: 'Pasien Percobaan',
        poli_name: 'Poliklinik Utama',
        room_name: 'Ruang Periksa 1',
        doctor_name: 'Dokter Spesialis',
        updated_at: new Date().toISOString(),
    };
    announceQueue(testItem, 1);
};

// Toggle Mode Fullscreen
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }

        isFullscreen.value = false;
    }
};

onMounted(() => {
    updateClock();

    const savedAudio = localStorage.getItem('display_audio_enabled');

    if (savedAudio === 'true') {
        isAudioEnabled.value = true;
    }

    const savedAutoRepeat = localStorage.getItem('display_auto_repeat');

    if (savedAutoRepeat !== null) {
        isAutoRepeat.value = savedAutoRepeat === 'true';
    }

    if (props.initialData.latestCalled) {
        lastAnnouncedKey.value = `${props.initialData.latestCalled.appointment_id}_${props.initialData.latestCalled.updated_at}`;
    }

    const handleGlobalClick = () => {
        getAudioContext();
    };
    window.addEventListener('click', handleGlobalClick, { once: true });

    clockTimer = setInterval(updateClock, 1000);
    pollingTimer = setInterval(fetchLiveData, 4000);

    // Langganan instan ke Laravel Echo WebSocket channel 'queue-display'
    try {
        import('@/echo').then(({ echo }) => {
            echo.channel('queue-display')
                .listen('.PatientCalledEvent', (event: any) => {
                    const item: LatestCalled = {
                        appointment_id: event.appointment_id,
                        queue_number: event.queue_number,
                        patient_name: event.patient_name,
                        poli_name: event.poli_name,
                        room_name: event.room_name,
                        doctor_name: event.doctor_name,
                        updated_at: event.called_at,
                    };
                    displayData.value.latestCalled = item;
                    announceQueue(item, 1);
                })
                .listen('PatientCalledEvent', (event: any) => {
                    const item: LatestCalled = {
                        appointment_id: event.appointment_id,
                        queue_number: event.queue_number,
                        patient_name: event.patient_name,
                        poli_name: event.poli_name,
                        room_name: event.room_name,
                        doctor_name: event.doctor_name,
                        updated_at: event.called_at,
                    };
                    displayData.value.latestCalled = item;
                    announceQueue(item, 1);
                });
        });
    } catch (e) {
        console.warn('Echo subscribe warning:', e);
    }
});

onBeforeUnmount(() => {
    if (clockTimer) {
        clearInterval(clockTimer);
    }

    if (pollingTimer) {
        clearInterval(pollingTimer);
    }

    if (repeatTimeoutTimer) {
        clearTimeout(repeatTimeoutTimer);
    }

    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    if (audioCtx) {
        audioCtx.close().catch(() => {});
    }

    try {
        import('@/echo').then(({ echo }) => {
            echo.leaveChannel('queue-display');
        });
    } catch (e) {}
});
</script>

<template>
    <Head title="Monitor Antrean Ruang Tunggu - Hospital Population" />

    <div
        class="min-h-screen w-full space-y-4 bg-[#edede2] p-4 font-['Rubik'] text-[#000000] sm:p-6 lg:p-8"
    >
        <!-- Banner Interaktif untuk Mengaktifkan Suara Panggilan Otomatis -->
        <motion.div
            v-if="!isAudioEnabled"
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            @click="enableAudio"
            class="flex cursor-pointer flex-col justify-between gap-3 rounded-[12px] border-2 border-[#beedc0] bg-[#000000] p-4 text-white shadow-lg transition-colors hover:bg-[#1a1a1a] sm:flex-row sm:items-center"
        >
            <div class="flex items-center gap-3.5">
                <div
                    class="flex h-11 w-11 shrink-0 animate-pulse items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                >
                    <Volume2 class="size-6 text-[#000000]" />
                </div>
                <div>
                    <h4
                        class="flex items-center gap-2 text-sm font-bold text-[#beedc0]"
                    >
                        <span>Aktifkan Suara Panggilan Antrean</span>
                        <span
                            class="inline-flex items-center rounded-full bg-[#beedc0] px-2 py-0.5 text-[10px] font-extrabold text-[#000000]"
                        >
                            PENTING
                        </span>
                    </h4>
                    <p class="mt-0.5 text-xs text-white/80">
                        Klik di sini agar suara bel dan pengumuman nomor antrean
                        pasien berbunyi otomatis dari monitor TV.
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click.stop="enableAudio"
                class="inline-flex min-h-[40px] shrink-0 items-center justify-center gap-2 rounded-[40.5px] bg-[#beedc0] px-5 py-2 text-xs font-bold text-[#000000] shadow-sm transition-colors hover:bg-[#a6e5a8]"
            >
                <Volume2 class="size-4" />
                <span>Aktifkan Suara Sekarang</span>
            </button>
        </motion.div>

        <!-- Header Monitor -->
        <motion.header
            :initial="{ opacity: 0, y: -12 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.22, ease: 'easeOut' }"
            class="flex flex-col justify-between gap-4 border-b border-[#333333]/15 pb-4 md:flex-row md:items-center"
        >
            <motion.div
                :initial="{ opacity: 0, x: -12 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut' }"
                class="flex items-center gap-3.5"
            >
                <motion.div
                    :whileHover="{ scale: 1.05 }"
                    :whileTap="{ scale: 0.95 }"
                    class="flex h-13 w-13 shrink-0 items-center justify-center rounded-full bg-[#beedc0] shadow-sm"
                >
                    <AppLogoIcon class="size-7 fill-current text-[#000000]" />
                </motion.div>
                <div>
                    <h1
                        class="font-['ivypresto-headline'] text-2xl leading-none font-bold tracking-tight text-[#000000] sm:text-3xl"
                    >
                        {{
                            displayConfig?.hospital_name ||
                            'Hospital Population'
                        }}
                    </h1>
                    <span
                        class="mt-1 block text-xs font-medium tracking-wide text-[#333333] sm:text-sm"
                    >
                        Papan Informasi Antrean Layar Utama
                    </span>
                </div>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, x: 12 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.05 }"
                class="flex flex-wrap items-center gap-3"
            >
                <!-- Tombol Ulangi 2x Otomatis -->
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.03 }"
                    :whileTap="{ scale: 0.97 }"
                    @click="toggleAutoRepeat"
                    :class="[
                        'inline-flex min-h-[40px] cursor-pointer items-center gap-1.5 rounded-full border px-3.5 text-xs font-semibold shadow-xs transition-all',
                        isAutoRepeat
                            ? 'border-[#333333]/30 bg-[#beedc0] text-[#000000]'
                            : 'border-[#333333]/20 bg-[#ffffff] text-[#333333] hover:bg-[#edede2]',
                    ]"
                    title="Panggil ulang 2 kali otomatis agar pasien mendengar dengan jelas"
                >
                    <span
                        >Ulangi Panggilan 2x:
                        <strong>{{ isAutoRepeat ? 'ON' : 'OFF' }}</strong></span
                    >
                </motion.button>

                <!-- Tombol Test Audio -->
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.03 }"
                    :whileTap="{ scale: 0.97 }"
                    @click="testAudio"
                    class="inline-flex min-h-[40px] cursor-pointer items-center gap-1.5 rounded-full border border-[#333333]/20 bg-[#ffffff] px-3.5 text-xs font-semibold text-[#333333] transition-all hover:bg-[#edede2]"
                    title="Uji coba suara bel & pengumuman"
                >
                    <Bell class="size-3.5 text-[#000000]" />
                    <span>Uji Suara</span>
                </motion.button>

                <!-- Status Audio Toggle -->
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.03 }"
                    :whileTap="{ scale: 0.97 }"
                    @click="toggleAudio"
                    :class="[
                        'inline-flex min-h-[40px] cursor-pointer items-center gap-2 rounded-full border px-4 text-xs font-bold shadow-xs transition-all',
                        isAudioEnabled
                            ? 'border-[#333333]/30 bg-[#beedc0] text-[#000000]'
                            : 'border-[#333333]/20 bg-[#fffff3] text-[#333333] hover:bg-[#fffff3]/80',
                    ]"
                >
                    <component
                        :is="isAudioEnabled ? Volume2 : VolumeX"
                        class="size-4"
                    />
                    <span>{{
                        isAudioEnabled ? 'Suara Aktif' : 'Suara Senyap'
                    }}</span>
                </motion.button>

                <!-- Tombol Fullscreen -->
                <motion.button
                    type="button"
                    :whileHover="{ scale: 1.05 }"
                    :whileTap="{ scale: 0.95 }"
                    @click="toggleFullscreen"
                    class="flex h-[40px] w-[40px] cursor-pointer items-center justify-center rounded-full border border-[#333333]/20 bg-[#fffff3] text-[#000000] transition-colors hover:bg-[#fffff3]/80"
                    title="Fullscreen Mode"
                >
                    <component
                        :is="isFullscreen ? Minimize2 : Maximize2"
                        class="size-4"
                    />
                </motion.button>

                <!-- Jam Digital & Tanggal -->
                <div class="border-l border-[#333333]/20 pl-3 text-right">
                    <span
                        class="block font-mono text-2xl leading-tight font-extrabold tracking-wider text-[#000000] sm:text-3xl"
                    >
                        {{ liveClock }}
                    </span>
                    <span class="block text-xs font-medium text-[#333333]">
                        {{ currentDate }}
                    </span>
                </div>
            </motion.div>
        </motion.header>

        <!-- Main Monitor Display Grid -->
        <motion.main
            :initial="{ opacity: 0, y: 15 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.22, delay: 0.1, ease: 'easeOut' }"
            class="my-4 grid flex-1 grid-cols-1 items-stretch gap-6 lg:grid-cols-12"
        >
            <!-- Sisi Kiri: Kartu Sorotan Utama (Pasien yang Sedang Dipanggil) -->
            <motion.div
                :initial="{ opacity: 0, x: -15 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.22, delay: 0.15, ease: 'easeOut' }"
                class="flex flex-col lg:col-span-5"
            >
                <div
                    class="flex flex-1 flex-col justify-between rounded-[16px] border border-[#333333] bg-[#000000] p-6 text-white shadow-lg sm:p-8"
                >
                    <div
                        class="flex items-center justify-between border-b border-white/15 pb-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-3.5 w-3.5 animate-ping rounded-full bg-emerald-400"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-widest text-[#beedc0] uppercase"
                                >Sedang Dipanggil</span
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="isSpeaking"
                                class="inline-flex animate-pulse items-center gap-1 rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[11px] font-bold text-[#000000]"
                            >
                                <Volume2 class="size-3" />
                                Memanggil...
                            </span>
                            <span v-else class="font-mono text-xs text-white/70"
                                >Panggilan Terkini</span
                            >
                        </div>
                    </div>

                    <!-- Nomor Panggilan Besar -->
                    <div
                        v-if="displayData.latestCalled"
                        class="my-auto space-y-3 py-8 text-center"
                    >
                        <span
                            class="block text-xs font-semibold tracking-wider text-white/60 uppercase"
                            >Nomor Antrean</span
                        >
                        <span
                            class="block animate-pulse font-mono text-6xl font-black tracking-tighter text-[#beedc0] sm:text-7xl lg:text-8xl"
                        >
                            {{ displayData.latestCalled.queue_number }}
                        </span>
                        <div class="space-y-1 pt-3">
                            <h2
                                class="font-['ivypresto-headline'] text-2xl font-bold text-white sm:text-3xl"
                            >
                                {{ displayData.latestCalled.poli_name }}
                            </h2>
                            <p class="text-lg font-semibold text-white/90">
                                {{ displayData.latestCalled.patient_name }}
                            </p>
                            <span
                                class="block text-sm font-medium text-white/70"
                            >
                                {{ displayData.latestCalled.room_name }} &bull;
                                {{
                                    formatDoctorName(
                                        displayData.latestCalled.doctor_name,
                                    )
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Keadaan Saat Belum Ada Pasien Yang Dipanggil -->
                    <div v-else class="my-auto space-y-2 py-12 text-center">
                        <Bell class="mx-auto size-12 text-white/30" />
                        <p class="text-lg font-medium text-white/70">
                            Menunggu Panggilan Dokter...
                        </p>
                        <p class="text-xs text-white/40">
                            Nomor pasien yang dipanggil oleh dokter akan tampil
                            otomatis di layar ini.
                        </p>
                    </div>

                    <!-- Petunjuk Bawah -->
                    <div
                        class="rounded-[8px] border border-white/10 bg-white/10 p-3.5 text-center text-xs text-white/80"
                    >
                        Harap memperhatikan nomor antrean dan langsung menuju ke
                        poliklinik tujuan saat nomor Anda dipanggil.
                    </div>
                </div>
            </motion.div>

            <!-- Sisi Kanan: Matriks Status Seluruh Poliklinik -->
            <motion.div
                :initial="{ opacity: 0, x: 15 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.22, delay: 0.2, ease: 'easeOut' }"
                class="flex flex-col lg:col-span-7"
            >
                <div
                    class="flex flex-1 flex-col justify-between rounded-[16px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-[#333333]/10 pb-4"
                    >
                        <div class="flex items-center gap-2">
                            <Radio class="size-4 text-[#000000]" />
                            <h3
                                class="font-['ivypresto-headline'] text-xl font-bold text-[#000000]"
                            >
                                Matriks Poliklinik & Ruang Periksa
                            </h3>
                        </div>
                        <span
                            class="rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            {{ displayData.clinics.length }} Poli Aktif
                        </span>
                    </div>

                    <!-- Grid List Ruang Poli -->
                    <div
                        v-if="displayData.clinics.length > 0"
                        class="grid flex-1 grid-cols-1 gap-3.5 sm:grid-cols-2"
                    >
                        <motion.div
                            v-for="clinic in displayData.clinics"
                            :key="clinic.schedule_id"
                            :initial="{ opacity: 0, y: 12 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :whileHover="{ scale: 1.015, y: -2 }"
                            :transition="{ duration: 0.2, ease: 'easeOut' }"
                            class="flex flex-col justify-between space-y-3 rounded-[12px] border border-[#333333]/10 bg-[#edede2]/60 p-4"
                            :class="
                                clinic.current_calling
                                    ? 'ring-1.5 bg-[#ffffff] ring-[#000000]'
                                    : ''
                            "
                        >
                            <div
                                class="flex items-start justify-between gap-2 border-b border-[#333333]/10 pb-2"
                            >
                                <div>
                                    <span
                                        class="block text-xs font-extrabold tracking-wide text-[#000000] uppercase"
                                    >
                                        {{ clinic.poli_name }}
                                    </span>
                                    <span
                                        class="block truncate text-[11px] text-[#333333]"
                                    >
                                        {{
                                            formatDoctorName(clinic.doctor_name)
                                        }}
                                    </span>
                                </div>
                                <span
                                    class="shrink-0 rounded-md bg-[#edede2] px-2 py-0.5 text-[11px] font-semibold text-[#333333]/70"
                                >
                                    {{ clinic.room_name }}
                                </span>
                            </div>

                            <!-- Baris Nomor Antrean Poli -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <span
                                        class="block text-[10px] font-bold text-[#333333]/60 uppercase"
                                        >Nomor Dipanggil</span
                                    >
                                    <span
                                        v-if="clinic.current_calling"
                                        class="block font-mono text-3xl font-black text-[#000000]"
                                    >
                                        {{ clinic.current_calling }}
                                    </span>
                                    <span
                                        v-else
                                        class="mt-1 block text-sm font-semibold text-[#333333]/50 italic"
                                    >
                                        Menunggu
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span
                                        class="block text-[10px] font-bold text-[#333333]/60 uppercase"
                                        >Antrean Berikutnya</span
                                    >
                                    <span
                                        class="block font-mono text-base font-bold text-[#333333]"
                                    >
                                        {{ clinic.next_calling }}
                                    </span>
                                    <span
                                        class="mt-0.5 block text-[10px] text-[#333333]/70"
                                    >
                                        {{ clinic.waiting_count }} Antre
                                    </span>
                                </div>
                            </div>
                        </motion.div>
                    </div>

                    <div
                        v-else
                        class="py-12 text-center text-xs text-[#333333]"
                    >
                        Tidak ada poliklinik yang melayani pemeriksaan saat ini.
                    </div>
                </div>
            </motion.div>
        </motion.main>

        <!-- Running Text / Footer Marquee -->
        <motion.footer
            :initial="{ opacity: 0, y: 10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.22, delay: 0.25, ease: 'easeOut' }"
            class="flex items-center gap-4 overflow-hidden rounded-[10px] border border-[#333333]/15 bg-[#fffff3] px-5 py-3 shadow-xs"
        >
            <span
                class="shrink-0 rounded-full bg-[#beedc0] px-3 py-1 text-xs font-extrabold tracking-wider text-[#000000] uppercase"
            >
                Informasi
            </span>
            <div class="truncate text-xs font-medium text-[#333333]">
                Selamat datang di Hospital Population. Mohon menjaga ketenangan
                di ruang tunggu. Jika nomor antrean Anda terlewat, silakan
                hubungi petugas loket pendaftaran.
            </div>
        </motion.footer>
    </div>
</template>
