<template>
    <Dropdown align="right">
        <template #trigger="{ open }">
            <button type="button"
                class="cursor-pointer flex h-10 items-center gap-2 rounded bg-white/10 px-3 text-sm font-semibold uppercase text-white transition duration-200 hover:bg-white/15">
                <GlobeAltIcon class="h-5 w-5" />
            </button>
        </template>
        <template #default="{ close }">
            <button v-for="lang in availableLocales" :key="lang.code" type="button"
                class="cursor-pointer flex w-full px-4 py-3 text-left text-sm font-medium transition gap-2 items-center"
                :class="locale === lang.code ? 'cursor-default bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                :disabled="locale === lang.code" @click="selectLocale(lang.code, close)">
                <img :src="lang.flag" :alt="lang.name" class="h-4 w-5 rounded-sm object-cover" />
                {{ lang.name }}
            </button>
        </template>
    </Dropdown>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { GlobeAltIcon } from '@heroicons/vue/24/outline';
import Dropdown from './Dropdown.vue';

const page = usePage();
const locale = computed(() => page.props.locale);
const availableLocales = computed(() => page.props.availableLocales ?? []);

function selectLocale(lang, close) {
    if (lang === locale.value) return;
    close();
    router.post(route('locale.switch', { locale: lang }), {}, {
        preserveState: false,
        preserveScroll: false,
    });
}
</script>
