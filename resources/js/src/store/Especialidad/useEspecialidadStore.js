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


  // RUTA QUE FUNCION PARA ESPECIALIDADES DE UN CICLO

  const {
    // index: getEspecialidadCicloOne,
    show: getEspecialidadCicloOne,
    // loading: loadingCiclo,
    // initialLoading: initialCiclo,
  } = useHttpRequest('/especialidad_ciclo');

  const {
    index: getEspecialidadCiclo,
    show: getEspecialidadByPrograma,
    loading: especialidadByCicloLoading,
    initialLoading: initialCiclo,
  } = useHttpRequest('/especialidadByPrograma');

  const {
    index: getGrupoEspecialidad,
    show: getGrupoByEspecialidad,
    loading: grupoByEspecialidadLoading,
    initialLoading: initialGrupoEspecialidad,
  } = useHttpRequest('/grupoByEspecialidad');

  // Estado
  const especialidad = ref([]);
  const especialidadPrograma = ref([]);
  const gruposDisponibles = ref([])

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
  const loadEspecialidadPrograma = async (id) => {
    const res = await getEspecialidadByPrograma(id);
    especialidadPrograma.value = res;
  };

  const loadGrupoEspecialidad = async (id) => {
    // especialidadLoading.value = loadingCiclo.value;
    // especialidadFirstTimeLoading.value = initialCiclo.value;
    const res = await getGrupoByEspecialidad(id);
    gruposDisponibles.value = res;
  };

   const loadEspecialidadCiclo = async (id) => {
    // especialidadLoading.value = loadingCiclo.value;
    // especialidadFirstTimeLoading.value = initialCiclo.value;
    const res = await getEspecialidadCicloOne(id);
    especialidadCiclo.value = res;
  };

  return {
    especialidad,
    especialidadPrograma,
    loadEspecialidad,
    loadEspecialidadPrograma,
    especialidadLoading,
    especialidadFirstTimeLoading,

    loadGrupoEspecialidad,
    gruposDisponibles,

    loadEspecialidadCiclo,
    especialidadCiclo,

    especialidadByCicloLoading,
    grupoByEspecialidadLoading
  };
});

export default useEspecialidadStore;
