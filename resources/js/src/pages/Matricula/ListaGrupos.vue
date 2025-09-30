<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import axios from 'axios';
import useGrupoStore from '../../store/Grupo/useGrupoStore';
import BaseSelectGrupo from '../../components/ui/BaseSelectGrupo.vue';
import useCicloStore from '../../store/Ciclo/useCicloStore';
import usePeriodosStore from '../../store/Periodo/usePeriodoStore';

const router = useRouter();

const grupoStore = useGrupoStore();
const cicloStore = useCicloStore();

if (!cicloStore.ciclo?.length) await cicloStore.loadCiclo();

const grupos = ref([]);
const loading = ref(true);

const selectedCiclo = ref(null)
const selectedPeriodo = ref(null)

onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 1000);
});

// const filteredGrupos = computed(() => {
//     return todosLosGrupos.value.filter(grupo => {
//         const matchCiclo = !selectedCiclo.value || grupo.ciclo === selectedCiclo.value;
//         const matchPeriodo = !selectedPeriodo.value || grupo.periodo === selectedPeriodo.value;
//         return matchCiclo && matchPeriodo;
//     });
// });


const onCicloChange = async () => {
    if (selectedCiclo.value) {
        await grupoStore.loadPeriodoCiclo(selectedCiclo.value);
        console.log("Ciclo cargados:", grupoStore.periodoCiclo);
    } else {
        selectedPeriodo.value = null
        grupoStore.periodoCiclo = [];
    }
};

const filtrarPorSeleccion = async () => {

    if (!selectedCiclo.value || !selectedPeriodo.value) {
        showToast('Seleccionar todos los filtros.')
        return;
    }

    await grupoStore.loadGruposCicloPeriodo({
        id_ciclo: selectedCiclo.value,
        id_periodo: selectedPeriodo.value,
    });

    grupos.value = grupoStore.gruposCicloPeriodo;
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

        console.log('respuesta excel: ', response)

        // Descargar archivo
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "nomina.xlsx");
        document.body.appendChild(link);
        link.click();
    } catch (error) {
        console.error("Error descargando reporte:", error);
    }
}

</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos', 'ver-permisos']">
        <div class="w-full space-y-4 py-2 px-3">
            <div class="flex justify-between items-center m-2">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Gestión de Matrícula por Grupos</h2>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo</label>

                            <BaseSelectGrupo v-model="selectedCiclo" :options="cicloStore.ciclo" label="nombre_ciclo"
                                placeholder="Seleccione un ciclo" @change="onCicloChange" />

                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</label>
                            <BaseSelectGrupo v-model="selectedPeriodo" :options="grupoStore.periodoCiclo"
                                label="nombre_periodo" placeholder="Seleccione un periodo" :loading="grupoStore.periodoByCicloLoading" :disabled="!selectedCiclo"/>
                        </div>
                    </div>

                    <div class="flex items-end pt-5">
                        <button @click="filtrarPorSeleccion"
                            class="bg-cetpro hover:bg-primary-dark text-white py-2 px-4 rounded-md w-full">
                            Filtrar
                        </button>
                    </div>

                </div>
            </div>

            <Table>
                <THead>
                    <Th>N°</Th>
                    <Th>Especialidad</Th>
                    <Th>Modulo</Th>
                    <Th>Seccion</Th>
                    <Th>Turno</Th>
                    <Th>Docente</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(grupo, index) in grupos" :key="grupo.id">
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ grupo.especialidad }}</Td>
                        <Td>{{ grupo.modulo }}</Td>
                        <Td>{{ grupo.seccion }}</Td>
                        <Td>{{ grupo.turno }}</Td>
                        <Td>{{ grupo.docente }}</Td>
                        <Td class="text-center">
                            <Button title="Ver Matriculados" @click="verMatriculados(grupo)" />
                            <Button title="Descargar Nomina"
                                @click="descargarNomina(grupo.id)" />
                        </Td>
                    </Tr>
                    <Tr v-if="grupos.length === 0 && !loading">
                        <Td colspan="8" class="text-center py-4">Seleccionar filtros para buscar grupos.
                        </Td>
                    </Tr>
                    <Tr v-if="loading">
                        <Td colspan="8" class="text-center py-4">Cargando...</Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
    </AuthorizationFallback>
</template>