<script setup>
import { ref, computed } from 'vue';
import flatPickr from 'vue-flatpickr-component';
import useEstadistica205Store from '../../store/Estadisticas/Estadistica205Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';
import { createDatePickerConfig } from '../../utils/datePickerConfig';

const fechaInicio = ref('');
const fechaFin = ref('');

const datePickerConfig = createDatePickerConfig();

const estadisticaStore = useEstadistica205Store();
const { showToast } = useModalToast();
const { addInstitutionalHeader } = useExportEstadisticasExcel();
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica205(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica205 || []);

const tablaProcesada = computed(() => {
  const resultado = {
    Mañana: { basico: 0, medio: 0, total: 0 },
    Tarde: { basico: 0, medio: 0, total: 0 },
    Noche: { basico: 0, medio: 0, total: 0 },
  };

  data.value.forEach(item => {
    const esBasico = item.ciclo.includes('Auxiliar');
    const esMedio = item.ciclo.includes('Técnico') && !item.ciclo.includes('Auxiliar');

    Object.entries(item.turnos).forEach(([turno, cantidad]) => {
      let nombreTurno = '';
      if (turno === 'M') nombreTurno = 'Mañana';
      if (turno === 'T') nombreTurno = 'Tarde';
      if (turno === 'N') nombreTurno = 'Noche';

      if (!resultado[nombreTurno]) return;

      if (esBasico) resultado[nombreTurno].basico += cantidad;
      if (esMedio) resultado[nombreTurno].medio += cantidad;

      resultado[nombreTurno].total += cantidad;
    });
  });

  return resultado;
});

const totalGeneral = computed(() => {
  return Object.values(tablaProcesada.value).reduce(
    (acc, turno) => {
      acc.basico += turno.basico;
      acc.medio += turno.medio;
      acc.total += turno.total;
      return acc;
    },
    { basico: 0, medio: 0, total: 0 }
  );
});

const chartSeries = computed(() => ([
  { label: 'Mañana', value: Number(tablaProcesada.value['Mañana']?.total || 0), color: '#0ea5e9' },
  { label: 'Tarde', value: Number(tablaProcesada.value['Tarde']?.total || 0), color: '#0284c7' },
  { label: 'Noche', value: Number(tablaProcesada.value['Noche']?.total || 0), color: '#0369a1' },
]));

const abrirGrafico = () => {
  if (!chartSeries.value.some((s) => s.value > 0)) {
    showToast('Primero consulta datos para mostrar el gráfico.', 'warning');
    return;
  }
  showChart.value = true;
};

const exportarExcel205 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  try {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Reporte 205', { views: [{ state: 'frozen', ySplit: 9 }] });

    ws.columns = [
      { width: 22 }, { width: 16 }, { width: 18 }, { width: 14 },
    ];

    const { startRow, applyThinBorder } = await addInstitutionalHeader(wb, ws, {
      reportTitle: 'REPORTE 205 - NUMERO TOTAL DE SECCIONES, POR CICLO SEGUN TURNO',
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      totalCols: 7,
      logoCols: 2,
    });

    ws.getCell(`A${startRow}`).value = 'TURNO DE TRABAJO';
    ws.getCell(`B${startRow}`).value = 'TOTAL SECCIONES';
    ws.getCell(`C${startRow}`).value = 'CICLO AUXILIAR TECNICO';
    ws.getCell(`D${startRow}`).value = 'CICLO TECNICO';

    ['A', 'B', 'C', 'D'].forEach((col, i) => {
      const c = ws.getCell(`${col}${startRow}`);
      c.font = { bold: true, italic: true, color: { argb: 'FFFFFFFF' } };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: i < 2 ? 'FF1E293B' : i === 2 ? 'FF0369A1' : 'FF075985' },
      };
      applyThinBorder(c);
    });

    const order = ['Mañana', 'Tarde', 'Noche'];
    let row = startRow + 1;

    order.forEach((turno) => {
      ws.addRow([
        turno,
        tablaProcesada.value[turno].total,
        tablaProcesada.value[turno].basico,
        tablaProcesada.value[turno].medio,
      ]);

      for (let c = 1; c <= 4; c++) {
        const cell = ws.getCell(row, c);
        cell.font = { bold: c === 1, color: { argb: 'FF0F172A' } };
        cell.alignment = { horizontal: c === 1 ? 'left' : 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: row % 2 === 0 ? 'FFFFFFFF' : 'FFF8FAFC' } };
        applyThinBorder(cell);
      }
      row++;
    });

    ws.addRow(['TOTAL GENERAL', totalGeneral.value.total, totalGeneral.value.basico, totalGeneral.value.medio]);
    for (let c = 1; c <= 4; c++) {
      const cell = ws.getCell(row, c);
      cell.font = { bold: true, color: { argb: 'FF075985' } };
      cell.alignment = { horizontal: c === 1 ? 'left' : 'center', vertical: 'middle' };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE6F3FA' } };
      applyThinBorder(cell);
    }

    const buffer = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    }), `Reporte_205_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`);

    showToast('Excel 205 generado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo exportar el reporte 205.', 'error');
  }
};

</script>

<template>
  <div class="space-y-2 p-3"><div class="flex flex-col gap-1.5 xl:flex-row xl:items-start xl:justify-between"><div class="space-y-0.5"><p class="text-[9px] font-semibold uppercase tracking-[0.18em] text-slate-400">Reporte 205</p><h2 class="text-[1.25rem] font-semibold tracking-[0.01em] text-slate-900">205. NÚMERO TOTAL DE SECCIONES, POR CICLO SEGÚN TURNO</h2></div><div class="flex flex-wrap items-center gap-1.5"><button @click="abrirGrafico" class="inline-flex h-8 items-center justify-center rounded-md border border-cetpro/20 bg-cetpro/10 px-3 text-[11px] font-semibold text-cetpro transition-colors hover:bg-cetpro/15 whitespace-nowrap">GRAFICO</button><button @click="exportarExcel205" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-emerald-200 bg-white px-3 text-[11px] font-semibold text-emerald-700 transition-colors hover:bg-emerald-50 whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 0 1 1 1v6.586l1.293-1.293a1 1 0 1 1 1.414 1.414l-3 3a1 1 0 0 1-1.414 0l-3-3A1 1 0 0 1 7.707 8.293L9 9.586V3a1 1 0 0 1 1-1Zm-6 11a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z" clip-rule="evenodd" /></svg>Exportar Excel</button></div></div><div class="grid grid-cols-1 gap-1.5 border border-slate-200 bg-white p-2.5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_220px]"><div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA INICIO</label><flat-pickr v-model="fechaInicio" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div><div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA FIN</label><flat-pickr v-model="fechaFin" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div><div class="flex items-end"><button @click="consultarDatos" class="inline-flex h-8 w-full items-center justify-center rounded-md bg-cetpro px-3 text-[11px] font-semibold text-white transition-colors hover:bg-cetpro-dark">Consultar datos</button></div></div><div class="max-w-4xl overflow-hidden border border-slate-200 bg-white"><table class="w-full text-[11px] text-center"><thead><tr class="bg-slate-800 text-white font-semibold uppercase"><th class="px-2.5 py-1.5 border-r border-slate-600">Turno de Trabajo</th><th class="px-2.5 py-1.5 border-r border-slate-600 bg-slate-700">Total Secciones</th><th class="px-2.5 py-1.5 border-r border-slate-600 bg-cetpro">Ciclo Auxiliar Tecnico</th><th class="px-2.5 py-1.5 bg-cetpro-dark">Ciclo Tecnico</th></tr></thead><tbody class="divide-y divide-slate-200 text-[13px] font-semibold text-slate-700"><tr v-for="turno in ['Mañana', 'Tarde', 'Noche']" :key="turno" class="hover:bg-slate-50/70"><td class="px-2.5 py-1.5 text-left border-r border-slate-200 uppercase bg-slate-50/50">{{ turno }}</td><td class="px-2.5 py-1.5 border-r border-slate-200 text-[18px] font-bold">{{ tablaProcesada[turno].total }}</td><td class="px-2.5 py-1.5 border-r border-slate-200 text-cetpro font-bold">{{ tablaProcesada[turno].basico }}</td><td class="px-2.5 py-1.5 text-cetpro-dark font-bold">{{ tablaProcesada[turno].medio }}</td></tr><tr class="bg-cetpro/5 text-cetpro"><td class="px-2.5 py-1.5 text-left border-r border-slate-200 font-bold uppercase">Total General</td><td class="px-2.5 py-1.5 border-r border-slate-200 text-[18px] font-bold">{{ totalGeneral.total }}</td><td class="px-2.5 py-1.5 border-r border-slate-200 font-bold">{{ totalGeneral.basico }}</td><td class="px-2.5 py-1.5 font-bold">{{ totalGeneral.medio }}</td></tr></tbody></table></div><ChartDonutModal :show="showChart" title="Gráfico 205 - Secciones por Turno" subtitle="Distribución total de secciones por turno" :series="chartSeries" @close="showChart = false" /></div>
</template>





