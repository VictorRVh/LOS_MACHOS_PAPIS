import useHttpRequest from '@/composables/useHttpRequest';
import { ref } from 'vue';

export default function useSesiones() {
  const { store, update, destroy } = useHttpRequest('/api/sesiones');
  const bloquesDeSesiones = ref([]);

  const saveSesion = async (formData, editingId) => {
    if (editingId) {
      const updated = await update(editingId, formData);
      return updated;
    } else {
      const created = await store(formData);
      return created;
    }
  };

  const deleteSesionById = async (id) => {
    await destroy(id);
    bloquesDeSesiones.value = bloquesDeSesiones.value.filter(b => b.id !== id);
  };

  return { bloquesDeSesiones, saveSesion, deleteSesionById };
}
