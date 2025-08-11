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

const router = useRouter();

const todosLosGrupos = ref([]);
const loading = ref(true);

const selectedCiclo = ref('');
const selectedPeriodo = ref('');

const datosFalsos = [
    { id: 1, ciclo: 'Formación Auxiliar', periodo: '2024-I', nombre_grupo: 'Computación - Básico', turno: 'Mañana', docente: { name: 'Juan Carlos Pérez' } },
    { id: 2, ciclo: 'Formación Media', periodo: '2024-I', nombre_grupo: 'Asistencia de Cocina - Intermedio', turno: 'Tarde', docente: { name: 'María Elena Rodríguez' } },
    { id: 3, ciclo: 'Formación Superior', periodo: '2024-II', nombre_grupo: 'Peluquería - Avanzado', turno: 'Noche', docente: { name: 'Sofia Castillo Vega' } },
    { id: 4, ciclo: 'Formación Auxiliar', periodo: '2024-II', nombre_grupo: 'Mecánica - Inyección', turno: 'Mañana', docente: { name: 'Luis Alberto Quispe' } },
    { id: 5, ciclo: 'Formación Media', periodo: '2023-II', nombre_grupo: 'Contabilidad - Básico', turno: 'Tarde', docente: { name: 'Roberto Gómez Bolaños' } },
    { id: 6, ciclo: 'Formación Auxiliar', periodo: '2024-I', nombre_grupo: 'Electrónica - Básico', turno: 'Noche', docente: { name: 'Ana María Hurtado' } },
];

onMounted(() => {
    setTimeout(() => {
        todosLosGrupos.value = datosFalsos;
        loading.value = false;
    }, 1000);
});

const ciclosUnicos = computed(() => {
    const ciclos = todosLosGrupos.value.map(g => g.ciclo);
    return [...new Set(ciclos)];
});

const periodosUnicos = computed(() => {
    const periodos = todosLosGrupos.value.map(g => g.periodo);
    return [...new Set(periodos)].sort().reverse();
});

const filteredGrupos = computed(() => {
    return todosLosGrupos.value.filter(grupo => {
        const matchCiclo = !selectedCiclo.value || grupo.ciclo === selectedCiclo.value;
        const matchPeriodo = !selectedPeriodo.value || grupo.periodo === selectedPeriodo.value;
        return matchCiclo && matchPeriodo;
    });
});

const verMatriculados = (grupo) => {
    router.push({ name: 'matricula.grupo.detalle', params: { id: grupo.id } });
};
</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos']">
        <div class="w-full space-y-4 py-2 px-3">
            <div class="flex justify-between items-center m-2">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Gestión de Matrícula por Grupos</h2>
            </div>
            
            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <label for="ciclo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo</label>
                        <select v-model="selectedCiclo" id="ciclo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                            <option value="">Seleccione un ciclo</option>
                            <option v-for="ciclo in ciclosUnicos" :key="ciclo" :value="ciclo">{{ ciclo }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label for="periodo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</label>
                        <select v-model="selectedPeriodo" id="periodo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600">
                            <option value="">Seleccione un periodo</option>
                            <option v-for="periodo in periodosUnicos" :key="periodo" :value="periodo">{{ periodo }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <Table>
                <THead>
                    <Th>N°</Th>
                    <Th>Nombre del Grupo</Th>
                    <Th>Turno</Th>
                    <Th>Docente</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(grupo, index) in filteredGrupos" :key="grupo.id">
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ grupo.nombre_grupo }}</Td>
                        <Td>{{ grupo.turno }}</Td>
                        <Td>{{ grupo.docente.name }}</Td>
                        <Td class="text-center">
                           <Button title="Ver Matriculados" @click="verMatriculados(grupo)" />
                        </Td>
                    </Tr>
                    <Tr v-if="filteredGrupos.length === 0 && !loading">
                        <Td colspan="5" class="text-center py-4">No se encontraron grupos con los filtros seleccionados.</Td>
                    </Tr>
                     <Tr v-if="loading">
                        <Td colspan="5" class="text-center py-4">Cargando...</Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
    </AuthorizationFallback>
</template>