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
import estudianteSlider from "../../components/page/estudiante/estudianteSlider.vue";

import useestudianteStore from "../../store/estudiante/useestudianteStore";

import useSlider from "../../composables/useSlider";

import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";
import EstudianteSlider from "../../components/page/Estudiante/EstudianteSlider.vue";
import useEstudianteStore from "../../store/Estudiante/UseEstudianteStore";

const estudianteStore = useestudianteStore();

// if (!estudianteStore.estudiantes?.length) await estudianteStore.loadestudiantes();

const { slider, sliderData, showSlider, hideSlider } = useSlider("user-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteEstudiante, deleting } = useHttpRequest("/estudiante");

const showModal = ref(false);

const onDelete = (estudiante) => {
    if (deleting.value) return;

    showConfirmModal(null, async (confirmed) => {
        if (!confirmed) return;

        const isDeleted = await deleteEstudiante(estudiante?.id);
        if (isDeleted) {
            showToast(`"${estudiante?.name}" eliminado correctamente...`);
            estudianteStore.loadEstudiantes();
        }
    });
};


</script>

<template>
    <AuthorizationFallback :permissions="['todo-acceso-usuarios', 'ver-usuarios']">
        <div class="w-full space-y-2 py-2 px-3">
            <div class="m-2">
                <div class="flex-between">
                    <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">estudiantes</h2>
                    <CreateButton @click="showSlider(true)" />
                </div>


            </div>
            <THead>
                <Th>N°</Th>
                <Th>Nombres</Th>
                <Th>Apellidos</Th>
                <Th>Dni</Th>
                <Th>Correo</Th>

                <Th>Fecha de Creación</Th>
                <Th class="text-center">Acción</Th>
            </THead>

            <!-- <TBody>
                <Tr v-for="(estudiante, index) in usuariosPaginados" :key="index">
                    <Td><span class="text-gray-800 dark:text-gray-300">{{
                        (pagina - 1) * itemsPorPagina + index + 1
                            }}</span></Td>
                    <Td>{{ estudiante.nombre }}</Td>
                    <Td>{{ estudiante.apellido_paterno }} {{ estudiante.apellido_materno }}</Td>
                    <Td>{{ estudiante.dni }}</Td>
                    <Td>{{ estudiante.correo_electronico }}</Td>

                    <Td>{{ estudiante.created_at.slice(0, 10) }}</Td>
                </Tr>
            </TBody> -->
        </div>

        <EstudianteSlider :show="slider" :estudiante="sliderData" @hide="hideSlider" />
    </AuthorizationFallback>
    <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>
