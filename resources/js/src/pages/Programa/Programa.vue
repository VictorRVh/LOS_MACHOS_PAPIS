<script setup>
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
// import useHttpRequest from "../../composables/useHttpRequest"; // <-- YA NO ES NECESARIO
import useProgramaStore from "../../store/Programa/useProgramaStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import ProgramaSlider from "../../components/page/Programa/ProgramaSlider.vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia"; // Útil para mantener la reactividad en las variables de carga

const router = useRouter();
const programaStore = useProgramaStore();
const cicloStore = useCicloStore();

// Hacemos que la variable de carga sea reactiva también
const { programaLoading } = storeToRefs(programaStore);

// --- CORRECCIÓN 1: Carga inicial de datos ---
// Revisamos el array interno 'programas'
if (!programaStore.programa.programas.length) {
  await programaStore.loadPrograma();
}
// La carga de ciclos está bien
if (!cicloStore.ciclo.length) {
  await cicloStore.loadCiclo();
}

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
// --- CORRECCIÓN 2: Eliminar la llamada directa a la API ---
// const { destroy: deletePrograma, deleting } = useHttpRequest("/programa_estudio"); // <-- ELIMINAR ESTA LÍNEA

// --- CORRECCIÓN 3: Reescribir la función onDelete ---
const onDelete = (programa) => {
  // Usamos el 'loading' del store para evitar dobles clics
  if (programaLoading.value) return;

  showConfirmModal(`¿Seguro que quieres eliminar "${programa?.nameCiclo}"?`, async (confirmed) => {
    if (!confirmed) return;
   
    try {
      // Llamamos a la nueva función reactiva del store. ¡Eso es todo!
      await programaStore.removePrograma(programa?.id);
      
      // El store ya actualizó la lista, solo mostramos la notificación.
      showToast(`Programa "${programa?.nameCiclo}" eliminado exitosamente.`);

      // YA NO es necesario llamar a programaStore.loadPrograma() de nuevo.

    } catch (error) {
      // Si el store da un error (ej: la API falla), lo mostramos.
      showToast('Error al eliminar el programa.', 'error');
    }
  });
};

const SeeMore = (programa) => {
  router.push({
    name: "especialidadPrograma",
    params: { idPrograma: programa.id },
  });
};
</script>
<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Programa de estudio</h2>
    </div>
    <div class="flex px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar Programa
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <ProgramaSlider :show="slider" :programa="sliderData" :ciclo="cicloStore.ciclo" @hide="hideSlider" />
      </div>

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Programa</Th>
            <Th>Numero R.D.</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(programa, index) in programaStore?.programa?.programas" :key="programa.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ programa?.nameCiclo }}</Td>
              <Td>{{ programa?.numero_rd }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, programa)" />
                  <DeleteButton @click="onDelete(programa)" />
                  <div class="flex items-center justify-center space-x-2">
                    <div @click="SeeMore(programa)"
                      class="text-blue-500 hover:text-blue-700 font-semibold cursor-pointer border-b-2 border-transparent hover:border-blue-500">
                      Asignar Especialidades
                    </div>
                  </div>
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>
    </div>
  </AuthorizationFallback>
</template>