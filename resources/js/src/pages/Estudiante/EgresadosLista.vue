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
import useModalToast from "../../composables/useModalToast";

import useTableData from "../../composables/tabla/useTableData";
import useEstudianteStore from "../../store/Estudiante/UseEstudianteStore";
import useDocumento from "../../store/Estudiante/UseDocumentoEgresadoStore.js";

import { generateConstanciaEgresado } from "../../pdf/ConstanciaEgresado";
import { generateTituloCetpro } from "../../pdf/TituloCetpro.js";
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import useHttpRequest from "../../composables/useHttpRequest.js";


const route = useRoute();
const estudianteStore = useEstudianteStore();
const egresadoDocumento = useDocumento();
const { showToast } = useModalToast();
const { store: createDocumento } = useHttpRequest("/egresado-documento");

const idEspecialidad = route.params.id;
const idPeriodo = route.params.periodoId;

const estudiantes = ref([]);
const especialidad = ref(null);
const isLoading = ref(false);

const breadcrumb = useBreadcrumbStore();

/* ============================
   MODAL DOCUMENTO GENERAL
============================ */
const showDocumentoModal = ref(false);
const tipoDocumento = ref(null); // 1 constancia | 2 titulo
const estudianteSeleccionado = ref(null);

const codigoInstitucional = ref("");
const codigoUgel = ref("");
const esDuplicado = ref(false);






onMounted(async () => {
    isLoading.value = true;

    await estudianteStore.loadEstudiantesEgresados(idEspecialidad, idPeriodo);
    especialidad.value = estudianteStore.estudiantesEgresados?.especialidad ?? null;
    estudiantes.value = estudianteStore.estudiantesEgresados?.egresados ?? [];

    isLoading.value = false;

    breadcrumb.setTextItemAuto(
        `Egresados`,
        idEspecialidad,
        "egresados",
        { name: 'egresadosLista', params: { id: idEspecialidad, periodoId: idPeriodo } }
    );
});

/* ============================
   DATOS PLANOS
============================ */
const estudiantesPlanos = computed(() =>
    estudiantes.value.map((e, index) => ({
        index: index + 1,
        id: e.id,
        id_egresado: e.id_egresado,
        dni: e.dni ?? "",
        apellidos: `${e.apellido_paterno ?? ""} ${e.apellido_materno ?? ""}`.trim(),
        nombres: e.nombre ?? "",
        correo: e.correo ?? "",
    }))
);

/* ============================
   TABLA
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


const handlePrintConstancia = async (row) => {
    estudianteSeleccionado.value = row;
    tipoDocumento.value = 1;

    codigoInstitucional.value = "";
    codigoUgel.value = "";
    esDuplicado.value = false;

    const res = await egresadoDocumento.loadEgresadoDocumento(
        row.id_egresado,
        1
    );

    if (res?.existe) {
        esDuplicado.value = true;
        codigoInstitucional.value = res.data.codigo_institucion ?? "";

        showToast(
            `Ya existe constancia con código: ${codigoInstitucional.value}`,
            "warning"
        );
    }

    showDocumentoModal.value = true;
};

const handlePrintTitulo = async (row) => {
    estudianteSeleccionado.value = row;
    tipoDocumento.value = 2;

    codigoInstitucional.value = "";
    codigoUgel.value = "";
    esDuplicado.value = false;

    const res = await egresadoDocumento.loadEgresadoDocumento(
        row.id_egresado,
        2
    );

    if (res?.existe) {
        esDuplicado.value = true;
        codigoInstitucional.value = res.data.codigo_institucion ?? "";
        codigoUgel.value = res.data.codigo_ugel ?? "";

        showToast(
            `Ya existe título con código: ${codigoInstitucional.value}`,
            "warning"
        );
    }

    showDocumentoModal.value = true;
};

const emitirDocumento = async () => {
    const row = estudianteSeleccionado.value;
    if (!row) return;

    if (!codigoInstitucional.value.trim()) {
        showToast("Ingresa el código institucional", "warning");
        return;
    }

    if (tipoDocumento.value === 2 && !codigoUgel.value.trim()) {
        showToast("Ingresa el código UGEL", "warning");
        return;
    }

    try {
        const response = await createDocumento({
            id_egresado: row.id_egresado,
            tipo_documento: tipoDocumento.value,
            codigo_institucion: codigoInstitucional.value.trim(),
            codigo_ugel: tipoDocumento.value === 2
                ? codigoUgel.value.trim()
                : null
        });

        if (tipoDocumento.value === 1) {
            await generateConstanciaEgresado(
                row.id_egresado,
                response.data.codigo_institucion
            );
        } else {
            await generateTituloCetpro(
                row.id_egresado,
                response.data.codigo_institucion,
                response.data.codigo_ugel,
            );
        }

        showToast(
            esDuplicado.value
                ? "Documento generado como DUPLICADO"
                : "Documento generado correctamente",
            esDuplicado.value ? "warning" : "success"
        );

        showDocumentoModal.value = false;

    } catch (error) {
        console.error(error);
        showToast("Error al generar documento", "error");
    }
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
                                <BaseButton title="Constancia de Egresado" @click="handlePrintConstancia(est)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white">
                                    <template #icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>

                                    </template>
                                </BaseButton>

                                <BaseButton title="Título" @click="handlePrintTitulo(est)"
                                    class="bg-slate-700 hover:bg-slate-800 text-white">
                                    <template #icon>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 4.5h12.75a2.25 2.25 0 0 1 2.25 2.25V19.5m-15 0h12.75a2.25 2.25 0 0 0 2.25-2.25M3 4.5v15m0 0h15m-4.5-9.75h.008v.008h-.008V9.75Zm0 3.75h.008v.008h-.008V13.5Zm-3.75-3.75h.008v.008h-.008V9.75Zm0 3.75h.008v.008h-.008V13.5Z" />
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

    <div v-if="showDocumentoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">

            <h2 class="text-lg font-semibold mb-4">
                {{ tipoDocumento === 1
                    ? 'Emitir Constancia de Egresado'
                    : 'Emitir Título'
                }}
            </h2>

            <!-- AVISO DUPLICADO -->
            <div v-if="esDuplicado" class="mb-3 p-3 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
                Este documento ya fue emitido.<br>
                Se marcará como <b>DUPLICADO</b>.
            </div>

            <div class="space-y-4 mb-4">

                <!-- Código Institucional -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Código Institucional
                    </label>
                    <input v-model="codigoInstitucional" type="text" class="w-full px-3 py-2 border rounded-lg" />
                </div>

                <!-- Código UGEL (SOLO TÍTULO) -->
                <div v-if="tipoDocumento === 2">
                    <label class="block text-sm font-medium mb-1">
                        Código UGEL
                    </label>
                    <input v-model="codigoUgel" type="text" class="w-full px-3 py-2 border rounded-lg" />
                </div>

            </div>

            <div class="flex justify-end gap-2">
                <button @click="showDocumentoModal = false" class="px-4 py-2 rounded-lg bg-gray-200">
                    Cancelar
                </button>

                <button @click="emitirDocumento" class="px-4 py-2 rounded-lg bg-blue-600 text-white">
                    {{ esDuplicado ? 'Emitir DUPLICADO' : 'Emitir' }}
                </button>
            </div>

        </div>
    </div>
</template>
