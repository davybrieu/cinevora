<template>
    <AppLayout :title="item.title">
        <!-- Hero backdrop en fond, hauteur conservée -->
        <div class="absolute top-0 left-0 w-full h-[55vh] sm:h-[65vh] md:h-[80vh] overflow-hidden z-0">
            <img v-if="item.backdrop_path" :src="item.backdrop_path" :alt="item.title"
                class="h-full w-full object-cover pointer-events-none" loading="lazy" />
            <div class="hero-gradient absolute inset-0"></div>
            <div class="hero-gradient-left absolute inset-0"></div>
        </div>

        <!-- Main info collé en bas du backdrop sur l'image -->
        <div class="relative z-10 flex items-end h-[55vh] sm:h-[65vh] md:h-[80vh]">
            <div class="w-full px-4 sm:px-8 md:px-16 pb-6 sm:pb-8">
                <div class="flex flex-col gap-4 sm:gap-6 md:gap-8 md:flex-row">
                    <div class="hidden sm:block w-36 flex-shrink-0 sm:w-40 md:w-48 lg:w-64">
                        <PosterImage :src="item.poster_path" :alt="item.title" class="aspect-[2/3] w-full shadow-2xl" />
                    </div>

                    <div class="flex-1">
                        <MovieInfo :item="item" />
                        <div class="mt-6 flex flex-wrap gap-3">
                            <Button :href="watchUrl" variant="primary" size="md">
                                <PlayIcon class="h-5 w-5" />
                                <template v-if="item.watch_progress_percentage">{{ t('continue') }}</template>
                                <template v-else>{{ t('watch') }}</template>
                            </Button>
                            <Button variant="secondary" size="md" :disabled="!item.videos.length" @click="playTrailer">
                                <FilmIcon class="h-5 w-5" />
                                <template v-if="item.videos.length">{{ t('watch_trailer') }}</template>
                                <template v-else>{{ t('no_trailer') }}</template>
                            </Button>
                            <WatchlistButton :item-id="item.id" :item-type="item.media_type" size="md"
                                button-class="rounded-full" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content en dessous du backdrop -->
        <div class="space-y-12 mb-12">

            <!-- Saisons/Episodes -->
            <section class="px-4 sm:px-8 md:px-16" v-if="item.media_type === 'tv' && item.seasons?.length">
                <div class="flex flex-col gap-4 mb-6">
                    <SectionTitle :title="t('seasons')" class="mb-0" />
                    <select v-model="selectedSeason" @change="loadEpisodes"
                        class="max-w-40 rounded-lg border border-white/20 bg-white/5 px-4 py-2 text-sm font-medium text-white focus:border-theme-primary focus:outline-none focus:ring-1 focus:ring-theme-primary">
                        <option v-for="season in seasonsList" :key="season.season_number" :value="season.season_number">
                            {{ season.name || (t('season') + ' ' + season.season_number) }}
                        </option>
                    </select>
                </div>

                <!-- Liste des épisodes -->
                <Card>
                    <p v-if="episodesLoading" class="py-8 text-center text-white/60">{{ t('loading') }}</p>
                    <div v-else-if="!episodes.length" class="py-8 text-center text-white/60">
                        {{ t('no_episodes') }}
                    </div>
                    <div v-else class="space-y-4">
                        <Link v-for="(ep, index) in episodes" :key="ep.id"
                            :href="route('watch.tv', { id: item.id, season: ep.season_number, episode: ep.episode_number })"
                            class="flex gap-3 sm:gap-4 rounded-lg p-2 sm:p-3 transition-colors hover:bg-white/5">
                            <div class="relative h-16 w-[100px] flex-shrink-0 overflow-hidden rounded-md bg-white/10 sm:h-20 sm:w-[140px]">
                                <img v-if="ep.still_path" :src="ep.still_path" :alt="ep.name"
                                    class="h-full w-full object-cover" loading="lazy" />
                                <div v-else class="flex h-full items-center justify-center text-white/30">
                                    <span class="text-2xl font-bold">{{ index + 1 }}</span>
                                </div>
                                <div v-if="ep.runtime"
                                    class="absolute bottom-1 right-1 rounded bg-black/80 px-1.5 py-0.5 text-xs text-white">
                                    {{ ep.runtime }}
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-white">
                                    {{ ep.episode_number }}. {{ ep.name || (t('episode') + ' ' + ep.episode_number) }}
                                </p>
                                <p v-if="ep.runtime" class="mt-0.5 text-xs text-white/50">{{ ep.runtime }}</p>
                                <p v-if="ep.overview" class="mt-1 line-clamp-2 text-sm text-white/70">{{ ep.overview }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </Card>
            </section>

            <!-- Details -->
            <section class="px-4 sm:px-8 md:px-16">
                <SectionTitle :title="t('details')" />
                <Card>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-x-8 gap-y-4">
                        <template v-if="item.media_type === 'movie'">
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('release_date') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.release_date || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('status') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.status || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('original_title') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.original_title || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('runtime') }}</div>
                                <div class="text-sm text-white">
                                    <span v-if="item.runtime">{{ item.runtime }}</span>
                                    <span v-else>—</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('budget') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.budget ? `$${Number(item.budget).toLocaleString()}` : '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('revenue') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.revenue ? `$${Number(item.revenue).toLocaleString()}` : '—' }}
                                </div>
                            </div>
                        </template>
                        <template v-else-if="item.media_type === 'tv'">
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('first_air_date') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.first_air_date || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('last_air_date') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.last_air_date || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('status') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.status || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('original_title') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.original_title || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('number_of_seasons') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.number_of_seasons || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-white/40 mb-1">{{ t('number_of_episodes') }}</div>
                                <div class="text-sm text-white">
                                    {{ item.number_of_episodes || '—' }}
                                </div>
                            </div>
                        </template>
                    </div>
                </Card>
            </section>

            <!-- Cast -->
            <CastRow :title="t('cast')" :count="item.cast.length" :cast="item.cast" />

            <!-- Crew -->
            <section v-if="item.crew.length" class="px-4 sm:px-8 md:px-16">
                <SectionTitle :title="t('crew')" :count="item.crew.length" />
                <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(members, job) in crewByJob" :key="job">
                        <p class="mb-3 text-sm text-white/40">{{ job }}</p>
                        <div class="space-y-2">
                            <div v-for="person in members" :key="person.id" class="flex items-center gap-3">
                                <Link :href="route('person.show', person.id)"
                                    class="flex-shrink-0 h-10 w-10 overflow-hidden transition-opacity hover:opacity-70">
                                    <PosterImage :src="person.profile_path" :alt="person.name" type="person"
                                        rounded="rounded" icon-class="h-4 w-4" class="h-full w-full" />
                                </Link>
                                <Link :href="route('person.show', person.id)"
                                    class="text-sm font-medium text-white transition-opacity hover:opacity-70">
                                    {{ person.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Videos -->
            <section v-if="item.videos.length">
                <SectionTitle :title="t('videos')" :count="item.videos.length" class="px-4 sm:px-8 md:px-16" />
                <VideoGallery ref="videoGalleryRef" :videos="item.videos" />
            </section>

            <section v-if="item.backdrops.length || item.posters.length">
                <SectionTitle :title="t('images')" :count="item.backdrops.length + item.posters.length"
                    class="px-4 sm:px-8 md:px-16" />

                <div class="flex flex-col gap-6">
                    <div v-if="item.backdrops.length">
                        <SectionSubTitle :title="t('backdrops')" :count="item.backdrops.length" class="px-4 sm:px-8 md:px-16" />
                        <ImageGallery :images="item.backdrops" type="backdrops" key-prefix="bd" />
                    </div>
                    <div v-if="item.posters.length">
                        <SectionSubTitle :title="t('posters')" :count="item.posters.length" class="px-4 sm:px-8 md:px-16" />
                        <ImageGallery :images="item.posters" type="posters" key-prefix="ps" />
                    </div>
                </div>
            </section>

            <!-- Reviews -->
            <section v-if="item.reviews.length" class="px-4 sm:px-8 md:px-16">
                <SectionTitle :title="t('reviews')" :count="item.reviews.length" />
                <div class="space-y-4">
                    <ReviewCard v-for="review in item.reviews" :key="review.id" :review="review" />
                </div>
            </section>

            <!-- Where to watch -->
            <section v-if="hasProviders" class="px-4 sm:px-8 md:px-16">
                <SectionTitle :title="t('where_to_watch')" />
                <Card>
                    <div class="space-y-6">
                        <template v-for="(providers, key) in providerSections" :key="key">
                            <div class="mb-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-white/40">
                                    {{ t(key) }}
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <div v-for="p in providers" :key="p.name" class="flex items-center gap-4">
                                        <a :href="item.providers.link" target="_blank" rel="noopener noreferrer">
                                            <img :src="p.logo" :alt="p.name" class="h-14 w-14 rounded-lg"
                                                loading="lazy" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <p class="text-xs text-theme-text-muted mt-6">
                            {{ t('data_provided_by_justwatch') }}
                        </p>
                    </div>
                </Card>
            </section>

            <!-- Similar -->
            <section v-if="item.similar.length">
                <MovieRow :title="t('similar')" :items="item.similar" />
            </section>

        </div>

    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '../Layouts/AppLayout.vue';
import MovieInfo from '../Components/MovieInfo.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import PosterImage from '../Components/PosterImage.vue';
import ImageGallery from '../Components/ImageGallery.vue';
import VideoGallery from '../Components/VideoGallery.vue';
import ReviewCard from '../Components/ReviewCard.vue';
import MovieRow from '@/Components/MovieRow.vue';
import CastRow from '@/Components/CastRow.vue';
import Button from '@/Components/Button.vue';
import WatchlistButton from '@/Components/WatchlistButton.vue';
import { FilmIcon } from '@heroicons/vue/24/outline';
import { PlayIcon } from '@heroicons/vue/24/solid';
import SectionSubTitle from '@/Components/SectionSubTitle.vue';
import Card from '@/Components/Card.vue';

const { t } = useTranslation();

const props = defineProps({
    item: { type: Object, required: true },
});

const hasProviders = computed(() => {
    const p = props.item.providers;
    return p.flatrate.length || p.rent.length || p.buy.length;
});

const providerSections = computed(() => {
    const sections = {};
    if (props.item.providers.flatrate.length) sections.streaming = props.item.providers.flatrate;
    if (props.item.providers.rent.length) sections.rent = props.item.providers.rent;
    if (props.item.providers.buy.length) sections.buy = props.item.providers.buy;
    return sections;
});

const crewByJob = computed(() => {
    return props.item.crew.reduce((groups, person) => {
        const job = person.job;
        if (!groups[job]) {
            groups[job] = [];
        }
        groups[job].push(person);
        return groups;
    }, {});
});

const videoGalleryRef = ref(null);

const watchUrl = computed(() => {
    if (props.item.media_type === 'movie') {
        return route('watch.movie', { id: props.item.id });
    }
    const season = props.item.resume_season ?? 1;
    const episode = props.item.resume_episode ?? 1;
    return route('watch.tv', { id: props.item.id, season, episode });
});

function playTrailer() {
    const trailerIdx = props.item.videos.findIndex(v => v.type === 'Trailer');
    const idx = trailerIdx >= 0 ? trailerIdx : 0;
    videoGalleryRef.value?.openVideo(idx);
}

// Saisons / épisodes (TV)
const selectedSeason = ref(1);
const episodes = ref([]);
const episodesLoading = ref(false);

const seasonsList = computed(() => {
    const seasons = props.item.seasons ?? [];
    return seasons.filter(s => (s.season_number ?? 0) >= 1);
});

async function loadEpisodes() {
    if (props.item.media_type !== 'tv' || !props.item.id) return;
    episodesLoading.value = true;
    try {
        const url = route('api.tv.season', { id: props.item.id, season: selectedSeason.value });
        const res = await fetch(url);
        const data = await res.json();
        episodes.value = data.episodes ?? [];
    } catch {
        episodes.value = [];
    } finally {
        episodesLoading.value = false;
    }
}

watch(selectedSeason, loadEpisodes, { immediate: false });
watch(() => props.item.id, () => {
    if (props.item.media_type === 'tv' && props.item.id) {
        selectedSeason.value = 1;
        loadEpisodes();
    }
}, { immediate: true });
</script>
