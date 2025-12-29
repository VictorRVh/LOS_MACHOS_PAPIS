// /store/useBreadcrumbStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useBreadcrumbStore = defineStore('breadcrumb', () => {
  const itemsText = ref([]);

  /**
   * Agrega o actualiza un breadcrumb de forma ordenada
   * @param {string} text Texto a mostrar
   * @param {string} id ID único del item
   * @param {string|null} parentName Nombre de la ruta padre
   * @param {object|null} to Enlace (opcional)
   */
  function setTextItemAuto(text, id, parentName = null, to = null) {
    if (!id || !text) return;

    const existingIndex = itemsText.value.findIndex(item => item.id === id);
    const newItem = { text, id, parent: parentName, to };

    if (existingIndex >= 0) {
      // Si ya existe → solo actualiza
      itemsText.value[existingIndex] = { ...itemsText.value[existingIndex], ...newItem };
    } else {
      // 👉 Buscar el índice del padre (por su NOMBRE DE RUTA, no por ID)
      const parentIndex = itemsText.value.findIndex(item => item.parent === null && item.id === parentName);

      // Si el padre existe, insertamos después de él
      if (parentIndex >= 0) {
        itemsText.value.splice(parentIndex + 1, 0, newItem);
      } else {
        // Si no hay padre, lo agregamos al final
        itemsText.value.push(newItem);
      }
    }

    // 🔁 Reordenar para mantener jerarquía padre → hijo → nieto
    // itemsText.value.sort((a, b) => {
    //   const order = ['programa', 'especialidadPrograma', 'modulo'];
    //   const indexA = order.indexOf(a.parent || a.id);
    //   const indexB = order.indexOf(b.parent || b.id);
    //   return indexA - indexB;
    // });
  }

  function findTextById(id) {
    return itemsText.value.find(item => item.id === id) || null;
  }

  function clear() {
    itemsText.value = [];
  }

  function setBase(newBase = []) {
    itemsText.value = Array.isArray(newBase) ? [...newBase] : [];
  }

  // ---------------------------------------------------------
  //  🔥 NUEVO: elimina todos los breadcrumbs después del index
  // ---------------------------------------------------------
  function removeAfter(index) {
    if (index >= 0) {
      itemsText.value.splice(index + 1);
    }
  }

  // ---------------------------------------------------------
  // 🔥 NUEVO: retroceder al breadcrumb seleccionado
  // ---------------------------------------------------------
function goBack(index) {
  if (index >= 0) {
    removeAfter(index);
  }
}



  return {
    itemsText,
    setTextItemAuto,
    findTextById,
    clear,
    setBase,
    items: itemsText,
    // Para la flecha de retroceder
    goBack,
    removeAfter,
  };
});
