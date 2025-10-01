<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { EyeIcon, ArrowDownTrayIcon, ChevronDownIcon, UserGroupIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import axios from 'axios';
import useGrupoStore from '../../store/Grupo/useGrupoStore';
import BaseSelectGrupo from '../../components/ui/BaseSelectGrupo.vue';
import useCicloStore from '../../store/Ciclo/useCicloStore';

const router = useRouter();
const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();

if (!cicloStore.ciclo?.length) await cicloStore.loadCiclo();

const gruposData = ref([]);
const loading = ref(true);
const selectedCiclo = ref(null);
const selectedPeriodo = ref(null);
const openEspecialidades = ref(new Set());

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
        showToast('Seleccionar todos los filtros.');
        return;
    }
    loading.value = true;
    await grupoStore.loadGruposCicloPeriodo({
        id_ciclo: selectedCiclo.value,
        id_periodo: selectedPeriodo.value,
    });
    gruposData.value = grupoStore.gruposCicloPeriodo;
    openEspecialidades.value = new Set(gruposData.value.map(e => e.especialidad));
    loading.value = false;
};

const verMatriculados = (grupo) => {
    router.push({ name: 'matricula.grupo.detalle', params: { id: grupo.id } });
};

const descargarNomina = async (idGrupo) => {
    try {
        const response = await axios.get(
            `/reportes/nomina/grupo/${idGrupo}`,
            { responseType: "blob" }
        );
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
    if (newSet.has(especialidadNombre)) {
        newSet.delete(especialidadNombre);
    } else {
        newSet.add(especialidadNombre);
    }
    openEspecialidades.value = newSet;
};

const gruposAgrupados = computed(() => {
    const agrupados = {};
    gruposData.value.forEach(grupo => {
        if (!agrupados[grupo.especialidad]) {
            agrupados[grupo.especialidad] = [];
        }
        agrupados[grupo.especialidad].push(grupo);
    });
    return Object.entries(agrupados);
});

onMounted(() => {
    loading.value = false;
});
</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
        <div class="p-4 md:p-6 space-y-6">
            <header>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Gestión de Matrícula por Grupos</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Filtra y visualiza los grupos de estudio para gestionar sus matrículas.</p>
            </header>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Ciclo</label>
                        <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo" placeholder="Seleccione un ciclo" @change="onCicloChange" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Periodo</label>
                        <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoCiclo" label="nombre_periodo" placeholder="Seleccione un periodo" :loading="grupoStore.periodoByCicloLoading" :disabled="!selectedCiclo"/>
                    </div>
                    <button @click="filtrarPorSeleccion" class="w-full bg-cetpro hover:bg-cetpro-dark text-white font-semibold py-2 px-4 rounded-md transition-colors duration-300 h-10 flex items-center justify-center">
                        Filtrar
                    </button>
                </div>
                 <div class="mt-4 p-3 flex items-center bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                    <InformationCircleIcon class="h-5 w-5 text-green-500 dark:text-green-400 mr-3 flex-shrink-0" />
                    <p class="text-sm text-green-700 dark:text-green-300">
                        Nota: La lista de periodos solo muestra aquellos que se encuentran actualmente activos.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                <Table v-if="gruposData.length > 0">
                    <THead class="hidden">
                        <Th>N°</Th><Th>Módulo</Th><Th>Sección</Th><Th>Turno</Th><Th>Docente</Th><Th>Acciones</Th>
                    </THead>
                    <TBody>
                        <template v-for="([especialidad, grupos], espIndex) in gruposAgrupados" :key="especialidad">
                            <tr @click="toggleEspecialidad(especialidad)" class="bg-cetpro dark:bg-cetpro-dark hover:bg-cetpro-dark dark:hover:bg-cetpro cursor-pointer transition-colors duration-200 border-b border-white dark:border-cetpro">
                                <td colspan="6" class="px-4 py-3 font-bold uppercase tracking-wider text-sm">
                                    <div class="flex items-center justify-between text-cetpro-text">
                                        <span>{{ especialidad }}</span>
                                        <ChevronDownIcon :class="['h-6 w-6 text-cetpro-text transition-transform duration-300', { 'rotate-180': openEspecialidades.has(especialidad) }]" />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="openEspecialidades.has(especialidad)" class="bg-white dark:bg-gray-800">
                                <td colspan="6" class="p-0">
                                    <table class="w-full">
                                        <tbody>
                                            <Tr v-for="(grupo, index) in grupos" :key="grupo.id" class="border-t-0">
                                                <Td class="text-center w-12">{{ index + 1 }}</Td>
                                                <Td>{{ grupo.modulo }}</Td>
                                                <Td class="text-center">{{ grupo.seccion }}</Td>
                                                <Td class="text-center">{{ grupo.turno }}</Td>
                                                <Td>{{ grupo.docente }}</Td>
                                                <Td class="w-28">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <button @click="verMatriculados(grupo)" title="Ver Matriculados" class="p-2 text-gray-500 hover:text-cetpro dark:text-gray-400 dark:hover:text-cetpro-light transition-colors duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <EyeIcon class="h-5 w-5" />
                                                        </button>
                                                        <button @click="descargarNomina(grupo.id)" title="Descargar Nómina" class="p-2 text-gray-500 hover:text-cetpro dark:text-gray-400 dark:hover:text-cetpro-light transition-colors duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <ArrowDownTrayIcon class="h-5 w-5" />
                                                        </button>
                                                    </div>
                                                </Td>
                                            </Tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </template>
                    </TBody>
                </Table>
                <div v-if="!loading && gruposData.length === 0" class="text-center py-12">
                     <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
                     <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontraron grupos</h3>
                     <p class="mt-1 text-sm text-gray-500">Selecciona los filtros y haz clic en "Filtrar" para buscar.</p>
                </div>
            </div>
        </div>
    </AuthorizationFallback>
</template>