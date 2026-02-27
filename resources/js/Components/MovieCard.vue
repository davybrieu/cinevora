<template>
    <div>
        <div class="movie-card group relative flex-shrink-0 cursor-pointer overflow-hidden rounded-lg"
            :style="cardWidth ? { width: cardWidth + 'px' } : {}" @click="showDetail = true">
            <PosterImage :src="item.poster_path" :alt="item.title" rounded="rounded-lg" class="aspect-[3/5] w-full" />

            <!-- Top badges bar: Harmonize badge height -->
            <div
                class="absolute left-2 right-2 top-2 z-[2] flex flex-row items-start justify-between pointer-events-none">
                <!-- Type badge (top left) -->
                <span
                    class="min-h-[24px] flex items-center rounded-md bg-black/70 px-2 py-1 text-[11px] font-medium text-white backdrop-blur-sm">
                    {{ item.media_type === 'tv' ? t('series') : t('movie') }}
                </span>
                <!-- Rating badge (top right) -->
                <span
                    class="min-h-[24px] flex items-center gap-1 rounded-md bg-black/70 px-1.5 py-1 text-[11px] font-semibold text-white backdrop-blur-sm">
                    <StarIcon class="h-2.5 w-2.5 text-yellow-400" />
                    {{ Number(item.vote_average).toFixed(1) }}
                </span>
            </div>

            <!-- Watch progress bar (Netflix style) -->
            <div v-if="watchProgressPercent != null && watchProgressPercent > 0"
                class="absolute bottom-2 left-2 right-2 z-[2] h-1.5 overflow-hidden rounded-b-lg bg-white/20">
                <div class="h-full rounded-l-md bg-theme-accent transition-all duration-300"
                    :style="{ width: Math.min(100, watchProgressPercent) + '%' }" />
            </div>

            <!-- Hover overlay: play button -->
            <div
                class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                <PlayIcon class="h-12 w-12 text-white drop-shadow-lg" />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showDetail"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
                    @click.self="showDetail = false">
                    <Transition enter-active-class="transition-all duration-300 delay-75"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0" appear>
                        <div v-if="showDetail"
                            class="relative w-full max-w-[95vw] sm:max-w-xl md:max-w-2xl lg:max-w-3xl overflow-y-auto rounded-xl bg-theme-dark shadow-2xl max-h-[90vh]">
                            <!-- Backdrop image with reduced/max height -->
                            <div class="relative w-full h-[160px] sm:h-[200px] md:h-[250px]">
                                <img v-if="item.backdrop_path" :src="item.backdrop_path" :alt="item.title"
                                    class="absolute inset-0 w-full h-full object-cover rounded-t-xl" loading="lazy" />
                                <div v-else class="flex h-full items-center justify-center bg-transparent">
                                    <span class="text-theme-text-muted">{{ t('no_image') }}</span>
                                </div>
                                <div class="hero-gradient absolute inset-0"></div>
                                <button type="button" class="cursor-pointer absolute right-4 top-4"
                                    @click="showDetail = false">
                                    <XMarkIcon class="h-5 w-5 text-white" />
                                </button>
                            </div>
                            <div class="px-4 py-4 sm:px-6 sm:py-6">
                                <MovieInfo :item="item" title-class="text-xl sm:text-2xl md:text-3xl"
                                    overview-class="line-clamp-4 text-sm text-theme-text">
                                    <div class="mt-5 flex flex-wrap gap-3">
                                        <Button :href="detailUrl" variant="primary" size="md"
                                            @click="showDetail = false">
                                            <InformationCircleIcon class="h-4 w-4" />
                                            {{ t('view_details') }}
                                        </Button>
                                        <WatchlistButton :item-id="item.id" :item-type="item.media_type" size="md"
                                            button-class="rounded-full" />
                                    </div>
                                </MovieInfo>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useTranslation } from '../Composables/useTranslation.js';
import { StarIcon } from '@heroicons/vue/20/solid';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';
import { XMarkIcon, PlayIcon } from '@heroicons/vue/24/solid';
import PosterImage from './PosterImage.vue';
import MovieInfo from './MovieInfo.vue';
import Button from './Button.vue';
import WatchlistButton from './WatchlistButton.vue';

const { t } = useTranslation();

const props = defineProps({
    item: { type: Object, required: true },
    cardWidth: { type: Number, default: 180 },
});

const showDetail = ref(false);

const watchProgressPercent = computed(() => {
    const p = props.item.watch_progress_percentage;
    return p != null && Number(p) >= 0 ? Number(p) : null;
});

const detailUrl = computed(() => {
    const routeName = props.item.media_type === 'tv' ? 'tv.show' : 'movie.show';
    return route(routeName, { id: props.item.id });
});
</script>
