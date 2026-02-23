<template>
    <component :is="href ? Link : 'button'" :href="href" :type="href ? undefined : type"
        :disabled="disabled || processing"
        :class="[baseClass, variantClass, sizeClass, { 'w-full': full, 'opacity-50 pointer-events-none': disabled || processing }]"
        v-bind="$attrs">
        <span v-if="processing" class="inline-flex items-center justify-center gap-2 text-white">
            <Spinner size="xs" />
            <slot />
        </span>
        <template v-else>
            <slot />
        </template>
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Spinner from './Spinner.vue';

const props = defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    type: { type: String, default: 'button' },
    href: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    processing: { type: Boolean, default: false },
    full: { type: Boolean, default: false },
});

const baseClass = 'inline-flex items-center justify-center gap-2 font-semibold transition cursor-pointer text-white';

const variantClass = computed(() => ({
    primary: 'rounded-md bg-theme-accent text-white hover:bg-theme-accent-hover',
    secondary: 'rounded-md border border-white/20 text-white hover:bg-white/10 hover:border-white/40',
    ghost: 'rounded-md text-white/70 hover:bg-white/10 hover:text-white',
    danger: 'rounded-md bg-red-600 text-white hover:bg-red-500',
    text: 'text-theme-accent hover:underline',
    filter: 'rounded-full border',
}[props.variant] ?? ''));

const sizeClass = computed(() => {
    return {
        xs: 'h-8 px-2 text-xs',
        sm: 'h-10 px-4 text-sm',
        md: 'h-11 px-5 text-sm',
        lg: 'h-14 px-8 text-base font-bold',
    }[props.size] ?? 'h-11 px-5 text-sm';
});
</script>
