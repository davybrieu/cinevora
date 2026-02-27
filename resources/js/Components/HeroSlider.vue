<template>
    <div class="relative h-screen w-full overflow-hidden group select-none"
        :class="{ 'cursor-grab': !isDragging, 'cursor-grabbing': isDragging }"
        @mousedown="onDragStart" @mousemove="onDragMove" @mouseup="onDragEnd" @mouseleave="onDragEnd"
        @touchstart.passive="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd"
        @click.capture="(e) => { if (wasDragged) { e.preventDefault(); e.stopPropagation(); } }">
        <Transition :name="slideDirection === 'prev' ? 'hero-slide-right' : 'hero-slide-left'">
            <div v-if="activeItem" :key="activeItem.id" class="absolute inset-0 z-[1]">
                <img v-if="activeItem.backdrop_path" :src="activeItem.backdrop_path" :alt="activeItem.title"
                    class="h-full w-full object-cover pointer-events-none" loading="lazy" />
                <div class="hero-gradient absolute inset-0"></div>
                <div class="hero-gradient-left absolute inset-0"></div>

                <div class="absolute bottom-[18%] left-0 z-10 max-w-2xl px-8 md:bottom-[22%] md:px-16">
                    <div class="animate-slide-up">
                        <MovieInfo :item="activeItem" title-class="text-4xl md:text-6xl leading-tight drop-shadow-lg"
                            overview-class="line-clamp-3 text-base md:text-lg text-theme-text/90">
                            <div class="mt-7 flex flex-wrap items-center gap-3">
                                <Link
                                    :href="activeItem.media_type === 'tv' ? route('tv.show', { id: activeItem.id }) : route('movie.show', { id: activeItem.id })"
                                    class="inline-flex items-center gap-2 rounded bg-theme-accent px-6 py-3 text-base font-bold uppercase tracking-wider text-white shadow-lg transition hover:bg-theme-accent/90 active:bg-theme-accent/80 h-12 min-h-[48px]"
                                >
                                    <InformationCircleIcon class="h-5 w-5" />
                                    {{ t('view_details') }}
                                </Link>
                                <div class="h-12 min-h-[48px] flex items-center">
                                    <WatchlistButton
                                        :item-id="activeItem.id"
                                        :item-type="activeItem.media_type"
                                        size="md"
                                        button-class="rounded-full h-12 min-h-[48px] flex items-center justify-center"
                                    />
                                </div>
                            </div>
                        </MovieInfo>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Navigation dots -->
        <div class="absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 gap-2.5">
            <button v-for="(item, index) in items" :key="'dot-' + item.id" @click="goTo(index)"
                class="group relative h-[3px] overflow-hidden rounded-full transition-all duration-500 cursor-pointer"
                :class="currentIndex === index ? 'w-14 bg-white/40' : 'w-7 bg-white/20 hover:bg-white/30'">
                <div v-if="currentIndex === index"
                    class="absolute inset-y-0 left-0 rounded-full bg-white transition-all duration-100 ease-linear"
                    :style="{ width: progressWidth + '%' }"></div>
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

const { t } = useTranslation();

const props = defineProps({
    items: { type: Array, required: true },
});

const currentIndex = ref(0);
const progress = ref(0);
const isDragging = ref(false);
const wasDragged = ref(false);
const slideDirection = ref('next');

const SLIDE_DURATION = 7000;
const TICK = 50;
const DRAG_THRESHOLD = 50;

let slideTimer = null;
let progressTimer = null;
let dragStartX = 0;
let dragCurrentX = 0;

const progressWidth = computed(() => Math.min((progress.value / SLIDE_DURATION) * 100, 100));
const activeItem = computed(() => props.items[currentIndex.value] ?? null);

function goTo(index) {
    if (index === currentIndex.value) return;
    slideDirection.value = index > currentIndex.value ? 'next' : 'prev';
    currentIndex.value = index;
    resetTimers();
}

function prev() {
    slideDirection.value = 'prev';
    currentIndex.value = (currentIndex.value - 1 + props.items.length) % props.items.length;
    resetTimers();
}

function next() {
    slideDirection.value = 'next';
    currentIndex.value = (currentIndex.value + 1) % props.items.length;
    resetTimers();
}

function onDragStart(e) {
    if (props.items.length <= 1) return;
    isDragging.value = true;
    wasDragged.value = false;
    dragStartX = e.pageX;
    dragCurrentX = e.pageX;
}

function onDragMove(e) {
    if (!isDragging.value) return;
    e.preventDefault();
    dragCurrentX = e.pageX;
    const delta = Math.abs(dragCurrentX - dragStartX);
    if (delta > 5) wasDragged.value = true;
}

function onDragEnd() {
    if (!isDragging.value) return;
    const delta = dragCurrentX - dragStartX;
    if (delta < -DRAG_THRESHOLD) next();
    else if (delta > DRAG_THRESHOLD) prev();
    isDragging.value = false;
    setTimeout(() => { wasDragged.value = false; }, 50);
}

function onTouchStart(e) {
    if (props.items.length <= 1) return;
    isDragging.value = true;
    wasDragged.value = false;
    dragStartX = e.touches[0].pageX;
    dragCurrentX = e.touches[0].pageX;
}

function onTouchMove(e) {
    if (!isDragging.value) return;
    e.preventDefault();
    dragCurrentX = e.touches[0].pageX;
    const delta = Math.abs(dragCurrentX - dragStartX);
    if (delta > 5) wasDragged.value = true;
}

function onTouchEnd() {
    if (!isDragging.value) return;
    const delta = dragCurrentX - dragStartX;
    if (delta < -DRAG_THRESHOLD) next();
    else if (delta > DRAG_THRESHOLD) prev();
    isDragging.value = false;
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
});
</script>

<style scoped>
.hero-slide-left-enter-active,
.hero-slide-left-leave-active,
.hero-slide-right-enter-active,
.hero-slide-right-leave-active {
    transition: transform 0.7s ease;
}

.hero-slide-left-enter-active,
.hero-slide-right-enter-active {
    z-index: 2;
}

.hero-slide-left-leave-active,
.hero-slide-right-leave-active {
    z-index: 1;
}

.hero-slide-left-enter-from {
    transform: translateX(100%);
}

.hero-slide-left-enter-to {
    transform: translateX(0);
}

.hero-slide-left-leave-from {
    transform: translateX(0);
}

.hero-slide-left-leave-to {
    transform: translateX(-100%);
}

.hero-slide-right-enter-from {
    transform: translateX(-100%);
}

.hero-slide-right-enter-to {
    transform: translateX(0);
}

.hero-slide-right-leave-from {
    transform: translateX(0);
}

.hero-slide-right-leave-to {
    transform: translateX(100%);
}
</style>
