<template>
    <AuthLayout :title="t('reset_password')" :heading="t('reset_password')" :subtitle="t('reset_password_hint')">
        <form @submit.prevent="submit" class="space-y-5">
            <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                :placeholder="t('placeholder_email')" :error="form.errors.email" required autofocus />

            <InputGroup id="password" v-model="form.password" :label="t('new_password')" type="password"
                :placeholder="t('placeholder_new_password')" :error="form.errors.password" required />

            <InputGroup id="password_confirmation" v-model="form.password_confirmation"
                :label="t('confirm_password')" type="password"
                :placeholder="t('placeholder_confirm_password')" :error="form.errors.password_confirmation" required />

            <Button type="submit" variant="primary" size="md" full :processing="form.processing">
                {{ t('reset_password') }}
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

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });

function submit() {
    form.post(route('password.update'), { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>
