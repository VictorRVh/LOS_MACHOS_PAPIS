<script setup>
import { computed } from "vue";

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
import ConvenioSlider from "../../components/page/ConvenioSlider.vue";

import useUserStore from "../../store/useUserStore";
import useRoleStore from "../../store/useRoleStore";
import usePermissionStore from "../../store/usePermissionStore";
import useConveniosStore from "../../store/Convenio/useConvenioStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();
const conveniosStore = useConveniosStore();

if (!permissionStore.permissions.length) await permissionStore.loadPermissions();
if (!roleStore.roles?.length) await roleStore.loadRoles();
if (!conveniosStore.convenios.length) await conveniosStore.loadConvenios();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteConvenio, deleting } = useHttpRequest("/convenio");

const onDelete = (convenio) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteConvenio(convenio?.id);
    if (isDeleted) {
      showToast(`Convenio "${convenio?.nombre_institucion}" eliminado exitosamente.`);
      conveniosStore.loadConvenios();
    }
  });
};

const totalModalidades = computed(() => conveniosStore.convenios.length);
const modalidadesLargas = computed(() =>
  conveniosStore.convenios.filter((convenio) => (convenio?.nombre_institucion || "").length > 20).length
);
const promedioCaracteres = computed(() => {
  if (!conveniosStore.convenios.length) return 0;
  const total = conveniosStore.convenios.reduce(
    (acc, convenio) => acc + (convenio?.nombre_institucion || "").length,
    0
  );
  return Math.round(total / conveniosStore.convenios.length);
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-modalidades', 'ver-modalidades']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Modalidades">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total modalidades
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalModalidades }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registradas</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Nombres extensos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ modalidadesLargas }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">RevisiÃ³n</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Promedio de texto
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ promedioCaracteres }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Caracteres</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Vista actual
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalModalidades }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Sin filtro</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <div class="flex flex-col gap-4 lg:flex-row">
        <section
          class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3"
        >
          <div class="bg-white dark:bg-gray-800">
            <ConvenioSlider :show="slider" :convenio="sliderData" @hide="hideSlider" />
          </div>
        </section>

        <section
          class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-2/3"
        >
          <Table>
            <THead>
              <Th>Id</Th>
              <Th>Modalidad</Th>
              <Th class="text-center">Acciones</Th>
            </THead>

            <TBody>
              <Tr v-for="(convenio, index) in conveniosStore.convenios" :key="convenio.id">
                <Td>{{ index + 1 }}</Td>
                <Td>{{ convenio?.nombre_institucion }}</Td>
                <Td class="align-middle">
                  <div class="flex items-center justify-center gap-1">
                    <EditButton @click="showSlider(true, convenio)" />
                    <DeleteButton @click="onDelete(convenio)" />
                  </div>
                </Td>
              </Tr>
            </TBody>
          </Table>
        </section>
      </div>
    </div>
  </AuthorizationFallback>
</template>
