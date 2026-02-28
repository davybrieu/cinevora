<template>
    <div>
        <p ref="contentRef" :class="[textClass, { [lineClampClass]: !expanded }]" class="leading-relaxed">
            {{ text || fallback }}
        </p>
        <button v-if="shouldShowToggle" type="button"
            class="cursor-pointer mt-2 text-sm text-theme-accent hover:underline" @click="expanded = !expanded">
            {{ expanded ? t('show_less') : t('show_more_bio') }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { useTranslation } from '../Composables/useTranslation.js';

const { t } = useTranslation();

const props = defineProps({
    text: { type: String, default: '' },
    fallback: { type: String, default: '' },
    textClass: { type: String, default: '' },
    lineClamp: { type: Number, default: 4 },
});

const lineClampClass = computed(() =>
    props.lineClamp === 6 ? 'line-clamp-6' : 'line-clamp-4'
);
const expanded = ref(false);
const contentRef = ref(null);
const shouldShowToggle = ref(false);

function checkOverflow() {
    if (!contentRef.value) {
        shouldShowToggle.value = false;
        return;
    }
    const content = props.text || props.fallback;
    if (!content) {
        shouldShowToggle.value = false;
        return;
    }
    const wasExpanded = expanded.value;
    if (wasExpanded) expanded.value = false;

    nextTick(() => {
        requestAnimationFrame(() => {
            const el = contentRef.value;
            if (!el) return;
            shouldShowToggle.value = el.scrollHeight > el.offsetHeight + 2;
            if (wasExpanded) expanded.value = true;
        });
    });
}

let resizeObserver = null;

onMounted(() => {
    checkOverflow();
    nextTick(() => {
        resizeObserver = new ResizeObserver(() => checkOverflow());
        if (contentRef.value) {
            resizeObserver.observe(contentRef.value);
        }
    });
});

onUnmounted(() => {
    resizeObserver?.disconnect();
});

watch(
    () => [props.text, props.fallback],
    () => {
        nextTick(() => checkOverflow());
    }
);

watch(expanded, (val) => {
    if (!val) {
        nextTick(() => checkOverflow());
    }
});
</script>
