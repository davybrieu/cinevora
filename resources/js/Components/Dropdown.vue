<template>
    <div ref="wrapperRef" class="relative inline-block">
        <div @click="toggle" class="cursor-pointer">
            <slot name="trigger" :open="isOpen" />
        </div>
        <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isOpen"
                class="absolute z-50 mt-2 min-w-[120px] overflow-hidden rounded-lg bg-theme-darker shadow-2xl ring-1 ring-white/10 backdrop-blur-md"
                :class="align === 'right' ? 'right-0' : 'left-0'" :style="panelStyle">
                <slot :close="close" />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    /** Controlled open state (use with v-model:open) */
    open: { type: Boolean, default: undefined },
    /** Panel alignment */
    align: { type: String, default: 'right', validator: (v) => ['left', 'right'].includes(v) },
    /** Min width of the panel (e.g. '12rem') */
    minWidth: { type: String, default: null },
});

const emit = defineEmits(['update:open']);

const wrapperRef = ref(null);
const internalOpen = ref(false);

const isOpen = computed({
    get() {
        return props.open !== undefined ? props.open : internalOpen.value;
    },
    set(value) {
        if (props.open !== undefined) {
            emit('update:open', value);
        } else {
            internalOpen.value = value;
        }
    },
});

const panelStyle = computed(() => (props.minWidth ? { minWidth: props.minWidth } : {}));

function toggle() {
    isOpen.value = !isOpen.value;
}

function close() {
    isOpen.value = false;
}

function handleClickOutside(event) {
    if (isOpen.value && wrapperRef.value && !wrapperRef.value.contains(event.target)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('touchstart', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    document.removeEventListener('touchstart', handleClickOutside);
});
</script>
