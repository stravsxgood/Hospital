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
import { Head } from '@inertiajs/vue3'
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
} from '@lucide/vue'
import { motion } from 'motion-v'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import echo from '@/echo'

interface CallingPayload {
    appointment_id: number
    queue_number: string
    patient_name: string
    poli_name: string
    room_name: string
    doctor_name: string
    voice_text: string
    called_at: string
}

interface ClinicItem {
    schedule_id: number
    doctor_name: string
    poli_name: string
    room_name: string
    current_calling: string | null
    patient_name: string | null
    next_calling: string
    waiting_count: number
}

const props = defineProps<{
    initialData?: {
        clinics: ClinicItem[]
        latestCalled: CallingPayload | null
    }
    currentDate?: string
}>()

const activeCall = ref<CallingPayload | null>(props.initialData?.latestCalled || null)
const clinics = ref<ClinicItem[]>(props.initialData?.clinics || [])
const isAudioEnabled = ref(false)
const isFullscreen = ref(false)
const liveClock = ref('')
const isSpeaking = ref(false)
const callHistory = ref<CallingPayload[]>([])

let clockTimer: any = null
let audioCtx: AudioContext | null = null

// Audio Context Unlock
const getAudioContext = (): AudioContext | null => {
    if (typeof window === 'undefined') {
return null
}

    try {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext

            if (AudioContextClass) {
                audioCtx = new AudioContextClass()
            }
        }

        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume()
        }

        return audioCtx
    } catch (e) {
        console.warn('Audio Context initialization error:', e)

        return null
    }
}

// 4-Tone Hospital Chime (C5 -> E5 -> G5 -> C6)
const playHospitalChime = () => {
    try {
        const ctx = getAudioContext()

        if (!ctx) {
return
}

        const playTone = (freq: number, start: number, duration: number, gainValue = 0.25) => {
            const osc = ctx.createOscillator()
            const gain = ctx.createGain()
            osc.type = 'sine'
            osc.frequency.setValueAtTime(freq, ctx.currentTime + start)

            gain.gain.setValueAtTime(gainValue, ctx.currentTime + start)
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + duration)

            osc.connect(gain)
            gain.connect(ctx.destination)

            osc.start(ctx.currentTime + start)
            osc.stop(ctx.currentTime + start + duration)
        }

        playTone(523.25, 0.0, 0.5, 0.25)
        playTone(659.25, 0.2, 0.5, 0.25)
        playTone(783.99, 0.4, 0.6, 0.3)
        playTone(1046.5, 0.65, 0.8, 0.35)
    } catch (e) {
        console.warn('Audio chime playback error:', e)
    }
}

// Text-to-Speech Engine (Web Speech API with Indonesian Voice)
const speakVoiceText = (text: string) => {
    if (!isAudioEnabled.value || typeof window === 'undefined' || !('speechSynthesis' in window)) {
        return
    }

    try {
        window.speechSynthesis.cancel()

        playHospitalChime()

        setTimeout(() => {
            const utterance = new SpeechSynthesisUtterance(text)
            utterance.lang = 'id-ID'
            utterance.rate = 0.92
            utterance.pitch = 1.05

            const voices = window.speechSynthesis.getVoices()
            const idVoice = voices.find((v) => v.lang.includes('id') || v.lang.includes('ID'))

            if (idVoice) {
                utterance.voice = idVoice
            }

            utterance.onstart = () => {
                isSpeaking.value = true
            }
            utterance.onend = () => {
                isSpeaking.value = false
            }
            utterance.onerror = () => {
                isSpeaking.value = false
            }

            window.speechSynthesis.speak(utterance)
        }, 1200)
    } catch (e) {
        console.error('TTS error:', e)
        isSpeaking.value = false
    }
}

// Toggle Audio Enable
const toggleAudio = () => {
    isAudioEnabled.value = !isAudioEnabled.value

    if (isAudioEnabled.value) {
        getAudioContext()
        playHospitalChime()
    }
}

// Toggle Fullscreen
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {})
        isFullscreen.value = true
    } else {
        document.exitFullscreen().catch(() => {})
        isFullscreen.value = false
    }
}

// Live Digital Clock
const updateClock = () => {
    const now = new Date()
    liveClock.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
}

// Handle WebSocket Event
const handleIncomingCall = (payload: CallingPayload) => {
    activeCall.value = payload

    // Prepend to history
    callHistory.value = [payload, ...callHistory.value.filter((c) => c.appointment_id !== payload.appointment_id)].slice(0, 6)

    // Update clinic list active calling if matches
    const targetClinic = clinics.value.find((c) => c.poli_name === payload.poli_name || c.room_name === payload.room_name)

    if (targetClinic) {
        targetClinic.current_calling = payload.queue_number
        targetClinic.patient_name = payload.patient_name
    }

    // Voice announcement
    speakVoiceText(payload.voice_text)
}

onMounted(() => {
    updateClock()
    clockTimer = setInterval(updateClock, 1000)

    // Inisialisasi daftar suara TTS
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.getVoices()
    }

    // Hubungkan Laravel Echo ke channel public 'queue-display'
    try {
        echo.channel('queue-display').listen('.PatientCalledEvent', (event: CallingPayload) => {
            handleIncomingCall(event)
        }).listen('PatientCalledEvent', (event: CallingPayload) => {
            handleIncomingCall(event)
        })
    } catch (e) {
        console.warn('Echo channel subscribe warning:', e)
    }
})

onBeforeUnmount(() => {
    if (clockTimer) {
clearInterval(clockTimer)
}

    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.cancel()
    }

    try {
        echo.leaveChannel('queue-display')
    } catch (e) {}
})
</script>

<template>
    <div class="min-h-screen bg-[#edede2] text-[#000000] font-sans antialiased flex flex-col justify-between select-none">
        <Head title="Display TV Antrean Publik SIMRS" />

        <!-- Header Display TV -->
        <header class="bg-[#fffff3] border-b border-[#000000]/10 px-8 py-5 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-5">
                <div class="w-14 h-14 rounded-2xl bg-[#beedc0] border border-[#065f46]/30 flex items-center justify-center shadow-inner">
                    <AppLogoIcon class="w-8 h-8 text-[#065f46]" />
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-serif font-bold text-[#000000] tracking-tight">
                        HOSPITAL POPULATION
                    </h1>
                    <p class="text-sm font-medium text-[#065f46] flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#065f46] animate-pulse"></span>
                        Sistem Informasi Layar Panggilan Antrean Terpadu (Live Real-Time)
                    </p>
                </div>
            </div>

            <!-- Jam, Tanggal, & Kontrol Display -->
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <div class="text-3xl font-bold font-mono text-[#065f46] tracking-wider">
                        {{ liveClock }}
                    </div>
                    <div class="text-xs font-semibold text-[#000000]/60 uppercase tracking-widest">
                        {{ currentDate || 'Hari Ini' }}
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <motion.button
                        type="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="toggleAudio"
                        class="min-h-[44px] px-4 py-2.5 rounded-xl border font-medium text-sm flex items-center gap-2 transition-colors cursor-pointer"
                        :class="isAudioEnabled ? 'bg-[#beedc0] text-[#065f46] border-[#065f46]/30 shadow-sm' : 'bg-[#fffff3] text-[#000000]/70 border-[#000000]/15 hover:bg-[#edede2]'"
                    >
                        <Volume2 v-if="isAudioEnabled" class="w-5 h-5 text-[#065f46]" />
                        <VolumeX v-else class="w-5 h-5 text-[#000000]/50" />
                        <span>{{ isAudioEnabled ? 'Suara Aktif' : 'Aktifkan Suara' }}</span>
                    </motion.button>

                    <motion.button
                        type="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="toggleFullscreen"
                        class="min-h-[44px] p-2.5 rounded-xl border border-[#000000]/15 bg-[#fffff3] text-[#000000]/70 hover:bg-[#edede2] transition-colors cursor-pointer"
                        title="Mode Layar Penuh"
                    >
                        <Minimize2 v-if="isFullscreen" class="w-5 h-5" />
                        <Maximize2 v-else class="w-5 h-5" />
                    </motion.button>
                </div>
            </div>
        </header>

        <!-- Main Display Content -->
        <main class="flex-1 p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <!-- Left Banner: Active Calling Spotlight (7 Cols) -->
            <section class="lg:col-span-7 flex flex-col">
                <motion.div
                    :initial="{ opacity: 0, scale: 0.98 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.3 }"
                    class="flex-1 bg-[#fffff3] rounded-3xl border-2 border-[#065f46] p-8 lg:p-10 shadow-xl flex flex-col justify-between relative overflow-hidden"
                >
                    <!-- Background Accent Glow -->
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-[#beedc0]/30 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Header Spotlight -->
                    <div class="flex items-center justify-between border-b border-[#000000]/10 pb-6">
                        <div class="flex items-center space-x-3">
                            <span class="relative flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#065f46] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-[#065f46]"></span>
                            </span>
                            <span class="text-sm lg:text-base font-bold uppercase tracking-widest text-[#065f46]">
                                Sedang Dipanggil Saat Ini
                            </span>
                        </div>

                        <div v-if="isSpeaking" class="flex items-center gap-2 text-xs font-semibold bg-[#beedc0] text-[#065f46] px-3 py-1.5 rounded-full border border-[#065f46]/20">
                            <Volume2 class="w-4 h-4 animate-bounce" />
                            <span>Pengumuman Suara...</span>
                        </div>
                    </div>

                    <!-- Main Number Presentation -->
                    <div class="my-auto py-8 text-center" v-if="activeCall">
                        <div class="text-sm font-semibold uppercase tracking-wider text-[#000000]/60 mb-2">
                            Nomor Antrean Pasien
                        </div>
                        <div class="text-7xl lg:text-9xl font-black font-mono tracking-tight text-[#065f46] drop-shadow-sm">
                            {{ activeCall.queue_number }}
                        </div>
                        <div class="mt-4 text-2xl lg:text-3xl font-serif font-bold text-[#000000]">
                            {{ activeCall.patient_name }}
                        </div>
                    </div>

                    <!-- Empty State saat belum ada panggilan -->
                    <div class="my-auto py-12 text-center" v-else>
                        <Bell class="w-16 h-16 text-[#000000]/20 mx-auto mb-4 animate-pulse" />
                        <div class="text-xl font-serif font-bold text-[#000000]/70">
                            Menunggu Pemanggilan Antrean Berikutnya
                        </div>
                        <p class="text-sm text-[#000000]/50 mt-1">
                            Nomor antrean yang dipanggil dokter akan tampil di sini secara real-time.
                        </p>
                    </div>

                    <!-- Destination Footer -->
                    <div class="bg-[#edede2] rounded-2xl p-6 border border-[#000000]/10 flex items-center justify-between" v-if="activeCall">
                        <div>
                            <div class="text-xs font-semibold text-[#000000]/60 uppercase">Tujuan Poliklinik & Ruang</div>
                            <div class="text-xl lg:text-2xl font-bold text-[#000000]">
                                {{ activeCall.poli_name }} <span class="text-[#065f46]">({{ activeCall.room_name }})</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-semibold text-[#000000]/60 uppercase">Dokter Pemeriksa</div>
                            <div class="text-base lg:text-lg font-semibold text-[#065f46]">
                                {{ activeCall.doctor_name }}
                            </div>
                        </div>
                    </div>
                </motion.div>
            </section>

            <!-- Right Panel: Matrix Poliklinik & Riwayat Panggilan (5 Cols) -->
            <section class="lg:col-span-5 flex flex-col gap-6">
                <!-- Riwayat Panggilan Terkini -->
                <div class="bg-[#fffff3] rounded-3xl border border-[#000000]/10 p-6 shadow-sm flex-1 flex flex-col">
                    <h3 class="text-base font-bold text-[#000000] font-serif uppercase tracking-wide flex items-center gap-2 mb-4">
                        <Clock class="w-5 h-5 text-[#065f46]" />
                        Riwayat Panggilan Terakhir
                    </h3>

                    <div class="space-y-3 flex-1 overflow-y-auto pr-1" v-if="callHistory.length > 0">
                        <div
                            v-for="(hist, idx) in callHistory"
                            :key="hist.appointment_id + '-' + idx"
                            class="bg-[#edede2]/60 rounded-xl p-3.5 border border-[#000000]/5 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <span class="text-lg font-mono font-bold text-[#065f46] px-2.5 py-1 rounded-lg bg-[#beedc0]">
                                    {{ hist.queue_number }}
                                </span>
                                <div>
                                    <div class="text-sm font-bold text-[#000000] truncate max-w-[160px]">
                                        {{ hist.patient_name }}
                                    </div>
                                    <div class="text-xs text-[#000000]/60">
                                        {{ hist.poli_name }} · {{ hist.room_name }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-medium text-[#000000]/50">
                                {{ new Date(hist.called_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 text-[#000000]/40" v-else>
                        <Clock class="w-10 h-10 mb-2 opacity-50" />
                        <span class="text-sm">Belum ada riwayat panggilan sesi ini</span>
                    </div>
                </div>

                <!-- Informasi Pelayanan RS -->
                <div class="bg-[#065f46] text-[#fffff3] rounded-3xl p-6 shadow-md border border-[#065f46]">
                    <div class="flex items-center space-x-3 mb-2">
                        <Stethoscope class="w-6 h-6 text-[#beedc0]" />
                        <span class="text-base font-bold font-serif">Tata Tertib Ruang Tunggu</span>
                    </div>
                    <p class="text-xs leading-relaxed text-[#fffff3]/80">
                        Harap perhatikan nomor antrean di layar. Saat nomor Anda dipanggil, mohon segera menuju ruang poli yang tertera dengan membawa kartu berobat / identitas pasien.
                    </p>
                </div>
            </section>
        </main>

        <!-- Footer Running Text / Ticker -->
        <footer class="bg-[#000000] text-[#fffff3] py-3.5 px-6 border-t border-[#000000] flex items-center overflow-hidden">
            <div class="text-xs font-bold uppercase tracking-widest text-[#beedc0] whitespace-nowrap mr-6 flex items-center gap-2">
                <Sparkles class="w-4 h-4 text-[#beedc0]" />
                INFORMASI SIMRS:
            </div>
            <div class="overflow-hidden relative flex-1">
                <div class="animate-marquee whitespace-nowrap text-sm text-[#fffff3]/90 font-medium">
                    Selamat datang di Rumah Sakit Hospital Population. Pelayanan rawat jalan beroperasi setiap Senin s/d Sabtu. Pendaftaran online dan informasi jadwal dokter dapat diakses melalui portal pasien. Jagalah kebersihan dan ketenangan di lingkungan rumah sakit.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes marquee {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

.animate-marquee {
    display: inline-block;
    animation: marquee 25s linear infinite;
}
</style>
