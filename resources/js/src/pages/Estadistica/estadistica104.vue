<script setup>
import { ref, computed } from 'vue';
import useEstadistica104Store from '../../store/Estadisticas/Estadistica104Store';
import useModalToast from '../../composables/useModalToast';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useHttpRequest from "../../composables/useHttpRequest";
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica104Store();
const { showToast } = useModalToast();
const { index: getCetproData } = useHttpRequest('/cetprodata');
const showChart = ref(false);

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica104(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica104 || []);

const totalGeneral = computed(() => ({
  totalMatricH: data.value.reduce((a, c) => a + c.total.matriculados.H, 0),
  totalMatricM: data.value.reduce((a, c) => a + c.total.matriculados.M, 0),
  totalRetH: data.value.reduce((a, c) => a + c.total.retirados.H, 0),
  totalRetM: data.value.reduce((a, c) => a + c.total.retirados.M, 0),
  basicoMatricH: data.value.reduce((a, c) => a + c.basico.matriculados.H, 0),
  basicoMatricM: data.value.reduce((a, c) => a + c.basico.matriculados.M, 0),
  basicoRetH: data.value.reduce((a, c) => a + c.basico.retirados.H, 0),
  basicoRetM: data.value.reduce((a, c) => a + c.basico.retirados.M, 0),
  medioMatricH: data.value.reduce((a, c) => a + c.medio.matriculados.H, 0),
  medioMatricM: data.value.reduce((a, c) => a + c.medio.matriculados.M, 0),
  medioRetH: data.value.reduce((a, c) => a + c.medio.retirados.H, 0),
  medioRetM: data.value.reduce((a, c) => a + c.medio.retirados.M, 0),
}));

const chartSeries = computed(() =>
  (data.value || []).map((item, idx) => ({
    label: String(item.nombre || 'CARRERA').toUpperCase(),
    value:
      Number(item?.total?.matriculados?.H || 0) +
      Number(item?.total?.matriculados?.M || 0) +
      Number(item?.total?.retirados?.H || 0) +
      Number(item?.total?.retirados?.M || 0),
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

const aplicarBorde = (cell) => {
  cell.border = {
    top: { style: 'thin', color: { argb: 'FFC8CDD4' } },
    left: { style: 'thin', color: { argb: 'FFC8CDD4' } },
    bottom: { style: 'thin', color: { argb: 'FFC8CDD4' } },
    right: { style: 'thin', color: { argb: 'FFC8CDD4' } },
  };
};

const styleHeaderGroup = (cell, color) => {
  cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, italic: true, size: 11 };
  cell.alignment = { horizontal: 'center', vertical: 'middle' };
  cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: color } };
  aplicarBorde(cell);
};

const styleHeaderSub = (cell, danger = false) => {
  cell.font = { bold: true, color: { argb: danger ? 'FFE11D48' : 'FF1F2937' }, size: 10 };
  cell.alignment = { horizontal: 'center', vertical: 'middle' };
  cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF3F4F6' } };
  aplicarBorde(cell);
};

const styleHeaderSex = (cell) => {
  cell.font = { bold: true, color: { argb: 'FF64748B' }, size: 10 };
  cell.alignment = { horizontal: 'center', vertical: 'middle' };
  cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
  aplicarBorde(cell);
};

const aplicarBloqueInstitucional = (cell, bold = false) => {
  cell.font = { bold, size: 10.5, color: { argb: 'FF0F172A' } };
  cell.alignment = { horizontal: 'left', vertical: 'middle' };
  cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
  aplicarBorde(cell);
};

const fetchImageBase64 = async (url) => {
  const response = await fetch(url);
  if (!response.ok) return null;
  const blob = await response.blob();
  return await new Promise((resolve) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve(reader.result);
    reader.onerror = () => resolve(null);
    reader.readAsDataURL(blob);
  });
};

const exportarReporte104 = async () => {
  if (!fechaInicio.value || !fechaFin.value) {
    showToast('Primero selecciona el rango de fechas.', 'warning');
    return;
  }

  if (!data.value.length) {
    showToast('Primero consulta datos para poder exportar.', 'warning');
    return;
  }

  try {
    const cetpro = await getCetproData();
    const nombreCetproRaw = String(cetpro?.cetpro || 'PUNO').trim().toUpperCase();
    const nombreCetpro = nombreCetproRaw.startsWith('CETPRO ')
      ? nombreCetproRaw
      : `CETPRO ${nombreCetproRaw}`;

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'CETPRO';
    workbook.lastModifiedBy = 'Sistema CETPRO';
    workbook.created = new Date();

    const ws = workbook.addWorksheet('Reporte 104', {
      views: [{ state: 'frozen', ySplit: 12 }],
    });

    ws.columns = [
      { width: 10 }, // codigo
      { width: 36 }, // denominacion
      { width: 8 }, { width: 8 }, { width: 8 }, { width: 8 },
      { width: 8 }, { width: 8 }, { width: 8 }, { width: 8 },
      { width: 8 }, { width: 8 }, { width: 8 }, { width: 8 },
    ];

    ws.mergeCells('A1:N1');
    const title = ws.getCell('A1');
    title.value = 'REPORTE 104 - MATRICULADOS Y RETIRADOS POR CARRERA';
    title.font = { bold: true, size: 16, color: { argb: 'FFFFFFFF' } };
    title.alignment = { horizontal: 'center', vertical: 'middle' };
    title.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F2747' } };

    ws.mergeCells('A2:N2');
    const subtitle = ws.getCell('A2');
    subtitle.value = `${nombreCetpro} | Fecha inicio: ${fechaInicio.value || '-'} | Fecha fin: ${fechaFin.value || '-'} | Generado: ${new Date().toLocaleString('es-PE')}`;
    subtitle.font = { size: 10, color: { argb: 'FF334155' } };
    subtitle.alignment = { horizontal: 'center', vertical: 'middle' };
    subtitle.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };

    ws.getRow(1).height = 28;
    ws.getRow(2).height = 20;

    ws.mergeCells('A3:J3');
    ws.getCell('A3').value = `Emitido por: ${nombreCetpro}`;
    aplicarBloqueInstitucional(ws.getCell('A3'), true);
    applyRangeBorder(ws, 'A3', 'J3');

    ws.mergeCells('A4:J4');
    ws.getCell('A4').value = `Tipo de gestión: ${cetpro?.tipo_gestion || '-'} | UGEL: ${cetpro?.ugel || '-'} | DRE: ${cetpro?.dre || '-'}`;
    aplicarBloqueInstitucional(ws.getCell('A4'));
    applyRangeBorder(ws, 'A4', 'J4');

    ws.mergeCells('A5:J5');
    ws.getCell('A5').value = `Ubicación: ${cetpro?.region || '-'} / ${cetpro?.provincia || '-'} / ${cetpro?.distrito || '-'} | Dirección: ${cetpro?.direccion || '-'} | Año: ${cetpro?.anio || '-'}`;
    aplicarBloqueInstitucional(ws.getCell('A5'));
    applyRangeBorder(ws, 'A5', 'J5');

    ws.mergeCells('K3:N5');
    const logoBox = ws.getCell('K3');
    logoBox.value = '';
    logoBox.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
    logoBox.alignment = { horizontal: 'center', vertical: 'middle' };
    applyRangeBorder(ws, 'K3', 'N5');

    ws.getRow(3).height = 24;
    ws.getRow(4).height = 22;
    ws.getRow(5).height = 22;

    const logoBase64 = await fetchImageBase64('/img/cetproLOGOO.png');
    if (logoBase64) {
      const logoId = workbook.addImage({
        base64: logoBase64,
        extension: 'png',
      });
      ws.addImage(logoId, {
        tl: { col: 11.35, row: 2.12 },
        ext: { width: 72, height: 80 },
      });
    }

    ws.mergeCells('A7:A9');
    ws.mergeCells('B7:B9');
    ws.mergeCells('C7:F7');
    ws.mergeCells('G7:J7');
    ws.mergeCells('K7:N7');

    ws.getCell('A7').value = 'CÓDIGO';
    ws.getCell('B7').value = 'DENOMINACIÓN DE LA CARRERA';
    ws.getCell('C7').value = 'TOTAL GENERAL';
    ws.getCell('G7').value = 'CICLO AUXILIAR TÉCNICO';
    ws.getCell('K7').value = 'CICLO TÉCNICO';

    ['A7', 'B7'].forEach((addr) => styleHeaderGroup(ws.getCell(addr), 'FF1E293B'));
    styleHeaderGroup(ws.getCell('C7'), 'FF334155');
    styleHeaderGroup(ws.getCell('G7'), 'FF0369A1');
    styleHeaderGroup(ws.getCell('K7'), 'FF075985');

    ws.mergeCells('C8:D8'); ws.mergeCells('E8:F8');
    ws.mergeCells('G8:H8'); ws.mergeCells('I8:J8');
    ws.mergeCells('K8:L8'); ws.mergeCells('M8:N8');

    ws.getCell('C8').value = 'Matric.'; ws.getCell('E8').value = 'Retir.';
    ws.getCell('G8').value = 'Matric.'; ws.getCell('I8').value = 'Retir.';
    ws.getCell('K8').value = 'Matric.'; ws.getCell('M8').value = 'Retir.';

    ['C8', 'G8', 'K8'].forEach((addr) => styleHeaderSub(ws.getCell(addr), false));
    ['E8', 'I8', 'M8'].forEach((addr) => styleHeaderSub(ws.getCell(addr), true));

    const sexHeaders = ['C9', 'D9', 'E9', 'F9', 'G9', 'H9', 'I9', 'J9', 'K9', 'L9', 'M9', 'N9'];
    sexHeaders.forEach((addr, idx) => {
      ws.getCell(addr).value = idx % 2 === 0 ? 'H' : 'M';
      styleHeaderSex(ws.getCell(addr));
    });

    const total = totalGeneral.value;
    ws.mergeCells('A10:B10');
    ws.getCell('A10').value = 'GRAN TOTAL';
    ws.getCell('A10').font = { bold: true, italic: true, size: 11, color: { argb: 'FF075985' } };
    ws.getCell('A10').alignment = { horizontal: 'right', vertical: 'middle' };
    ws.getCell('A10').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE6F3FA' } };
    applyRangeBorder(ws, 'A10', 'N10');

    const totalValues = [
      total.totalMatricH, total.totalMatricM, total.totalRetH, total.totalRetM,
      total.basicoMatricH, total.basicoMatricM, total.basicoRetH, total.basicoRetM,
      total.medioMatricH, total.medioMatricM, total.medioRetH, total.medioRetM,
    ];
    totalValues.forEach((v, i) => {
      const cell = ws.getCell(10, 3 + i);
      cell.value = v;
      cell.font = { bold: true, size: 11, color: { argb: 'FF075985' } };
      cell.alignment = { horizontal: 'center', vertical: 'middle' };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE6F3FA' } };
      aplicarBorde(cell);
    });

    let row = 11;
    data.value.forEach((item, index) => {
      const values = [
        String(index + 1).padStart(3, '0'),
        item.nombre,
        item.total.matriculados.H,
        item.total.matriculados.M,
        item.total.retirados.H,
        item.total.retirados.M,
        item.basico.matriculados.H,
        item.basico.matriculados.M,
        item.basico.retirados.H,
        item.basico.retirados.M,
        item.medio.matriculados.H,
        item.medio.matriculados.M,
        item.medio.retirados.H,
        item.medio.retirados.M,
      ];

      ws.addRow(values);
      for (let col = 1; col <= 14; col++) {
        const cell = ws.getCell(row, col);
        const alt = row % 2 === 0;
        cell.fill = {
          type: 'pattern',
          pattern: 'solid',
          fgColor: { argb: alt ? 'FFFFFFFF' : 'FFF8FAFC' },
        };
        cell.font = {
          size: 10,
          color: { argb: col === 1 ? 'FF64748B' : 'FF0F172A' },
          bold: col === 2,
        };
        cell.alignment = { horizontal: col === 2 ? 'left' : 'center', vertical: 'middle' };
        aplicarBorde(cell);
      }
      row++;
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    });
    const file = `Reporte_104_${fechaInicio.value || 'sin-inicio'}_${fechaFin.value || 'sin-fin'}.xlsx`;
    saveAs(blob, file);
    showToast('Reporte 104 exportado correctamente.', 'success');
  } catch (error) {
    console.error(error);
    showToast('No se pudo generar el reporte Excel.', 'error');
  }
};

function applyRangeBorder(ws, startAddr, endAddr) {
  const start = ws.getCell(startAddr);
  const end = ws.getCell(endAddr);
  for (let r = start.row; r <= end.row; r++) {
    for (let c = start.col; c <= end.col; c++) {
      aplicarBorde(ws.getCell(r, c));
    }
  }
}

</script>


<template>
  <div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div>
        <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">104. MATRICULADOS Y RETIRADOS POR
          CARRERA</h2>
        <p class="text-sm text-gray-500 font-medium">Reporte detallado por denominación de especialidad técnica.</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-5 py-2.5 rounded-lg font-bold shadow-google hover:bg-sky-600 transition-all active:scale-95">
          GRAFICO
        </button>
        <button @click="exportarReporte104"
          class="bg-[#10b981] text-white px-5 py-2.5 rounded-lg font-bold flex items-center gap-2 shadow-google hover:bg-emerald-600 transition-all active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
              clip-rule="evenodd" />
          </svg>
          DESCARGAR REPORTE
        </button>
      </div>
    </div>

    <!-- Filtros -->
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

    <!-- Tabla -->
    <div class="overflow-x-auto rounded-xl shadow-google border border-gray-200 bg-white">
      <table class="w-full text-xs text-center border-collapse">
        <thead>
          <tr class="bg-gray-800 text-white uppercase italic">
            <th rowspan="3" class="p-3 border-r border-gray-700">Código</th>
            <th rowspan="3" class="p-3 border-r border-gray-700 text-left">Denominación de la Carrera</th>
            <th colspan="4" class="p-2 border-b border-r border-gray-700 bg-gray-700">Total General</th>
            <th colspan="4" class="p-2 border-b border-r border-gray-700 bg-cetpro">Ciclo Auxiliar Tecnico</th>
            <th colspan="4" class="p-2 bg-cetpro-dark border-b border-gray-700">Ciclo Tecnico</th>
          </tr>
          <tr class="bg-gray-100 text-gray-700 font-bold border-b border-gray-200">
            <th colspan="2" class="p-1 border-r">Matric.</th>
            <th colspan="2" class="p-1 border-r text-red-600">Retir.</th>
            <th colspan="2" class="p-1 border-r">Matric.</th>
            <th colspan="2" class="p-1 border-r text-red-600">Retir.</th>
            <th colspan="2" class="p-1 border-r">Matric.</th>
            <th colspan="2" class="p-1 text-red-600">Retir.</th>
          </tr>
          <tr class="bg-gray-50 text-[10px] text-gray-500 border-b">
            <th class="p-1 border-r">H</th>
            <th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th>
            <th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th>
            <th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th>
            <th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th>
            <th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th>
            <th class="p-1">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <!-- GRAN TOTAL -->
          <tr class="bg-cetpro/5 text-cetpro font-black">
            <td colspan="2" class="p-3 text-right border-r uppercase italic">
              Gran Total
            </td>

            <!-- Total General -->
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.total.matriculados.H, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.total.matriculados.M, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.total.retirados.H, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.total.retirados.M, 0)}}</td>

            <!-- Básico -->
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.basico.matriculados.H, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.basico.matriculados.M, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.basico.retirados.H, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.basico.retirados.M, 0)}}</td>

            <!-- Medio -->
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.medio.matriculados.H, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.medio.matriculados.M, 0)}}</td>
            <td class="p-2 border-r">{{data.reduce((a, c) => a + c.medio.retirados.H, 0)}}</td>
            <td class="p-2">{{data.reduce((a, c) => a + c.medio.retirados.M, 0)}}</td>
          </tr>

          <!-- FILAS DINÁMICAS -->
          <tr v-for="(item, index) in data" :key="index" class="hover:bg-gray-50 transition-colors">
            <td class="p-3 border-r text-gray-400">
              {{ (index + 1).toString().padStart(3, '0') }}
            </td>

            <td class="p-3 text-left border-r font-medium">
              {{ item.nombre }}
            </td>

            <!-- Total -->
            <td class="p-2 border-r">{{ item.total.matriculados.H }}</td>
            <td class="p-2 border-r">{{ item.total.matriculados.M }}</td>
            <td class="p-2 border-r">{{ item.total.retirados.H }}</td>
            <td class="p-2 border-r">{{ item.total.retirados.M }}</td>

            <!-- Básico -->
            <td class="p-2 border-r">{{ item.basico.matriculados.H }}</td>
            <td class="p-2 border-r">{{ item.basico.matriculados.M }}</td>
            <td class="p-2 border-r">{{ item.basico.retirados.H }}</td>
            <td class="p-2 border-r">{{ item.basico.retirados.M }}</td>

            <!-- Medio -->
            <td class="p-2 border-r">{{ item.medio.matriculados.H }}</td>
            <td class="p-2 border-r">{{ item.medio.matriculados.M }}</td>
            <td class="p-2 border-r">{{ item.medio.retirados.H }}</td>
            <td class="p-2">{{ item.medio.retirados.M }}</td>
          </tr>
        </tbody>

      </table>
    </div>

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 104 - Matriculados y Retirados por Carrera"
      subtitle="Distribución total por denominación de carrera"
      :series="chartSeries"
      @close="showChart = false"
    />
  </div>
</template>
