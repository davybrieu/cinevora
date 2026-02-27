<template>
    <SettingsLayout>
        <Card>
            <h2 class="mb-2 text-xl font-semibold text-white">{{ t('account_information') }}</h2>
            <p class="mb-6 text-sm text-white/50">{{ t('account_information_hint') }}</p>

            <form @submit.prevent="submit" class="space-y-5">
                <InputGroup id="name" v-model="form.name" :label="t('name')" type="text"
                    :placeholder="t('placeholder_name')" :error="form.errors.name" required />

                <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                    :placeholder="t('placeholder_email')" :error="form.errors.email" required />

                <div class="space-y-2">
                    <InputGroup id="torrentio_realdebrid_key" v-model="form.torrentio_realdebrid_key"
                        label="Real-Debrid API key" type="text" placeholder="Enter your Real-Debrid API key"
                        :error="form.errors.torrentio_realdebrid_key">
                        <template #labelSuffix>
                            <a href="https://real-debrid.com/devices" target="_blank" rel="noopener noreferrer"
                                class="text-sm text-theme-accent hover:text-theme-accent-hover hover:underline">
                                {{ t('real_debrid_api_key_link') }}
                            </a>
                        </template>
                    </InputGroup>
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-white/80">Torrent providers</label>
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                        <label v-for="(label, provider) in providerOptions" :key="provider"
                            class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-white/80">
                            <input type="checkbox" :value="provider" v-model="form.torrentio_providers"
                                class="h-4 w-4 rounded border-white/20 bg-white/5 text-theme-accent focus:ring-theme-accent" />
                            <span>{{ label }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.torrentio_providers" class="text-sm text-red-400">
                        {{ form.errors.torrentio_providers }}
                    </p>
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-white/80">Preferred language</label>
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                        <label v-for="(label, language) in languageOptions" :key="language"
                            class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-white/80">
                            <input type="checkbox" :value="language" v-model="form.torrentio_language"
                                class="h-4 w-4 rounded border-white/20 bg-white/5 text-theme-accent focus:ring-theme-accent" />
                            <span>{{ label }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.torrentio_language" class="text-sm text-red-400">
                        {{ form.errors.torrentio_language }}
                    </p>
                </div>

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
    user: { type: Object, required: true },
    providerOptions: { type: [Array, Object], required: true },
    languageOptions: { type: [Array, Object], required: true },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    torrentio_realdebrid_key: props.user.torrentio_realdebrid_key ?? '',
    torrentio_providers: Array.isArray(props.user.torrentio_providers) ? props.user.torrentio_providers : [],
    torrentio_language: Array.isArray(props.user.torrentio_language) ? props.user.torrentio_language : [],
});

function submit() {
    form.put(route('settings.account.update'), {
        preserveScroll: true,
    });
}
</script>
