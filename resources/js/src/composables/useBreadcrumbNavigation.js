import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import { useRouter } from 'vue-router';

/**
 * Nuestro nuevo asistente para la navegación con breadcrumbs.
 */
export function useBreadcrumbNavigation() {
  const breadcrumbStore = useBreadcrumbStore();
  const router = useRouter();

  /**
   * Navega a un subnivel, actualizando el breadcrumb automáticamente.
   * @param {object} item - El objeto del que se navega (ej. el objeto 'programa').
   * @param {string} textField - El nombre de la propiedad a mostrar en el breadcrumb (ej. 'año').
   * @param {string} destinationRouteName - El nombre de la ruta de destino (ej. 'especialidadPrograma').
   * @param {string} destinationParamName - El nombre del parámetro en la ruta de destino (ej. 'idPrograma').
   * @param {string} backRouteName - El nombre de la ruta para el enlace de "vuelta" (ej. 'programa.editar').
   */
  const navigateToChild = (item, textField, destinationRouteName, destinationParamName, backRouteName) => {
    // 1. Añade el nivel actual a la pila.
    breadcrumbStore.push({
      text: item[textField], // Obtiene el texto dinámicamente
      to: { name: backRouteName, params: { id: item.id } }
    });

    // 2. Navega a la siguiente página.
    router.push({
      name: destinationRouteName,
      params: { [destinationParamName]: item.id }
    });
  };

  return { navigateToChild };
}