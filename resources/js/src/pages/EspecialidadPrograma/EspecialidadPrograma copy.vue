<script setup>
import { computed } from 'vue';
import { useRouter } from "vue-router";
import { useBreadcrumbStore } from "../../store/useBreadcrumbStore"; 
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
import useEspecialidadStore from "../../store/Especialidad/useEspecialidadStore";
import useEspecialidadProgramaStore from "../../store/EspecialidadPrograma/useEspecialidadProgramaStore";
import EspecialidadProgramaSlider from "../../components/page/EspecialidadPrograma/EspecialidadProgramaSlider.vue";

const props = defineProps({
  idPrograma: { type: String, required: true },
});

const especialidadStore = useEspecialidadStore();
const especialidadProgramaStore = useEspecialidadProgramaStore();
const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePrograma, deleting } = useHttpRequest("/especialidad_programa");
const router = useRouter();
const breadcrumbStore = useBreadcrumbStore(); 

// Cargar datos iniciales
if (!especialidadProgramaStore.especialidadPrograma.length) {
  await especialidadProgramaStore.loadEspecialidadProgramaById(props.idPrograma);
}

if (!especialidadStore.especialidadCiclo.length) {
  const cicloId = especialidadProgramaStore.especialidadProgramaFiltrado[0]?.programa_estudio?.id_ciclo;
  await especialidadStore.loadEspecialidadCiclo(cicloId);
}

const especialidadesDisponibles = computed(() => {
  const asignadas = especialidadProgramaStore.especialidadProgramaFiltrado.map(
    (ep) => ep.especialidad_madre.id
  );
  return especialidadStore.especialidadCiclo?.especialidades?.filter(
    (especialidad) => !asignadas.includes(especialidad.id)
  ) || [];
});

const onDelete = (especialidadPrograma) => {
  if (deleting.value) return;
  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;
    const isDeleted = await deletePrograma(especialidadPrograma?.id);
    if (isDeleted) {
      showToast(`Especialidad eliminada exitosamente.`);
      await especialidadProgramaStore.loadEspecialidadProgramaById(props.idPrograma);
    }
  });
};

const SeeMore = (especialidadPrograma) => {
  breadcrumbStore.push({
    text: especialidadPrograma.especialidad_madre.nombre_especialidad,
    to: { name: 'modulo', params: { idEspecialidadPrograma: especialidadPrograma.id } }
  });
  router.push({
    name: "modulo",
    params: { idEspecialidadPrograma: especialidadPrograma.id },
  });
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-roles', 'ver-roles']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Asignar especialidad</h2>
    </div>
    <div class="flex px-6">
      <div class="w-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Asignar Especialidad
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <EspecialidadProgramaSlider
          :show="slider"
          :especialidadPrograma="sliderData"
          :especialidad="especialidadesDisponibles"
          :idPrograma="props.idPrograma"
          @hide="hideSlider"
        />
      </div>

      <div class="w-full">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Especialidad</Th>
            <Th>Nro módulos</Th>
            <Th>Acciones</Th>
          </THead>
          <TBody>
            <Tr
              v-for="(especialidadPrograma, index) in especialidadProgramaStore.especialidadProgramaFiltrado"
              :key="especialidadPrograma.id"
            >
              <Td>{{ index + 1 }}</Td>
              <Td>{{ especialidadPrograma?.especialidad_madre.nombre_especialidad }}</Td>
              <Td>{{ especialidadPrograma?.nro_modulos }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, especialidadPrograma)" />
                  <DeleteButton @click="onDelete(especialidadPrograma)" />
                  <div
                    @click="SeeMore(especialidadPrograma)"
                    class="text-blue-500 hover:text-blue-700 font-semibold cursor-pointer border-b-2 border-transparent hover:border-blue-500"
                  >
                    Módulos
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