<template>
    <div class="flex items-center mb-3">
        <h3 class="text-sm font-semibold text-theme-text-muted">
            {{ title }}
            <span v-if="count !== null" class="text-base font-normal text-white/30">({{ count }})</span>
        </h3>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    count: { type: Number, default: null },
});
</script>
