<template>
    <div class="border-b border-white/[0.06] last:border-b-0">
        <div class="flex min-w-0 flex-1 items-center gap-3 px-0 py-3">
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-theme-accent/12 ring-1 ring-theme-accent/15">
                <component :is="iconComponent" class="h-4 w-4 text-theme-accent" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-white/95">{{ title }}</p>
            </div>
        </div>
        <div class="pb-4 pt-0">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import {
    MagnifyingGlassIcon,
    FilmIcon,
    TvIcon,
    CalendarDaysIcon,
    LanguageIcon,
    StarIcon,
    ChartBarIcon,
    ArrowsRightLeftIcon,
    RectangleGroupIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: { type: String, required: true },
    icon: { type: String, default: 'filter' },
});

const iconMap = {
    search: MagnifyingGlassIcon,
    type: FilmIcon,
    tv: TvIcon,
    genre: RectangleGroupIcon,
    calendar: CalendarDaysIcon,
    language: LanguageIcon,
    star: StarIcon,
    votes: ChartBarIcon,
    sort: ArrowsRightLeftIcon,
};

const iconComponent = computed(() => iconMap[props.icon] || RectangleGroupIcon);
</script>
