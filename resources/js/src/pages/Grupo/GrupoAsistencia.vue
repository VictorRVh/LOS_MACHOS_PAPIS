<script setup>
import { ref, computed, watch } from 'vue'
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

    // 1. LIMPIAR ANTES DE CARGAR
    programacionSesion.sesiones = [];
    sesionStore.sesion = null;

    allEvents.value = [];
    selectionEvents.value = [];
    datesForSlider.value = [];
    calendarKey.value++; // Rerender inmediato sin los eventos anteriores

    // 2. CARGAR NUEVA DATA
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

// const isFinalizado = computed(() => sesionStore?.sesion?.estado === 4)

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


// ▶ generar eventos de calendario (solo vista)
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



//console.log("dATOS SESION: ", sesionStore?.sesion)
</script>

<template>
  <div v-if="sesionStore?.sesion?.id"
    class="col-span-full bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-xl p-2 px-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
    <div>
      <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
        Programación de Sesiones
      </h3>
      <p class="text-sm text-gray-700 dark:text-gray-300">
        Del
        <strong>
          {{
            new Date(sesionStore?.sesion?.fecha_inicio).toLocaleDateString(
              'es-PE',
              { day: '2-digit', month: 'long', year: 'numeric' }
            )
          }}
        </strong>
        al
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

    <div class="px-3 py-1 rounded-full text-sm font-bold" :class="{
      'bg-yellow-100 text-yellow-800': sesionStore?.sesion?.estado === 0,
      'bg-green-100 text-green-800': sesionStore?.sesion?.estado === 1,
      'bg-gray-200 text-gray-800': sesionStore?.sesion?.estado === 2,
    }">
      Estado: {{ estadoTexto }}
    </div>

    <BaseButton title="Asistencia" @click="Asistencia"
      class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
      <template #icon>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
        </svg>

      </template>
    </BaseButton>

  </div>

  <div v-else
    class="col-span-full bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-xl p-3 flex flex-col">

    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
      No existe una programación para crear sesiones.
    </h3>

    <p class="text-sm text-red-700 dark:text-red-300 mt-1">
      Debe existir la programación de sesiones. Solicítala a coordinación.
    </p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-2">
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow calendar-container">
      <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 p-2 border-b border-gray-200 dark:border-gray-700">
        <div class="md:flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
              Programador de Sesiones
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Haga clic en los días para seleccionar.
            </p>
          </div>

          <div class="mt-4 md:mt-0 flex gap-2 justify-end">
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
        <BaseCalendar :key="calendarKey" :events="[...allEvents, ...selectionEvents]" :holidays="holidays"
          @date-click="handleDateClick" @event-click="handleEventClick" :idEntrega="sesionStore?.sesion?.id" />
      </div>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
          Sesiones Programadas
        </h3>
        <BaseButton title="Exportar" @click="exportarCalendarioExcel(sesionStore, programacionSesion)">
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </template>
        </BaseButton>

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

            <tr @click="toggleCapacidad(capacidad.id)" class="bg-cetpro dark:bg-cetpro-dark hover:bg-cetpro-dark dark:hover:bg-cetpro cursor-pointer
               transition-colors duration-200 border-b border-white dark:border-cetpro">
              <td colspan="8" class="px-4 py-3 font-bold uppercase tracking-wider text-sm">
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
              <Td class="text-center w-12">{{ index + 1 }}</Td>

              <Td>
                <div class="flex items-center gap-2 font-medium">
                  <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: '#22c55e' }"></span>
                  {{ sesion.nombre_sesion }}
                </div>

                <div class="text-xs opacity-60 mt-1 ml-5">
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

    <TomarAsistencia :show="asist" :grupo-id="id" :llamar-asistencia="true"  @hide="ocultarSliderAsistencia"
      @save="clearSelection" />

    <SesionSlider :show="slider" :blockToEdit="sliderData ?? null" :idGrupo="id" :sesion="sesionStore?.sesion"
      @hide="onSliderHide" :fechas-seleccionadas="datesForSlider" />
  </div>
</template>
<style scoped>
.calendar-container {
  /* ajusta según necesidad */
  max-height: 450px;

}

.calendar-container {
  display: flex;
  flex-direction: column;
  height: 450px;
  /* puedes ajustar según el espacio total */
}

/* Encabezado fijo */
.sticky {
  position: sticky;
  top: 0;
}
</style>