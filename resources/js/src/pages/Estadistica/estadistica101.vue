<script setup>
import { ref, computed } from 'vue';
import useEstadistica101Store from '../../store/Estadisticas/Estadistica101Store';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica101Store();

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica101(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica101 || {
  total_matriculados: {
    general: { H: 0, M: 0 },
    basico: { H: 0, M: 0 },
    medio: { H: 0, M: 0 }
  },
  aprobados: {
    general: { H: 0, M: 0 },
    basico: { H: 0, M: 0 },
    medio: { H: 0, M: 0 }
  },
  retirados: {
    general: { H: 0, M: 0 },
    basico: { H: 0, M: 0 },
    medio: { H: 0, M: 0 }
  }
});
</script>

<template>
  <div class="p-6">

    <!-- TÍTULO Y EXCEL -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-extrabold text-gray-800">
        101. APROBADOS Y RETIRADOS SEGÚN CICLO Y SEXO
      </h2>

      <button
        class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 shadow-google-sm hover:bg-emerald-600 transition-all">
        EXPORTAR EXCEL
      </button>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl mb-6 border">
      <div>
        <label class="text-[10px] font-black uppercase">Fecha Inicio</label>
        <input
          type="date"
          v-model="fechaInicio"
          class="w-full shadow-google-sm rounded-lg p-2"
        />
      </div>

      <div>
        <label class="text-[10px] font-black uppercase">Fecha Fin</label>
        <input
          type="date"
          v-model="fechaFin"
          class="w-full shadow-google-sm rounded-lg p-2"
        />
      </div>

      <div class="flex items-end">
        <button
          @click="consultarDatos"
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
            <th colspan="2" class="p-4 border-r bg-cetpro">BÁSICO</th>
            <th colspan="2" class="p-4 bg-cetpro-dark">MEDIO</th>
          </tr>
          <tr class="bg-gray-100 text-gray-600">
            <th></th>
            <th></th>
            <th>H</th><th>M</th>
            <th>H</th><th>M</th>
            <th>H</th><th>M</th>
          </tr>
        </thead>

        <tbody class="text-center font-medium">

          <!-- TOTAL MATRICULADOS -->
          <tr>
            <td class="font-bold text-left">TOTAL MATRICULADOS</td>
            <td class="font-black text-cetpro">
              {{ data.total_matriculados?.general?.H + data.total_matriculados?.general?.M }}
            </td>
            <td>{{ data.total_matriculados?.general?.H }}</td>
            <td>{{ data.total_matriculados?.general?.M }}</td>
            <td>{{ data.total_matriculados?.basico?.H }}</td>
            <td>{{ data.total_matriculados?.basico?.M }}</td>
            <td>{{ data.total_matriculados?.medio?.H }}</td>
            <td>{{ data.total_matriculados?.medio?.M }}</td>
          </tr>

          <!-- APROBADOS -->
          <tr>
            <td class="font-bold text-left">APROBADOS</td>
            <td class="font-black text-cetpro">
              {{ data.aprobados?.general?.H + data.aprobados?.general?.M }}
            </td>
            <td>{{ data.aprobados?.general?.H }}</td>
            <td>{{ data.aprobados?.general?.M }}</td>
            <td>{{ data.aprobados?.basico?.H }}</td>
            <td>{{ data.aprobados?.basico?.M }}</td>
            <td>{{ data.aprobados?.medio?.H }}</td>
            <td>{{ data.aprobados?.medio?.M }}</td>
          </tr>

          <!-- RETIRADOS -->
          <tr>
            <td class="font-bold text-left">RETIRADOS</td>
            <td class="font-black text-cetpro">
              {{ data.retirados?.general?.H + data.retirados?.general?.M }}
            </td>
            <td>{{ data.retirados?.general?.H }}</td>
            <td>{{ data.retirados?.general?.M }}</td>
            <td>{{ data.retirados?.basico?.H }}</td>
            <td>{{ data.retirados?.basico?.M }}</td>
            <td>{{ data.retirados?.medio?.H }}</td>
            <td>{{ data.retirados?.medio?.M }}</td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>
</template>
