<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import StatsOverviewSection from "../../components/page/StatsOverviewSection.vue";
import ProgramaSlider from "../../components/page/Programa/ProgramaSlider.vue";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useProgramaStore from "../../store/Programa/useProgramaStore";
import useCicloStore from "../../store/Ciclo/useCicloStore";
import useHttpRequest from "../../composables/useHttpRequest";

const router = useRouter();
const programaStore = useProgramaStore();
const cicloStore = useCicloStore();

if (!programaStore?.programas?.length) await programaStore.loadProgramas();
if (!cicloStore?.ciclo?.length) await cicloStore.loadCiclo();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePrograma, deleting } = useHttpRequest("/programa_estudio");

const filtroCiclo = ref("Ciclo Técnico");

const programas = computed(() => programaStore.programas?.programas || []);
const programasFiltrados = computed(() =>
  programas.value.filter((programa) => programa.nameCiclo === filtroCiclo.value)
);

const totalProgramas = computed(() => programas.value.length);
const programasTecnicos = computed(() =>
  programas.value.filter((programa) => programa.nameCiclo === "Ciclo Técnico").length
);
const programasAuxiliares = computed(() =>
  programas.value.filter((programa) => programa.nameCiclo === "Ciclo Auxiliar Técnico").length
);
const programasEnCurso = computed(() =>
  programas.value.filter((programa) => Boolean(programa.status)).length
);

const handleProgramaGuardado = (programaGuardado) => {
  const nombreCiclo =
    cicloStore.ciclo.find((ciclo) => ciclo.id === programaGuardado.id_ciclo)?.nombre_ciclo || "";
  if (nombreCiclo) filtroCiclo.value = nombreCiclo;
};

const onDelete = async (programa) => {
  if (deleting.value) return;

  showConfirmModal(`¿Seguro que quieres eliminar "${programa?.nameCiclo} - ${programa?.["año"]}"?`, async (confirmed) => {
    if (!confirmed) return;
    try {
      const isDeleted = await deletePrograma(programa.id);
      if (isDeleted) {
        showToast("Programa de estudio eliminado exitosamente.");
        programaStore.loadProgramas();
      }
    } catch (error) {
      showToast("Error al eliminar el programa de estudio.", "error");
    }
  });
};

const seeMore = (programa) => {
  router.push({
    name: "especialidadPrograma",
    params: { idPrograma: programa.id },
  });
};

const tooltip = ref({
  visible: false,
  text: "Asignar programas",
  x: 0,
  y: 0,
});

const showTooltip = () => {
  tooltip.value.visible = true;
};

const hideTooltip = () => {
  tooltip.value.visible = false;
};

const updateTooltipPos = (event) => {
  if (tooltip.value.visible) {
    tooltip.value.x = event.clientX + 15;
    tooltip.value.y = event.clientY + 15;
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-ciclo-académico', 'ver-ciclo-académico']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <StatsOverviewSection eyebrow="Gestion institucional" title="Programas de estudio">
          <div class="grid gap-1 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Total programas
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ totalProgramas }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Registrados</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Ciclo técnico
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ programasTecnicos }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Técnico</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                Ciclo auxiliar
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ programasAuxiliares }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Auxiliar</span>
              </div>
            </div>

            <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-white px-2.5 py-1.5 transition-colors duration-300 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-900">
              <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                En curso
              </p>
              <div class="mt-1 flex items-end justify-between gap-3">
                <p class="text-[1.05rem] font-semibold leading-none text-cetpro dark:text-cetpro-light">{{ programasEnCurso }}</p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Vigentes</span>
              </div>
            </div>
          </div>
      </StatsOverviewSection>

      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:col-span-1">
          <ProgramaSlider
            :show="true"
            :programa="sliderData"
            :ciclo="cicloStore.ciclo"
            @hide="hideSlider"
            @programa-guardado="handleProgramaGuardado"
          />
        </section>

        <section class="space-y-4 border border-slate-200 bg-white p-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900 lg:col-span-2">
          <div class="flex items-center space-x-2 rounded-lg bg-white p-2 shadow-sm dark:bg-gray-800">
            <button
              @click="filtroCiclo = 'Ciclo Técnico'"
              :class="[
                'w-full rounded-md px-4 py-2 text-sm font-semibold transition-colors',
                filtroCiclo === 'Ciclo Técnico'
                  ? 'bg-cetpro text-white shadow'
                  : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
              ]"
            >
              Ciclo Técnico
            </button>
            <button
              @click="filtroCiclo = 'Ciclo Auxiliar Técnico'"
              :class="[
                'w-full rounded-md px-4 py-2 text-sm font-semibold transition-colors',
                filtroCiclo === 'Ciclo Auxiliar Técnico'
                  ? 'bg-cetpro text-white shadow'
                  : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
              ]"
            >
              Ciclo Auxiliar Técnico
            </button>
          </div>

          <div class="space-y-3" @mousemove="updateTooltipPos">
            <div
              v-for="programa in programasFiltrados"
              :key="programa.id"
              class="flex rounded-lg border-l-4 bg-white shadow-md dark:bg-gray-800"
              :class="[programa.status ? 'border-green-500' : 'border-red-500']"
            >
              <div
                class="flex-grow cursor-pointer rounded-l-md p-3 transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                @click="seeMore(programa)"
                @mouseover="showTooltip"
                @mouseleave="hideTooltip"
              >
                <div class="grid h-full grid-cols-2 items-center gap-3 md:grid-cols-4">
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Programa</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ programa.nameCiclo }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Periodo</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ programa["año"] }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Resolución</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">RD {{ programa.numero_rd }}</p>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Estado</span>
                    <span
                      class="w-fit rounded-full px-2 py-1 text-xs font-bold"
                      :class="[
                        programa.status
                          ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                          : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                      ]"
                    >
                      {{ programa.status ? "En Curso" : "Finalizado" }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex flex-shrink-0 flex-col items-center justify-center space-y-2 border-l border-gray-200 px-3 py-2 dark:border-gray-700">
                <button
                  @click="showSlider(true, programa)"
                  title="Editar"
                  class="rounded-full p-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                >
                  <PencilSquareIcon class="h-5 w-5" />
                </button>
                <button
                  @click="onDelete(programa)"
                  title="Eliminar"
                  class="rounded-full p-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-red-400"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <div
              v-if="!programasFiltrados.length && !deleting"
              class="rounded-lg bg-white py-10 text-center shadow-md dark:bg-gray-800"
            >
              <p class="text-gray-500 dark:text-gray-400">No hay programas para mostrar en esta categoría.</p>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div
      v-if="tooltip.visible"
      class="pointer-events-none fixed z-50 rounded-md bg-cetpro-dark px-3 py-1.5 text-xs font-semibold text-white shadow-lg transition-opacity duration-200"
      :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
    >
      {{ tooltip.text }}
    </div>
  </AuthorizationFallback>
</template>
