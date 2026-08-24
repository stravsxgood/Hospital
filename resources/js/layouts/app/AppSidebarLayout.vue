<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden bg-[#edede2] min-h-screen text-[#000000] flex flex-col">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1 w-full bg-[#edede2]">
                <slot />
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
