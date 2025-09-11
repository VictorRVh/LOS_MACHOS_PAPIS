<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

import SearchBar from "../../components/head_table/headSearch.vue";

import { storeToRefs } from "pinia";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";

import CreateButton from "../../components/ui/CreateButton.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import DocenteSlider from '../../components/page/Docente/DocenteSlider.vue'

import useDocenteStore from "../../store/Docente/useDocenteStore";

import useSlider from "../../composables/useSlider";

import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";

const docenteStore = useDocenteStore();

if (!docenteStore.modulosAsignados?.length) await docenteStore.loadModulosAsignados();

const { slider, sliderData, showSlider, hideSlider } = useSlider("docente-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteDocente, deleting } = useHttpRequest("/docente");

const showModal = ref(false);

const modulos = computed(() => docenteStore.modulosAsignados);


</script>

<template>
    <AuthorizationFallback :permissions="['ver-mis-modulos', 'ver-estudiantes-asignados']">
        <div class="w-full space-y-2 py-2 px-3">
            <div class="m-2">
                <div class="flex-between">
                    <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Modulos Asignados</h2>
                    <CreateButton @click="showSlider(true)" />
                </div>
                <div class="font-inter text-md w-full">Lista</div>
            </div>
            <Table>
                <THead>
                    <Th>N°</Th>
                    <Th>Especialidad</Th>
                    <Th>Modulo</Th>
                    <Th>Docente</Th>
                    <Th>Fecha de Inicio</Th>
                    <Th>Fecha de Fin</Th>
                    <Th>Sección</Th>
                    <Th>Turno</Th>
                    <Th>Matriculados</Th>
                    <Th class="text-center">Acción</Th>
                </THead>

                <TBody>
                    <Tr v-for="(modulo, index) in modulos" :key="index">
                        <Td><span class="text-gray-800 dark:text-gray-300">{{ index + 1 }}</span></Td>
                        <Td>{{ modulo.especialidad }}</Td>
                        <Td>{{ modulo.modulo }}</Td>
                        <Td>{{ modulo.docente }}</Td>
                        <Td>{{ modulo.fecha_inicio }}</Td>
                        <Td>{{ modulo.fecha_fin }}</Td>
                        <Td>{{ modulo.seccion }}</Td>
                        <Td>{{ modulo.turno }}</Td>
                        <Td>{{ modulo.matriculados }}</Td>
                        <Td class="text-center text-gray-600 dark:text-gray-200">
                            <button>Nomina de matricula</button>
                            <button>Capacidades Terminales</button>
                            <button>Evaluaciones</button>
                            <button>Practicas</button>
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>

        <DocenteSlider :show="slider" :docente="sliderData" @hide="hideSlider" />
    </AuthorizationFallback>
    <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>
