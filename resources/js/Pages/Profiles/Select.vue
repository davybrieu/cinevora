<template>
    <AppLayout :title="t('who_is_watching')" :navbar="false" :footer="false">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 space-y-12">
            <h1 class="text-2xl font-bold text-white sm:text-3xl md:text-4xl text-center">{{ t('who_is_watching') }}</h1>

            <div class="flex flex-wrap items-start justify-center gap-6">
                <!-- Existing profiles -->
                <div v-for="profile in profiles" :key="profile.id" class="group relative w-28 md:w-36">
                    <button @click="selectProfile(profile)"
                        class="cursor-pointer flex w-full flex-col items-center transition-transform transition duration-200 ease-in-out focus:ring-0 focus:outline-none outline-none"
                        tabindex="0">
                        <div
                            class="h-28 w-28 overflow-hidden rounded-full border-2 border-transparent transition-all duration-200 group-hover:border-theme-accent group-hover:shadow-lg md:h-36 md:w-36">
                            <img :src="profile.avatar_url" :alt="profile.name"
                                class="h-full w-full object-cover rounded-full transition-transform duration-200" />
                        </div>
                        <span
                            class="mt-3 w-full truncate text-center font-semibold text-xl text-theme-text-muted transition-colors duration-200 group-hover:text-white">
                            {{ profile.name }}
                        </span>
                    </button>
                    <button @click="deleteProfile(profile)"
                        class="cursor-pointer absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white transition-colors transition-transform duration-200 hover:bg-red-500 focus:outline-none focus:ring-0 outline-none"
                        tabindex="0">
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>

                <!-- Add profile button -->
                <div v-if="profiles.length < 3" class="w-28 md:w-36">
                    <button @click="showCreateModal = true"
                        class="cursor-pointer flex w-full flex-col items-center transition-transform transition duration-200 outline-none focus:outline-none focus:ring-0"
                        tabindex="0">
                        <div
                            class="flex h-28 w-28 items-center justify-center rounded-full border-2 border-white/20 bg-theme-dark transition-all duration-200 group hover:border-theme-accent hover:shadow-lg md:h-36 md:w-36">
                            <PlusIcon
                                class="h-12 w-12 text-white/40 transition-colors duration-200 group-hover:text-white" />
                        </div>
                    </button>
                </div>
            </div>

            <Button variant="primary" size="lg" @click="logout">
                {{ t('sign_out') }}
            </Button>

            <!-- Create profile modal -->
            <Modal v-model:open="showCreateModal" size="md" :show-close="false" @close="createForm.reset()">
                <h2 class="mb-6 text-xl font-bold text-white">{{ t('create_profile') }}</h2>

                <form @submit.prevent="createProfile" class="space-y-5">
                    <InputGroup v-model="createForm.name" :label="t('profile_name')"
                        :placeholder="t('placeholder_profile_name')" :error="createForm.errors.name" required
                        :maxlength="50" />

                    <div>
                        <label class="mb-2 block text-sm text-white/70">{{ t('choose_avatar') }}</label>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 sm:gap-3">
                            <button v-for="avatar in avatars" :key="avatar.name" type="button"
                                @click="createForm.avatar = avatar.name"
                                class="overflow-hidden rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-theme-accent outline-none"
                                :class="createForm.avatar === avatar.name ? 'border-theme-accent shadow-md' : 'border-transparent hover:border-theme-accent hover:shadow-lg'"
                                tabindex="0">
                                <img :src="avatar.url" :alt="avatar.name"
                                    class="h-full w-full object-cover transition-transform duration-200" />
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <Button variant="secondary" size="md" class="flex-1" @click="showCreateModal = false">
                            {{ t('cancel') }}
                        </Button>
                        <Button type="submit" variant="primary" size="md" class="flex-1"
                            :processing="createForm.processing">
                            {{ t('create') }}
                        </Button>
                    </div>
                </form>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useTranslation } from '../../Composables/useTranslation.js';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { XMarkIcon } from '@heroicons/vue/24/solid';
import Modal from '../../Components/Modal.vue';
import Button from '../../Components/Button.vue';
import InputGroup from '../../Components/InputGroup.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useTranslation();

const props = defineProps({
    profiles: { type: Array, required: true },
    avatars: { type: Array, required: true },
});

const showCreateModal = ref(false);

const createForm = useForm({
    name: '',
    avatar: 'avatar-1.svg',
});

function logout() {
    router.post(route('logout'));
}

function selectProfile(profile) {
    router.post(route('profiles.select', { profile: profile.id }));
}

function createProfile() {
    createForm.post(route('profiles.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function deleteProfile(profile) {
    if (confirm(t('delete_profile') + ' ?')) {
        router.delete(route('profiles.destroy', { profile: profile.id }));
    }
}
</script>
