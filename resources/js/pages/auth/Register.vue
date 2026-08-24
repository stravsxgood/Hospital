<script setup lang="ts">
/**
 * @file Register.vue
 * @description Patient Registration with interactive Rive mascot.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { Rive, StateMachineInput } from '@rive-app/webgl2';
import { motion } from 'motion-v';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';

const form = useForm({
    name: '',
    email: '',
    resident_n: '',
    gender: '',
    birthday_date: '',
    number_phone: '',
    password: '',
    password_confirmation: '',
});

const riveCanvas = ref<HTMLCanvasElement | null>(null);
const riveLoaded = ref(false);
const riveError = ref(false);
let riveInstance: Rive | null = null;

// Rive State Machine Inputs
let inputChecking: StateMachineInput | null = null;
let inputHandsUp: StateMachineInput | null = null;
let inputLook: StateMachineInput | null = null;
let inputError: StateMachineInput | null = null;
let inputBusy: StateMachineInput | null = null;

/**
 * Sinkronisasi reactive state formulir ke State Machine Rive
 */
const syncRiveState = () => {
    if (!riveLoaded.value) {
        return;
    }

    const hasFilledData =
        form.name.length > 0 ||
        form.email.length > 0 ||
        form.resident_n.length > 0;
    const isTypingSecurity =
        form.password.length > 0 || form.password_confirmation.length > 0;
    const hasErrors = Object.keys(form.errors).length > 0;

    if (inputChecking) {
        inputChecking.value = hasFilledData;
    }

    if (inputHandsUp) {
        inputHandsUp.value = isTypingSecurity;
    }

    if (inputLook) {
        inputLook.value = Math.min(
            (form.name.length + form.email.length) * 2,
            100,
        );
    }

    if (inputError) {
        inputError.value = hasErrors;
    }

    if (inputBusy) {
        inputBusy.value = form.processing;
    }
};

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(async () => {
    if (!riveCanvas.value) {
        return;
    }

    // Samakan nama State Machine dengan file hospital.riv (misal: 'State Machine 1' atau 'LoginSM')
    const STATE_MACHINE_NAME = 'State Machine 1';

    try {
        const riveModule = await import('@rive-app/webgl2');
        const RiveConstructor =
            riveModule.Rive ||
            (riveModule as any).default?.Rive ||
            (riveModule as any).default;

        riveInstance = new RiveConstructor({
            src: '/assets/rive/hospital.riv',
            canvas: riveCanvas.value,
            autoplay: true,
            stateMachines: STATE_MACHINE_NAME,
            onLoad: () => {
                riveLoaded.value = true;
                riveError.value = false;
                riveInstance?.resizeDrawingSurfaceToCanvas();

                const inputs =
                    riveInstance?.stateMachineInputs(STATE_MACHINE_NAME);

                if (inputs) {
                    // Mendukung nama variabel kustom maupun default Rive
                    inputChecking =
                        inputs.find(
                            (i) =>
                                i.name === 'isChecking' ||
                                i.name === 'isNameFilled',
                        ) || null;
                    inputHandsUp =
                        inputs.find(
                            (i) =>
                                i.name === 'isHandsUp' ||
                                i.name === 'isPasswordFilled',
                        ) || null;
                    inputLook =
                        inputs.find((i) => i.name === 'numLook') || null;
                    inputError =
                        inputs.find(
                            (i) =>
                                i.name === 'hasError' ||
                                i.name === 'isFormValid',
                        ) || null;
                    inputBusy =
                        inputs.find(
                            (i) =>
                                i.name === 'isBusy' ||
                                i.name === 'isSubmitting',
                        ) || null;
                }

                syncRiveState();
            },
            onLoadError: (err) => {
                riveLoaded.value = false;
                riveError.value = true;
                console.error(
                    'File /assets/rive/hospital.riv gagal dimuat di Register:',
                    err,
                );
            },
        });
    } catch (err) {
        riveLoaded.value = false;
        riveError.value = true;
        console.error('Inisialisasi Rive WebGL2 gagal di Register:', err);
    }
});

watch(
    () => [
        form.name,
        form.email,
        form.resident_n,
        form.gender,
        form.password,
        form.password_confirmation,
        form.processing,
        form.errors,
    ],
    () => syncRiveState(),
    { deep: true },
);

onUnmounted(() => {
    if (riveInstance) {
        riveInstance.cleanup();
        riveInstance = null;
    }
});
</script>

<template>
    <Head title="Daftar Pasien" />

    <div
        class="flex min-h-screen w-full items-center justify-center bg-[#edede2] p-4 font-['Rubik'] text-[#000000] sm:p-6 lg:p-12"
    >
        <motion.div
            :initial="{ opacity: 0, y: 15 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="grid w-full max-w-6xl grid-cols-1 overflow-hidden rounded-[10px] border border-neutral-200 bg-[#fffff3] shadow-none lg:grid-cols-12"
        >
            <!-- Left: Visual Character -->
            <div
                class="relative flex flex-col justify-between overflow-hidden border-b border-neutral-200 bg-[#fffff3] p-6 sm:p-8 lg:col-span-6 lg:border-r lg:border-b-0 lg:p-12"
            >
                <div class="z-10 flex items-center justify-between">
                    <Link :href="home()" class="group flex items-center gap-3">
                        <motion.div
                            :whileHover="{ scale: 1.05 }"
                            :whileTap="{ scale: 0.95 }"
                            class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-300 bg-[#beedc0]"
                        >
                            <AppLogoIcon
                                class="size-7 fill-current text-[#000000]"
                            />
                        </motion.div>
                        <div>
                            <span
                                class="block font-['ivypresto-headline'] text-xl font-bold tracking-tight text-[#000000] sm:text-2xl"
                                >Daftar Sekarang</span
                            >
                            <span
                                class="block text-xs tracking-wide text-[#333333]"
                                >Hospital Population Portal</span
                            >
                        </div>
                    </Link>
                    <span
                        class="inline-flex items-center rounded-[46px] bg-[#beedc0] px-3.5 py-1 text-xs font-semibold text-[#000000]"
                        >Registrasi Gratis</span
                    >
                </div>

                <div class="z-10 my-8 space-y-6 py-6 lg:my-auto">
                    <div class="space-y-3">
                        <h1
                            class="font-['ivypresto-headline'] text-3xl leading-[1.3] font-semibold text-[#000000] sm:text-4xl lg:text-[44px]"
                        >
                            Bergabunglah
                            <span
                                class="inline-block rounded-[46px] bg-[#beedc0] px-2 py-0.5"
                                >Sehat</span
                            >
                            & Aman.
                        </h1>
                        <p
                            class="text-base leading-[1.7] text-[#333333] sm:text-lg"
                        >
                            Buat akun untuk mengakses layanan kesehatan, jadwal
                            dokter, dan riwayat medis keluarga Anda.
                        </p>
                    </div>

                    <motion.div
                        :whileHover="{ scale: 1.02, y: -2 }"
                        :transition="{ duration: 0.2, ease: 'easeOut' }"
                        class="relative overflow-hidden rounded-[10px] border border-neutral-200 bg-[#ffffff] p-4"
                    >
                        <div
                            class="absolute top-3 right-3 rounded-[46px] bg-[#beedc0] px-3 py-1 text-[10px] font-semibold text-[#000000]"
                        >
                            Character Live
                        </div>
                        <canvas
                            ref="riveCanvas"
                            class="h-[280px] w-full sm:h-[320px]"
                            aria-label="Interactive hospital mascot"
                        />
                        <div
                            v-if="!riveLoaded"
                            class="absolute inset-0 flex items-center justify-center p-6 text-center text-sm text-[#333333]"
                        >
                            Memuat animasi...
                        </div>
                    </motion.div>

                    <div class="grid grid-cols-2 gap-3">
                        <div
                            class="rounded-[10px] border border-neutral-200 bg-[#ffffff] p-4"
                        >
                            <div class="text-xs text-[#333333]">
                                Pendaftaran
                            </div>
                            <div
                                class="mt-1 text-lg font-semibold text-[#000000]"
                            >
                                Cepat
                            </div>
                        </div>
                        <div
                            class="rounded-[10px] border border-neutral-200 bg-[#ffffff] p-4"
                        >
                            <div class="text-xs text-[#333333]">Bantuan</div>
                            <div
                                class="mt-1 text-lg font-semibold text-[#000000]"
                            >
                                Tim Medis
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Register Form -->
            <div
                class="flex flex-col justify-center bg-[#fffff3] p-6 sm:p-8 lg:col-span-6 lg:p-12"
            >
                <div class="mx-auto w-full max-w-md space-y-6">
                    <div class="space-y-2">
                        <h2
                            class="font-['ivypresto-headline'] text-2xl font-semibold text-[#000000] sm:text-3xl"
                        >
                            Buat Akun Baru
                        </h2>
                        <p class="text-sm text-[#333333] sm:text-base">
                            Isi data Anda untuk memulai layanan kesehatan
                            digital.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-5">
                        <div class="grid gap-1.5">
                            <Label
                                for="name"
                                class="text-sm font-medium text-[#000000]"
                                >Nama Lengkap</Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Nama lengkap sesuai KTP"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="resident_n"
                                class="text-sm font-medium text-[#000000]"
                                >NIK / Nomor KTP</Label
                            >
                            <Input
                                id="resident_n"
                                v-model="form.resident_n"
                                type="text"
                                required
                                placeholder="16 digit Nomor Induk Kependudukan"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError :message="form.errors.resident_n" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label
                                    for="gender"
                                    class="text-sm font-medium text-[#000000]"
                                    >Jenis Kelamin</Label
                                >
                                <select
                                    id="gender"
                                    v-model="form.gender"
                                    required
                                    class="min-h-[44px] w-full rounded-[7px] border border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] ring-offset-background focus-visible:ring-2 focus-visible:ring-[#000000] focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="" disabled>
                                        Pilih Jenis Kelamin
                                    </option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                                <InputError :message="form.errors.gender" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    for="birthday_date"
                                    class="text-sm font-medium text-[#000000]"
                                    >Tanggal Lahir</Label
                                >
                                <Input
                                    id="birthday_date"
                                    v-model="form.birthday_date"
                                    type="date"
                                    required
                                    class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] focus-visible:ring-2 focus-visible:ring-[#000000]"
                                />
                                <InputError
                                    :message="form.errors.birthday_date"
                                />
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="email"
                                class="text-sm font-medium text-[#000000]"
                                >Alamat Email</Label
                            >
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="nama@email.com"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="number_phone"
                                class="text-sm font-medium text-[#000000]"
                                >Nomor Telepon / WhatsApp</Label
                            >
                            <Input
                                id="number_phone"
                                v-model="form.number_phone"
                                type="tel"
                                placeholder="081234567890"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError :message="form.errors.number_phone" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="password"
                                class="text-sm font-medium text-[#000000]"
                                >Kata Sandi</Label
                            >
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="password_confirmation"
                                class="text-sm font-medium text-[#000000]"
                                >Konfirmasi Kata Sandi</Label
                            >
                            <PasswordInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                required
                                placeholder="Ulangi kata sandi Anda"
                                class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]"
                            />
                            <InputError
                                :message="form.errors.password_confirmation"
                            />
                        </div>

                        <motion.div
                            :whileHover="{ scale: 1.015, y: -1 }"
                            :whileTap="{ scale: 0.985 }"
                            class="pt-2"
                        >
                            <Button
                                type="submit"
                                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-[40.5px] bg-[#000000] px-6 py-3 text-base font-medium text-[#ffffff] shadow-none transition-colors hover:bg-[#333333] focus-visible:ring-2 focus-visible:ring-[#000000] focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-50"
                                :disabled="form.processing"
                            >
                                <Spinner
                                    v-if="form.processing"
                                    class="mr-2 h-5 w-5 text-white"
                                />
                                <span>{{
                                    form.processing
                                        ? 'Mendaftarkan...'
                                        : 'Daftar Sekarang'
                                }}</span>
                            </Button>
                        </motion.div>
                    </form>

                    <div
                        class="border-t border-neutral-200 pt-4 text-center text-sm text-[#333333]"
                    >
                        Sudah memiliki akun?
                        <TextLink
                            href="/login"
                            class="ml-1 font-medium text-[#000000] underline underline-offset-4 hover:text-[#333333]"
                            >Masuk di sini</TextLink
                        >
                    </div>
                </div>
            </div>
        </motion.div>
    </div>
</template>
