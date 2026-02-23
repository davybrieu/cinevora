<template>
    <ArrowPathIcon class="animate-spin" :class="sizeClass" />
</template>

<script setup>
import { computed } from 'vue';
import { ArrowPathIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    size: { type: String, default: 'sm' },
});

const sizeClass = computed(() => ({
    xs: 'h-4 w-4',
    sm: 'h-5 w-5',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
}[props.size] ?? 'h-5 w-5'));
</script>
