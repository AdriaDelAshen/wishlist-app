<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    options: {
        type: Object,
        required: true,
    },
    mustTranslateOption: {
        type: Boolean,
        default: false
    },
    setDefaultValue: {
        type: String,
        default: ''
    },
});

const model = defineModel({
    type: [String, Boolean, Number],
    required: true,
});

const select = ref(null);

// Set the default value on mount if model is empty
onMounted(() => {
    if (model.value === undefined || model.value === null || model.value === '') {
        model.value = props.setDefaultValue;
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    >
        <option v-for="(option, index) in options" :key="index" :value="index">{{ mustTranslateOption? $t('options.'+option): option }}</option>
    </select>
</template>
