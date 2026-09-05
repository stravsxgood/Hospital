<script setup lang="ts">
/**
 * @file Index.vue (Public Queue TV Display & Dynamic Auto-Looping Video Player)
 * @description Layar Monitor TV Ruang Tunggu Publik Rumah Sakit Terintegrasi.
 *              Menampilkan matriks antrean poliklinik real-time, panggilan nomor antrean besar,
 *              dan pemutar playlist video edukasi/promosi SIMRS secara otomatis dan berurutan.
 *
 * Standar Desain Sistem Evergreen:
 * - Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Deep Emerald, Ink Black (#000000).
 * - Typography: IvyPresto Headline serif + Rubik sans.
 * - Motion-V untuk micro-interactions.
 */
import { Head } from '@inertiajs/vue3';
import {
    Activity,
    Bell,
    CheckCircle2,
    Clock,
    Film,
    Maximize2,
    Minimize2,
    Play,
    Radio,
    Sparkles,
    Stethoscope,
    Tv,
    UserCheck,
    Users,
    Volume2,
    VolumeX,
} from '@lucide/vue';
import { motion } from 'motion-v';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    announceHospitalQueue,
    buildHospitalAnnouncementText,
    getAudioContext,
    playClosingChime,
    playOpeningChime,
} from '@/lib/queueAudio';

interface DisplayVideoItem {
    id: number;
    title: string;
    file_path: string;
    order: number;
    is_active: boolean;
    youtube_id?: string | null;
    embed_url?: string;
    video_url: string;
    thumbnail_url?: string;
    file_size_formatted?: string;
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
    videos?: DisplayVideoItem[];
    displayConfig?: DisplayConfig;
}>();

// ═══════════════════════════════════════════════════════════════
// State Manajemen Data Antrean & Clock
// ═══════════════════════════════════════════════════════════════
const displayData = ref<DisplayPayload>(props.initialData);
const isAudioEnabled = ref(false);
const isFullscreen = ref(false);
const liveClock = ref('');
const isSpeaking = ref(false);

let pollingTimer: any = null;
let clockTimer: any = null;

// ═══════════════════════════════════════════════════════════════
// State Video Playlist Player Auto-Looping (YouTube & Local)
// ═══════════════════════════════════════════════════════════════
// State Video Playlist Player Auto-Looping (YouTube & Local)
// ═══════════════════════════════════════════════════════════════
const videoList = ref<DisplayVideoItem[]>(props.videos || []);
const currentVideoIndex = ref(0);
const isVideoMuted = ref(true);
const videoPlayerRef = ref<HTMLVideoElement | null>(null);
const isVideoPlaying = ref(false);
const videoHasError = ref(false);
let ytPlayer: any = null;

const activeVideos = computed(() => {
    return videoList.value.filter((v) => v.is_active);
});

const currentVideo = computed(() => {
    if (activeVideos.value.length === 0) {
        return null;
    }

    const index = Math.min(
        currentVideoIndex.value,
        activeVideos.value.length - 1,
    );

    return activeVideos.value[index] || null;
});

// Penanganan inisialisasi YouTube Iframe Player API
const loadYouTubeIframeApi = () => {
    if (typeof window === 'undefined') {
        return;
    }

    if ((window as any).YT && (window as any).YT.Player) {
        initYouTubePlayer();

        return;
    }

    if (!document.getElementById('yt-iframe-api-script')) {
        const tag = document.createElement('script');
        tag.id = 'yt-iframe-api-script';
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag?.parentNode?.insertBefore(tag, firstScriptTag);
    }

    const previousOnReady = (window as any).onYouTubeIframeAPIReady;
    (window as any).onYouTubeIframeAPIReady = () => {
        if (typeof previousOnReady === 'function') {
            previousOnReady();
        }

        initYouTubePlayer();
    };
};

const initYouTubePlayer = () => {
    if (
        typeof window === 'undefined' ||
        !(window as any).YT ||
        !(window as any).YT.Player
    ) {
        return;
    }

    if (!currentVideo.value?.youtube_id) {
        return;
    }

    const container = document.getElementById('yt-player-target');

    if (!container) {
        return;
    }

    if (ytPlayer && typeof ytPlayer.destroy === 'function') {
        try {
            ytPlayer.destroy();
        } catch {}

        ytPlayer = null;
    }

    try {
        ytPlayer = new (window as any).YT.Player('yt-player-target', {
            videoId: currentVideo.value.youtube_id,
            playerVars: {
                autoplay: 1,
                mute: isVideoMuted.value ? 1 : 0,
                controls: 0,
                disablekb: 1,
                fs: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                playsinline: 1,
                rel: 0,
                enablejsapi: 1,
                origin: window.location.origin,
            },
            events: {
                onReady: (event: any) => {
                    try {
                        if (isVideoMuted.value) {
                            event.target.mute();
                        } else {
                            event.target.unMute();
                            event.target.setVolume(isSpeaking.value ? 10 : 100);
                        }

                        event.target.playVideo();
                    } catch {}
                },
                onStateChange: (event: any) => {
                    // YT.PlayerState.ENDED === 0: Otomatis berganti ke video berikutnya saat video selesai
                    if (event.data === 0) {
                        handleVideoEnded();
                    }
                },
                onError: () => {
                    handleVideoError();
                },
            },
        });
    } catch {
        // Abaikan kegagalan inisialisasi sementara
    }
};

// Penanganan transisi ke video berikutnya saat durasi video selesai (Auto-Cycle)
const handleVideoEnded = () => {
    if (activeVideos.value.length === 0) {
        return;
    }

    if (activeVideos.value.length === 1) {
        // Jika hanya ada 1 video, ulangi putar kembali dari detik awal
        if (
            currentVideo.value?.youtube_id &&
            ytPlayer &&
            typeof ytPlayer.seekTo === 'function'
        ) {
            try {
                ytPlayer.seekTo(0);
                ytPlayer.playVideo();
            } catch {}
        } else if (videoPlayerRef.value) {
            videoPlayerRef.value.currentTime = 0;
            videoPlayerRef.value.play().catch(() => {});
        }

        return;
    }

    // Jika terdapat lebih dari 1 video, ganti indeks ke video selanjutnya (looping melingkar)
    currentVideoIndex.value =
        (currentVideoIndex.value + 1) % activeVideos.value.length;
    playCurrentVideo();
};

// Fungsi pemutar video aktif saat ini
const playCurrentVideo = () => {
    nextTick(() => {
        if (currentVideo.value?.youtube_id) {
            if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                try {
                    ytPlayer.loadVideoById(currentVideo.value.youtube_id);

                    if (isVideoMuted.value) {
                        ytPlayer.mute();
                    } else {
                        ytPlayer.unMute();
                        ytPlayer.setVolume(isSpeaking.value ? 10 : 100);
                    }

                    ytPlayer.playVideo();
                } catch {
                    initYouTubePlayer();
                }
            } else {
                initYouTubePlayer();
            }
        } else if (videoPlayerRef.value) {
            videoPlayerRef.value.load();
            videoPlayerRef.value.play().catch(() => {});
        }
    });
};

// Listener pesan postMessage YouTube iframe sebagai jaring pengaman tambahan
const handleWindowMessage = (event: MessageEvent) => {
    try {
        if (typeof event.data === 'string') {
            const data = JSON.parse(event.data);

            if (data.event === 'onStateChange' && data.info === 0) {
                handleVideoEnded();
            }
        }
    } catch {
        // Abaikan data non-JSON
    }
};

// ═══════════════════════════════════════════════════════════════
// Audio Ducking: Mengecilkan volume video saat pemanggilan suara aktif
// ═══════════════════════════════════════════════════════════════
const duckVideoAudio = () => {
    // 1. Duck HTML5 Video lokal ke volume 10%
    if (videoPlayerRef.value && !isVideoMuted.value) {
        videoPlayerRef.value.volume = 0.1;
    }

    // 2. Duck YouTube API Player ke volume 10%
    if (
        ytPlayer &&
        typeof ytPlayer.setVolume === 'function' &&
        !isVideoMuted.value
    ) {
        try {
            ytPlayer.setVolume(10);
        } catch {}
    }
};

const restoreVideoAudio = () => {
    // 1. Pulihkan volume HTML5 Video lokal ke 100%
    if (videoPlayerRef.value && !isVideoMuted.value) {
        videoPlayerRef.value.volume = 1.0;
    }

    // 2. Pulihkan volume YouTube API Player ke 100%
    if (
        ytPlayer &&
        typeof ytPlayer.setVolume === 'function' &&
        !isVideoMuted.value
    ) {
        try {
            ytPlayer.setVolume(100);
        } catch {}
    }
};

// Toggle audio video mandiri untuk petugas klinik
const toggleVideoSound = () => {
    isVideoMuted.value = !isVideoMuted.value;

    if (videoPlayerRef.value) {
        videoPlayerRef.value.muted = isVideoMuted.value;
        videoPlayerRef.value.volume = isSpeaking.value ? 0.1 : 1.0;
    }

    if (ytPlayer) {
        try {
            if (isVideoMuted.value) {
                ytPlayer.mute();
            } else {
                ytPlayer.unMute();
                ytPlayer.setVolume(isSpeaking.value ? 10 : 100);
            }
        } catch {}
    }
};

const handleVideoError = () => {
    videoHasError.value = true;

    // Coba lompat ke video berikutnya jika terjadi corrupt/error load agar layar TV tidak macet
    if (activeVideos.value.length > 1) {
        setTimeout(() => {
            currentVideoIndex.value =
                (currentVideoIndex.value + 1) % activeVideos.value.length;
            videoHasError.value = false;
            playCurrentVideo();
        }, 2500);
    }
};

watch(currentVideoIndex, () => {
    videoHasError.value = false;
    playCurrentVideo();
});

// ═══════════════════════════════════════════════════════════════
// Audio Pemanggilan Antrean (Opening Chime, TTS Lambat, & Closing Chime)
// ═══════════════════════════════════════════════════════════════
const executeQueueAnnouncement = (latestCalled: LatestCalled) => {
    if (!isAudioEnabled.value) {
        return;
    }

    const announcementText = buildHospitalAnnouncementText({
        queueNumber: latestCalled.queue_number,
        patientName: latestCalled.patient_name,
        poliName: latestCalled.poli_name,
        roomName: latestCalled.room_name,
        showPatientName: props.displayConfig?.show_patient_name !== false,
    });

    announceHospitalQueue({
        text: announcementText,
        rate: 0.80, // Laju agak lambat, tenang, dan artikulatif khas rumah sakit
        onStart: () => {
            isSpeaking.value = true;
            duckVideoAudio();
        },
        onEnd: () => {
            isSpeaking.value = false;
            setTimeout(() => {
                if (!isSpeaking.value) {
                    restoreVideoAudio();
                }
            }, 600);
        },
        onError: () => {
            isSpeaking.value = false;
            setTimeout(restoreVideoAudio, 500);
        },
    });
};

// ═══════════════════════════════════════════════════════════════
// Live Polling Matriks Antrean
// ═══════════════════════════════════════════════════════════════
const fetchLiveData = async () => {
    try {
        const res = await fetch('/display/live-data');

        if (!res.ok) {
            return;
        }

        const data: DisplayPayload = await res.json();

        // Deteksi pemanggilan baru
        if (
            data.latestCalled &&
            data.latestCalled.queue_number !==
                displayData.value.latestCalled?.queue_number
        ) {
            displayData.value = data;
            executeQueueAnnouncement(data.latestCalled);
        } else {
            displayData.value = data;
        }
    } catch {
        // Abaikan kegagalan jaringan sementara
    }
};

const toggleAudio = () => {
    getAudioContext();
    isAudioEnabled.value = !isAudioEnabled.value;

    if (isAudioEnabled.value) {
        playOpeningChime();
    }
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        document.exitFullscreen().catch(() => {});
        isFullscreen.value = false;
    }
};

const updateClock = () => {
    const now = new Date();
    liveClock.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
};

onMounted(() => {
    updateClock();
    clockTimer = setInterval(updateClock, 1000);
    pollingTimer = setInterval(fetchLiveData, 4000);

    window.addEventListener('message', handleWindowMessage);

    if (currentVideo.value?.youtube_id) {
        loadYouTubeIframeApi();
    }

    // Auto-play video lokal saat mounted
    nextTick(() => {
        if (videoPlayerRef.value) {
            videoPlayerRef.value.play().catch(() => {});
        }
    });
});

onBeforeUnmount(() => {
    if (clockTimer) {
        clearInterval(clockTimer);
    }

    if (pollingTimer) {
        clearInterval(pollingTimer);
    }

    window.removeEventListener('message', handleWindowMessage);

    if (ytPlayer && typeof ytPlayer.destroy === 'function') {
        try {
            ytPlayer.destroy();
        } catch {}

        ytPlayer = null;
    }

    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }
});
</script>

<template>
    <Head
        :title="`Layar Monitor Antrean & Video Display TV - ${displayConfig?.hospital_name || 'Hospital Population'}`"
    />

    <div
        class="flex min-h-screen flex-col justify-between overflow-x-hidden bg-[#edede2] p-4 text-[#000000] selection:bg-[#beedc0] sm:p-6 lg:p-8"
    >
        <!-- ═══════════════════════════════════════════════════════════════
             1. Header Layar Monitor TV
             ═══════════════════════════════════════════════════════════════ -->
        <motion.header
            :initial="{ opacity: 0, y: -15 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.22, ease: 'easeOut' }"
            class="flex flex-col gap-4 rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-4 shadow-none sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-4"
        >
            <div class="flex items-center gap-3.5">
                <div
                    class="flex size-11 items-center justify-center rounded-2xl bg-[#000000] text-[#ffffff] shadow-xs"
                >
                    <AppLogoIcon class="size-6 text-[#beedc0]" />
                </div>
                <div>
                    <h1
                        class="font-['ivypresto-headline'] text-xl font-bold tracking-tight text-[#000000] sm:text-2xl"
                    >
                        {{
                            displayConfig?.hospital_name ||
                            'Hospital Population'
                        }}
                    </h1>
                    <div
                        class="flex items-center gap-2 font-['Rubik'] text-xs text-[#333333]"
                    >
                        <span class="inline-flex items-center gap-1">
                            <span
                                class="size-2 animate-pulse rounded-full bg-emerald-500"
                            ></span>
                            <span>Layar Display TV Antrean & Edukasi</span>
                        </span>
                        <span>&bull;</span>
                        <span>Sistem Antrean Poliklinik Terpadu</span>
                    </div>
                </div>
            </div>

            <!-- Kontrol Audio, Fullscreen, & Jam Digital -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Audio Panggilan Suara -->
                <button
                    type="button"
                    @click="toggleAudio"
                    :class="[
                        'inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-[40.5px] border px-4 text-xs font-semibold shadow-none transition-all',
                        isAudioEnabled
                            ? 'border-[#000000] bg-[#beedc0] text-[#000000]'
                            : 'border-[#000000]/15 bg-[#fffff3] text-[#000000] hover:bg-[#edede2]',
                    ]"
                    :title="
                        isAudioEnabled
                            ? 'Panggilan suara antrean aktif'
                            : 'Panggilan suara dinonaktifkan'
                    "
                >
                    <component
                        :is="isAudioEnabled ? Volume2 : VolumeX"
                        class="size-4"
                    />
                    <span>{{
                        isAudioEnabled
                            ? 'Suara Panggilan Aktif'
                            : 'Suara Senyap'
                    }}</span>
                </button>

                <!-- Tombol Fullscreen -->
                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="flex size-11 cursor-pointer items-center justify-center rounded-[40.5px] border border-[#000000]/15 bg-[#fffff3] text-[#000000] transition-colors hover:bg-[#edede2]"
                    title="Layar Penuh (Fullscreen)"
                >
                    <component
                        :is="isFullscreen ? Minimize2 : Maximize2"
                        class="size-4"
                    />
                </button>

                <!-- Jam Digital & Tanggal -->
                <div class="border-l border-[#000000]/15 pl-3 text-right">
                    <span
                        class="block font-mono text-2xl leading-none font-extrabold tracking-wider text-[#000000] sm:text-3xl"
                    >
                        {{ liveClock }}
                    </span>
                    <span
                        class="mt-1 block text-[11px] font-medium text-[#333333]"
                    >
                        {{ currentDate }}
                    </span>
                </div>
            </div>
        </motion.header>

        <!-- ═══════════════════════════════════════════════════════════════
             2. Konten Utama: Split Grid Video & Antrean Real-time
             ═══════════════════════════════════════════════════════════════ -->
        <main class="my-4 grid flex-1 grid-cols-1 gap-5 lg:grid-cols-12">
            <!-- ─────────────────────────────────────────────────────────
                 KOLOM KIRI (lg:col-span-7): Video Player Playlist + Panggilan Besar
                 ───────────────────────────────────────────────────────── -->
            <div class="flex flex-col gap-5 lg:col-span-7">
                <!-- Video Player Container -->
                <motion.div
                    :initial="{ opacity: 0, scale: 0.98 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.25, ease: 'easeOut' }"
                    class="relative flex flex-col overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#000000] shadow-xl"
                >
                    <!-- Video Frame -->
                    <div
                        class="relative aspect-video w-full overflow-hidden bg-neutral-950"
                    >
                        <!-- YouTube Player Container (YouTube API Player) -->
                        <div
                            v-if="currentVideo && currentVideo.youtube_id"
                            class="h-full w-full bg-[#000000] [&>iframe]:h-full [&>iframe]:w-full [&>iframe]:border-0"
                        >
                            <div
                                id="yt-player-target"
                                class="h-full w-full"
                            ></div>
                        </div>

                        <!-- Local Video Element (Fallback) -->
                        <video
                            v-else-if="currentVideo"
                            ref="videoPlayerRef"
                            :key="`local-${currentVideo.id}`"
                            :src="currentVideo.video_url"
                            autoplay
                            :muted="isVideoMuted"
                            playsinline
                            @ended="handleVideoEnded"
                            @error="handleVideoError"
                            class="h-full w-full object-cover"
                        ></video>

                        <!-- Fallback Ketika Tidak Ada Video Aktif -->
                        <div
                            v-else
                            class="flex h-full w-full flex-col items-center justify-center space-y-3 p-8 text-center text-[#ffffff]"
                        >
                            <div
                                class="flex size-16 items-center justify-center rounded-full bg-[#beedc0]/20 text-[#beedc0]"
                            >
                                <Tv class="size-8" />
                            </div>
                            <h3
                                class="font-['ivypresto-headline'] text-2xl font-bold text-[#ffffff]"
                            >
                                Layar Edukasi & Informasi Rumah Sakit
                            </h3>
                            <p
                                class="max-w-md font-['Rubik'] text-xs text-[#ffffff]/70"
                            >
                                Menampilkan informasi kesehatan terpercaya dan
                                panduan layanan medis poliklinik untuk
                                kenyamanan pasien dan keluarga.
                            </p>
                        </div>

                        <!-- Badge Overlay Video Title & Audio Control -->
                        <div
                            v-if="currentVideo"
                            class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-[#000000]/90 via-[#000000]/50 to-transparent p-4 text-[#ffffff]"
                        >
                            <div class="flex items-center gap-2.5 truncate">
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#beedc0] text-[#000000]"
                                >
                                    <Film class="size-3" />
                                </span>
                                <div class="truncate">
                                    <h4
                                        class="truncate font-['Rubik'] text-xs font-bold text-[#ffffff] sm:text-sm"
                                    >
                                        {{ currentVideo.title }}
                                    </h4>
                                    <span class="text-[10px] text-[#beedc0]">
                                        Video {{ currentVideoIndex + 1 }} dari
                                        {{ activeVideos.length }}
                                    </span>
                                </div>
                            </div>

                            <!-- Tombol Audio Video Toggle -->
                            <button
                                type="button"
                                @click="toggleVideoSound"
                                class="inline-flex min-h-[38px] cursor-pointer items-center gap-1.5 rounded-[40.5px] border border-[#ffffff]/20 bg-[#000000]/60 px-3 py-1 text-xs font-medium text-[#ffffff] backdrop-blur-xs transition-colors hover:bg-[#000000]"
                                :title="
                                    isVideoMuted
                                        ? 'Aktifkan suara video'
                                        : 'Senyapkan suara video'
                                "
                            >
                                <component
                                    :is="isVideoMuted ? VolumeX : Volume2"
                                    class="size-3.5"
                                    :class="
                                        isVideoMuted
                                            ? 'text-[#ffffff]/70'
                                            : 'text-[#beedc0]'
                                    "
                                />
                                <span class="hidden sm:inline">{{
                                    isVideoMuted ? 'Audio Bisu' : 'Audio Aktif'
                                }}</span>
                            </button>
                        </div>
                    </div>
                </motion.div>

                <!-- Kartu Nomor Antrean Besar yang Sedang Dipanggil -->
                <motion.div
                    :initial="{ opacity: 0, y: 12 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{
                        duration: 0.22,
                        delay: 0.1,
                        ease: 'easeOut',
                    }"
                    class="flex flex-1 flex-col justify-between rounded-3xl border border-[#000000]/15 bg-[#000000] p-6 text-[#ffffff] shadow-xl sm:p-7"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#ffffff]/15 pb-3.5"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="size-3 animate-ping rounded-full bg-[#beedc0]"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-widest text-[#beedc0] uppercase"
                            >
                                Sedang Dipanggil Dokter
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="isSpeaking"
                                class="inline-flex animate-pulse items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-bold text-[#000000]"
                            >
                                <Volume2 class="size-3.5" />
                                <span>Memanggil Suara...</span>
                            </span>
                            <span
                                v-else
                                class="font-mono text-xs text-[#ffffff]/60"
                            >
                                Real-Time Call
                            </span>
                        </div>
                    </div>

                    <!-- Display Nomor Panggilan Terkini -->
                    <div
                        v-if="displayData.latestCalled"
                        class="my-auto space-y-2 py-4 text-center"
                    >
                        <span
                            class="block text-xs font-semibold tracking-wider text-[#ffffff]/60 uppercase"
                        >
                            Nomor Antrean Pasien
                        </span>
                        <span
                            class="block animate-pulse font-mono text-6xl font-black tracking-tighter text-[#beedc0] sm:text-7xl lg:text-8xl"
                        >
                            {{ displayData.latestCalled.queue_number }}
                        </span>
                        <div class="space-y-1 pt-2">
                            <h2
                                class="font-['ivypresto-headline'] text-2xl font-bold text-[#ffffff] sm:text-3xl"
                            >
                                {{ displayData.latestCalled.poli_name }} &bull;
                                {{ displayData.latestCalled.room_name }}
                            </h2>
                            <p
                                class="text-base font-semibold text-[#ffffff]/90 sm:text-lg"
                            >
                                {{ displayData.latestCalled.patient_name }}
                            </p>
                            <span
                                class="block text-xs font-medium text-[#beedc0]"
                            >
                                DPJP: {{ displayData.latestCalled.doctor_name }}
                            </span>
                        </div>
                    </div>

                    <!-- Keadaan Saat Belum Ada Pasien Yang Dipanggil -->
                    <div v-else class="my-auto space-y-3 py-10 text-center">
                        <div
                            class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-[#ffffff]/10 text-[#beedc0]"
                        >
                            <Bell class="size-8 animate-bounce" />
                        </div>
                        <div class="space-y-1">
                            <h3
                                class="font-['ivypresto-headline'] text-2xl font-bold text-[#ffffff] sm:text-3xl"
                            >
                                Menunggu Panggilan Dokter
                            </h3>
                            <p
                                class="mx-auto max-w-sm font-['Rubik'] text-xs text-[#ffffff]/70"
                            >
                                Nomor antrean dan ruang periksa akan otomatis
                                terpampang dan dipanggil melalui suara saat
                                dokter memanggil pasien.
                            </p>
                        </div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-[#beedc0]/30 bg-[#beedc0]/10 px-4 py-1.5 text-xs font-semibold text-[#beedc0]"
                        >
                            <Activity class="size-3.5 animate-pulse" />
                            <span>Sistem Pemanggilan Antrean Terpadu</span>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-[#ffffff]/10 bg-[#ffffff]/5 p-3 text-center font-['Rubik'] text-xs text-[#ffffff]/80"
                    >
                        Silakan mempersiapkan berkas pendaftaran dan langsung
                        menuju ke ruangan saat nomor Anda dipanggil.
                    </div>
                </motion.div>
            </div>

            <!-- ─────────────────────────────────────────────────────────
                 KOLOM KANAN (lg:col-span-5): Matriks Ruang Poliklinik & Dokter Jaga
                 ───────────────────────────────────────────────────────── -->
            <motion.div
                :initial="{ opacity: 0, x: 15 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.22, delay: 0.15, ease: 'easeOut' }"
                class="flex h-full max-h-[85vh] min-h-[600px] flex-col overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#fffff3] p-5 shadow-none sm:p-6 lg:col-span-5"
            >
                <!-- Header Matriks Poliklinik -->
                <div
                    class="mb-4 flex shrink-0 items-center justify-between border-b border-[#000000]/10 pb-3.5"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-[#000000] text-[#beedc0] shadow-xs"
                        >
                            <Radio class="size-4.5" />
                        </div>
                        <div>
                            <h3
                                class="font-['ivypresto-headline'] text-lg leading-tight font-bold text-[#000000] sm:text-xl"
                            >
                                Status Ruang Poliklinik
                            </h3>
                            <p
                                class="mt-0.5 font-['Rubik'] text-[11px] text-[#333333]"
                            >
                                Antrean pemeriksaan dokter secara langsung
                            </p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#000000]/15 bg-[#edede2] px-3 py-1 text-xs font-bold text-[#000000]"
                    >
                        <span
                            class="size-2 animate-pulse rounded-full bg-emerald-500"
                        ></span>
                        <span>{{ displayData.clinics.length }} Poli Aktif</span>
                    </span>
                </div>

                <!-- List Matriks Poliklinik -->
                <div
                    v-if="displayData.clinics.length > 0"
                    class="flex-1 space-y-3 overflow-y-auto pr-1.5"
                >
                    <motion.div
                        v-for="clinic in displayData.clinics"
                        :key="clinic.schedule_id"
                        :initial="{ opacity: 0, y: 8 }"
                        :animate="{ opacity: 1, y: 0 }"
                        class="flex flex-col justify-between rounded-2xl border bg-[#ffffff] shadow-xs transition-all"
                        :class="[
                            clinic.current_calling
                                ? 'border-l-4 border-t-[#000000]/10 border-r-[#000000]/10 border-b-[#000000]/10 border-l-emerald-500 ring-1 ring-emerald-500/20'
                                : clinic.waiting_count > 0
                                  ? 'border-l-4 border-t-[#000000]/10 border-r-[#000000]/10 border-b-[#000000]/10 border-l-amber-400'
                                  : 'border-l-4 border-t-[#000000]/10 border-r-[#000000]/10 border-b-[#000000]/10 border-l-neutral-300',
                        ]"
                    >
                        <div class="space-y-2.5 p-3.5 sm:p-4">
                            <!-- Baris Utama: Detail Poli vs Box Nomor Antrean -->
                            <div class="flex items-start justify-between gap-3">
                                <!-- Info Kiri: Poliklinik, DPJP, Ruangan -->
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4
                                            class="truncate font-['ivypresto-headline'] text-base font-bold text-[#000000] sm:text-lg"
                                        >
                                            {{ clinic.poli_name }}
                                        </h4>
                                    </div>
                                    <div
                                        class="flex items-center gap-1.5 truncate text-xs font-medium text-[#333333]"
                                    >
                                        <Stethoscope
                                            class="size-3.5 shrink-0 text-[#000000]"
                                        />
                                        <span class="truncate">{{
                                            clinic.doctor_name
                                        }}</span>
                                    </div>
                                    <div class="pt-0.5">
                                        <span
                                            class="inline-flex items-center rounded-md bg-[#edede2] px-2 py-0.5 text-[10px] font-semibold text-[#000000] sm:text-[11px]"
                                        >
                                            {{ clinic.room_name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Box Kanan: Nomor Antrean / Status -->
                                <div class="min-w-[110px] shrink-0 text-right">
                                    <!-- Status 1: Ada Pasien Sedang Diperiksa -->
                                    <div
                                        v-if="clinic.current_calling"
                                        class="flex flex-col items-end"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-[#beedc0] px-2.5 py-0.5 text-[10px] font-extrabold tracking-wide text-[#000000] uppercase"
                                        >
                                            <span
                                                class="size-1.5 animate-ping rounded-full bg-emerald-700"
                                            ></span>
                                            <span>Diperiksa</span>
                                        </span>
                                        <div
                                            class="mt-1 font-mono text-2xl font-black tracking-tight text-[#000000] sm:text-3xl"
                                        >
                                            {{ clinic.current_calling }}
                                        </div>
                                    </div>

                                    <!-- Status 2: Menunggu Panggilan Dokter -->
                                    <div
                                        v-else-if="clinic.waiting_count > 0"
                                        class="flex flex-col items-end"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold tracking-wide text-amber-900 uppercase"
                                        >
                                            <Clock
                                                class="size-3 text-amber-700"
                                            />
                                            <span>Menunggu</span>
                                        </span>
                                        <div
                                            class="mt-1 font-mono text-xl font-bold text-[#333333] sm:text-2xl"
                                        >
                                            {{
                                                clinic.next_calling !== '-'
                                                    ? clinic.next_calling
                                                    : `${clinic.waiting_count} Siap`
                                            }}
                                        </div>
                                    </div>

                                    <!-- Status 3: Standby / Antrean Kosong -->
                                    <div v-else class="flex flex-col items-end">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full border border-[#000000]/10 bg-[#edede2] px-2.5 py-0.5 text-[10px] font-bold tracking-wide text-[#333333] uppercase"
                                        >
                                            <CheckCircle2
                                                class="size-3 text-emerald-600"
                                            />
                                            <span>Standby</span>
                                        </span>
                                        <div
                                            class="mt-1 font-mono text-xs font-semibold text-[#333333]/60 sm:text-sm"
                                        >
                                            Siap Melayani
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Kartu: Antrean Berikutnya & Badge Sisa -->
                            <div
                                class="flex items-center justify-between border-t border-[#000000]/5 pt-2 font-['Rubik'] text-xs text-[#333333]"
                            >
                                <div
                                    class="flex items-center gap-1.5 text-[11px] sm:text-xs"
                                >
                                    <span class="text-[#333333]/70"
                                        >Berikutnya:</span
                                    >
                                    <span
                                        class="font-mono font-bold"
                                        :class="
                                            clinic.next_calling &&
                                            clinic.next_calling !== '-'
                                                ? 'text-[#000000]'
                                                : 'text-[#333333]/40'
                                        "
                                    >
                                        {{
                                            clinic.next_calling &&
                                            clinic.next_calling !== '-'
                                                ? clinic.next_calling
                                                : '--'
                                        }}
                                    </span>
                                </div>

                                <span
                                    :class="[
                                        clinic.waiting_count > 0
                                            ? 'bg-[#beedc0] font-bold text-[#000000]'
                                            : 'bg-neutral-100 font-medium text-neutral-600',
                                    ]"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] sm:text-[11px]"
                                >
                                    <Users class="size-3" />
                                    <span
                                        >{{
                                            clinic.waiting_count
                                        }}
                                        Menunggu</span
                                    >
                                </span>
                            </div>
                        </div>
                    </motion.div>
                </div>

                <!-- Empty State Poliklinik -->
                <div
                    v-else
                    class="flex flex-1 flex-col items-center justify-center space-y-3 p-8 text-center"
                >
                    <div
                        class="flex size-14 items-center justify-center rounded-2xl bg-[#edede2] text-[#000000]"
                    >
                        <Stethoscope class="size-7 text-[#000000]" />
                    </div>
                    <div>
                        <h4
                            class="font-['ivypresto-headline'] text-lg font-bold text-[#000000]"
                        >
                            Tidak Ada Poliklinik Aktif
                        </h4>
                        <p
                            class="mt-1 max-w-xs font-['Rubik'] text-xs text-[#333333]"
                        >
                            Jadwal poliklinik dan dokter yang bertugas hari ini
                            akan otomatis muncul pada layar monitor.
                        </p>
                    </div>
                </div>
            </motion.div>
        </main>

        <!-- ═══════════════════════════════════════════════════════════════
             3. Footer Announcement Marquee
             ═══════════════════════════════════════════════════════════════ -->
        <motion.footer
            :initial="{ opacity: 0, y: 15 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.22, delay: 0.2, ease: 'easeOut' }"
            class="flex items-center overflow-hidden rounded-2xl border border-[#000000]/10 bg-[#000000] px-4 py-3 text-[#ffffff]"
        >
            <div
                class="flex shrink-0 items-center gap-2 border-r border-[#ffffff]/20 pr-4 text-xs font-bold text-[#beedc0] uppercase"
            >
                <Sparkles class="size-4" />
                <span>Pengumuman</span>
            </div>
            <div
                class="relative flex-1 overflow-hidden pl-4 font-['Rubik'] text-xs text-[#ffffff]/90 sm:text-sm"
            >
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    {{
                        displayConfig?.announcement_text ||
                        'Selamat datang di Layanan Rawat Jalan Rumah Sakit. Mohon selalu menjaga ketertiban, kebersihan, dan keselamatan bersama di area ruang tunggu poliklinik.'
                    }}
                </marquee>
            </div>
        </motion.footer>
    </div>
</template>
