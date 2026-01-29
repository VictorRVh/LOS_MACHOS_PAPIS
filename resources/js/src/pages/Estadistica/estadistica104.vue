<template>
  <div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div>
        <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">104. MATRICULADOS Y RETIRADOS POR CARRERA</h2>
        <p class="text-sm text-gray-500 font-medium">Reporte detallado por denominación de especialidad técnica.</p>
      </div>
      <button class="bg-[#10b981] text-white px-5 py-2.5 rounded-lg font-bold flex items-center gap-2 shadow-google hover:bg-emerald-600 transition-all active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
        DESCARGAR REPORTE
      </button>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl mb-6 shadow-google-sm border border-gray-100">
      <div class="space-y-1">
        <label class="text-[10px] font-black text-cetpro uppercase ml-1">Fecha de Inicio</label>
        <input type="date" class="w-full border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-cetpro outline-none border">
      </div>
      <div class="space-y-1">
        <label class="text-[10px] font-black text-cetpro uppercase ml-1">Fecha de Corte</label>
        <input type="date" class="w-full border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-cetpro outline-none border">
      </div>
      <div class="flex items-end pb-0.5">
        <button class="bg-cetpro w-full text-white font-bold py-2 rounded-lg hover:bg-cetpro-dark shadow-md transition-all uppercase text-sm tracking-widest">
          Consultar
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
            <th colspan="4" class="p-2 border-b border-r border-gray-700 bg-cetpro">Ciclo Básico</th>
            <th colspan="4" class="p-2 bg-cetpro-dark border-b border-gray-700">Ciclo Medio</th>
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
            <th class="p-1 border-r">H</th><th class="p-1 border-r">M</th><th class="p-1 border-r">H</th><th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th><th class="p-1 border-r">M</th><th class="p-1 border-r">H</th><th class="p-1 border-r">M</th>
            <th class="p-1 border-r">H</th><th class="p-1 border-r">M</th><th class="p-1 border-r">H</th><th class="p-1">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <tr class="bg-cetpro/5 text-cetpro font-black">
            <td colspan="2" class="p-3 text-right border-r uppercase italic">Gran Total</td>
            <td v-for="n in 12" :key="n" class="p-2 border-r last:border-0">0</td>
          </tr>
          <!-- Ejemplo de fila -->
          <tr v-for="i in 5" :key="i" class="hover:bg-gray-50 transition-colors">
            <td class="p-3 border-r text-gray-400">00{{i}}</td>
            <td class="p-3 text-left border-r font-medium">PROGRAMA DE ESTUDIO EJEMPLO {{i}}</td>
            <td v-for="n in 12" :key="n" class="p-2 border-r last:border-0 font-normal">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>