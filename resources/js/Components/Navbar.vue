<template>
    <nav class="fixed left-0 right-0 top-0 z-40 transition-all duration-500" :class="!isScrolled
        ? 'bg-gradient-to-b from-black/70 to-transparent'
        : 'bg-theme-dark/95 shadow-xl shadow-black/30 backdrop-blur-md'">
        <div class="flex items-center justify-between px-8 py-4 md:px-16">
            <div class="flex items-center gap-8">
                <Link :href="route('landing')" class="shrink-0">
                    <img src="/images/logo.svg" alt="logo" class="w-24">
                </Link>

                <nav v-if="profile" class="hidden items-center gap-1 md:flex">
                    <Link v-for="tab in navTabs" :key="tab.route" :href="route(tab.route)"
                        class="rounded-md px-4 py-2 text-sm font-medium transition" :class="isActive(tab.route)
                            ? 'bg-white/10 text-white'
                            : 'text-white/60 hover:bg-white/5 hover:text-white'">
                        {{ tab.label }}
                    </Link>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <template v-if="!profile || !page.props.auth.user">
                    <Button variant="primary" size="sm" :href="route('login')">
                        {{ t('sign_in') }}
                    </Button>
                    <Button variant="secondary" size="sm" :href="route('register')">
                        {{ t('sign_up') }}
                    </Button>
                </template>

                <SearchBar v-if="profile" />

                <Dropdown v-if="profile" v-model:open="profileDropdownOpen" align="right" min-width="12rem">
                    <template #trigger="{ open }">
                        <button type="button"
                            class="cursor-pointer flex h-10 items-center gap-2 rounded bg-white/10 px-2 text-white transition duration-200 hover:bg-white/15">
                            <img :src="profile.avatar_url" :alt="profile.name"
                                class="h-7 w-7 rounded-full object-cover" />
                        </button>
                    </template>
                    <template #default="{ close }">
                        <div class="border-b border-white/10 px-4 py-3">
                            <p class="text-sm font-semibold text-white">{{ profile.name }}</p>
                        </div>
                        <Link :href="route('watchlist.index')"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-white/70 transition hover:bg-white/10 hover:text-white"
                            @click="close">
                            <HeartIcon class="h-4 w-4" />
                            {{ t('my_watchlist') }}
                        </Link>
                        <button type="button"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-white/70 transition hover:bg-white/10 hover:text-white cursor-pointer"
                            @click="onChangeProfile(close)">
                            <UserGroupIcon class="h-4 w-4" />
                            {{ t('change_profile') }}
                        </button>
                        <Link :href="route('settings.profile')"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-white/70 transition hover:bg-white/10 hover:text-white"
                            @click="close">
                            <Cog6ToothIcon class="h-4 w-4" />
                            {{ t('settings') }}
                        </Link>
                        <div class="border-t border-white/10 mt-1 pt-1">
                            <Button variant="ghost" size="sm" full class="justify-start !rounded-none"
                                @click="onLogout(close)">
                                <ArrowRightStartOnRectangleIcon class="h-4 w-4" />
                                {{ t('sign_out') }}
                            </Button>
                        </div>
                    </template>
                </Dropdown>

                <LanguageSwitcher />
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import SearchBar from './SearchBar.vue';
import LanguageSwitcher from './LanguageSwitcher.vue';
import Dropdown from './Dropdown.vue';
import Button from '@/Components/Button.vue';
import { UserGroupIcon, ArrowRightStartOnRectangleIcon, HeartIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const { t } = useTranslation();

const isScrolled = ref(false);
const profileDropdownOpen = ref(false);

const profile = computed(() => page.props.auth?.profile);

const navTabs = computed(() => [
    { route: 'browse.index', label: t('catalog') },
    { route: 'trending.index', label: t('trending') },
    { route: 'watchlist.index', label: t('watchlist') },
]);

function isActive(routeName) {
    return route().current(routeName);
}

function onChangeProfile(close) {
    close();
    router.post(route('profiles.deselect'));
}

function onLogout(close) {
    close();
    router.post(route('logout'));
}

function handleScroll() {
    isScrolled.value = window.scrollY > 60;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>
