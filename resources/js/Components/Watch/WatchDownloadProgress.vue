<template>
    <div class="flex items-center justify-center h-full flex-col gap-6 max-w-lg mx-auto px-6">
        <Spinner class="w-10 h-10 text-theme-accent" />
        <p class="text-white text-xl font-semibold tracking-tight text-center">{{ status.step }}</p>

        <div class="w-full space-y-2">
            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-theme-accent to-theme-accent-hover rounded-full transition-all duration-500 ease-out"
                    :style="{ width: `${status.progress ?? 0}%` }" />
            </div>
            <div class="flex items-center justify-between text-xs text-white/40">
                <span v-if="status.progress !== null" class="text-theme-accent/80 font-medium">{{ status.progress
                    }}%</span>
                <div class="flex gap-3">
                    <span v-if="status.downloaded">{{ status.downloaded }}</span>
                    <span v-if="status.speed">{{ status.speed }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Spinner from '@/Components/Spinner.vue';

defineProps({
    status: {
        type: Object,
        required: true,
    },
});
</script>
