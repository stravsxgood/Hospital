<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';

declare const route: any;

defineProps<{
    user?: User;
}>();
</script>

<template>
    <!-- Profil di Dropdown -->
    <DropdownMenuLabel class="p-1 font-normal">
        <div class="px-1.5 py-1">
            <UserInfo :user="user" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator class="my-1 bg-[#333333]/10" />

    <!-- Opsi Pengaturan Akun -->
    <DropdownMenuGroup>
        <DropdownMenuItem
            as-child
            class="cursor-pointer !rounded-lg !px-2.5 !py-2 text-sm font-medium text-[#2d2d2d] transition-colors duration-150 hover:!bg-[#f4f4ec] hover:!text-[#111111] focus:!bg-[#f4f4ec] focus:!text-[#111111] data-[highlighted]:!bg-[#f4f4ec] data-[highlighted]:!text-[#111111]"
        >
            <Link
                :href="typeof route === 'function' ? route('profile.edit') : '/profile'"
                class="flex w-full items-center gap-2.5"
            >
                <Settings class="size-4 text-[#666660]" />
                <span>Pengaturan Akun</span>
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator class="my-1 bg-[#333333]/10" />

    <!-- Tombol Keluar -->
    <DropdownMenuItem
        as-child
        class="cursor-pointer !rounded-lg !px-2.5 !py-2 text-sm font-medium text-red-600 transition-colors duration-150 hover:!bg-red-50 hover:!text-red-700 focus:!bg-red-50 focus:!text-red-700 data-[highlighted]:!bg-red-50 data-[highlighted]:!text-red-700"
    >
        <Link
            :href="typeof route === 'function' ? route('logout') : '/logout'"
            method="post"
            as="button"
            class="flex w-full items-center gap-2.5"
        >
            <LogOut class="size-4 text-red-500" />
            <span>Keluar dari Akun</span>
        </Link>
    </DropdownMenuItem>
</template>