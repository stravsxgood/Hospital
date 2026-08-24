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
        <AppContent
            variant="sidebar"
            class="flex min-h-screen flex-col overflow-x-hidden bg-[#edede2] text-[#000000]"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="w-full flex-1 bg-[#edede2]">
                <slot />
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
