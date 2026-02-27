<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2">
        <button v-show="visible"
            type="button"
            class="fixed bottom-8 right-8 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-theme-accent text-white shadow-lg shadow-theme-accent/25 transition hover:bg-theme-accent-hover focus:outline-none focus:ring-2 focus:ring-theme-accent focus:ring-offset-2 focus:ring-offset-theme-dark cursor-pointer"
            aria-label="Scroll to top"
            @click="scrollToTop">
            <ChevronUpIcon class="h-6 w-6" />
        </button>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronUpIcon } from '@heroicons/vue/24/solid';

const visible = ref(false);
const THRESHOLD = 300;

function updateVisibility() {
    visible.value = window.scrollY > THRESHOLD;
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => {
    updateVisibility();
    window.addEventListener('scroll', updateVisibility, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', updateVisibility);
});
</script>
