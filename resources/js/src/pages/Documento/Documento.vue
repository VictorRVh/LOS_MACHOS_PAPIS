<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter } from "vue-router";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";
import DocumentoSlider from "../../components/page/Documento/DocumentoSlider.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";

import usePeriodoStatusStore from "../../store/Periodo/usePeriodoStatusStore";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useProgramacionAdmintore from "../../store/Documento/useDocumentoStore";
import PublicarEntregaModal from "../../components/ui/PublicarEntregaModal.vue";

const router = useRouter();
const periodoStore = usePeriodoStatusStore();
const documentoProgramado = useProgramacionAdmintore();

const { showToast, showConfirmModal } = useModalToast();
const { destroy, loading } = useHttpRequest("/entrega_docente_admin");
const { update: updateDocente, updating } = useHttpRequest("crear_grupos");
const { update: storeGrupoPersonalizado, updating: updtingPersonalizado } = useHttpRequest("crear_grupos_personalizado");

const selectedPeriodo = ref(null);
const programaciones = ref([]);
const programacionParaEditar = ref(null);
const gruposPorPeriodo = ref([]);
const modalPublicarAbierto = ref(false);
const modalPublicarData = ref(null);

const fetchProgramaciones = async (periodoId) => {
  await documentoProgramado.loadgetProgramacionAdminByPerido(periodoId);
  programaciones.value = documentoProgramado.programacionAdmin?.programaciones || [];
};

onMounted(async () => {
  try {
    if (!periodoStore.periodos.length) {
      await periodoStore.loadPeriodos();
    }

    const ultimoPeriodo = periodoStore.periodos.at(-1);
    if (ultimoPeriodo) {
      selectedPeriodo.value = ultimoPeriodo.id;
    }
  } catch (error) {
    console.error("Error al cargar periodos o programaciones:", error);
  }
});

watch(selectedPeriodo, async (nuevoPeriodo, anterior) => {
  if (!nuevoPeriodo || nuevoPeriodo === anterior) return;
  await fetchProgramaciones(nuevoPeriodo);
});

const getProgramacionStatus = (doc) => {
  const status = Number(doc.status);
  const texto = doc.status_texto?.toLowerCase() || "";

  if (status === 0 || texto.includes("pendiente")) {
    return {
      text: "Pendiente",
      class: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300",
    };
  }

  switch (status) {
    case 1:
      return {
        text: "Activo",
        class: "bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300",
      };
    case 2:
      return {
        text: "Desactivo",
        class: "bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300",
      };
    case 3:
      return {
        text: "Anulado",
        class: "bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300",
      };
    case 4:
      return {
        text: "Finalizado",
        class: "bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300",
      };
    case 5:
      return {
        text: "Completado",
        class: "bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300",
      };
    default:
      return {
        text: "Desconocido",
        class: "bg-gray-100 text-gray-500",
      };
  }
};

const verDetalleEntrega = (programacion) => {
  router.push({ name: "programacion.detalle", params: { id: programacion.id } });
};

const editProgramacion = (programacion) => {
  programacionParaEditar.value = programacion;
};

const resetEditingState = () => {
  programacionParaEditar.value = null;
};

const handleFormSubmitted = async () => {
  await fetchProgramaciones(selectedPeriodo.value);
  resetEditingState();
};

const onDelete = (programacion) => {
  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    try {
      const response = await destroy(programacion.id);
      if (!response) {
        return showToast("No se puede eliminar la programación porque ya fue programada para los grupos.", "error");
      }

      showToast("Programación eliminada.", "success");
      await fetchProgramaciones(selectedPeriodo.value);

      if (programacionParaEditar.value?.id === programacion.id) resetEditingState();
    } catch (error) {
      showToast("Error al eliminar.", "error");
    }
  });
};

const abrirModalPublicar = async (programacion) => {
  try {
    await documentoProgramado.loadGruposByPeriodo(selectedPeriodo.value);
    gruposPorPeriodo.value = documentoProgramado.gruposByPeriodo;
    modalPublicarData.value = programacion;
    modalPublicarAbierto.value = true;
  } catch (error) {
    console.error("Error al abrir modal:", error);
    showToast("No se pudieron cargar los grupos.", "error");
  }
};

const publicarMasivo = async (programacion) => {
  try {
    updating.value = true;
    const response = await updateDocente(programacion.id);

    if (!response) {
      return showToast("No se puede publicar masivamente.", "warning");
    }

    showToast("Publicación para todos los grupos realizada correctamente", "success");
    modalPublicarAbierto.value = false;
    await fetchProgramaciones(selectedPeriodo.value);
  } catch (error) {
    console.error(error);
    showToast("Error en la publicación para todos los grupos.", "error");
  } finally {
    updating.value = false;
  }
};

const publicarPersonalizado = async (programacion, gruposSeleccionados) => {
  try {
    updtingPersonalizado.value = true;

    const response = await storeGrupoPersonalizado(programacion.id, {
      grupos: gruposSeleccionados,
    });

    if (!response) {
      return showToast("No se pudo publicar para los grupos seleccionados.", "warning");
    }

    showToast("Publicación personalizada realizada correctamente.", "success");
    modalPublicarAbierto.value = false;
    await fetchProgramaciones(selectedPeriodo.value);
  } catch (error) {
    console.error(error);
    showToast(error.response?.data?.message || "Ocurrió un error al publicar.", "error");
  } finally {
    updtingPersonalizado.value = false;
  }
};

const totalProgramaciones = computed(() => programaciones.value.length);
const totalPublicadas = computed(() => programaciones.value.filter((programacion) => Boolean(programacion.mostrar)).length);
const totalPendientes = computed(() => programaciones.value.filter((programacion) => getProgramacionStatus(programacion).text === "Pendiente").length);
const periodoActualLabel = computed(() => {
  const periodo = periodoStore.periodos.find((item) => item.id === selectedPeriodo.value);
  return periodo?.nombre_periodo || "Sin periodo";
});
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-documento-programado', 'ver-documento-programado']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <Transition name="fade">
        <div v-if="updating || updtingPersonalizado" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/40 backdrop-blur-sm">
          <div class="flex flex-col items-center space-y-4">
            <svg class="h-10 w-10 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-lg font-semibold text-white">Publicando programación...</p>
          </div>
        </div>
      </Transition>

      <StatsOverviewSection eyebrow="Gestion institucional" title="Programacion de entregas">
        <template #actions>
          <div class="w-full lg:w-64">
              <BaseSelectGrupo v-model="selectedPeriodo" :options="periodoStore?.periodos" label="nombre_periodo" value-prop="id" placeholder="Seleccione un periodo" />
          </div>
        </template>

          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Programaciones</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalProgramaciones }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registradas</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Publicadas</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalPublicadas }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Visibles</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Pendientes</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalPendientes }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Por publicar</span>
              </div>
            </div>
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Periodo actual</p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ periodoActualLabel }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Activo</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:col-span-1">
          <div class="mb-3">
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Nueva programación</h3>
          </div>

          <DocumentoSlider
            :programacion-to-edit="programacionParaEditar"
            :periodos="periodoStore?.periodos"
            :selected-periodo-id="selectedPeriodo"
            @form-submitted="handleFormSubmitted"
            @cancel-edit="resetEditingState"
          />
        </section>

        <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:col-span-2">
          <div class="mb-3">
            <h3 class="mt-1 text-[15px] font-medium text-slate-900 dark:text-slate-100">Lista de entregas</h3>
          </div>

          <Table>
            <THead>
              <Th>Título / Descripción</Th>
              <Th>Plazo de entrega</Th>
              <Th>Estado</Th>
              <Th>Publicación</Th>
              <Th class="text-center">Acciones</Th>
            </THead>
            <TBody :filas="programaciones.length">
              <Tr v-if="loading">
                <Td colspan="5" class="py-10 text-center">Cargando...</Td>
              </Tr>
              <Tr v-else-if="!selectedPeriodo">
                <Td colspan="5" class="py-12 text-center">Seleccione un periodo para empezar.</Td>
              </Tr>
              <Tr v-else-if="!programaciones.length">
                <Td colspan="5" class="py-12 text-center">No hay programaciones para este periodo.</Td>
              </Tr>
              <Tr v-else v-for="prog in programaciones" :key="prog.id">
                <Td>
                  <p class="font-semibold text-gray-800 hover:text-cetpro dark:text-gray-200 dark:hover:text-cetpro-light">
                    {{ prog.nombre_entrega }}
                  </p>
                </Td>
                <Td class="font-mono text-xs">{{ prog.fecha_inicio }} - {{ prog.fecha_fin }}</Td>
                <Td>
                  <span :class="getProgramacionStatus(prog).class" class="rounded-full px-2 py-1 text-xs font-semibold">
                    {{ getProgramacionStatus(prog).text }}
                  </span>
                </Td>
                <Td>
                  <span v-if="prog.mostrar" class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/50 dark:text-green-300">
                    Publicado
                  </span>
                  <span v-else class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    Borrador
                  </span>
                </Td>
                <Td class="text-center">
                  <MenuTable
                    :actions="{ view: prog.mostrar !== 0, edit: true, custom1: prog.mostrar === 0, delete: true }"
                    :labels="{ custom1: 'Publicar' }"
                    entity-label="entrega"
                    @view="verDetalleEntrega(prog)"
                    @edit="editProgramacion(prog)"
                    @delete="onDelete(prog)"
                    @custom1="() => abrirModalPublicar(prog)"
                  />
                </Td>
              </Tr>
            </TBody>
          </Table>
        </section>
      </div>
    </div>

    <PublicarEntregaModal
      v-if="modalPublicarAbierto"
      :programacion="modalPublicarData"
      :periodo-id="selectedPeriodo"
      :grupos="gruposPorPeriodo"
      @close="modalPublicarAbierto = false"
      @masivo="publicarMasivo(modalPublicarData)"
      @personalizado="publicarPersonalizado(modalPublicarData, $event)"
    />
  </AuthorizationFallback>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
