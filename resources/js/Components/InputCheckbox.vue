<template>
    <div class="flex items-center gap-2">
        <input :id="id" :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)"
            type="checkbox"
            class="h-4 w-4 rounded border-white/20 bg-white/5 text-theme-accent focus:ring-theme-accent" />
        <label :for="id" class="text-sm text-white/60">{{ label }}</label>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, required: true },
    id: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>
