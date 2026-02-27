<template>
    <div>
        <Transition name="panel">
            <div v-if="modelValue"
                class="absolute inset-y-0 right-0 z-500 w-full max-w-md flex flex-col bg-black/95 border-l border-theme-accent/10 backdrop-blur-xl shadow-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-theme-accent/10">
                    <h2 class="text-white font-semibold text-lg tracking-tight">{{ title }}</h2>
                    <button type="button" class="cursor-pointer text-white/60 hover:text-white transition-colors"
                        @click="$emit('update:modelValue', false)">
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <slot />
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="modelValue && closeOnBackdrop" class="absolute inset-0 z-40"
                @click="$emit('update:modelValue', false)" />
        </Transition>
    </div>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: '' },
    closeOnBackdrop: { type: Boolean, default: true },
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.panel-enter-active {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
}

.panel-leave-active {
    transition: transform 0.3s cubic-bezier(0.7, 0, 0.84, 0), opacity 0.2s ease;
}

.panel-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.panel-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
