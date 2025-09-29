<script setup>
import { ref, computed } from "vue";
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
import useHttpRequest from "../../composables/useHttpRequest";
import ModuloSlider from "../../components/page/Modulo/ModuloSlider.vue";
import useModuloStore from "../../store/Modulos/useModulosStore";


const moduloStore = useModuloStore();



const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePrograma, deleting } = useHttpRequest("/modulo");

const props = defineProps({
  idEspecialidadPrograma: {
    type: Number,
    default: null,
  }

});


if (!moduloStore.modulo.length) await moduloStore.loadModuloById(props.idEspecialidadPrograma)



// Lista base de índices
const indexModulos = [
  {    id: "01",    name: "Módulo 01",  },
  {    id: "02",    name: "Módulo 02",  },
  {    id: "03",    name: "Módulo 03",  },
  {    id: "04",    name: "Módulo 04",  },
  {    id: "05",    name: "Módulo 05",  },
  {    id: "06",    name: "Módulo 06",  },
  {    id: "07",    name: "Módulo 07",  },
  {    id: "08",    name: "Módulo 08",  },
  {    id: "09",    name: "Módulo 09",  },
  {    id: "10",    name: "Módulo 10",  }

];

const indicesArray = computed(() => {
  // Mapea los módulos ya asignados
  const asignadas = moduloStore.moduloFiltrado?.map(
    (ep) => ep?.numero_modulo
  ) || [];

  // Si estoy editando
  const currentId = sliderData.value?.numero_modulo;

  return indexModulos.filter(
    (indice) => !asignadas.includes(indice.id) || indice.id === currentId
  );

});

console.log("los indices: ", indicesArray.value);
// ["02", "04", "05", "06", "07", "08", "09", "10"]


const onDelete = (modulo) => {

  console.log('eliminar modulo', modulo)

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletePrograma(modulo?.id);
    if (isDeleted) {
      showToast(`Modulo "${modulo?.descripcion}" eliminado exitosamente...`);
      moduloStore.loadModuloById(props.idEspecialidadPrograma)

    }
  });
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-modulos', 'ver-modulos']">

    <div class="flex  px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Asignar Módulo
        </h3>
        <hr class="border-t-2  border-cetpro dark:border-cetpro-light mb-4" />
        <ModuloSlider :show="slider" :modulo="sliderData" :especialidad="props.idEspecialidadPrograma"
          :indexModulo="indicesArray" @hide="hideSlider" />
      </div>


      <div class="w-full">
        <Table>
          <THead>
            <Th>N°</Th>
            <Th>Modulo</Th>
            <Th>Creditos</Th>
            <Th>Horas</Th>
            <Th>Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(modulo, index) in moduloStore.moduloFiltrado" :key="modulo.id">
              <Td>{{ modulo?.numero_modulo }}</Td>
              <Td>{{ modulo?.descripcion }}</Td>
              <Td>{{ modulo?.creditos }}</Td>
              <Td>{{ modulo?.horas }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, modulo)" />
                  <DeleteButton @click="onDelete(modulo)" />
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>
