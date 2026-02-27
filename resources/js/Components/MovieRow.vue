<template>
    <div class="movie-row group/row relative py-4">
        <SectionTitle :title="title" :count="count" class="px-4 sm:px-8 md:px-16">
            <template #action v-if="slug">
                <Link :href="route('browse.show', { category: slug })"
                    class="flex items-center gap-1 text-sm text-theme-text-muted transition-colors hover:text-white">
                    <span>{{ t('see_more') }}</span>
                    <ChevronRightIcon class="h-4 w-4" />
                </Link>
            </template>
        </SectionTitle>

        <ScrollRow section-class="movie-row" v-slot="{ wasDragged }">
            <MovieCard v-for="item in items" :key="item.id" :item="item" :card-width="cardWidth"
                :class="{ 'pointer-events-none': wasDragged }" />
        </ScrollRow>
    </div>
</template>

<script setup>
import { ChevronRightIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import SectionTitle from './SectionTitle.vue';
import MovieCard from './MovieCard.vue';
import ScrollRow from './ScrollRow.vue';

const { t } = useTranslation();

defineProps({
    title: { type: String, required: true },
    slug: { type: String, default: null },
    items: { type: Array, required: true },
    count: { type: Number, default: null },
    cardWidth: { type: Number, default: 180 },
});
</script>
