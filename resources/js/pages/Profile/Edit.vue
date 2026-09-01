<script setup lang="ts">
/**
 * @file Edit.vue
 * @description Halaman Manajemen Profil & Keamanan Akun Pengguna SIMRS Hospital Population.
 *
 * Mengikuti standar arsitektur AGENTS.md, GEMINI.md & DESIGN.md:
 * - Tema Evergreen: Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Ink Black (#000000).
 * - Tipografi: IvyPresto Headline serif + Rubik sans.
 * - Form state management menggunakan useForm dari @inertiajs/vue3 dengan penanganan inline error dan status processing.
 * - Feedback interaktif menggunakan Sonner toast & visual banner berbasis Motion-V.
 * - Target sentuh ramah minimum 44px (min-h-[44px]).
 */
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2, KeyRound, Loader2, Lock, Save, User } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface UserPayload {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    role?: string;
    is_doctor?: boolean;
    email_verified_at?: string | null;
}

interface Props {
    user: UserPayload;
    mustVerifyEmail?: boolean;
    status?: string | null;
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan Akun',
                href: '/profile',
            },
            {
                title: 'Profil Pengguna',
                href: '/profile',
            },
        ],
    },
});

const page = usePage();

/* ── 1. State Form Informasi Profil ── */
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
});

const profileSavedSuccessfully = ref(false);

const submitProfileUpdate = (): void => {
    profileSavedSuccessfully.value = false;
    profileForm.patch('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            profileSavedSuccessfully.value = true;
            toast.success('Profil Anda berhasil diperbarui!');
            setTimeout(() => {
                profileSavedSuccessfully.value = false;
            }, 6000);
        },
        onError: () => {
            toast.error('Gagal memperbarui profil. Silakan periksa formulir.');
        },
    });
};

/* ── 2. State Form Pembaruan Kata Sandi ── */
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordSavedSuccessfully = ref(false);

const submitPasswordUpdate = (): void => {
    passwordSavedSuccessfully.value = false;
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordSavedSuccessfully.value = true;
            passwordForm.reset();
            toast.success('Kata sandi berhasil diperbarui!');
            setTimeout(() => {
                passwordSavedSuccessfully.value = false;
            }, 6000);
        },
        onError: () => {
            toast.error(
                'Gagal memperbarui kata sandi. Pastikan kata sandi saat ini sesuai.',
            );
        },
    });
};

/* ── Format Label Role Pengguna ── */
const roleDisplayLabel = computed((): string => {
    const role = props.user.role || page.props.auth?.user?.role;

    switch (role) {
        case 'super-admin':
        case 'admin':
            return 'Administrator Sistem';
        case 'doctor':
            return 'Dokter Spesialis / DPJP';
        case 'nurse':
            return 'Tenaga Medis / Perawat';
        case 'patient':
        default:
            return 'Pasien Terdaftar';
    }
});
</script>

<template>
    <Head title="Pengaturan Profil - Hospital Population" />

    <div
        class="min-h-screen w-full space-y-8 bg-[#edede2] p-6 font-['Rubik'] text-[#000000] sm:p-8"
    >
        <div class="mx-auto max-w-4xl space-y-8">
            <!-- ═══════════════════════════════════════════════════════════════
                 Header Halaman
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.div
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut' }"
                class="flex flex-col items-start justify-between gap-4 border-b border-[#333333]/15 pb-6 sm:flex-row sm:items-center"
            >
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            <User class="size-3.5" />
                            <span>{{ roleDisplayLabel }}</span>
                        </span>
                    </div>
                    <h1
                        class="font-['ivypresto-headline'] text-3xl font-semibold text-[#000000] sm:text-4xl"
                    >
                        Pengaturan Profil
                    </h1>
                    <p class="text-sm text-[#333333]">
                        Kelola data pribadi, informasi kontak telepon, dan
                        keamanan kata sandi akun Anda.
                    </p>
                </div>
            </motion.div>

            <!-- ═══════════════════════════════════════════════════════════════
                 Bagian 1: Form Informasi Profil
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.section
                :initial="{ opacity: 0, y: 14 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.28, ease: 'easeOut', delay: 0.05 }"
                class="rounded-[12px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm sm:p-8"
            >
                <div
                    class="mb-6 flex items-start justify-between border-b border-[#333333]/10 pb-4"
                >
                    <div>
                        <h2
                            class="font-['ivypresto-headline'] text-xl font-semibold text-[#000000] sm:text-2xl"
                        >
                            Informasi Profil
                        </h2>
                        <p class="mt-1 text-xs text-[#333333]/80 sm:text-sm">
                            Perbarui nama lengkap, alamat surel resmi, dan nomor
                            telepon kontak.
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#beedc0]"
                    >
                        <User class="size-5 text-[#000000]" />
                    </div>
                </div>

                <!-- Banner Feedback Berhasil Simpan Profil -->
                <motion.div
                    v-if="profileSavedSuccessfully"
                    :initial="{ opacity: 0, height: 0 }"
                    :animate="{ opacity: 1, height: 'auto' }"
                    :transition="{ duration: 0.22, ease: 'easeOut' }"
                    class="mb-6 flex items-center gap-3 rounded-[8px] border border-emerald-300 bg-emerald-50 p-4 text-xs font-medium text-emerald-900 sm:text-sm"
                >
                    <CheckCircle2 class="size-5 shrink-0 text-emerald-600" />
                    <span>Profil berhasil diperbarui dan tersimpan aman.</span>
                </motion.div>

                <form
                    @submit.prevent="submitProfileUpdate"
                    class="space-y-6"
                    novalidate
                >
                    <!-- Input Nama Lengkap -->
                    <div class="space-y-1.5">
                        <Label
                            for="profile_name"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Nama Lengkap
                            <span class="text-red-500">*</span>
                        </Label>
                        <div class="relative">
                            <Input
                                id="profile_name"
                                v-model="profileForm.name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="Masukkan nama lengkap Anda"
                                class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                                :class="{
                                    'border-red-400 focus-visible:ring-red-300':
                                        profileForm.errors.name,
                                }"
                            />
                        </div>
                        <InputError
                            :message="profileForm.errors.name"
                            class="mt-1"
                        />
                    </div>

                    <!-- Input Alamat Surel (Email) -->
                    <div class="space-y-1.5">
                        <Label
                            for="profile_email"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Alamat Surel (Email)
                            <span class="text-red-500">*</span>
                        </Label>
                        <div class="relative">
                            <Input
                                id="profile_email"
                                v-model="profileForm.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="contoh: user@hospital.com"
                                class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                                :class="{
                                    'border-red-400 focus-visible:ring-red-300':
                                        profileForm.errors.email,
                                }"
                            />
                        </div>
                        <InputError
                            :message="profileForm.errors.email"
                            class="mt-1"
                        />
                    </div>

                    <!-- Input Nomor Telepon Kontak -->
                    <div class="space-y-1.5">
                        <Label
                            for="profile_phone"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Nomor Telepon / WhatsApp
                            <span class="text-xs font-normal text-[#333333]/70"
                                >(Opsional)</span
                            >
                        </Label>
                        <div class="relative">
                            <Input
                                id="profile_phone"
                                v-model="profileForm.phone"
                                type="tel"
                                autocomplete="tel"
                                placeholder="contoh: 081234567890"
                                class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                                :class="{
                                    'border-red-400 focus-visible:ring-red-300':
                                        profileForm.errors.phone,
                                }"
                            />
                        </div>
                        <p class="text-[11px] text-[#333333]/70">
                            Nomor telepon digunakan untuk pengingat jadwal
                            antrean dan konfirmasi reservasi poliklinik.
                        </p>
                        <InputError
                            :message="profileForm.errors.phone"
                            class="mt-1"
                        />
                    </div>

                    <!-- Tombol Simpan Profil -->
                    <div class="flex items-center justify-end pt-2">
                        <motion.button
                            type="submit"
                            :disabled="
                                profileForm.processing || !profileForm.isDirty
                            "
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-[#333333] disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                        >
                            <Loader2
                                v-if="profileForm.processing"
                                class="size-4 animate-spin"
                            />
                            <Save v-else class="size-4" />
                            <span>{{
                                profileForm.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Perubahan'
                            }}</span>
                        </motion.button>
                    </div>
                </form>
            </motion.section>

            <!-- ═══════════════════════════════════════════════════════════════
                 Bagian 2: Form Pembaruan Kata Sandi
                 ═══════════════════════════════════════════════════════════════ -->
            <motion.section
                :initial="{ opacity: 0, y: 14 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.28, ease: 'easeOut', delay: 0.1 }"
                class="rounded-[12px] border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm sm:p-8"
            >
                <div
                    class="mb-6 flex items-start justify-between border-b border-[#333333]/10 pb-4"
                >
                    <div>
                        <h2
                            class="font-['ivypresto-headline'] text-xl font-semibold text-[#000000] sm:text-2xl"
                        >
                            Pembaruan Kata Sandi
                        </h2>
                        <p class="mt-1 text-xs text-[#333333]/80 sm:text-sm">
                            Pastikan akun Anda menggunakan kata sandi yang kuat
                            dan aman demi privasi data medis.
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#beedc0]"
                    >
                        <Lock class="size-5 text-[#000000]" />
                    </div>
                </div>

                <!-- Banner Feedback Berhasil Simpan Password -->
                <motion.div
                    v-if="passwordSavedSuccessfully"
                    :initial="{ opacity: 0, height: 0 }"
                    :animate="{ opacity: 1, height: 'auto' }"
                    :transition="{ duration: 0.22, ease: 'easeOut' }"
                    class="mb-6 flex items-center gap-3 rounded-[8px] border border-emerald-300 bg-emerald-50 p-4 text-xs font-medium text-emerald-900 sm:text-sm"
                >
                    <CheckCircle2 class="size-5 shrink-0 text-emerald-600" />
                    <span>Kata sandi akun Anda berhasil diperbarui.</span>
                </motion.div>

                <form
                    @submit.prevent="submitPasswordUpdate"
                    class="space-y-6"
                    novalidate
                >
                    <!-- Input Kata Sandi Saat Ini -->
                    <div class="space-y-1.5">
                        <Label
                            for="current_password"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Kata Sandi Saat Ini
                            <span class="text-red-500">*</span>
                        </Label>
                        <PasswordInput
                            id="current_password"
                            v-model="passwordForm.current_password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi saat ini"
                            class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                            :class="{
                                'border-red-400 focus-visible:ring-red-300':
                                    passwordForm.errors.current_password,
                            }"
                        />
                        <InputError
                            :message="passwordForm.errors.current_password"
                            class="mt-1"
                        />
                    </div>

                    <!-- Input Kata Sandi Baru -->
                    <div class="space-y-1.5">
                        <Label
                            for="new_password"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Kata Sandi Baru
                            <span class="text-red-500">*</span>
                        </Label>
                        <PasswordInput
                            id="new_password"
                            v-model="passwordForm.password"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter kombinasi aman"
                            class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                            :class="{
                                'border-red-400 focus-visible:ring-red-300':
                                    passwordForm.errors.password,
                            }"
                        />
                        <InputError
                            :message="passwordForm.errors.password"
                            class="mt-1"
                        />
                    </div>

                    <!-- Input Konfirmasi Kata Sandi Baru -->
                    <div class="space-y-1.5">
                        <Label
                            for="password_confirmation"
                            class="text-xs font-semibold text-[#000000] sm:text-sm"
                        >
                            Konfirmasi Kata Sandi Baru
                            <span class="text-red-500">*</span>
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi baru"
                            class="min-h-[44px] rounded-[8px] border-[#333333]/20 bg-[#ffffff] px-3.5 text-sm text-[#000000] placeholder:text-[#333333]/40 focus-visible:border-[#000000] focus-visible:ring-[#beedc0]"
                            :class="{
                                'border-red-400 focus-visible:ring-red-300':
                                    passwordForm.errors.password_confirmation,
                            }"
                        />
                        <InputError
                            :message="passwordForm.errors.password_confirmation"
                            class="mt-1"
                        />
                    </div>

                    <!-- Tombol Perbarui Kata Sandi -->
                    <div class="flex items-center justify-end pt-2">
                        <motion.button
                            type="submit"
                            :disabled="
                                passwordForm.processing ||
                                !passwordForm.current_password ||
                                !passwordForm.password
                            "
                            :whileHover="{ scale: 1.02 }"
                            :whileTap="{ scale: 0.98 }"
                            class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-[#333333] disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                        >
                            <Loader2
                                v-if="passwordForm.processing"
                                class="size-4 animate-spin"
                            />
                            <KeyRound v-else class="size-4" />
                            <span>{{
                                passwordForm.processing
                                    ? 'Memperbarui...'
                                    : 'Perbarui Kata Sandi'
                            }}</span>
                        </motion.button>
                    </div>
                </form>
            </motion.section>
        </div>
    </div>
</template>
