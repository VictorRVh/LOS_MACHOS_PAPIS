<script setup>
import axios from "axios";
import { computed } from "vue";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import TableBadge from "../../components/ui/TableBadge.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import BaseButton from "../../components/ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import periodoSlider from "../../components/page/Periodo/PeriodoSlider.vue";
import usePeriodosStore from "../../store/Periodo/usePeriodoStore";

const periodosStore = usePeriodosStore();

if (!periodosStore.periodos.length) await periodosStore.loadPeriodos();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePeriodo, deleting } = useHttpRequest("/periodo");

const onDelete = (periodo) => {
  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletePeriodo(periodo?.id);
    if (isDeleted) {
      showToast(`Periodo "${periodo?.nombre_periodo}" eliminado exitosamente...`);
      periodosStore.loadPeriodos();
    }
  });
};

const descargarDocumento = async (idPeriodo, nombre) => {
  try {
    const response = await axios.get(`reportes/matriculaInstitucional/${idPeriodo}`, { responseType: "blob" });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `Historial ${nombre} Matricula Institucional.xlsx`);
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

const descargarReporteCertificado = async (idPeriodo, nombre) => {
  try {
    const response = await axios.get(`reportes/certificadosPorPeriodo/${idPeriodo}`, {
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement("a");

    link.href = url;
    link.setAttribute("download", `Historial ${nombre} Certificados Emitidos.xlsx`);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

const totalPeriodos = computed(() => periodosStore.periodos.length);
const periodosActivos = computed(() =>
  periodosStore.periodos.filter((periodo) => Number(periodo.status) === 1).length
);
const periodosInactivos = computed(() =>
  periodosStore.periodos.filter((periodo) => Number(periodo.status) !== 1).length
);
const periodosConReportes = computed(() =>
  periodosStore.periodos.filter((periodo) => periodo?.id).length
);
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-periodos', 'ver-periodos']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <section
        class="border border-slate-200 bg-white px-3 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="flex flex-col gap-1.5">
          <div class="flex flex-col gap-1">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Gestion institucional
            </p>
            <h2 class="text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">Periodos</h2>
          </div>

          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total periodos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalPeriodos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Activos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ periodosActivos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Vigentes</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Inactivos
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ periodosInactivos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Historicos</span>
              </div>
            </div>

            <div
              class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900"
            >
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Con reportes
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ periodosConReportes }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Disponibles</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="flex flex-col gap-4 lg:flex-row">
        <section
          class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3"
        >
          <div class="mb-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Configuracion
            </p>
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Agregar periodo</h3>
          </div>

          <div class="bg-white dark:bg-gray-800">
            <periodoSlider :show="slider" :periodo="sliderData" @hide="hideSlider" />
          </div>
        </section>

        <section
          class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-2/3"
        >
          <div class="mb-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Registro operativo
            </p>
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de periodos</h3>
          </div>

          <Table>
            <THead>
              <Th>Id</Th>
              <Th>Periodo</Th>
              <Th>Estado</Th>
              <Th class="text-center">Acciones</Th>
            </THead>

            <TBody>
              <Tr v-for="(periodo, index) in periodosStore.periodos" :key="periodo.id">
                <Td>{{ index + 1 }}</Td>
                <Td>{{ periodo?.nombre_periodo }}</Td>
                <Td>
                  <TableBadge
                    :label="periodo.status === 1 ? 'Activo' : 'Inactivo'"
                    :variant="periodo.status === 1 ? 'success' : 'danger'"
                    :dot="true"
                  />
                </Td>
                <Td class="align-middle">
                  <div class="flex items-center justify-center gap-1">
                    <EditButton @click="showSlider(true, periodo)" />
                    <DeleteButton @click="onDelete(periodo)" />

                    <BaseButton
                      title="MATRICULA INSTITUCIONAL"
                      @click="descargarDocumento(periodo.id, periodo?.nombre_periodo)"
                      class="h-[35px] rounded-lg bg-green-600 px-1 text-white shadow hover:bg-green-700"
                    >
                      <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                      </template>
                    </BaseButton>

                    <BaseButton
                      title="CERTIFICADOS"
                      @click="descargarReporteCertificado(periodo.id, periodo?.nombre_periodo)"
                      class="h-[35px] rounded-lg bg-green-600 px-1 text-white shadow hover:bg-green-700"
                    >
                      <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                      </template>
                    </BaseButton>
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
