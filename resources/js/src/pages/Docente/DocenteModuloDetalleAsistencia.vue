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
    showToast("Selecciona una capacidad terminal primero.", "warning");
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
  (nuevasCapacidades) => {
    if (!Array.isArray(nuevasCapacidades) || !nuevasCapacidades.length) {
      allEvents.value = []
      return
    }

    const eventos = []

    nuevasCapacidades.forEach((cap) => {
      if (!Array.isArray(cap.sesiones)) return

      cap.sesiones.forEach((sesion) => {
        const dias = (sesion.calendario_admin ?? [])
          .map(d => d.fecha)
          .filter(Boolean)
          .sort()

        if (!dias.length) return

        let inicio = dias[0]
        let fin = dias[0]

        for (let i = 1; i <= dias.length; i++) {
          const actual = dias[i]
          const anterior = dias[i - 1]

          const diff =
            actual &&
            (new Date(actual) - new Date(anterior)) / (1000 * 60 * 60 * 24)

          if (!actual || diff !== 1) {
            // cerrar tramo
            eventos.push({
              id: `${sesion.id}-${inicio}`,
              title: sesion.nombre_sesion,
              start: inicio,
              end: new Date(new Date(fin).getTime() + 86400000)
                .toISOString()
                .split("T")[0],
              allDay: true,
              backgroundColor:
                sesion.status === 0
                  ? '#facc15' // pendiente
                  : sesion.status === 1
                    ? '#22c55e' // activo
                    : '#3b82f6', // finalizado
              borderColor: '#fff',
              extendedProps: {
                descripcion: sesion.descripcion,
                idSesion: sesion.id,
                idCapacidad: cap.id,
                nombreCapacidad: cap.nombre_capacidad
              }
            })

            // iniciar tramo nuevo
            inicio = actual
            fin = actual
          } else {
            // seguimos tramo actual
            fin = actual
          }
        }
      })
    })

    allEvents.value = eventos
  },
  { deep: true, immediate: true }
)


// 🧭 Manejo de selección

const selectedDates = computed(() => selectionEvents.value.map(e => e.start).sort())
const hasSelection = computed(() => selectedDates.value.length > 0)

watch(
  selectedDates,
  (nuevasFechas) => {
    // 🔄 Mantener sincronizadas las fechas para el slider
    datesForSlider.value = [...nuevasFechas];
  },
  { immediate: true }
);


const handleDateClick = ({ dateStr, date }) => {
  const isWeekend = date.getDay() === 0 || date.getDay() === 6;
  if (isWeekend) return;

  // ⛔ Evitar seleccionar fechas ya programadas en otros bloques
  const isAlreadyScheduled = allEvents.value.some(event =>
    dateStr >= event.start && dateStr < event.end &&
    (!isEditing.value || event.extendedProps.idSesion !== sliderData.value?.id)
  );

  if (isAlreadyScheduled) {
    showToast("Esta fecha ya está programada en otra sesión.");
    return;
  }

  // ✅ Alternar selección
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

const verSesion = (bloque) =>{
  
}
const isEditing = ref(false)

// aumentado para edicion 
const handleEdit = (bloque) => {
  clearSelection() // limpia selección anterior
  isEditing.value = true // modo edición

  // ✅ Marcar las fechas del bloque en el calendario
  const fechas = bloque.calendario_admin.map(d => d.fecha)
  selectionEvents.value = fechas.map(f => ({
    start: f,
    display: 'background',
    color: 'rgba(51,139,191,0.9)' // amarillo suave
  }))

  datesForSlider.value = [...fechas] // para el slider
  // console.log("antes: ",sliderData.value)
  sliderData.value = bloque // pasamos el bloque al modal
  // console.log("despues: ",sliderData.value)
}
const cancelEdit = () => {
  isEditing.value = false
  clearSelection()
  sliderData.value = null
}

const updateSession = () => {
  if (!datesForSlider.value.length) return
  // console.log("antes: ",sliderData.value)
  showSlider(true, sliderData.value) // abre el modal con datos y fechas
  // console.log("despues: ",sliderData.value)
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

// collapse
const openCapacidades = ref(new Set())

const toggleCapacidad = (id) => {
  if (openCapacidades.value.has(id)) {
    openCapacidades.value.delete(id)
  } else {
    openCapacidades.value.add(id)
  }
}


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

    <!-- 🔵 Botón de asistencia -->
    <BaseButton title="Asistencia" @click="Asistencia"
      class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow" />

  </div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-2">
    <!-- 📅 CALENDARIO -->
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow calendar-container">
      <!-- Encabezado fijo -->
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

          <!-- 🔵 Botones de acción (limpiar / guardar) -->
          <div class="mt-4 md:mt-0 flex gap-2 justify-end">
            <template v-if="isEditing">
              <BaseButton title="Cancelar" variant="secondary" @click="cancelEdit" />
              <BaseButton :title="`Actualizar ${selectedDates.length} sesines`" variant="primary"
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

      <!-- Calendario con scroll -->
      <div class="calendar-scroll">
        <BaseCalendar :events="[...allEvents, ...selectionEvents]" :holidays="holidays" @date-click="handleDateClick"
          @event-click="handleEventClick" :idEntrega="sesionStore?.sesion?.id" />
      </div>
    </div>

    <!-- 📘 BLOQUES DE SESIONES -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <Table>
        <THead class="hidden">
          <Th>N°</Th>
          <Th>Módulo</Th>
          <Th>días</Th>
          <Th>acción</Th>
        </THead>

        <TBody>
          <template v-for="capacidad in programacionSesion?.sesiones" :key="capacidad.id">

            <!-- CABECERA DE CAPACIDAD -->
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

            <!-- SESIONES DE ESA CAPACIDAD -->
            <tr v-if="openCapacidades.has(capacidad.id)" class="bg-white dark:bg-gray-800">
              <td colspan="8" class="p-0">
                <TransitionGroup name="list" tag="table" class="w-full">

            <Tr v-for="(sesion, index) in capacidad.sesiones" :key="sesion.id" class="border-t-0">
              <!-- Nº -->
              <Td class="text-center w-12">{{ index + 1 }}</Td>

              <!-- Título + Fechas -->
              <Td>
                <div class="flex items-center gap-2 font-medium">
                  <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: '#22c55e' }"></span>
                  {{ sesion.nombre_sesion }}
                </div>

                <!-- Fechas con opacidad -->
                <div class="text-xs opacity-60 mt-1 ml-5">
                  {{ sesion.fecha_inicio }} - {{ sesion.fecha_fin }}
                </div>
              </Td>

              <!-- Días -->
              <Td class="text-xs text-gray-500">
                {{ sesion.calendario_admin.length }} días
              </Td>

              <!-- Acciones -->
              <Td class="text-center text-gray-600 dark:text-gray-200">
                <MenuTable :actions="{ view: true, edit: true, delete: true }" @view="verSesion(sesion)"
                  @edit="handleEdit(sesion)" @delete="confirmDelete(sesion)" entity-label="sesión" />
              </Td>
            </Tr>

            </TransitionGroup>
            </td>
            </tr>

          </template>
        </TBody>
      </Table>


    </div>

    <!-- 🟢 Sliders -->
    <TomarAsistencia :show="asist" :grupo-id="id" :sesion-id="sesionStore?.sesion?.id" @hide="ocultarSliderAsistencia"
      @save="clearSelection" />

    <SesionSlider :show="slider" :blockToEdit="sliderData ?? null" :idGrupo="id" :sesion="sesionStore?.sesion"
      @hide="hideSlider" @save="handleSaveSesion" @clear-selection="clearSelection"
      :fechas-seleccionadas="datesForSlider" />
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