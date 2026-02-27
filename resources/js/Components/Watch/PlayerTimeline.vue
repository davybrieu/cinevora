<template>
    <div class="group mb-3">
        <div class="relative h-1 w-full rounded-full bg-white/20 cursor-pointer transition-all duration-150 group-hover:h-1.5"
            ref="trackRef" @mousedown="handleMouseDown" @contextmenu.prevent>
            <div class="absolute left-0 top-0 h-full rounded-full bg-theme-accent transition-all"
                :style="{ width: `${displayedPercent}%` }" />
            <div class="absolute top-1/2 h-3 w-3 rounded-full bg-theme-accent shadow-md shadow-theme-accent/30 -translate-y-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                :style="{ left: `${displayedPercent}%` }" />
        </div>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref } from 'vue';

const props = defineProps({
    currentTime: { type: Number, default: 0 },
    duration: { type: Number, default: 0 },
});

const emit = defineEmits(['seek']);
const trackRef = ref(null);
const isDragging = ref(false);
const dragRatio = ref(null);
const SEEK_DEBOUNCE_MS = 25;
let seekDebounceTimer = null;
let pendingRatio = null;

const percent = computed(() => {
    if (!props.duration || props.duration <= 0) return 0;
    return Math.min(100, Math.max(0, (props.currentTime / props.duration) * 100));
});
const displayedPercent = computed(() => (
    dragRatio.value === null ? percent.value : dragRatio.value * 100
));

function ratioFromClientX(clientX) {
    const track = trackRef.value;
    if (!track) return 0;
    const rect = track.getBoundingClientRect();
    return Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
}

function flushSeek() {
    if (pendingRatio === null) return;
    emit('seek', pendingRatio);
    pendingRatio = null;
}

function queueSeek(ratio) {
    pendingRatio = ratio;
    if (seekDebounceTimer) return;
    seekDebounceTimer = window.setTimeout(() => {
        seekDebounceTimer = null;
        flushSeek();
    }, SEEK_DEBOUNCE_MS);
}

function seekToClientX(clientX, immediate = false) {
    const ratio = ratioFromClientX(clientX);
    dragRatio.value = ratio;
    if (immediate) {
        pendingRatio = ratio;
        flushSeek();
        return;
    }
    queueSeek(ratio);
}

function handleMouseMove(event) {
    if (!isDragging.value) return;
    seekToClientX(event.clientX);
}

function stopDragging() {
    isDragging.value = false;
    dragRatio.value = null;
    if (seekDebounceTimer) {
        window.clearTimeout(seekDebounceTimer);
        seekDebounceTimer = null;
    }
    flushSeek();
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseup', stopDragging);
}

function handleMouseDown(event) {
    if (event.button !== 0 && event.button !== 2) return;
    event.preventDefault();
    isDragging.value = true;
    seekToClientX(event.clientX, true);
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', stopDragging);
}

onUnmounted(() => {
    stopDragging();
});
</script>
