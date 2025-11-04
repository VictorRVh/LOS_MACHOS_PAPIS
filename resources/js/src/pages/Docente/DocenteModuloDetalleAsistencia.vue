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

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

// 🧠 Store de sesiones
const sesionStore = useSesionStore()
const programacionSesion = useProgramacionStore();
const datesForSlider = ref([])

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteSesion, deleting } = useHttpRequest("/programacion_sesion_docente");

// 🔹 Cargar la sesión una sola vez
if (!sesionStore?.sesion?.length) {
  await sesionStore.loadSesion(props.id)
}

if (!programacionSesion?.sesiones?.length) {
  await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
}

// 🗓️ Calendario

const holidays = ref([])
const selectionEvents = ref([])
const allEvents = ref([])

watch(
  () => programacionSesion.sesiones,
  (nuevasSesiones) => {
    if (Array.isArray(nuevasSesiones) && nuevasSesiones.length) {
      const eventos = []

      nuevasSesiones.forEach((sesion) => {
        const dias = sesion.calendario_admin
          .filter(d => d.laborable === 1)
          .map(d => d.fecha)
          .sort() // ordenar por fecha

        if (dias.length) {
          let inicio = dias[0]
          let fin = dias[0]

          for (let i = 1; i <= dias.length; i++) {
            const actual = dias[i]
            const anterior = dias[i - 1]

            // si el día actual no es consecutivo al anterior, cerramos el tramo
            const diff =
              actual &&
              (new Date(actual) - new Date(anterior)) / (1000 * 60 * 60 * 24)

            if (!actual || diff !== 1) {
              eventos.push({
                id: sesion.id + '-' + inicio,
                title: sesion.nombre_sesion,
                start: inicio,
                end: new Date(new Date(fin).getTime() + 86400000) // +1 día porque FullCalendar trata el end como exclusivo
                  .toISOString()
                  .split('T')[0],
                allDay: true,
                backgroundColor:
                  sesion.status === 0
                    ? '#facc15' // amarillo pendiente
                    : sesion.status === 1
                      ? '#22c55e' // verde activo
                      : '#3b82f6', // azul finalizado
                borderColor: '#fff',
                extendedProps: {
                  descripcion: sesion.descripcion,
                  idSesion: sesion.id,
                },
              })

              // iniciar nuevo tramo
              inicio = actual
              fin = actual
            } else {
              // seguimos en el mismo tramo
              fin = actual
            }
          }
        }
      })

      allEvents.value = eventos
      console.log('📅 Eventos agrupados:', allEvents.value)
    } else {
      allEvents.value = []
    }
  },
  { deep: true, immediate: true }
)

// 🧭 Manejo de selección

const selectedDates = computed(() => selectionEvents.value.map(e => e.start).sort())
const hasSelection = computed(() => selectedDates.value.length > 0)


const handleDateClick = ({ dateStr, date }) => {
  const isWeekend = date.getDay() === 0 || date.getDay() === 6;
  if (isWeekend) return;

  // ⛔ Evitar seleccionar fechas que ya están programadas
  const isAlreadyScheduled = allEvents.value.some(event =>
    dateStr >= event.start && dateStr < event.end
  );

  if (isAlreadyScheduled) {
    showToast("Esta fecha ya está programada en una sesión.");
    return;
  }

  // ✅ Alternar selección normalmente
  const index = selectionEvents.value.findIndex(e => e.start === dateStr);
  if (index >= 0) {
    selectionEvents.value.splice(index, 1);
  } else {
    selectionEvents.value.push({
      start: dateStr,
      display: 'background',
      color: 'rgba(51,139,191,0.4)',
    });
  }
};


// 👇 Agrega después de los handlers de selección

// Limpia la selección visual del calendario

const clearSelection = () => {
  selectionEvents.value = [];
  datesForSlider.value = [];

  // 🔄 Si tienes referencia al calendario, forzamos el repaint
  const calendar = document.querySelector('.fc');
  if (calendar) {
    calendar.dispatchEvent(new Event('refresh')); // opcional según tu implementación
  }
};


// Abre el slider en modo CREACIÓN (nuevo bloque)
const openSessionForm = () => {
  if (!hasSelection.value) return; // solo si hay fechas seleccionadas

  datesForSlider.value = [...selectedDates.value]; // copiar fechas
  sliderData.value = null; // indicamos que NO estamos editando
  showSlider(true); // mostramos el slider
};


const confirmDelete = (bloque) => {
  if (deleting.value) return;

  showConfirmModal(
    `¿Estás seguro de que deseas eliminar el bloque "${bloque.nombre_sesion}"?`,
    async (confirmed) => {
      if (!confirmed) return

      // llama a tu función DELETE
      const wasDeleted = await deleteSesion(bloque.id)

      if (wasDeleted) {
        showToast(`"${bloque.nombre_sesion}" eliminado correctamente.`)
        await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
      }
    }
  )
}

// 🔸 estado de sesión
const estadoTexto = computed(() => {
  if (!sesionStore?.sesion) return 'Sin programación'
  switch (sesionStore?.sesion?.estado) {
    case 0: return 'Pendiente'
    case 1: return 'En curso'
    case 2: return 'Finalizada'
    default: return 'Desconocido'
  }
})
</script>

<template>
  <div v-if="sesionStore?.sesion"
    class="col-span-full bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-xl p-2 px-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
    <div>
      <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
        Programación de Sesión
      </h3>
      <p class="text-sm text-gray-700 dark:text-gray-300">
        Del
        <strong>
          {{ new Date(sesionStore?.sesion?.fecha_inicio).toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long', year:
              'numeric'
          }) }}
        </strong>
        al
        <strong>
          {{ new Date(sesionStore?.sesion?.fecha_fin).toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long', year:
              'numeric'
          }) }}
        </strong>
      </p>
    </div>

    <div class="px-3 py-1 rounded-full text-sm font-bold" :class="{
      'bg-yellow-100 text-yellow-800': sesionStore?.sesion?.estado === 0,
      'bg-green-100 text-green-800': sesionStore?.sesion?.estado === 1,
      'bg-gray-200 text-gray-800': sesionStore?.sesion?.estado === 2
    }">
      Estado: {{ estadoTexto }}
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-5 gap-2">
    <!-- 🧾 INFORMACIÓN DE PROGRAMACIÓN DE SESIÓN -->


    <!-- 📅 CALENDARIO -->
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow calendar-container">

      <div class="md:flex justify-between items-center ">

        <div>
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Programador de Sesiones</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Haga clic en los días para seleccionar.</p>
        </div>

        <div v-if="hasSelection" class="mt-4 md:mt-0 flex gap-2 justify-end">
          <button @click="clearSelection"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors">
            Limpiar
          </button>
          <button @click="openSessionForm"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors">
            Guardar {{ selectedDates.length }} sesión(es)
          </button>
        </div>

      </div>

      <BaseCalendar :events="allEvents" :holidays="holidays" @date-click="handleDateClick"
        @event-click="handleEventClick" :idEntrega="sesionStore?.sesion?.id" />
    </div>

    <!-- 📘 BLOQUES DE SESIONES -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">

      <Table>
        <THead>
          <Th>Tema</Th>
          <Th>Fechas</Th>
          <Th>Acciones</Th>
        </THead>

        <TBody>
          <Tr v-if="programacionSesion?.sesiones?.length === 0">
            <Td colspan="3" class="text-center text-gray-500">
              Aún no hay bloques programados.
            </Td>
          </Tr>

          <Tr v-for="bloque in programacionSesion?.sesiones" :key="bloque.id">
            <Td class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: bloque.color }"></span>
              {{ bloque.nombre_sesion }}
            </Td>
            <Td class="text-xs">
              {{ bloque?.fecha_inicio }}
              -
              {{ bloque?.fecha_fin }}
            </Td>
            <Td class="flex gap-2">
              <DeleteButton @click="confirmDelete(bloque)" />
              <EditButton @click="showSlider(true, bloque)" />
            </Td>
          </Tr>
        </TBody>
      </Table>

    </div>

    <SesionSlider :show="slider" :blockToEdit="sliderData ?? null" :idGrupo="id" :sesion="sesionStore?.sesion"
      @hide="hideSlider" @save="handleSaveSesion" @clear-selection="clearSelection"
      :fechas-seleccionadas="datesForSlider" />

  </div>
</template>

<style scoped>
.calendar-container {
  /* ajusta según necesidad */
  max-height: 450px;
  overflow-y: auto;
}
</style>
