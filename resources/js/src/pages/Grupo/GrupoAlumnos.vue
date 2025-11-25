<script setup>
import { onMounted, computed, ref, watch } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import { generatePdfMatricula } from '../../pdf/fichaMatricula';
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import axios from 'axios';
import Slider from '../../components/ui/Slider.vue';
import useModalToast from '../../composables/useModalToast';

const props = defineProps({
  id: { type: [String, Number], required: true },
});

const { showConfirmModal, showToast } = useModalToast();
const matriculaStore = useMatriculaStore();

const matriculados = computed(() => matriculaStore.matriculadosPorGrupoExtendido);
const loading = ref(false);
const estudiantesSeleccionados = ref([]);

/* ------------------------------------------
   ✔ FIX PRINCIPAL: seleccionar todos
------------------------------------------ */
const todosSeleccionados = computed({
  get() {
    return (
      matriculados.value?.estudiantes?.length > 0 &&
      estudiantesSeleccionados.value.length === matriculados.value.estudiantes.length
    );
  },
  set(valor) {
    if (valor) {
      estudiantesSeleccionados.value = matriculados.value.estudiantes.map(e => e.id_matricula);
    } else {
      estudiantesSeleccionados.value = [];
    }
  }
});


onMounted(() => {});

const showModal = ref(false);
const nuevoGrupoId = ref("");
const saving = ref(false);

watch(showModal, async () => {});


const cambiarGrupo = async () => {
  // tu lógica luego
};


const descargarNomina = async (idGrupo) => {
  try {
    const response = await axios.get(
      `/reportes/nomina/grupo/${idGrupo}`,
      { responseType: "blob" }
    );

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "nomina.xlsx");
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
}
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos']">
    <div class="w-full space-y-4 py-2 px-3" v-if="matriculados">


      <div class="flex justify-start mb-4 ml-2">
        <Button title="Descargar nomina" @click="descargarNomina(props.id)" variant="secondary" />
      </div>

      <Table>
        <THead>
          <Th class="w-10 text-center">
            <input type="checkbox" v-model="todosSeleccionados"
              class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
          </Th>
          <Th>N°</Th>
          <Th>DNI</Th>
          <Th>Apellidos y Nombres</Th>
          <Th>Sexo</Th>
          <Th>Edad</Th>
          <Th>Condición</Th>
          <Th>Fecha de Nacimiento</Th>
          <Th>Lugar</Th>
          <Th>Estado Civil</Th>
          <Th>Grado de Instrucción</Th>
          <Th>Teléfono</Th>
          <Th>Correo Electrónico</Th>
          <Th>Nro. Recibo</Th>
          <Th>Aporte</Th>
        </THead>

        <TBody>
          <Tr v-for="(estudiante, index) in matriculados.estudiantes" :key="estudiante.nro_documento"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50">

            <Td class="text-center">
              <input type="checkbox"
                :value="estudiante.id_matricula"
                v-model="estudiantesSeleccionados"
                class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
            </Td>

            <Td>{{ index + 1 }}</Td>
            <Td>{{ estudiante.nro_documento }}</Td>
            <Td>{{ estudiante.apellidos_nombres }}</Td>
            <Td>{{ estudiante.sexo }}</Td>
            <Td>{{ estudiante.edad }}</Td>
            <Td>{{ estudiante.condicion }}</Td>
            <Td>{{ estudiante.fecha_nacimiento }}</Td>
            <Td>{{ estudiante.lugar }}</Td>
            <Td>{{ estudiante.estado_civil }}</Td>
            <Td>{{ estudiante.grado_instruccion }}</Td>
            <Td>{{ estudiante.telefono ?? '-' }}</Td>
            <Td>{{ estudiante.correo_electronico ?? '-' }}</Td>
            <Td>{{ estudiante.nro_recibo }}</Td>
            <Td>{{ estudiante.aporte }}</Td>

          </Tr>

          <Tr v-if="matriculados.estudiantes?.length === 0 && !loading">
            <Td colspan="16" class="text-center py-4">
              No hay estudiantes matriculados en este grupo.
            </Td>
          </Tr>

          <Tr v-if="loading">
            <Td colspan="16" class="text-center py-4">Cargando estudiantes...</Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <div v-else class="text-center p-8">Cargando información del grupo...</div>

    <!-- MODAL -->
    <Slider :show="showModal" title="Cambiar Grupo" @hide="showModal = false">
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
      <div class="mt-4 space-y-3">
        <p class="text-gray-600 dark:text-gray-300">
          Estás a punto de mover <strong>{{ estudiantesSeleccionados.length }}</strong> estudiantes.
        </p>

        <div class="flex justify-end gap-2 mt-6">
          <Button title="Cancelar" variant="secondary" @click="showModal = false" />
          <Button title="Confirmar" variant="primary" :disabled="!nuevoGrupoId || saving" :loading="saving"
            @click="cambiarGrupo" />
        </div>
      </div>
    </Slider>
  </AuthorizationFallback>
</template>
