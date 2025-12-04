<script setup>
import { onMounted, computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import Button from '../../components/ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';

import StudentInfoModal  from '../../components/page/Matricula/ViewModalSlider.vue'
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import useGrupoStore from '../../store/Grupo/useGrupoStore';
import { generatePdfMatricula } from '../../pdf/fichaMatricula';
import useModalToast from '../../composables/useModalToast';
import ConfirmModalReserva from '../../components/page/ConfirmModalReserva.vue';
import Slider from '../../components/ui/Slider.vue';
import BaseSelectGrupo from '../../components/ui/BaseSelectGrupo.vue';
import FormLabelError from '../../components/ui/FormLabelError.vue';
// --- NUEVO ---
import SearchBar from "../../components/head_table/headSearch.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import useTableData from "../../composables/tabla/useTableData";
import useHttpRequest from "../../composables/useHttpRequest";

import useExportAlumnos from "@/composables/tabla/useAlumnosMatricula";








const props = defineProps({
    id: { type: [String], required: true },
});

//console.log(" id de grupo: ".props?.id)

const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteMatricula, deleting } = useHttpRequest("/matricula");

const matriculaStore = useMatriculaStore();
const grupoStore = useGrupoStore();
const router = useRouter();

//variaalbes para mostra informacion del estudiante
const showInfoModal = ref(false)
const estudianteSeleccionado = ref(null)


const verEstudiante = (matricula) => {
    estudianteSeleccionado.value = matricula
    showInfoModal.value = true
}


onMounted(() => {
    loading.value = true;
    setTimeout(async () => {
        await matriculaStore.fetchMatriculadosPorGrupo(props?.id)
        loading.value = false;
    }, 1000);
});

const matriculados = computed(() => matriculaStore.matriculadosPorGrupo)
const loading = ref(false)
const estudiantesSeleccionados = ref([]);

const { exportarAlumnos } = useExportAlumnos();


const nuevoGrupoId = ref("");
const saving = ref(false);

const showCambioGrupoModal = ref(false)
const showReservaModal = ref(false)

const selectedMatricula = ref(null)

const todosSeleccionados = computed({
    get: () => {
        const estudiantes = matriculados.value?.matriculados ?? []
        return estudiantes.length > 0 && estudiantesSeleccionados.value.length === estudiantes.length
    },
    set: (value) => {
        const estudiantes = matriculados.value?.matriculados ?? []
        estudiantesSeleccionados.value = value ? estudiantes.map(e => e.id_matricula) : []
    }
})

watch(showCambioGrupoModal, async (nuevoValor) => {
    if (nuevoValor) {
        console.log("params:", matriculados.value.id_periodo, matriculados.value.id_grupo)

        await grupoStore.loadGruposDisponibles(
            matriculados.value.id_periodo,
            matriculados.value.id_grupo
        )

        console.log("gruposDisponibles:", grupoStore.gruposDisponibles)
    }
})

const cambiarGrupo = async () => {
    if (estudiantesSeleccionados.value.length === 0) {
        alert("Selecciona al menos un estudiante.")
        return
    }
    if (!nuevoGrupoId.value) {
        alert("Selecciona un grupo destino.")
        return
    }

    console.log(estudiantesSeleccionados.value, nuevoGrupoId.value)

    saving.value = true
    try {
        await matriculaStore.loadCambioMatricula(estudiantesSeleccionados.value, nuevoGrupoId.value)

        await matriculaStore.fetchMatriculadosPorGrupo(props?.id)

        estudiantesSeleccionados.value = []
        nuevoGrupoId.value = ""
        showCambioGrupoModal.value = false
        showToast("Cambio de grupo exitoso")

    } catch (error) {
        showToast("Error al cambiar grupo")
    } finally {
        saving.value = false
    }
}

const abrirModalReserva = (matricula) => {
    selectedMatricula.value = matricula
    showReservaModal.value = true
}

const confirmarReserva = async () => {
    try {
        await matriculaStore.loadReservaMatricula(selectedMatricula.value.id_matricula)
        await matriculaStore.fetchMatriculadosPorGrupo(props?.id)
    } catch (error) {
        console.error(error)
        alert('Error al reservar la matrícula')
    } finally {
        showReservaModal.value = false
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


const EditarMatricula = (idMatricula) => {
    // Redirige al componente de matrícula con el id para edición
    router.push({ name: 'matricula.editar', params: { id: idMatricula } })
}
// Lista raw desde el store
const listaMatriculados = computed(() => matriculados.value?.matriculados ?? []);

// Aplicando filtrado + ordenamiento + paginación
const {
    query,
    orderBy,
    orderDirection,
    pagina,
    itemsPorPagina,
    paginados: matriculadosPaginados,
    totalPaginas,
    ordenados: matriculadosOrdenados,
    filtrar: filtrarMatriculados
} = useTableData(listaMatriculados, {
    defaultOrderBy: "apellidos",
    searchFields: ["apellidos", "nombre", "nro_documento", "celular_personal", "correo_electronico"]
});



const EliminarMatricula = (idMatricula, nombre) => {

    if (deleting.value) return;

    showConfirmModal(null, async (confirmed) => {
        if (!confirmed) return;

        const isDeleted = await deleteMatricula(idMatricula);
        if (isDeleted) {
            console.log('eliminadno ')
            showToast(`Matrícula de  "${nombre}" eliminada exitosamente...`);
            await matriculaStore.fetchMatriculadosPorGrupo(props?.id);
        }
    });
};

</script>
<template>
    <AuthorizationFallback :permissions="['todo-acceso-grupo', 'ver-grupos']">
        <div class="w-full px-3" v-if="matriculados">
            <div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <!-- 🟦 IZQUIERDA: Detalles del grupo -->
                    <div class="text-sm">
                        <h2 class="text-base font-bold text-cetpro dark:text-cetpro-light mb-1">
                            Especialidad:
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ matriculados.especialidad }}
                            </span>
                        </h2>

                        <p class="text-gray-700 dark:text-gray-300">
                            Módulo:
                            <span class="font-semibold">
                                {{ matriculados.modulo }}
                            </span> |
                            Turno:
                            <span class="font-semibold">
                                {{ matriculados.turno }}
                            </span> |
                            Sección:
                            <span class="font-semibold">
                                {{ matriculados.seccion }}
                            </span>
                        </p>
                    </div>

                    <!-- 🟥 DERECHA: Botones -->
                    <div class="flex flex-wrap gap-3 justify-end">

                        <!-- CAMBIAR GRUPO -->
                        <Button title="Cambiar de Grupo" variant="secondary" @click="showCambioGrupoModal = true"
                            :disabled="estudiantesSeleccionados.length === 0">
                            <template #icon>
                                <ArrowPathIcon class="h-4 w-4" />
                            </template>

                            Cambiar Grupo ({{ estudiantesSeleccionados.length }})
                        </Button>

                        <!-- EXPORTAR ALUMNOS -->
                        <Button title="Exportar Alumnos" variant="secondary" @click="exportarAlumnos(matriculados)">
                            <template #icon>
                                <ArrowDownTrayIcon class="h-4 w-4" />
                            </template>

                            Exportar Alumnos
                        </Button>

                    </div>
                </div>

                <!-- 🔍 BUSCADOR ABAJO PERO DENTRO DEL MISMO BLOQUE -->
                <div class="flex justify-end my-2">
                    <SearchBar :totalResultados="matriculadosOrdenados.length" :campoOrden="'apellidos'"
                        @search="filtrarMatriculados" />
                </div>
            </div>



            <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas" @changePage="pagina = $event">

                <THead>
                    <Th class="w-10 text-center">
                        <input type="checkbox" v-model="todosSeleccionados"
                            class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                    </Th>
                    <Th>N°</Th>
                    <Th>Estudiante</Th>
                    <Th>DNI</Th>
                    <Th>Sexo</Th>

                    <Th>Celular</Th>
                    <Th>Correo</Th>
                    <Th>F. Matrícula</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>

                <TBody>
                    <Tr v-for="(matricula, index) in matriculadosPaginados" :key="matricula.id_matricula"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50">

                        <Td class="text-center">
                            <input type="checkbox" :value="matricula.id_matricula" v-model="estudiantesSeleccionados"
                                class="rounded border-gray-300 text-cetpro focus:ring-cetpro-light" />
                        </Td>

                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ matricula.apellidos }}, {{ matricula.nombre }}</Td>
                        <Td>{{ matricula.nro_documento }}</Td>
                        <Td>{{ matricula.sexo }}</Td>


                        <Td>{{ matricula.celular_personal }}</Td>
                        <Td>{{ matricula.correo_electronico }}</Td>


                        <!-- FECHA DE MATRÍCULA CORREGIDA -->
                        <Td>{{ matricula.created_at }}</Td>

                        <Td class="text-center">
                            <MenuTable :actions="{
                                view: true,
                                edit: true,
                                delete: true,
                                download: true,
                                custom1: true
                            }" :labels="{
                                view: 'Ver estudiante',
                                edit: 'Editar matrícula',
                                custom1: 'Reservar matrícula',
                                download: 'Descargar ficha',
                                delete: 'Eliminar matrícula'
                            }" @view="verEstudiante(matricula)" @edit="EditarMatricula(matricula.id_matricula)"
                                @custom1="abrirModalReserva(matricula)"
                                @delete="EliminarMatricula(matricula.id_matricula, `${matricula.nombre}, ${matricula.apellidos}`)"
                                @download="exportarFicha(matricula.id_estudiante)" />

                        </Td>


                    </Tr>

                    <Tr v-if="matriculados?.matriculados?.length === 0 && !loading">
                        <Td colspan="12" class="text-center py-4">No hay estudiantes matriculados en este grupo.</Td>
                    </Tr>

                    <Tr v-if="loading">
                        <Td colspan="12" class="text-center py-4">Cargando estudiantes...</Td>
                    </Tr>

                </TBody>
            </Table>

        </div>
        <div v-else class="text-center p-8">Cargando información del grupo...</div>

        <Slider :show="showCambioGrupoModal" title="Cambiar Grupo" @hide="showCambioGrupoModal = false">
            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

            <div class="mt-4 space-y-3">
                <p class="text-gray-600 dark:text-gray-300">
                    Estás a punto de mover <strong>{{ estudiantesSeleccionados.length }}</strong> estudiantes.
                </p>

                <div class="grid grid-cols-1 gap-4">
                    <label class="font-medium">Grupos disponibles</label>
                    <BaseSelectGrupo v-model="nuevoGrupoId" :options="grupoStore.gruposDisponibles" label="nombre_grupo"
                        value-prop="id" placeholder="Seleccione un grupo" />

                    <!-- <select v-model="nuevoGrupoId" class="border rounded p-2">
                        <option disabled value="">-- Selecciona Grupo --</option>
                        <option v-for="grupo in grupoStore.gruposDisponibles" :key="grupo.id" :value="grupo.id">
                            {{ grupo.nombre_grupo }}
                        </option>
                    </select> -->
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <Button title="Cancelar" variant="secondary" @click="showCambioGrupoModal = false" />
                    <Button title="Confirmar" variant="primary" :disabled="!nuevoGrupoId || saving" :loading="saving"
                        @click="cambiarGrupo" />
                </div>
            </div>
        </Slider>

        <ConfirmModalReserva :show="showReservaModal" :estudiante="selectedMatricula" @close="showReservaModal = false"
            @confirm="confirmarReserva" />
        <StudentInfoModal :show="showInfoModal" :data="estudianteSeleccionado" @close="showInfoModal = false" />

    </AuthorizationFallback>

</template>