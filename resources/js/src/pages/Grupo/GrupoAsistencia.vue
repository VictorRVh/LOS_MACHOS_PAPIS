<script setup>
import { ref, watch, computed } from 'vue'
import useSesionStore from "../../store/Sesion/useSesionStore"
import useProgramacionStore from "../../store/Sesion/useProgramacionDocenteStore"
import BaseCalendar from '@/components/ui/FullCalendar.vue'
import Table from '@/components/table/Table.vue'
import THead from '@/components/table/THead.vue'
import TBody from '@/components/table/TBody.vue'
import Tr from '@/components/table/Tr.vue'
import Th from '@/components/table/Th.vue'
import Td from '@/components/table/Td.vue'
import useModalToast from "../../composables/useModalToast";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const sesionStore = useSesionStore()
const programacionSesion = useProgramacionStore()
const { showToast } = useModalToast()

const allEvents = ref([])
const holidays = ref([])

// 🔹 Cargar sesión y programaciones
if (!sesionStore?.sesion?.length) {
  await sesionStore.loadSesion(props.id)
}
if (!programacionSesion?.sesiones?.length) {
  await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
}

// 🔹 Generar eventos para el calendario
watch(
  () => programacionSesion.sesiones,
  (nuevasSesiones) => {
    if (Array.isArray(nuevasSesiones) && nuevasSesiones.length) {
      const eventos = []

      nuevasSesiones.forEach((sesion) => {
        // Asegurar que calendario_admin siempre sea un array
        const dias = (sesion?.calendario_admin ?? [])
          .filter(d => d.laborable === 0)
          .map(d => d.fecha)
          .sort()

        if (dias.length) {
          let inicio = dias[0]
          let fin = dias[0]

          for (let i = 1; i <= dias.length; i++) {
            const actual = dias[i]
            const anterior = dias[i - 1]
            const diff =
              actual &&
              (new Date(actual) - new Date(anterior)) / (1000 * 60 * 60 * 24)

            if (!actual || diff !== 1) {
              eventos.push({
                id: `${sesion.id}-${inicio}`,
                title: sesion.nombre_sesion,
                start: inicio,
                end: new Date(new Date(fin).getTime() + 86400000)
                  .toISOString()
                  .split('T')[0],
                allDay: true,
                backgroundColor:
                  sesion.status === 0
                    ? '#facc15'
                    : sesion.status === 1
                      ? '#22c55e'
                      : '#3b82f6',
                borderColor: '#fff',
                extendedProps: {
                  descripcion: sesion.descripcion,
                  idSesion: sesion.id,
                },
              })

              inicio = actual
              fin = actual
            } else {
              fin = actual
            }
          }
        }
      })

      allEvents.value = eventos
    } else {
      allEvents.value = []
    }
  },
  { deep: true, immediate: true }
)


// 🔸 Estado de la sesión
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
        Programación de Sesión (Vista de Administración)
      </h3>
      <p class="text-sm text-gray-700 dark:text-gray-300">
        Del
        <strong>
          {{ new Date(sesionStore?.sesion?.fecha_inicio).toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long',
            year:'numeric' }) }}
        </strong>
        al
        <strong>
          {{ new Date(sesionStore?.sesion?.fecha_fin).toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long',
            year:'numeric' }) }}
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
    <!-- 📅 CALENDARIO SOLO VISUAL -->
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow calendar-container">
      <div class="md:flex justify-between items-center">
        <div>
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Calendario de Sesiones</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Vista solo lectura — sin edición</p>
        </div>
      </div>

      <BaseCalendar :events="allEvents" :holidays="holidays" :read-only="true" :idEntrega="sesionStore?.sesion?.id" />
    </div>

    <!-- 📘 LISTA DE SESIONES -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <Table>
        <THead>
          <Th>Tema</Th>
          <Th>Fechas</Th>
        </THead>

        <TBody>
          <Tr v-if="programacionSesion?.sesiones?.length === 0">
            <Td colspan="2" class="text-center text-gray-500">
              Aún no hay bloques programados.
            </Td>
          </Tr>

          <Tr v-for="bloque in programacionSesion?.sesiones" :key="bloque.id">
            <Td class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: bloque.color }"></span>
              {{ bloque.nombre_sesion }}
            </Td>
            <Td class="text-xs">
              {{ bloque?.fecha_inicio }} - {{ bloque?.fecha_fin }}
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>
  </div>
</template>

<style scoped>
.calendar-container {
  max-height: 450px;
  overflow-y: auto;
}
</style>
