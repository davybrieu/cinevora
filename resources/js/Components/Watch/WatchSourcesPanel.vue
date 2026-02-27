<template>
    <Panel :model-value="modelValue" :title="t('sources')" @update:model-value="$emit('update:modelValue', $event)">
        <div class="space-y-1.5">
            <button v-for="(stream, index) in streamList" :key="stream.infoHash || `${stream.source}-${index}`"
                @click="$emit('select-stream', stream)"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200 break-words"
                :class="[
                    selectedStream === stream.infoHash
                        ? 'bg-emerald-600/20 border border-emerald-500/40 text-emerald-400'
                        : 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white',
                ]">
                <p class="text-sm font-medium" v-if="stream.title">{{ stream.title }}</p>
                <p class="text-xs mt-0.5 opacity-60" v-if="stream.name">{{ stream.name }}</p>
                <p class="text-[10px] mt-1 opacity-50 uppercase tracking-wide" v-if="stream.source">
                    {{ stream.source }}
                </p>
            </button>
            <p v-if="!streamList.length" class="text-white/40 text-sm text-center py-8">
                {{ t('no_sources_available') }}
            </p>
        </div>
    </Panel>
</template>

<script setup>
import { useTranslation } from '@/Composables/useTranslation';
import Panel from '@/Components/Panel.vue';

const { t } = useTranslation();

defineProps({
    modelValue: { type: Boolean, default: false },
    streamList: { type: Array, default: () => [] },
    selectedStream: { type: String, default: '' },
});

defineEmits(['update:modelValue', 'select-stream']);
</script>
