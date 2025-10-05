import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useBreadcrumbStore = defineStore('breadcrumb', () => {
  const items = ref([]);        // items que usa Breadcrumbs.vue
  const itemsText = ref([]);    // [{ name, id }]

  function setBase(baseItems = []) {
    items.value = baseItems.map(item => ({ ...item }));
    itemsText.value = baseItems.map(item => ({ ...item }));
  }

  function setTextItemAuto(text, id, parentName) {
  if (!id) return;

  const existingIndex = itemsText.value.findIndex(item => item.id === id);
  const newItem = { text, id, parent: parentName, to: null };

  if (existingIndex >= 0) {
    itemsText.value[existingIndex] = newItem;
    items.value[existingIndex] = newItem;
  } else {
    itemsText.value.push(newItem);
    items.value.push(newItem);
  }
}



  async function findTextById(id) {
    if (!id) return null;
    return itemsText.value.find(item => item.id === id) || null;
  }

  return { items, itemsText, setBase, setTextItemAuto, findTextById };
});
