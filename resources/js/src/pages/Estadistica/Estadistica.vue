<template>
  <AuthorizationFallback :permissions="['todo-acceso-estadísticas', 'ver-estadísticas']">
    <div class="p-6 bg-gray-100 min-h-screen">

      <!-- CONTENEDOR ANIMADO -->
      <transition name="fade" mode="out-in">

        <!-- VISTA PRINCIPAL (EL DASHBOARD CON BOTONES) -->
        <div v-if="!reporteActivo" key="menu">
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-cetpro">Estadísticas Institucionales</h1>
            <p class="text-gray-500">Seleccione el reporte que desea consultar para este periodo académico.</p>
          </div>

          <!-- GRID DE BOTONES/TARJETAS -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <button v-for="item in menu" :key="item.id" @click="reporteActivo = item.id"
              class="bg-white text-left rounded-xl border-l-[6px] border-cetpro shadow-google-sm hover:shadow-google hover:-translate-y-1 transition-all duration-300 p-6 group">

              <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-cetpro/10 rounded-lg group-hover:bg-cetpro transition-colors">
                  <component :is="item.icon" class="h-8 w-8 text-cetpro group-hover:text-white" />
                </div>
                <span class="text-2xl font-black text-gray-200 group-hover:text-cetpro/20">{{ item.id }}</span>
              </div>

              <h3 class="text-xl font-bold text-gray-800 mb-2">{{ item.title }}</h3>
              <p class="text-sm text-gray-500 leading-relaxed">{{ item.desc }}</p>

              <div class="mt-4 flex items-center text-cetpro font-bold text-sm uppercase tracking-wider">
                Abrir Reporte
                <svg xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- VISTA DE LOS REPORTES (CARGA DINÁMICA) -->
        <div v-else key="reporte" class="space-y-4">
          <!-- BARRA DE NAVEGACIÓN INTERNA -->
          <div class="bg-white shadow-google-sm p-4 rounded-xl flex items-center justify-between">
            <button @click="reporteActivo = null"
              class="flex items-center gap-2 text-cetpro font-bold hover:bg-gray-100 px-4 py-2 rounded-lg transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              VOLVER AL MENÚ
            </button>
            <div class="text-right">
              <span class="text-xs font-bold text-gray-400 block uppercase">Reporte Seleccionado</span>
              <span class="text-cetpro font-black">REPORTE {{ reporteActivo }}</span>
            </div>
          </div>

          <!-- AQUÍ SE RENDERIZA EL ARCHIVO CORRESPONDIENTE -->
          <div class="bg-white rounded-xl shadow-google overflow-hidden border">
            <component :is="componenteActual" />
          </div>
        </div>

      </transition>

    </div>

  </AuthorizationFallback>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  ChartBarIcon, UsersIcon, AcademicCapIcon,
  RectangleStackIcon, PresentationChartLineIcon, ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
// IMPORTAR TUS HIJOS (Asegúrate que los nombres de archivo coincidan)
import E101 from './estadistica101.vue';
import E104 from './estadistica104.vue';
import E201 from './estadistica201.vue';
import E202 from './estadistica202.vue';
import E203 from './estadistica203.vue';
import E205 from './estadistica205.vue';

const reporteActivo = ref(null);

const menu = [
  { id: '101', title: 'Aprobados y Retirados', desc: 'Situación académica según ciclo y sexo del estudiante.', icon: ChartBarIcon },
  { id: '104', title: 'Denominación Carrera', desc: 'Matrícula y retiro según especialidad técnica.', icon: AcademicCapIcon },
  { id: '201', title: 'Matrícula por Edad', desc: 'Distribución demográfica por rangos de edad y sexo.', icon: UsersIcon },
  { id: '202', title: 'Opciones Técnico Pro.', desc: 'Reporte basado en las opciones técnico productivas.', icon: PresentationChartLineIcon },
  { id: '203', title: 'Nivel Educativo', desc: 'Reporte por grado de instrucción de los participantes.', icon: RectangleStackIcon },
  { id: '205', title: 'Secciones por Turno', desc: 'Consolidado de secciones por ciclo y horario.', icon: ClipboardDocumentCheckIcon },
];

// Lógica para saber qué componente mostrar
const componenteActual = computed(() => {
  switch (reporteActivo.value) {
    case '101': return E101;
    case '104': return E104;
    case '201': return E201;
    case '202': return E202;
    case '203': return E203;
    case '205': return E205;
    default: return null;
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>