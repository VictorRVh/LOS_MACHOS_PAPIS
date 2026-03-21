<script setup>
import { computed, ref } from "vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
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
  const todosUsuarios = useUserStore?.users || [];
  const currentUserIds = sliderData.value?.usuarios?.map((user) => user.id) || [];

  return todosUsuarios.filter((usuario) => !usuario.deleted_at || currentUserIds.includes(usuario.id));
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
  showModal.value = true;
}

const totalComisiones = computed(() => comisionesStore.comisiones.length);
const totalUsuariosAsignados = computed(() =>
  comisionesStore.comisiones.reduce((acc, comision) => acc + (comision.usuarios?.length || 0), 0)
);
const comisionesMultipersona = computed(() =>
  comisionesStore.comisiones.filter((comision) => (comision.usuarios?.length || 0) > 1).length
);
const promedioIntegrantes = computed(() => {
  if (!comisionesStore.comisiones.length) return 0;
  return Math.round(totalUsuariosAsignados.value / comisionesStore.comisiones.length);
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-comisiones', 'ver-comisiones']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Comision">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Total comisiones</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalComisiones }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registradas</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Usuarios asignados</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalUsuariosAsignados }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Total</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Con múltiples usuarios</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ comisionesMultipersona }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Ver más</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Promedio integrantes</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ promedioIntegrantes }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Por comisión</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <div class="flex flex-col gap-4 lg:flex-row">
        <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3">
          <comisionSlider :show="slider" :comision="sliderData" :users-filter="UsuariosDisponibles" @hide="hideSlider" />
        </section>

        <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-2/3">
          <div class="overflow-x-auto">
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
                    <div :class="['flex w-full gap-2', comision.usuarios.length > 1 ? 'items-center justify-between' : 'justify-start']">
                      <ul class="w-30 list-none text-sm text-gray-700 dark:text-gray-300">
                        <li v-for="usuario in comision.usuarios.slice(0, 1)" :key="usuario.id">
                          {{ usuario.nameCompleto }}
                        </li>
                      </ul>
                      <button
                        v-if="comision.usuarios.length > 1"
                        @click="showPermissionsModal(comision)"
                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 transition hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800"
                      >
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
        </section>
      </div>

      <ModalRoles v-if="showModal" @close="showModal = false" class="font-inter">
        <template #title>
          Usuarios de la comisión:
          <span class="font-bold uppercase text-cetpro dark:text-cetpro-light">{{ selectedRole?.titulo }}</span>
        </template>

        <template #body>
          <ul class="ml-4 space-y-2">
            <li v-for="usuario in selectedRole?.usuarios" :key="usuario.id" class="text-sm text-gray-600 dark:text-gray-300">
              - {{ usuario.nameCompleto }}
            </li>
          </ul>
        </template>

        <template #footer>
          <button @click="showModal = false" class="rounded-md bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-700">
            Cerrar
          </button>
        </template>
      </ModalRoles>
    </div>
  </AuthorizationFallback>
</template>
