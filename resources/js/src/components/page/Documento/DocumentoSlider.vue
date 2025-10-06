<script setup>
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import Button from '../../ui/Button.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelect from '../../ui/BaseSelect.vue';
import useDocumentoStore from '../../../store/Documento/useDocumentoStore';

// <-- ¡CAMBIO CLAVE AQUÍ! Usamos el store que filtra por estado.
import usePeriodoStatusStore from '../../../store/Periodo/usePeriodoStatusStore';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import { XCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    show: { type: Boolean, default: false },
    documento: { type: [Object, null], default: null },
});
const emit = defineEmits(['hide']);

const documentoStore = useDocumentoStore();
const periodoStore = usePeriodoStatusStore();
// Renombramos 'periodos' a 'periodosActivos' para que el resto del código no se rompa.
const { periodos: periodosActivos } = storeToRefs(periodoStore);

const { store: createEntrega, saving } = useHttpRequest('/entregas-admin');
const { destroy: deleteDocumento } = useHttpRequest('/entregas-admin-documento');
const { showToast } = useModalToast();

const title = computed(() => (props.documento ? `Editar Programación` : 'Nueva Programación de Entrega'));

const initialFormData = () => ({
    id_periodo: null,
    tipo_entrega: '',
    descripcion: '',
    fecha_inicio: '',
    fecha_fin: '',
    status: false,
    archivos: [],
});

const formData = ref(initialFormData());
const existingFiles = ref([]);
const filesToUpload = ref([]);
const fileInput = ref(null);

watch(() => props.show, () => {
    if (props.show) {
        filesToUpload.value = [];
        if (fileInput.value) fileInput.value.value = '';

        if (props.documento?.id) {
            formData.value = { ...props.documento, status: !!props.documento.status, archivos: [] };
            existingFiles.value = [...props.documento.documentos];
        } else {
            formData.value = initialFormData();
            existingFiles.value = [];
        }
    }
});

const handleFileChange = (event) => {
    filesToUpload.value = [...event.target.files];
};

const removeFileToUpload = (index) => {
    filesToUpload.value.splice(index, 1);
};

const removeExistingFile = async (file, index) => {
    await deleteDocumento(file.id);
    existingFiles.value.splice(index, 1);
    showToast('Documento eliminado.');
};

const onSubmit = async () => {
    if (saving.value) return;

    const payload = new FormData();
    Object.keys(formData.value).forEach(key => {
        if (key !== 'archivos') {
            let value = formData.value[key];
            if (key === 'status') value = value ? 1 : 0;
            if (value !== null) payload.append(key, value);
        }
    });

    filesToUpload.value.forEach(file => {
        payload.append('archivos[]', file);
    });

    const response = await createEntrega(payload, { headers: { 'Content-Type': 'multipart/form-data' } });
    if (response.id) {
        documentoStore.addProgramacion(response);
        showToast('Programación creada exitosamente.');
        emit('hide');
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <div class="mt-4 space-y-4">
            <div>
                <label class="form-label required">Periodo Académico</label>
                <!-- Este select ahora usa la lista correcta de periodos ACTIVOS -->
                <BaseSelect v-model="formData.id_periodo" :options="periodosActivos" label="nombre_periodo" value-prop="id" placeholder="Seleccione un Periodo" />
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
            <div>
                <label class="form-label">Adjuntar Documentos Guía</label>
                <input type="file" multiple ref="fileInput" @change="handleFileChange" class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                <div v-if="filesToUpload.length > 0" class="mt-2 space-y-1">
                    <div v-for="(file, index) in filesToUpload" :key="index" class="flex items-center justify-between bg-gray-100 p-1 rounded">
                        <span class="text-xs text-gray-700">{{ file.name }}</span>
                        <button @click="removeFileToUpload(index)" class="text-red-500 hover:text-red-700">
                            <XCircleIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                 <div v-if="existingFiles.length > 0" class="mt-2 space-y-1">
                    <p class="text-xs font-semibold">Archivos existentes:</p>
                    <div v-for="(file, index) in existingFiles" :key="file.id" class="flex items-center justify-between bg-gray-100 p-1 rounded">
                        <a :href="file.url" target="_blank" class="text-xs text-blue-600 hover:underline">{{ file.nombre_original }}</a>
                        <button @click="removeExistingFile(file, index)" class="text-red-500 hover:text-red-700">
                            <XCircleIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
            <CheckBox v-model="formData.status" label="Publicar esta programación para los docentes" class="mt-4" />
             <p class="text-xs text-gray-500">Al desmarcar esto, la programación quedará como borrador y no será visible para los docentes.</p>
            <Button title="Crear Programación" loading-title="Creando..." class="!mt-6 !w-full" :loading="saving" @click="onSubmit" />
        </div>
    </Slider>
</template>