<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import type { Team } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { isMobile, state } = useSidebar();
const currentTeam = computed(() => page.props.currentTeam as Team | null);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="w-full rounded-xl text-[#222222] transition-colors duration-150 hover:bg-[#fffff3] data-[state=open]:bg-[#fffff3]"
                    >
                        <UserInfo :user="user" :team="currentTeam" />
                        <ChevronsUpDown class="ml-auto size-4 text-[#777770]" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-xl border border-[#333333]/15 bg-white p-1.5 text-[#222222] shadow-xl"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'right'
                              : 'top'
                    "
                    align="end"
                    :side-offset="8"
                >
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
