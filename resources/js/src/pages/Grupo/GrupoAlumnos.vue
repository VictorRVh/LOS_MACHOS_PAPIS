<script setup>
import { onMounted, computed, ref } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import axios from 'axios';
import Slider from '../../components/ui/Slider.vue';
import useModalToast from '../../composables/useModalToast';
import useExportAlumnos from '../../composables/tabla/useAlumnosMatricula.js';
import BaseButton from "../../components/ui/Button.vue"

import useCertificado from "../../store/Grupo/useCertificadoStore.js"

// IMPORTACIÓN DEL NUEVO CERTIFICADO MODULAR
import { generateCertificate } from "../../pdf/CertificadoPDF.js";

// IMPORTACIÓN DE CONSTANCIA
import { generateConstanciaEstudiante } from "../../pdf/CosntanciaEstudiante.js";
import useCertificadoStore from '../../store/Grupo/useCertificadoStore.js';

// IMPORTACIÓN DE CONSTANCIA DE EGRESADO (NUEVO)
// Asumo que la función exportada se llama generateConstanciaEgresado, ajusta si es diferente en tu archivo JS
import { generateConstanciaEgresado } from "../../pdf/ConstanciaEgresado.js"; 

const props = defineProps({
  id: { type: [String, Number], required: true },
});

const { showToast } = useModalToast();
const matriculaStore = useMatriculaStore();
const certificadoStore = useCertificadoStore();
const dataAlumnoCertificado = useCertificado();
const { exportarAlumnos } = useExportAlumnos();

const loading = ref(true);
const estudiantesSeleccionados = ref([]);

const showCertificadoModal = ref(false);
const codigoCertificado = ref('');
const selectedMatriculaId = ref(null);
const esDuplicado = ref(false);

const matriculados = computed(() => matriculaStore.matriculadosPorGrupoExtendido);

onMounted(async () => {
  loading.value = true;
  await matriculaStore.fetchMatriculadosPorGrupoExtendido(props.id);
  loading.value = false;
});

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

const showModal = ref(false);
const nuevoGrupoId = ref("");
const saving = ref(false);

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
      nombre: `${e.nombre} ${e.apellidos}`
    }))
  };
  exportarAlumnos(data);
};

// FUNCIÓN PARA GENERAR LA CONSTANCIA DE ESTUDIANTE
const imprimirConstancia = (estudiante) => {
  const dataParaConstancia = {
    estudiante: ` ${estudiante.nombre} ${estudiante.apellidos}`,
    nro_documento: estudiante.nro_documento,
    especialidad: matriculados.value.especialidad,
    modulo: matriculados.value.modulo,
    id_matricula: estudiante.id_matricula,
    periodo: matriculados.value.periodo || "2025"
  };
  generateConstanciaEstudiante(dataParaConstancia);
};

// FUNCIÓN PARA GENERAR LA CONSTANCIA DE EGRESADO (NUEVO)
const imprimirConstanciaEgresado = (estudiante) => {
  // Preparamos los datos combinando info del estudiante y del grupo (matriculados)
  const dataParaEgresado = {
    estudiante: `${estudiante.nombre} ${estudiante.apellidos}`,
    nro_documento: estudiante.nro_documento,
    especialidad: matriculados.value.especialidad,
    modulo: matriculados.value.modulo,
    fecha_inicio: matriculados.value.fecha_inicio,
    fecha_fin: matriculados.value.fecha_fin,
    horas: matriculados.value.horas, // Si tienes las horas en el grupo
    creditos: matriculados.value.creditos, // Si tienes créditos
    periodo: matriculados.value.periodo || "2025"
  };
  
  // Llamamos a la función importada
  generateConstanciaEgresado(dataParaEgresado);
};

// FUNCIÓN PARA GENERAR EL CERTIFICADO MODULAR
const generateSelectedCertificates = async (idMatricula, codigo) => {
  try {
    await dataAlumnoCertificado.loadCertificados(idMatricula);
    const data = dataAlumnoCertificado.certificados;

    if (data) {
      generateCertificate(data, codigo);
    } else {
      showToast("No se encontraron datos para el certificado", "warning");
    }
  } catch (error) {
    console.error(error);
    showToast("Error al generar el certificado modular", "error");
  }
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
<<<<<<< HEAD

const openCertificadoModal = async (matriculaId) => {
  selectedMatriculaId.value = matriculaId;
  codigoCertificado.value = '';

  const response = await certificadoStore.loadCheckCertificados({
    id_matricula: matriculaId,
    tipo_documento: 3,
  });

  esDuplicado.value = response.existe;

  showCertificadoModal.value = true;
};

const emitirCertificado = () => {
  // if (!codigoCertificado.value) return;

  generateSelectedCertificates(
    selectedMatriculaId.value,
    codigoCertificado.value
  );

  showCertificadoModal.value = false;
};


=======
>>>>>>> 69d5670934ac5103672d0e22f38c788dcde48c45
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos']">
    <div class="w-full space-y-4 py-2 px-3" v-if="matriculados">
      <div class="flex justify-end mb-4 gap-6 ml-2">
        <Button title="Descargar nomina" @click="descargarNomina(props.id)" variant="secondary" />
        <Button title="Exportar Alumnos" @click="exportar()" variant="secondary" />
        <Button title="Exporta registro de matrículas y evaluaciones" @click="exportarMatriculaEvaluaciones(props.id)"
          variant="secondary" />
      </div>

      <Table>
        <THead>
          <Th>N°</Th>
          <Th>DNI</Th>
          <Th>Apellidos y Nombres</Th>
          <Th>Sexo</Th>
          <Th>F. Nacimiento</Th>
          <Th>Teléfono</Th>
          <Th>Correo Electrónico</Th>
          <Th class="text-center">Acciones</Th>
        </THead>

        <TBody>
          <Tr v-for="(estudiante, index) in matriculados.estudiantes" :key="estudiante.nro_documento"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <Td>{{ index + 1 }}</Td>
            <Td>{{ estudiante.nro_documento }}</Td>
            <Td>{{ estudiante.nombre }} {{ estudiante.apellidos }}</Td>
            <Td>{{ estudiante.sexo }}</Td>
            <Td>{{ estudiante.fecha_nacimiento }}</Td>
            <Td>{{ estudiante.celular_personal ?? '-' }}</Td>
            <Td>{{ estudiante.correo_electronico ?? '-' }}</Td>

            <Td>
              <div class="flex gap-2 justify-center">
                
                <!-- BOTÓN CONSTANCIA ESTUDIANTE -->
                <BaseButton title="Constancia Est." @click="imprimirConstancia(estudiante)"
                  class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                  <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                  </template>
                </BaseButton>

                <!-- NUEVO BOTÓN: CONSTANCIA DE EGRESADO -->
                <BaseButton title="Constancia Egresado" @click="imprimirConstanciaEgresado(estudiante)"
                  class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg shadow">
                  <template #icon>
                    <!-- Icono tipo Birrete/Graduación -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.499 5.516 50.548 50.548 0 0 0-2.658.813m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                  </template>
                </BaseButton>

                <!-- BOTÓN CERTIFICADO MODULAR -->
                <BaseButton title="Certificado Modular" @click="openCertificadoModal(estudiante.id_matricula)"
                  class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                  <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                  </template>
                </BaseButton>
              </div>
            </Td>
          </Tr>

          <Tr v-if="matriculados.estudiantes?.length === 0 && !loading">
            <Td colspan="16" class="text-center py-4">No hay estudiantes matriculados en este grupo.</Td>
          </Tr>

          <Tr v-if="loading">
            <Td colspan="16" class="text-center py-4">Cargando estudiantes...</Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <div v-else class="text-center p-8">Cargando información del grupo...</div>

    <Slider :show="showModal" title="Cambiar Grupo" @hide="showModal = false">
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
      <div class="mt-4 space-y-3">
        <p class="text-gray-600 dark:text-gray-300">Estás a punto de mover <strong>{{ estudiantesSeleccionados.length
        }}</strong> estudiantes.</p>
        <div class="flex justify-end gap-2 mt-6">
          <Button title="Cancelar" variant="secondary" @click="showModal = false" />
          <Button title="Confirmar" variant="primary" :disabled="!nuevoGrupoId || saving" :loading="saving"
            @click="cambiarGrupo" />
        </div>
      </div>
    </Slider>

    <!-- MODAL CERTIFICADO -->
    <div v-if="showCertificadoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">
          Emitir Certificado Modular
        </h2>

        <!-- AVISO DUPLICADO -->
        <div v-if="esDuplicado" class="mb-4 p-3 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
          ⚠️ Este certificado ya fue emitido.
          Se marcará como <b>DUPLICADO</b>.
        </div>

        <!-- INPUT SOLO SI NO ES DUPLICADO -->
        <div v-if="!esDuplicado" class="mb-4">
          <label class="block text-sm font-medium mb-1">
            Código del certificado
          </label>
          <input v-model="codigoCertificado" type="text" placeholder="Ej: CM-2026-001"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500" />
        </div>

        <!-- ACCIONES -->
        <div class="flex justify-end gap-2">
          <button @click="showCertificadoModal = false" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
            Cancelar
          </button>

          <button @click="emitirCertificado" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">
            {{ esDuplicado ? 'Emitir duplicado' : 'Emitir' }}
          </button>
        </div>
      </div>
    </div>

  </AuthorizationFallback>
</template>