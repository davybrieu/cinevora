<template>

    <Head :title="title" />
    <div class="min-h-screen bg-theme-dark">
        <AppLoader />
        <Navbar />
        <Toast />
        <PageHeader v-if="description" :title="title" :description="description" />
        <slot />
        <Footer v-if="footer" />
    </div>
</template>

<script setup>
import AppLoader from '@/Components/AppLoader.vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    footer: { type: Boolean, default: true },
});
</script>
