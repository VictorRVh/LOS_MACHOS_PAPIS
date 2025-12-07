<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Th from '../../components/table/Th.vue';
import MenuTable from "../../components/table/MenuTable.vue";
import BaseSelectGrupo from '../../components/ui/BaseSelectGrupo.vue';
import SearchBar from "../../components/head_table/headSearch.vue";
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';

import useGrupoStore from '../../store/Grupo/useGrupoStore';
import useCicloStore from '../../store/Ciclo/useCicloStore';
import useTableData from "../../composables/tabla/useTableData";

const router = useRouter();
const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();

const gruposData = ref([]);
const loading = ref(true);
const selectedCiclo = ref(null);
const selectedPeriodo = ref(null);
const openEspecialidades = ref(new Set());

onMounted(async () => {
  if (!cicloStore.ciclo?.length) await cicloStore.loadCiclo();
});

const onCicloChange = async () => {
  selectedPeriodo.value = null;
  if (selectedCiclo.value) {
    await grupoStore.loadPeriodoCiclo(selectedCiclo.value);
  } else {
    grupoStore.periodoCiclo = [];
  }
};

const filtrarPorSeleccion = async () => {
  if (!selectedCiclo.value || !selectedPeriodo.value) {
    alert('Seleccionar todos los filtros.');
    return;
  }

  loading.value = true;

  await grupoStore.loadGruposCicloPeriodo({
    id_ciclo: selectedCiclo.value,
    id_periodo: selectedPeriodo.value,
  });

  gruposData.value = grupoStore.gruposCicloPeriodo || [];
  openEspecialidades.value = new Set(gruposData.value.map(e => e.especialidad));

  pagina.value = 1;   // <<< 🔥 AGREGAR ESTA LÍNEA

  loading.value = false;
};


const verMatriculados = (grupo) => {
  router.push({ name: 'matricula.grupo.alumnos', params: { id: grupo.id } });
};

const descargarNomina = async (idGrupo) => {
  try {
    const response = await axios.get(`/reportes/nomina/grupo/${idGrupo}`, { responseType: "blob" });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "nomina.xlsx");
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

const toggleEspecialidad = (especialidadNombre) => {
  const newSet = new Set(openEspecialidades.value);
  newSet.has(especialidadNombre) ? newSet.delete(especialidadNombre) : newSet.add(especialidadNombre);
  openEspecialidades.value = newSet;
};

const gruposPlanos = computed(() => {
  return gruposData.value.map(g => ({
    ...g,
    especialidadNombre: g.especialidad,
    docenteNombre: g.docente
  }));
});

const {
  query,
  pagina,
  itemsPorPagina,
  paginados: gruposPaginados,
  totalPaginas,
  ordenados: gruposOrdenados,
  filtrar: filtrarGrupos
} = useTableData(gruposPlanos, {
  defaultOrderBy: "modulo",
  searchFields: ["modulo", "seccion", "turno", "docenteNombre", "especialidadNombre"]
});

const gruposAgrupados = computed(() => {
  const agrupados = {};
  gruposPaginados.value.forEach(grupo => {
    if (!agrupados[grupo.especialidad]) agrupados[grupo.especialidad] = [];
    agrupados[grupo.especialidad].push(grupo);
  });
  return Object.entries(agrupados);
});

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
    <div class=""> <!-- FILTROS -->
      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-300">
        <!-- FILTROS EN UNA SOLA FILA MEJOR DISTRIBUIDOS -->
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end"> <!-- CICLO (más ancho) -->
          <div class="md:col-span-2 min-w-[250px]"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Ciclo</label>
            <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo"
              placeholder="Seleccione un ciclo" @change="onCicloChange" />
          </div> <!-- PERIODO -->
          <div class="md:col-span-1 min-w-[100px]"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Periodo</label>
            <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoCiclo" label="nombre_periodo"
              placeholder="Seleccione un periodo" :loading="grupoStore.periodoByCicloLoading"
              :disabled="!selectedCiclo" />
          </div> <!-- BOTÓN FILTRAR (columna fija para que NO DESAPAREZCA) -->
          <!-- BOTÓN FILTRAR (más pequeño sin romper el grid) -->
          <div class="md:col-span-1 flex items-end"> <button @click="filtrarPorSeleccion"
              class="w-[100px] bg-cetpro hover:bg-cetpro-dark text-white font-semibold py-2 px-2 rounded-md transition-colors duration-300 h-10 flex items-center justify-center text-sm">
              Filtrar </button> </div> <!-- BUSCADOR AL FINAL A LA DERECHA -->
        
              
          <div class="md:col-span-3"> <label
              class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Buscar</label>
            <SearchBar :totalResultados="gruposOrdenados.length" :campoOrden="'modulo'" @search="filtrarGrupos" />
          </div>
        </div>
      </div> <!-- TABLA --> <!-- Contenedor de la tabla con altura mínima y scroll -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-auto mt-2">
        <Table v-if="gruposAgrupados.length > 0" :paginacion="true" :current-page="pagina" :total-pages="totalPaginas"
          @changePage="pagina = $event" class="w-full border-collapse">
          <THead>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[5px]">N°</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 min-w-[280px]">Módulo</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[10px]">Sección</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[10px]">Turno</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[280px]">Docente</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[10px]">N° Estudiantes</Th>
            <Th class="border-b border-gray-300 dark:border-gray-300 w-[10px]">Acciones</Th>
          </THead>
          <TBody> <template v-for="([especialidad, grupos]) in gruposAgrupados" :key="especialidad">
              <!-- ESPECIALIDAD -->
              <tr @click="toggleEspecialidad(especialidad)"
                class="bg-cetpro dark:bg-cetpro-dark hover:bg-cetpro-dark/70 cursor-pointer border-b border-gray-400 dark:border-gray-600">
                <td colspan="7"
                  class="px-4 py-2 font-bold uppercase tracking-wider text-sm border-b border-gray-300 dark:border-gray-600">
                  <div class="flex items-center justify-between text-cetpro-text"> <span>{{ especialidad }}</span>
                    <ChevronDownIcon
                      :class="['h-6 w-6 text-cetpro-text transition-transform duration-300', { 'rotate-180': openEspecialidades.has(especialidad) }]" />
                  </div>
                </td>
              </tr> <!-- GRUPOS -->
              <tr v-for="(grupo, index) in grupos" :key="grupo.id" v-show="openEspecialidades.has(especialidad)"
                class="border-b border-gray-300 dark:border-gray-700">
                <td class="text-center w-6 border-b border-gray-300 dark:border-gray-700 py-3">{{ index + 1 }}</td>
                <td class="border-b border-gray-300 dark:border-gray-700 py-3">{{ grupo.modulo }}</td>
                <td class="text-center border-b border-gray-300 dark:border-gray-700 py-3">{{ grupo.seccion }}</td>
                <td class="text-center border-b border-gray-300 dark:border-gray-700 py-3">{{ grupo.turno }}</td>
                <td class="border-b border-gray-300 dark:border-gray-700 px-5 py-3">{{ grupo.docente }} </td>
                <td class="text-center border-b border-gray-300 dark:border-gray-700 text-green-700 font-semibold py-3">
                  {{ grupo.cantidad_estudiantes }}</td>
                <td class="text-center border-b border-gray-300 dark:border-gray-700 py-3">
                  <MenuTable :actions="{ view: true, download: true }"
                    :labels="{ view: 'Ver Alumnos', download: 'Descargar Nomina' }" @view="verMatriculados(grupo)"
                    @download="descargarNomina(grupo.id)" />
                </td>
              </tr>
            </template>
          </TBody>
        </Table>
      </div>
    </div>
  </AuthorizationFallback>
</template>