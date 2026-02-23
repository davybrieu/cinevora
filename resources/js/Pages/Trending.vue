<template>
    <AppLayout :title="t('trending')">
        <div class="px-8 pb-20 pt-24 md:px-16">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold text-white">{{ t('trending') }}</h1>

                <div class="flex flex-wrap gap-2">
                    <Button
                        v-for="time in timeOptions"
                        :key="time.value"
                        :variant="filters.time === time.value ? 'primary' : 'secondary'"
                        size="sm"
                        @click="updateFilter('time', time.value)"
                    >
                        {{ time.label }}
                    </Button>

                    <div class="mx-2 hidden h-8 w-px bg-white/10 sm:block"></div>

                    <Button
                        v-for="type in typeOptions"
                        :key="type.value"
                        :variant="filters.type === type.value ? 'primary' : 'secondary'"
                        size="sm"
                        @click="updateFilter('type', type.value)"
                    >
                        {{ type.label }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7">
                <MovieCard v-for="item in allItems" :key="item.id" :item="item" :card-width="null" class="!w-full" />
            </div>

            <div ref="sentinel" class="flex justify-center py-10">
                <div v-if="loading" class="flex items-center gap-2 text-sm text-white/40">
                    <Spinner />
                    {{ t('loading') }}
                </div>
                <p v-else-if="page >= totalPages && allItems.length > 0" class="text-xs text-white/20">
                    {{ allItems.length }} {{ t('results_displayed') }}
                </p>
            </div>

            <div v-if="allItems.length === 0 && !loading" class="py-20 text-center">
                <p class="text-lg text-theme-text-muted">{{ t('no_results') }}</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { useInfiniteScroll } from '../Composables/useInfiniteScroll.js';
import AppLayout from '../Layouts/AppLayout.vue';
import MovieCard from '../Components/MovieCard.vue';
import Spinner from '../Components/Spinner.vue';
import Button from '../Components/Button.vue';

const { t } = useTranslation();

const props = defineProps({
    items: { type: Array, required: true },
    totalResults: { type: Number, default: 0 },
    currentPage: { type: Number, default: 1 },
    totalPages: { type: Number, default: 1 },
    filters: { type: Object, required: true },
});

const allItems = ref([...props.items]);
const page = ref(props.currentPage);
const totalPages = ref(props.totalPages);

const timeOptions = computed(() => [
    { value: 'day', label: t('today') },
    { value: 'week', label: t('this_week') },
]);

const typeOptions = computed(() => [
    { value: 'all', label: t('all') },
    { value: 'movie', label: t('movies') },
    { value: 'tv', label: t('tv_series') },
]);

function updateFilter(key, value) {
    const newFilters = { ...props.filters, [key]: value };
    router.get(route('trending.index'), newFilters, {
        preserveState: false,
        preserveScroll: false,
    });
}

async function loadMore() {
    if (page.value >= totalPages.value) return;
    const nextPage = page.value + 1;
    const params = new URLSearchParams({
        page: nextPage,
        time: props.filters.time,
        type: props.filters.type,
    });

    const response = await fetch(`${route('trending.loadMore')}?${params}`);
    const data = await response.json();
    allItems.value.push(...data.items);
    page.value = data.currentPage;
    totalPages.value = data.totalPages;
}

const { sentinel, loading } = useInfiniteScroll(loadMore);
</script>
