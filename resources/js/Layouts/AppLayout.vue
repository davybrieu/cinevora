<template>

    <Head :title="title" />
    <div class="min-h-screen bg-theme-dark">
        <Navbar />
        <Toast />
        <slot />
        <Footer />
    </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { Head } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>
