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
import useProgramaStore from '../../../store/Programa/useProgramaStore'
import useGrupoStore from '../../../store/Grupo/useGrupoStore';
import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';
import useConvenioStore from '../../../store/Convenio/useConvenioStore'
import BaseSelectCiclo from '../../ui/BaseSelectCiclo.vue';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    grupo: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const docenteStore = useDocenteStore();
const programaStore = useProgramaStore();
const grupoStore = useGrupoStore();
const convenioStore = useConvenioStore();

const { store: createGrupo, saving, update: updateGrupo, updating } = useHttpRequest('/grupo');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast()


const requiredPermissions = computed(() => {
    if (!props.grupo?.id) return ['todo-acceso-usuarios', 'crear-usuarios'];
    else return ['todo-acceso-usuarios', 'editar-usuarios'];
});

if (!programaStore.programa.length) await programaStore.loadPrograma();
if (!convenioStore.convenios.length) await convenioStore.loadConvenios();
if (!docenteStore.docentes?.length) await docenteStore.loadDocentes();

const title = computed(() => (props.grupo ? `Actualizar Docente "${props.grupo?.name}"` : 'Añadir Nuevo  Docente'));

const initialFormData = () => ({
    id_programa: null,
    id_especialidad: null,
    id_modulo: null,
    id_periodo: null,
    id_convenio: null,
    fecha_inicio: null,
    fecha_fin: null,
    fecha_entrega_acta: null,
    seccion: null,
    turno: null,
    id_docente: null,
    status: 0,
});

console.log('store de prograa', programaStore.programa)

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
    if (props.show) {
        if (props.grupo?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => ({ ...r, [key]: props.grupo[key] || val }),
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
    await grupoStore.loadPeriodo(moduloId);

    if (grupoStore.periodo.length > 0) {
        formData.value.id_periodo = grupoStore.periodo[0].id;
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

    console.log('entrando aca')

    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
    };

    // const { validated, errors } = await runYupValidation(schema, data);
    // if (!validated) {
    //     formErrors.value = errors;
    //     return;
    // }
    // formErrors.value = {};

    const response = props.grupo?.id
        ? await updateGrupo(props.grupo?.id, data)
        : await createGrupo(data);

    if (response?.id) {
        showToast(`especialidad ${props.grupo?.id ? "editado" : "creado"} exitosamente.`);
        grupoStore.loadGrupos();

        if (!props.grupo?.id) {
            formData.value = initialFormData();
            formErrors.value = {};
        }
        emit("hide");
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
                        <BaseSelectGrupo
  v-model="formData.id_programa"
  :options="programaStore.programa"
  label="numero_rd"
  placeholder="Seleccione un programa"
  @change="onProgramaChange"
  :loading="grupoStore.especialidadByProgramLoading"
/>
                    </FormLabelError>

                    <FormLabelError label="Especialidad" required>
                        <BaseSelectGrupo
  v-model="formData.id_especialidad"
  :options="grupoStore.especialidades"
  label="nombre_especialidad"
  placeholder="Seleccione una especialidad"
  @change="onEspecialidadChange"
  :loading="grupoStore.moduloByEspecialidadLoading"
/>

                    </FormLabelError>

                    <FormLabelError label="Modulos" required>
                        <BaseSelectGrupo
  v-model="formData.id_modulo"
  :options="grupoStore.modulos"
  label="nombre_modulo"
  placeholder="Seleccione un módulo"
  @change="onModuloChange"
  :loading="grupoStore.docenteperiodoByModuloLoading"
/>
                    </FormLabelError>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <FormLabelError label="Periodo" required>
                        <BaseSelectGrupo v-model="formData.id_periodo" :options="grupoStore.periodo" label="nombre"
                            placeholder="Seleccione un periodo" disabled />
                    </FormLabelError>

                    <FormLabelError label="Convenio" required>
                        <BaseSelectCiclo v-model="formData.id_convenio" :options="convenioStore.convenios"
                            label="nombre_institucion" placeholder="Seleccione un convenio" />
                    </FormLabelError>

                    <FormLabelError label="Docente" >
                        <BaseSelectCiclo v-model="formData.id_docente" :options="docenteStore.docentes" label="name"
                            placeholder="Seleccione un docente" />
                    </FormLabelError>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <FormInput v-model="formData.fecha_inicio" label="Fecha Inicio" type="date" />
                    <FormInput v-model="formData.fecha_fin" label="Fecha de Fin" type="date" />
                    <FormInput v-model="formData.fecha_entrega_acta" label="Entrega de acta" type="date" />

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <FormInput v-model="formData.seccion" label="Seccion" />
                    <FormInput v-model="formData.turno" label="Turno" />
                    <CheckBox v-model="formData.status" label="Habilitado"
                        class="mt-8 pl-4 flex justify-center items-centers" />

                </div>

                <Button :title="grupo?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                    :loading-title="grupo?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" @click="onSubmit" />
            </div>
        </AuthorizationFallback>
    </Slider>
</template>