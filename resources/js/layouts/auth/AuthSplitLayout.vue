<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center bg-[#edede2] px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <!-- In Split layout, left side acts as the warm branding panel with linen styling & decorative items -->
        <div
            class="relative hidden h-full flex-col border-r border-neutral-200 bg-[#fffff3] p-10 text-[#000000] lg:flex"
        >
            <div class="relative z-20 flex items-center gap-3">
                <motion.div
                    :whileHover="{ scale: 1.05 }"
                    :whileTap="{ scale: 0.95 }"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-[#beedc0]"
                >
                    <AppLogoIcon class="size-8 fill-current text-[#000000]" />
                </motion.div>
                <span
                    class="font-['Rubik'] text-[19px] font-semibold text-[#000000]"
                >
                    {{ name }}
                </span>
            </div>

            <!-- Warm editorial content on left pane -->
            <div class="relative z-20 my-auto max-w-lg space-y-6">
                <h2
                    class="font-['ivypresto-headline'] text-[54px] leading-[1.48] font-semibold text-[#000000]"
                >
                    Pelayanan Medis
                    <span class="rounded-[46px] bg-[#beedc0] px-2"
                        >Lebih Mudah</span
                    >
                    & Cepat.
                </h2>
                <p
                    class="font-['Rubik'] text-[19px] leading-[1.9] text-[#333333]"
                >
                    Kami berkomitmen memberikan akses layanan kesehatan yang
                    responsif, transparan, dan inklusif bagi Anda dan keluarga
                    di setiap waktu.
                </p>
            </div>
        </div>

        <div class="flex w-full justify-center lg:p-8">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
                class="mx-auto flex w-full max-w-md flex-col justify-center space-y-6 rounded-[10px] border border-neutral-200 bg-[#fffff3] p-8 md:p-10"
            >
                <div class="flex flex-col space-y-3 text-center">
                    <h1
                        class="font-['ivypresto-headline'] text-[30px] leading-[1.41] font-semibold text-[#000000] md:text-[54px] md:leading-[1.48]"
                        v-if="title"
                    >
                        {{ title }}
                    </h1>
                    <p
                        class="font-['Rubik'] text-[17px] leading-[1.9] text-[#333333] md:text-[19px]"
                        v-if="description"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </motion.div>
        </div>
    </div>
</template>
