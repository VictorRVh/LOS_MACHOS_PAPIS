<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import FormInput from '../../components/ui/FormInput.vue';
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';

const matriculaStore = useMatriculaStore();

const loading = ref(true);

const searchQuery = ref('');
const selectedGroup = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(5);

onMounted(() => {
    loading.value = true;
    setTimeout(async () => {
        await matriculaStore.loadListaReserva()
        loading.value = false;
    }, 1000);
});

const estudiantesConReserva = computed(() => {
    return matriculaStore.matriculasReservadas?.estudiantes ?? []
})

// console.log('estudiantes con Reserva', estudiantesConReserva.value)

const gruposUnicos = computed(() => {
    const grupos = estudiantesConReserva.value.map(reserva => reserva.grupo.nombre_grupo);
    return [...new Set(grupos)];
});

const filteredReservas = computed(() => {
    let reservas = estudiantesConReserva.value;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        reservas = reservas.filter(reserva =>
            reserva.estudiante.nombre_completo.toLowerCase().includes(query) ||
            reserva.estudiante.nro_documento.includes(query)
        );
    }

    if (selectedGroup.value) {
        reservas = reservas.filter(reserva => reserva.grupo.nombre_grupo === selectedGroup.value);
    }

    return reservas;
});

const totalPages = computed(() => {
    return Math.ceil(filteredReservas.value.length / itemsPerPage.value);
});

const paginatedReservas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredReservas.value.slice(start, end);
});

watch([searchQuery, selectedGroup], () => {
    currentPage.value = 1;
});

const descargarReserva = (reserva) => {
    alert(`Simulación: Descargando documento para ${reserva.estudiante.nombre_completo}`);
};

const eliminarReserva = (reserva) => {
    if (confirm(`Simulación: ¿Estás seguro de eliminar la reserva de ${reserva.estudiante.nombre_completo}?`)) {
        estudiantesConReserva.value = estudiantesConReserva.value.filter(r => r.id !== reserva.id);
        alert('Reserva eliminada (simulado).');
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};
</script>

<template>
    <div class="w-full space-y-4 py-2 px-3">
        <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl m-2">
            Estudiantes con Reserva de Matrícula
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
            <FormInput v-model="searchQuery" placeholder="Buscar por nombre o DNI..." class="md:col-span-2" />
            <!-- <select
                v-model="selectedGroup"
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            >
                <option value="">Todos los Grupos</option>
                <option v-for="grupo in gruposUnicos" :key="grupo" :value="grupo">
                    {{ grupo }}
                </option>
            </select> -->
        </div>

        <Table>
            <THead>
                <Th>N°</Th>
                <Th>Estudiante</Th>
                <Th>DNI</Th>
                <Th>Grupo Reservado</Th>
                <Th class="text-center">Acciones</Th>
            </THead>
            <TBody>
                <Tr v-for="(reserva, index) in paginatedReservas" :key="reserva.id_matricula">
                    <Td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</Td>
                    <Td>{{ reserva.apellidos_nombres }}</Td>
                    <Td>{{ reserva.nro_documento }}</Td>
                    <Td>{{ reserva.especialidad }} - {{ reserva.modulo }}</Td>
                    <Td class="text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <button @click="descargarReserva(reserva)"
                                class="flex items-center gap-1 px-2 py-1.5 rounded-sm text-xsm shadow-google bg-blue-500 active:bg-blue-500 dark:bg-blue-800 active:dark:bg-blue-900 text-white dark:text-blue-200 hover:bg-blue-600 dark:hover:bg-blue-700 cursor-pointer transition duration-150">
                                <DownloadIcon class="w-4 h-4" />
                                <span>Descargar</span>
                            </button>
                            <button @click="eliminarReserva(reserva)"
                                class="flex items-center gap-1 px-2 py-1.5 rounded-sm text-xsm shadow-google bg-red-500 active:bg-red-500 dark:bg-red-800 active:dark:bg-red-900 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-red-700 cursor-pointer transition duration-150">
                                <TrashIcon class="w-4 h-4" />
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </Td>
                </Tr>

                <Tr v-if="filteredReservas.length === 0 && !loading">
                    <Td colspan="5" class="text-center py-4">No se encontraron resultados.</Td>
                </Tr>
                <Tr v-if="loading">
                    <Td colspan="5" class="text-center py-4">Cargando reservas...</Td>
                </Tr>
            </TBody>
        </Table>

        <div v-if="totalPages > 0" class="flex justify-between items-center mt-4">
            <button 
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                <span class="mr-2">←</span> Anterior
            </button>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ currentPage }} / {{ totalPages }}
            </span>
            <button 
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Siguiente <span class="ml-2">→</span>
            </button>
        </div>
    </div>
</template>