<template>
    <AuthLayout :title="t('sign_in')" :heading="t('sign_in')" :subtitle="t('sign_in_to_your_account')">
        <form @submit.prevent="submit" class="space-y-5">
            <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                :placeholder="t('placeholder_email')" :error="form.errors.email" required autofocus />

            <InputGroup id="password" v-model="form.password" :label="t('password')" type="password"
                :placeholder="t('placeholder_password')" required />

            <div class="flex items-center justify-between">
                <InputCheckbox id="remember" v-model="form.remember" :label="t('remember_me')" />
                <Link :href="route('password.request')" class="text-sm text-white/60 hover:text-white hover:underline">
                    {{ t('forgot_password_link') }}
                </Link>
            </div>

            <Button type="submit" variant="primary" size="md" full :processing="form.processing">
                {{ t('sign_in') }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-white/40">
            {{ t('no_account') }}
            <Link :href="route('register')" class="text-white hover:underline">{{ t('sign_up') }}</Link>
        </p>
    </AuthLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useTranslation } from '../../Composables/useTranslation.js';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputGroup from '../../Components/InputGroup.vue';
import InputCheckbox from '../../Components/InputCheckbox.vue';
import Button from '../../Components/Button.vue';

const { t } = useTranslation();

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post(route('login'), { onFinish: () => form.reset('password') });
}
</script>
