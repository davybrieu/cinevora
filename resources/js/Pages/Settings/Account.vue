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
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

function submit() {
    form.put(route('settings.account.update'), {
        preserveScroll: true,
    });
}
</script>
