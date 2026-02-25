<template>
    <AppLayout :title="t('settings')" :description="t('page_header_settings_desc')">
        <div class="min-h-screen pt-10 pb-12">
            <div class="px-8 md:px-16">
                <div class="flex flex-col gap-8 lg:flex-row">
                    <!-- Tabs navigation (left side) -->
                    <nav class="w-full shrink-0 lg:w-64">
                        <div class="rounded-xl border border-white/5 bg-white/[0.03] p-2">
                            <Link v-for="tab in tabs" :key="tab.id" :href="tab.href"
                                class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium transition"
                                :class="isActive(tab.route)
                                    ? 'bg-theme-accent text-white'
                                    : 'text-white/60 hover:bg-white/5 hover:text-white'">
                                <component :is="tab.icon" class="h-5 w-5" />
                                {{ tab.label }}
                            </Link>
                        </div>
                    </nav>

                    <!-- Content (right side) -->
                    <div class="flex-1">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslation } from '@/Composables/useTranslation.js';
import AppLayout from '@/Layouts/AppLayout.vue';
import { UserIcon, UserCircleIcon, KeyIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const { t } = useTranslation();
const page = usePage();

const tabs = computed(() => [
    { id: 'profile', label: t('profile'), icon: UserIcon, href: route('settings.profile'), route: 'settings.profile' },
    { id: 'account', label: t('account'), icon: UserCircleIcon, href: route('settings.account'), route: 'settings.account' },
    { id: 'password', label: t('password'), icon: KeyIcon, href: route('settings.password'), route: 'settings.password' },
    { id: 'danger', label: t('danger_zone'), icon: ExclamationTriangleIcon, href: route('settings.deleteaccount'), route: 'settings.deleteaccount' },
]);

function isActive(routeName) {
    return route().current(routeName);
}
</script>
