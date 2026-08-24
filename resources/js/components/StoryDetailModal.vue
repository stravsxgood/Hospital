<script setup lang="ts">
/**
 * @file StoryDetailModal.vue
 * @description Presentational modal component to display complete patient story narrative,
 * medical diagnosis details, specialist doctor info, and direct appointment CTA.
 * Strictly adheres to DESIGN.md (Evergreen theme) and accessibility standards.
 */
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowRight,
    Calendar,
    Clock,
    HeartHandshake,
    Quote,
    Stethoscope,
    User,
    X,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { PatientStory } from '@/types';

const props = defineProps<{
    open: boolean;
    story: PatientStory | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

/**
 * Split full content into clean paragraphs
 */
const paragraphs = computed(() => {
    if (!props.story?.full_content) {
        return [];
    }

    return props.story.full_content
        .split('\n\n')
        .map((p) => p.trim())
        .filter(Boolean);
});

/**
 * Generate direct link to doctor practice schedule based on polyclinic name
 */
const scheduleUrl = computed(() => {
    if (!props.story?.poli_name) {
        return '/schedule-guest';
    }

    return `/schedule-guest?poli=${encodeURIComponent(props.story.poli_name)}`;
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="max-h-[90vh] max-w-2xl scrollbar-thin overflow-y-auto rounded-[12px] border border-[#333333]/20 bg-[#fffff3] p-6 font-['Rubik'] text-[#000000] shadow-2xl sm:p-8"
        >
            <template v-if="story">
                <!-- Top Meta Header -->
                <DialogHeader class="space-y-3 text-left">
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b border-[#333333]/10 pb-3"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]"
                        >
                            <Activity class="size-3.5" />
                            {{ story.category }}
                        </span>

                        <div
                            class="flex items-center gap-3 text-xs font-medium text-[#333333]/75"
                        >
                            <span class="inline-flex items-center gap-1">
                                <Clock class="size-3.5 text-[#333333]/60" />
                                {{ story.read_time }}
                            </span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1">
                                <Calendar class="size-3.5 text-[#333333]/60" />
                                {{ story.published_at }}
                            </span>
                        </div>
                    </div>

                    <DialogTitle
                        class="font-['ivypresto-headline'] font-serif text-2xl leading-snug font-bold text-[#000000] sm:text-3xl"
                    >
                        {{ story.title }}
                    </DialogTitle>
                </DialogHeader>

                <!-- Featured Image (if available) -->
                <div
                    v-if="story.image_url"
                    class="relative my-2 max-h-64 w-full overflow-hidden rounded-[8px] sm:max-h-72"
                >
                    <img
                        :src="story.image_url"
                        :alt="story.title"
                        class="h-full w-full object-cover object-center"
                        loading="lazy"
                    />
                    <div v-if="story.badge" class="absolute bottom-3 left-3">
                        <span
                            class="rounded-full border border-white/20 bg-[#000000]/80 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur-sm"
                        >
                            {{ story.badge }}
                        </span>
                    </div>
                </div>

                <!-- Patient & Medical Profile Card -->
                <div
                    class="space-y-3 rounded-[8px] border border-[#333333]/10 bg-[#edede2]/70 p-4"
                >
                    <div class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2">
                        <div class="flex items-start gap-2.5">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-white"
                            >
                                <User class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span
                                    class="block text-[10px] font-semibold tracking-wider text-[#333333]/60 uppercase"
                                >
                                    Nama Pasien
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.patient_name }}
                                    {{
                                        story.patient_age
                                            ? `(${story.patient_age})`
                                            : ''
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-white"
                            >
                                <Stethoscope class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span
                                    class="block text-[10px] font-semibold tracking-wider text-[#333333]/60 uppercase"
                                >
                                    Dokter Penanggung Jawab
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.doctor_name }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-white"
                            >
                                <HeartHandshake
                                    class="size-3.5 text-[#000000]"
                                />
                            </div>
                            <div>
                                <span
                                    class="block text-[10px] font-semibold tracking-wider text-[#333333]/60 uppercase"
                                >
                                    Diagnosa / Prosedur
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.diagnosis }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[#333333]/10 bg-white"
                            >
                                <Activity class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span
                                    class="block text-[10px] font-semibold tracking-wider text-[#333333]/60 uppercase"
                                >
                                    Unit Poliklinik
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.poli_name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emotional Quote Highlight -->
                <div
                    v-if="story.quote"
                    class="relative my-2 rounded-[8px] border-y border-r border-l-4 border-[#333333]/10 border-[#beedc0] bg-white p-4 sm:p-5"
                >
                    <Quote class="mb-1 size-6 text-[#beedc0] opacity-80" />
                    <p
                        class="font-serif text-sm leading-relaxed text-[#000000] italic sm:text-base"
                    >
                        {{ story.quote }}
                    </p>
                    <span
                        class="mt-2 block text-right text-[11px] font-semibold text-[#333333]"
                    >
                        — {{ story.patient_name }}
                    </span>
                </div>

                <!-- Full Story Narrative Paragraphs -->
                <div
                    class="space-y-4 py-2 text-xs leading-relaxed text-[#333333] sm:text-sm"
                >
                    <p
                        v-for="(para, idx) in paragraphs"
                        :key="idx"
                        :class="{
                            'text-sm leading-relaxed font-medium text-[#000000] sm:text-base':
                                idx === 0,
                        }"
                    >
                        {{ para }}
                    </p>
                </div>

                <!-- Dialog Action Footer -->
                <DialogFooter
                    class="flex flex-col items-center justify-between gap-3 border-t border-[#333333]/15 pt-4 sm:flex-row"
                >
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="inline-flex min-h-[44px] w-full cursor-pointer items-center justify-center rounded-[40.5px] border border-[#333333]/20 bg-white px-5 py-2.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#edede2] sm:w-auto"
                    >
                        Tutup
                    </button>

                    <Link
                        :href="scheduleUrl"
                        class="inline-flex min-h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-[#333333] sm:w-auto"
                    >
                        <Stethoscope class="size-3.5 text-[#beedc0]" />
                        <span>Konsultasi dengan Dokter Terkait</span>
                        <ArrowRight class="size-3.5" />
                    </Link>
                </DialogFooter>
            </template>
        </DialogContent>
    </Dialog>
</template>
