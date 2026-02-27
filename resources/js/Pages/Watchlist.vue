<template>
    <AppLayout :title="t('my_watchlist')" :description="t('page_header_watchlist_desc')">
        <div class="px-4 pb-20 pt-10 sm:px-8 md:px-16">
            <div
                v-if="items.length"
                class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7"
            >
                <MovieCard v-for="item in items" :key="item.id" :item="item" :card-width="null" class="!w-full" />
            </div>

            <div v-else class="py-20 text-center">
                <HeartIcon class="mx-auto mb-4 h-16 w-16 text-white/20" />
                <p class="text-lg text-theme-text-muted">{{ t('watchlist_empty') }}</p>
                <p class="mt-2 text-sm text-white/40">{{ t('watchlist_empty_hint') }}</p>
                <Button :href="route('home')" variant="primary" size="md" class="mt-6">
                    {{ t('browse_catalog') }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '../Layouts/AppLayout.vue';
import MovieCard from '../Components/MovieCard.vue';
import Button from '../Components/Button.vue';
import { HeartIcon } from '@heroicons/vue/24/outline';

const { t } = useTranslation();

defineProps({
    items: { type: Array, required: true },
});
</script>
