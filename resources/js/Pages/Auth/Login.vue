<template>
    <AuthLayout :title="t('sign_in')">
            <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 sm:px-8">
            <Card class="w-full max-w-[95vw] sm:max-w-md">
                <h1 class="mb-3 text-2xl font-bold text-white">{{ t('sign_in') }}</h1>
                <p class="mb-6 text-sm text-white/60">{{ t('sign_in_to_your_account') }}</p>
                <form @submit.prevent="submit" class="space-y-5">
                    <InputGroup id="email" v-model="form.email" :label="t('email')" type="email"
                        :placeholder="t('placeholder_email')" :error="form.errors.email" required autofocus />

                    <InputGroup id="password" v-model="form.password" :label="t('password')" type="password"
                        :placeholder="t('placeholder_password')" :error="form.errors.password" required>
                        <template #labelSuffix>
                            <Link :href="route('password.request')"
                                class="text-sm text-theme-accent hover:text-theme-accent-hover hover:underline">
                                {{ t('forgot_password_link') }}
                            </Link>
                        </template>
                    </InputGroup>

                    <Button type="submit" variant="primary" size="md" full :processing="form.processing">
                        {{ t('sign_in') }}
                    </Button>
                </form>
                <p class="mt-6 text-center text-sm text-white/40">
                    {{ t('no_account') }}
                    <Link :href="route('register')" class="text-white hover:underline">{{ t('sign_up') }}</Link>
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
import InputCheckbox from '../../Components/InputCheckbox.vue';
import Button from '../../Components/Button.vue';
import Card from '@/Components/Card.vue';

const { t } = useTranslation();

const form = useForm({ email: '', password: '' });

function submit() {
    form.post(route('login'), { onFinish: () => form.reset('password') });
}
</script>
