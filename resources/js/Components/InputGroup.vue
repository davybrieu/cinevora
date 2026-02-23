<template>
    <div>
        <label v-if="label" :for="id" class="mb-1.5 block text-sm text-white/70">{{ label }}</label>
        <input :id="id" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" :type="type"
            :placeholder="placeholder" :required="required" :autofocus="autofocus" :maxlength="maxlength"
            class="w-full rounded-md border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/30 outline-none transition focus:border-theme-accent focus:ring-1 focus:ring-theme-accent" />
        <p v-if="error" class="mt-1.5 text-sm text-red-400">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: null },
    id: { type: String, default: null },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: null },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
    autofocus: { type: Boolean, default: false },
    maxlength: { type: [Number, String], default: null },
});

defineEmits(['update:modelValue']);
</script>
