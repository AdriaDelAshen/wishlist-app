<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue:{
        type: Number,
        required: true,
    }
});
const emit = defineEmits(['update:modelValue'])

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        :value="props.modelValue"
        @change="emit('update:modelValue', $event.target.value?parseInt($event.target.value):0)"
        type="number"
        ref="input"
    />
</template>
