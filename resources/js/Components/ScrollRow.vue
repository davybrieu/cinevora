<template>
    <div class="relative" :class="sectionClass">
        <button v-show="canScrollLeft" @click="scrollByAmount('left')"
            class="cursor-pointer row-arrow absolute left-0 top-0 z-10 flex h-full w-10 items-center justify-center bg-gradient-to-r from-theme-dark/95 to-transparent md:w-14">
            <ChevronLeftIcon class="h-7 w-7 text-white drop-shadow" />
        </button>

        <div ref="scrollContainer"
            class="scrollbar-hide select-none flex overflow-x-auto px-4 sm:px-8 md:px-16"
            :class="[gapClass, { 'cursor-grab': !isDragging, 'cursor-grabbing': isDragging, 'scroll-smooth': !isDragging }]"
            @scroll="updateScrollState" @mousedown="onDragStart" @mousemove="onDragMove"
            @mouseup="onDragEnd" @mouseleave="onDragEnd" @dragstart.prevent @selectstart.prevent>
            <slot :wasDragged="wasDragged" />
        </div>

        <button v-show="canScrollRight" @click="scrollByAmount('right')"
            class="cursor-pointer row-arrow absolute right-0 top-0 z-10 flex h-full w-10 items-center justify-center bg-gradient-to-l from-theme-dark/95 to-transparent md:w-14">
            <ChevronRightIcon class="h-7 w-7 text-white drop-shadow" />
        </button>
    </div>
</template>

<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { useDragScroll } from '../Composables/useDragScroll.js';

defineProps({
    sectionClass: { type: String, default: '' },
    gapClass: { type: String, default: 'gap-4' },
});

const {
    scrollContainer, canScrollLeft, canScrollRight,
    isDragging, wasDragged,
    updateScrollState, scrollByAmount,
    onDragStart, onDragMove, onDragEnd,
} = useDragScroll();
</script>
