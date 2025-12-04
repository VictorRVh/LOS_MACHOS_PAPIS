<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import useMatriculaUpStore from '../../../store/Matricula/useMatriculaUpdateStore';

import Step2 from './Steps/Step2.vue';
import Step3 from './Steps/Step3.vue';
import Button from '../../ui/Button.vue';
import * as yup from "yup";

const router = useRouter();
const route = useRoute();
const { showToast } = useModalToast();
const { updating, update: updateMatricula } = useHttpRequest('/matricula');

const props = defineProps({
    id: { type: String, required: true },
});
const dataMatricula = useMatriculaUpStore();
// estado
const isLoading = ref(true);
const isEditing = ref(false);
const matriculaId = ref(null);
const formErrors = ref({});
const nameGrupo = ref("");
const idGrupo = ref("");



// Solo Step2 y Step3
const formData = ref({
    // --- STEP 2 ---
    tipo_documento: 'DNI',
    nro_documento: '',
    apellido_paterno: '',
    apellido_materno: '',
    nombre: '',
    sexo: '',
    fecha_nacimiento: '',
    pais_nacimiento: 'PERÚ',
    departamento_nacimiento: '',
    provincia_nacimiento: '',
    distrito_nacimiento: '',
    lugar_nacimiento: '',
    direccion_residencia: '',
    correo_electronico: '',
    celular_personal: '',
    estado_civil: '',
    grado_instruccion: '',
    trabaja: '',
    detalle_trabajo: '',
    carga_familiar: '',
    detalle_carga_familiar: '',
    internet_casa: '',
    tipo_internet: '',
    tipo_operador: '',
    equipo_clases: [],
    discapacidad: '',
    tipo_discapacidad: '',
    celular_referencia: '',
    parentesco_referencia: '',
    lengua_materna: '',

    // --- STEP 3 ---
    condicion: 'G | Gratuito',
    nro_recibo: '',
    aporte: '',
    anio_egreso: ''
});

// VALIDACIÓN SOLO STEP 2 (step3 no necesita)
const schema = yup.object({
    tipo_documento: yup.string().required('Tipo de documento es requerido'),
    nro_documento: yup.string().required('N° de documento es requerido'),
    apellido_paterno: yup.string().required('Apellido paterno es requerido'),
    apellido_materno: yup.string().required('Apellido materno es requerido'),
    nombre: yup.string().required('Nombre es requerido'),
    sexo: yup.string().required('Sexo es requerido'),

    fecha_nacimiento: yup.date()
        .required('Fecha de nacimiento es requerida')
        .max(new Date(new Date().setFullYear(new Date().getFullYear() - 12)), 'Debe tener más de 12 años')
        .min(new Date(new Date().setFullYear(new Date().getFullYear() - 100)), 'Edad inválida'),

    celular_personal: yup
        .string()
        .required('Celular es requerido')
        .matches(/^\d{9}$/, 'El celular debe tener 9 números'),

    celular_referencia: yup
        .string()
        .notRequired()
        .matches(/^\d{9}$/, 'El celular debe tener 9 números'),

    correo_electronico: yup
        .string()
        .email('Debe ser un correo válido')
        .notRequired(),

    direccion_residencia: yup.string().required('La dirección es requerida'),
    estado_civil: yup.string().required('Estado civil es requerido'),
});


const validate = async () => {
    try {
        await schema.validate(formData.value, { abortEarly: false });
        formErrors.value = {};
        return true;
    } catch (err) {
        const errors = {};
        err.inner.forEach(e => errors[e.path] = e.message);
        formErrors.value = errors;
        return false;
    }
};

// cargar estudiante
const editarEstudiante = async (idMatricula) => {
    try {
        await dataMatricula.loadMatriculaUpdate(props.id)
        const data = dataMatricula.matriculaUpdate;

        // Cargar datos correctamente
        formData.value = {
            ...formData.value,

            // Estudiante
            tipo_documento: data.estudiante.tipo_documento,
            nro_documento: data.estudiante.nro_documento,
            apellido_paterno: data.estudiante.apellido_paterno,
            apellido_materno: data.estudiante.apellido_materno,
            nombre: data.estudiante.nombre,
            sexo: data.estudiante.sexo,
            fecha_nacimiento: data.estudiante.fecha_nacimiento,
            pais_nacimiento: data.estudiante.pais_nacimiento,
            departamento_nacimiento: data.estudiante.departamento_nacimiento,
            provincia_nacimiento: data.estudiante.provincia_nacimiento,
            distrito_nacimiento: data.estudiante.distrito_nacimiento,
            lugar_nacimiento: data.estudiante.lugar_nacimiento,
            direccion_residencia: data.estudiante.direccion_residencia,
            correo_electronico: data.estudiante.correo_electronico,
            celular_personal: data.estudiante.celular_personal,
            estado_civil: data.estudiante.estado_civil,
            grado_instruccion: data.estudiante.grado_instruccion,
            trabaja: data.estudiante.trabaja,
            detalle_trabajo: data.estudiante.detalle_trabajo,
            carga_familiar: data.estudiante.carga_familiar,
            detalle_carga_familiar: data.estudiante.detalle_carga_familiar,
            internet_casa: data.estudiante.internet_casa,
            tipo_internet: data.estudiante.tipo_internet,
            equipo_clases: JSON.parse(data.estudiante.equipos_virtuales ?? "[]"),
            discapacidad: data.estudiante.discapacidad,
            tipo_discapacidad: data.estudiante.tipo_discapacidad,
            celular_referencia: data.estudiante.celular_referencia,
            parentesco_referencia: data.estudiante.parentesco_referencia,
            lengua_materna: data.estudiante.lengua_materna,

            // Pago
            condicion: data.pago.condicion,
            nro_recibo: data.pago.nro_recibo,
            aporte: data.pago.aporte,
            anio_egreso: data.estudiante.anio_egreso
        };

        nameGrupo.value = data.grupo_nombre || '';
        idGrupo.value = data.id_grupo;
        matriculaId.value = idMatricula;
        isEditing.value = true;

        showToast("Datos cargados", "success");
    } catch (e) {
        showToast("No se pudieron cargar los datos.", "error");
    } finally {
        isLoading.value = false;
    }
};


// guardar cambios
const onSubmit = async () => {
    const isValid = await validate();
    if (!isValid) {
        showToast("Complete los campos obligatorios.", "error");
        return;
    }

    const response = await updateMatricula(matriculaId.value, formData.value);

    if (response?.matricula?.id) {
        showToast(`Matrícula actualizada. para ${response?.matricula?.nombre_completo}`, 'success');

        router.replace({ name: 'matricula.grupo.alumnos', params: { id: response?.matricula?.idGrupo } });

    } else {
        showToast('Error al guardar.', 'error');
    }
};

// cargar automáticamente desde la ruta
onMounted(() => {
    if (route.params.id) {
        editarEstudiante(route.params.id);
    }
});
</script>

<template>
    <div class="p-2 bg-white dark:bg-gray-900/50 font-inter">
        <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Editar Datos del Estudiante
        </h4>

        <div v-if="isLoading"
            class="flex justify-center items-center min-h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <p class="text-gray-500 dark:text-gray-400 text-lg">
                Cargando datos del formulario...
            </p>
        </div>

        <div v-else>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-2 min-h-[350px]">

                <Step2 v-model="formData" :errors="formErrors" :edit="isEditing" />
                <hr class="my-4 border-gray-300" />
                <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    Editar Datos de pago
                </h4>

                <Step3 v-model="formData" :nameGrupo="nameGrupo" :edit="isEditing" />

            </div>

            <div class="flex justify-end gap-3 mt-4">


                <Button slotted @click="router.replace({ name: 'matricula.grupo.alumnos', params: { id: idGrupo } })"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 ">
                    Cancelar
                </Button>


                <!-- Botón Guardar Cambios -->
                <Button @click="onSubmit" :loading="saving" title="Guardar Cambios" />
            </div>

        </div>
    </div>
</template>
