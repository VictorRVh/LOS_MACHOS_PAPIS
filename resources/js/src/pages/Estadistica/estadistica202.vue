<template>
  <div class="p-6">
    <h2 class="text-xl font-black text-gray-800 mb-6 uppercase border-l-8 border-cetpro pl-4">
      202. MATRÍCULA POR CICLO Y SEXO, SEGÚN OPCIONES TÉCNICO PRODUCTIVAS
    </h2>
    
    <div class="overflow-x-auto rounded-xl shadow-google border border-gray-200">
      <table class="w-full text-xs text-center">
        <thead class="bg-gray-800 text-white uppercase tracking-widest">
          <tr>
            <th rowspan="2" class="p-4 border-r border-gray-700">N°</th>
            <th rowspan="2" class="p-4 border-r border-gray-700 text-left">Código y Denominación</th>
            <th rowspan="2" class="p-4 border-r border-gray-700">Ciclo</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-gray-700">Total</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-cetpro">Básico</th>
            <th colspan="2" class="p-2 border-b border-gray-700 bg-cetpro-dark">Medio</th>
          </tr>
          <tr class="bg-gray-100 text-gray-700 border-b font-black">
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <tr v-for="i in 10" :key="i" class="hover:bg-gray-50 transition-all">
            <td class="p-3 border-r text-gray-400">{{ i }}</td>
            <td class="p-3 text-left border-r font-medium">000000: DENOMINACIÓN DE OPCIÓN {{ i }}</td>
            <td class="p-3 border-r italic text-cetpro">MEDIO</td>
            <td v-for="n in 6" :key="n" class="p-3 border-r last:border-0 text-gray-600">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>