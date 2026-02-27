<template>

    <Head :title="title" />
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none z-10 absolute left-0 top-0 right-0 lg:right-grid-xs block w-full">
            <div
                class="h-[600px] pt-grid-xs lg:pt-0 pr-0 w-full flex items-center justify-center lg:pr-grid-xs lg:rounded-xl ring-1 ring-theme-accent/10 ring-inset relative">
                <div class="absolute inset-0 lg:rounded-xl opacity-15 pointer-events-none overflow-hidden">
                    <div class="w-full h-full bg-center bg-theme-accent"></div>
                </div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-theme-dark z-10"></div>
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
