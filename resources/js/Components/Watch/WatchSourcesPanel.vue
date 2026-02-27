<template>
    <Panel :model-value="modelValue" :title="t('sources')" @update:model-value="$emit('update:modelValue', $event)">
        <div class="space-y-2">
            <InputGroup id="watch-sources-search" v-model="query" type="text" size="sm"
                :placeholder="t('search_placeholder')">
                <template #inputSuffix>
                    <button v-if="query" type="button" @click="query = ''"
                        class="rounded-md p-1 text-white/60 transition hover:bg-white/10 hover:text-white cursor-pointer"
                        :title="t('close')">
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </template>
            </InputGroup>

            <button v-for="(stream, index) in filteredStreams" :key="stream.infoHash || `${stream.source}-${index}`"
                @click="$emit('select-stream', stream)"
                class="cursor-pointer w-full text-left px-4 py-3 rounded-lg transition-all duration-200 break-words"
                :class="[
                    selectedStream === getStreamSelectionId(stream)
                        ? 'bg-theme-accent/20 border border-theme-accent/40 text-theme-accent'
                        : 'bg-white/5 border border-transparent hover:bg-white/10 text-white/80 hover:text-white',
                ]">
                <p class="text-sm font-medium" v-if="stream.title">{{ stream.title }}</p>
                <p class="text-xs mt-0.5 opacity-60" v-if="stream.name">{{ stream.name }}</p>
                <p class="text-[10px] mt-1 opacity-50 uppercase tracking-wide" v-if="stream.source">
                    {{ stream.source }}
                </p>
            </button>
            <p v-if="!filteredStreams.length" class="text-white/40 text-sm text-center py-8">
                {{ t('no_sources_available') }}
            </p>
        </div>
    </Panel>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useTranslation } from '@/Composables/useTranslation';
import Panel from '@/Components/Panel.vue';
import InputGroup from '@/Components/InputGroup.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const { t } = useTranslation();
const query = ref('');

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    streamList: { type: Array, default: () => [] },
    selectedStream: { type: String, default: '' },
});

const filteredStreams = computed(() => {
    const q = query.value.toLowerCase();
    if (!q) return props.streamList;

    return props.streamList.filter((stream) => {
        const haystack = [
            stream?.title ?? '',
            stream?.name ?? '',
            stream?.filename ?? '',
            stream?.source ?? '',
        ]
            .join(' ')
            .toLowerCase();

        return haystack.includes(q);
    });
});

function getStreamSelectionId(stream) {
    return stream?.infoHash || (stream?.url ? '__direct__' : '');
}

defineEmits(['update:modelValue', 'select-stream']);
</script>
