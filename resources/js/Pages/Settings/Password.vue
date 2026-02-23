<template>
    <SettingsLayout>
        <Card>
            <h2 class="mb-2 text-xl font-semibold text-white">{{ t('update_password') }}</h2>
            <p class="mb-6 text-sm text-white/50">{{ t('update_password_hint') }}</p>

            <form @submit.prevent="submit" class="space-y-5">
                <InputGroup id="current_password" v-model="form.current_password" :label="t('current_password')"
                    type="password" :placeholder="t('placeholder_current_password')"
                    :error="form.errors.current_password" required />

                <InputGroup id="password" v-model="form.password" :label="t('new_password')" type="password"
                    :placeholder="t('placeholder_new_password')" :error="form.errors.password" required />

                <InputGroup id="password_confirmation" v-model="form.password_confirmation" :label="t('confirm_password')"
                    type="password" :placeholder="t('placeholder_confirm_password')" required />

                <div class="flex items-center gap-4">
                    <Button type="submit" variant="primary" size="md" :processing="form.processing">
                        {{ t('update_password') }}
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

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put(route('settings.password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>
