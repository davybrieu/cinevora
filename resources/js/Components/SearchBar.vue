<template>
    <div class="relative" ref="searchWrapper">
        <div class="flex h-10 items-center overflow-hidden rounded bg-white/10 transition-all duration-300"
            :class="isOpen ? 'w-64 border border-white/20 bg-black/80 backdrop-blur-md sm:w-80' : 'w-10 hover:bg-white/15'">
            <button @click="toggleSearch"
                class="flex h-10 w-10 flex-shrink-0 items-center justify-center text-white transition-colors cursor-pointer"
                :class="isOpen ? 'text-white/70 hover:text-white' : ''">
                <MagnifyingGlassIcon class="h-5 w-5" />
            </button>
            <input v-show="isOpen" ref="searchInput" v-model="query" @keydown.enter="goToSearch"
                @keydown.escape="closeSearch" type="text" :placeholder="t('search_placeholder')"
                class="h-10 w-full bg-transparent pr-3 text-sm text-white placeholder-white/40 outline-none" />
            <button v-if="isOpen && query" @click="clearSearch"
                class="mr-2 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-white/10 text-white/50 transition-colors hover:bg-white/20 hover:text-white cursor-pointer">
                <XMarkIcon class="h-3 w-3" />
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const { t } = useTranslation();

const searchWrapper = ref(null);
const searchInput = ref(null);
const isOpen = ref(false);
const query = ref('');

function toggleSearch() {
    if (isOpen.value && !query.value) {
        closeSearch();
    } else {
        isOpen.value = true;
        nextTick(() => searchInput.value?.focus());
    }
}

function closeSearch() {
    isOpen.value = false;
    query.value = '';
}

function clearSearch() {
    query.value = '';
    searchInput.value?.focus();
}

function goToSearch() {
    if (!query.value.trim()) return;
    router.get(route('browse.index'), { query: query.value.trim() });
    closeSearch();
}
</script>
