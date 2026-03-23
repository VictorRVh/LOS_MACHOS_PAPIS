<script setup>
import { ref, onMounted } from 'vue';
import Button from '@/components/ui/Button.vue';
import Table from '@/components/table/Table.vue';
import THead from '@/components/table/THead.vue';
import TBody from '@/components/table/TBody.vue';
import Tr from '@/components/table/Tr.vue';
import Th from '@/components/table/Th.vue';
import Td from '@/components/table/Td.vue';

const props = defineProps({
    idAsignacion: { type: [String, Number], required: true }
});

const registros = ref([]);
const asignacionInfo = ref(null);
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
  console.log(`Buscando registros para asignacion ${props.idAsignacion} desde ${dateFrom.value} hasta ${dateTo.value}`);
};

onMounted(() => {
  asignacionInfo.value = {
      ciclo: 'Ciclo Tecnico - 2025 I',
      programa: 'Peluqueria',
      turno: 'Manana'
  };

  registros.value = [
    { fecha: '2025-07-28', ingreso: '08:05 AM', salida: '05:30 PM', estado: 'Completo', estadoClass: 'bg-emerald-500 text-white dark:bg-emerald-600 dark:text-white' },
    { fecha: '2025-07-27', ingreso: '08:00 AM', salida: null, estado: 'Salida Pendiente', estadoClass: 'bg-amber-500 text-white dark:bg-amber-600 dark:text-white' },
    { fecha: '2025-07-26', ingreso: null, salida: null, estado: 'Ausente', estadoClass: 'bg-red-500 text-white dark:bg-red-600 dark:text-white' },
  ];
});
</script>

<template>
  <div class="space-y-5 p-5">
    <div v-if="asignacionInfo" class="space-y-1">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        Historial de Asistencia
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Mostrando registros para:
        <span class="font-semibold text-slate-700 dark:text-slate-200">
          {{ asignacionInfo.ciclo }} - {{ asignacionInfo.programa }} ({{ asignacionInfo.turno }})
        </span>
      </p>
    </div>

    <section class="space-y-3">
      <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Registros Biometricos</h2>
          <p class="text-xs uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">
            Consulta de marcaciones
          </p>
        </div>

        <div class="flex flex-wrap items-end justify-end gap-2">
          <button
            @click="setDateRange(7)"
            class="inline-flex h-9 items-center rounded-[3px] border border-slate-200 bg-slate-50 px-3 text-xs font-medium text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
          >
            Ultimos 7 dias
          </button>

          <div class="space-y-1">
            <label
              for="dateFrom"
              class="block text-[11px] font-medium uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400"
            >
              Desde
            </label>
            <input
              id="dateFrom"
              v-model="dateFrom"
              type="date"
              class="block min-h-9 w-36 rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
            >
          </div>

          <div class="space-y-1">
            <label
              for="dateTo"
              class="block text-[11px] font-medium uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400"
            >
              Hasta
            </label>
            <input
              id="dateTo"
              v-model="dateTo"
              type="date"
              class="block min-h-9 w-36 rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
            >
          </div>

          <Button @click="applyDateFilter" title="Buscar" class="!h-9 !px-4 !py-0 !text-sm" />
        </div>
      </div>

      <Table>
        <THead>
          <Th>Fecha</Th>
          <Th>Hora de Ingreso</Th>
          <Th>Hora de Salida</Th>
          <Th>Estado</Th>
        </THead>
        <TBody>
          <Tr v-for="registro in registros" :key="registro.fecha">
            <Td class="font-medium text-slate-800 dark:text-slate-200">{{ registro.fecha }}</Td>
            <Td>{{ registro.ingreso || '---' }}</Td>
            <Td>{{ registro.salida || '---' }}</Td>
            <Td>
              <span class="inline-flex min-w-[108px] items-center justify-center rounded-[3px] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.08em] leading-none" :class="registro.estadoClass">
                {{ registro.estado }}
              </span>
            </Td>
          </Tr>
        </TBody>
      </Table>
    </section>
  </div>
</template>
