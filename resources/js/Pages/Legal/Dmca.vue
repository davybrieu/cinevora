<template>
    <AppLayout :title="`${t('dmca')} — theme`">
        <div class="mx-auto max-w-3xl px-8 pb-20 pt-28 md:px-16">
            <Card>
                <h1 class="mb-8 text-3xl font-bold text-white">{{ t('dmca') }}</h1>

                <div class="space-y-6 text-sm leading-relaxed text-white/60">
                    <section v-for="key in ['intro', 'notice', 'counter', 'contact']" :key="key">
                        <h2 class="mb-3 text-lg font-semibold text-white/90">{{ t(`dmca_${key}_title`) }}</h2>
                        <p>{{ t(`dmca_${key}_text`) }}</p>
                    </section>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { useTranslation } from '../../Composables/useTranslation.js';
import AppLayout from '../../Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const { t } = useTranslation();
</script>
