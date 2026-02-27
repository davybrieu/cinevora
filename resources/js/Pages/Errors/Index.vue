<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-4 px-4 py-12 text-white">
        <component :is="iconComponent" class="mx-auto mb-2 h-16 w-16 text-theme-accent" />
        <h1 class="text-center text-4xl font-extrabold">
            {{ title }}
        </h1>
        <h2 class=" text-center text-lg" v-html="message"></h2>
        <Button v-if="props.status !== 503" href="/" variant="primary">
            <ArrowLeftIcon class="w-5 h-5" />
            {{ t('back_to_home') }}
        </Button>
    </div>
</template>

<script setup>
import Button from '@/Components/Button.vue';
import {
    BoltIcon,
    ExclamationTriangleIcon,
    LockClosedIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import { useTranslation } from '@/Composables/useTranslation';
import { ArrowLeftIcon } from '@heroicons/vue/20/solid';

const { t } = useTranslation();

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const errorContent = computed(() => {
    switch (props.status) {
        case 403:
            return {
                title: 'Accès refusé',
                message:
                    "Vous n'avez pas la permission d'accéder à cette page.",
                icon: LockClosedIcon,
            };
        case 404:
            return {
                title: 'Page introuvable',
                message: "La page demandée n'existe pas.",
                icon: XCircleIcon,
            };
        case 500:
            return {
                title: 'Erreur serveur',
                message:
                    'Une erreur interne est survenue.<br>Veuillez réessayer plus tard.',
                icon: BoltIcon,
            };
        case 503:
            return {
                title: 'Maintenance',
                message:
                    'Le site est momentanément indisponible pour maintenance.',
                icon: ExclamationTriangleIcon,
            };
        case 419:
            return {
                title: 'Page expirée',
                message: 'La page a expiré, veuillez réessayer.',
                icon: ExclamationTriangleIcon,
            };
        default:
            return {
                title: `Erreur ${props.status}`,
                message: 'Une erreur est survenue.',
                icon: ExclamationTriangleIcon,
            };
    }
});

const title = computed(() => errorContent.value.title);
const message = computed(() => errorContent.value.message);
const iconComponent = computed(() => errorContent.value.icon);
</script>
