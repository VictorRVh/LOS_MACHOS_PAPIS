<script setup>
import axios from "axios";
import { computed, ref } from "vue";
import { ArrowDownTrayIcon, ChevronDownIcon, TableCellsIcon } from "@heroicons/vue/24/outline";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import TableBadge from "../../components/ui/TableBadge.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
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
const reportsOpen = ref(false);
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
          <div class="mb-3 flex items-start justify-between gap-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                Registro operativo
              </p>
              <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de periodos</h3>
            </div>

            <button
              type="button"
              @click="reportsOpen = !reportsOpen"
              class="inline-flex h-8 shrink-0 items-center gap-2 rounded-[3px] border border-slate-200 bg-white px-2.5 text-slate-600 transition-colors hover:border-cetpro/20 hover:bg-cetpro/10 hover:text-cetpro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/20 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cetpro-light/25 dark:hover:bg-cetpro-light/10 dark:hover:text-cetpro-light dark:focus-visible:ring-offset-slate-900"
              :title="reportsOpen ? 'Ocultar reportes' : 'Mostrar reportes'"
              :aria-expanded="reportsOpen"
              aria-label="Alternar reportes"
            >
              <ArrowDownTrayIcon class="h-4 w-4 shrink-0" />
              <span class="text-[12px] font-medium leading-none">
                {{ reportsOpen ? "Ocultar descargas" : "Mostrar descargas" }}
              </span>
              <ChevronDownIcon class="h-4 w-4 transition-transform duration-200" :class="reportsOpen ? 'rotate-180' : ''" />
            </button>
          </div>

          <div
            v-if="reportsOpen"
            class="mb-4 space-y-2 border border-slate-200 bg-slate-50/70 p-2.5 dark:border-slate-700 dark:bg-slate-800/50"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                  Reportes
                </p>
                <p class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">
                  Descargas operativas por periodo
                </p>
              </div>
            </div>

            <div class="space-y-2">
              <div
                v-for="periodo in periodosStore.periodos"
                :key="`reportes-${periodo.id}`"
                class="flex flex-col gap-2 border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900 md:flex-row md:items-center md:justify-between"
              >
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                    Periodo
                  </p>
                  <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">
                    {{ periodo?.nombre_periodo }}
                  </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    @click="descargarDocumento(periodo.id, periodo?.nombre_periodo)"
                    class="inline-flex h-9 items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-3 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
                  >
                    <TableCellsIcon class="h-4 w-4 shrink-0" />
                    <span>Matricula institucional</span>
                    <ArrowDownTrayIcon class="h-4 w-4 shrink-0 opacity-70" />
                  </button>

                  <button
                    type="button"
                    @click="descargarReporteCertificado(periodo.id, periodo?.nombre_periodo)"
                    class="inline-flex h-9 items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-3 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
                  >
                    <TableCellsIcon class="h-4 w-4 shrink-0" />
                    <span>Certificados</span>
                    <ArrowDownTrayIcon class="h-4 w-4 shrink-0 opacity-70" />
                  </button>
                </div>
              </div>
            </div>
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
