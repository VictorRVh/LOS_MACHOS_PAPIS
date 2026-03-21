<script setup>
import { ref } from 'vue';
import { v4 } from 'uuid';

const props = defineProps({
    label: {
        type: String,
        default: () => '',
    },
    labelClass: {
        type: String,
        default: () => '',
    },
    error: {
        type: [String, null],
        default: () => null,
    },
    errorClass: {
        type: String,
        default: () => '',
    },
    required: {
        type: Boolean,
        default: () => false,
    },
});

const id = ref(`input-${v4()}`);
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 block text-[13px] font-semibold leading-5 text-slate-700 dark:text-slate-200"
            :class="[labelClass ? labelClass : '']"
        >
            {{ label }}
            <span
                v-if="required"
                class="ml-1 text-red-600 dark:text-red-400"
                >*</span
            >
        </label>
        <slot></slot>
        <div
            v-if="error"
            class="mt-1.5 w-full text-[12px] leading-4 text-red-600 dark:text-red-400"
            :class="[errorClass ? errorClass : '']"
        >
            {{ error }}
        </div>
    </div>
</template>
