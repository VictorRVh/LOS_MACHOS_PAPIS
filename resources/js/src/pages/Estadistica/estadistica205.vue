<script setup>
import { ref, computed } from 'vue';
import useEstadistica205Store from '../../store/Estadisticas/Estadistica205Store';

const fechaInicio = ref('');
const fechaFin = ref('');

const estadisticaStore = useEstadistica205Store();

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

</script>

<template>
  <div class="p-6">
    <div class="flex items-center gap-4 mb-8">
      <div class="h-12 w-2 bg-cetpro-dark rounded-full shadow-google"></div>
      <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic">205. NÚMERO TOTAL DE SECCIONES,
        POR CICLO SEGÚN TURNO</h2>
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

            <td class="p-6 border-r text-2xl font-black">
              {{ tablaProcesada[turno].total }}
            </td>

            <td class="p-6 border-r text-cetpro font-black">
              {{ tablaProcesada[turno].basico }}
            </td>

            <td class="p-6 text-cetpro-dark font-black">
              {{ tablaProcesada[turno].medio }}
            </td>
          </tr>

          <tr class="bg-gray-800 text-white">
            <td class="p-6 text-left border-r font-black uppercase tracking-widest italic">
              Total General
            </td>
            <td class="p-6 border-r text-2xl">
              {{ totalGeneral.total }}
            </td>
            <td class="p-6 border-r">
              {{ totalGeneral.basico }}
            </td>
            <td class="p-6">
              {{ totalGeneral.medio }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>