<template>
    <AppLayout :title="`${t('privacy_policy')} — theme`">
        <div class="mx-auto max-w-3xl px-8 pb-20 pt-28 md:px-16">
            <Card>
                <h1 class="mb-8 text-3xl font-bold text-white">{{ t('privacy_policy') }}</h1>

                <div class="space-y-6 text-sm leading-relaxed text-white/60">
                    <section v-for="(key, i) in ['collect', 'usage', 'cookies', 'thirdparty', 'contact']" :key="key">
                        <h2 class="mb-3 text-lg font-semibold text-white/90">{{ i + 1 }}. {{ t(`privacy_${key}_title`)
                            }}
                        </h2>
                        <p>{{ t(`privacy_${key}_text`) }}</p>
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
