<script setup>
import { onMounted, onBeforeUnmount, computed, nextTick, ref } from 'vue';
import { ArrowDownTrayIcon, ChevronDownIcon, DocumentTextIcon, TableCellsIcon } from "@heroicons/vue/24/outline";
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

import useCertificado from "../../store/Grupo/useCertificadoStore.js";
import { generateConstanciaEstudiante } from "../../pdf/CosntanciaEstudiante.js";
import useCertificadoStore from '../../store/Grupo/useCertificadoStore.js';
import { generateConstanciaEgresado } from "../../pdf/ConstanciaEgresado.js";
import { generateCertificadoModular } from '../../pdf/CertificadoModular.js';
import { generateCertificadoEstudio } from '../../pdf/CertificadoEstudio.js';

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
const openDocumentsMenuId = ref(null);
const openDocumentsMenuStyles = ref({});
const documentsButtonRefs = ref({});
const documentsMenuRef = ref(null);

const matriculados = computed(() => matriculaStore.matriculadosPorGrupoExtendido);

onMounted(async () => {
  loading.value = true;
  await matriculaStore.fetchMatriculadosPorGrupoExtendido(props.id);
  loading.value = false;

  document.addEventListener("click", handleClickOutside);
  window.addEventListener("resize", handleViewportChange);
  window.addEventListener("scroll", handleViewportChange, true);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("resize", handleViewportChange);
  window.removeEventListener("scroll", handleViewportChange, true);
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
      estudiantesSeleccionados.value = matriculados.value.estudiantes.map((e) => e.id_matricula);
    } else {
      estudiantesSeleccionados.value = [];
    }
  }
});

const showModal = ref(false);
const nuevoGrupoId = ref("");
const saving = ref(false);

const cambiarGrupo = async () => {
  showModal.value = false;
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
};

const exportar = () => {
  const data = {
    especialidad: matriculados.value.especialidad,
    modulo: matriculados.value.modulo,
    seccion: matriculados.value.seccion,
    turno: matriculados.value.turno,
    docente: matriculados.value.docente ?? "No asignado",
    matriculados: matriculados.value.estudiantes.map((e) => ({
      ...e,
      nombre: `${e.nombre} ${e.apellidos}`
    }))
  };
  exportarAlumnos(data);
};

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

const imprimirConstanciaEgresado = (estudiante) => {
  const dataParaEgresado = {
    estudiante: `${estudiante.nombre} ${estudiante.apellidos}`,
    nro_documento: estudiante.nro_documento,
    especialidad: matriculados.value.especialidad,
    modulo: matriculados.value.modulo,
    fecha_inicio: matriculados.value.fecha_inicio,
    fecha_fin: matriculados.value.fecha_fin,
    horas: matriculados.value.horas,
    creditos: matriculados.value.creditos,
    periodo: matriculados.value.periodo || "2025"
  };

  generateConstanciaEgresado(dataParaEgresado);
};

const generateSelectedCertificates = async (idMatricula, codigo) => {
  try {
    await dataAlumnoCertificado.loadCertificados(idMatricula);
    const data = dataAlumnoCertificado.certificados;

    if (data) {
      generateCertificadoModular(data, codigo);
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
    link.setAttribute("download", "Registro de matriculas y evaluaciones.xlsx");
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

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
  generateSelectedCertificates(
    selectedMatriculaId.value,
    codigoCertificado.value
  );

  showCertificadoModal.value = false;
};

const emitirCertificadoEstudio = async (matriculaId) => {
  try {
    await dataAlumnoCertificado.loadCertificados(matriculaId);
    const data = dataAlumnoCertificado.certificados;

    if (!data) {
      showToast("No se encontraron datos para el certificado de estudios", "warning");
      return;
    }

    await generateCertificadoEstudio(data, null, {
      periodo: matriculados.value?.periodo || "-",
    });
  } catch (error) {
    console.error(error);
    showToast("Error al generar el certificado de estudios", "error");
  }
};

const setDocumentsButtonRef = (matriculaId, el) => {
  if (el) {
    documentsButtonRefs.value[matriculaId] = el;
    return;
  }

  delete documentsButtonRefs.value[matriculaId];
};

const closeDocumentsMenu = () => {
  openDocumentsMenuId.value = null;
};

const updateDocumentsMenuPosition = (matriculaId) => {
  const button = documentsButtonRefs.value[matriculaId];
  if (!button) return;

  const rect = button.getBoundingClientRect();
  openDocumentsMenuStyles.value = {
    position: "fixed",
    top: `${rect.bottom + 6}px`,
    left: `${Math.max(12, rect.right - 250)}px`,
    zIndex: 9999,
  };
};

const toggleDocumentsMenu = async (matriculaId) => {
  if (openDocumentsMenuId.value === matriculaId) {
    closeDocumentsMenu();
    return;
  }

  openDocumentsMenuId.value = matriculaId;
  await nextTick();
  updateDocumentsMenuPosition(matriculaId);
};

const handleClickOutside = (event) => {
  const activeId = openDocumentsMenuId.value;
  if (!activeId) return;

  const button = documentsButtonRefs.value[activeId];
  if (
    documentsMenuRef.value &&
    !documentsMenuRef.value.contains(event.target) &&
    button &&
    !button.contains(event.target)
  ) {
    closeDocumentsMenu();
  }
};

const handleViewportChange = () => {
  if (!openDocumentsMenuId.value) return;
  updateDocumentsMenuPosition(openDocumentsMenuId.value);
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-grupos']">
    <div class="w-full space-y-4 px-3 py-2" v-if="matriculados">
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
          <Tr
            v-for="(estudiante, index) in matriculados.estudiantes"
            :key="estudiante.nro_documento"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
          >
            <Td>{{ index + 1 }}</Td>
            <Td>{{ estudiante.nro_documento }}</Td>
            <Td>{{ estudiante.nombre }} {{ estudiante.apellidos }}</Td>
            <Td>{{ estudiante.sexo }}</Td>
            <Td>{{ estudiante.fecha_nacimiento }}</Td>
            <Td>{{ estudiante.celular_personal ?? '-' }}</Td>
            <Td>{{ estudiante.correo_electronico ?? '-' }}</Td>

            <Td>
              <div class="flex justify-center">
                <button
                  :ref="(el) => setDocumentsButtonRef(estudiante.id_matricula, el)"
                  type="button"
                  @click="toggleDocumentsMenu(estudiante.id_matricula)"
                  class="inline-flex h-8 items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-2.5 text-xsm font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
                  :aria-expanded="openDocumentsMenuId === estudiante.id_matricula"
                  title="Documentos"
                >
                  <ArrowDownTrayIcon class="h-4 w-4 shrink-0" />
                  <span>Documentos</span>
                  <ChevronDownIcon class="h-4 w-4 shrink-0 transition-transform duration-200" :class="openDocumentsMenuId === estudiante.id_matricula ? 'rotate-180' : ''" />
                </button>
              </div>
            </Td>
          </Tr>

          <Tr v-if="matriculados.estudiantes?.length === 0 && !loading">
            <Td colspan="16" class="py-4 text-center">No hay estudiantes matriculados en este grupo.</Td>
          </Tr>

          <Tr v-if="loading">
            <Td colspan="16" class="py-4 text-center">Cargando estudiantes...</Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <div v-else class="p-8 text-center">Cargando información del grupo...</div>

    <Teleport to="#grupo-header-actions">
      <div v-if="matriculados" class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="descargarNomina(props.id)"
          class="inline-flex min-h-[34px] items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-2.5 py-1 text-left text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
        >
          <TableCellsIcon class="h-3.5 w-3.5 shrink-0" />
          <span class="text-[12px] font-medium">Nomina</span>
          <ArrowDownTrayIcon class="h-3.5 w-3.5 shrink-0 opacity-60" />
        </button>

        <button
          type="button"
          @click="exportar()"
          class="inline-flex min-h-[34px] items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-2.5 py-1 text-left text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
        >
          <TableCellsIcon class="h-3.5 w-3.5 shrink-0" />
          <span class="text-[12px] font-medium">Alumnos</span>
          <ArrowDownTrayIcon class="h-3.5 w-3.5 shrink-0 opacity-60" />
        </button>

        <button
          type="button"
          @click="exportarMatriculaEvaluaciones(props.id)"
          class="inline-flex min-h-[34px] items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-2.5 py-1 text-left text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
        >
          <TableCellsIcon class="h-3.5 w-3.5 shrink-0" />
          <span class="text-[12px] font-medium">Matriculas y evaluaciones</span>
          <ArrowDownTrayIcon class="h-3.5 w-3.5 shrink-0 opacity-60" />
        </button>
      </div>
    </Teleport>

    <Teleport to="body">
      <div
        v-if="openDocumentsMenuId"
        ref="documentsMenuRef"
        :style="openDocumentsMenuStyles"
        class="min-w-[250px] rounded-[3px] border border-slate-200 bg-white p-1.5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
      >
        <button
          type="button"
          @click="imprimirConstancia(matriculados.estudiantes.find((estudiante) => estudiante.id_matricula === openDocumentsMenuId)); closeDocumentsMenu()"
          class="flex w-full items-center gap-2 rounded-[3px] px-2.5 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-300"
        >
          <DocumentTextIcon class="h-4 w-4 shrink-0" />
          <span class="flex-1">Constancia Est.</span>
          <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-rose-600 dark:text-rose-300">PDF</span>
        </button>

        <button
          type="button"
          @click="emitirCertificadoEstudio(openDocumentsMenuId); closeDocumentsMenu()"
          class="flex w-full items-center gap-2 rounded-[3px] px-2.5 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-300"
        >
          <DocumentTextIcon class="h-4 w-4 shrink-0" />
          <span class="flex-1">Certificado Estudio</span>
          <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-rose-600 dark:text-rose-300">PDF</span>
        </button>

        <button
          type="button"
          @click="openCertificadoModal(openDocumentsMenuId); closeDocumentsMenu()"
          class="flex w-full items-center gap-2 rounded-[3px] px-2.5 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-300"
        >
          <DocumentTextIcon class="h-4 w-4 shrink-0" />
          <span class="flex-1">Certificado Modular</span>
          <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-rose-600 dark:text-rose-300">PDF</span>
        </button>
      </div>
    </Teleport>

    <Slider :show="showModal" title="Cambiar Grupo" @hide="showModal = false">
      <hr class="mb-4 border-t-2 border-cetpro dark:border-cetpro-light" />
      <div class="mt-4 space-y-3">
        <p class="text-gray-600 dark:text-gray-300">Estás a punto de mover <strong>{{ estudiantesSeleccionados.length }}</strong> estudiantes.</p>
        <div class="mt-6 flex justify-end gap-2">
          <Button title="Cancelar" variant="secondary" @click="showModal = false" />
          <Button title="Confirmar" variant="primary" :disabled="!nuevoGrupoId || saving" :loading="saving" @click="cambiarGrupo" />
        </div>
      </div>
    </Slider>

    <div v-if="showCertificadoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
        <h2 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
          Emitir Certificado Modular
        </h2>

        <div v-if="esDuplicado" class="mb-4 rounded-lg bg-yellow-100 p-3 text-sm text-yellow-800">
          Este certificado ya fue emitido.
          Se marcará como <b>DUPLICADO</b>.
        </div>

        <div v-if="!esDuplicado" class="mb-4">
          <label class="mb-1 block text-sm font-medium">
            Código del certificado
          </label>
          <input
            v-model="codigoCertificado"
            type="text"
            placeholder="Ej: CM-2026-001"
            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-green-500"
          />
        </div>

        <div class="flex justify-end gap-2">
          <button @click="showCertificadoModal = false" class="rounded-lg bg-gray-200 px-4 py-2 hover:bg-gray-300">
            Cancelar
          </button>

          <button @click="emitirCertificado" class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700">
            {{ esDuplicado ? 'Emitir duplicado' : 'Emitir' }}
          </button>
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
