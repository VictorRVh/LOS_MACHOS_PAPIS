<script setup>
defineProps({
  title: {
    type: String,
    default: "",
  },
  metadata: {
    type: Array,
    default: () => [],
  },
  actionsTargetId: {
    type: String,
    default: "",
  },
});
</script>

<template>
  <header class="flex flex-col gap-2 px-6 pt-4">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
      <div class="min-w-0">
        <h2 class="truncate text-[1.9rem] font-bold leading-none text-slate-900 dark:text-slate-100">
          {{ title }}
        </h2>

        <div
          v-if="metadata.length"
          class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
        >
          <template v-for="(item, index) in metadata" :key="`${item.label}-${item.value}`">
            <div class="inline-flex items-baseline gap-1.5">
              <span class="font-semibold text-slate-400 dark:text-slate-500">{{ item.label }}</span>
              <span class="font-medium text-slate-700 dark:text-slate-200">{{ item.value }}</span>
            </div>
            <span v-if="index < metadata.length - 1" class="text-slate-300 dark:text-slate-600">·</span>
          </template>
        </div>
      </div>

      <div
        v-if="actionsTargetId"
        :id="actionsTargetId"
        class="flex flex-wrap items-center justify-start gap-2 lg:justify-end"
      ></div>
    </div>
  </header>
</template>
