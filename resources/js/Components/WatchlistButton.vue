<template>
        <Button :variant="isInWatchlist ? 'primary' : 'secondary'" :size="size" :class="buttonClass"
            :title="isInWatchlist ? t('remove_from_watchlist') : t('add_to_watchlist')"
            @click.stop="toggle">
        <HeartIconSolid v-if="isInWatchlist" class="h-5 w-5" />
        <HeartIcon v-else class="h-5 w-5" />
    </Button>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import Button from './Button.vue';
import { HeartIcon } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';

const { t } = useTranslation();
const page = usePage();

const props = defineProps({
    itemId: { type: Number, required: true },
    itemType: { type: String, required: true },
    size: { type: String, default: 'sm' },
    buttonClass: { type: String, default: '' },
});

const emit = defineEmits(['update:inWatchlist']);

const isInWatchlist = computed(() => {
    const watchlistIds = page.props.auth.profile.watchlist_ids ?? [];
    return watchlistIds.some(
        (item) => item.type === props.itemType && item.id === props.itemId
    );
});

function toggle() {
    const wasInWatchlist = isInWatchlist.value;

    router.post(route('watchlist.toggle'), {
        item_id: props.itemId,
        item_type: props.itemType,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('update:inWatchlist', !wasInWatchlist);
        },
    });
}
</script>
