<script setup lang="ts">
/**
 * @file Login.vue
 * @description Patient & Staff Login with interactive Rive mascot.
 */
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft } from '@lucide/vue'
import type { Rive, StateMachineInput } from '@rive-app/webgl2'
import axios from 'axios'
import { motion } from 'motion-v'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import InputError from '@/components/InputError.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import { home } from '@/routes'

const props = defineProps<{
    status?: string
    canResetPassword?: boolean
}>()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const riveCanvas = ref<HTMLCanvasElement | null>(null)
const riveLoaded = ref(false)
const riveError = ref(false)
let riveInstance: Rive | null = null

// Rive State Machine Inputs
let inputEmail: StateMachineInput | null = null
let inputPassword: StateMachineInput | null = null
let inputRemember: StateMachineInput | null = null
let inputError: StateMachineInput | null = null
let inputBusy: StateMachineInput | null = null

/**
 * Sinkronisasi reactive state formulir ke State Machine Rive
 */
const syncRiveState = () => {
    if (!riveLoaded.value) {
return
}

    if (inputEmail) {
inputEmail.value = form.email.length > 0
}

    if (inputPassword) {
inputPassword.value = form.password.length > 0
}

    if (inputRemember) {
inputRemember.value = form.remember
}

    if (inputError) {
inputError.value = Object.keys(form.errors).length > 0
}

    if (inputBusy) {
inputBusy.value = form.processing
}
}

const submit = async () => {
    form.clearErrors()
    form.processing = true

    try {
        const response = await axios.post('/api/login', {
            email: form.email,
            password: form.password,
            remember: form.remember,
        })

        if (response.data?.status === 'success') {
            // 1. Simpan token akses Sanctum di localStorage
            if (response.data.access_token) {
                localStorage.setItem('auth_token', response.data.access_token)
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`
            }

            // 2. Arahkan rute berdasarkan respon redirect_to atau role
            const userRole = response.data.data?.user?.role
            const isStaffOrDoctor = ['doctor', 'nurse', 'admin'].includes(userRole)
                || Boolean(response.data.data?.user?.is_doctor)
                || Boolean(response.data.data?.profile?.doctor_id)
                || Boolean(response.data.data?.profile?.nurse_id)

            const targetUrl = response.data.redirect_to 
                || (isStaffOrDoctor ? '/staff' : '/patient/dashboard')

            router.visit(targetUrl)
        }
    } catch (error: any) {
        form.processing = false
        form.reset('password')

        // Tampilkan pesan error validasi ke input form
        if (error.response?.status === 422 && error.response.data?.errors) {
            const errors = error.response.data.errors
            Object.keys(errors).forEach((key) => {
                form.setError(key as any, errors[key][0])
            })
        } else if (error.response?.data?.message) {
            form.setError('email', error.response.data.message)
        } else {
            form.setError('email', 'Terjadi kesalahan pada sistem. Silakan coba lagi.')
        }
    } finally {
        form.processing = false
    }
}

const handleLoginSuccess = (response: any) => {
    // Jika response mengandung redirect_to dari API
    if (response.data?.redirect_to) {
        router.visit(response.data.redirect_to)

        return
    }

    // Atau cek manual berdasarkan role
    const userRole = response.data?.data?.user?.role || response.data?.user?.role
    const isStaffOrDoctor = ['doctor', 'nurse', 'admin'].includes(userRole)
        || Boolean(response.data?.data?.user?.is_doctor)
        || Boolean(response.data?.user?.is_doctor)
        || Boolean(response.data?.data?.profile?.doctor_id)
        || Boolean(response.data?.data?.profile?.nurse_id)

    if (isStaffOrDoctor) {
        router.visit('/staff')
    } else {
        router.visit('/patient/dashboard')
    }
}

onMounted(async () => {
    if (!riveCanvas.value) {
return
}

    // Sesuaikan string State Machine dengan nama di file Rive kamu (misal: 'State Machine 1' atau 'Login Machine')
    const STATE_MACHINE_NAME = 'State Machine 1'

    try {
        const riveModule = await import('@rive-app/webgl2')
        const RiveConstructor = riveModule.Rive || (riveModule as any).default?.Rive || (riveModule as any).default

        riveInstance = new RiveConstructor({
            src: '/assets/rive/hospital.riv',
            canvas: riveCanvas.value,
            autoplay: true,
            stateMachines: STATE_MACHINE_NAME,
            onLoad: () => {
                riveLoaded.value = true
                riveError.value = false
                riveInstance?.resizeDrawingSurfaceToCanvas()

                // Hubungkan controller input
                const inputs = riveInstance?.stateMachineInputs(STATE_MACHINE_NAME)

                if (inputs) {
                    inputEmail = inputs.find(i => i.name === 'isEmailFilled' || i.name === 'isChecking') || null
                    inputPassword = inputs.find(i => i.name === 'isPasswordFilled' || i.name === 'isHandsUp') || null
                    inputRemember = inputs.find(i => i.name === 'rememberMe') || null
                    inputError = inputs.find(i => i.name === 'hasError') || null
                    inputBusy = inputs.find(i => i.name === 'isBusy') || null
                }

                syncRiveState()
            },
            onLoadError: (err) => {
                riveLoaded.value = false
                riveError.value = true
                console.error('File /assets/rive/hospital.riv gagal dimuat:', err)
            },
        })
    } catch (err) {
        riveLoaded.value = false
        riveError.value = true
        console.error('Inisialisasi Rive WebGL2 gagal:', err)
    }
})

watch(
    () => [form.email, form.password, form.remember, form.processing, form.errors],
    () => syncRiveState(),
    { deep: true },
)

onUnmounted(() => {
    if (riveInstance) {
        riveInstance.cleanup()
        riveInstance = null
    }
})
</script>

<template>
    <Head title="Masuk Layanan Rumah Sakit" />

    <div class="min-h-screen w-full bg-[#edede2] flex items-center justify-center p-4 sm:p-6 lg:p-12 font-['Rubik'] text-[#000000]">
        <motion.div
            :initial="{ opacity: 0, y: 15 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.25, ease: 'easeOut' }"
            class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 rounded-[10px] bg-[#fffff3] border border-neutral-200 overflow-hidden"
        >
            <!-- Left: Visual Character -->
            <div class="lg:col-span-6 bg-[#fffff3] p-6 sm:p-8 lg:p-12 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-neutral-200 relative overflow-hidden">
                <div class="flex items-center justify-between z-10">
                    <Link :href="home()" class="flex items-center gap-3 group">
                        <motion.div
                            :whileHover="{ scale: 1.05 }"
                            :whileTap="{ scale: 0.95 }"
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[#beedc0] border border-emerald-300"
                        >
                            <AppLogoIcon class="size-7 fill-current text-[#000000]" />
                        </motion.div>
                        <div>
                            <span class="font-['ivypresto-headline'] text-xl sm:text-2xl font-bold tracking-tight text-[#000000] block">
                                Sehat Bersama
                            </span>
                            <span class="text-xs text-[#333333] tracking-wide block">
                                Hospital Population Portal
                            </span>
                        </div>
                    </Link>
                    <span class="inline-flex items-center rounded-[46px] bg-[#beedc0] px-3.5 py-1 text-xs font-semibold text-[#000000]">
                        24/7 Siaga Medis
                    </span>
                </div>

                <div class="my-8 lg:my-auto py-6 z-10 space-y-6">
                    <div class="space-y-3">
                        <h1 class="font-['ivypresto-headline'] text-3xl sm:text-4xl lg:text-[44px] font-semibold leading-[1.3] text-[#000000]">
                            Pelayanan Medis <span class="bg-[#beedc0] px-2 py-0.5 rounded-[46px] inline-block">Cepat</span> & Bersahabat.
                        </h1>
                        <p class="text-base sm:text-lg leading-[1.7] text-[#333333]">
                            Satu akun untuk kemudahan antrean poli, jadwal dokter spesialis, hingga rekam medis keluarga Anda.
                        </p>
                    </div>

                    <motion.div
                        :whileHover="{ scale: 1.02, y: -2 }"
                        :transition="{ duration: 0.2, ease: 'easeOut' }"
                        class="rounded-[10px] bg-[#ffffff] p-4 border border-neutral-200 relative overflow-hidden"
                    >
                        <div class="absolute right-3 top-3 rounded-[46px] bg-[#beedc0] px-3 py-1 text-[10px] font-semibold text-[#000000]">
                            Character Live
                        </div>
                        <canvas ref="riveCanvas" class="h-[280px] w-full sm:h-[320px]" aria-label="Interactive hospital mascot" />
                        <div v-if="!riveLoaded" class="absolute inset-0 flex items-center justify-center p-6 text-center text-sm text-[#333333]">
                            Memuat animasi...
                        </div>
                    </motion.div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-[10px] bg-[#ffffff] border border-neutral-200 p-4">
                            <div class="text-xs text-[#333333]">Antrean</div>
                            <div class="mt-1 text-lg font-semibold text-[#000000]">Real-time</div>
                        </div>
                        <div class="rounded-[10px] bg-[#ffffff] border border-neutral-200 p-4">
                            <div class="text-xs text-[#333333]">Bayar</div>
                            <div class="mt-1 text-lg font-semibold text-[#000000]">QRIS / Xendit</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Login Form -->
            <div class="lg:col-span-6 p-6 sm:p-8 lg:p-12 flex flex-col justify-center bg-[#fffff3]">
                <div class="max-w-md w-full mx-auto space-y-6">
                    <div class="space-y-2">
                        <h2 class="font-['ivypresto-headline'] text-2xl sm:text-3xl font-semibold text-[#000000]">
                            Masuk ke Akun
                        </h2>
                        <p class="text-sm sm:text-base text-[#333333]">
                            Silakan masukkan email dan kata sandi Anda yang telah terdaftar.
                        </p>
                    </div>

                    <div v-if="status" class="rounded-[7px] bg-[#beedc0] p-4 text-center text-sm font-medium text-[#000000] border border-neutral-300 shadow-sm">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-5">
                        <div class="grid gap-2">
                            <Label for="email" class="text-sm font-medium text-[#000000]">Alamat Email</Label>
                            <Input id="email" v-model="form.email" type="email" required autofocus autocomplete="email" placeholder="nama@email.com" class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium text-[#000000]">Kata Sandi</Label>
                                <TextLink v-if="canResetPassword" href="/forgot-password" class="text-xs sm:text-sm font-medium text-[#333333] hover:text-[#000000] underline underline-offset-2 transition-colors">
                                    Lupa kata sandi?
                                </TextLink>
                            </div>
                            <PasswordInput id="password" v-model="form.password" required autocomplete="current-password" placeholder="Masukkan kata sandi" class="min-h-[44px] rounded-[7px] border-neutral-300 bg-[#ffffff] px-3.5 py-2.5 text-base text-[#000000] placeholder:text-[#333333]/50 focus-visible:ring-2 focus-visible:ring-[#000000]" />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between py-1">
                            <Label for="remember" class="flex min-h-[44px] cursor-pointer items-center space-x-3 text-sm font-medium text-[#333333] select-none">
                                <Checkbox id="remember" :checked="form.remember" @update:checked="form.remember = $event" class="h-5 w-5 rounded border-neutral-300 text-[#000000] focus:ring-[#000000]" />
                                <span>Ingat saya di perangkat ini</span>
                            </Label>
                        </div>

                        <motion.div :whileHover="{ scale: 1.015, y: -1 }" :whileTap="{ scale: 0.985 }" class="pt-2">
                            <Button type="submit" class="inline-flex min-h-[44px] w-full items-center justify-center rounded-[40.5px] bg-[#000000] px-6 py-3 text-base font-medium text-[#ffffff] shadow-none hover:bg-[#333333] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#000000] focus-visible:ring-offset-2 disabled:opacity-50" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2 h-5 w-5 text-white" />
                                <span>{{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}</span>
                            </Button>
                        </motion.div>
                    </form>

                    <div class="text-center text-sm text-[#333333] pt-4 border-t border-neutral-200">
                        Belum terdaftar sebagai pasien?
                        <TextLink href="/register" class="font-medium text-[#000000] underline underline-offset-4 hover:text-[#333333] ml-1">
                            Daftar Pasien Baru
                        </TextLink>
                    </div>

                    <!-- Ghost Pill Button — Back to Welcome (DESIGN.md Ghost Pill pattern) -->
                    <motion.div
                        :initial="{ opacity: 0, y: 6 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.22, ease: 'easeOut', delay: 0.15 }"
                        :whileHover="{ scale: 1.015, y: -1 }"
                        :whileTap="{ scale: 0.985 }"
                        class="pt-2"
                    >
                        <Link
                            :href="home().url"
                            class="inline-flex min-h-[44px] w-full items-center justify-center gap-2.5 rounded-[40.5px] border border-[#000000] bg-transparent px-6 py-3 font-['Rubik'] text-[15px] font-medium text-[#000000] transition-colors duration-150 hover:bg-[#000000] hover:text-[#ffffff] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#000000] focus-visible:ring-offset-2"
                        >
                            <ArrowLeft class="size-[18px]" aria-hidden="true" />
                            <span>Kembali ke Beranda</span>
                        </Link>
                    </motion.div>
                </div>
            </div>
        </motion.div>
    </div>
</template>
