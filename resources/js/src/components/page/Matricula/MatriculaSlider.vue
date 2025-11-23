<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import useProgramaStore from '../../../store/Programa//useProgramaStatusStore';


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
const formErrors = ref({});

const formData = ref({
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

onMounted(async () => {
    try {
        await Promise.all([
            programaStore.loadPrograma(),

        ]);
    } catch (error) {
        showToast("No se pudieron cargar los datos necesarios.", "error");
    } finally {
        isLoading.value = false;
    }
});

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
        celular_personal: yup.string().required('Celular es requerido'),
        correo_electronio: yup.string().email('Debe ser un correo válido').notRequired(),
        direccion_residencia: yup.string().required('La dirección es requerida'),
        estado_civil: yup.string().required('Estado civil es requerido'),
    }),
    3: yup.object({}),
};

const validateCurrentStep = async () => {
    const schema = stepSchemas[currentStep.value];

    try {
        await schema.validate(formData.value, { abortEarly: false });
        formErrors.value = {}; // limpiar errores si todo va bien
        return true;
    } catch (err) {
        const errors = {};
        err.inner.forEach(e => {
            errors[e.path] = e.message;
        });
        formErrors.value = errors;
        return false;
    }
};


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

const onSubmit = async () => {

    // console.log('llegada de datos de matricula: ', formData.value)

    const isValid = await validateCurrentStep();
    if (!isValid) {
        showToast("Faltan datos por completar.", "error");
        return;
    }

    const response = await store(formData.value);
    if (response.data.matricula.id) {
        showToast('¡Matrícula realizada con éxito!', 'success');
        router.push({ name: 'matricula.grupo.alumnos', params: { id: formData.value.id_grupo } });
    } else {
        showToast('Hubo un error al procesar la matrícula.', 'error');
    }
};
</script>

<template>
    <div class="p-2 bg-white dark:bg-gray-900/50 font-inter">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 ">Nueva Matrícula de Estudiante</h2>

        <div v-if="isLoading"
            class="flex justify-center items-center min-h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <p class="text-gray-500 dark:text-gray-400 text-lg">Cargando datos del formulario...</p>
        </div>

        <div v-else>
            <ol
                class="flex items-center w-full p-3 mb-2 space-x-2 text-sm font-medium text-center text-gray-500 bg-white border border-gray-200 rounded-lg shadow-sm dark:text-gray-400 sm:text-base dark:bg-gray-800 dark:border-gray-700 sm:p-4 sm:space-x-4">
                <li class="flex items-center" :class="{ 'text-blue-600 dark:text-blue-500': currentStep >= 1 }">
                    <span class="flex items-center justify-center w-5 h-5 me-2 text-xs border rounded-full shrink-0"
                        :class="{ 'border-blue-600 dark:border-blue-500': currentStep >= 1, 'border-gray-500 dark:border-gray-400': currentStep < 1 }">1</span>
                    Datos <span class="hidden sm:inline-flex sm:ms-2">Académicos</span>
                    <svg class="w-3 h-3 ms-2 sm:ms-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 12 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m7 9 4-4-4-4M1 9l4-4-4-4" />
                    </svg>
                </li>
                <li class="flex items-center" :class="{ 'text-blue-600 dark:text-blue-500': currentStep >= 2 }">
                    <span class="flex items-center justify-center w-5 h-5 me-2 text-xs border rounded-full shrink-0"
                        :class="{ 'border-blue-600 dark:border-blue-500': currentStep >= 2, 'border-gray-500 dark:border-gray-400': currentStep < 2 }">2</span>
                    Datos del <span class="hidden sm:inline-flex sm:ms-2">Estudiante</span>
                    <svg class="w-3 h-3 ms-2 sm:ms-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 12 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m7 9 4-4-4-4M1 9l4-4-4-4" />
                    </svg>
                </li>
                <li class="flex items-center" :class="{ 'text-blue-600 dark:text-blue-500': currentStep >= 3 }">
                    <span class="flex items-center justify-center w-5 h-5 me-2 text-xs border rounded-full shrink-0"
                        :class="{ 'border-blue-600 dark:border-blue-500': currentStep >= 3, 'border-gray-500 dark:border-gray-400': currentStep < 3 }">3</span>
                    Pago y <span class="hidden sm:inline-flex sm:ms-2">Confirmación</span>
                </li>
            </ol>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-2 min-h-[350px]">

                <Step1 v-show="currentStep === 1" v-model="formData" :programas="programaStore.programa.programas"
                    :nameGrupo="nameGrupo" @cambiarVariable="nameGrupo = $event" :errors="formErrors" />
                <Step2 v-show="currentStep === 2" v-model="formData" :errors="formErrors" />
                <Step3 v-show="currentStep === 3" v-model="formData" :nameGrupo="nameGrupo" />
            </div>

            <div class="flex justify-between ">
                <div>
                    <Button v-if="currentStep > 1" variant="outline" @click="prevStep" title="Anterior" />
                </div>
                <div>
                    <Button v-if="currentStep < 3" @click="nextStep" title="Siguiente" />
                    <Button v-if="currentStep === 3" @click="onSubmit" :loading="saving"
                        title="Confirmar y Matricular" />
                </div>
            </div>
        </div>
    </div>
</template>