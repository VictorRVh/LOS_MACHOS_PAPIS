import { ref } from 'vue';
import { defineStore } from 'pinia';

import useHttpRequest from '../../composables/useHttpRequest';

const useEspecialidadStore = defineStore('especialidadStore', () => {
  // Peticiones HTTP
  const {
    index: getEspecialidadMadre,
    loading: loadingMadre,
    initialLoading: initialMadre,
  } = useHttpRequest('/especialidad_madre');

  const {
    index: getEspecialidadCiclo,
    show: getEspecialidadByCiclo,
    loading: loadingCiclo,
    initialLoading: initialCiclo,
  } = useHttpRequest('/especialidad_ciclo');

  // Estado
  const especialidad = ref([]);
  const especialidadCiclo = ref([]);

  // Estado de carga (puedes usar uno general o ambos separados si quieres más control)
  const especialidadLoading = ref(false);
  const especialidadFirstTimeLoading = ref(false);

  // Carga de especialidades madre
  const loadEspecialidad = async () => {
    especialidadLoading.value = loadingMadre.value;
    especialidadFirstTimeLoading.value = initialMadre.value;
    const res = await getEspecialidadMadre();
    especialidad.value = res;
  };

  // Carga de especialidad por ciclo
  const loadEspecialidadCiclo = async (id) => {
    especialidadLoading.value = loadingCiclo.value;
    especialidadFirstTimeLoading.value = initialCiclo.value;
    const res = await getEspecialidadByCiclo(id);
    especialidadCiclo.value = res;
  };

  return {
    especialidad,
    especialidadCiclo,
    loadEspecialidad,
    loadEspecialidadCiclo,
    especialidadLoading,
    especialidadFirstTimeLoading,
  };
});

export default useEspecialidadStore;
