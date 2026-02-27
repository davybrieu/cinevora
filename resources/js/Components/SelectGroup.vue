<template>
    <div>
        <div v-if="label || $slots.labelSuffix" class="mb-1.5 flex items-center justify-between gap-2">
            <label v-if="label" :for="id" class="text-sm text-white/70">{{ label }}</label>
            <slot name="labelSuffix" />
        </div>
        <select :id="id" :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value); $emit('change', $event)"
            :required="required"
            class="w-full rounded-md border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-theme-accent focus:ring-1 focus:ring-theme-accent">
            <option v-if="placeholder" value="" class="bg-theme-dark text-white/60">{{ placeholder }}</option>
            <slot />
        </select>
        <p v-if="error" class="mt-1.5 text-sm text-red-400">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: null },
    id: { type: String, default: null },
    placeholder: { type: String, default: null },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
});

defineEmits(['update:modelValue', 'change']);
</script>
