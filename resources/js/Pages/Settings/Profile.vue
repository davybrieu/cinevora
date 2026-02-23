<template>
    <SettingsLayout>
        <Card>
            <h2 class="mb-2 text-xl font-semibold text-white">{{ t('profile_information') }}</h2>
            <p class="mb-6 text-sm text-white/50">{{ t('profile_information_hint') }}</p>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="mb-3 block text-sm font-medium text-white/70">{{ t('avatar') }}</label>
                    <div class="flex flex-wrap gap-3">
                        <button v-for="avatar in avatars" :key="avatar" type="button" @click="form.avatar = avatar"
                            class="relative h-16 w-16 overflow-hidden rounded-lg border-2 transition"
                            :class="form.avatar === avatar 
                                ? 'border-theme-accent ring-2 ring-theme-accent/50' 
                                : 'border-transparent hover:border-white/20'">
                            <img :src="`/images/avatars/${avatar}`" :alt="avatar" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <InputGroup id="name" v-model="form.name" :label="t('profile_name')" type="text"
                    :placeholder="t('placeholder_profile_name')" :error="form.errors.name" required />

                <div class="flex items-center gap-4">
                    <Button type="submit" variant="primary" size="md" :processing="form.processing">
                        {{ t('save_changes') }}
                    </Button>
                </div>
            </form>
        </Card>
    </SettingsLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useTranslation } from '@/Composables/useTranslation.js';
import SettingsLayout from './Layout.vue';
import Card from '@/Components/Card.vue';
import InputGroup from '@/Components/InputGroup.vue';
import Button from '@/Components/Button.vue';

const { t } = useTranslation();

const props = defineProps({
    profile: { type: Object, required: true },
    avatars: { type: Array, required: true },
});

const form = useForm({
    name: props.profile.name,
    avatar: props.profile.avatar,
});

function submit() {
    form.put(route('settings.profile.update'), {
        preserveScroll: true,
    });
}
</script>
