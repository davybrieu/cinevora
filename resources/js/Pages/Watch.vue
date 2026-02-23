<template>
    <HeadOnlyLayout :title="pageTitle">
        <div class="fixed inset-0 z-50 bg-black">
            <template v-if="iframeSrc">
                <div class="absolute top-6 left-6 z-60">
                    <Button :href="backUrl" variant="secondary" size="sm" class="!rounded-full shadow-lg">
                        <ArrowLeftIcon class="h-5 w-5" />
                        {{ t('back') }}
                    </Button>
                </div>
                <iframe :src="iframeSrc" class="w-full h-full" frameborder="0" allowfullscreen
                    allow="encrypted-media; gyroscope; accelerometer; picture-in-picture; web-share"></iframe>
            </template>
            <div v-else class="flex items-center justify-center h-full flex-col gap-4">
                <p class="text-white text-2xl font-bold">{{ t('no_video_available') }}</p>
                <Button :href="backUrl" variant="secondary">{{ t('back') }}</Button>
            </div>
        </div>
    </HeadOnlyLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import HeadOnlyLayout from '@/Layouts/HeadOnlyLayout.vue';
import Button from '@/Components/Button.vue';
import { useTranslation } from '@/Composables/useTranslation';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const { t } = useTranslation();

const props = defineProps({
    type: { type: String, required: true },
    item: { type: Object, required: true },
    tmdb_id: { type: Number, required: true },
    season: { type: Number, default: null },
    episode: { type: Number, default: null },
    iframeSrc: { type: String, default: null },
});

const pageTitle = computed(() => {
    if (props.type === 'movie') {
        return `${props.item.title} - ${props.item.release_date}`;
    } else if (props.type === 'tv') {
        return `${props.item.title} - ${props.season}x${props.episode}`;
    }
    return '';
});

const backUrl = computed(() => {
    if (props.type === 'movie') {
        return route('movie.show', { id: props.tmdb_id });
    } else if (props.type === 'tv') {
        return route('tv.show', { id: props.tmdb_id, season: props.season, episode: props.episode });
    }
    return null;
});

const SAVE_THROTTLE_MS = 5000;
const pendingPayload = ref(null);
let saveInterval = null;
let isSaving = false;

function buildPayload(data) {
    return {
        item_id: data.id,
        item_type: data.type || props.type,
        watched_progress: Math.round(data.progress ?? 0),
        watched_duration: Math.round(data.duration ?? 0),
        season: data.season ?? (props.season ?? 0),
        episode: data.episode ?? (props.episode ?? 0),
    };
}

function saveProgressInBackground() {
    if (isSaving || !pendingPayload.value) return;
    const payload = { ...pendingPayload.value };
    isSaving = true;
    axios.post(route('watch.progress.store'), payload).finally(() => {
        isSaving = false;
    });
}

function onPlayerMessage(event) {
    if (typeof event.data !== 'string') return;
    try {
        const data = JSON.parse(event.data);
        console.log(data);
        if (
            data &&
            data.type === 'PLAYER_EVENT' &&
            data.data?.event === 'timeupdate'
        ) {
            pendingPayload.value = buildPayload({
                id: data.data.id,
                type: data.data.mediaType,
                progress: data.data.currentTime,
                duration: data.data.duration,
                season: data.data.season,
                episode: data.data.episode,
            });
        }
    } catch (_) { }
}

onMounted(() => {
    window.addEventListener('message', onPlayerMessage);
    saveInterval = setInterval(saveProgressInBackground, SAVE_THROTTLE_MS);
});

onUnmounted(() => {
    window.removeEventListener('message', onPlayerMessage);
    if (saveInterval) clearInterval(saveInterval);
});
</script>