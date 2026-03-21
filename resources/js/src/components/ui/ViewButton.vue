<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";

defineProps({
    label: {
        type: String,
        default: "Ver",
    },
});
</script>

<template>
    <button
        type="button"
        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-sm border border-cetpro/15 bg-white px-2.5 py-1.5 text-xsm font-medium text-cetpro transition-colors duration-150 hover:border-cetpro/25 hover:bg-cetpro/10 active:bg-cetpro/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/20 focus-visible:ring-offset-1 focus-visible:ring-offset-white disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-cetpro-light dark:hover:bg-cetpro-light/10 dark:active:bg-cetpro-light/15 dark:focus-visible:ring-offset-slate-900"
    >
        <EyeIcon class="h-4 w-4 shrink-0" />
        <span class="leading-none">{{ label }}</span>
    </button>
</template>
