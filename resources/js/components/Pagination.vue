<script setup lang="ts">
/**
 * @file Pagination.vue
 * @description Universal, accessible, and responsive dynamic pagination component.
 * Supports both:
 *  1. Direct TanStack Table instance (`:table="table"`) for 100% client-side dynamic pagination without any page reload.
 *  2. Inertia paginator response (`:pagination="data"`) with smooth router.get SPA navigation without full page reload.
 * Adheres to DESIGN.md (Evergreen theme) and GEMINI.md standards:
 *  - Colors: Linen Canvas (#edede2), Bone Card (#fffff3), Sage Mint (#beedc0), Ink Black (#000000)
 *  - Touch targets: Minimum 44px on mobile (min-h-[44px]), min 36px on desktop
 *  - Zero XSS risk (HTML entities decoded safely)
 */
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MoreHorizontal } from '@lucide/vue';
import { computed } from 'vue';
import type { PaginatedResponse, PaginationLink } from '@/types';

interface PaginationProps {
    table?: any;
    links?:
        | PaginationLink[]
        | Array<{ url: string | null; label: string; active: boolean }>;
    pagination?:
        | PaginatedResponse<any>
        | {
              data?: any[];
              links?:
                  | PaginationLink[]
                  | Array<{
                        url: string | null;
                        label: string;
                        active: boolean;
                    }>;
              current_page?: number;
              last_page?: number;
              total?: number;
              from?: number | null;
              to?: number | null;
              per_page?: number;
          }
        | null;
    itemName?: string;
}

const props = withDefaults(defineProps<PaginationProps>(), {
    table: undefined,
    links: undefined,
    pagination: null,
    itemName: 'data',
});

const isTableMode = computed<boolean>(() => Boolean(props.table));

/**
 * Resolves the current table state snapshot.
 * TanStack Table v9 uses atom-based reactivity — state is read from
 * `table.store.state` (reactive snapshot) instead of the removed `getState()` method.
 */
const tableState = computed(() => {
    if (!props.table) {
        return null;
    }

    // v9: table.store is a ReadonlyStore backed by Vue computed ref
    if (props.table.store?.state) {
        return props.table.store.state;
    }

    // Fallback: try legacy getState() for older versions
    if (typeof props.table.getState === 'function') {
        return props.table.getState();
    }

    return null;
});

const currentPage = computed<number>(() => {
    if (props.table) {
        return (tableState.value?.pagination?.pageIndex ?? 0) + 1;
    }

    return props.pagination?.current_page ?? 1;
});

const lastPage = computed<number>(() => {
    if (props.table) {
        return props.table.getPageCount?.() ?? 1;
    }

    return props.pagination?.last_page ?? 1;
});

const totalItems = computed<number>(() => {
    if (props.table) {
        return props.table.getFilteredRowModel?.().rows?.length ?? 0;
    }

    return props.pagination?.total ?? 0;
});

const fromItem = computed<number | null>(() => {
    if (props.table) {
        const total = totalItems.value;

        if (total === 0) {
            return 0;
        }

        const pageSize = tableState.value?.pagination?.pageSize ?? 10;

        return (currentPage.value - 1) * pageSize + 1;
    }

    return props.pagination?.from ?? null;
});

const toItem = computed<number | null>(() => {
    if (props.table) {
        const total = totalItems.value;
        const pageSize = tableState.value?.pagination?.pageSize ?? 10;

        return Math.min(currentPage.value * pageSize, total);
    }

    return props.pagination?.to ?? null;
});

const canPrevious = computed<boolean>(() => {
    if (props.table) {
        return Boolean(props.table.getCanPreviousPage?.());
    }

    return currentPage.value > 1;
});

const canNext = computed<boolean>(() => {
    if (props.table) {
        return Boolean(props.table.getCanNextPage?.());
    }

    return currentPage.value < lastPage.value;
});

// Ambil array link baik dari props.pagination.links atau props.links
const resolvedLinks = computed<PaginationLink[]>(() => {
    if (props.pagination?.links && Array.isArray(props.pagination.links)) {
        return props.pagination.links;
    }

    if (props.links && Array.isArray(props.links)) {
        return props.links;
    }

    return [];
});

/**
 * Smart windowed page numbers with ellipsis (e.g. 1, 2, ..., 5, 6, 7, ..., 20)
 * Prevents horizontal layout overflow when there are many pages.
 */
const tableVisiblePages = computed<(number | string)[]>(() => {
    const total = lastPage.value;
    const current = currentPage.value;

    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const pages: (number | string)[] = [];
    pages.push(1);

    if (current > 3) {
        pages.push('...');
    }

    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);

    for (let i = start; i <= end; i++) {
        if (!pages.includes(i)) {
            pages.push(i);
        }
    }

    if (current < total - 2) {
        if (!pages.includes('...')) {
            pages.push('...');
        }
    }

    if (!pages.includes(total)) {
        pages.push(total);
    }

    return pages;
});

/**
 * Normalisasi URL dari Laravel Paginator (misal: "http://localhost:8000/admin/users?page=2")
 * menjadi relative URL (misal: "/admin/users?page=2") agar tidak crash saat host / port
 * berbeda di lingkungan Docker, local proxy, atau Laragon.
 */
const normalizeUrl = (rawUrl: string | null): string | null => {
    if (!rawUrl) {
        return null;
    }

    try {
        if (typeof window !== 'undefined') {
            const parsed = new URL(rawUrl, window.location.origin);

            return parsed.pathname + parsed.search + parsed.hash;
        }

        return rawUrl;
    } catch {
        return rawUrl;
    }
};

/**
 * Dynamic SPA visit via Inertia router without full-page refresh
 */
const handleInertiaNavigate = (rawUrl: string | null) => {
    if (!rawUrl) {
        return;
    }

    const targetUrl = normalizeUrl(rawUrl);

    if (!targetUrl) {
        return;
    }

    router.get(
        targetUrl,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const handleTablePageClick = (item: number | string) => {
    if (typeof item === 'number' && props.table) {
        props.table.setPageIndex(item - 1);
    }
};

const handleTablePrevious = () => {
    if (props.table && canPrevious.value) {
        props.table.previousPage();
    }
};

const handleTableNext = () => {
    if (props.table && canNext.value) {
        props.table.nextPage();
    }
};

const isPreviousLink = (label: string): boolean => {
    const clean = label.toLowerCase();

    return (
        clean.includes('previous') ||
        clean.includes('&laquo;') ||
        clean.includes('«') ||
        clean.includes('sebelumnya')
    );
};

const isNextLink = (label: string): boolean => {
    const clean = label.toLowerCase();

    return (
        clean.includes('next') ||
        clean.includes('&raquo;') ||
        clean.includes('»') ||
        clean.includes('berikutnya') ||
        clean.includes('selanjutnya')
    );
};

const cleanLabel = (label: string): string => {
    return label.replace(/&laquo;|&raquo;|«|»/g, '').trim();
};
</script>

<template>
    <nav
        v-if="lastPage > 1 || (resolvedLinks.length > 0 && lastPage > 1)"
        aria-label="Navigasi Halaman"
        class="flex flex-col items-center justify-between gap-3 font-['Rubik'] text-xs text-[#333333] sm:flex-row"
    >
        <!-- Info Ringkasan Data Halaman -->
        <div class="text-[#333333]/80">
            <template
                v-if="totalItems > 0 && fromItem !== null && toItem !== null"
            >
                Menampilkan
                <strong class="font-semibold text-[#000000]">{{
                    fromItem
                }}</strong>
                -
                <strong class="font-semibold text-[#000000]">{{
                    toItem
                }}</strong>
                dari
                <strong class="font-semibold text-[#000000]">{{
                    totalItems
                }}</strong>
                {{ itemName }}
                (Halaman {{ currentPage }} dari {{ lastPage }})
            </template>
            <template v-else>
                Halaman
                <strong class="font-semibold text-[#000000]">{{
                    currentPage
                }}</strong>
                dari
                <strong class="font-semibold text-[#000000]">{{
                    lastPage
                }}</strong>
            </template>
        </div>

        <!-- MODE 1: TanStack Table Direct Dynamic Pagination -->
        <div v-if="isTableMode" class="flex flex-wrap items-center gap-1">
            <!-- Tombol Sebelumnya -->
            <button
                type="button"
                v-if="canPrevious"
                @click="handleTablePrevious"
                aria-label="Halaman Sebelumnya"
                class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center gap-1 rounded-lg border border-[#333333]/15 bg-[#ffffff] px-3 py-1.5 text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:min-h-[36px] sm:min-w-[36px]"
            >
                <ChevronLeft class="size-4 shrink-0" />
                <span class="hidden sm:inline">Sebelumnya</span>
            </button>
            <span
                v-else
                aria-disabled="true"
                class="inline-flex min-h-[44px] min-w-[44px] cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-[#333333]/10 bg-[#edede2]/40 px-3 py-1.5 text-xs font-medium text-[#333333]/40 opacity-50 select-none sm:min-h-[36px] sm:min-w-[36px]"
            >
                <ChevronLeft class="size-4 shrink-0" />
                <span class="hidden sm:inline">Sebelumnya</span>
            </span>

            <!-- Nomor Halaman -->
            <template v-for="(item, idx) in tableVisiblePages" :key="idx">
                <span
                    v-if="item === '...'"
                    class="inline-flex min-h-[44px] min-w-[36px] items-center justify-center px-1 text-xs text-[#333333]/50 select-none sm:min-h-[36px]"
                >
                    <MoreHorizontal class="size-4" />
                </span>
                <button
                    v-else
                    type="button"
                    @click="handleTablePageClick(item)"
                    :aria-label="`Buka halaman ${item}`"
                    :aria-current="currentPage === item ? 'page' : undefined"
                    class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-lg px-3 py-1 text-xs font-medium transition-all select-none sm:min-h-[36px] sm:min-w-[36px]"
                    :class="
                        currentPage === item
                            ? 'bg-[#000000] font-semibold text-[#ffffff] shadow-sm'
                            : 'bg-[#edede2]/60 text-[#000000] hover:bg-[#edede2]'
                    "
                >
                    <span>{{ item }}</span>
                </button>
            </template>

            <!-- Tombol Selanjutnya -->
            <button
                type="button"
                v-if="canNext"
                @click="handleTableNext"
                aria-label="Halaman Selanjutnya"
                class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center gap-1 rounded-lg border border-[#333333]/15 bg-[#ffffff] px-3 py-1.5 text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:min-h-[36px] sm:min-w-[36px]"
            >
                <span class="hidden sm:inline">Selanjutnya</span>
                <ChevronRight class="size-4 shrink-0" />
            </button>
            <span
                v-else
                aria-disabled="true"
                class="inline-flex min-h-[44px] min-w-[44px] cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-[#333333]/10 bg-[#edede2]/40 px-3 py-1.5 text-xs font-medium text-[#333333]/40 opacity-50 select-none sm:min-h-[36px] sm:min-w-[36px]"
            >
                <span class="hidden sm:inline">Selanjutnya</span>
                <ChevronRight class="size-4 shrink-0" />
            </span>
        </div>

        <!-- MODE 2: Inertia Links Dynamic Navigation -->
        <div v-else class="flex flex-wrap items-center gap-1">
            <template v-for="(link, idx) in resolvedLinks" :key="idx">
                <!-- Case 1: Tombol Sebelumnya (Previous) -->
                <template v-if="isPreviousLink(link.label)">
                    <button
                        type="button"
                        v-if="link.url"
                        @click="handleInertiaNavigate(link.url)"
                        aria-label="Halaman Sebelumnya"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center gap-1 rounded-lg border border-[#333333]/15 bg-[#ffffff] px-3 py-1.5 text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:min-h-[36px] sm:min-w-[36px]"
                    >
                        <ChevronLeft class="size-4 shrink-0" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>
                    <span
                        v-else
                        aria-disabled="true"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-[#333333]/10 bg-[#edede2]/40 px-3 py-1.5 text-xs font-medium text-[#333333]/40 opacity-50 select-none sm:min-h-[36px] sm:min-w-[36px]"
                    >
                        <ChevronLeft class="size-4 shrink-0" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </span>
                </template>

                <!-- Case 2: Tombol Selanjutnya (Next) -->
                <template v-else-if="isNextLink(link.label)">
                    <button
                        type="button"
                        v-if="link.url"
                        @click="handleInertiaNavigate(link.url)"
                        aria-label="Halaman Selanjutnya"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center gap-1 rounded-lg border border-[#333333]/15 bg-[#ffffff] px-3 py-1.5 text-xs font-medium text-[#000000] transition-colors hover:bg-[#edede2] sm:min-h-[36px] sm:min-w-[36px]"
                    >
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <ChevronRight class="size-4 shrink-0" />
                    </button>
                    <span
                        v-else
                        aria-disabled="true"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-[#333333]/10 bg-[#edede2]/40 px-3 py-1.5 text-xs font-medium text-[#333333]/40 opacity-50 select-none sm:min-h-[36px] sm:min-w-[36px]"
                    >
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <ChevronRight class="size-4 shrink-0" />
                    </span>
                </template>

                <!-- Case 3: Pemisah Titik Tiga (...) -->
                <template v-else-if="link.label.includes('...')">
                    <span
                        class="inline-flex min-h-[44px] min-w-[36px] items-center justify-center px-1 text-xs text-[#333333]/50 select-none sm:min-h-[36px]"
                    >
                        <MoreHorizontal class="size-4" />
                    </span>
                </template>

                <!-- Case 4: Nomor Halaman Numerik -->
                <template v-else>
                    <button
                        type="button"
                        v-if="link.url"
                        @click="handleInertiaNavigate(link.url)"
                        :aria-label="`Buka halaman ${cleanLabel(link.label)}`"
                        :aria-current="link.active ? 'page' : undefined"
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-pointer items-center justify-center rounded-lg px-3 py-1 text-xs font-medium transition-all select-none sm:min-h-[36px] sm:min-w-[36px]"
                        :class="
                            link.active
                                ? 'bg-[#000000] font-semibold text-[#ffffff] shadow-sm'
                                : 'bg-[#edede2]/60 text-[#000000] hover:bg-[#edede2]'
                        "
                    >
                        <span>{{ cleanLabel(link.label) }}</span>
                    </button>
                    <span
                        v-else
                        class="inline-flex min-h-[44px] min-w-[44px] cursor-not-allowed items-center justify-center rounded-lg px-3 py-1 text-xs text-[#333333]/40 opacity-50 select-none sm:min-h-[36px] sm:min-w-[36px]"
                    >
                        {{ cleanLabel(link.label) }}
                    </span>
                </template>
            </template>
        </div>
    </nav>
</template>
