<script setup>
import { ref, computed } from 'vue';
import useEstadistica202Store from '../../store/Estadisticas/Estadistica202Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica202Store();
const { showToast } = useModalToast();
const { addInstitutionalHeader } = useExportEstadisticasExcel();
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica202(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica202 || []);

const chartSeries = computed(() =>
  (data.value || []).map((item, idx) => ({
    label: String(item.nombre || 'OPCION').toUpperCase(),
    value:
      Number(item?.auxiliar_tecnico?.H || 0) +
      Number(item?.auxiliar_tecnico?.M || 0) +
      Number(item?.tecnico?.H || 0) +
      Number(item?.tecnico?.M || 0),
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

const exportarExcel202 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  try {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Reporte 202', { views: [{ state: 'frozen', ySplit: 9 }] });

    ws.columns = [
      { width: 8 }, { width: 36 }, { width: 9 }, { width: 9 },
      { width: 9 }, { width: 9 }, { width: 9 }, { width: 9 },
    ];

    const { startRow, applyThinBorder } = await addInstitutionalHeader(wb, ws, {
      reportTitle: 'REPORTE 202 - MATRICULA POR CICLO Y SEXO, SEGUN OPCIONES TECNICO PRODUCTIVAS',
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

    ws.getCell(`A${startRow}`).value = 'N°';
    ws.getCell(`B${startRow}`).value = 'CODIGO Y DENOMINACION';
    ws.getCell(`C${startRow}`).value = 'TOTAL';
    ws.getCell(`E${startRow}`).value = 'BASICO';
    ws.getCell(`G${startRow}`).value = 'MEDIO';

    ['A', 'B', 'C', 'E', 'G'].forEach((col) => {
      const c = ws.getCell(`${col}${startRow}`);
      c.font = { bold: true, italic: true, color: { argb: 'FFFFFFFF' } };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
      applyThinBorder(c);
    });

    ['C', 'D', 'E', 'F', 'G', 'H'].forEach((col, i) => {
      const c = ws.getCell(`${col}${startRow + 1}`);
      c.value = i % 2 === 0 ? 'H' : 'M';
      c.font = { bold: true, color: { argb: 'FF64748B' } };
      c.alignment = { horizontal: 'center', vertical: 'middle' };
      c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
      applyThinBorder(c);
    });

    let row = startRow + 2;
    data.value.forEach((item, index) => {
      const totalH = (item.auxiliar_tecnico?.H || 0) + (item.tecnico?.H || 0);
      const totalM = (item.auxiliar_tecnico?.M || 0) + (item.tecnico?.M || 0);
      ws.addRow([
        index + 1,
        item.nombre,
        totalH,
        totalM,
        item.auxiliar_tecnico?.H || 0,
        item.auxiliar_tecnico?.M || 0,
        item.tecnico?.H || 0,
        item.tecnico?.M || 0,
      ]);

      for (let c = 1; c <= 8; c++) {
        const cell = ws.getCell(row, c);
        cell.font = { bold: c === 2, color: { argb: 'FF0F172A' } };
        cell.alignment = { horizontal: c === 2 ? 'left' : 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: row % 2 === 0 ? 'FFFFFFFF' : 'FFF8FAFC' } };
        applyThinBorder(cell);
      }
      row++;
    });

    const buffer = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    }), `Reporte_202_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`);

    showToast('Excel 202 generado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo exportar el reporte 202.', 'error');
  }
};

</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6 gap-3">
      <h2 class="text-xl font-black text-gray-800 uppercase border-l-8 border-cetpro pl-4">
        202. MATRÍCULA POR CICLO Y SEXO, SEGÚN OPCIONES TÉCNICO PRODUCTIVAS
      </h2>
      <div class="flex items-center gap-2">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-sky-600 transition-all">
          GRAFICO
        </button>
        <button @click="exportarExcel202"
          class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-emerald-600 transition-all">
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

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 202 - Opciones Técnico Productivas"
      subtitle="Distribución total por opción"
      :series="chartSeries"
      @close="showChart = false"
    />


    <div class="overflow-x-auto rounded-xl shadow-google border border-gray-200">
      <table class="w-full text-xs text-center">
        <thead class="bg-gray-800 text-white uppercase tracking-widest">
          <tr>
            <th rowspan="2" class="p-4 border-r border-gray-700">N°</th>
            <th rowspan="2" class="p-4 border-r border-gray-700 text-left">Código y Denominación</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-gray-700">Total</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-cetpro">Básico</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-cetpro-dark">Medio</th>
          </tr>
          <tr class="bg-gray-100 text-gray-700 border-b font-black">
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <tr v-for="(item, index) in data" :key="index" class="hover:bg-gray-50 transition-all">
            <td class="p-3 border-r text-gray-400">{{ index + 1 }}</td>
            <td class="p-3 text-left border-r font-medium">{{ item.nombre }}</td>
            <td class="p-3 border-r text-gray-600">{{ (item.auxiliar_tecnico?.H || 0) + (item.tecnico?.H || 0) }}</td>
            <td class="p-3 border-r text-gray-600">{{ (item.auxiliar_tecnico?.M || 0) + (item.tecnico?.M || 0) }}</td>
            <td class="p-3 border-r text-gray-600">{{ item.auxiliar_tecnico?.H || 0 }}</td>
            <td class="p-3 border-r text-gray-600">{{ item.auxiliar_tecnico?.M || 0 }}</td>
            <td class="p-3 border-r text-gray-600">{{ item.tecnico?.H || 0 }}</td>
            <td class="p-3 text-gray-600">{{ item.tecnico?.M || 0 }}</td>
          </tr>

          <tr v-if="!data.length">
            <td colspan="8" class="p-4 text-gray-400">No hay datos disponibles</td>
          </tr>
        </tbody>

      </table>
    </div>
  </div>
</template>
