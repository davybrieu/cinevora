<template>
    <section v-if="images.length" class="image-gallery">
        <ScrollRow section-class="image-gallery" v-slot="{ wasDragged }">
            <div v-for="(img, i) in images" :key="keyPrefix + '-' + i"
                class="flex-shrink-0 overflow-hidden rounded-lg cursor-pointer group"
                :class="{ 'pointer-events-none': wasDragged }" :style="itemStyle" @click="!wasDragged && openModal(i)">
                <img :src="img"
                    class="object-cover w-full h-full transition-transform duration-200 group-hover:scale-105 pointer-events-none"
                    loading="lazy" draggable="false" :alt="'Image ' + (i + 1)" />
            </div>
        </ScrollRow>

        <Modal v-model:open="modalOpen" size="auto" transparent>
            <img :src="images[modalIndex]" :class="modalHeightClass" :alt="'Image ' + (modalIndex + 1)" />
        </Modal>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import ScrollRow from './ScrollRow.vue';
import Modal from './Modal.vue';

const props = defineProps({
    images: { type: Array, required: true },
    type: { type: String, required: true, validator: v => ['posters', 'backdrops', 'profiles'].includes(v) },
    keyPrefix: { type: String, default: 'img' },
});

const itemStyle = computed(() => {
    if (props.type === 'posters') return { width: '170px', aspectRatio: '2/3' };
    if (props.type === 'backdrops') return { width: '350px', aspectRatio: '16/9' };
    if (props.type === 'profiles') return { width: '170px', aspectRatio: '2/3' };
    return { width: '180px', aspectRatio: '1' };
});

const modalHeightClass = computed(() => {
    if (props.type === 'posters') return 'max-h-[90vh] max-w-[54vw] rounded-lg shadow-2xl object-contain';
    if (props.type === 'backdrops') return 'max-h-[70vh] max-w-[98vw] rounded-lg shadow-2xl object-contain';
    if (props.type === 'profiles') return 'max-h-[90vh] max-w-[54vw] rounded-lg shadow-2xl object-contain';
    return 'max-h-[90vh] max-w-[98vw] rounded-lg shadow-2xl object-contain';
});

const modalOpen = ref(false);
const modalIndex = ref(0);

function openModal(index) {
    modalIndex.value = index;
    modalOpen.value = true;
}
</script>
