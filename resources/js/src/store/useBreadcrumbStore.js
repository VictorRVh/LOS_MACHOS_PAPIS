import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useBreadcrumbStore = defineStore('breadcrumb', () => {
  const items = ref([]);

  function setBase(baseItems = []) {
    items.value = baseItems;
  }

  return { items, setBase };
});