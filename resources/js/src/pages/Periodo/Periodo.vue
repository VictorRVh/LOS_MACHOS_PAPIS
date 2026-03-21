<script setup>
import axios from "axios";
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
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
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
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
const openDownloadMenuId = ref(null);
const openDownloadMenuStyles = ref({});
const downloadButtonRefs = ref({});
const downloadMenuRef = ref(null);

const setDownloadButtonRef = (periodoId, el) => {
  if (el) {
    downloadButtonRefs.value[periodoId] = el;
    return;
  }

  delete downloadButtonRefs.value[periodoId];
};

const closeDownloadMenu = () => {
  openDownloadMenuId.value = null;
};

const updateDownloadMenuPosition = (periodoId) => {
  const button = downloadButtonRefs.value[periodoId];
  if (!button) return;

  const rect = button.getBoundingClientRect();
  openDownloadMenuStyles.value = {
    position: "fixed",
    top: `${rect.bottom + 6}px`,
    left: `${Math.max(12, rect.right - 240)}px`,
    zIndex: 9999,
  };
};

const toggleDownloadMenu = async (periodoId) => {
  if (openDownloadMenuId.value === periodoId) {
    closeDownloadMenu();
    return;
  }

  openDownloadMenuId.value = periodoId;
  await nextTick();
  updateDownloadMenuPosition(periodoId);
};

const handleClickOutside = (event) => {
  const activeId = openDownloadMenuId.value;
  if (!activeId) return;

  const button = downloadButtonRefs.value[activeId];
  if (
    downloadMenuRef.value &&
    !downloadMenuRef.value.contains(event.target) &&
    button &&
    !button.contains(event.target)
  ) {
    closeDownloadMenu();
  }
};

const handleViewportChange = () => {
  if (!openDownloadMenuId.value) return;
  updateDownloadMenuPosition(openDownloadMenuId.value);
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("resize", handleViewportChange);
  window.addEventListener("scroll", handleViewportChange, true);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("resize", handleViewportChange);
  window.removeEventListener("scroll", handleViewportChange, true);
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-periodos', 'ver-periodos']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Periodos">
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
      </StatsOverviewSection>

      <div class="flex flex-col gap-4 lg:flex-row">
        <section
          class="w-full border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:w-1/3"
        >
          <div class="mb-3">
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
            <div>
              <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de periodos</h3>
            </div>
          </div>

          <Table class="periodos-table">
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
                    <button
                      :ref="(el) => setDownloadButtonRef(periodo.id, el)"
                      type="button"
                      @click="toggleDownloadMenu(periodo.id)"
                      class="inline-flex h-8 items-center gap-2 rounded-[3px] border border-emerald-200 bg-white px-2.5 text-xsm font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200 focus-visible:ring-offset-1 focus-visible:ring-offset-white dark:border-emerald-900/60 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20 dark:focus-visible:ring-offset-slate-900"
                      :aria-expanded="openDownloadMenuId === periodo.id"
                      title="Descargas"
                    >
                      <ArrowDownTrayIcon class="h-4 w-4 shrink-0" />
                      <span>Descargas</span>
                      <ChevronDownIcon class="h-4 w-4 shrink-0 transition-transform duration-200" :class="openDownloadMenuId === periodo.id ? 'rotate-180' : ''" />
                    </button>
                  </div>
                </Td>
              </Tr>
            </TBody>
          </Table>
        </section>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="openDownloadMenuId"
        ref="downloadMenuRef"
        :style="openDownloadMenuStyles"
        class="min-w-[240px] rounded-[3px] border border-slate-200 bg-white p-1.5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
      >
        <button
          type="button"
          @click="descargarDocumento(openDownloadMenuId, periodosStore.periodos.find((p) => p.id === openDownloadMenuId)?.nombre_periodo); closeDownloadMenu()"
          class="flex w-full items-center gap-2 rounded-[3px] px-2.5 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-300"
        >
          <TableCellsIcon class="h-4 w-4 shrink-0" />
          <span class="flex-1">Matricula institucional</span>
          <ArrowDownTrayIcon class="h-4 w-4 shrink-0 opacity-70" />
        </button>

        <button
          type="button"
          @click="descargarReporteCertificado(openDownloadMenuId, periodosStore.periodos.find((p) => p.id === openDownloadMenuId)?.nombre_periodo); closeDownloadMenu()"
          class="flex w-full items-center gap-2 rounded-[3px] px-2.5 py-2 text-left text-sm text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-300"
        >
          <TableCellsIcon class="h-4 w-4 shrink-0" />
          <span class="flex-1">Certificados</span>
          <ArrowDownTrayIcon class="h-4 w-4 shrink-0 opacity-70" />
        </button>
      </div>
    </Teleport>
  </AuthorizationFallback>
</template>
