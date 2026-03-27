<script setup>
import { ref, computed } from 'vue';
import flatPickr from 'vue-flatpickr-component';
import useEstadistica101Store from '../../store/Estadisticas/Estadistica101Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';
import { createDatePickerConfig } from '../../utils/datePickerConfig';

const fechaInicio = ref('');
const fechaFin = ref('');

const datePickerConfig = createDatePickerConfig();

const estadisticaStore = useEstadistica101Store();
const { showToast } = useModalToast();
const { addInstitutionalHeader } = useExportEstadisticasExcel();
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica101(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => {
  const source = estadisticaStore.estadistica101;

  return {
    aprobados: {
      total: source?.aprobados?.total ?? 0,
      auxiliar_tecnico: {
        H: source?.aprobados?.auxiliar_tecnico?.H ?? 0,
        M: source?.aprobados?.auxiliar_tecnico?.M ?? 0,
      },
      tecnico: {
        H: source?.aprobados?.tecnico?.H ?? 0,
        M: source?.aprobados?.tecnico?.M ?? 0,
      }
    },
    retirados: {
      total: source?.retirados?.total ?? 0,
      auxiliar_tecnico: {
        H: source?.retirados?.auxiliar_tecnico?.H ?? 0,
        M: source?.retirados?.auxiliar_tecnico?.M ?? 0,
      },
      tecnico: {
        H: source?.retirados?.tecnico?.H ?? 0,
        M: source?.retirados?.tecnico?.M ?? 0,
      }
    }
  };
});

const chartSeries = computed(() => ([
  { label: 'Aprobados', value: Number(data.value.aprobados.total || 0), color: '#10b981' },
  { label: 'Retirados', value: Number(data.value.retirados.total || 0), color: '#ef4444' },
]));

const abrirGrafico = () => {
  if (!chartSeries.value.some((s) => s.value > 0)) {
    showToast('Primero consulta datos para mostrar el gráfico.', 'warning');
    return;
  }
  showChart.value = true;
};

const exportarExcel101 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  try {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Reporte 101', { views: [{ state: 'frozen', ySplit: 9 }] });

    ws.columns = [
      { width: 30 }, { width: 14 }, { width: 10 }, { width: 10 },
      { width: 10 }, { width: 10 }, { width: 10 }, { width: 10 },
    ];

    const { startRow, applyThinBorder } = await addInstitutionalHeader(wb, ws, {
      reportTitle: 'REPORTE 101 - APROBADOS Y RETIRADOS SEGÚN CICLO Y SEXO',
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      totalCols: 8,
      logoCols: 2,
    });

    ws.mergeCells(`A${startRow}:A${startRow + 1}`);
    ws.mergeCells(`B${startRow}:B${startRow + 1}`);
    ws.mergeCells(`C${startRow}:D${startRow}`);
    ws.mergeCells(`E${startRow}:F${startRow}`);
    ws.mergeCells(`G${startRow}:H${startRow}`);

    ws.getCell(`A${startRow}`).value = 'SITUACIÓN';
    ws.getCell(`B${startRow}`).value = 'TOTAL GENERAL';
    ws.getCell(`C${startRow}`).value = 'TOTAL';
    ws.getCell(`E${startRow}`).value = 'AUXILIAR TÉCNICO';
    ws.getCell(`G${startRow}`).value = 'TÉCNICO';

    const topHeaders = [`A${startRow}`, `B${startRow}`, `C${startRow}`, `E${startRow}`, `G${startRow}`];
    topHeaders.forEach((addr) => {
      const c = ws.getCell(addr);
      c.font = { bold: true, color: { argb: 'FFFFFFFF' }, italic: true };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
      applyThinBorder(c);
    });

    ['C', 'D', 'E', 'F', 'G', 'H'].forEach((col, i) => {
      const cell = ws.getCell(`${col}${startRow + 1}`);
      cell.value = i % 2 === 0 ? 'H' : 'M';
      cell.font = { bold: true, color: { argb: 'FF475569' } };
      cell.alignment = { horizontal: 'center', vertical: 'middle' };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
      applyThinBorder(cell);
    });

    const rows = [
      [
        'APROBADOS',
        data.value.aprobados.total,
        data.value.aprobados.auxiliar_tecnico.H + data.value.aprobados.tecnico.H,
        data.value.aprobados.auxiliar_tecnico.M + data.value.aprobados.tecnico.M,
        data.value.aprobados.auxiliar_tecnico.H,
        data.value.aprobados.auxiliar_tecnico.M,
        data.value.aprobados.tecnico.H,
        data.value.aprobados.tecnico.M,
      ],
      [
        'RETIRADOS',
        data.value.retirados.total,
        data.value.retirados.auxiliar_tecnico.H + data.value.retirados.tecnico.H,
        data.value.retirados.auxiliar_tecnico.M + data.value.retirados.tecnico.M,
        data.value.retirados.auxiliar_tecnico.H,
        data.value.retirados.auxiliar_tecnico.M,
        data.value.retirados.tecnico.H,
        data.value.retirados.tecnico.M,
      ],
    ];

    let r = startRow + 2;
    rows.forEach((vals) => {
      ws.addRow(vals);
      for (let col = 1; col <= 8; col++) {
        const c = ws.getCell(r, col);
        c.font = { bold: col === 1 || col === 2, color: { argb: 'FF0F172A' } };
        c.alignment = { horizontal: col === 1 ? 'left' : 'center', vertical: 'middle' };
        c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: r % 2 === 0 ? 'FFFFFFFF' : 'FFF8FAFC' } };
        applyThinBorder(c);
      }
      r++;
    });

    const buffer = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    }), `Reporte_101_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`);

    showToast('Excel 101 generado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo exportar el reporte 101.', 'error');
  }
};

</script>

<template>
  <div class="space-y-2 p-3">
    <div class="flex flex-col gap-1.5 xl:flex-row xl:items-start xl:justify-between">
      <div class="space-y-0.5">
        <p class="text-[9px] font-semibold uppercase tracking-[0.18em] text-slate-400">Reporte 101</p>
        <h2 class="text-[1.25rem] font-semibold tracking-[0.01em] text-slate-900">101. APROBADOS Y RETIRADOS SEGÚN CICLO Y SEXO</h2>
      </div>
      <div class="flex flex-wrap items-center gap-1.5">
        <button @click="abrirGrafico" class="inline-flex h-8 items-center justify-center rounded-md border border-cetpro/20 bg-cetpro/10 px-3 text-[11px] font-semibold text-cetpro transition-colors hover:bg-cetpro/15">GRAFICO</button>
        <button @click="exportarExcel101" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-emerald-200 bg-white px-3 text-[11px] font-semibold text-emerald-700 transition-colors hover:bg-emerald-50"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 0 1 1 1v6.586l1.293-1.293a1 1 0 1 1 1.414 1.414l-3 3a1 1 0 0 1-1.414 0l-3-3A1 1 0 0 1 7.707 8.293L9 9.586V3a1 1 0 0 1 1-1Zm-6 11a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z" clip-rule="evenodd" /></svg>Exportar Excel</button>
      </div>
    </div>
    <div class="grid grid-cols-1 gap-1.5 border border-slate-200 bg-white p-2.5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_220px]">
      <div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA INICIO</label><flat-pickr v-model="fechaInicio" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div>
      <div><label class="mb-0.5 block text-[9px] font-semibold tracking-[0.1em] text-slate-700">FECHA FIN</label><flat-pickr v-model="fechaFin" :config="datePickerConfig" class="h-8 w-full rounded-md border border-slate-300 px-2.5 text-[11px] text-slate-800 outline-none transition-colors hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15" /></div>
      <div class="flex items-end"><button @click="consultarDatos" class="inline-flex h-8 w-full items-center justify-center rounded-md bg-cetpro px-3 text-[11px] font-semibold text-white transition-colors hover:bg-cetpro-dark">Consultar datos</button></div>
    </div>
    <div class="overflow-x-auto border border-slate-200 bg-white">
      <table class="w-full text-[11px]">
        <thead>
          <tr class="bg-slate-800 text-center text-white"><th class="border-r border-slate-600 px-2 py-1">SITUACIÓN</th><th class="border-r border-slate-600 px-2 py-1">TOTAL GENERAL</th><th colspan="2" class="border-r border-slate-600 bg-slate-700 px-2 py-1">TOTAL</th><th colspan="2" class="border-r border-slate-600 bg-cetpro px-2 py-1">AUXILIAR TECNICO</th><th colspan="2" class="bg-cetpro-dark px-2 py-1">TECNICO</th></tr>
          <tr class="bg-slate-50 text-[12px] font-semibold text-slate-500"><th class="px-2 py-1"></th><th class="px-2 py-1"></th><th class="px-2 py-1">H</th><th class="px-2 py-1">M</th><th class="px-2 py-1">H</th><th class="px-2 py-1">M</th><th class="px-2 py-1">H</th><th class="px-2 py-1">M</th></tr>
        </thead>
        <tbody class="text-center font-medium text-slate-700"><tr class="border-t border-slate-200"><td class="px-2 py-1 text-left font-semibold text-slate-900">APROBADOS</td><td class="px-2 py-1 font-bold text-cetpro">{{ data.aprobados.total }}</td><td class="px-2 py-1">{{ data.aprobados.auxiliar_tecnico.H + data.aprobados.tecnico.H }}</td><td class="px-2 py-1">{{ data.aprobados.auxiliar_tecnico.M + data.aprobados.tecnico.M }}</td><td class="px-2 py-1">{{ data.aprobados.auxiliar_tecnico.H }}</td><td class="px-2 py-1">{{ data.aprobados.auxiliar_tecnico.M }}</td><td class="px-2 py-1">{{ data.aprobados.tecnico.H }}</td><td class="px-2 py-1">{{ data.aprobados.tecnico.M }}</td></tr><tr class="border-t border-slate-200 bg-slate-50/40"><td class="px-2 py-1 text-left font-semibold text-slate-900">RETIRADOS</td><td class="px-2 py-1 font-bold text-cetpro">{{ data.retirados.total }}</td><td class="px-2 py-1">{{ data.retirados.auxiliar_tecnico.H + data.retirados.tecnico.H }}</td><td class="px-2 py-1">{{ data.retirados.auxiliar_tecnico.M + data.retirados.tecnico.M }}</td><td class="px-2 py-1">{{ data.retirados.auxiliar_tecnico.H }}</td><td class="px-2 py-1">{{ data.retirados.auxiliar_tecnico.M }}</td><td class="px-2 py-1">{{ data.retirados.tecnico.H }}</td><td class="px-2 py-1">{{ data.retirados.tecnico.M }}</td></tr></tbody>
      </table>
    </div>
    <ChartDonutModal :show="showChart" title="Gráfico 101 - Aprobados vs Retirados" subtitle="Distribución total por situación académica" :series="chartSeries" @close="showChart = false" />
  </div>
</template>




