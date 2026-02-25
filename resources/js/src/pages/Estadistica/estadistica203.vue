<script setup>
import { ref, computed } from 'vue';
import useEstadistica203Store from '../../store/Estadisticas/Estadistica203Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

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
  <div class="p-6">
    <div class="flex justify-between items-center gap-3 mb-6">
      <div class="bg-cetpro p-4 rounded-t-xl shadow-google-sm flex-1">
        <h2 class="text-white font-black uppercase text-center tracking-widest">203. MATRÍCULA POR CICLO Y SEXO, SEGÚN
          NIVEL EDUCATIVO</h2>
      </div>
      <div class="flex items-center gap-2">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-sky-600 transition-all whitespace-nowrap">
          GRAFICO
        </button>
        <button @click="exportarExcel203"
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

    <div class="overflow-x-auto shadow-google border border-gray-200 rounded-b-xl">
      <table class="w-full text-sm text-center bg-white">
        <thead>
          <tr class="bg-gray-100 text-gray-800 uppercase font-black border-b">
            <th rowspan="2" class="p-4 border-r text-left">Nivel Educativo de los Participantes</th>
            <th colspan="2" class="p-2 border-b border-r">Total</th>
            <th colspan="2" class="p-2 border-b border-r bg-gray-200/50">Básico</th>
            <th colspan="2" class="p-2 border-b bg-gray-200/50">Medio</th>
          </tr>
          <tr class="bg-gray-50 text-gray-500 border-b">
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <tr v-for="item in data" :key="item.nivel" class="hover:bg-cetpro/5">
            <td class="p-4 text-left border-r font-black text-gray-700">{{ item.nivel.toUpperCase() }}</td>
            <td class="p-4 border-r">{{ item.total.H }}</td>
            <td class="p-4 border-r">{{ item.total.M }}</td>
            <td class="p-4 border-r">{{ item.auxiliar_tecnico.H }}</td>
            <td class="p-4 border-r">{{ item.auxiliar_tecnico.M }}</td>
            <td class="p-4 border-r">{{ item.tecnico.H }}</td>
            <td class="p-4">{{ item.tecnico.M }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 203 - Nivel Educativo"
      subtitle="Distribución de matrícula total por nivel educativo"
      :series="chartSeries"
      @close="showChart = false"
    />
  </div>
</template>
