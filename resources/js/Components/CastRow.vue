<template>
    <section v-if="cast.length" class="cast-row">
        <SectionTitle :title="title" :count="count" class="px-4 sm:px-8 md:px-16" />

        <ScrollRow section-class="cast-row" v-slot="{ wasDragged }">
            <Link v-for="person in cast" :key="person.id" :href="route('person.show', person.id)"
                class="flex-shrink-0 transition-opacity hover:opacity-80" :class="{ 'pointer-events-none': wasDragged }"
                :style="{ width: `${cardWidth}px` }" draggable="false" @dragstart.prevent>
                <div class="overflow-hidden rounded-xl" :style="{ height: `${cardWidth * 1.2}px` }">
                    <PosterImage :src="person.profile_path" :alt="person.name" type="person"
                        class="h-full w-full select-none" />
                </div>
                <div class="mt-2">
                    <p class="truncate text-sm font-semibold text-white">{{ person.name }}</p>
                    <p class="truncate text-xs text-white/40">{{ person.character }}</p>
                </div>
            </Link>
        </ScrollRow>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SectionTitle from './SectionTitle.vue';
import PosterImage from './PosterImage.vue';
import ScrollRow from './ScrollRow.vue';

defineProps({
    title: { type: String, required: true },
    count: { type: Number, required: false },
    cast: { type: Array, required: true },
    cardWidth: { type: Number, default: 150 },
});
</script>
