<script setup>
import { ref, computed } from 'vue';
import useEstadistica201Store from '../../store/Estadisticas/Estadistica201Store';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica201Store();

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

</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-black text-gray-800 uppercase italic">201. MATRÍCULA POR CICLO Y SEXO, SEGÚN EDAD</h2>
      <div class="bg-cetpro/10 px-4 py-2 rounded-lg border border-cetpro/20">
        <span class="text-cetpro font-bold">EDAD CUMPLIDA AL: 31-12-2025</span>
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
          <!-- TOTAL GENERAL -->
          <tr v-if="totalGeneral" class="bg-gray-50 font-black text-cetpro">
            <td class="p-3 border-r uppercase italic">
              {{ totalGeneral.edad }}
            </td>

            <!-- TOTAL -->
            <td class="p-3 border-r">
              {{ totalGeneral.total.H }}
            </td>
            <td class="p-3 border-r">
              {{ totalGeneral.total.M }}
            </td>

            <!-- CICLO BÁSICO -->
            <td class="p-3 border-r">
              {{ totalGeneral.auxiliar_tecnico.H }}
            </td>
            <td class="p-3 border-r">
              {{ totalGeneral.auxiliar_tecnico.M }}
            </td>

            <!-- CICLO MEDIO -->
            <td class="p-3 border-r">
              {{ totalGeneral.tecnico.H }}
            </td>
            <td class="p-3">
              {{ totalGeneral.tecnico.M }}
            </td>
          </tr>

          <!-- EDADES -->
          <tr v-for="edad in edades" :key="edad.edad" class="hover:bg-cetpro/5">
            <td class="p-2 border-r font-bold text-gray-700 bg-gray-50/50">
              {{ edad.edad }}
            </td>

            <!-- TOTAL -->
            <td class="p-2 border-r">
              {{ edad.total.H }}
            </td>
            <td class="p-2 border-r">
              {{ edad.total.M }}
            </td>

            <!-- CICLO BÁSICO -->
            <td class="p-2 border-r">
              {{ edad.auxiliar_tecnico.H }}
            </td>
            <td class="p-2 border-r">
              {{ edad.auxiliar_tecnico.M }}
            </td>

            <!-- CICLO MEDIO -->
            <td class="p-2 border-r">
              {{ edad.tecnico.H }}
            </td>
            <td class="p-2">
              {{ edad.tecnico.M }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>