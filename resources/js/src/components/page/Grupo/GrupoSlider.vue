<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';

import useDocenteStore from '../../../store/Docente/useDocenteStore';

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useUtils from '../../../composables/useUtils';
import useModalToast from '../../../composables/useModalToast';
import * as yup from 'yup';
import SelectedChips from '../../ui/selectedChips.vue';
import CheckBox from '../../ui/CheckBox.vue';
import BaseSelect from '../../ui/BaseSelect.vue';
import BaseSelectCiclo from '../../ui/BaseSelectCiclo.vue';
import useProgramaStore from '../../../store/Programa/useProgramaStore'
import useGrupoStore from '../../../store/Grupo/useGrupoStore';
import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    user: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const docenteStore = useDocenteStore();
const programaStore = useProgramaStore();
const grupoStore = useGrupoStore();

const { store: createUser, saving, update: updateUser, updating } = useHttpRequest('docente');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast()


const requiredPermissions = computed(() => {
    if (!props.user?.id) return ['todo-acceso-usuarios', 'crear-usuarios'];
    else return ['todo-acceso-usuarios', 'editar-usuarios'];
});

if (!programaStore.programa.length) await programaStore.loadPrograma();

const title = computed(() => (props.user ? `Actualizar Docente "${props.user?.name}"` : 'Añadir Nuevo  Docente'));

const initialFormData = () => ({
    id_programa: null,
    id_especialidad: null,
    id_modulo: null,
    id_periodo: null,
    convenio: null,
    fecha_inicio: null,
    fecha_fin: null,
    fecha_entrega_acta: null,
    seccion: null,
    turno: null,
    docente: null,
    status: null,
});

console.log('store de prograa', programaStore.programa)

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

const onProgramaChange = async (programaId) => {

    console.log('entradn qui')
    formData.value.id_especialidad = null;
    formData.value.id_modulo = null;
    formData.value.id_periodo = null;
    await grupoStore.loadEspecialidades(programaId);
    console.log('store de grupoesp', grupoStore.especialidades)

};

const onEspecialidadChange = async (especialidadId) => {
    console.log('entrado por modulos')
    formData.value.id_modulo = null;
    formData.value.id_periodo = null;
    await grupoStore.loadModulos(especialidadId);
};

const onModuloChange = async (moduloId) => {
    formData.value.id_periodo = null;
    await grupoStore.loadPeriodo(moduloId); // esto guarda el periodo en el store

    // Asignar el id del periodo automáticamente al select
    if (grupoStore.periodo?.id) {
        formData.value.id_periodo = grupoStore.periodo.id;
    }
};


const schema = yup.object().shape({
    name: yup.string().nullable().required("El nombre es requerido."),
    apellido_paterno: yup.string().nullable().required("El apellido paterno es requerido."),
    apellido_materno: yup.string().nullable().required("El apellido materno es requerido."),
    usuario: yup.string().nullable().required("El usuario es requerido."),
    dni: yup.string().nullable().required("El dni es requerido."),
    email: yup.string().email("Debe ser un email válido.").nullable().required("El email es requerido."),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;
    let data = { ...formData.value, roles: formData.value.roles[0] };
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    // console.log(formData.value)

    formErrors.value = {};
    const fieldsToBeOmitted = ['confirm_password'];
    if (props.user?.id) fieldsToBeOmitted.push('password');
    data = omitPropsFromObject(data, fieldsToBeOmitted);
    const response = props.user?.id ? await updateUser(props.user?.id, data) : await createUser(data);
    if (response?.user.id) {
        showToast(`Docente ${props.user?.id ? 'actualizado' : 'creado'} correctamente.`);

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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormLabelError label="Programa" required>
                        <BaseSelectGrupo v-model="formData.id_programa" :options="programaStore.programa"
                            label="numero_rd" placeholder="Seleccione un programa" @change="onProgramaChange" />
                    </FormLabelError>

                    <FormLabelError label="Especialidad" required>
                        <BaseSelectGrupo v-model="formData.id_especialidad" :options="grupoStore.especialidades"
                            label="nombre_especialidad" placeholder="Seleccione una especialidad"
                            @change="onEspecialidadChange" />
                    </FormLabelError>

                    <FormLabelError label="Modulos" required>
                        <BaseSelectGrupo v-model="formData.id_modulo" :options="grupoStore.modulos"
                            label="nombre_modulo" placeholder="Seleccione un módulo" @change="onModuloChange" />
                    </FormLabelError>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- <FormLabelError label="Periodo" required>
                        <BaseSelectGrupo v-model="formData.id_periodo" :options="grupoStore.periodo" label="nombre"
                            placeholder="Seleccione un periodo" disabled />
                    </FormLabelError> -->
                    <FormInput v-model="formData.usuario" label="Periodo" :error="formErrors?.usuario"
                        class="md:col-span-1" required />
                    <FormInput v-model="formData.dni" label="Convenio" :error="formErrors?.dni" required />

                </div>

                <Button :title="user?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                    :loading-title="user?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" @click="onSubmit" />
            </div>
        </AuthorizationFallback>
    </Slider>
</template>