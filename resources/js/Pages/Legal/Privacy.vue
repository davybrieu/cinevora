<template>
    <AppLayout :title="t('privacy_policy')" :description="t('page_header_privacy_desc')">
        <div class="mx-auto max-w-3xl px-4 pb-20 pt-10 sm:px-8 md:px-16">
            <Card>
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
