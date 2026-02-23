<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center"
                :class="[backdropClass, size === 'video' ? 'p-2 md:p-4' : 'p-4']"
                @click.self="onBackdropClick"
            >
                <button
                    v-if="showClose"
                    type="button"
                    class="cursor-pointer absolute top-4 right-4 z-50 p-2 rounded-full transition hover:bg-white/10"
                    aria-label="Close"
                    @click="close"
                >
                    <XMarkIcon class="h-6 w-6 text-white" />
                </button>

                <div :class="panelClass" @click.stop>
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { watch, onMounted, onBeforeUnmount } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    open: { type: Boolean, default: false },
    size: { type: String, default: 'md', validator: v => ['sm', 'md', 'lg', 'xl', 'full', 'video', 'auto'].includes(v) },
    showClose: { type: Boolean, default: true },
    closeOnBackdrop: { type: Boolean, default: true },
    closeOnEscape: { type: Boolean, default: true },
    transparent: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open', 'close']);

const backdropClass = 'bg-black/90 backdrop-blur-sm';

const panelClass = props.transparent ? {
    video: 'w-full max-w-[90vw] aspect-video',
    auto: '',
}[props.size] || '' : {
    sm: 'w-full max-w-sm rounded-xl bg-theme-darker p-6 shadow-2xl',
    md: 'w-full max-w-md rounded-xl bg-theme-darker p-8 shadow-2xl',
    lg: 'w-full max-w-lg rounded-xl bg-theme-darker p-8 shadow-2xl',
    xl: 'w-full max-w-xl rounded-xl bg-theme-darker p-8 shadow-2xl',
    full: 'w-full max-w-7xl',
    video: 'w-full max-w-[90vw] aspect-video',
    auto: '',
}[props.size];

function close() {
    emit('update:open', false);
    emit('close');
}

function onBackdropClick() {
    if (props.closeOnBackdrop) {
        close();
    }
}

function handleKeydown(e) {
    if (e.key === 'Escape' && props.open && props.closeOnEscape) {
        close();
    }
}

watch(() => props.open, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
}, { immediate: true });

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>
