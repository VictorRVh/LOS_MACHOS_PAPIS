<template>
  <AuthorizationFallback :permissions="['todo-acceso-estadísticas', 'ver-estadísticas']">
    <div class="min-h-screen bg-slate-100 px-3 py-3 transition-colors duration-300 dark:bg-slate-800">
      <transition name="fade" mode="out-in">
        <div v-if="!reporteActivo" key="menu" class="space-y-3">
          <section
            class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="flex flex-col gap-1.5">
              <div class="flex flex-col gap-1">
                <h1 class="text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
                  Estadísticas institucionales
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Seleccione el reporte que desea consultar para este periodo académico.
                </p>
              </div>
            </div>
          </section>

          <section
            class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="mb-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                Reportes disponibles
              </p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
              <button
                v-for="item in menu"
                :key="item.id"
                @click="reporteActivo = item.id"
                class="group border border-slate-200 border-l-[3px] border-l-cetpro bg-white p-4 text-left transition-all duration-200 hover:bg-slate-50 hover:shadow-sm dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900 dark:hover:bg-slate-800"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="space-y-3">
                    <div class="flex h-11 w-11 items-center justify-center border border-slate-200 bg-slate-50 text-cetpro transition-colors duration-200 dark:border-slate-700 dark:bg-slate-800 dark:text-cetpro-light">
                      <component :is="item.icon" class="h-5 w-5" />
                    </div>

                    <div>
                      <h3 class="text-[1.05rem] font-semibold text-slate-900 dark:text-slate-100">{{ item.title }}</h3>
                      <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ item.desc }}</p>
                    </div>
                  </div>

                  <span class="text-3xl font-semibold tracking-tight text-slate-200 transition-colors duration-200 group-hover:text-slate-300 dark:text-slate-700 dark:group-hover:text-slate-600">
                    {{ item.id }}
                  </span>
                </div>

                <div class="mt-4 flex items-center gap-2 text-[12px] font-semibold uppercase tracking-[0.18em] text-cetpro dark:text-cetpro-light">
                  <span>Abrir reporte</span>
                  <ArrowRightIcon class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                </div>
              </button>
            </div>
          </section>
        </div>

        <div v-else key="reporte" class="space-y-3">
          <section
            class="flex items-center justify-between border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
          >
            <button
              @click="reporteActivo = null"
              class="inline-flex items-center gap-2 text-sm font-semibold text-cetpro transition-colors hover:text-cetpro-dark dark:text-cetpro-light dark:hover:text-white"
            >
              <ArrowLeftIcon class="h-4 w-4" />
              <span>Volver al menú</span>
            </button>

            <div class="text-right">
              <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Reporte seleccionado
              </span>
              <span class="text-sm font-semibold text-cetpro dark:text-cetpro-light">Reporte {{ reporteActivo }}</span>
            </div>
          </section>

          <section
            class="overflow-hidden border border-slate-200 bg-white shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
          >
            <component :is="componenteActual" />
          </section>
        </div>
      </transition>
    </div>
  </AuthorizationFallback>
</template>

<script setup>
import { ref, computed } from "vue";
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  ChartPieIcon,
  AcademicCapIcon,
  UserGroupIcon,
  AdjustmentsHorizontalIcon,
  BuildingLibraryIcon,
  ClipboardDocumentListIcon,
} from "@heroicons/vue/24/outline";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import E101 from "./estadistica101.vue";
import E104 from "./estadistica104.vue";
import E201 from "./estadistica201.vue";
import E202 from "./estadistica202.vue";
import E203 from "./estadistica203.vue";
import E205 from "./estadistica205.vue";

const reporteActivo = ref(null);

const menu = [
  {
    id: "101",
    title: "Aprobados y Retirados",
    desc: "Situación académica según ciclo y sexo del estudiante.",
    icon: ChartPieIcon,
  },
  {
    id: "104",
    title: "Denominación Carrera",
    desc: "Matrícula y retiro según especialidad técnica.",
    icon: AcademicCapIcon,
  },
  {
    id: "201",
    title: "Matrícula por Edad",
    desc: "Distribución demográfica por rangos de edad y sexo.",
    icon: UserGroupIcon,
  },
  {
    id: "202",
    title: "Opciones Técnico Pro.",
    desc: "Reporte basado en las opciones técnico productivas.",
    icon: AdjustmentsHorizontalIcon,
  },
  {
    id: "203",
    title: "Nivel Educativo",
    desc: "Reporte por grado de instrucción de los participantes.",
    icon: BuildingLibraryIcon,
  },
  {
    id: "205",
    title: "Secciones por Turno",
    desc: "Consolidado de secciones por ciclo y horario.",
    icon: ClipboardDocumentListIcon,
  },
];

const componenteActual = computed(() => {
  switch (reporteActivo.value) {
    case "101":
      return E101;
    case "104":
      return E104;
    case "201":
      return E201;
    case "202":
      return E202;
    case "203":
      return E203;
    case "205":
      return E205;
    default:
      return null;
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
