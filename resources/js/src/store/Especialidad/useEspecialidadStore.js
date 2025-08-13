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
    show: getEspecialidadByPrograma,
    loading: loadingCiclo,
    initialLoading: initialCiclo,
  } = useHttpRequest('/especialidadByPrograma');

  const {
    index: getGrupoEspecialidad,
    show: getGrupoByEspecialidad,
    loading: loadingGrupoEspecialidad,
    initialLoading: initialGrupoEspecialidad,
  } = useHttpRequest('/grupoByEspecialidad');

  // Estado
  const especialidad = ref([]);
  const especialidadPrograma = ref([]);
  const gruposDisponibles = ref([])

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
  const loadEspecialidadPrograma = async (id) => {
    especialidadLoading.value = loadingCiclo.value;
    especialidadFirstTimeLoading.value = initialCiclo.value;
    const res = await getEspecialidadByPrograma(id);
    especialidadPrograma.value = res;
  };

  const loadGrupoEspecialidad = async (id) => {
    // especialidadLoading.value = loadingCiclo.value;
    // especialidadFirstTimeLoading.value = initialCiclo.value;
    const res = await getGrupoByEspecialidad(id);
    gruposDisponibles.value = res;
  };

  return {
    especialidad,
    especialidadPrograma,
    loadEspecialidad,
    loadEspecialidadPrograma,
    especialidadLoading,
    especialidadFirstTimeLoading,

    loadGrupoEspecialidad,
    gruposDisponibles
  };
});

export default useEspecialidadStore;
