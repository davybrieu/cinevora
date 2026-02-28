<template>
    <div class="flex items-center gap-1">
        <Button
            :variant="isLiked ? 'primary' : 'secondary'"
            :size="size"
            :class="buttonClass"
            @click.stop="setReaction('like')"
            :title="t('like')"
        >
            <HandThumbUpIcon v-if="isLiked" class="h-5 w-5" />
            <HandThumbUpIconOutline v-else class="h-5 w-5" />
            <span class="ml-1">{{ likeCount ?? 0 }}</span>
        </Button>
        <Button
            :variant="isDisliked ? 'danger' : 'secondary'"
            :size="size"
            :class="buttonClass"
            @click.stop="setReaction('dislike')"
            :title="t('dislike')"
        >
            <HandThumbDownIcon v-if="isDisliked" class="h-5 w-5" />
            <HandThumbDownIconOutline v-else class="h-5 w-5" />
            <span class="ml-1">{{ dislikeCount ?? 0 }}</span>
        </Button>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTranslation } from '../Composables/useTranslation.js';
import Button from './Button.vue';
import { HandThumbUpIcon, HandThumbDownIcon } from '@heroicons/vue/24/solid';
import { HandThumbUpIcon as HandThumbUpIconOutline, HandThumbDownIcon as HandThumbDownIconOutline } from '@heroicons/vue/24/outline';

const { t } = useTranslation();
const page = usePage();

const props = defineProps({
    itemId: { type: Number, required: true },
    itemType: { type: String, required: true },
    likeCount: { type: Number, default: undefined },
    dislikeCount: { type: Number, default: undefined },
    size: { type: String, default: 'sm' },
    buttonClass: { type: String, default: '' },
});

const isLiked = computed(() => {
    const likeIds = page.props.auth?.profile?.like_ids ?? [];
    return likeIds.some((item) => item.type === props.itemType && item.id === props.itemId);
});

const isDisliked = computed(() => {
    const dislikeIds = page.props.auth?.profile?.dislike_ids ?? [];
    return dislikeIds.some((item) => item.type === props.itemType && item.id === props.itemId);
});

function setReaction(reaction) {
    router.post(route('item_reactions.set'), {
        item_id: props.itemId,
        item_type: props.itemType,
        reaction,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>
