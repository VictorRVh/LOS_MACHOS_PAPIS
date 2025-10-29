<script setup>
import { ref, watch, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'

const props = defineProps({
  events: { type: Array, default: () => [] },
  holidays: { type: Array, default: () => [] },
  selectable: { type: Boolean, default: true },
})

const emit = defineEmits(['date-click', 'event-click', 'selection-change'])

// 🔹 Fechas seleccionadas internamente
const selectedDates = ref([])

// 🔹 Opciones base del calendario
const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: esLocale,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek',
  },
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana'
  },

  // ✅ Manejamos el click en día
  dateClick: (info) => {
    const day = info.date.getDay()
    if (day === 0 || day === 6) return // Ignorar fines de semana

    const dateStr = info.dateStr
    const index = selectedDates.value.findIndex(d => d.start === dateStr)

    if (index >= 0) {
      // Deseleccionar
      selectedDates.value.splice(index, 1)
    } else {
      // Seleccionar
      selectedDates.value.push({
        id: `select-${dateStr}`,
        start: dateStr,
        allDay: true,
        display: 'background',
        backgroundColor: 'rgba(37,99,235,0.5)', // azul semitransparente
        borderColor: '#2563eb',
      })
    }

    // 🔹 Mantiene compatibilidad con el padre (botón “Crear sesión”)
    emit('date-click', info)

    // 🔹 Nuevo evento con todas las fechas seleccionadas
    emit('selection-change', selectedDates.value.map(d => d.start))
  },

  eventClick: (info) => emit('event-click', info),
  events: props.events,
})

// 🔄 Combinar eventos externos + seleccionados
watch(
  [() => props.events, selectedDates],
  ([newEvents, selected]) => {
    calendarOptions.value.events = [...newEvents, ...selected]
  },
  { deep: true, immediate: true }
)

onMounted(() => {
  calendarOptions.value.events = [...props.events]
})
</script>

<template>
  <div class="base-calendar">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<style scoped>
/* ✅ Colores personalizados para sábados y domingos */
:deep(.fc-day-sun) {
  background-color: rgba(255, 99, 99, 0.15) !important; /* rojo claro */
}
:deep(.fc-day-sat) {
  background-color: rgba(72, 187, 120, 0.15) !important; /* verde claro */
}

/* 🌑 En modo oscuro */
:deep(.dark .fc-day-sun) {
  background-color: rgba(239, 68, 68, 0.25) !important;
}
:deep(.dark .fc-day-sat) {
  background-color: rgba(34, 197, 94, 0.25) !important;
}

/* Día actual */
:deep(.fc-day-today) {
  background-color: rgba(252, 153, 4, 0.4) !important;
}
:deep(.dark .fc-day-today) {
  background-color: rgba(56, 191, 51, 0.15) !important;
}

/* Eventos */
:deep(.fc-event) {
  cursor: pointer;
}
</style>
