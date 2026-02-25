<template>
    <Transition enter-active-class="" leave-active-class="transition-opacity duration-600 delay-200 ease-out"
        leave-to-class="opacity-0">
        <div v-if="visible" class="fixed inset-0 bg-black flex items-center justify-center z-[9999]">
            <div :class="[
                'transition-all duration-[900ms] ease-[cubic-bezier(0.16,1,0.3,1)] transform',
                'opacity-0 scale-40',
                animating ? 'opacity-100 scale-100' : ''
            ]">
                <img src="/images/logo.svg" :alt="page.props.name" class="w-[180px] h-auto" />
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const visible = ref(false);
const animating = ref(false);
const page = usePage();

onMounted(() => {
    // N'afficher que si on vient d'un full load (premier accès ou refresh).
    // __inertia_initial_load est posé dans app.js au chargement du document et passé à false après affichage.
    const isInitialLoad = typeof window !== 'undefined' && window.__inertia_initial_load;
    if (!isInitialLoad) return;
    window.__inertia_initial_load = false;

    visible.value = true;

    requestAnimationFrame(() => {
        setTimeout(() => {
            animating.value = true;
        }, 100);
    });

    const finish = router.on('finish', () => {
        setTimeout(() => {
            visible.value = false;
            finish();
        }, 1800);
    });

    setTimeout(() => {
        if (visible.value) visible.value = false;
    }, 4000);
});
</script>
