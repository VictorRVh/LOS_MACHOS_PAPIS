<script setup>
import { computed, onMounted, ref, watch } from "vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import ModalRoles from "../../layouts/components/ModalRoles.vue";
import useHttpRequest from "../../composables/useHttpRequest";


import comisionSlider from "../../components/page/Comision/ComisionSlider.vue";
import useComisionesStore from "../../store/Comision/useComisionesStore";
import useUserStatuStore from "../../store/User/useUserStatusStore";

const comisionesStore = useComisionesStore();
const useUserStore = useUserStatuStore();

if (!comisionesStore.comisiones.length) await comisionesStore.loadComisiones();

if (!useUserStore.users.length) await useUserStore.loadUsers();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletecomision, deleting } = useHttpRequest("/comisiones");



const UsuariosDisponibles = computed(() => {
  // todos los usuarios activos
  const todosUsuarios = useUserStore?.users || [];

  // todos los IDs de usuarios ya asignados a comisiones
  const usuariosAsignados = comisionesStore?.comisiones
    ?.flatMap(c => c.usuarios?.map(u => u.id)) || [];

  // si estoy editando, mantener el usuario ya asignado
  const currentUserIds = sliderData.value?.usuarios?.map(u => u.id) || [];

  return todosUsuarios.filter(usuario =>
    !usuariosAsignados.includes(usuario.id) || currentUserIds.includes(usuario.id)
  );
});



const onDelete = (comision) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletecomision(comision?.id);
    if (isDeleted) {
      showToast(`Comisión "${comision?.titulo}" eliminada exitosamente...`);
      comisionesStore.loadComisiones();
      comisionesStore.loadComisionesUserFilter();
    }
  });
};

const showModal = ref(false);
const selectedRole = ref(null);

function showPermissionsModal(comision) {

  selectedRole.value = comision;

  // console.log('comision selec', selectedRole.value)

  showModal.value = true;
}
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-comisiones', 'ver-comisiones']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Comisión</h2>
    </div>

    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
          Agregar comisión
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <comisionSlider :show="slider" :comision="sliderData" :users-filter="UsuariosDisponibles" @hide="hideSlider" />
      </div>

      <div class="w-full lg:w-2/3">
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <Table>
              <THead>
                <Th>#</Th>
                <Th>Comisión</Th>
                <Th>Usuarios</Th>
                <Th>Acciones</Th>
              </THead>

              <TBody>
                <Tr v-for="(comision, index) in comisionesStore.comisiones" :key="comision.id">
                  <Td>{{ index + 1 }}</Td>
                  <Td>{{ comision?.titulo }}</Td>

                  <Td class="w-48 whitespace-nowrap">
                    <div :class="[
                      'flex gap-2 w-full',
                      comision.usuarios.length > 1
                        ? 'justify-between items-center'
                        : 'justify-start',
                    ]">
                      <ul class="text-sm text-gray-700 dark:text-gray-300 w-30 list-none">
                        <li v-for="(usuario, index) in comision.usuarios.slice(0, 1)" :key="usuario.id">
                          {{ usuario.nameCompleto }}
                        </li>
                      </ul>
                      <button v-if="comision.usuarios.length > 1" @click="showPermissionsModal(comision)"
                        class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-0.5 text-xs rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition">
                        Ver más ({{ comision.usuarios.length }})
                      </button>
                    </div>
                  </Td>

                  <Td>
                    <div class="flex items-center justify-center gap-1">
                      <EditButton @click="showSlider(true, comision)" />
                      <DeleteButton @click="onDelete(comision)" />
                    </div>
                  </Td>
                </Tr>
              </TBody>
            </Table>
        </div>
      </div>

      <ModalRoles v-if="showModal" @close="showModal = false" class="font-inter">
        <template #title>
          Usuarios de la comisión: <span class="uppercase font-bold text-cetpro dark:text-cetpro-light">{{ selectedRole?.titulo }}</span>
        </template>

        <template #body>
          <ul class="ml-4 space-y-2">
            <li class="text-sm text-gray-600 dark:text-gray-300" v-for="usuario in selectedRole?.usuarios" :key="usuario.id">
              - {{ usuario.nameCompleto }}
            </li>
          </ul>
        </template>

        <template #footer>
          <button @click="showModal = false"
            class="px-4 py-2 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700">
            Cerrar
          </button>
        </template>
      </ModalRoles>

    </div>
  </AuthorizationFallback>
</template>