<script setup>
import { ref, watch, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'

const props = defineProps({
  events: { type: Array, default: () => [] },
  selectable: { type: Boolean, default: true },
})

const emit = defineEmits(['date-click', 'event-click', 'selection-change'])

const selectedDates = ref([])

const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: esLocale,
  height: 500,
  stickyHeaderDates: true, // ✅ hace que “lun, mar, mié…” queden fijos
  expandRows: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek',
  },
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana',
  },

  dateClick: (info) => {
    // const day = info.date.getDay()
    // if (day === 0 || day === 6) return

    const dateStr = info.dateStr
    const index = selectedDates.value.findIndex(d => d.start === dateStr)

    if (index >= 0) {
      selectedDates.value.splice(index, 1)
    } else {
      selectedDates.value.push({
        id: `select-${dateStr}`,
        start: dateStr,
        allDay: true,
        display: 'background',
        backgroundColor: 'rgba(37,99,235,0.5)',
        borderColor: '#2563eb',
      })
    }

    emit('date-click', info)
    emit('selection-change', selectedDates.value.map(d => d.start))
  },

  eventClick: (info) => emit('event-click', info),
  events: props.events,
})

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
  <div class="calendar-container">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<style scoped>
.calendar-container {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 0.5rem;
}

/* Colores de sábado y domingo */
:deep(.fc-day-sun) {
  background-color: rgba(255, 99, 99, 0.15) !important;
}
:deep(.fc-day-sat) {
  background-color: rgba(72, 187, 120, 0.15) !important;
}

/* Día actual */
:deep(.fc-day-today) {
  background-color: rgba(252, 153, 4, 0.35) !important;
}

/* Eventos */
:deep(.fc-event) {
  cursor: pointer;
}
</style>
