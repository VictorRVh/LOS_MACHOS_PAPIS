<script setup>
import { ref, computed } from 'vue';
import useEstadistica205Store from '../../store/Estadisticas/Estadistica205Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

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
  <div class="p-6">
    <div class="flex items-center justify-between gap-4 mb-8">
      <div class="flex items-center gap-4">
        <div class="h-12 w-2 bg-cetpro-dark rounded-full shadow-google"></div>
        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic">205. NÚMERO TOTAL DE SECCIONES,
          POR CICLO SEGÚN TURNO</h2>
      </div>
      <div class="flex items-center gap-2">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-sky-600 transition-all whitespace-nowrap">
          GRAFICO
        </button>
        <button @click="exportarExcel205"
          class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-emerald-600 transition-all whitespace-nowrap">
          EXPORTAR EXCEL
        </button>
      </div>
    </div>

    <div
      class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl mb-6 shadow-google-sm border border-gray-100">
      <div>
        <label class="text-[10px] font-black uppercase">Fecha Inicio</label>
        <input type="date" v-model="fechaInicio" class="w-full shadow-google-sm rounded-lg p-2" />
      </div>

      <div>
        <label class="text-[10px] font-black uppercase">Fecha Fin</label>
        <input type="date" v-model="fechaFin" class="w-full shadow-google-sm rounded-lg p-2" />
      </div>
      <div class="flex items-end pb-0.5">
        <button @click="consultarDatos"
          class="bg-cetpro w-full text-white font-bold py-2 rounded-lg hover:bg-cetpro-dark shadow-google-sm transition-all">
          CONSULTAR DATOS
        </button>
      </div>
    </div>

    <div class="max-w-4xl mx-auto overflow-hidden rounded-2xl shadow-google border border-gray-100 bg-white">
      <table class="w-full text-sm text-center">
        <thead>
          <tr class="bg-gray-900 text-white font-bold uppercase italic">
            <th class="p-5 border-r border-gray-800">Turno de Trabajo</th>
            <th class="p-5 border-r border-gray-800 bg-gray-800">Total Secciones</th>
            <th class="p-5 border-r border-gray-800 bg-cetpro">Ciclo Auxiliar Tecnico</th>
            <th class="p-5 bg-cetpro-dark">Ciclo Tecnico</th>
          </tr>
        </thead>
        <tbody class="divide-y text-lg font-black text-gray-700">
          <tr v-for="turno in ['Mañana', 'Tarde', 'Noche']" :key="turno" class="hover:bg-gray-50 group">
            <td class="p-6 text-left border-r uppercase bg-gray-50/50 group-hover:text-cetpro transition-colors italic">
              {{ turno }}
            </td>

            <td class="p-6 border-r text-2xl font-black">{{ tablaProcesada[turno].total }}</td>
            <td class="p-6 border-r text-cetpro font-black">{{ tablaProcesada[turno].basico }}</td>
            <td class="p-6 text-cetpro-dark font-black">{{ tablaProcesada[turno].medio }}</td>
          </tr>

          <tr class="bg-gray-800 text-white">
            <td class="p-6 text-left border-r font-black uppercase tracking-widest italic">Total General</td>
            <td class="p-6 border-r text-2xl">{{ totalGeneral.total }}</td>
            <td class="p-6 border-r">{{ totalGeneral.basico }}</td>
            <td class="p-6">{{ totalGeneral.medio }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 205 - Secciones por Turno"
      subtitle="Distribución total de secciones por turno"
      :series="chartSeries"
      @close="showChart = false"
    />
  </div>
</template>
