<template>
    <div class="overflow-hidden bg-theme-darker" :class="rounded">
        <img v-if="src" :src="src" :alt="alt" class="h-full w-full object-cover" :class="imgClass" loading="lazy"
            draggable="false" />
        <div v-else class="flex h-full items-center justify-center text-theme-text-muted">
            <UserIcon v-if="type === 'person'" class="text-white/10" :class="iconClass" />
            <VideoCameraIcon v-else class="h-12 w-12" />
        </div>
    </div>
</template>

<script setup>
import { UserIcon } from '@heroicons/vue/24/solid';
import { VideoCameraIcon } from '@heroicons/vue/24/outline';

defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: '' },
    type: { type: String, default: 'media' },
    rounded: { type: String, default: 'rounded-xl' },
    imgClass: { type: String, default: '' },
    iconClass: { type: String, default: 'h-20 w-20' },
});
</script>
