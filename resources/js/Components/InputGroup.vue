<template>
    <div>
        <div v-if="label || $slots.labelSuffix" class="mb-1.5 flex items-center justify-between gap-2">
            <label v-if="label" :for="id" :class="sizeLabelClass">{{ label }}</label>
            <slot name="labelSuffix" />
        </div>
        <div class="relative">
            <input :id="id" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" :type="type"
                :placeholder="placeholder" :required="required" :autofocus="autofocus" :maxlength="maxlength" :class="[
                    'w-full rounded-md border border-white/10 bg-white/5 text-white placeholder-white/30 outline-none transition focus:border-theme-accent focus:ring-1 focus:ring-theme-accent',
                    inputSizeClass,
                    { 'pr-12': $slots.inputSuffix }
                ]" />
            <div v-if="$slots.inputSuffix" class="absolute inset-y-0 right-2 flex items-center">
                <slot name="inputSuffix" />
            </div>
        </div>
        <p v-if="error" class="mt-1.5 text-sm text-red-400">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: null },
    id: { type: String, default: null },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: null },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
    autofocus: { type: Boolean, default: false },
    maxlength: { type: [Number, String], default: null },
    size: {
        type: String,
        default: 'md', // default size is 'md'
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
});

defineEmits(['update:modelValue']);

const inputSizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-2 py-2 text-sm';
        case 'lg':
            return 'px-6 py-4 text-lg';
        case 'md':
            return 'px-4 py-3 text-base';
        default:
            return 'px-4 py-3 text-base';
    }
});

const sizeLabelClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'text-xs text-white/70';
        case 'lg':
            return 'text-lg text-white/70';
        case 'md':
        default:
            return 'text-sm text-white/70';
    }
});
</script>
