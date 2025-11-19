<script setup>
import { ref, onMounted, watch } from "vue";
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

const { update: updateDocente, updating } = useHttpRequest('crear_grupos');
const { update: storeGrupoPersonalizado, updating: updtingPersonalizado} = useHttpRequest('crear_grupos_personalizado');
// 🔹 Estados
const selectedPeriodo = ref(null);
const programaciones = ref([]);
const programacionParaEditar = ref(null);

const gruposPorPeriodo = ref([]);
const modalPublicarAbierto = ref(false);
const modalPublicarData = ref(null);


// 🔹 Función para traer programaciones por periodo
const fetchProgramaciones = async (periodoId) => {
  await documentoProgramado.loadgetProgramacionAdminByPerido(periodoId);
  programaciones.value = documentoProgramado.programacionAdmin?.programaciones || [];
};

// 🔹 Cargar periodos al iniciar
onMounted(async () => {
  try {
    if (!periodoStore.periodos.length) {
      await periodoStore.loadPeriodos();
    }

    // Tomamos el último periodo disponible
    const ultimoPeriodo = periodoStore.periodos.at(-1);
    if (ultimoPeriodo) {
      selectedPeriodo.value = ultimoPeriodo.id;
      //await fetchProgramaciones(selectedPeriodo.value);
    }
  } catch (error) {
    console.error("Error al cargar periodos o programaciones:", error);
  }
});

// 🔹 Cuando cambia el select
watch(selectedPeriodo, async (nuevoPeriodo, anterior) => {
  if (!nuevoPeriodo || nuevoPeriodo === anterior) return;
  await fetchProgramaciones(nuevoPeriodo);
});

// 🔹 Estado visual de cada programación según su status numérico
// 🔹 Estado visual de cada programación según su status numérico o texto
const getProgramacionStatus = (doc) => {
  // Algunos backends devuelven status = 0 o status_texto = 'Pendiente'
  const status = Number(doc.status); // aseguramos que sea número
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

// 🔹 Acciones
const verDetalleEntrega = (programacion) => {
  router.push({ name: "programacion.detalle", params: { id: programacion.id } });
};

const editProgramacion = (prog) => {
  programacionParaEditar.value = prog;
};

const resetEditingState = () => {
  programacionParaEditar.value = null;
};

const handleFormSubmitted = async () => {
  await fetchProgramaciones(selectedPeriodo.value);
  resetEditingState();
};

const onDelete = (prog) => {

  showConfirmModal(
    null,
    async (confirmed) => {
      if (!confirmed) return;

      try {
        const response = await destroy(prog.id);
        console.log("entrada: ", response)
        if (!response) {
          return showToast("No se puede eliminar la programacion porque ya fue programada para los grupos.", "error");
        }

        showToast("Programación eliminada.", "success");
        await fetchProgramaciones(selectedPeriodo.value);

        if (programacionParaEditar.value?.id === prog.id) resetEditingState();
      } catch (error) {
        showToast("Error al eliminar.", "error");
      }
    }
  );
};

const createSubGrupos = (prog) => {
  showConfirmModal(
    {
      title: "Confirmar publicación",
      message: "¿Deseas publicar esta programación para todos los grupos?",
      actionButton: {
        class: "bg-emerald-600 hover:bg-emerald-700",
        text: "Sí, publicar",
      },
      returnButton: {
        class: "bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600",
        text: "Cancelar",
      },
    },
    async (confirmed) => {
      if (!confirmed) return;

      try {
        const response = await updateDocente(prog.id);

        if (!response) {
          showToast(
            "No se puede publicar la programación porque ya fue asignada a los grupos.",
            "warning"
          );
          return;
        }

        showToast("Programación publicada correctamente.", "success");
        await fetchProgramaciones(selectedPeriodo.value);

        if (programacionParaEditar.value?.id === prog.id) {
          resetEditingState();
        }
      } catch (error) {
        console.error(error);
        const msg =
          error.response?.data?.message ||
          "Ocurrió un error al intentar publicar la programación.";
        showToast(msg, "error");
      }
    }
  );
};

const abrirModalPublicar = async (prog) => {
  try {
    // Cargar grupos por período
    await documentoProgramado.loadGruposByPeriodo(selectedPeriodo.value);
    gruposPorPeriodo.value = documentoProgramado.gruposByPeriodo

    // Guardar la programación seleccionada
    modalPublicarData.value = prog;

    // Abrir modal
    modalPublicarAbierto.value = true;

  } catch (error) {
    console.error("Error al abrir modal:", error);
    showToast("No se pudieron cargar los grupos.", "error");
  }
};


const publicarMasivo = async (prog) => {
  try {
    updating.value = true;

    const response = await updateDocente(prog.id);

    if (!response) {
      return showToast("No se puede publicar masivamente.", "warning");
    }

    showToast("Publicación masiva realizada correctamente", "success");
    modalPublicarAbierto.value = false;
    await fetchProgramaciones(selectedPeriodo.value);

  } catch (error) {
    console.error(error);
    showToast("Error en la publicación masiva.", "error");
  } finally {
    updating.value = false;
  }
};

const publicarPersonalizado = async (prog, gruposSeleccionados) => {
  try {
    updtingPersonalizado.value = true;

    const response = await storeGrupoPersonalizado(prog.id, {
      grupos: gruposSeleccionados
    });

    if (!response) {
      return showToast("No se pudo publicar para los grupos seleccionados.", "warning");
    }

    showToast("Publicación personalizada realizada correctamente.", "success");

    modalPublicarAbierto.value = false;
    await fetchProgramaciones(selectedPeriodo.value);

  } catch (error) {
    console.error(error);
    showToast(
      error.response?.data?.message || "Ocurrió un error al publicar.",
      "error"
    );
  } finally {
    updtingPersonalizado.value = false;
  }
};


</script>


<template>
  <AuthorizationFallback :permissions="['todo-documento-programado', 'ver-documento-programado']">
    <div class="p-4 md:p-6 space-y-6">
      <!-- Overlay de carga -->
      <Transition name="fade">
        <div v-if="updating || updtingPersonalizado"
          class="fixed inset-0 bg-black/40 backdrop-blur-sm flex flex-col items-center justify-center z-50">
          <div class="flex flex-col items-center space-y-4">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-white font-semibold text-lg">Publicando programación...</p>
          </div>
        </div>
      </Transition>

      <header class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
            Programación de Entregas
          </h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">
            Crea y gestiona los plazos de entrega de documentos para los docentes.
          </p>
        </div>
        <div class="w-64">
          <BaseSelectGrupo v-model="selectedPeriodo" :options="periodoStore?.periodos" label="nombre_periodo"
            value-prop="id" placeholder="Seleccione un Periodo" />

        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
          <DocumentoSlider :programacion-to-edit="programacionParaEditar" :periodos="periodoStore?.periodos"
            :selected-periodo-id="selectedPeriodo" @form-submitted="handleFormSubmitted"
            @cancel-edit="resetEditingState" />
        </div>

        <div class="lg:col-span-2">
          <Table>
            <THead>
              <Th>Título / Descripción</Th>
              <Th>Plazo de Entrega</Th>
              <Th>Estado</Th>
              <Th>Publicación</Th>
              <Th class="text-center">Acciones</Th>
            </THead>
            <TBody>
              <Tr v-if="loading">
                <Td colspan="5" class="text-center py-10">Cargando...</Td>
              </Tr>
              <Tr v-else-if="!selectedPeriodo">
                <Td colspan="5" class="text-center py-12">Seleccione un periodo para empezar.</Td>
              </Tr>
              <Tr v-else-if="!programaciones.length">
                <Td colspan="5" class="text-center py-12">No hay programaciones para este periodo.</Td>
              </Tr>
              <Tr v-else v-for="prog in programaciones" :key="prog.id">
                <Td>
                  <p
                    class="font-semibold text-gray-800 dark:text-gray-200 hover:text-cetpro dark:hover:text-cetpro-light">
                    {{ prog.nombre_entrega }}
                  </p>
                </Td>
                <Td class="font-mono text-xs">{{ prog.fecha_inicio }} - {{ prog.fecha_fin }}</Td>
                <Td>
                  <span :class="getProgramacionStatus(prog).class" class="px-2 py-1 text-xs rounded-full font-semibold">
                    {{ getProgramacionStatus(prog).text }}
                  </span>

                </Td>
                <Td>
                  <span v-if="prog.mostrar"
                    class="px-2 py-1 text-xs rounded-full font-semibold text-green-700 bg-green-100 dark:bg-green-900/50 dark:text-green-300">Publicado</span>
                  <span v-else
                    class="px-2 py-1 text-xs rounded-full font-semibold text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300">Borrador</span>
                </Td>
                <Td class="text-center">
                  <MenuTable :actions="{
                    view: prog.mostrar !== 0,
                    edit: true,
                    custom1: prog.mostrar === 0,
                    delete: true
                  }" :labels="{ custom1: 'Publicar' }" entity-label="entrega" @view="verDetalleEntrega(prog)"
                    @edit="editProgramacion(prog)" @delete="onDelete(prog)" @custom1="() => abrirModalPublicar(prog)" />
                </Td>

              </Tr>
            </TBody>
          </Table>
        </div>
      </div>


    </div>
    <PublicarEntregaModal v-if="modalPublicarAbierto" :programacion="modalPublicarData" :periodo-id="selectedPeriodo"
      :grupos="gruposPorPeriodo" @close="modalPublicarAbierto = false" @masivo="publicarMasivo(modalPublicarData)"
      @personalizado="publicarPersonalizado(modalPublicarData, $event)" />
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