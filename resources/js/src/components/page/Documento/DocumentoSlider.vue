<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import Button from '../../ui/Button.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelect from '../../ui/BaseSelect.vue';
import useDocumentoStore from '../../../store/Documento/useDocumentoStore';
import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import * as yup from 'yup';

const props = defineProps({
    show: { type: Boolean, default: false },
    documento: { type: [Object, null], default: null },
    periodoSeleccionado: { type: String, default: null },
});
const emit = defineEmits(['hide']);

const documentoStore = useDocumentoStore();
const { store: createEntrega, saving, update: updateEntrega, updating } = useHttpRequest('/entregas-admin');
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const title = computed(() => (props.documento ? `Editar Programación` : 'Nueva Programación de Entrega'));

const initialFormData = () => ({
    id_periodo: props.periodoSeleccionado,
    tipo_entrega: '',
    descripcion: '',
    fecha_inicio: '',
    fecha_fin: '',
    status: true,
    documento_plantilla: null,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const fileInput = ref(null);

watch(() => props.show, () => {
    if (props.show) {
        formErrors.value = {};
        if (fileInput.value) fileInput.value.value = '';
        
        if (props.documento?.id) {
            formData.value = {
                ...props.documento,
                status: !!props.documento.status,
                documento_plantilla: null,
            };
        } else {
            formData.value = initialFormData();
        }
    }
});

const schema = yup.object().shape({
    id_periodo: yup.string().required("Debe seleccionar un periodo."),
    tipo_entrega: yup.string().required("El tipo de entrega es requerido."),
    fecha_inicio: yup.date().required("La fecha de inicio es requerida."),
    fecha_fin: yup.date().required("La fecha de fin es requerida.").min(yup.ref('fecha_inicio'), "La fecha fin debe ser posterior a la de inicio."),
    documento_plantilla: yup.mixed().nullable().test('fileSize', 'El archivo no debe superar los 15MB', value => !value || (value && value.size <= 15 * 1024 * 1024)),
});

const handleFileChange = (event) => {
    formData.value.documento_plantilla = event.target.files[0] || null;
};

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    const payload = new FormData();
    Object.keys(formData.value).forEach(key => {
        let value = formData.value[key];
        if (key === 'status') value = value ? 1 : 0;
        if (value !== null && value !== undefined) {
             payload.append(key, value);
        }
    });

    if (props.documento?.id) {
        payload.append('_method', 'POST'); // Laravel trata FormData como POST, _method lo convierte a PUT/PATCH
         const response = await updateEntrega(`${props.documento.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } });
         if (response.id) {
            showToast(`Programación actualizada correctamente.`);
            documentoStore.loadDocumentos(props.periodoSeleccionado);
            emit('hide');
        }
    } else {
        const response = await createEntrega(payload, { headers: { 'Content-Type': 'multipart/form-data' } });
         if (response.id) {
            showToast(`Programación creada correctamente.`);
            documentoStore.loadDocumentos(props.periodoSeleccionado);
            emit('hide');
        }
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <div class="mt-4 space-y-4">
            <FormInput v-model="formData.tipo_entrega" label="Título o Tipo de Entrega" :error="formErrors?.tipo_entrega" required />
            
            <div>
                <label class="form-label">Descripción</label>
                <textarea v-model="formData.descripcion" rows="3" class="form-input" placeholder="Ej: Subir el sílabo mensual en formato PDF."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio" type="date" :error="formErrors?.fecha_inicio" required />
                <FormInput v-model="formData.fecha_fin" label="Fecha de Fin" type="date" :error="formErrors?.fecha_fin" required />
            </div>

             <div>
                <label class="form-label">Plantilla o Documento Guía (Opcional)</label>
                <input type="file" ref="fileInput" @change="handleFileChange" class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                <span v-if="formErrors?.documento_plantilla" class="text-sm text-red-500">{{ formErrors.documento_plantilla }}</span>
                <p v-if="documento?.id && documento.documento_plantilla_url" class="text-xs text-gray-500 mt-1">
                    Ya existe un archivo. Reemplácelo subiendo uno nuevo. <a :href="documento.documento_plantilla_url" target="_blank" class="text-blue-500 hover:underline">Ver actual</a>
                </p>
            </div>

            <CheckBox v-model="formData.status" label="Habilitar programación para los docentes" class="mt-4" />

            <Button :title="documento?.id ? 'Guardar Cambios' : 'Crear Programación'"
                :loading-title="documento?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                :loading="saving || updating" @click="onSubmit" />
        </div>
    </Slider>
</template>