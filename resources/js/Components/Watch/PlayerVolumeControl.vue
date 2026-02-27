<template>
    <div class="flex items-center">
        <button type="button" class="cursor-pointer text-white/70 hover:text-white transition-colors"
            @click="$emit('toggle-mute')">
            <SpeakerXMarkIcon v-if="isMuted || volume <= 0" class="h-5 w-5" />
            <SpeakerWaveIcon v-else class="h-5 w-5" />
        </button>
        <div class="ml-2 mr-2 w-24 h-8 flex items-center transition-all duration-200">
            <div class="relative h-1 w-full rounded-full bg-white/20 cursor-pointer" ref="trackRef"
                @pointerdown="handlePointerDown" @contextmenu.prevent>
                <div class="absolute left-0 top-0 h-full rounded-full bg-emerald-500"
                    :style="{ width: `${percent}%` }" />
                <div class="absolute top-1/2 h-2.5 w-2.5 rounded-full bg-emerald-400 -translate-y-1/2 -translate-x-1/2"
                    :style="{ left: `${percent}%` }" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref } from 'vue';
import { SpeakerWaveIcon, SpeakerXMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    isMuted: { type: Boolean, default: false },
    volume: { type: Number, default: 1 },
});

const emit = defineEmits(['toggle-mute', 'set-volume']);
const trackRef = ref(null);
const isDragging = ref(false);

const percent = computed(() => Math.min(100, Math.max(0, props.volume * 100)));

function getRatioFromClientX(clientX) {
    const track = trackRef.value;
    if (!track) return null;
    const rect = track.getBoundingClientRect();
    return Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
}

function setVolumeFromClientX(clientX) {
    const ratio = getRatioFromClientX(clientX);
    if (ratio === null) return;
    emit('set-volume', ratio);
}

function handlePointerMove(event) {
    if (!isDragging.value) return;
    setVolumeFromClientX(event.clientX);
}

function stopDragging() {
    isDragging.value = false;
    window.removeEventListener('pointermove', handlePointerMove);
    window.removeEventListener('pointerup', stopDragging);
}

function handlePointerDown(event) {
    const isMouse = event.pointerType === 'mouse';
    if (isMouse && event.button !== 0 && event.button !== 2) return;
    event.preventDefault();
    isDragging.value = true;
    setVolumeFromClientX(event.clientX);
    window.addEventListener('pointermove', handlePointerMove);
    window.addEventListener('pointerup', stopDragging);
}

onUnmounted(() => {
    stopDragging();
});
</script>
