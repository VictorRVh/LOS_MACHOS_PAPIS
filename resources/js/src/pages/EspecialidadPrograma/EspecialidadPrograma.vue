<script setup>
import { computed,onMounted } from 'vue';
import { useRouter } from "vue-router";
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
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';

const props = defineProps({
  idPrograma: {
    type: Number,
    default: null,
  },
});

const especialidadStore = useEspecialidadStore();
const especialidadProgramaStore = useEspecialidadProgramaStore();
const breadcrumb = useBreadcrumbStore();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePrograma, deleting } = useHttpRequest("/especialidad_programa");
const router = useRouter();




onMounted(async () => {
  await especialidadProgramaStore.loadEspecialidadProgramaById(props.idPrograma);

  const ciclo = especialidadProgramaStore.especialidadProgramaFiltrado?.ciclo;
  const programaNombre = ciclo?.nombre_ciclo || "Ciclo sin nombre";
  if (ciclo?.id) {
    await especialidadStore.loadEspecialidadCiclo(ciclo.id);
  }
  breadcrumb.setTextItemAuto(programaNombre, props.idPrograma, "programa");
    breadcrumb.setTextItemAuto(
    ` ${programaNombre ||''}`,
    props.idPrograma,
    'programa',
    { name: 'especialidadPrograma', params: { idPrograma: props.idPrograma } }
  );
});



const especialidadesDisponibles = computed(() => {
  const asignadas = especialidadProgramaStore?.especialidadProgramaFiltrado?.especialidad_programas?.map(
    (ep) => ep?.especialidad_madre.id
  ) || [];
  // especialidad actual si estamos editando
  const currentId = sliderData.value?.especialidad_madre?.id;

  //  console.log("prueba de editar: ",currentId)

  return especialidadStore.especialidadCiclo?.especialidades?.filter(
    (especialidad) => !asignadas.includes(especialidad.id) || especialidad.id === currentId
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
  router.push({
    name: "modulo",
    params: { idEspecialidadPrograma: especialidadPrograma.id },
  });
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-programa-especialidades', 'ver-programa-especialidades']">
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
              v-for="(especialidadPrograma, index) in especialidadProgramaStore?.especialidadProgramaFiltrado?.especialidad_programas"
              :key="especialidadPrograma.id"
               @click="SeeMore(especialidadPrograma)"
               class=" bg-white dark:bg-gray-800 rounded-lg shadow-md  border-l-4 cursor-pointer transition-all duration-200 hover:shadow-lg hover:bg-gray-200 dark:hover:bg-gray-700/50"
            >
              <Td>{{ index + 1 }}</Td>
              <Td>{{ especialidadPrograma?.especialidad_madre?.nombre_especialidad }}</Td>
              <Td>{{ especialidadPrograma?.nro_modulos }}</Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click.stop="showSlider(true, especialidadPrograma)" />
                  <DeleteButton @click.stop="onDelete(especialidadPrograma)" />
                 
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>
    </div>
  </AuthorizationFallback>
</template>