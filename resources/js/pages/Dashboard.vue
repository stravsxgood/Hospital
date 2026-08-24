<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';

defineProps<{
    pendingInvitations?: Array<{
        code: string;
        inviterName: string;
        team: {
            name: string;
            slug: string;
        };
    }>;
}>();
</script>

<template>
    <AppSidebarLayout>
        <Head title="Dashboard" />

        <div
            class="min-h-screen bg-[#edede2] p-6 font-['Rubik'] text-[#000000]"
        >
            <div
                class="mx-auto max-w-4xl rounded-2xl border border-[#333333]/15 bg-[#fffff3] p-6 shadow-sm"
            >
                <h1 class="font-['ivypresto-headline'] text-2xl font-bold">
                    Dashboard
                </h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Selamat datang di SIMRS Hospital Population.
                </p>

                <div
                    v-if="pendingInvitations && pendingInvitations.length > 0"
                    class="mt-6 space-y-3"
                >
                    <h2 class="text-base font-semibold">Undangan Tim</h2>
                    <div
                        v-for="invitation in pendingInvitations"
                        :key="invitation.code"
                        class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-4"
                    >
                        <div>
                            <div class="font-semibold">
                                {{ invitation.team.name }}
                            </div>
                            <div class="text-xs text-neutral-500">
                                Diundang oleh {{ invitation.inviterName }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
