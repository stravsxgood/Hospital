<script setup lang="ts">
/**
 * @file ResponsiveTable.vue
 * @description Komponen Kontainer Tabel Responsif untuk Modul Super Admin.
 *              Menangani horizontal scrolling, filter slots, empty state, dan pagination.
 */
import { FileQuestion } from '@lucide/vue';

defineProps<{
    totalItems?: number;
    isEmpty?: boolean;
    emptyMessage?: string;
}>();
</script>

<template>
    <div class="space-y-4">
        <!-- 1. Filter & Search Action Bar Slot -->
        <div v-if="$slots.filters" class="w-full">
            <slot name="filters" />
        </div>

        <!-- 2. Responsive Table Card -->
        <div
            class="overflow-hidden rounded-3xl border border-[#000000]/10 bg-[#fffff3] shadow-xs"
        >
            <!-- Scrollable Table Container -->
            <div
                class="relative w-full scrollbar-thin scrollbar-thumb-[#000000]/15 scrollbar-track-transparent overflow-x-auto"
            >
                <table class="w-full text-left text-xs sm:text-sm">
                    <!-- Table Header -->
                    <thead
                        class="border-b border-[#000000]/10 bg-[#edede2]/80 text-[11px] font-bold tracking-wider text-[#000000] uppercase"
                    >
                        <slot name="header" />
                    </thead>

                    <!-- Table Body -->
                    <tbody
                        class="divide-y divide-[#000000]/5 font-medium text-[#000000]"
                    >
                        <slot />

                        <!-- Empty State Slot -->
                        <tr v-if="isEmpty">
                            <td
                                colspan="100"
                                class="px-4 py-12 text-center text-[#000000]/50"
                            >
                                <slot name="empty">
                                    <div
                                        class="flex flex-col items-center justify-center space-y-2"
                                    >
                                        <div
                                            class="flex size-12 items-center justify-center rounded-full bg-[#edede2] text-[#000000]/30"
                                        >
                                            <FileQuestion class="size-6" />
                                        </div>
                                        <p
                                            class="text-xs font-medium sm:text-sm"
                                        >
                                            {{
                                                emptyMessage ||
                                                'Tidak ada data yang ditemukan.'
                                            }}
                                        </p>
                                    </div>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 3. Pagination Footer Slot -->
            <div
                v-if="$slots.pagination"
                class="border-t border-[#000000]/10 bg-[#fffff3] px-4 py-3 sm:px-6"
            >
                <slot name="pagination" />
            </div>
        </div>
    </div>
</template>
