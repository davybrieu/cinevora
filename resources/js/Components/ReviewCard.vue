<template>
    <Card>
        <div class="mb-3 flex items-center gap-3">
            <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full bg-theme-darker">
                <img v-if="review.author_avatar" :src="review.author_avatar" :alt="review.author"
                    class="h-full w-full object-cover" loading="lazy" />
                <div v-else class="flex h-full items-center justify-center text-sm font-bold text-white/30">
                    {{ review.author.charAt(0).toUpperCase() }}
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white">{{ review.author }}</p>
                <div class="flex items-center gap-2">
                    <StarRating v-if="review.author_rating" :value="review.author_rating" size="xs" />
                    <span v-if="review.created_at" class="text-xs text-white/20">
                        {{ review.created_at }}
                    </span>
                </div>
            </div>
        </div>
        <p ref="contentRef" class="text-sm leading-relaxed text-white/50" :class="{ 'line-clamp-4': !expanded }"
            v-html="review.content">
        </p>
        <button v-if="shouldShowToggle" type="button"
            class="cursor-pointer mt-2 text-xs text-theme-accent hover:underline" @click="expanded = !expanded">
            {{ expanded ? t('show_less') : t('show_more_bio') }}
        </button>
    </Card>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useTranslation } from '../Composables/useTranslation.js';
import StarRating from './StarRating.vue';
import Card from './Card.vue';

const { t } = useTranslation();

const props = defineProps({
    review: { type: Object, required: true },
});

const expanded = ref(false);
const contentRef = ref(null);
const shouldShowToggle = ref(false);

function checkOverflow() {
    if (!contentRef.value) {
        shouldShowToggle.value = false;
        return;
    }
    // force clamp to be active for accurate measurement by collapsing (if not expanded)
    const wasExpanded = expanded.value;
    if (wasExpanded) expanded.value = false;

    nextTick(() => {
        const el = contentRef.value;
        // check if content is overflowing due to line-clamp
        // scrollHeight = full content height, offsetHeight = visible height
        shouldShowToggle.value = el.scrollHeight > el.offsetHeight + 2; // the +2 handles subpixel differences

        if (wasExpanded) expanded.value = true;
    });
}

onMounted(() => {
    checkOverflow();
});

watch(
    () => props.review.content,
    () => {
        nextTick(() => {
            checkOverflow();
        });
    }
);

watch(expanded, (val) => {
    // recalc overflow when collapsed, since expanded disables clamp
    if (!val) {
        nextTick(() => {
            checkOverflow();
        });
    }
});
</script>
