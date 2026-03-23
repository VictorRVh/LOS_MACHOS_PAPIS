<script setup>
import { ref, computed } from 'vue';
import flatPickr from 'vue-flatpickr-component';
import useEstadistica203Store from '../../store/Estadisticas/Estadistica203Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';
import { createDatePickerConfig } from '../../utils/datePickerConfig';

const fechaInicio = ref('');
const fechaFin = ref('');

const datePickerConfig = createDatePickerConfig();

const estadisticaStore = useEstadistica203Store();
const { showToast } = useModalToast();
const { addInstitutionalHeader } = useExportEstadisticasExcel();
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica203(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica203 || []);

const chartSeries = computed(() =>
  (data.value || []).map((item, idx) => ({
    label: String(item.nivel || "NIVEL").toUpperCase(),
    value: Number(item?.total?.H || 0) + Number(item?.total?.M || 0),
    color: ["#0ea5e9", "#0284c7", "#0369a1", "#0891b2", "#22c55e", "#f59e0b", "#ef4444"][idx % 7],
  }))
);

const abrirGrafico = () => {
  if (!chartSeries.value.some((s) => s.value > 0)) {
    showToast('Primero consulta datos para mostrar el gráfico.', 'warning');
    return;
  }
  showChart.value = true;
};

const exportarExcel203 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  try {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Reporte 203', { views: [{ state: 'frozen', ySplit: 9 }] });

    ws.columns = [
      { width: 34 }, { width: 9 }, { width: 9 }, { width: 9 },
      { width: 9 }, { width: 9 }, { width: 9 },
    ];

    const { startRow, applyThinBorder } = await addInstitutionalHeader(wb, ws, {
      reportTitle: 'REPORTE 203 - MATRICULA POR CICLO Y SEXO, SEGUN NIVEL EDUCATIVO',
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      totalCols: 7,
      logoCols: 2,
    });

    ws.mergeCells(`A${startRow}:A${startRow + 1}`);
    ws.mergeCells(`B${startRow}:C${startRow}`);
    ws.mergeCells(`D${startRow}:E${startRow}`);
    ws.mergeCells(`F${startRow}:G${startRow}`);

    ws.getCell(`A${startRow}`).value = 'NIVEL EDUCATIVO DE LOS PARTICIPANTES';
    ws.getCell(`B${startRow}`).value = 'TOTAL';
    ws.getCell(`D${startRow}`).value = 'BASICO';
    ws.getCell(`F${startRow}`).value = 'MEDIO';

    ['A', 'B', 'D', 'F'].forEach((col) => {
      const c = ws.getCell(`${col}${startRow}`);
      c.font = { bold: true, italic: true, color: { argb: 'FFFFFFFF' } };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
      applyThinBorder(c);
    });

    ['B', 'C', 'D', 'E', 'F', 'G'].forEach((col, i) => {
      const c = ws.getCell(`${col}${startRow + 1}`);
      c.value = i % 2 === 0 ? 'H' : 'M';
      c.font = { bold: true, color: { argb: 'FF64748B' } };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
      applyThinBorder(c);
    });

    let row = startRow + 2;
    data.value.forEach((item) => {
      ws.addRow([
        String(item.nivel || '').toUpperCase(),
        item.total.H,
        item.total.M,
        item.auxiliar_tecnico.H,
        item.auxiliar_tecnico.M,
        item.tecnico.H,
        item.tecnico.M,
      ]);

      for (let c = 1; c <= 7; c++) {
        const cell = ws.getCell(row, c);
        cell.font = { bold: c === 1, color: { argb: 'FF0F172A' } };
        cell.alignment = { horizontal: c === 1 ? 'left' : 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: row % 2 === 0 ? 'FFFFFFFF' : 'FFF8FAFC' } };
        applyThinBorder(cell);
      }
      row++;
    });

    const buffer = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    }), `Reporte_203_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`);

    showToast('Excel 203 generado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo exportar el reporte 203.', 'error');
  }
};

</script>

<template>
  <div class="space-y-2 p-3"><div class="flex flex-col gap-1.5 xl:flex-row xl:items-start xl:justify-between"><div class="space-y-0.5"><p class="text-[9px] font-semibold uppercase tracking-[0.18em] text-slate-400">Reporte 203</p><h2 class="text-[1.25rem] font-semibold tracking-[0.01em] text-slate-900">203. MATRÍCULA POR CICLO Y SEXO, SEGÚN NIVEL EDUCATIVO</h2></div><div class="flex flex-wrap items-center gap-1.5"><button @click="abrirGrafico" class="inline-flex h-8 items-center justify-center rounded-md border border-cetpro/20 bg-cetpro/10 px-3 text-[11px] font-semibold text-cetpro transition-colors hover:bg-cetpro/15 whitespace-nowrap">GRAFICO</button><button @click="exportarExcel203" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-emerald-200 bg-white px-3 text-[11px] font-semibold text-emerald-700 transition-colors hover:bg-emerald-50 whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 0 1 1 1v6.586l1.293-1.293a1 1 0 1 1 1.414 1.414l-3 3a1 1 0 0 1-1.414 0l-3-3A1 1 0 0 1 7.707 8.293L9 9.586V3a1 1 0 0 1 1-1Zm-6 11a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z" clip-rule="evenodd" /></svg>Exportar Excel</button></div></div><div class="grid grid-cols-1 gap-1.5 border border-slate-200 bg-white p-2.5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_220px]"><div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA INICIO</label><flat-pickr v-model="fechaInicio" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div><div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA FIN</label><flat-pickr v-model="fechaFin" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div><div class="flex items-end"><button @click="consultarDatos" class="inline-flex h-8 w-full items-center justify-center rounded-md bg-cetpro px-3 text-[11px] font-semibold text-white transition-colors hover:bg-cetpro-dark">Consultar datos</button></div></div><div class="overflow-x-auto border border-slate-200 bg-white"><table class="w-full text-[11px] text-center"><thead><tr class="bg-slate-800 text-white uppercase font-semibold border-b border-slate-600"><th rowspan="2" class="px-2 py-1 border-r border-slate-600 text-left">Nivel Educativo de los Participantes</th><th colspan="2" class="px-2 py-1 border-b border-r border-slate-600 bg-slate-700">Total</th><th colspan="2" class="px-2 py-1 border-b border-r border-slate-600 bg-cetpro">Básico</th><th colspan="2" class="px-2 py-1 border-b bg-cetpro-dark">Medio</th></tr><tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-semibold"><th class="px-2 py-1 border-r border-slate-200">H</th><th class="px-2 py-1 border-r border-slate-200">M</th><th class="px-2 py-1 border-r border-slate-200">H</th><th class="px-2 py-1 border-r border-slate-200">M</th><th class="px-2 py-1 border-r border-slate-200">H</th><th class="px-2 py-1">M</th></tr></thead><tbody class="divide-y divide-slate-200 font-medium text-slate-700"><tr v-for="item in data" :key="item.nivel" class="hover:bg-slate-50/70"><td class="px-2 py-1 text-left border-r border-slate-200 font-semibold text-slate-900">{{ item.nivel.toUpperCase() }}</td><td class="px-2 py-1 border-r border-slate-200">{{ item.total.H }}</td><td class="px-2 py-1 border-r border-slate-200">{{ item.total.M }}</td><td class="px-2 py-1 border-r border-slate-200">{{ item.auxiliar_tecnico.H }}</td><td class="px-2 py-1 border-r border-slate-200">{{ item.auxiliar_tecnico.M }}</td><td class="px-2 py-1 border-r border-slate-200">{{ item.tecnico.H }}</td><td class="px-2 py-1">{{ item.tecnico.M }}</td></tr></tbody></table></div><ChartDonutModal :show="showChart" title="Gráfico 203 - Nivel Educativo" subtitle="Distribución de matrícula total por nivel educativo" :series="chartSeries" @close="showChart = false" /></div>
</template>




