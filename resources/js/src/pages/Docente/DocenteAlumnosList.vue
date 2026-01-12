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
import useExportAlumnos from '../../composables/tabla/useAlumnosMatricula.js';  // Importa el composable

const props = defineProps({
  id: { type: [String, Number], required: true },
});

const { showConfirmModal, showToast } = useModalToast();
const matriculaStore = useMatriculaStore();

const { exportarAlumnos } = useExportAlumnos();  // Obtén la función

const loading = ref(true);
const estudiantesSeleccionados = ref([]);

// ✅ computed correcto
const matriculados = computed(() => matriculaStore.matriculadosPorGrupoExtendido);

// 🔥 Cargar los matriculados correctamente
onMounted(async () => {
  loading.value = true;
  await matriculaStore.fetchMatriculadosPorGrupoExtendido(props.id);
  loading.value = false;
});

/* ------------------------------------------
   ✔ Seleccionar todos
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
      estudiantesSeleccionados.value =
        matriculados.value.estudiantes.map(e => e.id_matricula);
    } else {
      estudiantesSeleccionados.value = [];
    }
  }
});

// Modal
const showModal = ref(false);
const nuevoGrupoId = ref("");
const saving = ref(false);

const cambiarGrupo = async () => {
  // tu lógica...
};

// Método para exportar alumnos

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
};

const exportar = () => {
  const data = {
    especialidad: matriculados.value.especialidad,
    modulo: matriculados.value.modulo,
    seccion: matriculados.value.seccion,
    turno: matriculados.value.turno,
    docente: matriculados.value.docente ?? "No asignado",

    matriculados: matriculados.value.estudiantes.map(e => ({
      ...e,
      nombre: `${e.nombre} ${e.apellidos}` // 👈 aquí envías el nombre como "nom"
    }))
  };

  exportarAlumnos(data);
};


const exportarMatriculaEvaluaciones = async (idGrupo) => {
  try {
    const response = await axios.get(
      `/reportes/registroMatriculaConEvaluaciones/${idGrupo}`,
      { responseType: "blob" }
    );
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "Restro de matriculas y evaluaciones.xlsx");
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};


</script>


<template>
  <AuthorizationFallback :permissions="['todo-acceso-alumnos-docente', 'ver-alumnos-docente']">
    <div class="w-full space-y-4 py-2 px-3" v-if="matriculados">


      <div class="flex justify-end mb-4  gap-6 ml-2">
        <Button title="Descargar nomina" @click="descargarNomina(props.id)" variant="secondary" />
        <Button title="Alumnos" @click="exportar()" variant="secondary" />
        <Button title="Exporta registro de matrículas y evaluaciones" @click="exportarMatriculaEvaluaciones(props.id)"
          variant="secondary" />

      </div>

      <Table>
        <THead>
          <!-- <Th class="w-10 text-center">
            <input type="checkbox" v-model="todosSeleccionados"
              class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
          </Th> -->
          <Th>N°</Th>
          <Th>DNI</Th>
          <Th>Apellidos y Nombres</Th>
          <Th>Sexo</Th>

          <Th>Fecha de Nacimiento</Th>
          <Th>Teléfono</Th>
          <Th>Correo Electrónico</Th>

        </THead>

        <TBody>
          <Tr v-for="(estudiante, index) in matriculados.estudiantes" :key="estudiante.nro_documento"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <!-- 
            <Td class="text-center">
              <input type="checkbox"
                :value="estudiante.id_matricula"
                v-model="estudiantesSeleccionados"
                class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
            </Td> -->

            <Td>{{ index + 1 }}</Td>
            <Td>{{ estudiante.nro_documento }}</Td>
            <Td> {{ estudiante.apellidos }} {{ estudiante.nombre }} </Td>
            <Td>{{ estudiante.sexo }}</Td>

            <Td>{{ estudiante.fecha_nacimiento }}</Td>
            <Td>{{ estudiante.celular_personal ?? '-' }}</Td>
            <Td>{{ estudiante.correo_electronico ?? '-' }}</Td>


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
