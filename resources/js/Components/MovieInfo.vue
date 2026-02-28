<template>
    <div>
        <img v-if="item.logo_path" :src="item.logo_path" :alt="item.title" class="mb-4 max-h-30 w-auto object-cover"
            loading="lazy" />
        <h1 v-else :class="titleClass" class="mb-4 font-black text-white">
            {{ item.title }}
        </h1>

        <p v-if="item.tagline" class="mb-4 text-base italic text-white/50">
            "{{ item.tagline }}"
        </p>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <MediaBadge :type="item.media_type" />
            <StarRating :value="item.vote_average" :count="item.vote_count" />
            <span v-if="item.release_date" class="text-sm text-theme-text-muted">
                {{ item.release_date }}
            </span>
            <span v-if="item.runtime" class="text-sm text-theme-text-muted">{{ item.runtime }}</span>
            <span v-if="item.number_of_seasons" class="text-sm text-theme-text-muted">
                {{ item.number_of_seasons }} {{ t('seasons') }}
            </span>
        </div>

        <GenreBadge v-if="item.genres?.length" :genres="item.genres" size="md" gap="md" class="mb-4" />

        <ExpandableText
            :text="item.overview || ''"
            :fallback="t('no_description')"
            :text-class="overviewClass"
            :line-clamp="4"
        />

        <slot />
    </div>
</template>

<script setup>
import { useTranslation } from '../Composables/useTranslation.js';
import MediaBadge from './MediaBadge.vue';
import StarRating from './StarRating.vue';
import GenreBadge from './GenreBadge.vue';
import ExpandableText from './ExpandableText.vue';

const { t } = useTranslation();

defineProps({
    item: { type: Object, required: true },
    titleClass: { type: String, default: 'text-3xl md:text-5xl' },
    overviewClass: { type: String, default: 'max-w-3xl text-theme-text' },
});
</script>
