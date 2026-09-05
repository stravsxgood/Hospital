/**
 * @file queueAudio.ts
 * @description Modul Audio & Text-to-Speech Pemanggilan Antrean Rumah Sakit (SIMRS).
 *
 * Mengimplementasikan:
 * 1. Nada Pembuka (Opening Chime) 4-nada khas rumah sakit / bandara (C5 -> E5 -> G5 -> C6).
 * 2. Nada Penutup (Closing Chime) 4-nada resolusi turun (C6 -> G5 -> E5 -> C5).
 * 3. Sintesis suara Web Speech API dengan kecepatan agak lambat (rate ~0.82) khas rumah sakit.
 * 4. Pilihan suara Bahasa Indonesia (id-ID) alami dan pemformatan nomor antrean (misal: "A, nol, nol, satu").
 */

let sharedAudioContext: AudioContext | null = null;

/**
 * Mengambil atau menginisialisasi AudioContext browser (singleton & resume state).
 */
export const getAudioContext = (): AudioContext | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        if (!sharedAudioContext) {
            const AudioContextClass =
                window.AudioContext || (window as any).webkitAudioContext;

            if (AudioContextClass) {
                sharedAudioContext = new AudioContextClass();
            }
        }

        if (sharedAudioContext && sharedAudioContext.state === 'suspended') {
            sharedAudioContext.resume();
        }

        return sharedAudioContext;
    } catch (e) {
        console.warn('Gagal menginisialisasi AudioContext:', e);

        return null;
    }
};

/**
 * Membunyikan satu nada lonceng (bell tone) dengan overtone harmonik hangat khas lonceng rumah sakit.
 */
const playBellTone = (
    ctx: AudioContext,
    frequency: number,
    startTime: number,
    duration = 0.65,
    volume = 0.28,
): void => {
    // 1. Nada fundamental (sine wave utama)
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(frequency, startTime);

    // Envelope nada utama: Attack halus 0.02s lalu peluruhan eksponensial
    gain1.gain.setValueAtTime(0.0001, startTime);
    gain1.gain.linearRampToValueAtTime(volume, startTime + 0.02);
    gain1.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

    osc1.connect(gain1);
    gain1.connect(ctx.destination);

    osc1.start(startTime);
    osc1.stop(startTime + duration);

    // 2. Nada overtone (oktaf ke-2) dengan volume lebih lembut untuk menghasilkan resonansi lonceng tubular
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(frequency * 2.0, startTime);

    gain2.gain.setValueAtTime(0.0001, startTime);
    gain2.gain.linearRampToValueAtTime(volume * 0.25, startTime + 0.015);
    gain2.gain.exponentialRampToValueAtTime(0.0001, startTime + duration * 0.6);

    osc2.connect(gain2);
    gain2.connect(ctx.destination);

    osc2.start(startTime);
    osc2.stop(startTime + duration * 0.6);
};

/**
 * 1. Nada Pembuka Pemanggilan (Opening Hospital Chime)
 * Melodi naik 4-nada: C5 (523.25Hz) -> E5 (659.25Hz) -> G5 (783.99Hz) -> C6 (1046.5Hz).
 * Durasi total nada: ~1.4 detik.
 */
export const playOpeningChime = (customCtx?: AudioContext | null): Promise<void> => {
    return new Promise((resolve) => {
        const ctx = customCtx || getAudioContext();

        if (!ctx) {
            resolve();

            return;
        }

        try {
            const start = ctx.currentTime;
            playBellTone(ctx, 523.25, start + 0.00, 0.55, 0.26); // C5
            playBellTone(ctx, 659.25, start + 0.22, 0.55, 0.26); // E5
            playBellTone(ctx, 783.99, start + 0.44, 0.60, 0.28); // G5
            playBellTone(ctx, 1046.50, start + 0.68, 0.85, 0.32); // C6

            setTimeout(() => {
                resolve();
            }, 1400);
        } catch (e) {
            console.warn('Gagal memutar nada pembuka:', e);
            resolve();
        }
    });
};

/**
 * 2. Nada Penutup Pemanggilan (Closing Hospital Chime)
 * Melodi turun 4-nada penutup resolusi: C6 (1046.5Hz) -> G5 (783.99Hz) -> E5 (659.25Hz) -> C5 (523.25Hz).
 * Durasi total nada: ~1.5 detik.
 */
export const playClosingChime = (customCtx?: AudioContext | null): Promise<void> => {
    return new Promise((resolve) => {
        const ctx = customCtx || getAudioContext();

        if (!ctx) {
            resolve();

            return;
        }

        try {
            const start = ctx.currentTime;
            playBellTone(ctx, 1046.50, start + 0.00, 0.55, 0.28); // C6
            playBellTone(ctx, 783.99, start + 0.22, 0.55, 0.26);  // G5
            playBellTone(ctx, 659.25, start + 0.44, 0.60, 0.26);  // E5
            playBellTone(ctx, 523.25, start + 0.68, 0.90, 0.30);  // C5

            setTimeout(() => {
                resolve();
            }, 1500);
        } catch (e) {
            console.warn('Gagal memutar nada penutup:', e);
            resolve();
        }
    });
};

/**
 * Mengubah kode nomor antrean (misal: "A-001" atau "B-12") menjadi ejaan angka lambat & jelas.
 * Contoh: "A-001" -> "A, nol, nol, satu"
 */
export const formatQueueNumberForSpeech = (rawQueue: string): string => {
    if (!rawQueue) {
        return '';
    }

    const trimmed = rawQueue.trim();
    const match = trimmed.match(/^([A-Za-z]+)[-\s]?(\d+)$/);

    const digitNames: Record<string, string> = {
        '0': 'nol',
        '1': 'satu',
        '2': 'dua',
        '3': 'tiga',
        '4': 'empat',
        '5': 'lima',
        '6': 'enam',
        '7': 'tujuh',
        '8': 'delapan',
        '9': 'sembilan',
    };

    if (match) {
        const prefix = match[1].toUpperCase().split('').join(' ');
        const digits = match[2]
            .split('')
            .map((d) => digitNames[d] || d)
            .join(', ');

        return `${prefix}, ${digits}`;
    }

    // Jika format non-standar, pisahkan tiap digit angka dengan koma
    return trimmed
        .replace(/-/g, ' ')
        .split('')
        .map((char) => digitNames[char] || char)
        .join(' ');
};

/**
 * Menyusun kalimat pemanggilan antrean formal dan santun dengan intonasi rumah sakit.
 */
export const buildHospitalAnnouncementText = (params: {
    queueNumber: string;
    patientName?: string | null;
    poliName: string;
    roomName: string;
    showPatientName?: boolean;
}): string => {
    const formattedQueue = formatQueueNumberForSpeech(params.queueNumber);

    const shouldIncludeName =
        params.showPatientName !== false &&
        Boolean(params.patientName && params.patientName.trim());

    const patientClause = shouldIncludeName
        ? `, atas nama ${params.patientName?.trim()}`
        : '';

    return `Nomor antrean, ${formattedQueue}${patientClause}. Silakan menuju ke ${params.poliName}, ${params.roomName}. Terima kasih.`;
};

/**
 * Menjalankan siklus pemanggilan lengkap antrean rumah sakit:
 * 1. Membunyikan nada pembuka (Opening Chime).
 * 2. Menunggu nada pembuka selesai.
 * 3. Membacakan pengumuman suara dengan laju lambat (rate ~0.82) dan suara Indonesia.
 * 4. Saat suara selesai, otomatis membunyikan nada penutup (Closing Chime).
 * 5. Memanggil callback onFinished setelah seluruh siklus dan nada penutup tuntas.
 */
export const announceHospitalQueue = async (options: {
    text: string;
    rate?: number;
    onStart?: () => void;
    onEnd?: () => void;
    onError?: (err?: any) => void;
}): Promise<void> => {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        options.onError?.(new Error('Web Speech Synthesis API tidak didukung'));

        return;
    }

    try {
        // 1. Bunyikan nada pembuka terlebih dahulu
        options.onStart?.();
        await playOpeningChime();

        // 2. Beri jeda sejenak (300ms) setelah nada pembuka agar suara tidak tumpang tindih
        await new Promise((r) => setTimeout(r, 300));

        // 3. Konfigurasi Utterance
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(options.text);
        utterance.lang = 'id-ID';

        // Laju bicara lambat dan tenang khas rumah sakit (default: 0.82)
        utterance.rate = options.rate ?? 0.82;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;

        // Cari suara Bahasa Indonesia alami
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(
            (v) =>
                v.lang.toLowerCase().includes('id-id') ||
                v.lang.toLowerCase().includes('id_id') ||
                v.lang.toLowerCase().includes('id')
        );

        if (idVoice) {
            utterance.voice = idVoice;
        }

        utterance.onend = async () => {
            // 4. Tutup dengan nada penutup pemanggilan (Closing Chime)
            try {
                await playClosingChime();
            } finally {
                options.onEnd?.();
            }
        };

        utterance.onerror = (err) => {
            console.warn('Speech synthesis error:', err);
            options.onError?.(err);
            options.onEnd?.();
        };

        window.speechSynthesis.speak(utterance);
    } catch (e) {
        console.error('Gagal menjalankan pengumuman suara antrean:', e);
        options.onError?.(e);
        options.onEnd?.();
    }
};
