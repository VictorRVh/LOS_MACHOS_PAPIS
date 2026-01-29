<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-black text-gray-800 uppercase italic">201. MATRÍCULA POR CICLO Y SEXO, SEGÚN EDAD</h2>
      <div class="bg-cetpro/10 px-4 py-2 rounded-lg border border-cetpro/20">
        <span class="text-cetpro font-bold">EDAD CUMPLIDA AL: 31-12-2025</span>
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
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-medium">
          <tr class="bg-gray-50 font-black text-cetpro">
            <td class="p-3 border-r uppercase italic">Total General</td>
            <td v-for="n in 6" :key="n" class="p-3 border-r last:border-0">0</td>
          </tr>
          <!-- Ciclo de edades -->
          <tr v-for="edad in ['12','13','14','15','16','17','18','19','20','21','22','23','24','25-29','30-34','35-39','40-44','45-49','50-54','55-59','60 y más']" 
              :key="edad" class="hover:bg-cetpro/5">
            <td class="p-2 border-r font-bold text-gray-700 bg-gray-50/50">{{ edad }} AÑOS</td>
            <td v-for="n in 6" :key="n" class="p-2 border-r last:border-0">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>