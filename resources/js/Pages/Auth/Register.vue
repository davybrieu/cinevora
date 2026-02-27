<template>
    <AuthLayout :title="t('sign_up')">
            <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 sm:px-8">
            <Card class="w-full max-w-[95vw] sm:max-w-md">
                <h1 class="mb-3 text-2xl font-bold text-white">{{ t('sign_up') }}</h1>
                <p class="mb-6 text-sm text-white/60">{{ t('create_your_account') }}</p>

                <form @submit.prevent="submit" class="space-y-5">
                    <InputGroup id="name" v-model="form.name" :label="t('name')" type="text"
                        :placeholder="t('placeholder_name')" :error="form.errors.name" required autofocus />

                    <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                        :placeholder="t('placeholder_email')" :error="form.errors.email" required />

                    <InputGroup id="password" v-model="form.password" :label="t('password')" type="password"
                        :placeholder="t('placeholder_password')" :error="form.errors.password" required />

                    <InputGroup id="password_confirmation" v-model="form.password_confirmation"
                        :label="t('password_confirmation')" type="password"
                        :placeholder="t('placeholder_password_confirmation')" required />

                    <Button type="submit" variant="primary" size="md" full :processing="form.processing">
                        {{ t('sign_up') }}
                    </Button>
                </form>

                <p class="mt-6 text-center text-sm text-white/40">
                    {{ t('already_have_account') }}
                    <Link :href="route('login')" class="text-white hover:underline">{{ t('sign_in') }}</Link>
                </p>
            </Card>
        </div>
    </AuthLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useTranslation } from '../../Composables/useTranslation.js';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputGroup from '../../Components/InputGroup.vue';
import Button from '../../Components/Button.vue';
import Card from '@/Components/Card.vue';

const { t } = useTranslation();

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

function submit() {
    form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>
