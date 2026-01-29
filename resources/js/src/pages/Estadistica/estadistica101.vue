<template>
  <div class="p-6">
    <!-- TÍTULO Y EXCEL -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-extrabold text-gray-800">101. APROBADOS Y RETIRADOS SEGÚN CICLO Y SEXO</h2>
      <button class="bg-[#10b981] text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 shadow-google-sm hover:bg-emerald-600 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
        EXPORTAR EXCEL
      </button>
    </div>

    <!-- FILTROS PRO -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl mb-6 border">
      <div class="space-y-1">
        <label class="text-[10px] font-black text-cetpro uppercase">Fecha Inicio</label>
        <input type="date" class="w-full border-none shadow-google-sm rounded-lg p-2 focus:ring-2 focus:ring-cetpro">
      </div>
      <div class="space-y-1">
        <label class="text-[10px] font-black text-cetpro uppercase">Fecha Fin</label>
        <input type="date" class="w-full border-none shadow-google-sm rounded-lg p-2 focus:ring-2 focus:ring-cetpro">
      </div>
      <div class="flex items-end">
        <button class="bg-cetpro w-full text-white font-bold py-2 rounded-lg hover:bg-cetpro-dark shadow-google-sm transition-all">
          CONSULTAR DATOS
        </button>
      </div>
    </div>

    <!-- TABLA ESTILO 2025 -->
    <div class="overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-800 text-white text-center">
            <th class="p-4 border-r border-gray-700">SITUACIÓN</th>
            <th class="p-4 border-r border-gray-700">TOTAL GENERAL</th>
            <th colspan="2" class="p-4 border-r border-gray-700 bg-gray-700">TOTAL</th>
            <th colspan="2" class="p-4 border-r border-gray-700 bg-cetpro">BÁSICO</th>
            <th colspan="2" class="p-4 bg-cetpro-dark">MEDIO</th>
          </tr>
          <tr class="bg-gray-100 text-gray-600 border-b">
            <th class="p-2 border-r"></th>
            <th class="p-2 border-r"></th>
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-medium text-center">
          <tr class="hover:bg-cetpro/5">
            <td class="p-4 text-left font-bold border-r">TOTAL MATRICULADOS</td>
            <td class="p-4 border-r font-black text-cetpro">0</td>
            <td class="p-4 border-r">0</td><td class="p-4 border-r text-cetpro">0</td>
            <td class="p-4 border-r">0</td><td class="p-4 border-r text-cetpro">0</td>
            <td class="p-4 border-r">0</td><td class="p-4 text-cetpro">0</td>
          </tr>
          <!-- Repetir filas según necesites -->
        </tbody>
      </table>
    </div>
  </div>
</template>