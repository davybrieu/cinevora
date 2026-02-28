<template>
    <div class="relative w-full h-full min-h-0">
        <img v-if="item.backdrop_path" :src="item.backdrop_path" :alt="item.title"
            class="h-full w-full object-cover pointer-events-none" loading="eager" />
        <div class="hero-gradient absolute inset-0"></div>
        <div class="hero-gradient-left absolute inset-0"></div>

        <div class="absolute bottom-[12%] left-0 z-10 max-w-xl px-4 sm:bottom-[18%] sm:max-w-2xl sm:px-8 md:bottom-[22%] md:px-16">
            <div class="animate-slide-up">
                <MovieInfo :item="item" title-class="text-2xl sm:text-3xl md:text-4xl lg:text-6xl leading-tight drop-shadow-lg"
                    overview-class="line-clamp-2 text-sm sm:line-clamp-3 sm:text-base md:text-lg text-theme-text/90">
                    <div class="mt-4 flex flex-wrap items-center gap-2 sm:mt-7 sm:gap-3">
                        <Link
                            :href="item.media_type === 'tv' ? route('tv.show', { id: item.id }) : route('movie.show', { id: item.id })"
                            class="inline-flex items-center gap-2 rounded bg-theme-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-theme-accent/90 active:bg-theme-accent/80 h-10 min-h-[40px] sm:px-6 sm:py-3 sm:text-base sm:h-12 sm:min-h-[48px]"
                        >
                            <InformationCircleIcon class="h-5 w-5" />
                            {{ t('view_details') }}
                        </Link>
                        <div class="h-10 min-h-[40px] flex items-center sm:h-12 sm:min-h-[48px]">
                            <WatchlistButton
                                :item-id="item.id"
                                :item-type="item.media_type"
                                size="md"
                                button-class="rounded-full h-10 min-h-[40px] sm:h-12 sm:min-h-[48px] flex items-center justify-center"
                            />
                        </div>
                    </div>
                </MovieInfo>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';
import MovieInfo from './MovieInfo.vue';
import WatchlistButton from './WatchlistButton.vue';

const { t } = useTranslation();

defineProps({
    item: { type: Object, required: true },
});
</script>
