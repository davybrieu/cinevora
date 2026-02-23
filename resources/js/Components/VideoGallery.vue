<template>
    <section v-if="videos.length" class="video-gallery">
        <ScrollRow section-class="video-gallery" v-slot="{ wasDragged }">
            <div v-for="(video, i) in videos" :key="video.key"
                class="flex-shrink-0 overflow-hidden rounded-lg cursor-pointer group"
                :class="{ 'pointer-events-none': wasDragged }" style="width: 350px;"
                @click="!wasDragged && openVideo(i)">
                <div class="relative aspect-video">
                    <img :src="`https://img.youtube.com/vi/${video.key}/hqdefault.jpg`" :alt="video.name"
                        class="object-cover w-full h-full transition-transform duration-200" loading="lazy"
                        draggable="false" />
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black/30 transition-opacity duration-200 group-hover:bg-black/50">
                        <PlayIcon class="h-12 w-12 text-white drop-shadow-lg" />
                    </div>
                </div>
                <div class="bg-theme-darker px-3 py-2">
                    <p class="truncate text-sm text-white/70">{{ video.name }}</p>
                    <p class="text-xs text-white/30">{{ video.type }}</p>
                </div>
            </div>
        </ScrollRow>

        <Modal v-model:open="modalOpen" size="video" transparent>
            <iframe v-if="videos[modalIndex]"
                :src="`https://www.youtube.com/embed/${videos[modalIndex].key}?autoplay=1`"
                :title="videos[modalIndex].name" class="h-full w-full rounded-lg" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        </Modal>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { PlayIcon } from '@heroicons/vue/24/solid';
import ScrollRow from './ScrollRow.vue';
import Modal from './Modal.vue';

defineProps({
    videos: { type: Array, required: true },
});

const modalOpen = ref(false);
const modalIndex = ref(0);

function openVideo(index) {
    modalIndex.value = index;
    modalOpen.value = true;
}

defineExpose({ openVideo });
</script>
