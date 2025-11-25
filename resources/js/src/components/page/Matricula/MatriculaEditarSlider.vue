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
const { saving, update: updateModulo } = useHttpRequest('/matricula');

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
    tipo_documento: yup.string().required(),
    nro_documento: yup.string().required(),
    apellido_paterno: yup.string().required(),
    apellido_materno: yup.string().required(),
    nombre: yup.string().required(),
    sexo: yup.string().required(),
    fecha_nacimiento: yup.date().required(),
    celular_personal: yup.string().required(),
    direccion_residencia: yup.string().required(),
    estado_civil: yup.string().required(),
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

        formData.value = { ...formData.value, ...data };
        nameGrupo.value = data.grupo_nombre || '';
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

    const response = await updateModulo(`/matricula/${matriculaId.value}`, formData.value);

    if (response.data?.matricula?.id) {
        showToast('Matrícula actualizada.', 'success');
        router.push({ name: 'matricula.grupo.alumnos', params: { id: response.data.matricula.id_grupo } });
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
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Editar Datos del Estudiante
        </h2>

        <div v-if="isLoading"
            class="flex justify-center items-center min-h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <p class="text-gray-500 dark:text-gray-400 text-lg">
                Cargando datos del formulario...
            </p>
        </div>

        <div v-else>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-2 min-h-[350px]">

                <Step2 v-model="formData" :errors="formErrors" />
                <Step3 v-model="formData" :nameGrupo="nameGrupo" />

            </div>

            <div class="flex justify-end mt-4">
                <Button @click="onSubmit" :loading="saving" title="Guardar Cambios" />
            </div>
        </div>
    </div>
</template>
