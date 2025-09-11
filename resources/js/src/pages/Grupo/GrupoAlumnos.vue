<script setup>
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import { PencilSquareIcon, TrashIcon, ArchiveBoxIcon, DocumentArrowDownIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import { generatePdfMatricula } from '../../pdf/fichaMatricula';
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import axios from 'axios';

const props = defineProps({
  id: { type: [String, Number], required: true },
});

const router = useRouter();

const matriculaStore = useMatriculaStore();

const matriculados = computed(() => matriculaStore.matriculadosPorGrupo)
const loading = ref(false)
const estudiantesSeleccionados = ref([]);
const datosParaFicha = ref([]);


const todosSeleccionados = computed({
  get: () => matriculados.value.length > 0 && estudiantesSeleccionados.value.length === matriculados.value.length,
  set: (value) => {
    estudiantesSeleccionados.value = value ? matriculados.value.map(m => m.id) : [];
  }
});

onMounted(() => {
  loading.value = true;
  setTimeout(async () => {
    await matriculaStore.fetchMatriculadosPorGrupo(props.id)
    loading.value = false;
  }, 1000);
});

const editarMatricula = (matricula) => {
  alert(`Simulación: Redirigiendo a editar matrícula con ID ${matricula.id}`);
  router.push({ name: 'matricula.editar', params: { id: matricula.id } });
};

const eliminarMatricula = (matricula) => {
  if (confirm(`Simulación: ¿Estás seguro de eliminar a ${matricula.estudiante.nombres}?`)) {
    matriculados.value = matriculados.value.filter(m => m.id !== matricula.id);
    alert('Estudiante eliminado de la lista (simulado).');
  }
};

const reservarMatricula = (matricula) => {
  if (confirm(`Simulación: ¿Pasar a RESERVA a ${matricula.estudiante.nombres}?`)) {
    matriculados.value = matriculados.value.filter(m => m.id !== matricula.id);
    alert('Matrícula reservada. El estudiante ha sido quitado de esta lista (simulado).');
  }
};

const exportarFicha = async (matricula) => {
  try {
    await matriculaStore.fetchFichaMatricula(matricula);

    const datosMatricula = matriculaStore.datosMatricula;

    if (datosMatricula) {
      generatePdfMatricula(datosMatricula);
    } else {
      console.error('No se pudieron obtener los datos de la matrícula');
    }
  } catch (error) {
    console.error('Error al exportar ficha:', error);
  }
};
const cambiarGrupo = () => {
  if (estudiantesSeleccionados.value.length === 0) {
    alert("Selecciona al menos un estudiante.");
    return;
  }
  const nuevoGrupoId = prompt("Simulación: Ingresa el ID del nuevo grupo de destino:", "");
  if (nuevoGrupoId && !isNaN(nuevoGrupoId)) {
    saving.value = true;
    setTimeout(() => {
      matriculados.value = matriculados.value.filter(m => !estudiantesSeleccionados.value.includes(m.id));
      estudiantesSeleccionados.value = [];
      alert(`Estudiantes movidos al grupo ${nuevoGrupoId} (simulado).`);
      saving.value = false;
    }, 1500);
  }
};

const descargarNomina = async (idGrupo) => {
    try {
        const response = await axios.get(
            `/reportes/nomina/grupo/${idGrupo}`,
            { responseType: "blob" }
        );

        console.log('respuesta excel: ', response)

        // Descargar archivo
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
  <AuthorizationFallback :permissions="['todo-acceso-permisos']">
    <div class="w-full space-y-4 py-2 px-3" v-if="matriculados">
      <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl m-2">
        Especialidad: {{ matriculados.especialidad }}
        Módulo: {{ matriculados.modulo }}
      </h2>

      <div class="flex justify-start mb-4 ml-2">
        <Button title="Cambiar de Grupo Seleccionados" @click="cambiarGrupo"
          :disabled="estudiantesSeleccionados.length === 0" :loading="saving" variant="secondary">
          <ArrowPathIcon class="h-5 w-5 mr-2" />
          Cambiar Grupo ({{ estudiantesSeleccionados.length }})
        </Button>
      </div>

      <div class="flex justify-start mb-4 ml-2">
        <Button title="Descargar nomina"  @click="descargarNomina(props.id)" variant="secondary">
        </Button>
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
              <input type="checkbox" :value="estudiante.nro_documento" v-model="estudiantesSeleccionados"
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
  </AuthorizationFallback>
</template>