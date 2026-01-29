<template>
  <div class="p-6">
    <div class="bg-cetpro p-4 rounded-t-xl shadow-google-sm">
      <h2 class="text-white font-black uppercase text-center tracking-widest">203. MATRÍCULA POR CICLO Y SEXO, SEGÚN NIVEL EDUCATIVO</h2>
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
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2 border-r">M</th>
            <th class="p-2 border-r">H</th><th class="p-2">M</th>
          </tr>
        </thead>
        <tbody class="divide-y font-bold">
          <tr v-for="nivel in ['Sin Nivel', 'Primaria incompleta', 'Primaria completa', 'Secundaria incompleta', 'Secundaria completa', 'Superior incompleta', 'Superior completa']" 
              :key="nivel" class="hover:bg-cetpro/5">
            <td class="p-4 text-left border-r font-black text-gray-700">{{ nivel.toUpperCase() }}</td>
            <td v-for="n in 6" :key="n" class="p-4 border-r last:border-0">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>