<template>
    <header class="mt-30 mb-8">
        <div class="relative z-10 flex flex-col items-center justify-center text-center">
            <h1 class="font-bold text-4xl text-white mb-4">{{ title }}</h1>
            <p v-if="description" class="text-lg text-theme-text-muted">{{ description }}</p>
        </div>
    </header>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>