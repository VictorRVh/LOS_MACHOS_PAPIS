<script setup>
import { ref, watch,computed } from "vue";
import * as yup from "yup";

import FormInput from "../../ui/FormInput.vue";
import CheckBox from "../../ui/CheckBox.vue";
import Button from "../../ui/Button.vue";
import BaseSelectGrupo from "../../ui/BaseSelectGrupo.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useProgramacionSubidostore from "../../../store/Documento/useDocumentoSubidoStore";

const props = defineProps({
  programacionToEdit: {
    type: Object,
    default: null,
  },
  periodos: {
    type: Array,
    required: true,
  },
  selectedPeriodoId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['form-submitted', 'cancel-edit']);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { store, update, saving, updating } = useHttpRequest('/entrega_docente_admin');

const isEditing = ref(false);
const formErrors = ref({});
const programacionDocumento = useProgramacionSubidostore();

const initialFormData = () => ({
    id: null,
    id_periodo: '',
    tipo_entrega: '',
    fecha_inicio: '',
    fecha_fin: '',
    hora_inicio: '',
    hora_fin:'',
    mostrar: 0,
});

const formData = ref(initialFormData());

const schema = yup.object().shape({
    id_periodo: yup.string().required('El periodo es requerido.'),
    tipo_entrega: yup.string().required('El título es requerido.'),
    fecha_inicio: yup.date().required('La fecha de inicio es requerida.'),
    fecha_fin: yup.date().required('La fecha de fin es requerida.').min(yup.ref('fecha_inicio'), 'La fecha de fin no puede ser anterior a la de inicio.'),
      hora_inicio: yup.string().required('La hora de inicio es requerida.'),
  hora_fin: yup.string().required('La hora de fin es requerida.'),
});

const requiredPermissions = computed(() => {
  return props.comision?.id
    ? ["todo-acceso-documento-programado", "editar-documento-programado"]
    : ["todo-acceso-documento-programado", "crear-documento-programado"];
});

watch(() => props.programacionToEdit, (newVal) => {
    if (newVal) {
        formData.value = { ...newVal, id_periodo: newVal.id_periodo_academico };
        isEditing.value = true;
        formErrors.value = {};
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        resetForm();
    }
}, { deep: true });




const resetForm = () => {
    formData.value = initialFormData();
    isEditing.value = false;
    formErrors.value = {};
    emit('cancel-edit');
}

const onSubmit = async () => {
  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  // 🕓 Combinar fecha + hora en formato ISO
  const inicioISO = new Date(`${formData.value.fecha_inicio}T${formData.value.hora_inicio}:00Z`).toISOString();
  const finISO = new Date(`${formData.value.fecha_fin}T${formData.value.hora_fin}:00Z`).toISOString();

  const payload = {
    ...formData.value,
    fecha_inicio: inicioISO,
    fecha_fin: finISO,
  };

  try {
    let response;
    if (isEditing.value) {
      response = await update(payload.id, payload);
    } else {
      response = await store(payload);
    }

    if (response) {
      showToast(`Programación ${isEditing.value ? 'actualizada' : 'creada'} con éxito.`, 'success');
      emit('form-submitted');
      await programacionDocumento.loadgetProgramacionAdminByPerido(selectedPeriodoId);
    }
  } catch (error) {
    showToast('Ocurrió un error al guardar.', 'error');
  }
};

</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
   
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
        <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            {{ isEditing ? 'Editar Programación' : 'Nueva Programación' }}
        </h3>
        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
        <form @submit.prevent="onSubmit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periodo Académico</label>
                <BaseSelectGrupo 
                  v-model="formData.id_periodo" 
                  :options="periodos" 
                  label="nombre_periodo" 
                  value-prop="id" 
                  placeholder="Seleccione un Periodo"
                 
                />
            </div>
            <FormInput v-model="formData.tipo_entrega" label="Título o Tipo de Entrega *" :error-message="formErrors.tipo_entrega" placeholder="Ej: Sílabo mensual"/>
            
            <div class="grid grid-cols-2 gap-4">
                 <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio *" type="date" :error-message="formErrors.fecha_inicio" />
                 <FormInput v-model="formData.fecha_fin" label="Fecha de Fin *" type="date" :error-message="formErrors.fecha_fin" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                 <FormInput v-model="formData.hora_inicio" label="Fecha de Inicio *" type="time" :error-message="formErrors.hora_inicio" />
                 <FormInput v-model="formData.hora_fin" label="Fecha de Fin *" type="time" :error-message="formErrors.hora_fin" />
            </div>

            <div class="flex items-center space-x-3 pt-2">
                 <CheckBox v-model="formData.mostrar" />
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
     </AuthorizationFallback>
</template>