<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';

import useDocenteStore from '../../../store/Administrativo/useAdministrativoStore';

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useUtils from '../../../composables/useUtils';
import useModalToast from '../../../composables/useModalToast';
import * as yup from 'yup';
import SelectedChips from '../../ui/selectedChips.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelect from '../../ui/BaseSelect.vue';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    user: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const docenteStore = useDocenteStore();

const { store: createUser, saving, update: updateUser, updating } = useHttpRequest('personal_administrativo');
const { runYupValidation } = useValidation();
const { showToast } = useModalToast()


const requiredPermissions = computed(() => {
    if (!props.user?.id) return ['todo-acceso-usuarios', 'crear-usuarios'];
    else return ['todo-acceso-usuarios', 'editar-usuarios'];
});

const title = computed(() => (props.user ? `Actualizar Local y Turno para "${props.user?.name}"` : 'Añadir Nuevo  Docente'));

const initialFormData = () => ({
    id_usuario: props.user?.id,
    turno: null,
    local: null,
    
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
    if (props.show) {
        if (props.user?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => ({ ...r, [key]: props.user[key] || val }),
                {}
            );
        } else {
            formData.value = initialFormData();
            formErrors.value = {};
        }
    }
});

const schema = yup.object().shape({
    turno: yup.string().nullable(),
    local: yup.string().nullable(),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;
    let data = { ...formData.value};
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    // console.log(formData.value)
    //console.log()
    const response = props.user?.id ? await updateUser(props.user?.id, data) : await createUser(data);
    
   // console.log("administrativo: ",response?.data?.id);

    if (response?.data?.id) {
        showToast(`Datos de ${props.user?.id ? 'actualizado' : 'creado'} correctamente.`);
        
        docenteStore.loadDocentes();
        emit('hide');
    }
};
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">

            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

            <div class="mt-4 space-y-3">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput v-model="formData.turno" label="Turno" :error="formErrors?.turno"  />
                    <FormInput v-model="formData.local" label="Local"
                        :error="formErrors?.local"  />
                </div>

                <Button :title="user?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                    :loading-title="user?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" @click="onSubmit" />
            </div>
        </AuthorizationFallback>
    </Slider>
</template>