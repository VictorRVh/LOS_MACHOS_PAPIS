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

const emit = defineEmits(['date-click', 'event-click'])

const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: esLocale,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek',
  },
  buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
  dateClick: (info) => emit('date-click', info),
  eventClick: (info) => emit('event-click', info),
  events: props.events,
})

watch(() => props.events, (newEvents) => {
  calendarOptions.value.events = newEvents
}, { deep: true })

onMounted(() => {
  calendarOptions.value.events = props.events
})
</script>

<template>
  <div class="base-calendar">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<style scoped>
.fc-day-sun,
.fc-day-sat {
  background-color: #f5f5f5 !important;
}
.dark .fc-day-sun,
.dark .fc-day-sat {
  background-color: #2d3748 !important;
}
.gcal-event {
  background-color: #6b7280 !important;
  border-color: #4b5563 !important;
  color: white !important;
  cursor: not-allowed;
  font-weight: bold;
}
.fc-day-today {
  background-color: rgba(0, 111, 159, 0.1) !important;
}
.dark .fc-day-today {
  background-color: rgba(51, 139, 191, 0.15) !important;
}
.fc-event {
  cursor: pointer;
}
</style>
