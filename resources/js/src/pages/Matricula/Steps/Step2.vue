<script setup>
import { computed, ref, watch } from 'vue';
import FormInput from '../../../components/ui/FormInput.vue';
import FormLabelError from '../../../components/ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import ubigeo from '../../../utils/ubigeo';

const props = defineProps({
    modelValue: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
const emit = defineEmits(['update:modelValue']);

const formData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const opcionesSexo = ['Masculino', 'Femenino'];
const opcionesEstadoCivil = ['SOLTERO(A)', 'CASADO(A)', 'VIUDO(A)', 'DIVORCIADO(A)', 'CONVIVIENTE'];

const departamentos = ref(ubigeo.map(dep => dep.departamento));
const provincias = ref([]);
const distritos = ref([]);
const mostrarOtroDistrito = ref(false);

watch(() => formData.value.departamento_nacimiento, (newDep) => {
    formData.value.provincia_nacimiento = null;
    formData.value.distrito_nacimiento = null;
    provincias.value = [];
    distritos.value = [];
    mostrarOtroDistrito.value = false;
    if (newDep) {
        const depData = ubigeo.find(d => d.departamento === newDep);
        provincias.value = depData ? depData.provincias.map(p => p.provincia) : [];
    }
});

watch(() => formData.value.provincia_nacimiento, (newProv) => {
    formData.value.distrito_nacimiento = null;
    distritos.value = [];
    mostrarOtroDistrito.value = false;
    if (newProv && formData.value.departamento_nacimiento) {
        const depData = ubigeo.find(d => d.departamento === formData.value.departamento_nacimiento);
        const provData = depData ? depData.provincias.find(p => p.provincia === newProv) : null;
        distritos.value = provData ? [...provData.distritos, 'OTRO'] : [];
    }
});

watch(() => formData.value.distrito_nacimiento, (newDist) => {
    mostrarOtroDistrito.value = newDist === 'OTRO';
    if(newDist !== 'OTRO') {
        formData.value.lugar_nacimiento = '';
    }
});
</script>

<template>
    <div>
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <UserCircleIcon class="h-6 w-6" />
            DATOS PERSONALES DEL ESTUDIANTE
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <FormInput v-model="formData.nro_documento" label="N° DNI *" :error-message="errors.nro_documento" maxlength="8" />
            <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno *" :error-message="errors.apellido_paterno" />
            <FormInput v-model="formData.apellido_materno" label="Apellido Materno *" :error-message="errors.apellido_materno" />
            <FormInput v-model="formData.nombre" label="Nombres *" :error-message="errors.nombre" />
            
            <FormLabelError label="Sexo *" :error-message="errors.sexo">
                <v-select v-model="formData.sexo" :options="opcionesSexo" placeholder="Seleccione sexo" :clearable="false"/>
            </FormLabelError>

            <FormLabelError label="Fecha de Nacimiento *" :error-message="errors.fecha_nacimiento">
                 <FormInput v-model="formData.fecha_nacimiento" type="date" />
            </FormLabelError>

            <FormLabelError label="Departamento de Nacimiento *" :error-message="errors.departamento_nacimiento">
                <v-select v-model="formData.departamento_nacimiento" :options="departamentos" placeholder="Buscar departamento..." />
            </FormLabelError>

            <FormLabelError label="Provincia de Nacimiento *" :error-message="errors.provincia_nacimiento">
                <v-select v-model="formData.provincia_nacimiento" :options="provincias" placeholder="Buscar provincia..." :disabled="!formData.departamento_nacimiento" />
            </FormLabelError>

            <FormLabelError label="Distrito de Nacimiento *" :error-message="errors.distrito_nacimiento">
                <v-select v-model="formData.distrito_nacimiento" :options="distritos" placeholder="Buscar distrito..." :disabled="!formData.provincia_nacimiento" />
            </FormLabelError>

            <FormInput v-if="mostrarOtroDistrito" v-model="formData.lugar_nacimiento" label="Especifique otro lugar" />
            
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                 <FormInput v-model="formData.celular" label="Celular *" :error-message="errors.celular" maxlength="9" />
                <FormInput v-model="formData.correo" label="Correo Electrónico (Opcional)" type="email" />
                <FormInput v-model="formData.direccion_residencia" label="Dirección de Residencia *" :error-message="errors.direccion_residencia" />

                <FormLabelError label="Estado Civil *" :error-message="errors.estado_civil">
                    <v-select v-model="formData.estado_civil" :options="opcionesEstadoCivil" placeholder="Seleccione estado" />
                </FormLabelError>

                <FormInput v-model="formData.grado_instruccion" label="Grado de Instrucción" />
                <FormInput v-model="formData.pais_nacimiento" label="País de Nacimiento" />
            </div>
        </div>
    </div>
</template>