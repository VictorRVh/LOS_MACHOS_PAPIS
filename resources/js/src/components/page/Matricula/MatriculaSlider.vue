<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import useProgramaStore from '../../../store/Programa/useProgramaStatusStore';

import Step1 from './Steps/Step1.vue';
import Step2 from './Steps/Step2.vue';
import Step3 from './Steps/Step3.vue';
import Button from '../../ui/Button.vue';
import * as yup from "yup";

const router = useRouter();
const { showToast } = useModalToast();
const { store, saving } = useHttpRequest('/matricula');
const programaStore = useProgramaStore();

const isLoading = ref(true);
const currentStep = ref(1);
const nameGrupo = ref("");

// Formulario
const formData = reactive({
    id_programa: null,
    id_especialidad: null,
    id_grupo: null,
    convenio: '',
    duracion: '',
    horas: '',
    turno: '',
    seccion: '',
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
    otro_operador: '',
    equipo_clases: [],
    discapacidad: '',
    tipo_discapacidad: '',
    celular_referencia: '',
    parentesco_referencia: '',
    lengua_materna: '',
    condicion: 'G | Gratuito',
    nro_recibo: '',
    aporte: '',
    anio_egreso: ''
});

// Errores por paso
const stepErrors = reactive({
    1: {},
    2: {},
    3: {}
});

// Cargar programas
onMounted(async () => {
    try {
        await programaStore.loadPrograma();
    } catch (error) {
        showToast("No se pudieron cargar los datos necesarios.", "error");
    } finally {
        isLoading.value = false;
    }
});

// Esquemas Yup
const stepSchemas = {
    1: yup.object({
        id_programa: yup.string().required('Debe seleccionar un programa'),
        id_especialidad: yup.string().required('Debe seleccionar una especialidad'),
        id_grupo: yup.string().required('Debe seleccionar un grupo'),
    }),
    2: yup.object({
        tipo_documento: yup.string().required('Tipo de documento es requerido'),
        nro_documento: yup.string().required('N° de documento es requerido'),
        apellido_paterno: yup.string().required('Apellido paterno es requerido'),
        apellido_materno: yup.string().required('Apellido materno es requerido'),
        nombre: yup.string().required('El nombre es requerido'),
        sexo: yup.string().required('El sexo es requerido'),
        fecha_nacimiento: yup.date()
            .required('Fecha de nacimiento es requerida')
            .max(new Date(new Date().setFullYear(new Date().getFullYear() - 12)), 'El estudiante debe ser mayor de 12 años')
            .min(new Date(new Date().setFullYear(new Date().getFullYear() - 100)), 'La edad no puede ser mayor a 100 años'),
        celular_personal: yup
            .string()
            .required('Celular es requerido')
            .matches(/^\d{9}$/, 'El celular debe tener 9 números'),
              celular_referencia: yup
            .string()
            .notRequired()
            .matches(/^\d{9}$/, 'El celular debe tener 9 números'),
        correo_electronico: yup.string().email('Debe ser un correo válido').notRequired(),
        direccion_residencia: yup.string().required('La dirección es requerida'),
        estado_civil: yup.string().required('Estado civil es requerido'),
    }),
    3: yup.object({})
};

// Validar paso actual
const validateCurrentStep = async () => {
    const schema = stepSchemas[currentStep.value];
    console.log("errroes: ", schema)
    try {
        await schema.validate(formData, { abortEarly: false });
        stepErrors[currentStep.value] = {}; // limpiar errores si todo ok
        return true;
    } catch (err) {
        const errors = {};
        err.inner.forEach(e => errors[e.path] = e.message);
        stepErrors[currentStep.value] = errors;
        return false;
    }
};

// Navegación entre pasos
const nextStep = async () => {
    const isValid = await validateCurrentStep();
    if (!isValid) {
        showToast("Por favor complete todos los campos obligatorios.", "error");
        return;
    }
    if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// Enviar formulario
const onSubmit = async () => {
    const isValid = await validateCurrentStep();
    if (!isValid) {
        showToast("Faltan datos por completar.", "error");
        return;
    }
    const response = await store(formData);
    if (response.data?.matricula?.id) {
        showToast('¡Matrícula realizada con éxito!', 'success');
        router.push({ name: 'matricula.grupo.alumnos', params: { id: formData.id_grupo } });
    } else {
        showToast('Hubo un error al procesar la matrícula.', 'error');
    }
};
</script>

<template>
    <div class="p-2 bg-white dark:bg-gray-900/50 font-inter">

        <div v-if="isLoading"
            class="flex justify-center items-center min-h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <p class="text-gray-500 dark:text-gray-400 text-lg">Cargando datos del formulario...</p>
        </div>

        <div v-else>
            <ol class="flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400 sm:text-base">
                <li class="flex items-center">
                    <span :class="currentStep >= 1 ? 'text-blue-600 dark:text-blue-500' : ''">1 Datos Académicos</span>
                    <span v-if="currentStep < 3" class="mx-2">»</span>
                </li>
                <li class="flex items-center">
                    <span :class="currentStep >= 2 ? 'text-blue-600 dark:text-blue-500' : ''">2 Datos del
                        Estudiante</span>
                    <span v-if="currentStep < 3" class="mx-2">»</span>
                </li>
                <li class="flex items-center">
                    <span :class="currentStep >= 3 ? 'text-blue-600 dark:text-blue-500' : ''">3 Pago y
                        Confirmación</span>
                </li>
            </ol>


            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-2 min-h-[350px]">
                <Step1 v-show="currentStep === 1" v-model="formData" :programas="programaStore.programa.programas"
                    :nameGrupo="nameGrupo" @cambiarVariable="nameGrupo = $event" :errors="stepErrors[1]" />
                <Step2 v-show="currentStep === 2" v-model="formData" :errors="stepErrors[2]" />
                <Step3 v-show="currentStep === 3" v-model="formData" :nameGrupo="nameGrupo" />
            </div>

            <div class="flex justify-between mt-2">
                <Button v-if="currentStep > 1" variant="outline" @click="prevStep" title="Anterior" />
                <div>
                    <Button v-if="currentStep < 3" @click="nextStep" title="Siguiente" />
                    <Button v-if="currentStep === 3" @click="onSubmit" :loading="saving"
                        title="Confirmar y Matricular" />
                </div>
            </div>
        </div>
    </div>
</template>
