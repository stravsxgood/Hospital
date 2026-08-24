<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-8 bg-[#edede2] p-6 transition-colors md:p-10"
    >
        <motion.div
            :initial="{ opacity: 0, scale: 0.95 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="{ duration: 0.3, ease: 'easeOut' }"
            class="w-full max-w-sm rounded-[10px] border border-neutral-200 bg-[#fffff3] p-8 md:p-10"
        >
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-6">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <motion.div
                            :whileHover="{ scale: 1.05 }"
                            :whileTap="{ scale: 0.95 }"
                            class="mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-[#beedc0] p-2"
                        >
                            <AppLogoIcon
                                class="size-10 fill-current text-[#000000]"
                            />
                        </motion.div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-3 text-center">
                        <h1
                            class="font-['ivypresto-headline'] text-[30px] leading-[1.41] font-semibold text-[#000000] md:text-[54px] md:leading-[1.48]"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="font-['Rubik'] text-[17px] leading-[1.9] text-[#333333] md:text-[19px]"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </motion.div>
    </div>
</template>
