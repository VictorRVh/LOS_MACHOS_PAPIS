<script setup>
import { ref } from 'vue';
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

// --- SIMULACIÓN DE DATOS ---
// Nos inventamos una lista de grupos aquí mismo.
const gruposSimulados = ref([
    { id: 1, nombre_grupo: 'Computación e Informática - Turno Mañana', turno: 'Mañana', docente: { name: 'Juan Alberto Pérez' } },
    { id: 2, nombre_grupo: 'Asistencia de Cocina - Turno Tarde', turno: 'Tarde', docente: { name: 'María Eugenia Rosas' } },
    { id: 3, nombre_grupo: 'Peluquería Básica - Turno Noche', turno: 'Noche', docente: { name: 'Esther Vílchez' } },
    { id: 4, nombre_grupo: 'Mecánica Automotriz - Turno Mañana', turno: 'Mañana', docente: { name: 'Carlos Mendoza' } },
]);
// --- FIN DE LA SIMULACIÓN ---

const verMatriculados = (grupo) => {
    router.push({ name: 'matricula.grupo.detalle', params: { id: grupo.id } });
};
</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos']">
        <div class="w-full space-y-2 py-2 px-3">
            <div class="flex justify-between items-center m-2">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Gestión de Matrícula por Grupos</h2>
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
                    <Tr v-for="(grupo, index) in gruposSimulados" :key="grupo.id">
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ grupo.nombre_grupo }}</Td>
                        <Td>{{ grupo.turno }}</Td>
                        <Td>{{ grupo.docente.name }}</Td>
                        <Td class="text-center">
                           <Button title="Ver Matriculados" @click="verMatriculados(grupo)" />
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
    </AuthorizationFallback>
</template>