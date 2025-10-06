<script setup>
import { computed, ref, watch, onMounted } from "vue";
import { useRouter } from "vue-router"; // <-- IMPORTANTE: Añadir router
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

import usePeriodoStatusStore from "../../store/Periodo/usePeriodoStatusStore";
import useDocumentoStore from "../../store/Documento/useDocumentoStore";
import useSlider from "../../composables/useSlider";

const router = useRouter(); // <-- IMPORTANTE: Inicializar router
const periodoStore = usePeriodoStatusStore();
const { periodos: periodosActivos } = storeToRefs(periodoStore);
if (!periodoStore.periodos.length) await periodoStore.loadPeriodos();

const selectedPeriodo = ref(periodosActivos.value[0]?.id || null);

const documentoStore = useDocumentoStore();
const { programaciones, loading } = storeToRefs(documentoStore);

onMounted(() => {
    if (selectedPeriodo.value) {
        documentoStore.loadProgramaciones(selectedPeriodo.value);
    }
});

watch(selectedPeriodo, (newPeriodoId) => {
    documentoStore.loadProgramaciones(newPeriodoId);
});

const { slider, sliderData, showSlider, hideSlider } = useSlider("programacion-crud");

const getProgramacionStatus = (doc) => {
    if (!doc.fecha_inicio) return { text: 'Sin Fecha', class: 'bg-gray-100 text-gray-600' };
    const ahora = new Date();
    const inicio = new Date(doc.fecha_inicio);
    const fin = new Date(doc.fecha_fin);
    fin.setHours(23, 59, 59);

    if (ahora < inicio) return { text: 'Programado', class: 'bg-blue-100 text-blue-700' };
    if (ahora >= inicio && ahora <= fin) return { text: 'Vigente', class: 'bg-green-100 text-green-700' };
    return { text: 'Finalizado', class: 'bg-red-100 text-red-600' };
};

// <-- ¡CORRECCIÓN DEL BUG!
const formatFecha = (fecha) => {
    if (!fecha) return 'N/A'; // Si la fecha es nula, no intentes formatearla
    const date = new Date(fecha + 'T00:00:00');
    if (isNaN(date)) return 'Fecha inválida'; // Verificación extra
    return date.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
};

// <-- ¡NUEVA FUNCIÓN! Para ir al detalle
const verDetalleEntrega = (programacion) => {
    router.push({
        name: 'programacion.detalle',
        params: { id: programacion.id },
    });
};
</script>

<template>
    <div class="w-full space-y-2 py-2 px-3">
        <div class="m-2">
            <div class="flex-between">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Programación de Entregas</h2>
                <CreateButton @click="showSlider(true)" title="Nueva Programación" :disabled="!selectedPeriodo"/>
            </div>
            <div class="flex-between my-5">
                <BaseSelect v-model="selectedPeriodo" :options="periodosActivos" label="nombre_periodo" value-prop="id" placeholder="Seleccione un Periodo" class="w-72" />
            </div>
        </div>
        <Table>
            <THead>
                <Th>Título / Descripción</Th>
                <Th>Plazo de Entrega</Th>
                <Th>Documentos Adjuntos</Th>
                <Th>Estado de Programación</Th>
                <Th>Publicación</Th>
                <Th class="text-center">Acción</Th>
            </THead>
            <TBody>
                <Tr v-if="loading"><Td colspan="6" class="text-center">Cargando...</Td></Tr>
                <Tr v-else-if="!selectedPeriodo"><Td colspan="6" class="text-center">Seleccione un periodo para empezar.</Td></Tr>
                <Tr v-else-if="programaciones.length === 0"><Td colspan="6" class="text-center">No hay programaciones para este periodo. ¡Crea una!</Td></Tr>
                <!-- ¡CAMBIOS AQUÍ para hacer la fila clickeable! -->
                <Tr v-for="prog in programaciones" :key="prog.id" @click="verDetalleEntrega(prog)" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" title="Click para ver el estado de entrega por grupo">
                    <Td>
                        <p class="font-semibold text-gray-800">{{ prog.tipo_entrega }}</p>
                        <p class="text-xs text-gray-500 max-w-xs">{{ prog.descripcion }}</p>
                    </Td>
                    <Td>{{ formatFecha(prog.fecha_inicio) }} - {{ formatFecha(prog.fecha_fin) }}</Td>
                    <Td>{{ prog.documentos_count }}</Td>
                    <Td>
                        <span :class="getProgramacionStatus(prog).class" class="px-2 py-1 text-xs rounded-md font-semibold">
                            {{ getProgramacionStatus(prog).text }}
                        </span>
                    </Td>
                    <Td>
                        <span v-if="prog.status" class="px-2 py-1 text-xs rounded-md font-semibold text-green-700 bg-green-100">Publicado</span>
                        <span v-else class="px-2 py-1 text-xs rounded-md font-semibold text-gray-600 bg-gray-100">Borrador</span>
                    </Td>
                    <Td class="text-center">
                       <MenuTable :actions="{ edit: true, delete: true }" @edit.stop="showSlider(true, prog)" @delete.stop="onDelete(prog)" />
                    </Td>
                </Tr>
            </TBody>
        </Table>
    </div>
    <DocumentoSlider :show="slider" :documento="sliderData" @hide="hideSlider" />
</template>