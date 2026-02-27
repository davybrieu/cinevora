<template>
    <AppLayout :title="t('catalog')" :description="t('page_header_browse_desc')">
        <div class="px-4 pb-20 pt-10 sm:px-8 md:px-16">
            <div class="mb-8 flex flex-wrap justify-end gap-2">
                <div class="browse-aside-panel rounded-xl border border-white/10 overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-theme-accent/15 ring-1 ring-theme-accent/20">
                                <FunnelIcon class="h-5 w-5 text-theme-accent" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold tracking-tight text-white">{{ t('browse_filters')
                                }}</h2>
                                <p class="text-xs text-white/45 mt-0.5">{{ t('catalog') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-4">
                        <!-- Clear all -->
                        <div v-if="hasActiveFilters" class="mb-4">
                            <Button variant="secondary" size="sm" class="w-full" @click="clearAllFilters">
                                <XMarkIcon class="h-3.5 w-3.5" />
                                {{ t('browse_clear_all') }}
                            </Button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <!-- Search -->
                            <FilterSection v-model:open="openSections.search" :title="t('browse_search')" icon="search">
                                <InputGroup id="browse-query" v-model="form.query" type="text"
                                    :placeholder="t('search_placeholder')" @update:modelValue="scheduleApply" />
                            </FilterSection>

                            <!-- Type -->
                            <FilterSection v-model:open="openSections.type" :title="t('browse_type')" icon="type">
                                <SelectGroup id="browse-type" v-model="form.type" @update:modelValue="scheduleApply">
                                    <option value="all">{{ t('all') }}</option>
                                    <option value="movie">{{ t('movie') }}</option>
                                    <option value="tv">{{ t('tv_series') }}</option>
                                </SelectGroup>
                            </FilterSection>

                            <!-- Watch Providers -->
                            <FilterSection v-model:open="openSections.providers" :title="t('streaming_providers')"
                                icon="tv">
                                <SelectGroup id="browse-provider" v-model="form.provider"
                                    :placeholder="t('browse_select_providers')" @update:modelValue="scheduleApply">
                                    <option v-for="p in providers.filter(pr => pr.id)" :key="p.id" :value="p.id">
                                        {{ p.name }}
                                    </option>
                                </SelectGroup>
                            </FilterSection>

                            <!-- Category -->
                            <FilterSection v-model:open="openSections.genres" :title="t('browse_category')"
                                icon="genre">
                                <div
                                    class="max-h-44 space-y-0.5 overflow-y-auto rounded-lg bg-white/[0.04] p-2 ring-1 ring-white/5">
                                    <label v-for="(name, id) in genres" :key="id"
                                        class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 text-sm text-white/75 transition hover:bg-white/5 hover:text-white">
                                        <input type="checkbox" :value="Number(id)" v-model="form.genres"
                                            class="rounded border-white/25 bg-white/5 text-theme-accent focus:ring-theme-accent focus:ring-offset-0 focus:ring-2"
                                            @change="scheduleApply" />
                                        <span class="truncate">{{ name }}</span>
                                    </label>
                                </div>
                            </FilterSection>

                            <!-- Release Date -->
                            <FilterSection v-model:open="openSections.date" :title="t('browse_release_date')"
                                icon="calendar">
                                <div class="space-y-2">
                                    <InputGroup id="browse-date-from" v-model="form.date_from" type="date"
                                        @update:modelValue="scheduleApply" />
                                    <InputGroup id="browse-date-to" v-model="form.date_to" type="date"
                                        @update:modelValue="scheduleApply" />
                                </div>
                            </FilterSection>

                            <!-- Language -->
                            <FilterSection v-model:open="openSections.language" :title="t('language')" icon="language">
                                <SelectGroup id="browse-language" v-model="form.language"
                                    :placeholder="t('browse_select_language')" @update:modelValue="scheduleApply">
                                    <option v-for="lang in languages" :key="lang.code || lang[0] || lang"
                                        :value="lang.code || lang[0] || lang">
                                        {{ lang.name }}
                                    </option>
                                </SelectGroup>
                            </FilterSection>

                            <!-- Rating -->
                            <FilterSection v-model:open="openSections.rating" :title="t('browse_rating')" icon="star">
                                <div class="space-y-2 pt-0.5">
                                    <input v-model.number="form.vote_min" type="range" min="0" max="10" step="0.5"
                                        class="browse-range h-2 w-full appearance-none rounded-full bg-white/10 accent-theme-accent"
                                        @input="scheduleApply" />
                                    <div class="flex justify-between text-xs text-white/40">
                                        <span>0</span>
                                        <span class="text-theme-accent font-semibold">
                                            {{ (form.vote_min || 0) + '+' }}
                                        </span>
                                        <span>10</span>
                                    </div>
                                </div>
                            </FilterSection>

                            <!-- Vote count -->
                            <FilterSection v-model:open="openSections.votes" :title="t('browse_vote_count_min')"
                                icon="votes">
                                <div class="space-y-2 pt-0.5">
                                    <input v-model.number="form.vote_count_min" type="range" min="0" max="500" step="50"
                                        class="browse-range h-2 w-full appearance-none rounded-full bg-white/10 accent-theme-accent"
                                        @input="scheduleApply" />
                                    <div class="flex justify-between text-xs text-white/40">
                                        <span>0</span>
                                        <span class="text-theme-accent font-semibold">
                                            {{ (form.vote_count_min || 0) + '+' }}
                                        </span>
                                        <span>500</span>
                                    </div>
                                </div>
                            </FilterSection>
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div
                    class="flex flex-col items-center w-full sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                    <p class="py-2 text-sm text-white/50 text-center w-full sm:text-left sm:flex-1 sm:min-w-0">
                        {{ t('browse_showing') }} {{ totalLoaded }} {{ t('browse_of') }} {{ totalResultsLabel }}
                    </p>
                    <div class="w-full flex justify-center sm:justify-end sm:w-auto sm:flex-1 min-w-[180px]">
                        <SelectGroup id="browse-sort" v-model="form.sort" @update:modelValue="scheduleApply">
                            <option v-for="(labelKey, value) in sortOptions" :key="value" :value="value">
                                {{ t(labelKey) }}
                            </option>
                        </SelectGroup>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">
                    <MovieCard v-for="item in allItems" :key="`${item.media_type}-${item.id}`" :item="item"
                        :card-width="null" class="!w-full" />
                </div>

                <div class="flex w-full justify-center py-10 text-center">
                    <div ref="sentinel">
                        <div v-if="loading" class="flex items-center justify-center gap-2 text-sm text-white/40">
                            <Spinner />
                            {{ t('loading') }}
                        </div>
                        <p v-else-if="page >= totalPages && allItems.length > 0"
                            class="text-center text-xs text-white/20">
                            {{ allItems.length }} {{ t('results_displayed') }}
                        </p>
                    </div>

                    <div v-if="allItems.length === 0 && !loading">
                        <p class="text-lg text-theme-text-muted">{{ t('no_results') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { useInfiniteScroll } from '../Composables/useInfiniteScroll.js';
import AppLayout from '../Layouts/AppLayout.vue';
import MovieCard from '../Components/MovieCard.vue';
import Spinner from '../Components/Spinner.vue';
import FilterSection from '../Components/Browse/FilterSection.vue';
import InputGroup from '../Components/InputGroup.vue';
import SelectGroup from '../Components/SelectGroup.vue';
import {
    FunnelIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import Button from '@/Components/Button.vue';
const { t } = useTranslation();

const props = defineProps({
    items: { type: Array, required: true },
    currentPage: { type: Number, required: true },
    totalPages: { type: Number, required: true },
    totalResults: { type: Number, default: 0 },
    filters: { type: Object, required: true },
    providers: { type: Array, required: true },
    genres: { type: Object, required: true },
    languages: { type: Object, required: true },
    sortOptions: { type: Object, required: true },
});

const allItems = ref([...props.items]);
const page = ref(props.currentPage);
const totalPages = ref(props.totalPages);
const totalResultsCount = ref(props.totalResults);

const DEBOUNCE_MS = 400;
let debounceTimer = null;

const openSections = ref({
    search: true,
    type: true,
    providers: true,
    genres: true,
    date: true,
    language: true,
    rating: true,
    votes: true,
    sort: true,
});

const form = ref({
    query: props.filters.query ?? '',
    type: props.filters.type ?? 'all',
    provider: props.filters.provider ?? '',
    genres: Array.isArray(props.filters.genres) ? [...props.filters.genres] : [],
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    language: props.filters.language ?? '',
    vote_min: props.filters.vote_min ?? 0,
    vote_count_min: props.filters.vote_count_min ?? 0,
    sort: props.filters.sort ?? 'popularity_desc',
});

const totalLoaded = computed(() => allItems.value.length);
const totalResultsLabel = computed(() => {
    const n = totalResultsCount.value;
    if (n >= 1000000) return (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    return n.toLocaleString();
});

const hasActiveFilters = computed(() => {
    const f = form.value;
    return !!(
        (f.query && f.query.trim()) ||
        f.type !== 'all' ||
        f.provider ||
        (Array.isArray(f.genres) && f.genres.length > 0) ||
        f.date_from ||
        f.date_to ||
        f.language ||
        (f.vote_min !== '' && f.vote_min != null && Number(f.vote_min) > 0) ||
        (f.vote_count_min !== '' && f.vote_count_min != null && Number(f.vote_count_min) > 0) ||
        f.sort !== 'popularity_desc'
    );
});

watch(() => [props.items, props.currentPage, props.totalPages, props.totalResults], () => {
    if (props.currentPage === 1) {
        allItems.value = [...props.items];
    }
    page.value = props.currentPage;
    totalPages.value = props.totalPages;
    totalResultsCount.value = props.totalResults;
}, { deep: true });

watch(() => props.filters, (newFilters) => {
    form.value = {
        query: newFilters.query ?? '',
        type: newFilters.type ?? 'all',
        provider: newFilters.provider ?? '',
        genres: Array.isArray(newFilters.genres) ? [...newFilters.genres] : [],
        date_from: newFilters.date_from ?? '',
        date_to: newFilters.date_to ?? '',
        language: newFilters.language ?? '',
        vote_min: newFilters.vote_min ?? 0,
        vote_count_min: newFilters.vote_count_min ?? 0,
        sort: newFilters.sort ?? 'popularity_desc',
    };
}, { deep: true });

function buildQuery() {
    const q = {};
    if (form.value.query) q.query = form.value.query;
    if (form.value.type) q.type = form.value.type;
    if (form.value.provider) q.provider = form.value.provider;
    if (form.value.genres?.length) q.genres = form.value.genres.join(',');
    if (form.value.date_from) q.date_from = form.value.date_from;
    if (form.value.date_to) q.date_to = form.value.date_to;
    if (form.value.language) q.language = form.value.language;
    if (form.value.vote_min !== '' && form.value.vote_min != null && Number(form.value.vote_min) > 0) q.vote_min = form.value.vote_min;
    if (form.value.vote_count_min !== '' && form.value.vote_count_min != null && Number(form.value.vote_count_min) > 0) q.vote_count_min = form.value.vote_count_min;
    if (form.value.sort) q.sort = form.value.sort;
    return q;
}

function applyFilters() {
    clearTimeout(debounceTimer);
    router.get(route('browse.index'), buildQuery(), { preserveState: false });
}

function scheduleApply() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, DEBOUNCE_MS);
}

function clearAllFilters() {
    form.value = {
        query: '',
        type: 'all',
        provider: '',
        genres: [],
        date_from: '',
        date_to: '',
        language: '',
        vote_min: 0,
        vote_count_min: 0,
        sort: 'popularity_desc',
    };
    router.get(route('browse.index'), {}, { preserveState: false, preserveScroll: true });
}

async function loadMore() {
    if (page.value >= totalPages.value) return;
    const nextPage = page.value + 1;
    const params = new URLSearchParams(buildQuery());
    params.set('page', String(nextPage));

    const response = await fetch(`${route('browse.loadMore')}?${params}`);
    const data = await response.json();
    allItems.value.push(...data.items);
    page.value = data.currentPage;
    totalPages.value = data.totalPages;
    if (data.totalResults != null) totalResultsCount.value = data.totalResults;
}

const { sentinel, loading } = useInfiniteScroll(loadMore);

onUnmounted(() => {
    clearTimeout(debounceTimer);
});
</script>
