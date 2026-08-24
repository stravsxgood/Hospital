<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { Team, User } from '@/types';

defineProps<{
    user?: User;
    team?: Team | null;
}>();

const getInitials = (name?: string) => {
    return (
        name
            ?.split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .substring(0, 2) || 'U'
    );
};
</script>

<template>
    <div class="flex items-center gap-2.5 overflow-hidden">
        <Avatar
            class="h-8 w-8 rounded-lg border border-[#333333]/15 bg-[#dcdccf]"
        >
            <!-- Render AvatarImage hanya jika user memiliki avatar -->
            <AvatarImage
                v-if="user?.avatar"
                :src="user.avatar"
                :alt="user.name"
            />
            <AvatarFallback
                class="rounded-lg bg-[#dcdccf] text-xs font-semibold text-[#18181b]"
            >
                {{ getInitials(user?.name) }}
            </AvatarFallback>
        </Avatar>
        <div
            class="grid flex-1 overflow-hidden text-left text-sm leading-tight"
        >
            <span class="truncate font-semibold text-[#111111]">{{
                user?.name || 'Pengguna'
            }}</span>
            <span class="truncate text-xs text-[#666660]">{{
                team ? team.name : user?.email
            }}</span>
        </div>
    </div>
</template>
