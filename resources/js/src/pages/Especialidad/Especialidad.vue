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
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import EspecialidadSlider from "../../components/page/Especialidad/EspecialidadSlider.vue";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import useEspecialidadStore from "../../store/Especialidad/useEspecialidadStore";

const especialidadStore = useEspecialidadStore();
const cicloStore = useCicloStore();

if (!especialidadStore.especialidad.length) await especialidadStore.loadEspecialidad();
if (!cicloStore.ciclo.length) await cicloStore.loadCiclo();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteEspecialidad, deleting } = useHttpRequest("/especialidad_madre");

const onDelete = (especialidad) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deleteEspecialidad(especialidad?.id);
    if (isDeleted) {
      showToast(`Especialidad "${especialidad?.nombre_especialidad}" eliminada exitosamente...`);
      especialidadStore.loadEspecialidad();
    }
  });
};

const totalEspecialidades = computed(() => especialidadStore.especialidad.length);
const tecnicos = computed(() =>
  especialidadStore.especialidad.filter((item) => item?.ciclo_academico?.nombre_ciclo === "Ciclo Técnico").length
);
const auxiliares = computed(() =>
  especialidadStore.especialidad.filter((item) => item?.ciclo_academico?.nombre_ciclo === "Ciclo Auxiliar Técnico").length
);
const ciclosConEspecialidades = computed(() => {
  const nombres = new Set(
    especialidadStore.especialidad.map((item) => item?.ciclo_academico?.nombre_ciclo).filter(Boolean)
  );
  return nombres.size;
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-programas-de-estudio', 'ver-programas-de-estudio']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Programas de estudio">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total especialidades
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalEspecialidades }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registradas</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Ciclo técnico
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ tecnicos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Técnico</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Ciclo auxiliar
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ auxiliares }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Auxiliar</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Ciclos con registro
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ ciclosConEspecialidades }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Activos</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <div class="flex flex-col gap-4 lg:flex-row">
        <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3">
          <EspecialidadSlider :show="slider" :especialidad="sliderData" :ciclo="cicloStore.ciclo" @hide="hideSlider" />
        </section>

        <section class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-2/3">
          <Table>
            <THead>
              <Th>Id</Th>
              <Th>Especialidad</Th>
              <Th>Ciclo académico</Th>
              <Th>Acciones</Th>
            </THead>

            <TBody>
              <Tr v-for="(especialidad, index) in especialidadStore.especialidad" :key="especialidad.id">
                <Td>{{ index + 1 }}</Td>
                <Td>{{ especialidad?.nombre_especialidad }}</Td>
                <Td>{{ especialidad?.ciclo_academico?.nombre_ciclo }}</Td>
                <Td class="align-middle">
                  <div class="flex items-center justify-center gap-1">
                    <EditButton @click="showSlider(true, especialidad)" />
                    <DeleteButton @click="onDelete(especialidad)" />
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
