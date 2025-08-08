import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useMatriculaStore = defineStore('matricula', () => {
    const {
        index: getMatriculas,
        store: createMatricula,
        show: getMatriculaById,
        update: updateMatricula,
        destroy: deleteMatricula,
        loading: matriculasLoading,
        initialLoading: matriculasFirstTimeLoading,
    } = useHttpRequest('/matriculas'); // Asegúrate que la ruta del API sea correcta

    const matriculas = ref([]);
    const matriculaSeleccionada = ref(null);

    const loadMatriculas = async () => {
        const response = await getMatriculas();
        matriculas.value = response;
    };

    const findMatricula = async (id) => {
        const response = await getMatriculaById(id);
        matriculaSeleccionada.value = response;
        return response;
    };

    const saveMatricula = async (data) => {
        if (data.id) {
            // Actualizar
            return await updateMatricula(data.id, data);
        } else {
            // Crear
            return await createMatricula(data);
        }
    };

    const removeMatricula = async (id) => {
        await deleteMatricula(id);
        // Opcional: remover de la lista local
        const index = matriculas.value.findIndex(m => m.id === id);
        if (index > -1) {
            matriculas.value.splice(index, 1);
        }
    };

    return {
        matriculas,
        matriculaSeleccionada,
        matriculasLoading,
        matriculasFirstTimeLoading,
        loadMatriculas,
        findMatricula,
        saveMatricula,
        removeMatricula,
    };
});

export default useMatriculaStore;