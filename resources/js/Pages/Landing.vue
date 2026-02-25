<template>
    <AuthLayout :title="t('landing_title')">
        <div class="relative min-h-screen bg-theme-dark flex flex-col">

            <!-- Hero -->
            <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-8 text-center">
                <h1 class="mb-4 max-w-3xl text-4xl font-black leading-tight text-white md:text-6xl">
                    {{ t('landing_title') }}
                </h1>
                <p class="mb-8 max-w-xl text-lg text-theme-text-muted md:text-xl">
                    {{ t('landing_subtitle') }}
                </p>
                <div class="flex items-center gap-4">
                    <Button :href="route('register')" variant="primary" size="lg">
                        {{ t('get_started') }}
                    </Button>
                    <Button :href="route('login')" variant="secondary" size="lg">
                        {{ t('sign_in') }}
                    </Button>
                </div>
            </div>
        </div>

    </AuthLayout>
</template>

<script setup>
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import AuthBackground from '@/Components/AuthBackground.vue';
import { usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
const { t } = useTranslation();
</script>
