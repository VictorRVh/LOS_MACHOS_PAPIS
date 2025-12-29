<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import useTableData from "../../composables/tabla/useTableData";
import useModalToast from "../../composables/useModalToast";
import useEstudianteStore from "../../store/Estudiante/UseEstudianteStore";
import { generateConstanciaEgresado } from "../../pdf/ConstanciaEgresado";

const route = useRoute();
const estudianteStore = useEstudianteStore();
const { showToast } = useModalToast();

const idEspecialidad = route.params.id;
const idPeriodo = route.params.periodoId;

const estudiantes = ref([]);
const especialidad = ref(null);
const isLoading = ref(false);

onMounted(async () => {
    isLoading.value = true;

    const result = await estudianteStore.loadEstudiantesEgresados(idEspecialidad, idPeriodo);

    especialidad.value = estudianteStore.estudiantesEgresados.especialidad ?? null;
    estudiantes.value = estudianteStore.estudiantesEgresados.egresados ?? [];

    isLoading.value = false;
});

const estudiantesPlanos = computed(() => {
    return (estudiantes.value ?? []).map((e, index) => ({
        index: index + 1,
        id: e.id,
        dni: e.dni ?? "",
        apellidos: `${e.apellido_paterno ?? ""} ${e.apellido_materno ?? ""}`.trim(),
        nombres: e.nombre ?? "",
        telefono: e.telefono ?? "",
        correo: e.correo ?? "",
        fecha_registro: e.fecha_registro ?? "",
    }));
});

const {
    query,
    orderBy,
    orderDirection,
    pagina,
    itemsPorPagina,
    paginados: estudiantesPaginados,
    totalPaginas,
    ordenados: estudiantesOrdenados,
    filtrar: filtrarEstudiantes,
} = useTableData(estudiantesPlanos, {
    defaultOrderBy: "apellidos",
    searchFields: ["dni", "nombres", "apellidos", "correo"],
});

const handlePrintConstancia = (row) => {
    const data = {
        id_matricula: row.id,
        estudiante: `${row.apellidos} ${row.nombres}`,
        nro_documento: row.dni,
        especialidad: especialidad.value?.nombre_especialidad,
        ciclo: especialidad.value?.ciclo, 
    };
    generateConstanciaEgresado(data);
};
</script>

<template>
    <div class="p-4 md:p-6 space-y-4">

        <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-200">
            Especialidad: {{ especialidad?.nombre_especialidad ?? "" }}
        </h1>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-auto border border-gray-200 dark:border-gray-700">

            <div class="flex justify-between items-center p-2 pb-0">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Lista de Estudiantes ({{ estudiantes.length }})
                </h3>

                <SearchBar v-if="!isLoading && estudiantesPlanos.length > 0"
                    :totalResultados="estudiantesOrdenados.length" :campoOrden="'apellidos'"
                    @search="filtrarEstudiantes" />
            </div>

            <div v-if="isLoading" class="p-4 space-y-2">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
            </div>

            <Table v-else-if="estudiantesPaginados.length > 0" :paginacion="true" :current-page="pagina"
                :total-pages="totalPaginas" @changePage="pagina = $event" class="w-full border-collapse mt-2">
                <THead>
                    <Th class="w-[40px] text-center">#</Th>
                    <Th class="min-w-[120px]">DNI</Th>
                    <Th class="min-w-[200px]">Apellidos</Th>
                    <Th class="min-w-[200px]">Nombres</Th>
                    <Th class="text-center min-w-[100px]">Acciones</Th>
                </THead>

                <TBody>
                    <tr v-for="est in estudiantesPaginados" :key="est.id"
                        class="border-b border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 h-16">
                        <td class="text-center">{{ est.index }}</td>
                        <td>{{ est.dni }}</td>
                        <td>{{ est.apellidos }}</td>
                        <td>{{ est.nombres }}</td>
                        <td class="text-center">
                            <button 
                                @click="handlePrintConstancia(est)"
                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-100 hover:bg-red-200 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                title="Imprimir Constancia de Egresado"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </TBody>
            </Table>

            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                    No se encontraron egresados
                </h3>
                <p class="mt-1 text-sm text-gray-500">En esta especialidad no existen egresados todavía.</p>
            </div>

        </div>
    </div>
</template>