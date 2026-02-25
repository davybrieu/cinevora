<template>
    <AppLayout :title="t('viewing_history_title')" :description="t('page_header_viewing_history_desc')">
        <div class="px-8 pb-20 pt-10 md:px-16">
            <div v-if="hasHistory" class="mb-6 flex justify-end">
                <Button variant="primary" size="md" :disabled="clearing" @click="clearAllHistory">
                    {{ clearing ? t('loading') : t('viewing_history_clear_all') }}
                </Button>
            </div>

            <div v-if="!hasHistory" class="py-20 text-center">
                <p class="text-lg text-white/60">{{ t('viewing_history_empty') }}</p>
                <Button :href="route('home')" variant="primary" size="md" class="mt-6">
                    {{ t('browse_catalog') }}
                </Button>
            </div>

            <div v-else class="grid gap-10 lg:grid-cols-[320px_1fr]">
                <!-- Left: Stats -->
                <section>
                    <h2 class="mb-4 text-lg font-semibold text-white">{{ t('viewing_history_some_stats') }}</h2>
                    <div class="space-y-3">
                        <Card>
                            <p class="text-xs text-white/50">{{ t('viewing_history_movies_watched') }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ stats.movies_watched }}</p>
                        </Card>
                        <Card>
                            <p class="text-xs text-white/50">{{ t('viewing_history_series_started') }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ stats.series_started }}</p>
                        </Card>
                        <Card>
                            <p class="text-xs text-white/50">{{ t('viewing_history_episodes') }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ stats.episodes_count }}</p>
                        </Card>
                        <Card>
                            <p class="text-xs text-white/50">{{ t('viewing_history_total_time') }}</p>
                            <p class="mt-1 text-lg font-semibold text-white">{{ stats.total_time_formatted }}</p>
                        </Card>
                    </div>
                </section>

                <!-- Right: History -->
                <section>
                    <div class="space-y-8">
                        <div v-for="group in historyByDate" :key="group.date" class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-theme-accent" />
                                <span class="text-sm font-medium text-white/80">{{ group.date_label }}</span>
                            </div>
                            <div class="space-y-2">
                                <Card v-for="entry in group.entries" :key="entry.id"
                                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-4">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-white">{{ entry.title }}</p>
                                        <p class="mt-0.5 flex items-center gap-1 text-sm"
                                            :class="entry.is_completed ? 'text-emerald-400' : 'text-amber-400'">
                                            <CheckCircleIcon v-if="entry.is_completed" class="h-4 w-4 shrink-0" />
                                            <ExclamationCircleIcon v-else class="h-4 w-4 shrink-0" />
                                            {{ entry.status_label }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <Button :href="entry.detail_url" variant="secondary" size="sm">
                                            {{ t('view_details') }}
                                        </Button>
                                        <Button variant="danger" size="sm" :processing="deletingId === entry.id"
                                            @click="deleteEntry(entry.id)">
                                            <TrashIcon class="h-5 w-5" />
                                        </Button>
                                    </div>
                                </Card>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import AppLayout from '../Layouts/AppLayout.vue';
import Card from '../Components/Card.vue';
import Button from '../Components/Button.vue';
import { CheckCircleIcon, ExclamationCircleIcon, TrashIcon } from '@heroicons/vue/24/solid';

const { t } = useTranslation();

const props = defineProps({
    stats: { type: Object, required: true },
    historyByDate: { type: Array, required: true },
});

const clearing = ref(false);
const deletingId = ref(null);

const hasHistory = computed(() => {
    return props.historyByDate.some((g) => g.entries.length > 0);
});

function clearAllHistory() {
    if (!confirm(t('viewing_history_clear_confirm'))) return;
    clearing.value = true;
    router.delete(route('viewing_history.destroy_all'), {
        onFinish: () => { clearing.value = false; },
    });
}

function deleteEntry(id) {
    if (!confirm(t('viewing_history_delete_confirm'))) return;
    deletingId.value = id;
    router.delete(route('viewing_history.destroy', { id }), {
        onFinish: () => { deletingId.value = null; },
    });
}
</script>
