<template>
    <div class="relative h-screen w-full overflow-hidden group select-none"
        :class="{ 'cursor-grab': !isDragging, 'cursor-grabbing': isDragging }"
        @mousedown="onDragStart" @touchstart.passive="onTouchStart"
        @touchmove.prevent="onTouchMove" @touchend="onTouchEnd"
        @click.capture="(e) => { if (wasDragged) { e.preventDefault(); e.stopPropagation(); } }">
        <!-- Single track: always visible, never switches - [prev, current, next] -->
        <div ref="trackRef" class="absolute inset-0 z-[1] flex hero-track"
            :class="{ 'hero-track-dragging': isDragging }"
            style="min-width: 300%; will-change: transform;"
            :style="{ transform: `translate3d(${trackTranslate}, 0, 0)` }"
            @transitionend="onTrackTransitionEnd">
            <div v-for="(slide, i) in trackSlides" :key="slide.id + '-' + i"
                class="flex-shrink-0 h-full relative overflow-hidden"
                style="width: 33.333%">
                <HeroSlideContent :item="slide" />
            </div>
        </div>

        <!-- Navigation dots -->
        <div class="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 gap-2 sm:bottom-8 sm:gap-2.5">
            <button v-for="(item, index) in items" :key="'dot-' + item.id" @click="goTo(index)"
                class="group flex items-center py-3 cursor-pointer">
                <div class="relative h-[3px] overflow-hidden rounded-full transition-all duration-500"
                    :class="currentIndex === index ? 'w-10 sm:w-14 bg-white/40' : 'w-5 sm:w-7 bg-white/20 hover:bg-white/30'">
                    <div v-if="currentIndex === index"
                        class="absolute inset-y-0 left-0 rounded-full bg-white transition-all duration-100 ease-linear"
                        :style="{ width: progressWidth + '%' }"></div>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';
import MovieInfo from './MovieInfo.vue';
import WatchlistButton from './WatchlistButton.vue';
import HeroSlideContent from './HeroSlideContent.vue';

const { t } = useTranslation();

const props = defineProps({
    items: { type: Array, required: true },
});

const currentIndex = ref(0);
const progress = ref(0);
const isDragging = ref(false);
const wasDragged = ref(false);
const dragDelta = ref(0);
const animatingTo = ref(null); // 'prev' | 'next' | null
const trackRef = ref(null);

const SLIDE_DURATION = 7000;
const TICK = 50;
const DRAG_THRESHOLD = 50;

let slideTimer = null;
let progressTimer = null;
let dragStartX = 0;
let dragCurrentX = 0;

const progressWidth = computed(() => Math.min((progress.value / SLIDE_DURATION) * 100, 100));

// Always [prev, current, next] - single structure, never switches
const trackSlides = computed(() => {
    if (props.items.length === 0) return [];
    const n = props.items.length;
    const prevIdx = (currentIndex.value - 1 + n) % n;
    const nextIdx = (currentIndex.value + 1) % n;
    return [
        props.items[prevIdx] ?? props.items[0],
        props.items[currentIndex.value] ?? props.items[0],
        props.items[nextIdx] ?? props.items[0],
    ];
});

// -33.333% = current, -66.666% = next, 0 = prev
const trackTranslate = computed(() => {
    if (isDragging.value) {
        return `calc(-33.333% + ${dragDelta.value}px)`;
    }
    if (animatingTo.value === 'next') return '-66.666%';
    if (animatingTo.value === 'prev') return '0';
    return '-33.333%';
});

function goTo(index) {
    if (index === currentIndex.value || animatingTo.value) return;
    const diff = index - currentIndex.value;
    const n = props.items.length;
    const normalized = ((diff % n) + n) % n;
    if (normalized === 1) {
        animatingTo.value = 'next';
    } else if (normalized === n - 1) {
        animatingTo.value = 'prev';
    } else {
        currentIndex.value = index;
        resetTimers();
    }
}

function prev() {
    if (animatingTo.value) return;
    animatingTo.value = 'prev';
}

function next() {
    if (animatingTo.value) return;
    animatingTo.value = 'next';
}

function onTrackTransitionEnd(e) {
    if (e.propertyName !== 'transform' || !animatingTo.value || !trackRef.value) return;
    trackRef.value.style.transition = 'none';
    if (animatingTo.value === 'next') {
        currentIndex.value = (currentIndex.value + 1) % props.items.length;
    } else if (animatingTo.value === 'prev') {
        currentIndex.value = (currentIndex.value - 1 + props.items.length) % props.items.length;
    }
    animatingTo.value = null;
    resetTimers();
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (trackRef.value) trackRef.value.style.transition = '';
        });
    });
}

function onDragStart(e) {
    if (props.items.length <= 1) return;
    isDragging.value = true;
    wasDragged.value = false;
    dragDelta.value = 0;
    dragStartX = e.pageX ?? e.touches?.[0]?.pageX ?? 0;
    dragCurrentX = dragStartX;
    document.addEventListener('mousemove', onDocumentDragMove);
    document.addEventListener('mouseup', onDocumentDragEnd);
}

function onDocumentDragMove(e) {
    if (!isDragging.value) return;
    e.preventDefault();
    dragCurrentX = e.pageX ?? e.touches?.[0]?.pageX ?? dragCurrentX;
    const delta = dragCurrentX - dragStartX;
    if (Math.abs(delta) > 5) wasDragged.value = true;
    dragDelta.value = delta;
}

function onDocumentDragEnd() {
    if (!isDragging.value) return;
    document.removeEventListener('mousemove', onDocumentDragMove);
    document.removeEventListener('mouseup', onDocumentDragEnd);
    const delta = dragCurrentX - dragStartX;
    isDragging.value = false;
    if (delta < -DRAG_THRESHOLD) {
        animatingTo.value = 'next';
    } else if (delta > DRAG_THRESHOLD) {
        animatingTo.value = 'prev';
    }
    dragDelta.value = 0;
    setTimeout(() => { wasDragged.value = false; }, 50);
}

function onTouchStart(e) {
    if (props.items.length <= 1) return;
    isDragging.value = true;
    wasDragged.value = false;
    dragDelta.value = 0;
    dragStartX = e.touches[0].pageX;
    dragCurrentX = dragStartX;
}

function onTouchMove(e) {
    if (!isDragging.value) return;
    e.preventDefault();
    dragCurrentX = e.touches[0].pageX;
    const delta = dragCurrentX - dragStartX;
    if (Math.abs(delta) > 5) wasDragged.value = true;
    dragDelta.value = delta;
}

function onTouchEnd(e) {
    if (!isDragging.value) return;
    const delta = (e.changedTouches?.[0]?.pageX ?? dragCurrentX) - dragStartX;
    isDragging.value = false;
    if (delta < -DRAG_THRESHOLD) {
        animatingTo.value = 'next';
    } else if (delta > DRAG_THRESHOLD) {
        animatingTo.value = 'prev';
    }
    dragDelta.value = 0;
    setTimeout(() => { wasDragged.value = false; }, 50);
}

function resetTimers() {
    clearInterval(slideTimer);
    clearInterval(progressTimer);
    progress.value = 0;
    startTimers();
}

function startTimers() {
    progressTimer = setInterval(() => {
        progress.value += TICK;
    }, TICK);

    slideTimer = setInterval(() => {
        next();
    }, SLIDE_DURATION);
}

onMounted(() => {
    if (props.items.length > 1) {
        startTimers();
    }
});

onUnmounted(() => {
    clearInterval(slideTimer);
    clearInterval(progressTimer);
    document.removeEventListener('mousemove', onDocumentDragMove);
    document.removeEventListener('mouseup', onDocumentDragEnd);
});
</script>

<style scoped>
.hero-track {
    transition: transform 0.5s cubic-bezier(0.32, 0.72, 0, 1);
}

.hero-track-dragging {
    transition: none;
}
</style>
