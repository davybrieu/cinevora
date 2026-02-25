<template>
    <div class="flex items-center justify-between" :class="$slots.action ? 'mb-3' : 'mb-5'">
        <h2 :class="sizeClass">
            {{ title }}
            <span v-if="count !== null" class="text-base font-normal text-white/30">({{ count }})</span>
        </h2>
        <slot name="action" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { ChevronRightIcon } from '@heroicons/vue/20/solid';

const { t } = useTranslation();

const props = defineProps({
    title: { type: String, required: true },
    count: { type: Number, default: null },
    size: { type: String, default: 'xl' },
});

const sizeClass = computed(() => ({
    md: 'text-lg font-bold text-white md:text-xl',
    lg: 'text-xl font-bold text-white',
    xl: 'text-2xl font-bold text-white',
    xxl: 'text-3xl font-bold text-white',
}[props.size] ?? 'text-xl font-bold text-white'));
</script>
