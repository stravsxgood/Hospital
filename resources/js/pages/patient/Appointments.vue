<script setup lang="ts">
import MyAppointments from '@/pages/MyAppointments.vue';

defineProps<{
    appointments: any[];
}>();
</script>

<template>
    <MyAppointments :appointments="appointments" />
</template>
