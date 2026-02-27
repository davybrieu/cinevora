<template>

    <Head :title="title" />
    <div class="relative min-h-screen overflow-hidden bg-theme-dark">
        <div
            class="pointer-events-none absolute inset-0 z-0 bg-image-url('https://zerator.com/assets/images/parallax/03.png')">
        </div>

        <div class="relative z-10">
            <Navbar v-if="navbar" />
            <Toast />
            <PageHeader v-if="description && title" :title="title" :description="description" />
            <slot />
            <Footer v-if="footer" />
        </div>
    </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    footer: { type: Boolean, default: true },
    navbar: { type: Boolean, default: true },
});
</script>
