<template>
    <Link :href="route('browse.index', { type: type })">
        <span class="rounded bg-theme-accent px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-white">
            {{ label }}
        </span>
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { useTranslation } from '../Composables/useTranslation.js';
import { Link } from '@inertiajs/vue3';

const { t } = useTranslation();

const props = defineProps({
    type: { type: String, required: true },
});

const label = computed(() => props.type === 'tv' ? t('series') : t('movie'));
</script>
