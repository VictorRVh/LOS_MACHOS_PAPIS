<script setup>
import { ref, computed } from 'vue';
import useEstadistica203Store from '../../store/Estadisticas/Estadistica203Store';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica203Store()

const consultarDatos = () => {
  if (!fechaInicio.value || !fechaFin.value) return;
  estadisticaStore.loadEstadistica203(
    fechaInicio.value,
    fechaFin.value
  );
};

const data = computed(() => estadisticaStore.estadistica203 || []);

</script>

<template>
  <div class="p-6">
    <div class="bg-cetpro p-4 rounded-t-xl shadow-google-sm">
      <h2 class="text-white font-black uppercase text-center tracking-widest">203. MATRÍCULA POR CICLO Y SEXO, SEGÚN
        NIVEL EDUCATIVO</h2>
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
          <tr v-for="item in data" :key="data.nivel" class="hover:bg-cetpro/5">
            <td class="p-4 text-left border-r font-black text-gray-700">
              {{ item.nivel.toUpperCase() }}
            </td>

            <!-- TOTAL -->
            <td class="p-4 border-r">{{ item.total.H }}</td>
            <td class="p-4 border-r">{{ item.total.M }}</td>

            <!-- BÁSICO (auxiliar_tecnico) -->
            <td class="p-4 border-r">{{ item.auxiliar_tecnico.H }}</td>
            <td class="p-4 border-r">{{ item.auxiliar_tecnico.M }}</td>

            <!-- MEDIO (tecnico) -->
            <td class="p-4 border-r">{{ item.tecnico.H }}</td>
            <td class="p-4">{{ item.tecnico.M }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>