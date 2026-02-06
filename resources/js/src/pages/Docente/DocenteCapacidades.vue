<script setup>
import { ref, onMounted, computed } from "vue";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import CapacidaddesSlider from "../../components/page/Docente/CapacidadesSlider.vue";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useCapacidadesStore from "../../store/CapacidadTerminal/UseCapacidadesStore";
import useSesionesStore from "../../store/Sesion/useSesionStore";

const props = defineProps({
  id: { type: String, required: true },
  idModulo: { type: String, required: true },
});

const sesionStore = useSesionesStore();
const capacidadStore = useCapacidadesStore();

const openCompetencias = ref(new Set());

const { slider, sliderData, showSlider, hideSlider } = useSlider("capacidad-terminal-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteCapacidad, deleting } = useHttpRequest("/capacidad_competencia");

// Agrupar capacidades por competencia → unidad
const competencias = computed(() => {
  const mapCompetencia = new Map();

  capacidadStore.capacidades.forEach((item) => {
    // Padre: competencia
    if (!mapCompetencia.has(item.competencia)) {
      mapCompetencia.set(item.competencia, {
        nombre: item.competencia,
        id: item.id_competencia, // usamos tipo_competencia como id único
        unidades: new Map(),
      });
    }

    const comp = mapCompetencia.get(item.competencia);

    // Hijo: unidad
    if (!comp.unidades.has(item.id_unidad)) {
      comp.unidades.set(item.id_unidad, {
        nombre: item.unidad,
        id: item.id_unidad,
        indice_unidad:  item.indice_unidad,
        descripciones: [],
      });
    }

    // Descripciones
    comp.unidades.get(item.id_unidad).descripciones.push({
      id: item.id,
      descripcion: item.descripcion,
    });
  });

  // Convertir mapas a arrays
  return Array.from(mapCompetencia.values()).map((comp) => ({
    ...comp,
    unidades: Array.from(comp.unidades.values()),
  }));
});

// Toggle competencia (abre todas las unidades)
const toggleCompetencia = (id) => {
  if (openCompetencias.value.has(id)) openCompetencias.value.delete(id);
  else openCompetencias.value.add(id);
  openCompetencias.value = new Set(openCompetencias.value);
};

// Eliminar capacidad
const onDelete = async (capacidad) => {
  if (deleting.value) return;
  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;
    const isDeleted = await deleteCapacidad(capacidad.id);
    if (isDeleted) {
      showToast(`Capacidad eliminada exitosamente.`);
      await capacidadStore.loadCapacidades(props.id);
    } else {
      showToast("No se pudo eliminar la capacidad. Intenta nuevamente.", "error");
    }
  });
};

onMounted(async () => {
  await sesionStore.loadSesion({ id_grupo: props.id, tipo_entrega: 1 });
  if (!capacidadStore.capacidades?.length) {
    await capacidadStore.loadCapacidades(props.id);
  }
});

const reloadCapacidades = async () => {
  await capacidadStore.loadCapacidades(props.id);
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-docente', 'ver-capacidad-terminal-docente']">
    <div class="flex flex-col lg:flex-row px-6 gap-6">

      <!-- FORMULARIO -->
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
        <CapacidaddesSlider :show="slider" :idGrupo="id" :idModulo="idModulo" :capacidad="sliderData"
          @hide="hideSlider" @saved="reloadCapacidades" />
      </div>

      <!-- TABLA -->
      <div class="w-full lg:w-2/3">
        <Table class="w-full">
          <THead class="hidden">
            <th></th>
          </THead>
          <TBody>
            <!-- ITERAR COMPETENCIAS -->
            <template v-for="comp in competencias" :key="comp.id">
              <!-- HEADER COMPETENCIA -->
              <tr @click="toggleCompetencia(comp.id)"
                class="bg-cetpro dark:bg-cetpro-dark cursor-pointer hover:bg-cetpro-dark transition-colors border-b">
                <td colspan="8" class="px-4 py-3">
                  <div class="flex items-center justify-between text-white font-bold uppercase text-sm">
                    <span>{{ comp.nombre }}</span>
                    <ChevronDownIcon class="h-5 w-5 transition-transform"
                      :class="{ 'rotate-180': openCompetencias.has(comp.id) }" />
                  </div>
                </td>
              </tr>

              <!-- UNIDADES Y DESCRIPCIONES (si competencia abierta) -->
              <tr v-if="openCompetencias.has(comp.id)">
                <td colspan="8" class="bg-white dark:bg-gray-800 px-6 py-4">
                  <div class="flex flex-col gap-4">
                    <template v-for="unidad in comp.unidades" :key="unidad.id">
                      <div class="font-semibold text-gray-800 dark:text-gray-200">Unidad {{ unidad.indice_unidad }} :  {{ unidad.nombre }}</div>

                      <div class="ml-6 flex flex-col gap-2">
                        <div v-for="desc in unidad.descripciones" :key="desc.id"
                          class="flex justify-between items-center border-l-4 border-cetpro pl-4">
                          <p class="text-sm text-gray-600 dark:text-gray-300">{{ desc.descripcion }}</p>
                          <div class="flex items-center gap-2">
                            <EditButton @click="showSlider(true, {
                              id: desc.id,
                              descripcion: desc.descripcion,
                              id_competencia: comp.id,        // tipo_competencia
                              id_capacidad_terminal: unidad.id // id_unidad
                            })" />
                            <DeleteButton @click="onDelete(desc)" />
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </td>
              </tr>

            </template>
          </TBody>
        </Table>
      </div>
    </div>
  </AuthorizationFallback>
</template>
