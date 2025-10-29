<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { v4 as uuidv4 } from 'uuid'
import useHttpRequest from '@/composables/useHttpRequest'
import useSesiones from '@/composables/sesiones/useSesiones'
import useCalendarEvents from '@/composables/sesiones/useCalendarEvents'

import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";
import useSesionStore from "../../store/Sesion/useSesionStore";

import BaseCalendar from '@/components/ui/FullCalendar.vue'
import SesionSlider from '@/components/page/SesionesDocente/SesionSlider.vue'
import Table from '@/components/table/Table.vue'
import THead from '@/components/table/THead.vue'
import TBody from '@/components/table/TBody.vue'
import Tr from '@/components/table/Tr.vue'
import Th from '@/components/table/Th.vue'
import Td from '@/components/table/Td.vue'


const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const capacidadStore = useCapacidadTerminalStore();
const sesionStore = useSesionStore();

if (!capacidadStore.capacidadTerminal?.length)
  await capacidadStore.loadCapacidadTerminal(props.id);

if (!sesionStore.sesion?.length)
  await sesionStore.loadSesion(props.id);


const { bloquesDeSesiones, saveSesion, deleteSesionById } = useSesiones()
const { index: fetchHolidays } = useHttpRequest('/api/google-holidays')

const holidays = ref([])
const selectionEvents = ref([])
const allEvents = ref([])

const showForm = ref(false)
const editingBlockId = ref(null)
const datesForSlider = ref([])


const selectedDates = computed(() =>
  selectionEvents.value.map(e => e.start).sort()
)
const hasSelection = computed(() => selectedDates.value.length > 0)


const { buildAllEvents } = useCalendarEvents(bloquesDeSesiones, selectionEvents, holidays)

watch([bloquesDeSesiones, selectionEvents, holidays], () => {
  allEvents.value = buildAllEvents()
}, { deep: true })


const handleDateClick = ({ dateStr, date }) => {
  const isWeekend = date.getDay() === 0 || date.getDay() === 6
  if (isWeekend) return

  const index = selectionEvents.value.findIndex(e => e.start === dateStr)
  if (index >= 0) {
    selectionEvents.value.splice(index, 1)
  } else {
    selectionEvents.value.push({
      start: dateStr,
      display: 'background',
      color: 'rgba(51,139,191,0.4)',
    })
  }
}

const handleEventClick = (info) => {
  console.log('Evento seleccionado:', info.event)
}

const clearSelection = () => (selectionEvents.value = [])

const openSessionForm = () => {
  if (!hasSelection.value) return
  editingBlockId.value = null
  datesForSlider.value = [...selectedDates.value]
  showForm.value = true
}

const startEditing = (id) => {
  const bloque = bloquesDeSesiones.value.find(b => b.id === id)
  if (!bloque) return
  editingBlockId.value = id
  datesForSlider.value = [...bloque.dates]
  showForm.value = true
}

const deleteBlock = async (id) => {
  await deleteSesionById(id)
}

const closeSlider = () => {
  showForm.value = false
  clearSelection()
}

const handleSaveSesion = async (formData) => {
  await saveSesion(formData, editingBlockId.value)
  closeSlider()
}

/* ───────────────────────────────
 * 🚀 Lifecycle
 * ─────────────────────────────── */
onMounted(async () => {
  const data = await fetchHolidays()
  if (data?.items) {
    holidays.value = data.items.map(i => ({
      date: i.start.date,
      title: i.summary,
    }))
  }
  allEvents.value = buildAllEvents()
})
</script>

<template>
  <div class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- 📅 CALENDARIO -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <div class="md:flex justify-between items-center mb-4">
        <div>
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Programador de Sesiones</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Haga clic en los días para seleccionar.</p>
        </div>

        <div v-if="hasSelection" class="mt-4 md:mt-0 flex gap-2 justify-end">
          <button
            @click="clearSelection"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors"
          >
            Limpiar
          </button>
          <button
            @click="openSessionForm"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors"
          >
            Guardar {{ selectedDates.length }} sesión(es)
          </button>
        </div>
      </div>

      <BaseCalendar
        :events="allEvents"
        :holidays="holidays"
        @date-click="handleDateClick"
        @event-click="handleEventClick"
      />
    </div>

    <!-- 📘 BLOQUES DE SESIONES -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 h-fit">
      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">
        Bloques de Sesiones
      </h2>

      <Table>
        <THead>
          <Th>Tema</Th>
          <Th>Fechas</Th>
          <Th>Acciones</Th>
        </THead>

        <TBody>
          <Tr v-if="bloquesDeSesiones.length === 0">
            <Td colspan="3" class="text-center text-gray-500">
              Aún no hay bloques programados.
            </Td>
          </Tr>

          <Tr v-for="bloque in bloquesDeSesiones" :key="bloque.id">
            <Td class="flex items-center gap-2">
              <span
                class="w-3 h-3 rounded-full"
                :style="{ backgroundColor: bloque.color }"
              ></span>
              {{ bloque.title }}
            </Td>

            <Td class="text-xs">
              {{ new Date(bloque.dates[0] + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }) }}
              -
              {{ new Date(bloque.dates[bloque.dates.length - 1] + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }) }}
            </Td>

            <Td class="flex gap-2">
              <button
                @click="startEditing(bloque.id)"
                class="text-blue-500 hover:text-blue-700"
              >
                Editar
              </button>
              <button
                @click="deleteBlock(bloque.id)"
                class="text-red-500 hover:text-red-700"
              >
                Borrar
              </button>
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>

    <!-- 🪟 SLIDER DE SESIÓN -->
    <SesionSlider
      :show="showForm"
      :idGrupo="id"
      :fechas-seleccionadas="datesForSlider"
      :block-to-edit="bloquesDeSesiones.find(b => b.id === editingBlockId)"
      @hide="closeSlider"
      @save="handleSaveSesion"
    />
  </div>
</template>
