<template>
    <Transition leave-active-class="transition-opacity duration-700 ease-out" leave-from-class="opacity-100"
        leave-to-class="opacity-0" @after-leave="$emit('loaded')">
        <div v-if="visible" class="fixed inset-0 z-[100] flex items-center justify-center bg-theme-darker">
            <div class="flex flex-col items-center">
                <h1
                    class="animate-loader-glow select-none text-5xl font-black tracking-[0.3em] text-white sm:text-7xl md:text-8xl">
                    theme
                </h1>
                <div class="mt-8 flex gap-2">
                    <span v-for="i in 3" :key="i" class="h-2 w-2 rounded-full bg-theme-accent" :style="{
                        animation: `loader-dot 1.4s ease-in-out ${(i - 1) * 0.16}s infinite`,
                    }"></span>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const visible = ref(true);

defineEmits(['loaded']);

onMounted(() => {
    setTimeout(() => {
        visible.value = false;
    }, 2200);
});
</script>
