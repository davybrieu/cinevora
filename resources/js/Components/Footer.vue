<template>
    <footer class="border-t border-white/10 text-white/60 leading-relaxed">
        <div class="px-8 md:px-16 py-8 pb-5">
            <div class="mb-8 flex flex-col">
                <div class="max-w-3xl">
                    <div class="text-xl text-white font-semibold mb-4">Disclaimer</div>
                    <p>{{ t('footer_disclaimer_hosting', { name: page.props.name }) }}</p>
                    <p>{{ t('footer_disclaimer_legal') }}</p>
                    <p>{{ t('footer_disclaimer_responsibility', { name: page.props.name }) }}</p>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 sm:flex-row sm:justify-between border-t border-white/10 pt-4 text-sm items-center sm:items-center text-center sm:text-left">
                <p class="flex items-center gap-1 flex-wrap">
                    &copy; {{ new Date().getFullYear() }}
                    <span class="font-bold text-white/60">{{ page.props.name }}</span>
                    {{ t('all_rights_reserved') }}
                </p>
                <p class="flex items-center gap-1 flex-wrap">
                    <Link :href="route('privacy')" class="hover:text-theme-accent transition whitespace-nowrap">
                        {{ t('privacy_policy') }}
                    </Link>
                    -
                    <Link :href="route('terms')" class="hover:text-theme-accent transition whitespace-nowrap">
                        {{ t('terms_of_service') }}
                    </Link>
                    -
                    <Link :href="route('dmca')" class="hover:text-theme-accent transition whitespace-nowrap">
                        {{ t('dmca') }}
                    </Link>
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import { usePage } from '@inertiajs/vue3';
import { HeartIcon } from '@heroicons/vue/24/solid';
const { t } = useTranslation();

const page = usePage();
</script>
