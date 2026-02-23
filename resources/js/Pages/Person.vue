<template>
    <AppLayout :title="person.name">
        <div class="px-8 pb-8 pt-28 md:px-16">
            <div class="flex flex-col gap-8 md:flex-row">
                <div class="w-48 flex-shrink-0 md:w-64">
                    <PosterImage :src="person.profile_path" :alt="person.name" type="person"
                        class="aspect-[2/3] w-full rounded-xl shadow-2xl" />
                </div>

                <div class="flex-1">
                    <h1 class="mb-3 text-3xl font-black text-white md:text-5xl">{{ person.name }}</h1>

                    <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-white/40">
                        <Badge v-if="person.known_for_department" size="md">{{ person.known_for_department }}</Badge>
                        <span v-if="person.birthday">{{ t('born') }}: {{ person.birthday }}</span>
                        <span v-if="person.deathday">{{ t('died') }}: {{ person.deathday }}</span>
                        <span v-if="person.place_of_birth">{{ person.place_of_birth }}</span>
                    </div>

                    <p v-if="person.biography" class="max-w-3xl leading-relaxed text-theme-text"
                        :class="{ 'line-clamp-6': !bioExpanded }">
                        {{ person.biography }}
                    </p>
                    <button v-if="person.biography && person.biography.length > 400" type="button"
                        class="cursor-pointer mt-2 text-sm text-theme-accent hover:underline"
                        @click="bioExpanded = !bioExpanded">
                        {{ bioExpanded ? t('show_less') : t('show_more_bio') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-12 mb-12">
            <!-- Known For -->
            <section v-if="person.known_for.length">
                <MovieRow :title="t('known_for')" :items="person.known_for" :count="person.known_for.length" />
            </section>

            <!-- Photos -->
            <section v-if="person.images.length">
                <SectionTitle :title="t('photos')" :count="person.images.length" class="px-8 md:px-16" />
                <ImageGallery :images="person.images" type="profiles" key-prefix="photo" />
            </section>

            <!-- Acting Credits -->
            <section v-if="person.cast_credits.length">
                <MovieRow :title="t('acting_credits')" :items="person.cast_credits"
                    :count="person.cast_credits.length" />
            </section>

            <!-- Crew Credits -->
            <section v-if="person.crew_credits.length">
                <MovieRow :title="t('crew_credits')" :items="person.crew_credits" :count="person.crew_credits.length" />
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '../Layouts/AppLayout.vue';
import Badge from '../Components/Badge.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import PosterImage from '../Components/PosterImage.vue';
import ImageGallery from '../Components/ImageGallery.vue';
import MovieRow from '../Components/MovieRow.vue';

const { t } = useTranslation();

defineProps({
    person: { type: Object, required: true },
});

const bioExpanded = ref(false);
</script>
