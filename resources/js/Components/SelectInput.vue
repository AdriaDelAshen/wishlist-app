<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    options: {
        type: Object,
        required: true,
    },
    mustTranslateOption: {
        type: Boolean,
        required: true,
    },
});

const model = defineModel({
    type: String,
    required: true,
});

const select = ref(null);

onMounted(() => {
    // if (select.value.hasAttribute('autofocus')) {
    //     select.value.focus();
    // }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    >
        <option v-for="(option, index) in options" :value="index">{{ mustTranslateOption? $t('options.'+option): option }}</option>
    </select>
</template>
