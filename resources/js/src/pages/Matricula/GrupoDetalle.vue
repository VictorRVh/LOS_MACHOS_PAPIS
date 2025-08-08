<script setup>
// ... (El código simulado que te di antes para esta página es correcto y se mantiene) ...
import { ref, onMounted } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const grupo = ref(null);
const matriculados = ref([]);

const fakeApiDatabase = {
    '1': { id: 1, nombre_grupo: 'Computación e Informática - Turno Mañana', docente: { name: 'Juan Alberto Pérez' }, matriculas: [{ id: 101, created_at: '2023-01-15T10:00:00Z', estudiante: { nombres: 'Ana Lucía', apellidos: 'García Torres', nro_documento: '76543210' }}]},
    '2': { id: 2, nombre_grupo: 'Asistencia de Cocina - Turno Tarde', docente: { name: 'María Eugenia Rosas' }, matriculas: []},
};

onMounted(() => {
    const dataFromFakeApi = fakeApiDatabase[props.id];
    if (dataFromFakeApi) {
        grupo.value = dataFromFakeApi;
        matriculados.value = dataFromFakeApi.matriculas;
    }
});
</script>
<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos']">
        <div class="w-full space-y-2 py-2 px-3" v-if="grupo">
            <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl m-2">
                Estudiantes en: {{ grupo.nombre_grupo }}
            </h2>
            <Table>
                <THead>
                    <Th>N°</Th><Th>Estudiante</Th><Th>DNI</Th><Th>Fecha de Matrícula</Th>
                </THead>
                <TBody>
                    <Tr v-for="(matricula, index) in matriculados" :key="matricula.id">
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ matricula.estudiante.nombres }} {{ matricula.estudiante.apellidos }}</Td>
                        <Td>{{ matricula.estudiante.nro_documento }}</Td>
                        <Td>{{ new Date(matricula.created_at).toLocaleDateString() }}</Td>
                    </Tr>
                    <Tr v-if="matriculados.length === 0">
                        <Td colspan="4" class="text-center py-4">No hay estudiantes matriculados en este grupo.</Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
    </AuthorizationFallback>
</template>