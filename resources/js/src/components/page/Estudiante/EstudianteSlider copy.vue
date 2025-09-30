<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useUtils from '../../../composables/useUtils';
import useModalToast from '../../../composables/useModalToast';
import * as yup from 'yup';

import axios from 'axios';
import useEstudianteStore from '../../../store/estudiante/useestudianteStore';

const props = defineProps({
    show: { type: Boolean, default: () => false },
    estudiante: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const estudianteStore = useEstudianteStore();

const { store: createEstudiante, saving, update: updateEstudiante, updating } = useHttpRequest('/estudiante');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast()


const requiredPermissions = computed(() => {
    if (!props.user?.id) return ['todo-acceso-usuarios', 'crear-usuarios'];
    else return ['todo-acceso-usuarios', 'editar-usuarios'];
});

const title = computed(() => (props.estudiante ? `Actualizar estudiante "${props.estudiante?.name}"` : 'Añadir Nuevo  estudiante'));

const initialFormData = () => ({
    tipo_documento: 'dni',
    nro_documento: null,
    apellido_paterno: null,
    apellido_materno: null,
    nombre: null,
    sexo: null,
    pais_nacimiento: null,
    departamento_nacimiento: null,
    provincia_nacimiento: null,
    distrito_nacimiento: null,
    lugar_nacimiento: null,
    direccion_residencia: null,
    fecha_nacimiento: null,
    estado_civil: null,
    grado_instruccion: null,
    trabaja: null,
    puesto_trabajo: null,
    carga_familiar: null,
    correo_electronico: null,
    celular_personal: null,
    internet_casa: null,
    tipo_operador: null,
    equipo_clases: null,
    discapacidad: null,
    celular_referencia: null,
    parentesco_referencia: null,
    lengua_originaria: null,
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
    if (props.show) {
        if (props.estudiante?.id) {
            formData.value = Object.entries(initialFormData()).reduce(
                (r, [key, val]) => ({ ...r, [key]: props.estudiante[key] || val }),
                {}
            );
        } else {
            formData.value = initialFormData();
            
        }
        formErrors.value = {};
    }
});


const schema = yup.object().shape({
    tipo_documento: yup.string().nullable().required("El tipo de documento es requerido."),
    nro_documento: yup.string().nullable().required("El número de documento es requerido."),
    apellido_paterno: yup.string().nullable().required("El apellido paterno es requerido."),
    apellido_materno: yup.string().nullable().required("El apellido materno es requerido."),
    nombre: yup.string().nullable().required("El nombre es requerido."),
    sexo: yup.string().nullable().required("El sexo es requerido."),
    pais_nacimiento: yup.string().nullable().required("El país de nacimiento es requerido."),
    departamento_nacimiento: yup.string().nullable().required("El departamento de nacimiento es requerido."),
    provincia_nacimiento: yup.string().nullable().required("La provincia de nacimiento es requerida."),
    distrito_nacimiento: yup.string().nullable().required("El distrito de nacimiento es requerido."),
    lugar_nacimiento: yup.string().nullable().required("El lugar de nacimiento es requerido."),
    direccion_residencia: yup.string().nullable().required("La dirección de residencia es requerida."),
    fecha_nacimiento: yup.date().nullable().required("La fecha de nacimiento es requerida."),
    estado_civil: yup.string().nullable().required("El estado civil es requerido."),
    grado_instruccion: yup.string().nullable().required("El grado de instrucción es requerido."),
    trabaja: yup.string().nullable().required("El campo 'trabaja' es requerido."),
    puesto_trabajo: yup.string().nullable().required("El campo 'puestotrabajo' es requerido."),
    carga_familiar: yup.string().nullable("Carga familiar obligatoria"),
    correo_electronico: yup.string().nullable().email("Debe ser un correo válido.").required("El correo electrónico es requerido."),
    celular_personal: yup.string().nullable().required("El celular personal es requerido."),
    internet_casa: yup.string().nullable().required("Debe indicar si cuenta con internet en casa."),
    tipo_operador: yup.string().nullable().required("El tipo de operador es requerido."),
    equipo_clases: yup.string().nullable().required("Debe indicar si cuenta con equipo para clases."),
    discapacidad: yup.string().nullable().required("Debe indicar si tiene alguna discapacidad."),
    celular_referencia: yup.string().nullable().required("El celular de referencia es requerido."),
    parentesco_referencia: yup.string().nullable().required("El parentesco de referencia es requerido."),
    lengua_originaria: yup.string().nullable().required("Debe indicar si habla alguna lengua originaria."),
});

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
    };

    console.log(data)

    // const { validated, errors } = await runYupValidation(schema, data);
    // if (!validated) {
    //     formErrors.value = errors;
    //     return;
    // }
    // formErrors.value = {};

    const response = props.estudiante?.id
        ? await updateEstudiante(props.estudiante?.id, data)
        : await createEstudiante(data);

    console.log(response)

    if (response?.id) {
        showToast(`Estudiante ${props.estudiante?.id ? "editado" : "creado"} exitosamente.`);
        // especialidadStore.loadEspecialidad();

        // console.log(props.especialidad)

        if (!props.estudiante?.id) {
            formData.value = initialFormData();
            formErrors.value = {};
        }
        emit("hide");
    }
};

async function buscarPorDNI() {
    const dni = formData.value.nro_documento

    if (dni.length !== 8 || isNaN(dni)) {
        formErrors.value.dni = 'El DNI debe tener 8 dígitos numéricos.'
        return
    }

    try {
        const response = await axios.post('http://127.0.0.1:8000/api/buscar-dni', {
            dni: dni
        })

        console.log('RESPUESTA RENIEC', response)

        const data = response.data

        if (!data.success) {
            formErrors.value.dni = 'No se encontró información para el DNI ingresado.'
            return
        }

        const info = data.data

        formData.value.nombre = info.name || '' // ← este campo es el correcto
        formData.value.apellido_paterno = info.first_last_name || ''
        formData.value.apellido_materno = info.second_last_name || ''
        // formData.value.direccion = info.address || ''
        // formData.value.fecha_nacimiento = info.date_of_birth || ''
        // formErrors.value.dni = ''
    } catch (error) {
        console.error(error)
        formErrors.value.dni = 'Error al consultar DNI. Intente nuevamente.'
    }
}

</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">

            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

            <div class="mt-4 space-y-3">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- BUSQUEDA POR DNI -->

                    <div>
                        <div class="flex gap-2">
                            <FormInput v-model="formData.nro_documento" label="DNI" :error="formErrors?.dni"
                                maxlength="8" placeholder=" Ingrese DNI" />

                            <Button title="Buscar" :loading-title="'Buscando...'" :loading="buscandoDni"
                                class="!w-16 !h-10 !mt-6 !text-sm" @click="buscarPorDNI" />

                        </div>
                        <p v-if="formErrors?.dni" class="text-red-500 text-sm mt-1">{{ formErrors.dni }}</p>
                    </div>



                    <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno"
                        :error="formErrors?.apellido_paterno" required disabled="true" />
                    <FormInput v-model="formData.apellido_materno" label="Apellido Materno"
                        :error="formErrors?.apellido_materno" required disabled="true" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.nombre" label="Nombres" :error="formErrors?.nombre"
                        class="md:col-span-1" required disabled="true" />
                    <FormInput v-model="formData.sexo" label="Sexo" :error="formErrors?.sexo" required />
                    <FormInput v-model="formData.pais_nacimiento" label="Pais de Nacimiento"
                        :error="formErrors?.pais_nacimiento" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.departamento_nacimiento" label="Departamento de nacimiento" />
                    <FormInput v-model="formData.provincia_nacimiento" label="Provincia de nacimiento" />
                    <FormInput v-model="formData.distrito_nacimiento" label="Distrito de nacimiento" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.lugar_nacimiento" label="Lugar de nacimiento" />
                    <FormInput v-model="formData.direccion_residencia" label="Direccion de residencia" />
                    <FormInput v-model="formData.fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.estado_civil" label="Estado civil" />
                    <FormInput v-model="formData.grado_instruccion" label="Grado de instrucción" />
                    <FormInput v-model="formData.trabaja" label="¿Trabaja?" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.puesto_trabajo" label="Puesto de trabajo" />
                    <FormInput v-model="formData.carga_familiar" label="Carga familiar" />
                    <FormInput v-model="formData.correo_electronico" label="Correo electrónico" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.celular_personal" label="Celular personal" />
                    <FormInput v-model="formData.internet_casa" label="¿Internet en casa?" />
                    <FormInput v-model="formData.tipo_operador" label="Tipo de operador" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput v-model="formData.equipo_clases" label="¿Equipo para clases?" />
                    <FormInput v-model="formData.discapacidad" label="¿Discapacidad?" />
                    <FormInput v-model="formData.celular_referencia" label="Celular de referencia" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput v-model="formData.parentesco_referencia" label="Nro de familiar" />
                    <FormInput v-model="formData.lengua_originaria" label="Lengua originaria" />
                </div>

                <Button :title="estudiante?.id ? 'Guardar Cambios' : 'Crear Usuario'" key="submit-btn"
                    :loading-title="estudiante?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
                    :loading="saving || updating" @click="onSubmit" />

            </div>
        </AuthorizationFallback>
    </Slider>
</template>