<script setup>
import { onMounted, computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import { PencilSquareIcon, TrashIcon, ArchiveBoxIcon, DocumentArrowDownIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const router = useRouter();

// --- SIMULACIÓN DE DATOS ---
const loading = ref(true);
const saving = ref(false);
const grupo = ref(null);
const matriculados = ref([]);
const estudiantesSeleccionados = ref([]);

const datosFalsosGrupos = [
    { id: 1, nombre_grupo: 'Computación e Informática - 2024-I' },
    { id: 2, nombre_grupo: 'Asistencia de Cocina - Básico' },
    { id: 3, nombre_grupo: 'Peluquería Unisex - Avanzado' },
    { id: 4, nombre_grupo: 'Mecánica Automotriz - Inyección Electrónica' },
    { id: 5, nombre_grupo: 'Contabilidad - Ciclo Intermedio' },
];

const datosFalsosMatriculados = [
    { id: 101, created_at: '2024-03-01T10:00:00Z', estudiante: { nombres: 'Ana Lucía', apellidos: 'García Torres', nro_documento: '76543210' } },
    { id: 102, created_at: '2024-03-02T11:30:00Z', estudiante: { nombres: 'Luis Miguel', apellidos: 'Ramírez Soto', nro_documento: '71234567' } },
    { id: 103, created_at: '2024-03-02T14:15:00Z', estudiante: { nombres: 'Carla Sofía', apellidos: 'Mendoza Luna', nro_documento: '78901234' } },
    { id: 104, created_at: '2024-03-03T09:00:00Z', estudiante: { nombres: 'Jorge Andrés', apellidos: 'Castillo Vega', nro_documento: '75554433' } },
    { id: 105, created_at: '2024-03-04T16:45:00Z', estudiante: { nombres: 'David Alonso', apellidos: 'Flores Díaz', nro_documento: '72221100' } },
];
// --- FIN SIMULACIÓN ---

const todosSeleccionados = computed({
    get: () => matriculados.value.length > 0 && estudiantesSeleccionados.value.length === matriculados.value.length,
    set: (value) => {
        estudiantesSeleccionados.value = value ? matriculados.value.map(m => m.id) : [];
    }
});

onMounted(() => {
    loading.value = true;
    setTimeout(() => {
        grupo.value = datosFalsosGrupos.find(g => g.id == props.id) || { id: props.id, nombre_grupo: 'Grupo Desconocido' };
        matriculados.value = datosFalsosMatriculados;
        loading.value = false;
    }, 1000);
});

const editarMatricula = (matricula) => {
    alert(`Simulación: Redirigiendo a editar matrícula con ID ${matricula.id}`);
    router.push({ name: 'matricula.editar', params: { id: matricula.id } });
};

const eliminarMatricula = (matricula) => {
    if (confirm(`Simulación: ¿Estás seguro de eliminar a ${matricula.estudiante.nombres}?`)) {
        matriculados.value = matriculados.value.filter(m => m.id !== matricula.id);
        alert('Estudiante eliminado de la lista (simulado).');
    }
};

const reservarMatricula = (matricula) => {
    if (confirm(`Simulación: ¿Pasar a RESERVA a ${matricula.estudiante.nombres}?`)) {
        matriculados.value = matriculados.value.filter(m => m.id !== matricula.id);
        alert('Matrícula reservada. El estudiante ha sido quitado de esta lista (simulado).');
    }
};

const exportarFicha = (matricula) => {
    alert(`Simulación: Descargando ficha para el estudiante con DNI ${matricula.estudiante.nro_documento}`);
};

const cambiarGrupo = () => {
    if (estudiantesSeleccionados.value.length === 0) {
        alert("Selecciona al menos un estudiante.");
        return;
    }
    const nuevoGrupoId = prompt("Simulación: Ingresa el ID del nuevo grupo de destino:", "");
    if (nuevoGrupoId && !isNaN(nuevoGrupoId)) {
        saving.value = true;
        setTimeout(() => {
            matriculados.value = matriculados.value.filter(m => !estudiantesSeleccionados.value.includes(m.id));
            estudiantesSeleccionados.value = [];
            alert(`Estudiantes movidos al grupo ${nuevoGrupoId} (simulado).`);
            saving.value = false;
        }, 1500);
    }
};

</script>
<template>
    <AuthorizationFallback :permissions="['todo-acceso-permisos']">
        <div class="w-full space-y-4 py-2 px-3" v-if="grupo">
            <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl m-2">
                Estudiantes en: {{ grupo.nombre_grupo }}
            </h2>

            <div class="flex justify-start mb-4 ml-2">
                <Button 
                    title="Cambiar de Grupo Seleccionados" 
                    @click="cambiarGrupo"
                    :disabled="estudiantesSeleccionados.length === 0"
                    :loading="saving"
                    variant="secondary">
                    <ArrowPathIcon class="h-5 w-5 mr-2"/>
                    Cambiar Grupo ({{ estudiantesSeleccionados.length }})
                </Button>
            </div>

            <Table>
                <THead>
                    <Th class="w-10 text-center">
                        <input type="checkbox" v-model="todosSeleccionados" class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                    </Th>
                    <Th>N°</Th>
                    <Th>Estudiante</Th>
                    <Th>DNI</Th>
                    <Th>Fecha de Matrícula</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(matricula, index) in matriculados" :key="matricula.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <Td class="text-center">
                             <input type="checkbox" :value="matricula.id" v-model="estudiantesSeleccionados" class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                        </Td>
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ matricula.estudiante.nombres }} {{ matricula.estudiante.apellidos }}</Td>
                        <Td>{{ matricula.estudiante.nro_documento }}</Td>
                        <Td>{{ new Date(matricula.created_at).toLocaleDateString() }}</Td>
                        <Td class="text-center">
                            <div class="flex justify-center items-center space-x-1">
                                <button @click="editarMatricula(matricula)" title="Editar" class="p-1 text-blue-500 hover:text-blue-700"><PencilSquareIcon class="h-5 w-5"/></button>
                                <button @click="eliminarMatricula(matricula)" title="Eliminar" class="p-1 text-red-500 hover:text-red-700"><TrashIcon class="h-5 w-5"/></button>
                                <button @click="reservarMatricula(matricula)" title="Reservar Matrícula" class="p-1 text-yellow-500 hover:text-yellow-700"><ArchiveBoxIcon class="h-5 w-5"/></button>
                                <button @click="exportarFicha(matricula)" title="Exportar Ficha" class="p-1 text-green-500 hover:text-green-700"><DocumentArrowDownIcon class="h-5 w-5"/></button>
                            </div>
                        </Td>
                    </Tr>
                    <Tr v-if="matriculados.length === 0 && !loading">
                        <Td colspan="6" class="text-center py-4">No hay estudiantes matriculados en este grupo.</Td>
                    </Tr>
                    <Tr v-if="loading">
                        <Td colspan="6" class="text-center py-4">Cargando estudiantes...</Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
        <div v-else class="text-center p-8">Cargando información del grupo...</div>
    </AuthorizationFallback>
</template>