<template>
    <header class="page-header relative overflow-hidden">
        <div class="page-header-bg" />
        <div class="relative z-10 flex flex-col items-center justify-center px-6 py-14 text-center md:py-20">
            <h1 class="page-header-title">{{ title }}</h1>
            <p v-if="description" class="page-header-description">{{ description }}</p>
        </div>
    </header>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<style scoped>
.page-header {
    background-color: #1a0f0f;
}

.page-header-bg {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(135deg, rgba(229, 9, 20, 0.06) 0%, transparent 40%),
        linear-gradient(225deg, rgba(139, 0, 0, 0.08) 0%, transparent 35%),
        repeating-linear-gradient(
            100deg,
            transparent,
            transparent 2px,
            rgba(229, 9, 20, 0.03) 2px,
            rgba(229, 9, 20, 0.03) 4px
        );
    pointer-events: none;
}

.page-header-title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.2;
    color: #fff;
}

.page-header-description {
    margin-top: 0.5rem;
    font-size: clamp(0.9rem, 2vw, 1.05rem);
    font-weight: 400;
    line-height: 1.5;
    color: rgba(229, 180, 180, 0.85);
    max-width: 42ch;
}
</style>
