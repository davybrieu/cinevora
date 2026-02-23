<template>
    <div class="fixed top-4 right-4 z-[50000] flex w-full max-w-md flex-col gap-3">
        <TransitionGroup name="toast">
            <div v-for="(toast, idx) in displayToasts" :key="toast.id" :class="[
                'flex items-start gap-3 rounded-lg border p-4 shadow-xl backdrop-blur-sm',
                variantClasses[toast.type],
            ]" role="alert">
                <component :is="iconComponent(toast.type)" class="mt-0.5 h-5 w-5 flex-shrink-0" />
                <div class="min-w-0 flex-1">
                    <p v-if="toast.title" class="mb-1 font-semibold">
                        {{ toast.title }}
                    </p>
                    <p class="text-sm">{{ toast.message }}</p>
                </div>
                <button @click="removeToast(toast.id)"
                    class="flex-shrink-0 cursor-pointer text-current opacity-60 transition-opacity hover:opacity-100"
                    aria-label="Fermer">
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/solid';
import { usePage } from '@inertiajs/vue3';
import { watch, ref, nextTick } from 'vue';

// Types: success, error, warning, info
const variantClasses = {
    success: 'bg-gradient-to-br from-green-50 to-green-100 border border-green-500/20 text-green-800 shadow-lg backdrop-blur-xl',
    error: 'bg-gradient-to-br from-red-50 to-red-100 border border-red-500/20 text-red-800 shadow-lg backdrop-blur-xl',
    warning: 'bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-500/20 text-yellow-800 shadow-lg backdrop-bl-xl',
    info: 'bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-500/20 text-blue-800 shadow-lg backdrop-bl-xl',
};

const iconComponent = (type) => {
    switch (type) {
        case 'success':
            return CheckCircleIcon;
        case 'error':
            return XCircleIcon;
        case 'warning':
            return ExclamationTriangleIcon;
        case 'info':
        default:
            return InformationCircleIcon;
    }
};

// Toast queue
const displayToasts = ref([]);
// Generate unique toast IDs
let nextToastId = 1;
// Track timeouts for auto-hide, so we can cancel on remove
const toastTimeouts = ref({});

const DEFAULT_TOAST_DURATION = 4000;

function showToast({ type, message, title = '' }) {
    // Prevent showing the same message twice at the same time
    if (
        displayToasts.value.some(
            t => t.message === message && t.type === type && t.title === title
        )
    ) {
        return;
    }
    const id = nextToastId++;
    displayToasts.value.push({
        id,
        type,
        message,
        title,
    });

    // Auto-remove after timeout unless manually removed
    toastTimeouts.value[id] = setTimeout(() => {
        removeToast(id);
    }, DEFAULT_TOAST_DURATION);
}

function removeToast(id) {
    displayToasts.value = displayToasts.value.filter(t => t.id !== id);
    if (toastTimeouts.value[id]) {
        clearTimeout(toastTimeouts.value[id]);
        delete toastTimeouts.value[id];
    }
}

// Compose toasts from flash messages each time they change.
// Remove previous toasts when new flash appears
const page = usePage();

// Watch for changes in flash data, show toast(s)
watch(
    () => page.props.flash,
    (flash) => {
        // Remove previous toasts on flash change
        Object.keys(toastTimeouts.value).forEach(removeToast);
        if (!flash) return;

        // Array of all toasts to add
        const newToasts = [];

        if (flash.success) {
            newToasts.push({
                type: 'success',
                message: flash.success,
                title: '',
            });
        }
        if (flash.error) {
            newToasts.push({
                type: 'error',
                message: flash.error,
                title: '',
            });
        }
        if (flash.warning) {
            newToasts.push({
                type: 'warning',
                message: flash.warning,
                title: '',
            });
        }
        if (flash.info) {
            newToasts.push({
                type: 'info',
                message: flash.info,
                title: '',
            });
        }
        if (flash.message) {
            const type = flash.messageType || 'info';
            newToasts.push({
                type,
                message: flash.message,
                title: '',
            });
        }

        // Show new toasts (with short delay to avoid TransitionGroup glitches)
        newToasts.forEach(toast => showToast(toast));
    },
    { deep: true, immediate: true },
);
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
