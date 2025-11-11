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

    const asist = ref(false); // Estado para mostrar / ocultar slider
    const asistData = ref(null); // Datos que se pasan al slider           
 
// 🔹 Cargar la sesión una sola vez
if (!sesionStore?.sesion?.length) {
  await sesionStore.loadSesion(props.id)
}

if (!programacionSesion?.sesiones?.length) {
  await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
}

// 🗓️ Calendario
 const Asistencia = () => {
          if (sesionStore?.sesion?.id) {
              //sliderData.value = capacidadSeleccionada.value; // Asignar datos
              asist.value = true; // Mostrar slider
          } else {
              console.error("Selecciona una capacidad terminal primero.");
              showToast("Selecciona una capacidad terminal primero.","warning");
          }
    };         
const ocultarSliderAsistencia = () => {
  asist.value = false;
};    
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
  <div class="space-y-4">
    <!-- ENCABEZADO PRINCIPAL -->
    <div v-if="sesionStore?.sesion"
      class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Programación de Sesión</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Del <strong>{{ new Date(sesionStore.sesion.fecha_inicio).toLocaleDateString('es-PE', { day: '2-digit', month: 'long' }) }}</strong> al <strong>{{ new Date(sesionStore.sesion.fecha_fin).toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' }) }}</strong>
        </p>
      </div>
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <span class="px-3 py-1 rounded-full text-xs font-bold w-full text-center sm:w-auto" :class="{
          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': sesionStore.sesion.estado === 0,
          'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': sesionStore.sesion.estado === 1,
          'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300': sesionStore.sesion.estado === 2
        }">
          Estado: {{ estadoTexto }}
        </span>
        <BaseButton :title="'Asistencia'" @click="Asistencia" class="w-full sm:w-auto" />
      </div>
    </div>

    <!-- CUERPO PRINCIPAL: CALENDARIO Y TEMAS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      
      <!-- COLUMNA DEL CALENDARIO -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md flex flex-col h-[75vh]">
        <header class="p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
          <h3 class="text-lg font-bold text-gray-800 dark:text-gray-900">Programador de Sesiones</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Haz clic en los días laborables para programar un tema.</p>
        </header>

        <div class="flex-grow overflow-hidden relative">
          <BaseCalendar 
            :events="[...allEvents, ...selectionEvents]" 
            :holidays="holidays" 
            @date-click="handleDateClick"
            class="h-full"
          >
            <!-- Slot para la cabecera del calendario con botones de acción -->
            <template #header="{ calendarApi }">
              <div class="w-full flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 p-2 z-10">
                <div class="flex items-center gap-1">
                  <button @click="calendarApi.prev()" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">&lt;</button>
                  <button @click="calendarApi.next()" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">&gt;</button>
                  <button @click="calendarApi.today()" class="px-3 py-1.5 text-sm rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Hoy</button>
                </div>
                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ calendarApi.getCurrentData().viewTitle }}</h4>
                <div class="flex items-center gap-1">
                  <button @click="calendarApi.changeView('dayGridMonth')" class="px-3 py-1.5 text-sm rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Mes</button>
                  <button @click="calendarApi.changeView('timeGridWeek')" class="px-3 py-1.5 text-sm rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">Semana</button>
                </div>
              </div>
            </template>
          </BaseCalendar>

          <!-- Botones flotantes para guardar/limpiar selección -->
          <Transition name="fade">
            <div v-if="hasSelection" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-3 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm p-2 rounded-lg shadow-xl border dark:border-gray-700">
              <button @click="clearSelection" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition-colors">
                Limpiar ({{ selectedDates.length }})
              </button>
              <button @click="openSessionForm" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors">
                Crear Tema
              </button>
            </div>
          </Transition>
        </div>
      </div>

      <!-- COLUMNA DE TEMAS PROGRAMADOS -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md flex flex-col h-[75vh]">
        <header class="p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
          <h3 class="text-lg font-bold text-gray-800 dark:text-gray-900">Temas Programados</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Lista de los temas y sus fechas.</p>
        </header>

        <div class="flex-grow overflow-y-auto">
          <Table>
            <THead class="sticky top-0 bg-gray-50 dark:bg-gray-700 z-10">
              <Th>Tema</Th>
              <Th>Fechas</Th>
              <Th class="text-right">Acciones</Th>
            </THead>
            <TBody>
              <Tr v-if="programacionSesion?.sesiones?.length === 0">
                <Td colspan="3" class="text-center text-gray-500 py-10">
                  Aún no hay temas programados.
                </Td>
              </Tr>
              <Tr v-for="bloque in programacionSesion?.sesiones" :key="bloque.id">
                <Td class="font-semibold">{{ bloque.nombre_sesion }}</Td>
                <Td class="text-xs text-gray-500 dark:text-gray-400">
                  {{ bloque.fecha_inicio }} al {{ bloque.fecha_fin }}
                </Td>
                <Td class="flex gap-2 justify-end">
                  <DeleteButton @click="confirmDelete(bloque)" />
                  <EditButton @click="showSlider(true, bloque)" />
                </Td>
              </Tr>
            </TBody>
          </Table>
        </div>
      </div>
    </div>

    <!-- MODALS Y SLIDERS (NO VISIBLES INICIALMENTE) -->
    <TomarAsistencia 
      :show="asist"
      :grupo-id="id"
      :sesion-id="sesionStore?.sesion?.id"  
      @hide="ocultarSliderAsistencia"      
    />
        
    <SesionSlider :show="slider" :blockToEdit="sliderData ?? null" :idGrupo="id" :sesion="sesionStore?.sesion"
      @hide="hideSlider" @clear-selection="clearSelection"
      :fechas-seleccionadas="datesForSlider" />
  </div>
</template>

<style>
/* Estilos para el contenedor del calendario y su cabecera sticky */
.fc {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.fc-header-toolbar {
  flex-shrink: 0;
  position: sticky;
  top: 0;
  background: inherit; /* Hereda el fondo del padre */
  z-index: 10;
}
.fc-view-harness {
  flex-grow: 1;
  overflow-y: auto;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>