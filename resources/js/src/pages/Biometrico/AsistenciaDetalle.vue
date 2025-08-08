<script setup>
import { ref, onMounted } from 'vue';
import Button from '@/components/ui/Button.vue';
import Table from '@/components/table/Table.vue';
import THead from '@/components/table/THead.vue';
import TBody from '@/components/table/TBody.vue';
import Tr from '@/components/table/Tr.vue';
import Th from '@/components/table/Th.vue';
import Td from '@/components/table/Td.vue';

// Recibe el ID de la asignación desde la ruta
const props = defineProps({
    idAsignacion: { type: [String, Number], required: true }
});

const registros = ref([]);
const asignacionInfo = ref(null); // Para guardar la info del ciclo/programa
const dateFrom = ref(new Date().toISOString().slice(0, 10));
const dateTo = ref(new Date().toISOString().slice(0, 10));

const setDateRange = (days) => {
  const today = new Date();
  const from = new Date();
  from.setDate(today.getDate() - days);
  dateFrom.value = from.toISOString().slice(0, 10);
  dateTo.value = today.toISOString().slice(0, 10);
  applyDateFilter();
};

const applyDateFilter = () => {
  console.log(`Buscando registros para asignación ${props.idAsignacion} desde ${dateFrom.value} hasta ${dateTo.value}`);
};

onMounted(() => {
  // 1. Aquí harías una llamada a la API para traer la info de la asignación por props.idAsignacion
  asignacionInfo.value = {
      ciclo: 'Ciclo Técnico - 2025 I',
      programa: 'Peluquería',
      turno: 'Mañana'
  };

  // 2. Aquí harías otra llamada para traer los registros de esa asignación
  registros.value = [
    { fecha: '2025-07-28', ingreso: '08:05 AM', salida: '05:30 PM', estado: 'Completo', estadoClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300' },
    { fecha: '2025-07-27', ingreso: '08:00 AM', salida: null, estado: 'Salida Pendiente', estadoClass: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' },
    { fecha: '2025-07-26', ingreso: null, salida: null, estado: 'Ausente', estadoClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' },
  ];
});
</script>

<template>
  <div class="p-6 space-y-4">
    <div v-if="asignacionInfo">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
        Historial de Asistencia
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Mostrando registros para: <span class="font-semibold">{{ asignacionInfo.ciclo }} - {{ asignacionInfo.programa }} ({{ asignacionInfo.turno }})</span>
      </p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md">
      <div class="p-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-white">Registros Biométricos</h2>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <button @click="setDateRange(7)" class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-slate-700 rounded-md hover:bg-gray-200 dark:hover:bg-slate-600">Últimos 7 días</button>
            <div class="relative">
                <label for="dateFrom" class="absolute -top-2 left-2 text-[10px] text-gray-500 bg-white dark:bg-slate-800 px-1">Desde</label>
                <input id="dateFrom" type="date" v-model="dateFrom" class="text-sm w-36 pl-2 pr-2 py-1 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md focus:ring-cetpro focus:border-cetpro">
            </div>
            <div class="relative">
                <label for="dateTo" class="absolute -top-2 left-2 text-[10px] text-gray-500 bg-white dark:bg-slate-800 px-1">Hasta</label>
                <input id="dateTo" type="date" v-model="dateTo" class="text-sm w-36 pl-2 pr-2 py-1 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-md focus:ring-cetpro focus:border-cetpro">
            </div>
            <Button @click="applyDateFilter" title="Buscar" class="!text-sm !py-1.5"/>
        </div>
      </div>
      
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light" />

      <Table>
        <THead>
          <Tr>
            <Th>Fecha</Th>
            <Th>Hora de Ingreso</Th>
            <Th>Hora de Salida</Th>
            <Th>Estado</Th>
          </Tr>
        </THead>
        <TBody>
          <Tr v-for="registro in registros" :key="registro.fecha">
            <Td class="font-medium text-gray-800 dark:text-gray-200">{{ registro.fecha }}</Td>
            <Td>{{ registro.ingreso || '---' }}</Td>
            <Td>{{ registro.salida || '---' }}</Td>
            <Td>
              <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="registro.estadoClass">
                {{ registro.estado }}
              </span>
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>
  </div>
</template>