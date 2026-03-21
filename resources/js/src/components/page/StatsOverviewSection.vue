<script setup>
import { ref } from "vue";
import { ChevronDownIcon, EyeIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  eyebrow: {
    type: String,
    default: "Gestion institucional",
  },
  title: {
    type: String,
    default: "",
  },
  defaultOpen: {
    type: Boolean,
    default: false,
  },
});

const isOpen = ref(props.defaultOpen);
</script>

<template>
  <section
    class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
  >
    <div class="flex flex-col gap-2">
      <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
          <h2 class="text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
            {{ title }}
          </h2>
        </div>

        <div class="flex items-center gap-2">
          <slot name="actions"></slot>

          <button
            type="button"
            @click="isOpen = !isOpen"
            class="inline-flex h-8 items-center gap-2 rounded-[3px] border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-600 transition-colors hover:border-cetpro/20 hover:bg-cetpro/10 hover:text-cetpro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/20 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cetpro-light/25 dark:hover:bg-cetpro-light/10 dark:hover:text-cetpro-light dark:focus-visible:ring-offset-slate-900"
            :aria-expanded="isOpen"
          >
            <EyeIcon class="h-4 w-4 shrink-0" />
            <span>Vista estadistica</span>
            <ChevronDownIcon class="h-4 w-4 shrink-0 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" />
          </button>
        </div>
      </div>

      <div v-if="isOpen">
        <slot></slot>
      </div>
    </div>
  </section>
</template>
