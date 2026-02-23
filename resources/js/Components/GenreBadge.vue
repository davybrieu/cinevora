<template>
    <div v-if="genres.length" class="flex flex-wrap" :class="gapClass">
        <Link
            v-for="genre in genres"
            :key="genre.id ?? genre"
            :href="route('browse.index', { genres: genre.id ?? genre })"
            class="inline-block"
            tabindex="-1"
        >
            <Badge :size="size" :variant="variant" class="cursor-pointer">
                {{ genre.name ?? genre }}
            </Badge>
        </Link>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from './Badge.vue';

const props = defineProps({
    genres: { type: Array, required: true },
    size: { type: String, default: 'sm' },
    variant: { type: String, default: 'ghost' },
    gap: { type: String, default: 'sm' },
});

const gapClass = computed(() => ({
    xs: 'gap-1',
    sm: 'gap-1.5',
    md: 'gap-2',
}[props.gap] ?? 'gap-1.5'));
</script>
