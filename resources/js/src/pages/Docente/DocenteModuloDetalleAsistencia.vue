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
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';


const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const sesionStore = useSesionStore()
const programacionSesion = useProgramacionStore();
const datesForSlider = ref([])

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteSesion, deleting } = useHttpRequest("/programacion_sesion_docente");

const asist = ref(false);
const asistData = ref(null);

if (!sesionStore?.sesion?.length) {
  await sesionStore.loadSesion(props.id)
}

if (!programacionSesion?.sesiones?.length) {
  await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
}

const Asistencia = () => {
  if (sesionStore?.sesion?.id) {
    asist.value = true;
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
const calendarKey = ref(0)

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
                  ? '#facc15'
                  : sesion.status === 1
                    ? '#22c55e'
                    : '#3b82f6',
              borderColor: '#fff',
              extendedProps: {
                descripcion: sesion.descripcion,
                idSesion: sesion.id,
                idCapacidad: cap.id,
                nombreCapacidad: cap.nombre_capacidad
              }
            })

            inicio = actual
            fin = actual
          } else {
            fin = actual
          }
        }
      })
    })

    allEvents.value = eventos
  },
  { deep: true, immediate: true }
)

const selectedDates = computed(() => selectionEvents.value.map(e => e.start).sort())
const hasSelection = computed(() => selectedDates.value.length > 0)

watch(
  selectedDates,
  (nuevasFechas) => {
    datesForSlider.value = [...nuevasFechas];
  },
  { immediate: true }
);

const handleDateClick = ({ dateStr, date }) => {
  const isWeekend = date.getDay() === 0 || date.getDay() === 6;
  if (isWeekend) return;

  const isAlreadyScheduled = allEvents.value.some(event =>
    dateStr >= event.start && dateStr < event.end &&
    (!isEditing.value || event.extendedProps.idSesion !== sliderData.value?.id)
  );

  if (isAlreadyScheduled) {
    showToast("Esta fecha ya está programada en otra sesión.");
    return;
  }

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

const clearSelection = () => {
  selectionEvents.value = [];
  datesForSlider.value = [];
  selectedDates.value = [];
  calendarKey.value++

  const calendar = document.querySelector('.fc');
  if (calendar) {
    calendar.dispatchEvent(new Event('refresh'));
  }
};

const openSessionForm = () => {
  if (!hasSelection.value) return;

  datesForSlider.value = [...selectedDates.value];
  sliderData.value = null;
  showSlider(true);
};

const confirmDelete = (bloque) => {
  if (deleting.value) return;

  showConfirmModal(
    `¿Estás seguro de que deseas eliminar el bloque "${bloque.nombre_sesion}"?`,
    async (confirmed) => {
      if (!confirmed) return

      const wasDeleted = await deleteSesion(bloque.id)

      if (wasDeleted) {
        showToast(`"${bloque.nombre_sesion}" eliminado correctamente.`)
        await programacionSesion.loadSesiones(sesionStore?.sesion?.id)
      }
    }
  )
}

const verSesion = (bloque) => { }
const isEditing = ref(false)

const handleEdit = (bloque) => {
  clearSelection()
  isEditing.value = true

  const fechas = bloque.calendario_admin.map(d => d.fecha)
  selectionEvents.value = fechas.map(f => ({
    start: f,
    display: 'background',
    color: 'rgba(51,139,191,0.9)'
  }))

  datesForSlider.value = [...fechas]
  sliderData.value = bloque
}
const cancelEdit = () => {
  isEditing.value = false
  clearSelection()
}

const updateSession = () => {
  if (!datesForSlider.value.length) return
  showSlider(true, sliderData.value)
}

const estadoTexto = computed(() => {
  if (!sesionStore?.sesion) return 'Sin programación'
  switch (sesionStore?.sesion?.estado) {
    case 0: return 'Pendiente'
    case 1: return 'En curso'
    case 2: return 'Finalizada'
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

const exportarCalendarioExcel = async () => {
  if (!sesionStore.sesion || !programacionSesion.sesiones) {
    showToast("No hay datos suficientes para generar el reporte.", "error");
    return;
  }

  const workbook = new ExcelJS.Workbook();
  const worksheet = workbook.addWorksheet('Programacion de Sesiones');

  const grupoInfo = sesionStore.grupo;
  const sesionInfo = sesionStore.sesion;

  worksheet.mergeCells('B2:E2');
  const titleCell = worksheet.getCell('B2');
  titleCell.value = 'REPORTE DE PROGRAMACIÓN DE SESIONES';
  titleCell.font = { name: 'Calibri', size: 16, bold: true, color: { argb: 'FFFFFFFF' } };
  titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
  titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF007B8C' } };

  const headerData = [
    ['Módulo:', grupoInfo?.modulo || 'N/A', 'Docente:', grupoInfo?.docente || 'N/A'],
    ['Especialidad:', grupoInfo?.especialidad || 'N/A', 'Turno:', grupoInfo?.turno || 'N/A'],
    ['Periodo:', `${new Date(sesionInfo.fecha_inicio).toLocaleDateString()} - ${new Date(sesionInfo.fecha_fin).toLocaleDateString()}`, 'Sección:', grupoInfo?.seccion || 'N/A'],
  ];

  let currentRowNum = 4;
  headerData.forEach(rowData => {
    const row = worksheet.getRow(currentRowNum);
    row.getCell(2).value = { richText: [{ font: { bold: true }, text: rowData[0] }] };
    row.getCell(3).value = rowData[1];
    row.getCell(5).value = { richText: [{ font: { bold: true }, text: rowData[2] }] };
    row.getCell(6).value = rowData[3];

    ['B', 'C', 'E', 'F'].forEach(col => {
        const cell = worksheet.getCell(`${col}${currentRowNum}`);
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFF00' } };
        cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
    });
    currentRowNum++;
  });
  
  const startDate = new Date(sesionInfo.fecha_inicio);
  const endDate = new Date(sesionInfo.fecha_fin);
  const dateToCellMap = new Map();

  let currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
  currentRowNum += 2;

  while (currentDate <= endDate) {
    const anio = currentDate.getFullYear();
    const mes = currentDate.getMonth();
    const nombreMes = currentDate.toLocaleString('es-ES', { month: 'long' }).toUpperCase();

    worksheet.mergeCells(`A${currentRowNum}:G${currentRowNum}`);
    const monthTitleCell = worksheet.getCell(`A${currentRowNum}`);
    monthTitleCell.value = `${nombreMes} ${anio}`;
    monthTitleCell.font = { bold: true, size: 14, color: { argb: 'FF007B8C' } };
    monthTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };
    currentRowNum++;

    const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    const headerRow = worksheet.getRow(currentRowNum);
    diasSemana.forEach((dia, index) => {
        const cell = headerRow.getCell(index + 1);
        cell.value = dia;
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF007B8C' } };
        cell.alignment = { horizontal: 'center' };
    });
    currentRowNum++;

    const primerDiaDelMes = new Date(anio, mes, 1);
    let diaDeSemanaInicial = primerDiaDelMes.getDay();
    if (diaDeSemanaInicial === 0) diaDeSemanaInicial = 7;

    let fechaIterador = new Date(primerDiaDelMes);
    fechaIterador.setDate(primerDiaDelMes.getDate() - (diaDeSemanaInicial - 1));

    for (let semana = 0; semana < 6; semana++) {
        const row = worksheet.getRow(currentRowNum + semana);
        row.height = 60;
        for (let dia = 0; dia < 7; dia++) {
            const cell = row.getCell(dia + 1);
            const fechaActualStr = fechaIterador.toISOString().split('T')[0];
            
            dateToCellMap.set(fechaActualStr, cell);

            if (fechaIterador.getMonth() === mes) {
                cell.value = fechaIterador.getDate();
                cell.font = { color: { argb: 'FF000000' } };
            } else {
                cell.font = { color: { argb: 'FFB0B0B0' } };
            }
            
            cell.alignment = { vertical: 'top', horizontal: 'right', wrapText: true };
            cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
            fechaIterador.setDate(fechaIterador.getDate() + 1);
        }
    }
    currentRowNum += 7;
    currentDate.setMonth(currentDate.getMonth() + 1);
  }

  programacionSesion.sesiones.forEach(capacidad => {
    capacidad.sesiones.forEach(sesion => {
      const color = sesion.status === 0 ? 'FFFACС15' : (sesion.status === 1 ? 'FF22C55E' : 'FF3B82F6');
      (sesion.calendario_admin || []).forEach(dia => {
        const cell = dateToCellMap.get(dia.fecha);
        if (cell) {
          const numeroDia = cell.value || '';
          cell.value = `${numeroDia}\n\n${sesion.nombre_sesion}`;
          cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: color } };
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        }
      });
    });
  });

  currentRowNum++;
  worksheet.getCell(`B${currentRowNum}`).value = { richText: [{ font: { bold: true, size: 12 }, text: "Leyenda de Estados:" }] };
  currentRowNum++;
  const leyendaData = [
    { text: 'Pendiente', color: 'FFFACС15' },
    { text: 'Activo / En Curso', color: 'FF22C55E' },
    { text: 'Finalizado', color: 'FF3B82F6' },
  ];
  leyendaData.forEach(item => {
    const cellColor = worksheet.getCell(`B${currentRowNum}`);
    cellColor.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: item.color } };
    cellColor.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };

    const cellText = worksheet.getCell(`C${currentRowNum}`);
    cellText.value = item.text;
    currentRowNum++;
  });
  
  worksheet.columns.forEach(column => {
    column.width = 20;
  });

  workbook.xlsx.writeBuffer().then(buffer => {
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const fileName = `Programacion_${grupoInfo?.modulo || 'General'}.xlsx`;
    saveAs(blob, fileName);
  }).catch(err => {
    console.error("Error al generar el Excel:", err);
    showToast("Hubo un error al generar el reporte.", "error");
  });
};

console.log("dATOS SESION: ", sesionStore?.sesion)
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

    <BaseButton title="Asistencia" @click="Asistencia"
      class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow" />

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
        <BaseCalendar :key="calendarKey" :events="[...allEvents, ...selectionEvents]" :holidays="holidays" @date-click="handleDateClick"
          @event-click="handleEventClick" :idEntrega="sesionStore?.sesion?.id" />
      </div>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
      <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
            Sesiones Programadas
        </h3>
        <BaseButton 
            title="Exportar" 
            @click="exportarCalendarioExcel" 
            variant="secondary"
        >
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
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

    <TomarAsistencia :show="asist" :grupo-id="id" :sesion-id="sesionStore?.sesion?.id" @hide="ocultarSliderAsistencia"
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