<script setup>
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import Button from '../../ui/Button.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';
import usePeriodoStatusStore from '../../../store/Periodo/usePeriodoStatusStore';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';

const props = defineProps({
    show: { type: Boolean, default: false },
    documento: { type: [Object, null], default: null },
});
const emit = defineEmits(['hide', 'saved']);

const periodoStore = usePeriodoStatusStore();
const { periodos } = storeToRefs(periodoStore);

const { store: createEntrega, update: updateEntrega, saving, updating } = useHttpRequest('/entrega_docente_admin');
const { showToast } = useModalToast();

const title = computed(() => (props.documento?.id ? `Editar Programación` : 'Nueva Programación de Entrega'));
const isEditing = computed(() => !!props.documento?.id);

const initialFormData = () => ({
    id_periodo_academico: null,
    tipo_entrega: '',
    descripcion: '',
    fecha_inicio: '',
    fecha_fin: '',
    status: true,
});

const formData = ref(initialFormData());

watch(() => props.show, () => {
    if (props.show) {
        if (props.documento?.id) {
            formData.value = { 
                ...props.documento, 
                status: !!props.documento.status,
                id_periodo_academico: props.documento.id_periodo_academico
            };
        } else {
            formData.value = initialFormData();
        }
    }
});

const onSubmit = async () => {
    try {
        if (isEditing.value) {
            await updateEntrega(formData.value.id, formData.value);
            showToast('Programación actualizada exitosamente.');
        } else {
            await createEntrega(formData.value);
            showToast('Programación creada exitosamente.');
        }
        emit('saved');
        emit('hide');
    } catch (error) {
        showToast('Error al guardar la programación.', 'error');
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <div class="mt-4 space-y-4">
            <div>
                <label class="form-label required">Periodo Académico</label>
                <BaseSelectGrupo v-model="formData.id_periodo_academico" :options="periodos" label="nombre_periodo"
              placeholder="Seleccione un periodo" :reduce="p => p.id"/>
            </div>
            <FormInput v-model="formData.tipo_entrega" label="Título o Tipo de Entrega" required />
            <div>
                <label class="form-label">Descripción</label>
                <textarea v-model="formData.descripcion" rows="3" class="form-input" placeholder="Ej: Subir el sílabo mensual en formato PDF."></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio" type="date" required />
                <FormInput v-model="formData.fecha_fin" label="Fecha de Fin" type="date" required />
            </div>

            <CheckBox v-model="formData.status" label="Publicar esta programación para los docentes" class="mt-4" />
            <p class="text-xs text-gray-500">Al desmarcar esto, la programación quedará como borrador y no será visible para los docentes.</p>
            
            <Button 
                :title="isEditing ? 'Guardar Cambios' : 'Crear Programación'" 
                :loading-title="saving ? 'Creando...' : 'Guardando...'" 
                class="!mt-6 !w-full" 
                :loading="saving || updating" 
                @click="onSubmit" 
            />
        </div>
    </Slider>
</template>