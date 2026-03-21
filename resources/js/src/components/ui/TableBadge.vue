<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: [String, Number],
    default: "",
  },
  variant: {
    type: String,
    default: "neutral",
  },
  size: {
    type: String,
    default: "sm",
  },
  dot: {
    type: Boolean,
    default: false,
  },
});

const sizeClass = computed(() =>
  props.size === "xs"
    ? "min-h-5 gap-1 px-1.5 text-[10px]"
    : "min-h-5.5 gap-1 px-2 text-[11px]"
);

const variantClass = computed(() => {
  switch (props.variant) {
    case "success":
      return "border-green-200 bg-green-50 text-green-700 dark:border-green-800/70 dark:bg-green-900/20 dark:text-green-300";
    case "warning":
      return "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/70 dark:bg-amber-900/20 dark:text-amber-300";
    case "danger":
      return "border-red-200 bg-red-50 text-red-700 dark:border-red-800/70 dark:bg-red-900/20 dark:text-red-300";
    case "info":
      return "border-cetpro/20 bg-cetpro/10 text-cetpro dark:border-cetpro-light/30 dark:bg-cetpro-dark/20 dark:text-cetpro-light";
    default:
      return "border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300";
  }
});

const dotClass = computed(() => {
  switch (props.variant) {
    case "success":
      return "bg-green-600 dark:bg-green-400";
    case "warning":
      return "bg-amber-500 dark:bg-amber-400";
    case "danger":
      return "bg-red-600 dark:bg-red-400";
    case "info":
      return "bg-cetpro dark:bg-cetpro-light";
    default:
      return "bg-slate-400 dark:bg-slate-500";
  }
});
</script>

<template>
  <span
    :title="String(label || '')"
    :class="[sizeClass, variantClass]"
    class="inline-flex items-center rounded-sm border font-medium leading-none"
  >
    <span
      v-if="dot"
      :class="dotClass"
      class="h-1.5 w-1.5 rounded-full"
      aria-hidden="true"
    />
    <span class="truncate">{{ label }}</span>
  </span>
</template>
