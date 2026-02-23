<template>
    <div class="absolute inset-0 bg-gradient-to-t from-theme-dark via-theme-dark/80 to-theme-dark/40"></div>
    <img :src="`${page.props.tmdb.image_base_url}original/bOGkgRGdhrBYJSLpXaxhXVstddV.jpg`" alt="Background"
        class="absolute inset-0 w-full h-full object-cover opacity-20 pointer-events-none select-none">
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>