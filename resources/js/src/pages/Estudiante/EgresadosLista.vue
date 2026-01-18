<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";

import SearchBar from "../../components/head_table/headSearch.vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";

import BaseButton from "../../components/ui/Button.vue";

import useTableData from "../../composables/tabla/useTableData";
import useEstudianteStore from "../../store/Estudiante/UseEstudianteStore";
import useCertificado from "../../store/Grupo/useCertificadoStore.js";

import { generateConstanciaEgresado } from "../../pdf/ConstanciaEgresado";

const route = useRoute();
const estudianteStore = useEstudianteStore();
const certificadoStore = useCertificado();

const idEspecialidad = route.params.id;
const idPeriodo = route.params.periodoId;

const estudiantes = ref([]);
const especialidad = ref(null);
const isLoading = ref(false);

onMounted(async () => {
    isLoading.value = true;

    await estudianteStore.loadEstudiantesEgresados(idEspecialidad, idPeriodo);

    especialidad.value = estudianteStore.estudiantesEgresados?.especialidad ?? null;
    estudiantes.value = estudianteStore.estudiantesEgresados?.egresados ?? [];

    isLoading.value = false;
});

/* ============================
   DATOS PLANOS PARA LA TABLA
============================ */
const estudiantesPlanos = computed(() =>
    estudiantes.value.map((e, index) => ({
        index: index + 1,
        id: e.id,
        dni: e.dni ?? "",
        apellidos: `${e.apellido_paterno ?? ""} ${e.apellido_materno ?? ""}`.trim(),
        nombres: e.nombre ?? "",
        correo: e.correo ?? "",
    }))
);

/* ============================
   TABLA (FILTRO / PAGINACIÓN)
============================ */
const {
    pagina,
    totalPaginas,
    paginados: estudiantesPaginados,
    ordenados: estudiantesOrdenados,
    filtrar: filtrarEstudiantes,
} = useTableData(estudiantesPlanos, {
    defaultOrderBy: "apellidos",
    searchFields: ["dni", "nombres", "apellidos", "correo"],
});

/* ============================
   ACCIONES
============================ */
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

const openCertificadoModal = (idMatricula) => {
    certificadoStore.openModal(idMatricula);
};
</script>

<template>
    <div class="p-4 md:p-6 space-y-4">

        <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-200">
            Especialidad: {{ especialidad?.nombre_especialidad ?? "" }}
        </h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="flex justify-between items-center p-3">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Lista de Estudiantes 
                </h3>

                <SearchBar v-if="!isLoading && estudiantesPlanos.length" :totalResultados="estudiantesOrdenados.length"
                    campoOrden="apellidos" @search="filtrarEstudiantes" />
            </div>

            <!-- LOADING -->
            <div v-if="isLoading" class="p-4 space-y-2">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
            </div>

            <!-- TABLA -->
            <Table v-else-if="estudiantesPaginados.length" paginacion :current-page="pagina" :total-pages="totalPaginas"
                @changePage="pagina = $event">
                <THead>
                    <Th class="text-center w-10">#</Th>
                    <Th>DNI</Th>
                    <Th>Apellidos</Th>
                    <Th>Nombres</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>

                <TBody>
                    <tr v-for="est in estudiantesPaginados" :key="est.id"
                        class="border-b hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="text-center">{{ est.index }}</td>
                        <td>{{ est.dni }}</td>
                        <td>{{ est.apellidos }}</td>
                        <td>{{ est.nombres }}</td>

                        <Td>
                            <div class="flex gap-2 justify-center">

                                <!-- CONSTANCIA -->
                                <BaseButton title="Constancia" @click="handlePrintConstancia(est)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white">
                                    <template #icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>

                                    </template>
                                </BaseButton>

                                <!-- CERTIFICADO -->
                                <BaseButton title="Certificado Modular" @click="openCertificadoModal(est.id)"
                                    class="bg-green-600 hover:bg-green-700 text-white">
                                    <template #icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>

                                    </template>
                                </BaseButton>

                            </div>
                        </Td>
                    </tr>
                </TBody>
            </Table>

            <!-- VACÍO -->
            <div v-else class="text-center py-12 text-gray-500">
                No existen egresados en esta especialidad
            </div>

        </div>
    </div>
</template>
