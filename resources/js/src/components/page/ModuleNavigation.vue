<script setup>
defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <nav class="border-b border-slate-200/80 px-6 pt-1 dark:border-slate-700/80">
    <div class="module-navigation flex overflow-x-auto gap-5 whitespace-nowrap">
      <router-link
        v-for="link in links"
        :key="link.text"
        :to="link.to"
        class="border-b border-transparent px-0.5 pb-2.5 pt-1 text-[13px] font-normal text-slate-500 transition-colors duration-200 hover:text-cetpro-dark dark:text-slate-400 dark:hover:text-cetpro-light"
        active-class="!border-cetpro !text-cetpro dark:!border-cetpro-light dark:!text-cetpro-light font-medium"
      >
        {{ link.text }}
      </router-link>
    </div>
  </nav>
</template>

<style scoped>
.module-navigation {
  scrollbar-width: thin;
}

.module-navigation::-webkit-scrollbar {
  height: 4px;
}

.module-navigation::-webkit-scrollbar-track {
  background: transparent;
}

.module-navigation::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 20px;
}

.dark .module-navigation::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>
