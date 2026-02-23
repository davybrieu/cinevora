<template>
    <AppLayout :title="title">
        <div class="relative min-h-screen bg-theme-dark flex flex-col">
            <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-8">
                <Card class="w-full max-w-md">
                    <h1 class="mb-3 text-2xl font-bold text-white">{{ heading }}</h1>
                    <p v-if="subtitle" class="mb-6 text-sm text-white/60">{{ subtitle }}</p>
                    <slot />
                </Card>
            </div>
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
    heading: { type: String, required: true },
    subtitle: { type: String, default: null },
});
</script>
