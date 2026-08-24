<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[#edede2] p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium"
            >
                <motion.div
                    :whileHover="{ scale: 1.05 }"
                    :whileTap="{ scale: 0.95 }"
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-[#beedc0]"
                >
                    <AppLogoIcon
                        class="size-10 fill-current text-[#000000]"
                    />
                </motion.div>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-[10px] bg-[#fffff3] border-neutral-200 shadow-none">
                    <CardHeader class="px-6 pt-8 pb-0 text-center md:px-10">
                        <CardTitle class="font-['ivypresto-headline'] text-[30px] font-semibold leading-[1.41] text-[#000000] md:text-[54px] md:leading-[1.48]">
                            {{ title }}
                        </CardTitle>
                        <CardDescription class="font-['Rubik'] text-[17px] leading-[1.9] text-[#333333] md:text-[19px]">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-6 py-8 md:px-10">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
