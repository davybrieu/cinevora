<script setup>
import { StarIcon } from '@heroicons/vue/20/solid';
import Button from './Button.vue';

</script>

<template>
    <div
        class="w-full flex items-center bg-theme-accent/10 justify-between flex-col sm:flex-row px-8 lg:px-14 2xl:px-[6.5rem] gap-6 py-14  relative">
        <div class="absolute inset-0 pub-premium-overlay z-10 pointer-events-none"></div>
        <video class="absolute inset-0 w-full h-full object-cover opacity-20 sm:opacity-50 z-0" autoplay muted loop
            playsinline>
            <source src="/images/max.mp4" type="video/mp4">
        </video>
        <div class="flex flex-col gap-6 z-20 relative">
            <h1 class="text-3xl lg:text-5xl !leading-tight font-bold text-white text-center sm:text-left">
                Débloque le max<br>du streaming
            </h1>
            <p
                class="text-center sm:text-left col-start-1 row-start-3 max-w-3xl text-base leading-6 lg:text-lg text-white/80 lg:leading-8">
                blablablablablablablablablablablablablablablablablablablablablablablababab<br>
                ablablablablablablablablablablablablablablabla
            </p>
        </div>
        <div class="flex flex-col items-center sm:items-end xl:flex-row gap-4 z-20 relative">
            <Button href="#" variant="primary" size="lg">
                <StarIcon class="w-5 h-5" />
                Découvrir l'abonnement
            </Button>
        </div>
    </div>

</template>