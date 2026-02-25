<template>
    <AppLayout :title="title" :footer="false">
        <div class="relative min-h-screen bg-theme-dark flex flex-col">
            <slot />
            <AuthBackground />
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from './AppLayout.vue';
import Card from '@/Components/Card.vue';
import AuthBackground from '@/Components/AuthBackground.vue';

defineProps({
    title: { type: String, required: true },
});
</script>
