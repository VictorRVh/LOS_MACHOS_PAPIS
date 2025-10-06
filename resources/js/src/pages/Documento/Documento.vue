<script setup>
import { computed, ref, watch, onMounted } from "vue";
import { storeToRefs } from "pinia";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import CreateButton from "../../components/ui/CreateButton.vue";
import BaseSelect from "../../components/ui/BaseSelect.vue";
import DocumentoSlider from '../../components/page/Documento/DocumentoSlider.vue';

import usePeriodoStore from "../../store/Periodo/usePeriodoStore";
import useDocumentoStore from "../../store/Documento/useDocumentoStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import useTableData from "../../composables/tabla/useTableData";

const periodoStore = usePeriodoStore();
if (!periodoStore.periodos.length) await periodoStore.loadPeriodos();
const periodoActivoId = computed(() => periodoStore.periodos.find(p => p.status === 1)?.id || null);
const selectedPeriodo = ref(periodoActivoId.value);

const documentoStore = useDocumentoStore();
const { documentos, loading } = storeToRefs(documentoStore);

onMounted(() => {
    if (selectedPeriodo.value) {
        documentoStore.loadDocumentos(selectedPeriodo.value);
    }
});

watch(selectedPeriodo, (newPeriodoId) => {
    documentoStore.loadDocumentos(newPeriodoId);
});

const { slider, sliderData, showSlider, hideSlider } = useSlider("documento-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteEntrega, deleting } = useHttpRequest("/entregas-admin");

const { paginados } = useTableData(documentos);

const onDelete = (doc) => {
  if (deleting.value) return;
  showConfirmModal(`¿Seguro que quieres eliminar "${doc.tipo_entrega}"?`, async (confirmed) => {
    if (!confirmed) return;
    const isDeleted = await deleteEntrega(doc.id);
    if (isDeleted) {
      showToast(`Programación eliminada.`);
      documentoStore.loadDocumentos(selectedPeriodo.value);
    }
  });
};

const getProgramacionStatus = (doc) => {
    if (doc.status == 0) return { text: 'Deshabilitado', class: 'bg-gray-100 text-gray-600' };
    const ahora = new Date();
    const inicio = new Date(doc.fecha_inicio);
    const fin = new Date(doc.fecha_fin);
    fin.setHours(23, 59, 59); // Considerar todo el día de fin

    if (ahora < inicio) return { text: 'Programado', class: 'bg-blue-100 text-blue-700' };
    if (ahora >= inicio && ahora <= fin) return { text: 'Vigente', class: 'bg-green-100 text-green-700' };
    return { text: 'Finalizado', class: 'bg-red-100 text-red-600' };
};

const formatFecha = (fecha) => {
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="w-full space-y-2 py-2 px-3">
      <div class="m-2">
        <div class="flex-between">
          <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2-xl">Programación de Entregas</h2>
          <CreateButton @click="showSlider(true)" title="Nueva Programación" :disabled="!selectedPeriodo"/>
        </div>

        <div class="flex-between my-5">
            <BaseSelect v-model="selectedPeriodo" :options="periodoStore.periodos" label="nombre" value-prop="id" placeholder="Seleccione un Periodo" class="w-72" />
            <div class="font-inter text-md text-gray-600">Lista de documentos a entregar por los docentes.</div>
        </div>
      </div>

      <Table>
        <THead>
          <Th>Tipo de Entrega</Th>
          <Th>Periodo</Th>
          <Th>Plazo de Entrega</Th>
          <Th>Estado</Th>
          <Th class="text-center">Acción</Th>
        </THead>
        <TBody>
            <Tr v-if="loading">
                <Td colspan="5" class="text-center">Cargando datos...</Td>
            </Tr>
            <Tr v-else-if="!selectedPeriodo">
                <Td colspan="5" class="text-center">Seleccione un periodo para ver las programaciones.</Td>
            </Tr>
            <Tr v-else-if="documentos.length === 0">
                <Td colspan="5" class="text-center">No hay programaciones para este periodo.</Td>
            </Tr>
            <Tr v-for="doc in paginados" :key="doc.id">
                <Td>
                    <p class="font-semibold text-gray-800">{{ doc.tipo_entrega }}</p>
                    <p class="text-xs text-gray-500">{{ doc.descripcion }}</p>
                </Td>
                <Td>{{ doc.periodo?.nombre }}</Td>
                <Td>{{ formatFecha(doc.fecha_inicio) }} - {{ formatFecha(doc.fecha_fin) }}</Td>
                <Td>
                    <span :class="getProgramacionStatus(doc).class" class="px-2 py-1 text-xs rounded-md font-semibold inline-block">
                        {{ getProgramacionStatus(doc).text }}
                    </span>
                </Td>
                <Td class="text-center">
                   <MenuTable
                    :actions="{ edit: true, delete: true, download: doc.documento_plantilla_url }"
                    :download-url="doc.documento_plantilla_url"
                    @edit="showSlider(true, doc)"
                    @delete="onDelete(doc)"
                  />
                </Td>
            </Tr>
        </TBody>
      </Table>
    </div>

    <DocumentoSlider :show="slider" :documento="sliderData" :periodo-seleccionado="selectedPeriodo" @hide="hideSlider" />
</template>