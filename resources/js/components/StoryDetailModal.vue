<script setup lang="ts">
/**
 * @file StoryDetailModal.vue
 * @description Presentational modal component to display complete patient story narrative,
 * medical diagnosis details, specialist doctor info, and direct appointment CTA.
 * Strictly adheres to DESIGN.md (Evergreen theme) and accessibility standards.
 */
import { Link } from '@inertiajs/vue3'
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
} from '@lucide/vue'
import { motion } from 'motion-v'
import { computed } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import type { PatientStory } from '@/types'

const props = defineProps<{
    open: boolean
    story: PatientStory | null
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
}>()

/**
 * Split full content into clean paragraphs
 */
const paragraphs = computed(() => {
    if (!props.story?.full_content) return []
    return props.story.full_content
        .split('\n\n')
        .map((p) => p.trim())
        .filter(Boolean)
})

/**
 * Generate direct link to doctor practice schedule based on polyclinic name
 */
const scheduleUrl = computed(() => {
    if (!props.story?.poli_name) return '/schedule-guest'
    return `/schedule-guest?poli=${encodeURIComponent(props.story.poli_name)}`
})
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="max-w-2xl max-h-[90vh] overflow-y-auto border border-[#333333]/20 bg-[#fffff3] p-6 sm:p-8 text-[#000000] shadow-2xl rounded-[12px] font-['Rubik'] scrollbar-thin"
        >
            <template v-if="story">
                <!-- Top Meta Header -->
                <DialogHeader class="space-y-3 text-left">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#333333]/10 pb-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#beedc0] px-3 py-0.5 text-xs font-semibold text-[#000000]">
                            <Activity class="size-3.5" />
                            {{ story.category }}
                        </span>

                        <div class="flex items-center gap-3 text-xs text-[#333333]/75 font-medium">
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

                    <DialogTitle class="font-['ivypresto-headline'] font-serif text-2xl sm:text-3xl font-bold leading-snug text-[#000000]">
                        {{ story.title }}
                    </DialogTitle>
                </DialogHeader>

                <!-- Featured Image (if available) -->
                <div v-if="story.image_url" class="relative overflow-hidden rounded-[8px] my-2 max-h-64 sm:max-h-72 w-full">
                    <img
                        :src="story.image_url"
                        :alt="story.title"
                        class="w-full h-full object-cover object-center"
                        loading="lazy"
                    />
                    <div v-if="story.badge" class="absolute bottom-3 left-3">
                        <span class="rounded-full bg-[#000000]/80 backdrop-blur-sm px-3 py-1 text-[11px] font-semibold text-white border border-white/20">
                            {{ story.badge }}
                        </span>
                    </div>
                </div>

                <!-- Patient & Medical Profile Card -->
                <div class="rounded-[8px] bg-[#edede2]/70 border border-[#333333]/10 p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white border border-[#333333]/10 shrink-0">
                                <User class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
                                    Nama Pasien
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.patient_name }} {{ story.patient_age ? `(${story.patient_age})` : '' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white border border-[#333333]/10 shrink-0">
                                <Stethoscope class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
                                    Dokter Penanggung Jawab
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.doctor_name }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white border border-[#333333]/10 shrink-0">
                                <HeartHandshake class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
                                    Diagnosa / Prosedur
                                </span>
                                <span class="font-semibold text-[#000000]">
                                    {{ story.diagnosis }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white border border-[#333333]/10 shrink-0">
                                <Activity class="size-3.5 text-[#000000]" />
                            </div>
                            <div>
                                <span class="text-[10px] text-[#333333]/60 uppercase tracking-wider block font-semibold">
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
                    class="relative rounded-[8px] bg-white border-l-4 border-[#beedc0] border-y border-r border-[#333333]/10 p-4 sm:p-5 my-2"
                >
                    <Quote class="size-6 text-[#beedc0] mb-1 opacity-80" />
                    <p class="font-serif italic text-sm sm:text-base text-[#000000] leading-relaxed">
                        {{ story.quote }}
                    </p>
                    <span class="block text-right text-[11px] font-semibold text-[#333333] mt-2">
                        — {{ story.patient_name }}
                    </span>
                </div>

                <!-- Full Story Narrative Paragraphs -->
                <div class="space-y-4 text-xs sm:text-sm text-[#333333] leading-relaxed py-2">
                    <p
                        v-for="(para, idx) in paragraphs"
                        :key="idx"
                        :class="{ 'font-medium text-[#000000] text-sm sm:text-base leading-relaxed': idx === 0 }"
                    >
                        {{ para }}
                    </p>
                </div>

                <!-- Dialog Action Footer -->
                <DialogFooter class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-[#333333]/15">
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center rounded-[40.5px] border border-[#333333]/20 bg-white px-5 py-2.5 text-xs font-semibold text-[#000000] hover:bg-[#edede2] transition-colors cursor-pointer"
                    >
                        Tutup
                    </button>

                    <Link
                        :href="scheduleUrl"
                        class="min-h-[44px] w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[40.5px] bg-[#000000] px-6 py-2.5 text-xs font-semibold text-white hover:bg-[#333333] transition-colors shadow-sm cursor-pointer whitespace-nowrap"
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
