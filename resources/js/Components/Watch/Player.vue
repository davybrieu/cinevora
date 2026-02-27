<template>
    <video :ref="bindVideoRef" :src="streamUrl" class="w-full h-full object-contain cursor-pointer" autoplay playinline
        @click.prevent="handleVideoClick" @dblclick.prevent="handleVideoDoubleClick" @timeupdate="handleTimeUpdate"
        @loadedmetadata="handleLoadedMetadata" @loadstart="handleVideoLoadStart" @waiting="handleVideoWaiting"
        @seeking="handleVideoWaiting" @canplay="handleVideoCanPlay" @playing="handleVideoPlaying"
        @error="handleVideoError" @pause="handleVideoPause" />

    <div v-if="showCenterSpinner"
        class="absolute inset-0 z-20 h-full w-full bg-black/60 grid place-items-center pointer-events-none">
        <transition name="fade-opacity-spinner">
            <img v-if="item.logo_path" :src="item.logo_path" :alt="item.title" class="h-auto w-70 fade-opacity-img"
                loading="lazy" />
            <Spinner v-else size="lg" />
        </transition>
    </div>

    <div v-show="controlsVisible" class="player-controls absolute inset-x-0 bottom-0 z-30 px-5 pb-5">
        <div class="flex items-center justify-between mb-3 min-h-8">
            <Transition name="fade">
                <button v-if="showSkipNow" type="button"
                    class="cursor-pointer text-sm font-semibold px-4 py-2 rounded-full bg-theme-accent hover:bg-theme-accent-hover text-black transition-all duration-200 shadow-lg shadow-theme-accent/25"
                    @click="emit('skip-now')">
                    {{ t('skip_now') }}
                </button>
            </Transition>
        </div>

        <div class="flex items-center gap-2 font-semibold text-white text-2xl mb-4" v-if="pageTitle">
            {{ pageTitle }}
        </div>

        <PlayerTimeline :current-time="currentTime" :duration="duration" @seek="handleSeekByRatio" />

        <div class="flex items-center text-white justify-between w-full">
            <div class="flex items-center gap-2">
                <WatchButton type="button"
                    class="!h-10 !w-10 !rounded-full !bg-theme-accent/90 hover:!bg-theme-accent-hover !text-black transition-all duration-200 shadow-md shadow-theme-accent/20"
                    @click="togglePlayPause">
                    <PlayIcon v-if="!isPlaying" class="h-5 w-5" />
                    <PauseIcon v-else class="h-5 w-5" />
                </WatchButton>

                <div class="mx-2 hidden h-6 w-px bg-white/10 sm:block"></div>

                <WatchButton type="button" @click="seekBy(-10)" title="Seek -10">
                    <BackwardIcon class="h-5 w-5" />
                </WatchButton>

                <WatchButton type="button" @click="seekBy(10)" title="Seek +10">
                    <ForwardIcon class="h-5 w-5" />
                </WatchButton>

                <div class="mx-2 hidden h-6 w-px bg-white/10 sm:block"></div>

                <PlayerVolumeControl :is-muted="isMuted" :volume="volume" @toggle-mute="toggleMute"
                    @set-volume="setVolumeByRatio" />

                <div class="mx-2 hidden h-6 w-px bg-white/10 sm:block"></div>

                <div class="text-xs tabular-nums text-white/60 min-w-24 px-1">
                    <span class="text-white/90">{{ formattedCurrentTime }}</span>
                    <span class="mx-1 text-white/30">/</span>
                    <span>{{ formattedDuration }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <WatchButton type="button" @click="showSpeedPanel = true" title="Playback speed">
                    <ClockIcon class="h-5 w-5" />
                </WatchButton>
                <WatchButton type="button" @click="showAudioPanel = true" title="Audio tracks">
                    <MusicalNoteIcon class="h-5 w-5" />
                </WatchButton>
                <WatchButton type="button" @click="showCastPanel = true" title="Cast">
                    <TvIcon class="h-5 w-5" />
                </WatchButton>

                <div class="mx-2 hidden h-6 w-px bg-white/10 sm:block"></div>

                <WatchButton type="button" class="disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!pipSupported" @click="togglePip" :title="t('pip')">
                    <RectangleStackIcon class="h-5 w-5" />
                </WatchButton>
                <WatchButton type="button" @click="toggleFullscreen" :title="t('fullscreen')">
                    <ArrowsPointingOutIcon v-if="!isFullscreen" class="h-5 w-5" />
                    <ArrowsPointingInIcon v-else class="h-5 w-5" />
                </WatchButton>
            </div>
        </div>
    </div>

    <Panel v-if="controlsVisible" v-model="showSpeedPanel" title="Playback speed">
        <div class="space-y-1.5">
            <button v-for="speed in speedOptions" :key="speed" type="button"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200" :class="[
                    playbackRate === speed
                        ? 'bg-theme-accent/20 border border-theme-accent/40 text-theme-accent'
                        : 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white',
                ]" @click="setPlaybackRate(speed)">
                x{{ speed.toFixed(2) }}
            </button>
        </div>
    </Panel>

    <Panel v-if="controlsVisible" v-model="showAudioPanel" title="Audio tracks">
        <div class="space-y-1.5">
            <button v-for="track in audioTrackOptions" :key="track.index" type="button"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200" :class="[
                    selectedAudioTrackIndex === track.index
                        ? 'bg-theme-accent/20 border border-theme-accent/40 text-theme-accent'
                        : 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white',
                ]" @click="setAudioTrack(track.index)">
                <p class="text-sm font-medium">{{ track.label }}</p>
                <p v-if="track.meta" class="text-xs mt-0.5 opacity-60">{{ track.meta }}</p>
            </button>

            <p v-if="!audioTracksSupported" class="text-white/40 text-sm text-center py-8">
                {{ audioTrackUnsupportedMessage }}
            </p>
            <p v-else-if="audioTracksSupported && !audioTrackOptions.length"
                class="text-white/40 text-sm text-center py-8">
                {{ audioTrackEmptyMessage }}
            </p>
        </div>
    </Panel>

    <Panel v-if="controlsVisible" v-model="showCastPanel" title="Cast to device">
        <div class="space-y-2">
            <button type="button"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200" :class="remotePlaybackSupported
                    ? 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white'
                    : 'bg-white/5 border border-transparent text-white/40 cursor-not-allowed'"
                :disabled="!remotePlaybackSupported" @click="promptRemotePlayback">
                <p class="text-sm font-medium">Chromecast / Google Cast</p>
                <p class="text-xs mt-0.5 opacity-60">Open connected devices picker</p>
            </button>

            <button type="button"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200" :class="airPlaySupported
                    ? 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white'
                    : 'bg-white/5 border border-transparent text-white/40 cursor-not-allowed'"
                :disabled="!airPlaySupported" @click="promptAirPlay">
                <p class="text-sm font-medium">Apple AirPlay</p>
                <p class="text-xs mt-0.5 opacity-60">Open AirPlay devices picker</p>
            </button>

            <p v-if="castStatusMessage" class="text-xs text-white/70 px-1 pt-2">
                {{ castStatusMessage }}
            </p>
            <p v-else-if="!remotePlaybackSupported && !airPlaySupported" class="text-white/40 text-sm text-center py-8">
                Casting is not supported in this browser/device
            </p>
        </div>
    </Panel>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useTranslation } from '@/Composables/useTranslation';
import {
    ArrowsPointingInIcon,
    ArrowsPointingOutIcon,
    BackwardIcon,
    ClockIcon,
    ForwardIcon,
    MusicalNoteIcon,
    PauseIcon,
    PlayIcon,
    RectangleStackIcon,
    TvIcon,
} from '@heroicons/vue/24/outline';
import Panel from '@/Components/Panel.vue';
import Spinner from '@/Components/Spinner.vue';
import PlayerTimeline from '@/Components/Watch/PlayerTimeline.vue';
import PlayerVolumeControl from '@/Components/Watch/PlayerVolumeControl.vue';
import WatchButton from './WatchButton.vue';

const { t } = useTranslation();

const props = defineProps({
    streamUrl: { type: String, default: '' },
    showSkipNow: { type: Boolean, default: false },
    currentTime: { type: Number, default: 0 },
    duration: { type: Number, default: 0 },
    videoRef: { type: Object, default: null },
    playerContainerRef: { type: Object, default: null },
    item: { type: Object, default: null },
    pageTitle: { type: String, default: '' },
    controlsVisible: { type: Boolean, default: true },
});

const emit = defineEmits([
    'skip-now',
    'time-update',
    'loaded-metadata',
    'video-ready',
]);

const videoElement = ref(null);
const isPlaying = ref(false);
const isBuffering = ref(true);
const singleClickTimer = ref(null);
const isMuted = ref(false);
const volume = ref(1);
const isFullscreen = ref(false);
const playbackRate = ref(1);
const showSpeedPanel = ref(false);
const showAudioPanel = ref(false);
const showCastPanel = ref(false);
const castStatusMessage = ref('');
const audioTrackOptions = ref([]);
const selectedAudioTrackIndex = ref(-1);
const audioTracksEventTarget = ref(null);
const speedOptions = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2];
const VIDEO_SINGLE_CLICK_DELAY_MS = 220;
const pipSupported = computed(() =>
    typeof document !== 'undefined' ? (document.pictureInPictureEnabled ?? false) : false
);
const audioTracksSupported = computed(() => {
    const video = getVideo();
    const tracks = video?.audioTracks;
    return Boolean(tracks && typeof tracks.length === 'number');
});
const isRealDebridDirectStream = computed(() => {
    const url = (props.streamUrl ?? '').toLowerCase();
    return url.includes('real-debrid') || url.includes('download.real-debrid.com');
});
const audioTrackUnsupportedMessage = computed(() => {
    if (isRealDebridDirectStream.value) {
        return 'This browser cannot switch audio tracks on direct Real-Debrid links';
    }
    return 'Audio track switching is not supported by this browser';
});
const audioTrackEmptyMessage = computed(() => {
    if (isRealDebridDirectStream.value) {
        return 'No switchable audio tracks exposed on this Real-Debrid stream';
    }
    return 'No alternate audio tracks found for this stream';
});
const remotePlaybackSupported = computed(() => {
    const video = getVideo();
    return Boolean(video && video.remote && typeof video.remote.prompt === 'function');
});
const airPlaySupported = computed(() => {
    const video = getVideo();
    return Boolean(video && typeof video.webkitShowPlaybackTargetPicker === 'function');
});

const formattedCurrentTime = computed(() => formatTime(props.currentTime));
const formattedDuration = computed(() => formatTime(props.duration));
const showCenterSpinner = computed(() => isBuffering.value || !isPlaying.value);

function getVideo() {
    return videoElement.value ?? unwrapElement(props.videoRef);
}

function getContainer() {
    return unwrapElement(props.playerContainerRef);
}

function unwrapElement(value) {
    if (!value) return null;
    const canUseElementCtor = typeof Element !== 'undefined';
    if (canUseElementCtor && value instanceof Element) return value;
    if (typeof value === 'object' && 'value' in value) {
        if (!canUseElementCtor) return null;
        return value.value instanceof Element ? value.value : null;
    }
    return null;
}

function formatTime(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) return '00:00';
    const total = Math.floor(seconds);
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;
    if (h > 0) return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function syncVideoState() {
    const video = getVideo();
    if (!video) return;
    isPlaying.value = !video.paused && !video.ended;
    isMuted.value = video.muted;
    volume.value = video.volume ?? 1;
}

function syncFullscreenState() {
    const container = getContainer();
    isFullscreen.value = !!container && document.fullscreenElement === container;
}

function getAudioTrackLabel(track, index) {
    const label = track?.label?.trim();
    if (label) return label;
    const language = track?.language?.trim();
    if (language) return language.toUpperCase();
    return `Track ${index + 1}`;
}

function getAudioTrackMeta(track) {
    const parts = [];
    if (track?.language) parts.push(track.language.toUpperCase());
    if (track?.kind) parts.push(track.kind);
    return parts.join(' • ');
}

function syncAudioTracks() {
    const video = getVideo();
    const tracks = video?.audioTracks;
    if (!tracks || typeof tracks.length !== 'number') {
        audioTrackOptions.value = [];
        selectedAudioTrackIndex.value = -1;
        return;
    }

    const nextOptions = [];
    let nextSelectedIndex = -1;

    for (let i = 0; i < tracks.length; i += 1) {
        const track = tracks[i];
        if (track?.enabled) nextSelectedIndex = i;
        nextOptions.push({
            index: i,
            label: getAudioTrackLabel(track, i),
            meta: getAudioTrackMeta(track),
        });
    }

    audioTrackOptions.value = nextOptions;
    selectedAudioTrackIndex.value = nextSelectedIndex;
}

function detachAudioTrackListener() {
    const target = audioTracksEventTarget.value;
    if (!target || typeof target.removeEventListener !== 'function') return;
    target.removeEventListener('change', syncAudioTracks);
    audioTracksEventTarget.value = null;
}

function attachAudioTrackListener(video) {
    detachAudioTrackListener();
    const tracks = video?.audioTracks;
    if (!tracks || typeof tracks.addEventListener !== 'function') return;
    tracks.addEventListener('change', syncAudioTracks);
    audioTracksEventTarget.value = tracks;
}

function bindVideoRef(element) {
    videoElement.value = element ?? null;
    emit('video-ready', videoElement.value);
}

function handleTimeUpdate() {
    const video = getVideo();
    syncVideoState();
    emit('time-update', {
        currentTime: video?.currentTime ?? 0,
        duration: Number.isFinite(video?.duration) ? video.duration : 0,
    });
}

function handleLoadedMetadata() {
    const video = getVideo();
    isBuffering.value = false;
    syncVideoState();
    syncAudioTracks();
    attachAudioTrackListener(video);
    emit('loaded-metadata', {
        currentTime: video?.currentTime ?? 0,
        duration: Number.isFinite(video?.duration) ? video.duration : 0,
    });
}

function handleVideoLoadStart() {
    isBuffering.value = true;
}

function handleVideoWaiting() {
    isBuffering.value = true;
}

function handleVideoCanPlay() {
    isBuffering.value = false;
}

function handleVideoPlaying() {
    isBuffering.value = false;
    syncVideoState();
}

function handleVideoPause() {
    syncVideoState();
}

function handleVideoError() {
    isBuffering.value = false;

}

function handleSeekByRatio(ratio) {
    const video = getVideo();
    if (!video) return;
    const targetDuration = Number.isFinite(props.duration) && props.duration > 0
        ? props.duration
        : (Number.isFinite(video.duration) ? video.duration : 0);
    if (!targetDuration) return;
    video.currentTime = ratio * targetDuration;
}

async function togglePlayPause() {
    const video = getVideo();
    if (!video) return;
    if (video.paused) {
        try {
            await video.play();
        } catch (error) {
            // Prevent unhandled promise rejection on unsupported formats (e.g. MKV in some browsers).
            const maybeMessage = error?.message ? String(error.message) : '';
            if (maybeMessage.toLowerCase().includes('no supported sources')) {
                castStatusMessage.value = 'This video format is not supported by your browser';
            }
        }
    } else {
        video.pause();
    }
}

function clearSingleClickTimer() {
    if (singleClickTimer.value === null) return;
    window.clearTimeout(singleClickTimer.value);
    singleClickTimer.value = null;
}

function handleVideoClick() {
    clearSingleClickTimer();
    singleClickTimer.value = window.setTimeout(async () => {
        singleClickTimer.value = null;
        await togglePlayPause();
    }, VIDEO_SINGLE_CLICK_DELAY_MS);
}

async function handleVideoDoubleClick() {
    clearSingleClickTimer();
    await toggleFullscreen();
}

function seekBy(deltaSeconds) {
    const video = getVideo();
    if (!video) return;
    const next = (video.currentTime ?? 0) + deltaSeconds;
    const max = Number.isFinite(video.duration) ? video.duration : Infinity;
    video.currentTime = Math.max(0, Math.min(max, next));
}

function toggleMute() {
    const video = getVideo();
    if (!video) return;
    video.muted = !video.muted;
    syncVideoState();
}

function setVolumeByRatio(ratio) {
    const video = getVideo();
    if (!video) return;
    video.volume = Math.min(1, Math.max(0, ratio));
    if (video.volume > 0) video.muted = false;
    syncVideoState();
}

function adjustVolume(delta) {
    const video = getVideo();
    if (!video) return;
    setVolumeByRatio((video.volume ?? 1) + delta);
}

function setPlaybackRate(rate) {
    const video = getVideo();
    if (!video || !Number.isFinite(rate) || rate <= 0) return;
    playbackRate.value = rate;
    video.playbackRate = rate;
    showSpeedPanel.value = false;
}

function setAudioTrack(index) {
    const video = getVideo();
    const tracks = video?.audioTracks;
    if (!tracks || typeof tracks.length !== 'number') return;

    for (let i = 0; i < tracks.length; i += 1) {
        const track = tracks[i];
        if (track && 'enabled' in track) {
            track.enabled = i === index;
        }
    }

    selectedAudioTrackIndex.value = index;
    syncAudioTracks();
    showAudioPanel.value = false;
}

async function togglePip() {
    const video = getVideo();
    if (!video || !pipSupported.value) return;
    if (document.pictureInPictureElement) {
        await document.exitPictureInPicture();
        return;
    }
    await video.requestPictureInPicture();
}

async function toggleFullscreen() {
    const container = getContainer();
    if (!container) return;
    if (document.fullscreenElement) {
        await document.exitFullscreen();
        return;
    }
    await container.requestFullscreen();
}

async function promptRemotePlayback() {
    const video = getVideo();
    if (!video || !video.remote || typeof video.remote.prompt !== 'function') {
        castStatusMessage.value = 'Chromecast is not supported in this browser';
        return;
    }

    castStatusMessage.value = '';
    try {
        await video.remote.prompt();
        castStatusMessage.value = 'Cast device selected';
        showCastPanel.value = false;
    } catch (error) {
        if (error?.name === 'NotAllowedError') {
            castStatusMessage.value = 'Device selection canceled';
            return;
        }
        castStatusMessage.value = 'Unable to start casting';
    }
}

function promptAirPlay() {
    const video = getVideo();
    if (!video || typeof video.webkitShowPlaybackTargetPicker !== 'function') {
        castStatusMessage.value = 'AirPlay is not supported in this browser';
        return;
    }
    castStatusMessage.value = '';
    video.webkitShowPlaybackTargetPicker();
}

onMounted(() => {
    const video = getVideo();
    if (video) {
        video.addEventListener('play', syncVideoState);
        video.addEventListener('pause', syncVideoState);
        video.addEventListener('ended', syncVideoState);
        video.addEventListener('volumechange', syncVideoState);
        video.playbackRate = playbackRate.value;
        syncAudioTracks();
        attachAudioTrackListener(video);
        syncVideoState();
    }
    document.addEventListener('fullscreenchange', syncFullscreenState);
    syncFullscreenState();
});

onUnmounted(() => {
    clearSingleClickTimer();
    const video = getVideo();
    if (video) {
        video.removeEventListener('play', syncVideoState);
        video.removeEventListener('pause', syncVideoState);
        video.removeEventListener('ended', syncVideoState);
        video.removeEventListener('volumechange', syncVideoState);
    }
    detachAudioTrackListener();
    document.removeEventListener('fullscreenchange', syncFullscreenState);
});

watch(() => unwrapElement(props.videoRef), (nextVideo, prevVideo) => {
    if (prevVideo) {
        prevVideo.removeEventListener('play', syncVideoState);
        prevVideo.removeEventListener('pause', syncVideoState);
        prevVideo.removeEventListener('ended', syncVideoState);
        prevVideo.removeEventListener('volumechange', syncVideoState);
    }
    if (nextVideo) {
        nextVideo.addEventListener('play', syncVideoState);
        nextVideo.addEventListener('pause', syncVideoState);
        nextVideo.addEventListener('ended', syncVideoState);
        nextVideo.addEventListener('volumechange', syncVideoState);
        nextVideo.playbackRate = playbackRate.value;
        attachAudioTrackListener(nextVideo);
        syncAudioTracks();
        syncVideoState();
    } else {
        detachAudioTrackListener();
        audioTrackOptions.value = [];
        selectedAudioTrackIndex.value = -1;
    }
});

watch(() => props.streamUrl, () => {
    // Source switches can reset native video state; refresh controls.
    isBuffering.value = true;
    syncVideoState();
    syncAudioTracks();
});

watch(() => props.controlsVisible, (visible) => {
    if (visible) return;
    showSpeedPanel.value = false;
    showAudioPanel.value = false;
    showCastPanel.value = false;
});
</script>

<style scoped>
.player-controls {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.4) 60%, transparent 100%);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(4px);
}

/* Spinner Image Fade Effect */
.fade-opacity-spinner-enter-active,
.fade-opacity-spinner-leave-active {
    transition: opacity 0.8s;
}

.fade-opacity-spinner-enter-from,
.fade-opacity-spinner-leave-to {
    opacity: 0;
}

.fade-opacity-img {
    animation: fadeOpacityInfinite 1.5s infinite alternate;
}

@keyframes fadeOpacityInfinite {
    0% {
        opacity: 0.5;
    }

    100% {
        opacity: 1;
    }
}
</style>
