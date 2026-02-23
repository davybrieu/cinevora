import { ref, onMounted, nextTick } from 'vue';

export function useDragScroll() {
    const scrollContainer = ref(null);
    const canScrollLeft = ref(false);
    const canScrollRight = ref(true);
    const isDragging = ref(false);
    const wasDragged = ref(false);

    let dragStartX = 0;
    let dragDistance = 0;

    function updateScrollState() {
        if (!scrollContainer.value) return;
        const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value;
        canScrollLeft.value = scrollLeft > 10;
        canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 10;
    }

    function scrollByAmount(direction) {
        if (!scrollContainer.value) return;
        const amount = scrollContainer.value.clientWidth * 0.75;
        scrollContainer.value.scrollBy({
            left: direction === 'left' ? -amount : amount,
            behavior: 'smooth',
        });
    }

    function onDragStart(e) {
        if (!scrollContainer.value) return;
        isDragging.value = true;
        wasDragged.value = false;
        dragDistance = 0;
        dragStartX = e.pageX - scrollContainer.value.offsetLeft;
    }

    function onDragMove(e) {
        if (!isDragging.value || !scrollContainer.value) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.value.offsetLeft;
        const walk = (x - dragStartX) * 1.5;
        dragDistance += Math.abs(x - dragStartX);
        dragStartX = x;
        scrollContainer.value.scrollLeft -= walk;

        if (dragDistance > 5) {
            wasDragged.value = true;
        }
    }

    function onDragEnd() {
        isDragging.value = false;
        setTimeout(() => {
            wasDragged.value = false;
        }, 50);
    }

    onMounted(async () => {
        await nextTick();
        updateScrollState();
    });

    return {
        scrollContainer,
        canScrollLeft,
        canScrollRight,
        isDragging,
        wasDragged,
        updateScrollState,
        scrollByAmount,
        onDragStart,
        onDragMove,
        onDragEnd,
    };
}
