<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, Settings } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';

declare const route: any;

const props = defineProps<{
    user?: User;
}>();

const page = usePage();
const currentUser = computed(() => props.user || page.props.auth?.user);

/**
 * Pengaturan akun hanya diizinkan untuk pengguna dengan role Pasien.
 * Pengguna dengan role Dokter, Perawat (pekerja tetap maupun koas), dan Admin
 * tidak memiliki tombol Pengaturan Akun di menu navigasi.
 */
const isPatient = computed((): boolean => {
    const user = currentUser.value as any;

    if (!user) {
        return false;
    }

    if (Boolean(user.is_admin)) {
        return false;
    }

    // Role yang dikecualikan dari menu pengaturan akun
    const excludedRoles = [
        'doctor',
        'dpjp-doctor',
        'dokter',
        'dokter dpjp',
        'nurse',
        'perawat',
        'perawat-tetap',
        'perawat-koas',
        'koas',
        'koas-intern',
        'kasir',
        'admin',
        'super-admin',
        'super admin',
        'super_admin',
        'administrator',
        'staff',
        'staff-pekerja',
    ];

    const currentRole = (user.role ?? '').toLowerCase().trim();

    if (excludedRoles.includes(currentRole)) {
        return false;
    }

    if (
        user.roles?.some?.((r: any) => {
            const roleName = (typeof r === 'string' ? r : r.name)
                ?.toLowerCase?.()
                .trim();

            return excludedRoles.includes(roleName);
        })
    ) {
        return false;
    }

    if (
        Boolean(user.doctor) ||
        Boolean(user.is_doctor) ||
        Boolean(user.nurse)
    ) {
        return false;
    }

    // Cek jika pengguna adalah pasien
    return (
        currentRole === 'patient' ||
        user.roles?.some?.((r: any) => {
            const roleName = (
                typeof r === 'string' ? r : r.name
            )?.toLowerCase?.();

            return ['patient', 'pasien'].includes(roleName);
        }) ||
        Boolean(user.resident_n) ||
        Boolean(user.patient)
    );
});
</script>

<template>
    <!-- Profil di Dropdown -->
    <DropdownMenuLabel class="p-1 font-normal">
        <div class="px-1.5 py-1">
            <UserInfo :user="user" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator class="my-1 bg-[#333333]/10" />

    <!-- Opsi Pengaturan Akun (Khusus Role Pasien) -->
    <template v-if="isPatient">
        <DropdownMenuGroup>
            <DropdownMenuItem
                as-child
                class="cursor-pointer !rounded-lg !px-2.5 !py-2 text-sm font-medium text-[#2d2d2d] transition-colors duration-150 hover:!bg-[#f4f4ec] hover:!text-[#111111] focus:!bg-[#f4f4ec] focus:!text-[#111111] data-[highlighted]:!bg-[#f4f4ec] data-[highlighted]:!text-[#111111]"
            >
                <Link
                    :href="
                        typeof route === 'function'
                            ? route('profile.edit')
                            : '/profile'
                    "
                    class="flex w-full items-center gap-2.5"
                >
                    <Settings class="size-4 text-[#666660]" />
                    <span>Pengaturan Akun</span>
                </Link>
            </DropdownMenuItem>
        </DropdownMenuGroup>

        <DropdownMenuSeparator class="my-1 bg-[#333333]/10" />
    </template>

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
