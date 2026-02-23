<template>
    <span class="inline-flex items-center rounded-full border font-medium" :class="[sizeClass, variantClass]">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    size: { type: String, default: 'sm' },
    variant: { type: String, default: 'ghost' },
});

const sizeClass = computed(() => ({
    xs: 'px-2 py-0.5 text-[10px]',
    sm: 'px-2.5 py-0.5 text-xs',
    md: 'px-3 py-1 text-xs',
    lg: 'px-4 py-1.5 text-sm',
}[props.size] ?? 'px-2.5 py-0.5 text-xs'));

const variantClass = computed(() => ({
    ghost: 'border-white/10 bg-white/5 text-white/60',
    accent: 'border-theme-accent/30 bg-theme-accent/10 text-theme-accent',
    solid: 'border-transparent bg-white/15 text-white',
    outline: 'border-white/20 bg-transparent text-white/50',
}[props.variant] ?? 'border-white/10 bg-white/5 text-white/60'));
</script>
