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

    // if (!result) {
    //     showToast("No se pudieron cargar los estudiantes.", "error");
    // }

    // forzar array vacío si es undefined
    // especialidad.value = estudianteStore.especialidadActual || null;
    estudiantes.value = estudianteStore.estudiantesEgresados;

    isLoading.value = false;
});


// -------- Tabla plana de estudiantes
const estudiantesPlanos = computed(() => {
    return estudiantes.value.map((e, index) => ({
        index: index + 1,
        id: e.id,
        dni: e.dni ?? "",
        nombres: e.nombres ?? "",
        apellidos: e.apellidos ?? "",
        telefono: e.telefono ?? "",
        correo: e.correo ?? "",
        fecha_registro: e.fecha_registro ?? "",
    }));
});

// ------- useTableData (igual al patrón grupos)
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
</script>


<template>
    <div class="p-4 md:p-6 space-y-4">

        <!-- Título -->
        <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-200">
            Especialidad: {{ especialidad?.nombre_especialidad ?? "" }}
        </h1>

        <!-- Tarjeta de tabla -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-auto border border-gray-200 dark:border-gray-700">

            <!-- ENCABEZADO + BUSCADOR -->
            <div class="flex justify-between items-center p-2 pb-0">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Lista de Estudiantes ({{ estudiantes.length }})
                </h3>

                <SearchBar v-if="!isLoading && estudiantesPlanos.length > 0"
                    :totalResultados="estudiantesOrdenados.length" :campoOrden="'apellidos'"
                    @search="filtrarEstudiantes" />
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="p-4 space-y-2">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
            </div>

            <!-- TABLA -->
            <Table v-else-if="estudiantesPaginados.length > 0" :paginacion="true" :current-page="pagina"
                :total-pages="totalPaginas" @changePage="pagina = $event" class="w-full border-collapse mt-2">
                <THead>
                    <Th class="w-[40px] text-center">#</Th>
                    <Th class="min-w-[120px]">DNI</Th>
                    <Th class="min-w-[200px]">Apellidos</Th>
                    <Th class="min-w-[200px]">Nombres</Th>
                    <Th class="min-w-[150px]">Teléfono</Th>
                    <Th class="min-w-[220px]">Correo</Th>
                    <Th class="min-w-[160px]">Fecha Registro</Th>
                </THead>

                <TBody>
                    <tr v-for="est in estudiantesPaginados" :key="est.id"
                        class="border-b border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="text-center">{{ est.index }}</td>
                        <td>{{ est.dni }}</td>
                        <td>{{ est.apellidos }}</td>
                        <td>{{ est.nombres }}</td>
                        <td>{{ est.telefono }}</td>
                        <td>{{ est.correo }}</td>
                        <td>{{ est.fecha_registro }}</td>
                    </tr>
                </TBody>
            </Table>

            <!-- Sin resultados -->
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                    No se encontraron estudiantes
                </h3>
                <p class="mt-1 text-sm text-gray-500">Esta especialidad no tiene estudiantes registrados.</p>
            </div>

        </div>
    </div>
</template>
