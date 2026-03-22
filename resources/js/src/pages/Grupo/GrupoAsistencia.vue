<script setup>
import { ref, computed, watch } from 'vue'
import { ArrowDownTrayIcon, ChevronDownIcon, ExclamationTriangleIcon } from "@heroicons/vue/24/outline";
import useCalendarEvents from '@/composables/sesiones/useCalendarEvents'
import useSesionStore from "../../store/Sesion/useSesionStore"
import useProgramacionStore from "../../store/Sesion/useProgramacionDocenteStore"
import useModalToast from "../../composables/useModalToast";
import useSlider from "../../composables/useSlider";
import EditButton from "@/components/ui/EditButton.vue";
import DeleteButton from "@/components/ui/DeleteButton.vue";
import useSesiones from '@/composables/sesiones/useSesiones'
import BaseCalendar from '@/components/ui/FullCalendar.vue';
import SesionSlider from '@/components/page/SesionesDocente/SesionSlider.vue'
import useHttpRequest from "../../composables/useHttpRequest";
import Table from '@/components/table/Table.vue';
import THead from '@/components/table/THead.vue';
import TBody from '@/components/table/TBody.vue';
import Tr from '@/components/table/Tr.vue';
import Th from '@/components/table/Th.vue';
import Td from '@/components/table/Td.vue';
import BaseButton from '@/components/ui/Button.vue';
import TomarAsistencia from '../../components/page/SesionesDocente/TomarAsistenciaSlider.vue';
import MenuTable from "../../components/table/MenuTable.vue";
import useExportExcel from "../../composables/sesiones/useExportExcel"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const sesionStore = useSesionStore()
const programacionSesion = useProgramacionStore();
const datesForSlider = ref([])

const { exportarCalendarioExcel } = useExportExcel();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteSesion, deleting } = useHttpRequest("/programacion_sesion_docente");

const asist = ref(false);
const holidays = ref([])
const selectionEvents = ref([])
const allEvents = ref([])
const calendarKey = ref(0)

watch(
  () => props.id,
  async (nuevoId) => {
    if (!nuevoId) return;

    programacionSesion.sesiones = [];
    sesionStore.sesion = null;

    allEvents.value = [];
    selectionEvents.value = [];
    datesForSlider.value = [];
    calendarKey.value++;

    await sesionStore.loadSesion({
      id_grupo: nuevoId,
      tipo_entrega: 2,
    });

    if (sesionStore.sesion?.id) {
      await programacionSesion.loadSesiones(sesionStore.sesion.id);
    }
  },
  { immediate: true }
);

const Asistencia = () => {
  if (sesionStore?.sesion?.id) {
    asist.value = true;
  } else {
    console.error("Selecciona una Unidad Didactica primero.");
    showToast("Selecciona una Unidad Didactica primero.", "warning");
  }
};

const ocultarSliderAsistencia = () => {
  asist.value = false;
};

watch(
  () => programacionSesion.sesiones,
  (capas) => {
    if (!Array.isArray(capas)) {
      allEvents.value = []
      return
    }

    const ev = []

    capas.forEach(cap => {
      if (!cap.sesiones) return

      cap.sesiones.forEach(sesion => {
        const dias = (sesion.calendario_admin ?? [])
          .map(d => d.fecha)
          .sort()

        if (!dias.length) return

        let ini = dias[0]
        let fin = dias[0]

        for (let i = 1; i <= dias.length; i++) {
          const actual = dias[i]
          const anterior = dias[i - 1]

          const diff = actual && (new Date(actual) - new Date(anterior)) / 86400000

          if (!actual || diff !== 1) {
            ev.push({
              id: `${sesion.id}-${ini}`,
              title: sesion.nombre_sesion,
              start: ini,
              end: new Date(new Date(fin).getTime() + 86400000)
                .toISOString()
                .split("T")[0],
              allDay: true,
              backgroundColor: sesion.status === 0 ? '#facc15'
                : sesion.status === 1 ? '#22c55e'
                : '#3b82f6',
              borderColor: "#fff"
            })

            ini = actual
            fin = actual
          } else {
            fin = actual
          }
        }
      })
    })

    allEvents.value = ev
  },
  { deep: true, immediate: true }
)

const selectedDates = computed(() => selectionEvents.value.map(e => e.start).sort())
const hasSelection = computed(() => selectedDates.value.length > 0)

const estadoTexto = computed(() => {
  if (!sesionStore?.sesion) return 'Sin programación'
  switch (sesionStore?.sesion?.estado) {
    case 0: return 'Pendiente'
    case 1: return 'En curso'
    case 2: return 'En curso'
    case 3: return 'En curso'
    case 4: return 'Finalizada'
    default: return 'Desconocido'
  }
})

const openCapacidades = ref(new Set())

const toggleCapacidad = (id) => {
  if (openCapacidades.value.has(id)) {
    openCapacidades.value.delete(id)
  } else {
    openCapacidades.value.add(id)
  }
}

const onSliderHide = () => {
  hideSlider();
  sliderData.value = null;
  isEditing.value = false
  clearSelection()
};
</script>

<template>
  <div v-if="sesionStore?.sesion?.id"
    class="col-span-full border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="min-w-0">
        <h3 class="text-sm font-semibold tracking-wide text-slate-900 dark:text-slate-100">
          Programación de sesiones
        </h3>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
          <strong>
            {{
              new Date(sesionStore?.sesion?.fecha_inicio).toLocaleDateString(
                'es-PE',
                { day: '2-digit', month: 'long', year: 'numeric' }
              )
            }}
          </strong>
          <span class="px-1 text-slate-400 dark:text-slate-500">-</span>
          <strong>
            {{
              new Date(sesionStore?.sesion?.fecha_fin).toLocaleDateString(
                'es-PE',
                { day: '2-digit', month: 'long', year: 'numeric' }
              )
            }}
          </strong>
        </p>
      </div>

      <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end md:w-auto">
        <div class="inline-flex h-6 items-center gap-1.5 self-start rounded-sm border px-2 text-xs font-medium sm:self-auto" :class="{
          'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-700/50 dark:bg-amber-500/10 dark:text-amber-200': sesionStore?.sesion?.estado === 0,
          'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-500/10 dark:text-emerald-200': sesionStore?.sesion?.estado === 1,
          'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-200': sesionStore?.sesion?.estado === 2,
          'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-700/50 dark:bg-sky-500/10 dark:text-sky-200': sesionStore?.sesion?.estado === 3,
          'border-slate-300 bg-slate-50 text-slate-700 dark:border-slate-500 dark:bg-slate-700/50 dark:text-slate-200': sesionStore?.sesion?.estado === 4,
        }">
          <span class="h-2 w-2 rounded-full" :class="{
            'bg-amber-500': sesionStore?.sesion?.estado === 0,
            'bg-emerald-500': sesionStore?.sesion?.estado === 1,
            'bg-slate-400': sesionStore?.sesion?.estado === 2 || sesionStore?.sesion?.estado === 4,
            'bg-sky-500': sesionStore?.sesion?.estado === 3,
          }"></span>
          <span>{{ estadoTexto }}</span>
        </div>

        <button type="button" @click="Asistencia"
          class="inline-flex h-8 items-center justify-center gap-2 rounded-sm border border-emerald-200 bg-white px-3 text-sm font-semibold text-emerald-700 transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 dark:border-emerald-700/60 dark:bg-slate-800 dark:text-emerald-300 dark:hover:bg-emerald-500/10 dark:focus-visible:ring-emerald-700/40">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
          </svg>
          <span>Asistencia</span>
        </button>
      </div>
    </div>
  </div>

  <div v-else
    class="col-span-full border border-amber-200 border-l-[3px] border-l-amber-500 bg-white px-4 py-3 dark:border-amber-700/60 dark:bg-slate-800">
    <div class="flex items-start gap-3">
      <ExclamationTriangleIcon class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-300" />
      <div class="min-w-0">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
          No existe una programación para crear sesiones.
        </h3>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
          Debe existir la programación de sesiones. Solicítala a coordinación.
        </p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-2 lg:grid-cols-5">
    <div class="calendar-container overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800 lg:col-span-3">
      <div class="border-b border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
        <div class="items-center justify-between md:flex">
          <div>
            <h2 class="text-base font-bold text-gray-800 dark:text-gray-200">
              Calendario de sesiones
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Seleccione los días para programar.
            </p>
          </div>

          <div class="mt-3 flex justify-end gap-2 md:mt-0">
            <template v-if="isEditing">
              <BaseButton title="Cancelar" variant="secondary" @click="cancelEdit" />
              <BaseButton :title="`Actualizar ${selectedDates.length} sesiones`" variant="primary"
                @click="updateSession" />
            </template>

            <template v-else-if="hasSelection">
              <BaseButton title="Limpiar" variant="secondary" @click="clearSelection" />
              <BaseButton :title="`Guardar ${selectedDates.length} sesión(es)`" variant="primary"
                @click="openSessionForm" />
            </template>
          </div>
        </div>
      </div>

      <div class="calendar-scroll">
        <BaseCalendar :key="calendarKey" :events="[...allEvents, ...selectionEvents]" :holidays="holidays" height="auto" embedded
          @date-click="handleDateClick" @event-click="handleEventClick" :idEntrega="sesionStore?.sesion?.id" />
      </div>
    </div>

    <div class="rounded-lg bg-white p-3 shadow dark:bg-gray-800 lg:col-span-2">
      <div class="mb-3 flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
        <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">
          Sesiones programadas
        </h3>
        <button type="button" @click="exportarCalendarioExcel(sesionStore, programacionSesion)"
          class="inline-flex h-8 items-center justify-center gap-2 rounded-sm border border-emerald-200 bg-white px-3 text-sm font-semibold text-emerald-700 transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 dark:border-emerald-700/60 dark:bg-slate-800 dark:text-emerald-300 dark:hover:bg-emerald-500/10 dark:focus-visible:ring-emerald-700/40">
          <ArrowDownTrayIcon class="h-4 w-4" />
          <span>Exportar</span>
        </button>
      </div>

      <Table>
        <THead class="hidden">
          <Th>N°</Th>
          <Th>Módulo</Th>
          <Th>días</Th>
          <Th>acción</Th>
        </THead>

        <TBody>
          <template v-for="capacidad in programacionSesion?.sesiones" :key="capacidad.id">
            <tr @click="toggleCapacidad(capacidad.id)" class="cursor-pointer border-b border-white bg-cetpro transition-colors duration-200 hover:bg-cetpro-dark dark:border-cetpro dark:bg-cetpro-dark dark:hover:bg-cetpro">
              <td colspan="8" class="px-4 py-3 text-sm font-bold uppercase tracking-wider">
                <div class="flex items-center justify-between text-cetpro-text">
                  <span>Sesiones {{ capacidad.nombre_capacidad }}</span>

                  <ChevronDownIcon :class="[
                    'h-6 w-6 text-cetpro-text transition-transform duration-300',
                    { 'rotate-180': openCapacidades.has(capacidad.id) }
                  ]" />
                </div>
              </td>
            </tr>

            <tr v-if="openCapacidades.has(capacidad.id)" class="bg-white dark:bg-gray-800">
              <td colspan="8" class="p-0">
                <TransitionGroup name="list" tag="table" class="w-full">
                  <Tr v-for="(sesion, index) in capacidad.sesiones" :key="sesion.id" class="border-t-0">
                    <Td class="w-12 text-center">{{ index + 1 }}</Td>

                    <Td>
                      <div class="flex items-center gap-2 font-medium">
                        <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: '#22c55e' }"></span>
                        {{ sesion.nombre_sesion }}
                      </div>

                      <div class="ml-5 mt-1 text-xs opacity-60">
                        {{ sesion.fecha_inicio }} - {{ sesion.fecha_fin }}
                      </div>
                    </Td>

                    <Td class="text-xs text-gray-500">
                      {{ sesion.calendario_admin.length }} días
                    </Td>
                  </Tr>
                </TransitionGroup>
              </td>
            </tr>
          </template>
        </TBody>
      </Table>
    </div>

    <TomarAsistencia :show="asist" :grupo-id="id" :llamar-asistencia="true" @hide="ocultarSliderAsistencia"
      @save="clearSelection" />

    <SesionSlider :show="slider" :blockToEdit="sliderData ?? null" :idGrupo="id" :sesion="sesionStore?.sesion"
      @hide="onSliderHide" :fechas-seleccionadas="datesForSlider" />
  </div>
</template>

<style scoped>
.calendar-container {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

:deep(.fc) {
  font-size: 0.72rem;
}

:deep(.fc .fc-toolbar.fc-header-toolbar) {
  margin-bottom: 0.25rem;
}

:deep(.fc .fc-toolbar-title) {
  font-size: 0.82rem;
}

:deep(.fc .fc-button) {
  padding: 0.12rem 0.3rem;
  font-size: 0.68rem;
}

:deep(.fc .fc-col-header-cell-cushion) {
  padding: 0.12rem 0;
  font-size: 0.64rem;
}

:deep(.fc .fc-daygrid-day-frame) {
  min-height: 0.88rem;
}

:deep(.fc .fc-daygrid-day-top) {
  padding: 0.02rem 0.12rem 0;
}

:deep(.fc .fc-scrollgrid-section-header > *) {
  min-height: 1rem;
}

:deep(.fc .fc-view-harness),
:deep(.fc .fc-view-harness-active) {
  height: auto !important;
}

:deep(.fc .fc-scroller),
:deep(.fc .fc-scroller-liquid-absolute) {
  overflow: hidden !important;
}

:deep(.fc .fc-scrollgrid),
:deep(.fc .fc-scrollgrid-section-body table),
:deep(.fc .fc-scrollgrid-section-body tbody) {
  height: auto !important;
}
</style>
