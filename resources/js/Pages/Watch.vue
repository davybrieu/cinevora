<template>
    <AppLayout :title="pageTitle" :navbar="false" :footer="false">
        <div class="fixed inset-0 z-50 bg-black">
            <!-- Top controls when player is not ready -->
            <div v-if="!(selectedStream && isReadyToPlay)" class="absolute top-6 left-6 z-60">
                <Button :href="backUrl" variant="secondary" size="sm" class="!rounded-full shadow-lg">
                    <ArrowLeftIcon class="h-5 w-5" />
                    {{ t('back') }}
                </Button>
            </div>

            <div v-if="!(selectedStream && isReadyToPlay)" class="absolute top-6 right-6 z-60">
                <Button type="button" variant="secondary" size="sm" class="!rounded-full shadow-lg"
                    @click="showPanel = !showPanel">
                    <SignalIcon class="h-5 w-5" />
                </Button>
            </div>

            <WatchSourcesPanel v-if="!(selectedStream && isReadyToPlay)" v-model="showPanel" :stream-list="streamList"
                :selected-stream="selectedStream" @select-stream="selectAndPlay" />

            <div v-if="selectedStream && isReadyToPlay" ref="playerContainer" class="relative w-full h-full"
                @mousemove.passive="handlePlayerMouseMove" @mouseleave="handlePlayerMouseLeave">
                <div v-show="controlsVisible" class="absolute top-6 left-6 z-60">
                    <Button :href="backUrl" variant="secondary" size="sm" class="!rounded-full shadow-lg">
                        <ArrowLeftIcon class="h-5 w-5" />
                        {{ t('back') }}
                    </Button>
                </div>

                <div v-show="controlsVisible" class="absolute top-6 right-6 z-60">
                    <Button type="button" variant="secondary" size="sm" class="!rounded-full shadow-lg"
                        @click="showPanel = !showPanel">
                        <SignalIcon class="h-5 w-5" />
                    </Button>
                </div>

                <WatchSourcesPanel v-show="controlsVisible" v-model="showPanel" :stream-list="streamList"
                    :selected-stream="selectedStream" @select-stream="selectAndPlay" />

                <Player :stream-url="streamUrl" :show-skip-now="showSkipNow" :current-time="currentTime"
                    :duration="duration" :video-ref="videoPlayer" :player-container-ref="playerContainer"
                    :controls-visible="controlsVisible" @skip-now="skipNow" @time-update="handleTimeUpdate"
                    @loaded-metadata="handleLoadedMetadata" @video-ready="setVideoPlayer" :item="item"
                    :page-title="pageTitle" />
            </div>

            <WatchDownloadProgress :status="status" v-else-if="selectedStream && !isReadyToPlay" />

            <div v-else class="flex items-center justify-center h-full flex-col gap-4">
                <p class="text-white text-2xl font-bold">{{ t('no_stream_selected') }}</p>
                <Button type="button" variant="secondary" size="sm" class="!rounded-full shadow-lg"
                    @click="showPanel = true">
                    <SignalIcon class="h-5 w-5" />
                    {{ t('select_a_stream') }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Player from '@/Components/Watch/Player.vue';
import WatchDownloadProgress from '@/Components/Watch/WatchDownloadProgress.vue';
import WatchSourcesPanel from '@/Components/Watch/WatchSourcesPanel.vue';
import { useTranslation } from '@/Composables/useTranslation';
import {
    ArrowLeftIcon,
    SignalIcon,
} from '@heroicons/vue/24/outline';

const { t } = useTranslation();

const props = defineProps({
    type: { type: String, required: true },
    item: { type: Object, required: true },
    tmdb_id: { type: Number, required: true },
    season: { type: Number, default: null },
    episode: { type: Number, default: null },
    streams: { type: [Array, Object], default: () => [] },
    progress: {
        type: Object,
        default: () => ({ watched_progress: 0, watched_duration: 0 }),
    },
});

const showPanel = ref(false);
const selectedStream = ref('');
const selectedStreamPayload = ref(null);
const streamUrl = ref(null);
const videoPlayer = ref(null);
const playerContainer = ref(null);

const currentTime = ref(0);
const duration = ref(0);
const skipWindow = ref(null);
const controlsVisible = ref(true);
const CONTROLS_HIDE_DELAY_MS = 2200;
let controlsHideTimer = null;
const WATCH_PROGRESS_THROTTLE_MS = 5000;
const pendingWatchProgressPayload = ref(null);
let watchProgressInterval = null;
let isSavingWatchProgress = false;
const shouldApplyInitialProgress = ref(false);

const streamList = computed(() =>
    Array.isArray(props.streams) ? props.streams : (props.streams?.streams ?? [])
);

const status = ref({
    active: false,
    loading: false,
    step: '',
    progress: null,
    downloaded: null,
    speed: null,
    peers: null,
    error: null,
});

const pageTitle = computed(() => {
    if (props.type === 'movie') return props.item.title;
    if (props.type === 'tv') return `${props.item.title} S${props.season} E${props.episode}`;
    return '';
});

const backUrl = computed(() => {
    if (props.type === 'movie') return route('movie.show', { id: props.tmdb_id });
    if (props.type === 'tv') return route('tv.show', { id: props.tmdb_id, season: props.season, episode: props.episode });
    return null;
});

const showSkipNow = computed(() => {
    if (!skipWindow.value) return false;
    return currentTime.value >= skipWindow.value.start && currentTime.value <= skipWindow.value.end;
});
const isReadyToPlay = computed(() => Boolean(streamUrl.value));

function resetStatus() {
    status.value = { active: false, loading: false, step: '', progress: null, downloaded: null, speed: null, peers: null, error: null };
}

function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function buildWatchProgressPayload() {
    const watchedProgress = Math.max(0, Math.floor(currentTime.value || 0));
    const watchedDuration = Math.max(0, Math.floor(duration.value || 0));

    if (!streamUrl.value || watchedProgress <= 0) {
        return null;
    }

    return {
        item_id: props.tmdb_id,
        item_type: props.type,
        watched_progress: watchedProgress,
        watched_duration: watchedDuration,
        season: props.type === 'tv' ? (props.season ?? 0) : 0,
        episode: props.type === 'tv' ? (props.episode ?? 0) : 0,
    };
}

function queueWatchProgressSave() {
    const payload = buildWatchProgressPayload();
    if (!payload) return;
    pendingWatchProgressPayload.value = payload;
}

async function saveWatchProgressInBackground() {
    if (isSavingWatchProgress || !pendingWatchProgressPayload.value) return;

    const payload = { ...pendingWatchProgressPayload.value };
    pendingWatchProgressPayload.value = null;
    isSavingWatchProgress = true;

    try {
        await axios.post(route('watch.progress.store'), payload);
    } catch {
        // Ignore transient save failures to avoid interrupting playback.
    } finally {
        isSavingWatchProgress = false;
    }
}

function flushWatchProgressOnUnload() {
    const payload = buildWatchProgressPayload();
    if (!payload) return;

    fetch(route('watch.progress.store'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin',
        keepalive: true,
    }).catch(() => { });
}

function selectAndPlay(stream) {
    queueWatchProgressSave();
    saveWatchProgressInBackground();
    shouldApplyInitialProgress.value = true;

    if (typeof stream === 'string') {
        selectedStreamPayload.value = { infoHash: stream, source: 'torrentio' };
        selectedStream.value = stream;
    } else {
        selectedStreamPayload.value = stream;
        selectedStream.value = stream?.infoHash ?? (stream?.url ? '__direct__' : '');
    }
    streamUrl.value = null;
    handleStream();
}

function setVideoPlayer(videoElement) {
    videoPlayer.value = videoElement ?? null;
}

function handleLoadedMetadata(payload = null) {
    const video = videoPlayer.value;
    duration.value = Number.isFinite(payload?.duration)
        ? payload.duration
        : (Number.isFinite(video?.duration) ? video.duration : 0);
    currentTime.value = Number.isFinite(payload?.currentTime)
        ? payload.currentTime
        : (video?.currentTime ?? 0);

    if (shouldApplyInitialProgress.value && video) {
        const savedProgress = Number(props.progress?.watched_progress ?? 0);
        const maxDuration = Number.isFinite(video.duration) ? video.duration : 0;

        if (savedProgress > 0) {
            const safeProgress = maxDuration > 0
                ? Math.min(savedProgress, Math.max(0, maxDuration - 1))
                : savedProgress;

            video.currentTime = safeProgress;
            currentTime.value = safeProgress;
        }

        shouldApplyInitialProgress.value = false;
    }
}

function handleTimeUpdate(payload = null) {
    const video = videoPlayer.value;
    currentTime.value = Number.isFinite(payload?.currentTime)
        ? payload.currentTime
        : (video?.currentTime ?? 0);
    if (Number.isFinite(payload?.duration)) {
        duration.value = payload.duration;
    }

    queueWatchProgressSave();
}

async function loadSkipWindow() {
    skipWindow.value = null;

    try {
        const response = await axios.get(route('stream.skip_intro'), {
            params: {
                imdbId: props.item.imdb_id,
                season: props.season,
                episode: props.episode,
                type: props.type,
            },
        });

        if (response.data?.available && response.data?.skip) {
            skipWindow.value = {
                start: Number(response.data.skip.start),
                end: Number(response.data.skip.end),
            };
        }
    } catch {
        skipWindow.value = null;
    }
}

function skipNow() {
    const video = videoPlayer.value;
    if (!video || !skipWindow.value) return;
    video.currentTime = skipWindow.value.end;
}

function clearControlsHideTimer() {
    if (controlsHideTimer === null) return;
    window.clearTimeout(controlsHideTimer);
    controlsHideTimer = null;
}

function hideControls() {
    if (!isReadyToPlay.value) return;
    controlsVisible.value = false;
    showPanel.value = false;
}

function scheduleControlsHide() {
    clearControlsHideTimer();
    controlsHideTimer = window.setTimeout(() => {
        hideControls();
    }, CONTROLS_HIDE_DELAY_MS);
}

function showControls() {
    controlsVisible.value = true;
    if (isReadyToPlay.value) scheduleControlsHide();
}

function handlePlayerMouseMove() {
    showControls();
}

function handlePlayerMouseLeave() {
    if (showPanel.value) return;
    if (!isReadyToPlay.value) return;
    scheduleControlsHide();
}

function handleWindowBlur() {
    if (!isReadyToPlay.value) return;
    scheduleControlsHide();
}

function handleDocumentVisibilityChange() {
    if (!isReadyToPlay.value || !document.hidden) return;
    scheduleControlsHide();
}

function handleDocumentMouseLeave(event) {
    if (!isReadyToPlay.value || event.relatedTarget !== null) return;
    scheduleControlsHide();
}

async function handleStream() {
    if (!selectedStreamPayload.value) return;

    if (selectedStreamPayload.value?.url) {
        streamUrl.value = selectedStreamPayload.value.url;
        resetStatus();
        showPanel.value = false;
        return;
    }

    if (!selectedStream.value || selectedStream.value === '__direct__') return;
    resetStatus();
    status.value.loading = true;
    status.value.step = t('checking');

    try {
        const payload = {
            infoHash: selectedStream.value,
            fileIdx: selectedStreamPayload.value?.fileIdx ?? null,
            filename: selectedStreamPayload.value?.filename ?? null,
            source: selectedStreamPayload.value?.source ?? 'torrentio',
        };

        const { data: checkResult } = await axios.post(route('stream.check'), {
            ...payload,
        });

        if (checkResult.exists && checkResult.url) {
            streamUrl.value = checkResult.url;
            resetStatus();
            showPanel.value = false;
            return;
        }

        status.value.active = true;
        status.value.step = t('starting_download');
        status.value.progress = 0;

        const res = await fetch(route('stream.download'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'text/event-stream',
                'X-XSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        });

        if (!res.ok || !res.body) {
            const errText = await res.text();
            let errData = {};
            try { errData = JSON.parse(errText); } catch { }
            selectedStream.value = '';
            selectedStreamPayload.value = null;
            status.value = { ...status.value, active: false, loading: false, error: errData.message ?? errData.error ?? t('network_error') };
            return;
        }

        const reader = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let lastEvent = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() ?? '';

            for (const line of lines) {
                if (line.startsWith('event: ')) {
                    lastEvent = line.slice(7).trim();
                    continue;
                }
                if (!line.startsWith('data: ')) continue;

                let payload;
                try { payload = JSON.parse(line.slice(6)); } catch { continue; }

                if (lastEvent === 'error') {
                    selectedStream.value = '';
                    selectedStreamPayload.value = null;
                    status.value = { active: false, loading: false, step: '', progress: null, downloaded: null, speed: null, peers: null, error: payload.message ?? t('error') };
                    return;
                }

                if (payload.message) status.value.step = payload.message;

                if (lastEvent === 'progress') {
                    if (typeof payload.progress === 'number') status.value.progress = payload.progress;
                    if (payload.downloaded) status.value.downloaded = payload.downloaded;
                    if (payload.speed) status.value.speed = payload.speed;
                    if (typeof payload.peers === 'number') status.value.peers = payload.peers;
                }

                if (lastEvent === 'upload_progress' && typeof payload.percent === 'number') {
                    status.value.step = t('upload_r2_progress', { percent: payload.percent });
                    status.value.progress = payload.percent;
                    status.value.speed = null;
                    status.value.peers = null;
                }

                if (lastEvent === 'done' && payload.url) {
                    streamUrl.value = payload.url;
                    resetStatus();
                    showPanel.value = false;
                    return;
                }
            }
        }

        status.value = { ...status.value, active: false, loading: false };
    } catch (err) {
        selectedStream.value = '';
        selectedStreamPayload.value = null;
        status.value = { active: false, loading: false, step: '', progress: null, downloaded: null, speed: null, peers: null, error: err.message ?? t('error') };
    }
}

watch(streamUrl, async (nextValue) => {
    if (!nextValue) return;

    controlsVisible.value = true;
    scheduleControlsHide();
    currentTime.value = 0;
    duration.value = 0;
    skipWindow.value = null;

    await loadSkipWindow();
});

onMounted(() => {
    watchProgressInterval = window.setInterval(() => {
        saveWatchProgressInBackground();
    }, WATCH_PROGRESS_THROTTLE_MS);

    window.addEventListener('blur', handleWindowBlur);
    window.addEventListener('beforeunload', flushWatchProgressOnUnload);
    document.addEventListener('visibilitychange', handleDocumentVisibilityChange);
    document.addEventListener('mouseleave', handleDocumentMouseLeave);
});

onUnmounted(() => {
    queueWatchProgressSave();
    saveWatchProgressInBackground();
    flushWatchProgressOnUnload();

    if (watchProgressInterval) {
        window.clearInterval(watchProgressInterval);
        watchProgressInterval = null;
    }

    clearControlsHideTimer();
    window.removeEventListener('blur', handleWindowBlur);
    window.removeEventListener('beforeunload', flushWatchProgressOnUnload);
    document.removeEventListener('visibilitychange', handleDocumentVisibilityChange);
    document.removeEventListener('mouseleave', handleDocumentMouseLeave);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
