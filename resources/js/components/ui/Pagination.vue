<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { PaginatedResponse, PaginationLink } from '@/types';

defineProps<{
    table?: any;
    links?: PaginationLink[];
    pagination?: PaginatedResponse<any> | any;
    itemName?: string;
}>();
</script>

<template>
    <Pagination
        :table="table"
        :links="links"
        :pagination="pagination"
        :item-name="itemName"
    />
</template>
