<script setup>
import { ref, computed } from 'vue';
import useEstadistica201Store from '../../store/Estadisticas/Estadistica201Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica201Store();
const { showToast } = useModalToast();
const { addInstitutionalHeader } = useExportEstadisticasExcel();
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica201(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica201 || []);

const totalGeneral = computed(() =>
  data.value.find(item => item.edad === 'TOTAL GENERAL')
);

const edades = computed(() =>
  data.value.filter(item => item.edad !== 'TOTAL GENERAL')
);

const chartSeries = computed(() =>
  (edades.value || []).map((item, idx) => ({
    label: String(item.edad || 'EDAD'),
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

const exportarExcel201 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  try {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Reporte 201', { views: [{ state: 'frozen', ySplit: 9 }] });

    ws.columns = [
      { width: 26 }, { width: 9 }, { width: 9 }, { width: 9 },
      { width: 9 }, { width: 9 }, { width: 9 },
    ];

    const { startRow, applyThinBorder } = await addInstitutionalHeader(wb, ws, {
      reportTitle: 'REPORTE 201 - MATRICULA POR CICLO Y SEXO, SEGUN EDAD',
      fechaInicio: fechaInicio.value,
      fechaFin: fechaFin.value,
      totalCols: 7,
      logoCols: 2,
    });

    ws.mergeCells(`A${startRow}:A${startRow + 1}`);
    ws.mergeCells(`B${startRow}:C${startRow}`);
    ws.mergeCells(`D${startRow}:E${startRow}`);
    ws.mergeCells(`F${startRow}:G${startRow}`);

    ws.getCell(`A${startRow}`).value = 'EDAD EN ANOS CUMPLIDOS';
    ws.getCell(`B${startRow}`).value = 'TOTAL';
    ws.getCell(`D${startRow}`).value = 'CICLO AUXILIAR TECNICO';
    ws.getCell(`F${startRow}`).value = 'CICLO TECNICO';

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
    if (totalGeneral.value) {
      ws.addRow([
        'TOTAL GENERAL',
        totalGeneral.value.total.H,
        totalGeneral.value.total.M,
        totalGeneral.value.auxiliar_tecnico.H,
        totalGeneral.value.auxiliar_tecnico.M,
        totalGeneral.value.tecnico.H,
        totalGeneral.value.tecnico.M,
      ]);
      for (let c = 1; c <= 7; c++) {
        const cell = ws.getCell(row, c);
        cell.font = { bold: true, color: { argb: 'FF075985' } };
        cell.alignment = { horizontal: c === 1 ? 'left' : 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE6F3FA' } };
        applyThinBorder(cell);
      }
      row++;
    }

    edades.value.forEach((item) => {
      ws.addRow([
        item.edad,
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
    }), `Reporte_201_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`);

    showToast('Excel 201 generado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo exportar el reporte 201.', 'error');
  }
};

</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6 gap-3">
      <h2 class="text-xl font-black text-gray-800 uppercase italic">201. MATRICULA POR CICLO Y SEXO, SEGUN EDAD</h2>
      <div class="flex items-center gap-3">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-sky-600 transition-all">
          GRAFICO
        </button>
        <button @click="exportarExcel201"
          class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-emerald-600 transition-all">
          EXPORTAR EXCEL
        </button>
        <div class="bg-cetpro/10 px-4 py-2 rounded-lg border border-cetpro/20">
          <span class="text-cetpro font-bold">EDAD CUMPLIDA AL: 31-12-2025</span>
        </div>
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

    <div class="overflow-x-auto rounded-xl shadow-google border border-gray-200 bg-white">
      <table class="w-full text-sm text-center">
        <thead>
          <tr class="bg-cetpro text-white uppercase">
            <th rowspan="2" class="p-4 border-r border-cetpro-dark">Edad en años cumplidos</th>
            <th colspan="2" class="p-2 border-b border-cetpro-dark bg-cetpro-dark">Total</th>
            <th colspan="2" class="p-2 border-b border-cetpro-dark">Ciclo Básico</th>
            <th colspan="2" class="p-2 border-b">Ciclo Medio</th>
          </tr>
          <tr class="bg-gray-100 text-gray-600 font-bold border-b">
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th>
            <th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-medium">
          <tr v-if="totalGeneral" class="bg-gray-50 font-black text-cetpro">
            <td class="p-3 border-r uppercase italic">{{ totalGeneral.edad }}</td>
            <td class="p-3 border-r">{{ totalGeneral.total.H }}</td>
            <td class="p-3 border-r">{{ totalGeneral.total.M }}</td>
            <td class="p-3 border-r">{{ totalGeneral.auxiliar_tecnico.H }}</td>
            <td class="p-3 border-r">{{ totalGeneral.auxiliar_tecnico.M }}</td>
            <td class="p-3 border-r">{{ totalGeneral.tecnico.H }}</td>
            <td class="p-3">{{ totalGeneral.tecnico.M }}</td>
          </tr>

          <tr v-for="edad in edades" :key="edad.edad" class="hover:bg-cetpro/5">
            <td class="p-2 border-r font-bold text-gray-700 bg-gray-50/50">{{ edad.edad }}</td>
            <td class="p-2 border-r">{{ edad.total.H }}</td>
            <td class="p-2 border-r">{{ edad.total.M }}</td>
            <td class="p-2 border-r">{{ edad.auxiliar_tecnico.H }}</td>
            <td class="p-2 border-r">{{ edad.auxiliar_tecnico.M }}</td>
            <td class="p-2 border-r">{{ edad.tecnico.H }}</td>
            <td class="p-2">{{ edad.tecnico.M }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 201 - Matrícula por Edad"
      subtitle="Distribución total por edad"
      :series="chartSeries"
      @close="showChart = false"
    />
  </div>
</template>
