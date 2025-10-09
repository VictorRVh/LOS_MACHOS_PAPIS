<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import * as yup from "yup";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import MenuTable from "../../components/table/MenuTable.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";
import FormInput from "../../components/ui/FormInput.vue";
import CheckBox from "../../components/ui/CheckBox.vue";
import Button from "../../components/ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

import usePeriodoStatusStore from "../../store/Periodo/usePeriodoStatusStore";
import useModalToast from "../../composables/useModalToast";
import useValidation from "../../composables/useValidation";
import useHttpRequest from "../../composables/useHttpRequest";

const router = useRouter();
const periodoStore = usePeriodoStatusStore();
const { periodos: periodosActivos } = storeToRefs(periodoStore);
if (!periodoStore.periodos.length) await periodoStore.loadPeriodos();

const { showToast, showConfirmModal } = useModalToast();
const { runYupValidation } = useValidation();
const { indexWithParams, store, update, destroy, loading, saving, updating } = useHttpRequest('/entrega_docente_admin');

const programaciones = ref([]);
const selectedPeriodo = ref(periodosActivos.value[0]?.id || null);
const isEditing = ref(false);
const formErrors = ref({});

const initialFormData = () => ({
    id: null,
    id_periodo: selectedPeriodo.value,
    tipo_entrega: '',
    descripcion: '',
    fecha_inicio: '',
    fecha_fin: '',
    status: true,
});

const formData = ref(initialFormData());

const schema = yup.object().shape({
    id_periodo: yup.string().required('El periodo es requerido.'),
    tipo_entrega: yup.string().required('El título es requerido.'),
    fecha_inicio: yup.date().required('La fecha de inicio es requerida.'),
    fecha_fin: yup.date().required('La fecha de fin es requerida.').min(yup.ref('fecha_inicio'), 'La fecha de fin no puede ser anterior a la de inicio.'),
});

const tooltip = ref({ visible: false, text: 'Ver Entregas por Grupo', x: 0, y: 0 });

const fetchProgramaciones = async (periodoId) => {
    if (!periodoId) {
        programaciones.value = [];
        return;
    }
    const response = await indexWithParams({ id_periodo: periodoId });
    programaciones.value = response || [];
    console.log("lista de porgramacion: ",programaciones.value)
};


onMounted(() => {
    fetchProgramaciones(selectedPeriodo.value);
});

watch(selectedPeriodo, (newPeriodoId) => {
    fetchProgramaciones(newPeriodoId);
    formData.value.id_periodo = newPeriodoId;
    resetForm();
});

const getProgramacionStatus = (doc) => {
    if (!doc.fecha_inicio) return { text: 'Sin Fecha', class: 'bg-gray-100 text-gray-600' };
    const ahora = new Date();
    const inicio = new Date(doc.fecha_inicio);
    const fin = new Date(doc.fecha_fin);
    fin.setHours(23, 59, 59);
    if (ahora < inicio) return { text: 'Programado', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' };
    if (ahora >= inicio && ahora <= fin) return { text: 'Vigente', class: 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300' };
    return { text: 'Finalizado', class: 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300' };
};

const formatFecha = (fecha) => {
    if (!fecha) return 'N/A';
    const date = new Date(fecha + 'T00:00:00');
    return isNaN(date) ? 'Fecha inválida' : date.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
};

const verDetalleEntrega = (programacion) => {
    router.push({ name: 'programacion.detalle', params: { id: programacion.id } });
};

const resetForm = () => {
    formData.value = initialFormData();
    isEditing.value = false;
    formErrors.value = {};
}

const editProgramacion = (prog) => {
    const dataToEdit = { ...prog, id_periodo: prog.id_periodo_academico };
    formData.value = dataToEdit;
    isEditing.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    try {
        let response;
        if (isEditing.value) {
            response = await update(formData.value.id, formData.value);
        } else {
            response = await store(formData.value);
        }
        if (response) {
            await fetchProgramaciones(selectedPeriodo.value);
            showToast(`Programación ${isEditing.value ? 'actualizada' : 'creada'} con éxito.`, 'success');
            resetForm();
        }
    } catch (error) {
        showToast('Ocurrió un error al guardar.', 'error');
    }
}

const onDelete = (prog) => {
    showConfirmModal('¿Seguro que quieres eliminar esta programación?', async (confirmed) => {
        if (!confirmed) return;
        try {
            await destroy(prog.id);
            await fetchProgramaciones(selectedPeriodo.value);
            showToast('Programación eliminada.', 'success');
            if(formData.value.id === prog.id) resetForm();
        } catch (error) {
            showToast('Error al eliminar.', 'error');
        }
    });
}

const updateTooltipPos = (event) => {
  if (tooltip.value.visible) {
    tooltip.value.x = event.clientX + 15;
    tooltip.value.y = event.clientY + 15;
  }
};
</script>

<template>
    <div class="p-4 md:p-6 space-y-6" @mousemove="updateTooltipPos">
        <header>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Programación de Entregas</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Crea y gestiona los plazos de entrega de documentos para los docentes.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
                    <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
                        {{ isEditing ? 'Editar Programación' : 'Nueva Programación' }}
                    </h3>
                    <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
                    <form @submit.prevent="onSubmit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periodo Académico</label>
                            <BaseSelectGrupo v-model="selectedPeriodo" :options="periodosActivos" label="nombre_periodo" value-prop="id" placeholder="Seleccione un Periodo" />
                        </div>
                        <FormInput v-model="formData.tipo_entrega" label="Título o Tipo de Entrega *" :error-message="formErrors.tipo_entrega" placeholder="Ej: Sílabo mensual"/>
                        <FormInput v-model="formData.descripcion" label="Descripción" :error-message="formErrors.descripcion" />
                        <div class="grid grid-cols-2 gap-4">
                             <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio *" type="date" :error-message="formErrors.fecha_inicio" />
                             <FormInput v-model="formData.fecha_fin" label="Fecha de Fin *" type="date" :error-message="formErrors.fecha_fin" />
                        </div>
                        <div class="flex items-center space-x-3 pt-2">
                             <CheckBox v-model="formData.status" />
                             <div>
                                 <label class="font-medium text-gray-800 dark:text-gray-200">Publicar para docentes</label>
                                 <p class="text-xs text-gray-500 dark:text-gray-400">Al desmarcar, quedará como borrador.</p>
                             </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Programación'" type="submit" :loading="saving || updating" class="w-full" />
                            <Button v-if="isEditing" title="Cancelar" variant="outline" @click="resetForm" />
                        </div>
                    </form>
                </div>
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
                        <Tr v-if="loading"><Td colspan="5" class="text-center py-10">Cargando...</Td></Tr>
                        <Tr v-else-if="!selectedPeriodo"><Td colspan="5" class="text-center py-12">Seleccione un periodo para empezar.</Td></Tr>
                        <Tr v-else-if="!programaciones.length"><Td colspan="5" class="text-center py-12">No hay programaciones para este periodo.</Td></Tr>
                        <Tr v-else v-for="prog in programaciones" :key="prog.id" 
                            >
                            <Td>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 hover:text-cetpro dark:hover:text-cetpro-light">{{ prog.tipo_entrega }}</p>
                                
                            </Td>
                            <Td class="font-mono text-xs">{{ prog.fecha_inicio }} - {{prog.fecha_fin }}</Td>
                            <Td>
                                <span :class="getProgramacionStatus(prog).class" class="px-2 py-1 text-xs rounded-full font-semibold">
                                    {{ getProgramacionStatus(prog).text }}
                                </span>
                            </Td>
                            <Td>
                                <span v-if="prog.status" class="px-2 py-1 text-xs rounded-full font-semibold text-green-700 bg-green-100 dark:bg-green-900/50 dark:text-green-300">Publicado</span>
                                <span v-else class="px-2 py-1 text-xs rounded-full font-semibold text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300">Borrador</span>
                            </Td>
                            <Td class="text-center">
                               <MenuTable 
                                 :actions="{ view: true, edit: true, delete: true }"
                                 entity-label="entrega"
                                 @view="verDetalleEntrega(prog)"
                                 @edit="editProgramacion(prog)"
                                 @delete="onDelete(prog)"
                               />
                            </Td>
                        </Tr>
                    </TBody>
                </Table>
            </div>
        </div>

        <div 
          v-if="tooltip.visible"
          class="fixed z-50 px-3 py-1.5 bg-cetpro-dark text-white text-xs font-semibold rounded-md shadow-lg pointer-events-none"
          :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
        >
          {{ tooltip.text }}
        </div>
    </div>
</template>