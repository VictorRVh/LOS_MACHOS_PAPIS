<script setup>
import { ref, computed } from 'vue';
import useEstadistica101Store from '../../store/Estadisticas/Estadistica101Store';
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from '../../composables/useModalToast';
import useExportEstadisticasExcel from '../../composables/estadisticas/useExportEstadisticasExcel';
import ChartDonutModal from '../../components/estadisticas/ChartDonutModal.vue';

const fechaInicio = ref('');
const fechaFin = ref('');

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
  <div class="p-6">

    <!-- TÍTULO Y EXCEL -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-extrabold text-gray-800">
        101. APROBADOS Y RETIRADOS SEGÚN CICLO Y SEXO
      </h2>

      <div class="flex items-center gap-2">
        <button @click="abrirGrafico"
          class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg font-bold shadow-google-sm hover:bg-sky-600 transition-all">
          GRAFICO
        </button>
        <button @click="exportarExcel101"
          class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 shadow-google-sm hover:bg-emerald-600 transition-all">
          EXPORTAR EXCEL
        </button>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl mb-6 border">
      <div>
        <label class="text-[10px] font-black uppercase">Fecha Inicio</label>
        <input type="date" v-model="fechaInicio" class="w-full shadow-google-sm rounded-lg p-2" />
      </div>

      <div>
        <label class="text-[10px] font-black uppercase">Fecha Fin</label>
        <input type="date" v-model="fechaFin" class="w-full shadow-google-sm rounded-lg p-2" />
      </div>

      <div class="flex items-end">
        <button @click="consultarDatos"
          class="bg-cetpro w-full text-white font-bold py-2 rounded-lg hover:bg-cetpro-dark shadow-google-sm transition-all">
          CONSULTAR DATOS
        </button>
      </div>
    </div>

    <!-- TABLA -->
    <div class="overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-800 text-white text-center">
            <th class="p-4 border-r">SITUACIÓN</th>
            <th class="p-4 border-r">TOTAL GENERAL</th>
            <th colspan="2" class="p-4 border-r bg-gray-700">TOTAL</th>
            <th colspan="2" class="p-4 border-r bg-cetpro">AUXILIAR TECNICO</th>
            <th colspan="2" class="p-4 bg-cetpro-dark">TECNICO</th>
          </tr>
          <tr class="bg-gray-100 text-gray-600">
            <th></th>
            <th></th>
            <th>H</th>
            <th>M</th>
            <th>H</th>
            <th>M</th>
            <th>H</th>
            <th>M</th>
          </tr>
        </thead>

        <tbody class="text-center font-medium">

          <!-- APROBADOS -->
          <tr>
            <td class="font-bold text-left">APROBADOS</td>

            <!-- TOTAL GENERAL -->
            <td class="font-black text-cetpro">
              {{ data.aprobados.total }}
            </td>

            <!-- TOTAL (H/M) -->
            <td>
              {{ data.aprobados.auxiliar_tecnico.H + data.aprobados.tecnico.H }}
            </td>
            <td>
              {{ data.aprobados.auxiliar_tecnico.M + data.aprobados.tecnico.M }}
            </td>

            <!-- BÁSICO -->
            <td>{{ data.aprobados.auxiliar_tecnico.H }}</td>
            <td>{{ data.aprobados.auxiliar_tecnico.M }}</td>

            <!-- MEDIO -->
            <td>{{ data.aprobados.tecnico.H }}</td>
            <td>{{ data.aprobados.tecnico.M }}</td>
          </tr>

          <!-- RETIRADOS -->
          <tr>
            <td class="font-bold text-left">RETIRADOS</td>

            <!-- TOTAL GENERAL -->
            <td class="font-black text-cetpro">
              {{ data.retirados.total }}
            </td>

            <!-- TOTAL (H/M) -->
            <td>
              {{ data.retirados.auxiliar_tecnico.H + data.retirados.tecnico.H }}
            </td>
            <td>
              {{ data.retirados.auxiliar_tecnico.M + data.retirados.tecnico.M }}
            </td>

            <!-- BÁSICO -->
            <td>{{ data.retirados.auxiliar_tecnico.H }}</td>
            <td>{{ data.retirados.auxiliar_tecnico.M }}</td>

            <!-- MEDIO -->
            <td>{{ data.retirados.tecnico.H }}</td>
            <td>{{ data.retirados.tecnico.M }}</td>
          </tr>

        </tbody>

      </table>
    </div>

    <ChartDonutModal
      :show="showChart"
      title="Gráfico 101 - Aprobados vs Retirados"
      subtitle="Distribución total por situación académica"
      :series="chartSeries"
      @close="showChart = false"
    />
  </div>
</template>
