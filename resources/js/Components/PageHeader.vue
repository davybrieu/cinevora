<template>
    <header class="mt-20 mb-6 sm:mt-30 sm:mb-8">
        <div class="relative z-10 flex flex-col items-center justify-center px-4 text-center sm:px-8">
            <h1 class="font-bold text-2xl sm:text-3xl md:text-4xl text-white mb-3 sm:mb-4">{{ title }}</h1>
            <p v-if="description" class="text-sm sm:text-base md:text-lg text-theme-text-muted">{{ description }}</p>
        </div>
    </header>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>