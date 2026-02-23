<template>
    <AuthLayout :title="t('forgot_password')" :heading="t('forgot_password')" :subtitle="t('forgot_password_hint')">
        <form @submit.prevent="submit" class="space-y-5">
            <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                :placeholder="t('placeholder_email')" :error="form.errors.email" required autofocus />

            <Button type="submit" variant="primary" size="md" full :processing="form.processing">
                {{ t('send_reset_link') }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-white/40">
            <Link :href="route('login')" class="text-white hover:underline">{{ t('back_to_login') }}</Link>
        </p>
    </AuthLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useTranslation } from '../../Composables/useTranslation.js';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputGroup from '../../Components/InputGroup.vue';
import Button from '../../Components/Button.vue';

const { t } = useTranslation();

const form = useForm({ email: '' });

function submit() {
    form.post(route('password.email'));
}
</script>
