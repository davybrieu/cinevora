import { ref, shallowRef } from 'vue';
import { FFmpeg } from '@ffmpeg/ffmpeg';
import { fetchFile, toBlobURL } from '@ffmpeg/util';

const CORE_VERSION = '0.12.10';
const CORE_BASE = `https://cdn.jsdelivr.net/npm/@ffmpeg/core@${CORE_VERSION}/dist/esm`;
const FFMPEG_BASE = `https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@${CORE_VERSION}/dist/esm`;

/**
 * Composable for transcoding unsupported video formats using FFmpeg.wasm.
 * Use when native video playback fails (e.g. MKV, HEVC in some browsers).
 * All URLs use toBlobURL to avoid cross-origin Worker issues (e.g. Laravel on :8000 vs Vite on :5174).
 *
 * @returns {Object} { transcode, isTranscoding, progress, error, isLoaded }
 */
export function useFfmpegTranscode() {
    const ffmpeg = shallowRef(null);
    const isLoaded = ref(false);
    const isTranscoding = ref(false);
    const progress = ref(0);
    const error = ref(null);

    async function load() {
        if (isLoaded.value && ffmpeg.value) return;

        const instance = new FFmpeg();

        instance.on('progress', ({ progress: p }) => {
            progress.value = Math.round((p ?? 0) * 100);
        });

        const [coreURL, wasmURL, classWorkerURL] = await Promise.all([
            toBlobURL(`${CORE_BASE}/ffmpeg-core.js`, 'text/javascript'),
            toBlobURL(`${CORE_BASE}/ffmpeg-core.wasm`, 'application/wasm'),
            toBlobURL(`${FFMPEG_BASE}/worker.js`, 'text/javascript'),
        ]);

        await instance.load({
            coreURL,
            wasmURL,
            classWorkerURL,
        });

        ffmpeg.value = instance;
        isLoaded.value = true;
    }

    /**
     * Transcode a video URL to MP4 (browser-compatible).
     * @param {string} url - Video URL (must be fetchable, CORS permitting)
     * @returns {Promise<string|null>} Blob URL of transcoded MP4, or null on failure
     */
    async function transcode(url) {
        if (!url) return null;

        error.value = null;
        progress.value = 0;
        isTranscoding.value = true;

        try {
            await load();

            const instance = ffmpeg.value;
            const ext = getExtensionFromUrl(url) || 'mkv';
            const inputName = `input.${ext}`;
            const outputName = 'output.mp4';

            const data = await fetchFile(url);
            await instance.writeFile(inputName, data);

            await instance.exec([
                '-i', inputName,
                '-c:v', 'libx264',
                '-preset', 'fast',
                '-crf', '23',
                '-c:a', 'aac',
                '-b:a', '128k',
                '-movflags', '+faststart',
                outputName,
            ]);

            const outputData = await instance.readFile(outputName);

            await instance.deleteFile(inputName);
            await instance.deleteFile(outputName);

            const blob = new Blob([outputData.buffer], { type: 'video/mp4' });
            return URL.createObjectURL(blob);
        } catch (err) {
            error.value = err?.message ?? String(err);
            return null;
        } finally {
            isTranscoding.value = false;
            progress.value = 100;
        }
    }

    function getExtensionFromUrl(url) {
        try {
            const path = new URL(url).pathname;
            const match = path.match(/\.([a-z0-9]+)(?:\?|$)/i);
            return match ? match[1].toLowerCase() : null;
        } catch {
            return null;
        }
    }

    return {
        transcode,
        isTranscoding,
        progress,
        error,
        isLoaded,
    };
}
