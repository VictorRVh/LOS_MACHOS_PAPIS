import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useBreadcrumbStore = defineStore('breadcrumb', () => {
  const items = ref([]);

  function setItems(newItems = []) {
    items.value = newItems;
  }

  return { items, setItems };
});