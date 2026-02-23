import { ref, onMounted, onUnmounted } from 'vue';

export function useInfiniteScroll(loadMoreFn, { rootMargin = '400px' } = {}) {
    const sentinel = ref(null);
    const loading = ref(false);
    let observer = null;

    async function trigger() {
        if (loading.value) return;
        loading.value = true;
        try {
            await loadMoreFn();
        } finally {
            loading.value = false;
        }
    }

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => { if (entries[0].isIntersecting) trigger(); },
            { rootMargin },
        );
        if (sentinel.value) observer.observe(sentinel.value);
    });

    onUnmounted(() => {
        observer?.disconnect();
    });

    return { sentinel, loading };
}
