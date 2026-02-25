<script setup>
import { ref } from "vue";
import useHttpRequest from "../../composables/useHttpRequest";
import { generateReporteEspecialidadEstudiante } from "../../pdf/ReporteEspecialidadEstudiante";

const { store: buscar } = useHttpRequest("/buscarEstudiante");
const { store: egresado } = useHttpRequest("/egresados");

const query = ref("");
const loading = ref(false);
const estudiante = ref(null);
const historialAcademico = ref([]);
const hasSearched = ref(false);
const error = ref("");
const detalleVista = ref({});

const especialidadAbierta = ref(null);

const showEgresadoModal = ref(false);
const selectedEspecialidadId = ref(null);
const selectedEstudianteId = ref(null);

const buscarEstudiante = async () => {
  if (!query.value.trim()) return;

  loading.value = true;
  hasSearched.value = true;
  error.value = "";

  try {
    estudiante.value = null;
    historialAcademico.value = [];
    detalleVista.value = {};

    const response = await buscar({
      nro_documento: query.value.trim(),
    });

    if (response.success) {
      estudiante.value = response.data.estudiante;
      historialAcademico.value = response.data.historial_academico;
    } else {
      error.value = response.message || "No se encontró el estudiante";
    }
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.message || "Error al buscar estudiante";
  } finally {
    loading.value = false;
  }
};

const toggleEspecialidad = (id) => {
  especialidadAbierta.value = especialidadAbierta.value === id ? null : id;
};

const calcularEdad = (fecha) => {
  const hoy = new Date();
  const nacimiento = new Date(fecha);
  let edad = hoy.getFullYear() - nacimiento.getFullYear();
  const mes = hoy.getMonth() - nacimiento.getMonth();

  if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
    edad--;
  }

  return edad;
};

const sexoTexto = (sexo) =>
  sexo === "M" ? "Masculino" : sexo === "F" ? "Femenino" : "-";

const lugarNacimientoTexto = (est) => {
  const ln = est?.lugar_nacimiento;
  if (!ln) return "-";
  return [ln.distrito, ln.provincia, ln.departamento, ln.pais]
    .filter(Boolean)
    .join(", ");
};

const formatearFecha = (fecha) => {
  if (!fecha) return "";
  const f = new Date(fecha);
  const dia = String(f.getDate()).padStart(2, "0");
  const mes = String(f.getMonth() + 1).padStart(2, "0");
  const anio = f.getFullYear();
  return `${dia}/${mes}/${anio}`;
};

const esFinalizado = (fechaFin) => {
  if (!fechaFin) return false;
  const hoy = new Date();
  const fin = new Date(fechaFin);
  return hoy > fin;
};

const pasarEgresado = (especialidadId, estudianteId) => {
  selectedEspecialidadId.value = especialidadId;
  selectedEstudianteId.value = estudianteId;
  showEgresadoModal.value = true;
};

const confirmarEgresado = async () => {
  try {
    await egresado({
      id_estudiante: selectedEstudianteId.value,
      id_especialidad: selectedEspecialidadId.value,
    });

    showEgresadoModal.value = false;
    selectedEspecialidadId.value = null;
    selectedEstudianteId.value = null;
  } catch (err) {
    console.error(err);
  }
};

const setDetalleVista = (matriculaId, vista) => {
  detalleVista.value[matriculaId] = vista;
};

const getDetalleVista = (matriculaId) => detalleVista.value[matriculaId] || "notas";

const getEstadoMatricula = (matricula = {}) => {
  const fromApi = Number(matricula?.matriculado_estado);
  const fallbackBool = matricula?.matriculado ? 1 : 0;
  const estado = Number.isFinite(fromApi) ? fromApi : fallbackBool;

  if (estado === 1) return { label: "Matriculado", className: "badge-matriculado" };
  if (estado === 2) return { label: "Retirado", className: "badge-retirado" };
  if (estado === 3) return { label: "Retirado Justificado", className: "badge-retirado-justificado" };

  return { label: "Pendiente", className: "badge-pendiente" };
};

const getEstadoReserva = (matricula = {}) => {
  const fromApi = Number(matricula?.reserva_estado);
  const fallbackBool = matricula?.reserva ? 1 : 0;
  const estado = Number.isFinite(fromApi) ? fromApi : fallbackBool;

  if (estado === 1) return { label: "Reserva activa", className: "badge-reserva" };
  if (estado === 3) return { label: "Reserva utilizada", className: "badge-reserva-usada" };

  return null;
};

const exportarEspecialidadPDF = async (especialidad) => {
  if (!estudiante.value || !especialidad) return;
  await generateReporteEspecialidadEstudiante(estudiante.value, especialidad);
};
</script>

<template>
  <div class="page-wrap">
    <div class="panel search-panel" :class="{ compact: !!estudiante }">
      <div class="section-head">
        <h1 class="section-title">Buscar estudiante</h1>
        <p v-if="!estudiante" class="section-subtitle">
          Consulta rápida por DNI para ver perfil y avance académico.
        </p>
      </div>
      <div class="search-wrap">
        <input
          v-model="query"
          @keyup.enter="buscarEstudiante"
          class="search-input"
          type="text"
          placeholder="Ingrese DNI del estudiante..."
        />
        <button @click="buscarEstudiante" :disabled="loading" class="search-btn">
          {{ loading ? "..." : "BUSCAR" }}
        </button>
      </div>
    </div>

    <div v-if="error" class="error-box">
      {{ error }}
    </div>

    <div v-if="!hasSearched" class="panel empty-state">
      <div class="empty-head">
        <h2 class="section-title mb-0">Consulta de historial estudiantil</h2>
        <span class="empty-badge">Módulo de consulta</span>
      </div>
      <div class="empty-grid">
        <div class="empty-block">
          <p class="empty-title">Cómo usar</p>
          <ul class="empty-list">
            <li>Ingrese el DNI del estudiante.</li>
            <li>Presione <b>Buscar</b> o la tecla Enter.</li>
            <li>Revise datos personales, módulos, notas y asistencia.</li>
          </ul>
        </div>
        <div class="empty-block">
          <p class="empty-title">Datos que mostrará</p>
          <ul class="empty-list">
            <li>Información personal validada.</li>
            <li>Historial por especialidad, periodo y módulo.</li>
            <li>Resumen de notas por unidad y promedio.</li>
            <li>Resumen de asistencia por módulo.</li>
          </ul>
        </div>
      </div>
    </div>

    <div v-if="estudiante" class="panel">
      <h2 class="section-title">Información personal</h2>
      <div class="info-table">
        <div class="info-item">
          <span class="label">Estudiante</span>
          <span class="value main">{{ estudiante.nombre_completo }}</span>
        </div>
        <div class="info-item">
          <span class="label">DNI</span>
          <span class="value">{{ estudiante.nro_documento }}</span>
        </div>
        <div class="info-item">
          <span class="label">Sexo</span>
          <span class="value">{{ sexoTexto(estudiante.sexo) }}</span>
        </div>
        <div class="info-item">
          <span class="label">Edad</span>
          <span class="value">{{ calcularEdad(estudiante.fecha_nacimiento) }} años</span>
        </div>
        <div class="info-item info-wide">
          <span class="label">Lugar de Nacimiento</span>
          <span class="value">{{ lugarNacimientoTexto(estudiante) }}</span>
        </div>
      </div>
    </div>

    <div v-if="historialAcademico.length > 0" class="space-y-2">
      <h2 class="section-title">Historial académico</h2>

      <div
        v-for="especialidad in historialAcademico"
        :key="especialidad.id"
        class="panel p-0 overflow-hidden"
      >
        <div
          @click="toggleEspecialidad(especialidad.id)"
          @keydown.enter.prevent="toggleEspecialidad(especialidad.id)"
          @keydown.space.prevent="toggleEspecialidad(especialidad.id)"
          class="accordion-head"
          role="button"
          tabindex="0"
        >
          <div>
            <h3 class="text-sm font-semibold text-slate-800">{{ especialidad.nombre }}</h3>
            <p class="text-sm text-slate-500">{{ especialidad.total_modulos }} módulos</p>
          </div>
          <div class="flex items-center gap-1.5">
            <button
              class="btn-pdf"
              @click.stop="exportarEspecialidadPDF(especialidad)"
              title="Descargar reporte PDF de especialidad"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M6 2h9l5 5v15H6z" stroke-width="2" />
                <path d="M15 2v5h5" stroke-width="2" />
                <path d="M8 14h8M8 18h8M8 10h4" stroke-width="2" />
              </svg>
              PDF
            </button>
            <span class="chev" :class="especialidadAbierta === especialidad.id ? 'open' : ''">⌃</span>
          </div>
        </div>

        <transition name="fade">
          <div
            v-if="especialidadAbierta === especialidad.id"
            class="p-2.5 border-t border-slate-200 space-y-2.5"
          >
            <div v-for="periodo in especialidad.periodos" :key="periodo.id" class="space-y-2">
              <div class="period-chip">{{ periodo.nombre }}</div>

              <div class="space-y-2">
                <div v-for="item in periodo.modulos" :key="item.matricula_id" class="module-card">
                  <div class="module-top">
                    <div class="module-title">
                      Módulo {{ item.modulo.numero }}: {{ item.modulo.descripcion }}
                    </div>
                    <div class="module-top-right">
                      <div class="inline-toggle">
                        <button
                          class="toggle-btn"
                          :class="getDetalleVista(item.matricula_id) === 'notas' ? 'active' : ''"
                          @click="setDetalleVista(item.matricula_id, 'notas')"
                          title="Notas por unidad"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 5h16v14H4z" stroke-width="2" />
                            <path d="M8 9h8M8 13h5" stroke-width="2" />
                          </svg>
                          Notas
                        </button>
                        <button
                          class="toggle-btn"
                          :class="getDetalleVista(item.matricula_id) === 'asistencia' ? 'active' : ''"
                          @click="setDetalleVista(item.matricula_id, 'asistencia')"
                          title="Asistencia"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 7v6l4 2" stroke-width="2" />
                            <circle cx="12" cy="12" r="9" stroke-width="2" />
                          </svg>
                          Asistencia
                        </button>
                      </div>
                      <span
                        :class="esFinalizado(item.grupo.fecha_fin) ? 'state-end' : 'state-progress'"
                        class="state-pill"
                      >
                        {{ esFinalizado(item.grupo.fecha_fin) ? "FINALIZADO" : "EN CURSO" }}
                      </span>
                    </div>
                  </div>

                  <div class="module-table">
                    <div class="module-cell">
                      <span class="cell-label">Créditos</span>
                      <span class="cell-value">{{ item.modulo.creditos }}</span>
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Horas</span>
                      <span class="cell-value">{{ item.modulo.horas }}</span>
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Unidades didácticas</span>
                      <span class="cell-value">{{ item.modulo.nro_capacidades }}</span>
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Sección</span>
                      <span class="cell-value">{{ item.grupo.seccion || "N/A" }}</span>
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Turno</span>
                      <span class="cell-value">{{ item.grupo.turno || item.matricula.turno || "N/A" }}</span>
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Fechas</span>
                      <span class="cell-value"
                        >{{ formatearFecha(item.grupo.fecha_inicio) }} al
                        {{ formatearFecha(item.grupo.fecha_fin) }}</span
                      >
                    </div>
                    <div class="module-cell">
                      <span class="cell-label">Estado</span>
                      <div class="status-stack">
                        <span
                          class="status-chip"
                          :class="getEstadoMatricula(item.matricula).className"
                        >
                          {{ getEstadoMatricula(item.matricula).label }}
                        </span>
                        <span
                          v-if="getEstadoReserva(item.matricula)"
                          class="status-chip"
                          :class="getEstadoReserva(item.matricula).className"
                        >
                          {{ getEstadoReserva(item.matricula).label }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div v-if="getDetalleVista(item.matricula_id) === 'notas'" class="detail-panel">
                    <div class="detail-head">
                      <span>Notas por unidad didáctica</span>
                      <span class="detail-badge">
                        Promedio: {{ item.promedio_notas ?? "--" }}
                      </span>
                    </div>

                    <div class="mini-table">
                      <div class="mini-th">Unidad</div>
                      <div class="mini-th">Descripción</div>
                      <div class="mini-th">Nota</div>

                      <template
                        v-for="unidad in item.notas_unidades || []"
                        :key="`${item.matricula_id}-${unidad.id_capacidad}`"
                      >
                        <div class="mini-td" :class="{ 'exp-row': unidad.es_experiencia_formativa }">
                          {{ unidad.es_experiencia_formativa ? "EF" : `U${unidad.numero_unidad}` }}
                        </div>
                        <div class="mini-td" :class="{ 'exp-row': unidad.es_experiencia_formativa }">
                          {{ unidad.nombre_unidad }}
                        </div>
                        <div class="mini-td font-semibold" :class="{ 'exp-row': unidad.es_experiencia_formativa }">
                          {{ unidad.nota ?? "--" }}
                        </div>
                      </template>
                    </div>
                  </div>

                  <div v-if="getDetalleVista(item.matricula_id) === 'asistencia'" class="detail-panel">
                    <div class="detail-head">
                      <span>Resumen de asistencia</span>
                      <span class="detail-badge">
                        % Asistencia: {{ item.asistencia_resumen?.porcentaje_asistencia ?? "--" }}
                      </span>
                    </div>

                    <div class="asistencia-grid">
                      <div class="asistencia-item">
                        <span class="cell-label">Registros</span>
                        <span class="cell-value">{{ item.asistencia_resumen?.total_registros ?? 0 }}</span>
                      </div>
                      <div class="asistencia-item">
                        <span class="cell-label">Asistió</span>
                        <span class="cell-value">{{ item.asistencia_resumen?.asistio ?? 0 }}</span>
                      </div>
                      <div class="asistencia-item">
                        <span class="cell-label">Tardanzas</span>
                        <span class="cell-value">{{ item.asistencia_resumen?.tardanzas ?? 0 }}</span>
                      </div>
                      <div class="asistencia-item">
                        <span class="cell-label">Faltas</span>
                        <span class="cell-value">{{ item.asistencia_resumen?.faltas ?? 0 }}</span>
                      </div>
                      <div class="asistencia-item">
                        <span class="cell-label">Permisos</span>
                        <span class="cell-value">{{ item.asistencia_resumen?.permisos ?? 0 }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end pt-2">
                <button
                  :disabled="especialidad.es_egresado"
                  @click="!especialidad.es_egresado && pasarEgresado(especialidad.especialidad_programa, estudiante.id)"
                  :class="especialidad.es_egresado ? 'btn-disabled' : 'btn-primary'"
                  class="btn-action"
                >
                  {{ especialidad.es_egresado ? "YA ES EGRESADO" : "PASAR A EGRESADO" }}
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <div v-if="hasSearched && !loading && !estudiante && !error" class="panel text-center">
      <p class="text-slate-500">No se encontró ningún estudiante con ese documento.</p>
    </div>
  </div>

  <div v-if="showEgresadoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-md shadow-lg w-full max-w-md p-6 border border-slate-200">
      <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">
        ¿Desea pasar a este estudiante a Egresado?
      </h2>

      <div class="flex justify-end gap-2">
        <button @click="showEgresadoModal = false" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300">
          Cancelar
        </button>

        <button @click="confirmarEgresado" class="px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white">
          Sí
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.22s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.page-wrap {
  @apply p-2.5 md:p-3 space-y-2.5 min-h-[calc(100vh-130px)];
  background:
    radial-gradient(circle at top right, rgba(2, 132, 199, 0.08), transparent 34%),
    linear-gradient(180deg, #f3f6fa 0%, #eef2f7 100%);
}

.panel {
  @apply bg-white border border-slate-300 shadow-sm rounded-sm p-2.5;
  box-shadow:
    0 1px 0 rgba(15, 23, 42, 0.03),
    inset 0 1px 0 rgba(255, 255, 255, 0.65);
}

.section-head {
  @apply mb-1;
}

.section-title {
  @apply text-slate-900 font-semibold tracking-tight mb-0.5 text-[15px];
}

.section-subtitle {
  @apply text-[12px] text-slate-500;
}

.search-panel.compact {
  @apply py-1.5;
}

.search-panel.compact .section-head {
  @apply mb-1;
}

.search-panel.compact .section-title {
  @apply text-[15px] mb-0;
}

.search-wrap {
  @apply flex items-center border border-slate-300 rounded-sm overflow-hidden bg-white transition-colors;
}

.search-wrap:focus-within {
  border-color: #0b5f8a;
  box-shadow: 0 0 0 1px rgba(11, 95, 138, 0.1);
}

.search-input {
  @apply flex-1 px-2.5 py-1 bg-transparent text-slate-800 focus:outline-none text-[13px];
}

.search-btn {
  @apply px-3.5 py-1 bg-cetpro text-white font-semibold tracking-wide hover:bg-cetpro-dark transition disabled:opacity-50 text-[13px];
}

.error-box {
  @apply border border-red-300 bg-red-50 text-red-700 px-4 py-3 rounded-md;
}

.empty-state {
  @apply border-dashed;
}

.empty-head {
  @apply flex items-center justify-between gap-2 mb-2;
}

.empty-badge {
  @apply text-[10px] uppercase tracking-wide px-2 py-0.5 rounded border border-slate-300 bg-slate-100 text-slate-600;
}

.empty-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-2;
}

.empty-block {
  @apply border border-slate-300 bg-white rounded-sm p-2;
}

.empty-title {
  @apply text-[12px] font-semibold text-slate-800 mb-1;
}

.empty-list {
  @apply text-[12px] text-slate-600 space-y-0.5 pl-4 list-disc;
}

.info-table {
  @apply grid grid-cols-1 md:grid-cols-3 border border-slate-300 rounded-sm bg-white overflow-hidden;
}

.info-item {
  @apply flex flex-col gap-0.5 p-1.5 border-b border-slate-200 md:border-r;
}

.info-item:nth-child(-n + 3) {
  @apply bg-slate-50;
}

.info-wide {
  @apply md:col-span-2 md:border-r-0;
}

.info-item:nth-child(3) {
  @apply md:border-r-0;
}

.info-item:nth-child(4),
.info-item:nth-child(5) {
  @apply border-b-0;
}

.label {
  @apply text-[10px] uppercase tracking-wide text-slate-500 font-medium;
}

.value {
  @apply text-slate-900 font-medium text-[13px] leading-tight;
}

.value.main {
  @apply text-slate-900 text-[14px] leading-tight;
}

.accordion-head {
  @apply w-full flex justify-between items-center p-2.5 border-b border-slate-200 bg-white hover:bg-slate-50 transition text-left;
}

.chev {
  @apply text-slate-500 text-base transition-transform duration-200;
}

.chev.open {
  transform: rotate(180deg);
}

.btn-pdf {
  @apply inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold border border-slate-300 bg-white text-slate-700 rounded-sm;
}

.btn-pdf svg {
  width: 12px;
  height: 12px;
}

.btn-pdf:hover {
  @apply bg-slate-100;
}

.period-chip {
  @apply inline-block px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase bg-slate-800 text-white border border-slate-800 rounded;
}

.module-card {
  @apply border border-slate-300 rounded-sm p-2 bg-slate-50;
  box-shadow: none;
}

.module-top {
  @apply flex items-center justify-between gap-2 pb-1 mb-1 border-b border-slate-300;
}

.module-top-right {
  @apply flex items-center gap-2;
}

.inline-toggle {
  @apply flex items-center gap-1;
}

.toggle-btn {
  @apply inline-flex items-center gap-1 px-2 py-0.5 text-[10px] border border-slate-300 bg-white text-slate-700 rounded-sm;
}

.toggle-btn svg {
  width: 12px;
  height: 12px;
}

.toggle-btn.active {
  @apply bg-slate-800 text-white border-slate-800;
}

.module-title {
  @apply text-slate-900 font-semibold text-[13px];
}

.module-table {
  @apply grid grid-cols-1 md:grid-cols-4 border border-slate-300 rounded-sm overflow-hidden bg-white;
}

.module-cell {
  @apply p-1.5 border-b border-slate-200 md:border-r;
}

.module-cell:nth-child(4n) {
  @apply md:border-r-0;
}

.module-cell:nth-last-child(-n + 3) {
  @apply border-b-0;
}

.cell-label {
  @apply block text-[9px] uppercase tracking-wide text-slate-500;
}

.cell-value {
  @apply block text-[11px] font-semibold text-slate-800 leading-tight mt-0.5;
}

.status-stack {
  @apply flex flex-wrap items-center gap-1 mt-0.5;
}

.status-chip {
  @apply inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded-sm border uppercase tracking-wide;
}

.badge-matriculado {
  @apply bg-emerald-50 text-emerald-700 border-emerald-200;
}

.badge-retirado {
  @apply bg-red-50 text-red-700 border-red-200;
}

.badge-retirado-justificado {
  @apply bg-amber-50 text-amber-700 border-amber-200;
}

.badge-pendiente {
  @apply bg-slate-100 text-slate-700 border-slate-300;
}

.badge-reserva {
  @apply bg-cyan-50 text-cyan-700 border-cyan-200;
}

.badge-reserva-usada {
  @apply bg-indigo-50 text-indigo-700 border-indigo-200;
}

.detail-panel {
  @apply mt-2 border border-slate-300 rounded-sm bg-white;
}

.detail-head {
  @apply flex items-center justify-between gap-2 px-2 py-1 border-b border-slate-200 text-[11px] font-semibold text-slate-700;
}

.detail-badge {
  @apply px-2 py-0.5 border border-slate-300 rounded-sm text-[10px] bg-slate-50;
}

.mini-table {
  @apply grid grid-cols-[90px_minmax(0,1fr)_90px];
}

.mini-th {
  @apply px-2 py-1 text-[10px] uppercase tracking-wide text-slate-500 border-b border-r border-slate-200 bg-slate-50;
}

.mini-th:nth-child(3) {
  @apply border-r-0;
}

.mini-td {
  @apply px-2 py-1 text-[11px] text-slate-700 border-b border-r border-slate-200;
}

.mini-td:nth-child(3n) {
  @apply border-r-0;
}

.exp-row {
  @apply font-semibold text-cetpro;
}

.mini-table > .mini-td:nth-last-child(-n + 3) {
  @apply border-b-0;
}

.asistencia-grid {
  @apply grid grid-cols-2 md:grid-cols-5;
}

.asistencia-item {
  @apply p-2 border-r border-slate-200;
}

.asistencia-item:last-child {
  @apply border-r-0;
}

.state-pill {
  @apply px-2 py-0.5 text-[10px] font-semibold rounded border;
}

.state-progress {
  @apply bg-cetpro text-white border-cetpro;
}

.state-end {
  @apply bg-slate-700 text-white border-slate-700;
}

.btn-action {
  @apply px-3.5 py-1 text-white text-[12px] font-semibold tracking-wide rounded transition;
}

.btn-primary {
  @apply bg-cetpro hover:bg-cetpro-dark;
}

.btn-disabled {
  @apply bg-slate-400 cursor-not-allowed;
}
</style>
