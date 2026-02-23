<template>
    <span class="flex items-center gap-1 text-yellow-400" :class="sizeClass">
        <StarIcon class="fill-current" :class="iconClass" />
        {{ Number(value).toFixed(1) }}
        <span class="text-white/30" v-if="count"> ({{ count }})</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';
import { StarIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    value: { type: [Number, String], required: true },
    size: { type: String, default: 'sm' },
    count: { type: [Number, String], default: null },
});

const sizeClass = computed(() => ({
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
}[props.size] ?? 'text-sm'));

const iconClass = computed(() => ({
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
}[props.size] ?? 'h-4 w-4'));
</script>
