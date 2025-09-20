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
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import { generatePdfMatricula } from '../../pdf/fichaMatricula';
import useModalToast from '../../composables/useModalToast';
import ConfirmModalReserva from '../../components/page/ConfirmModalReserva.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const { showConfirmModal, showToast } = useModalToast();

const router = useRouter();

const matriculaStore = useMatriculaStore();

onMounted(() => {
    loading.value = true;
    setTimeout(async () => {
        await matriculaStore.fetchMatriculadosPorGrupo(props.id)
        loading.value = false;
    }, 1000);
});

// const matriculados = computed(() => matriculaStore.estudiantesMatriculados)
const matriculados = computed(() => matriculaStore.matriculadosPorGrupo)
const loading = ref(false)
const estudiantesSeleccionados = ref([]);
const datosParaFicha = ref([]);


const todosSeleccionados = computed({
    get: () => matriculados.value.length > 0 && estudiantesSeleccionados.value.length === matriculados.value.length,
    set: (value) => {
        estudiantesSeleccionados.value = value ? matriculados.value.map(m => m.id) : [];
    }
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

// const reservarMatricula = async (matricula) => {

//     console.log('RESERVA DE MATRICULA', matricula)

//     if (confirm(`¿Pasar a RESERVA a ${matricula.estudiante.nombres}?`)) {
//         try {
//             await matriculaStore.loadReservaMatricula(matricula.id_matricula)

//             await matriculaStore.fetchMatriculadosPorGrupo(props.id)
//             // alert(response.data.message);
//         } catch (error) {
//             console.error(error);
//             alert('Error al reservar la matrícula');
//         }
//     }
// };

const showModal = ref(false)
const selectedMatricula = ref(null)

const abrirModalReserva = (matricula) => {
    selectedMatricula.value = matricula
    showModal.value = true
}

const confirmarReserva = async () => {
    try {
        await matriculaStore.loadReservaMatricula(selectedMatricula.value.id_matricula)
        await matriculaStore.fetchMatriculadosPorGrupo(props.id)
    } catch (error) {
        console.error(error)
        alert('Error al reservar la matrícula')
    } finally {
        showModal.value = false
    }
}

const exportarFicha = async (matricula) => {
    try {
        await matriculaStore.fetchFichaMatricula(matricula);

        const datosMatricula = matriculaStore.datosMatricula;

        if (datosMatricula) {
            generatePdfMatricula(datosMatricula);
        } else {
            console.error('No se pudieron obtener los datos de la matrícula');
        }
    } catch (error) {
        console.error('Error al exportar ficha:', error);
    }
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
        <div class="w-full space-y-4 py-2 px-3" v-if="matriculados">
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-4 shadow-md">
                <h2 class="text-xl font-bold text-cetpro dark:text-cetpro-light mb-2">
                    Especialidad: <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                        matriculados.especialidad }}</span>
                </h2>
                <p class="text-gray-700 dark:text-gray-300">
                    Módulo: <span class="font-semibold">{{ matriculados.modulo }}</span>
                </p>
            </div>

            <div class="flex justify-start mb-4 ml-2">
                <Button title="Cambiar de Grupo Seleccionados" @click="cambiarGrupo"
                    :disabled="estudiantesSeleccionados.length === 0" :loading="saving" variant="secondary">
                    <ArrowPathIcon class="h-5 w-5 mr-2" />
                    Cambiar Grupo ({{ estudiantesSeleccionados.length }})
                </Button>
            </div>

            <Table>
                <THead>
                    <Th class="w-10 text-center">
                        <input type="checkbox" v-model="todosSeleccionados"
                            class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                    </Th>
                    <Th>N°</Th>
                    <Th>Estudiante</Th>
                    <Th>DNI</Th>
                    <Th>Fecha de Matrícula</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(matricula, index) in matriculados.matriculados" :key="matricula.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <Td class="text-center">
                            <input type="checkbox" :value="matricula.id" v-model="estudiantesSeleccionados"
                                class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                        </Td>
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ matricula.estudiante }}</Td>
                        <Td>{{ matricula.nro_documento }}</Td>
                        <Td>{{ new Date(matricula.created_at).toLocaleDateString() }}</Td>
                        <Td class="text-center">
                            <div class="flex justify-center items-center space-x-1">
                                <!-- <button @click="editarMatricula(matricula)" title="Editar"
                                    class="p-1 text-blue-500 hover:text-blue-700">
                                    <PencilSquareIcon class="h-5 w-5" />
                                </button>
                                <button @click="eliminarMatricula(matricula)" title="Eliminar"
                                    class="p-1 text-red-500 hover:text-red-700">
                                    <TrashIcon class="h-5 w-5" />
                                </button> -->
                                <button @click="abrirModalReserva(matricula)" title="Reservar Matrícula"
                                    class="p-1 text-yellow-500 hover:text-yellow-700">
                                    <ArchiveBoxIcon class="h-5 w-5" />
                                </button>
                                <button @click="exportarFicha(matricula.id_estudiante)" title="Exportar Ficha"
                                    class="p-1 text-green-500 hover:text-green-700">
                                    <DocumentArrowDownIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </Td>
                    </Tr>
                    <Tr v-if="matriculados?.matriculados?.length === 0 && !loading">
                        <Td colspan="6" class="text-center py-4">No hay estudiantes matriculados en este grupo.</Td>
                    </Tr>

                    <Tr v-if="loading">
                        <Td colspan="6" class="text-center py-4">Cargando estudiantes...</Td>
                    </Tr>

                </TBody>
            </Table>
        </div>
        <div v-else class="text-center p-8">Cargando información del grupo...</div>

        <ConfirmModalReserva :show="showModal" :estudiante="selectedMatricula" @close="showModal = false"
            @confirm="confirmarReserva" />
    </AuthorizationFallback>
</template>