<script setup>
import { ref, computed, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import useHttpRequest from '../../composables/useHttpRequest';
import { v4 as uuidv4 } from 'uuid';

import SesionSlider from '../../components/page/SesionesDocente/SesionSlider.vue';
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";

const bloquesDeSesiones = ref([]);
const selectionEvents = ref([]);
const holidays = ref([]);
const showForm = ref(false);
const editingBlockId = ref(null);
let colorIndex = 0;
const eventColors = ['#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#3b82f6'];

const { index: fetchHolidays } = useHttpRequest('/google-holidays');

const selectedDates = computed(() => selectionEvents.value.map(e => e.start).sort());
const hasSelection = computed(() => selectedDates.value.length > 0);

const allEvents = computed(() => {
    const holidayEvents = holidays.value.map(h => ({
        title: h.title, start: h.date, allDay: true, color: '#6b7280', classNames: ['gcal-event'], display: 'block'
    }));
    
    const sessionBlockEvents = bloquesDeSesiones.value.map(bloque => ({
        id: bloque.id,
        title: bloque.title,
        start: bloque.start,
        end: bloque.end,
        allDay: true,
        backgroundColor: bloque.color,
        borderColor: bloque.color,
        extendedProps: { description: bloque.description }
    }));

    return [...sessionBlockEvents, ...selectionEvents.value, ...holidayEvents];
});

const isHoliday = (dateStr) => holidays.value.some(h => h.date === dateStr);

const handleDateClick = (clickInfo) => {
    const clickedDate = clickInfo.dateStr;
    const dayOfWeek = clickInfo.date.getDay();
    if (dayOfWeek === 0 || dayOfWeek === 6 || isHoliday(clickedDate)) return;
    const existingIndex = selectionEvents.value.findIndex(e => e.start === clickedDate);
    if (existingIndex > -1) {
        selectionEvents.value.splice(existingIndex, 1);
    } else {
        selectionEvents.value.push({ start: clickedDate, display: 'background', color: '#338CBF' });
    }
};

const handleEventClick = (clickInfo) => {
    if (clickInfo.event.classNames.includes('gcal-event')) return;
    const blockId = clickInfo.event.id;
    const block = bloquesDeSesiones.value.find(b => b.id === blockId);
    if (block) {
        editingBlockId.value = blockId;
        showForm.value = true;
    }
};

const clearSelection = () => selectionEvents.value = [];
const openSessionForm = () => { if (hasSelection.value) showForm.value = true; };

const handleSaveSesion = (formData) => {
    if (editingBlockId.value) {
        const blockIndex = bloquesDeSesiones.value.findIndex(b => b.id === editingBlockId.value);
        if (blockIndex > -1) {
            bloquesDeSesiones.value[blockIndex].title = `Sesión: ${formData.nombre_sesion}`;
            bloquesDeSesiones.value[blockIndex].description = formData.descripcion;
        }
    } else {
        const newColor = eventColors[colorIndex % eventColors.length];
        colorIndex++;
        const newBlock = {
            id: uuidv4(),
            title: `Sesión: ${formData.nombre_sesion || 'Sin Tema'}`,
            description: formData.descripcion,
            start: selectedDates.value[0],
            end: new Date(new Date(selectedDates.value[selectedDates.value.length - 1]).getTime() + 86400000).toISOString().split('T')[0],
            color: newColor,
            dates: selectedDates.value
        };
        bloquesDeSesiones.value.push(newBlock);
    }
    showForm.value = false;
    clearSelection();
    editingBlockId.value = null;
};

const deleteBlock = (blockId) => {
    bloquesDeSesiones.value = bloquesDeSesiones.value.filter(b => b.id !== blockId);
};

const calendarOptions = ref({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: esLocale,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,dayGridWeek' },
    buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    events: allEvents,
});

onMounted(async () => {
    try {
        const data = await fetchHolidays();
        if (data && Array.isArray(data.items)) {
            holidays.value = data.items.map(item => ({ date: item.start.date, title: item.summary }));
        }
    } catch (error) {
        console.error("Error al obtener feriados desde el backend:", error);
    }
});
</script>

<template>
    <div class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="md:flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Programador de Sesiones</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Haga clic en los días para seleccionar.</p>
                </div>
                <div v-if="hasSelection" class="mt-4 md:mt-0 flex gap-2 justify-end">
                     <button @click="clearSelection" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors">Limpiar</button>
                    <button @click="openSessionForm" class="bg-cetpro hover:bg-cetpro-dark text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors">
                        Guardar {{ selectedDates.length }} Sesión(es)
                    </button>
                </div>
            </div>
            
            <FullCalendar :options="calendarOptions" />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 h-fit">
             <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Bloques de Sesiones</h2>
            <Table>
                <THead>
                    <Th>Tema</Th>
                    <Th>Fechas</Th>
                    <Th>Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-if="bloquesDeSesiones.length === 0">
                        <Td colspan="3" class="text-center text-gray-500">Aún no hay bloques programados.</Td>
                    </Tr>
                    <Tr v-for="bloque in bloquesDeSesiones" :key="bloque.id">
                        <Td class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: bloque.color }"></span>
                            {{ bloque.title }}
                        </Td>
                        <Td class="text-xs">
                           {{ new Date(bloque.start + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }) }} - 
                           {{ new Date(new Date(bloque.end).getTime() - 86400000).toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }) }}
                        </Td>
                        <Td class="flex gap-1">
                            <button @click="editingBlockId = bloque.id; showForm = true;" class="text-blue-500 hover:text-blue-700">Editar</button>
                            <button @click="deleteBlock(bloque.id)" class="text-red-500 hover:text-red-700">Borrar</button>
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>

        <SesionSlider
            v-if="showForm"
            :show="showForm"
            :fechas-seleccionadas="selectedDates"
            :block-to-edit="bloquesDeSesiones.find(b => b.id === editingBlockId)"
            @hide="showForm = false; editingBlockId = null;"
            @save="handleSaveSesion"
        />
    </div>
</template>

<style>
.fc-day-sun, .fc-day-sat { background-color: #f5f5f5 !important; }
.dark .fc-day-sun, .dark .fc-day-sat { background-color: #2d3748 !important; }
.gcal-event {
    background-color: #6b7280 !important; border-color: #4b5563 !important;
    cursor: not-allowed; color: white !important; font-weight: bold;
}
.fc .fc-bg-event { opacity: 0.8 !important; }
.fc-day-today { background-color: rgba(0, 111, 159, 0.1) !important; }
.dark .fc-day-today { background-color: rgba(51, 139, 191, 0.15) !important; }
.fc-event { cursor: pointer; }
</style>