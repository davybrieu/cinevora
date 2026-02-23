<template>
    <AppLayout :title="t('home_title')">
        <HeroSlider v-if="hero.length" :items="hero" />

        <div class="relative z-10 mt-14 pb-8">
            <SectionTitle :title="t('streaming_providers')" class="px-8 md:px-16" />
            <ProviderFilter :providers="providers" :current-provider="currentProvider" />
        </div>

        <div class="relative z-10 space-y-2 pb-20">
            <MovieRow v-for="category in categories" :key="category.slug" :title="category.title" :slug="category.slug"
                :items="category.items" />
        </div>
    </AppLayout>
</template>

<script setup>
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '../Layouts/AppLayout.vue';
import HeroSlider from '../Components/HeroSlider.vue';
import MovieRow from '../Components/MovieRow.vue';
import ProviderFilter from '../Components/ProviderFilter.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import { usePage } from '@inertiajs/vue3';

const { t } = useTranslation();

const props = defineProps({
    hero: { type: Array, required: true },
    categories: { type: Array, required: true },
    providers: { type: Array, required: true },
    currentProvider: { type: Number, default: null },
});
</script>
