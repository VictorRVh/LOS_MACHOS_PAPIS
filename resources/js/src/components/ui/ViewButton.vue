<template>
    <button class="flex items-center gap-1 px-2 py-1.5 rounded-sm text-xsm shadow-google 
           bg-blue-500 active:bg-blue-500 dark:bg-cc-10 active:dark:bg-cc-10 
           text-white dark:text-blue-200 hover:bg-blue-600 dark:hover:bg-cc-12 
           cursor-pointer transition duration-150">
        <EyeIcon class="w-4 h-4" />
        <span>{{ label }}</span>
    </button>
</template>

<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";

defineProps({
    label: {
        type: String,
        default: "Ver",
    },
});
</script>