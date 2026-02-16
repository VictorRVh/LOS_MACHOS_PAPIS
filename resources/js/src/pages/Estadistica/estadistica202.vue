<script setup>
import { ref, computed } from 'vue';
import useEstadistica202Store from '../../store/Estadisticas/Estadistica202Store';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica202Store();

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica202(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica202 || []);

</script>

<template>
  <div class="p-6">
    <h2 class="text-xl font-black text-gray-800 mb-6 uppercase border-l-8 border-cetpro pl-4">
      202. MATRÍCULA POR CICLO Y SEXO, SEGÚN OPCIONES TÉCNICO PRODUCTIVAS
    </h2>

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
            <!-- N° -->
            <td class="p-3 border-r text-gray-400">
              {{ index + 1 }}
            </td>

            <!-- Nombre -->
            <td class="p-3 text-left border-r font-medium">
              {{ item.nombre }}
            </td>

            <!-- TOTAL H -->
            <td class="p-3 border-r text-gray-600">
              {{ (item.auxiliar_tecnico?.H || 0) + (item.tecnico?.H || 0) }}
            </td>

            <!-- TOTAL M -->
            <td class="p-3 border-r text-gray-600">
              {{ (item.auxiliar_tecnico?.M || 0) + (item.tecnico?.M || 0) }}
            </td>

            <!-- BÁSICO H -->
            <td class="p-3 border-r text-gray-600">
              {{ item.auxiliar_tecnico?.H || 0 }}
            </td>

            <!-- BÁSICO M -->
            <td class="p-3 border-r text-gray-600">
              {{ item.auxiliar_tecnico?.M || 0 }}
            </td>

            <!-- MEDIO H -->
            <td class="p-3 border-r text-gray-600">
              {{ item.tecnico?.H || 0 }}
            </td>

            <!-- MEDIO M -->
            <td class="p-3 text-gray-600">
              {{ item.tecnico?.M || 0 }}
            </td>
          </tr>

          <!-- Mensaje si no hay datos -->
          <tr v-if="!data.length">
            <td colspan="8" class="p-4 text-gray-400">
              No hay datos disponibles
            </td>
          </tr>
        </tbody>

      </table>
    </div>
  </div>
</template>