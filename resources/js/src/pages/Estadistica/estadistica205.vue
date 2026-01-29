<template>
  <div class="p-6">
    <div class="flex items-center gap-4 mb-8">
        <div class="h-12 w-2 bg-cetpro-dark rounded-full shadow-google"></div>
        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic">205. NÚMERO TOTAL DE SECCIONES, POR CICLO SEGÚN TURNO</h2>
    </div>

    <div class="max-w-4xl mx-auto overflow-hidden rounded-2xl shadow-google border border-gray-100 bg-white">
      <table class="w-full text-sm text-center">
        <thead>
          <tr class="bg-gray-900 text-white font-bold uppercase italic">
            <th class="p-5 border-r border-gray-800">Turno de Trabajo</th>
            <th class="p-5 border-r border-gray-800 bg-gray-800">Total Secciones</th>
            <th class="p-5 border-r border-gray-800 bg-cetpro">Ciclo Básico</th>
            <th class="p-5 bg-cetpro-dark">Ciclo Medio</th>
          </tr>
        </thead>
        <tbody class="divide-y text-lg font-black text-gray-700">
          <tr v-for="turno in ['Mañana', 'Tarde', 'Noche']" :key="turno" class="hover:bg-gray-50 group">
            <td class="p-6 text-left border-r uppercase bg-gray-50/50 group-hover:text-cetpro transition-colors italic">{{ turno }}</td>
            <td class="p-6 border-r text-2xl font-black">0</td>
            <td class="p-6 border-r text-cetpro font-black">0</td>
            <td class="p-6 text-cetpro-dark font-black">0</td>
          </tr>
          <tr class="bg-gray-800 text-white">
            <td class="p-6 text-left border-r font-black uppercase tracking-widest italic">Total General</td>
            <td class="p-6 border-r text-2xl">0</td>
            <td class="p-6 border-r">0</td>
            <td class="p-6">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>