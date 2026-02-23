<template>
    <SettingsLayout>
        <Card class="!border-red-500/20">
            <h2 class="mb-2 text-xl font-semibold text-white">{{ t('delete_account') }}</h2>
            <p class="mb-6 text-sm text-white/50">{{ t('delete_account_hint') }}</p>

            <Button variant="danger" size="md" @click="showDeleteModal = true">
                {{ t('delete_account') }}
            </Button>
        </Card>

        <!-- Delete Account Modal -->
        <Modal v-model:open="showDeleteModal" size="sm">
            <div class="p-6">
                <h3 class="mb-2 text-xl font-semibold text-white">{{ t('delete_account_confirm') }}</h3>
                <p class="mb-6 text-sm text-white/50">{{ t('delete_account_warning') }}</p>

                <form @submit.prevent="submit" class="space-y-5">
                    <InputGroup id="delete_password" v-model="form.password" :label="t('password')" type="password"
                        :placeholder="t('placeholder_password')" :error="form.errors.password" required />

                    <div class="flex gap-3">
                        <Button type="button" variant="secondary" size="md" @click="showDeleteModal = false">
                            {{ t('cancel') }}
                        </Button>
                        <Button type="submit" variant="danger" size="md" :processing="form.processing">
                            {{ t('delete_account') }}
                        </Button>
                    </div>
                </form>
            </div>
        </Modal>
    </SettingsLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useTranslation } from '@/Composables/useTranslation.js';
import SettingsLayout from './Layout.vue';
import Card from '@/Components/Card.vue';
import InputGroup from '@/Components/InputGroup.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

const { t } = useTranslation();

const showDeleteModal = ref(false);

const form = useForm({
    password: '',
});

function submit() {
    form.delete(route('settings.deleteaccount.destroy'), {
        preserveScroll: true,
    });
}
</script>
