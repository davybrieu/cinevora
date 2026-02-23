<template>
    <div class="grid grid-cols-2 gap-2 px-8 md:grid-cols-4 md:gap-4 md:px-16 lg:grid-cols-10">
        <Link v-for="provider in providers.slice(0, 10)" :key="provider.id ?? 'all'" :href="provider.id && currentProvider === provider.id
            ? route('home')
            : (provider.id ? route('home', { provider: provider.id }) : route('home'))"
            class="flex items-center justify-center gap-2 rounded-md border px-4 py-2 transition-all duration-200 group"
            :class="currentProvider === provider.id
                ? 'border-white  text-black shadow-lg shadow-white/10'
                : 'border-white/10 bg-white/5 text-white/80 hover:border-white/25 hover:bg-white/10 hover:text-white'">
            <template v-if="provider.id === null">
                <span class="text-white/80">{{ provider.name }}</span>
            </template>
            <template v-else>
                <img v-if="provider.logo" :src="provider.logo" :alt="provider.name"
                    class="h-20 w-20 transition-transform duration-200 group-hover:scale-115" loading="lazy" />
            </template>
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
    providers: { type: Array, required: true },
    currentProvider: { type: Number, default: null },
    baseUrl: { type: String, default: '/' },
});
</script>
