<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
</script>

<template>
    <div>
        <button
            @click="isOpen = !isOpen"
            class="mb-4 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
        >
            <slot name="toggle" :isOpen="isOpen">
                {{ isOpen ? 'Hide panel' : 'Show panel' }}
            </slot>
        </button>

        <transition name="accordion">
            <div
                v-show="isOpen"
                class="overflow-hidden mb-4 p-4 bg-gray-50 rounded-md shadow"
            >
                <slot />
            </div>
        </transition>
    </div>
</template>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
    transition: max-height 0.3s ease, opacity 0.3s ease;
}
.accordion-enter-from,
.accordion-leave-to {
    max-height: 0;
    opacity: 0;
}
.accordion-enter-to,
.accordion-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>
