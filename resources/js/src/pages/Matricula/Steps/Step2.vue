<script setup>
import { computed, ref, watch } from 'vue';
import FormInput from '../../../components/ui/FormInput.vue';
import FormLabelError from '../../../components/ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import ubigeo from '../../../utils/ubigeo';
import BaseSelect from '../../../components/ui/BaseSelect.vue';
import axios from 'axios';
import useModalToast from '../../../composables/useModalToast';


const { showConfirmModal, showToast } = useModalToast();

const props = defineProps({
    modelValue: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
const emit = defineEmits(['update:modelValue']);

const formData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const opcionesSexo = [{ name: 'Masculino', value: "M" }, { name: 'Femenino', value: "F" }, { name: 'Otro', value: "O" }];
const opcionesEstadoCivil = ['SOLTERO(A)', 'CASADO(A)', 'VIUDO(A)', 'DIVORCIADO(A)', 'CONVIVIENTE'];

const departamentos = ref(ubigeo.map(dep => dep.departamento));
const provincias = ref([]);
const distritos = ref([]);
const mostrarOtroDistrito = ref(false);


const buscarDNI = async () => {
    const tipo = formData.value.tipo_documento;
    const numero = formData.value.nro_documento;

    if (!tipo) {
        showToast("Debe seleccionar un tipo de documento");
        return;
    }

    if (!numero) {
        showToast("Debe ingresar un número de documento");
        return;
    }

    if (tipo === "DNI" && numero.length !== 8) {
        showToast("El DNI debe tener 8 dígitos");
        return;
    }

    if (tipo === "CARNET EXT." && numero.length < 9) {
        showToast("El Carnet de Extranjería debe tener al menos 9 caracteres");
        return;
    }

    try {
        const { data } = await axios.post("buscar-documento", {
            tipo_documento: tipo,
            dni: numero,
        });

        if (data.error) {
            showToast(data.error);
            return;
        }

        const d = data.data ?? data;

        // Para comprobar si los datos vienen de FACTILIZA
        const esFactiliza = !!d.nombres;

        formData.value.apellido_paterno = d.apellido_paterno ?? "";
        formData.value.apellido_materno = d.apellido_materno ?? "";
        formData.value.nombre = esFactiliza ? d.nombres ?? "" : d.nombre ?? "";
        formData.value.direccion_residencia = esFactiliza
            ? d.direccion ?? ""
            : d.direccion_residencia ?? "";
        formData.value.departamento_nacimiento = esFactiliza
            ? d.departamento ?? ""
            : d.departamento_nacimiento ?? "";
        formData.value.provincia_nacimiento = esFactiliza
            ? d.provincia ?? ""
            : d.provincia_nacimiento ?? "";
        formData.value.distrito_nacimiento = esFactiliza
            ? d.distrito ?? ""
            : d.distrito_nacimiento ?? "";
        formData.value.pais_nacimiento = d.pais_nacimiento ?? "PERÚ";

        showToast("Datos encontrados correctamente");
    } catch (error) {
        console.error(error);
        showToast("Error al buscar el documento");
    }
};


// watch(() => formData.value.departamento_nacimiento, (newDep) => {
//     formData.value.provincia_nacimiento = null;
//     formData.value.distrito_nacimiento = null;
//     provincias.value = [];
//     distritos.value = [];
//     mostrarOtroDistrito.value = false;
//     if (newDep) {
//         const depData = ubigeo.find(d => d.departamento === newDep);
//         provincias.value = depData ? depData.provincias.map(p => p.provincia) : [];
//     }
// });

// watch(() => formData.value.provincia_nacimiento, (newProv) => {
//     formData.value.distrito_nacimiento = null;
//     distritos.value = [];
//     mostrarOtroDistrito.value = false;
//     if (newProv && formData.value.departamento_nacimiento) {
//         const depData = ubigeo.find(d => d.departamento === formData.value.departamento_nacimiento);
//         const provData = depData ? depData.provincias.find(p => p.provincia === newProv) : null;
//         distritos.value = provData ? [...provData.distritos, 'OTRO'] : [];
//     }
// });

// watch(() => formData.value.distrito_nacimiento, (newDist) => {
//     mostrarOtroDistrito.value = newDist === 'OTRO';
//     if(newDist !== 'OTRO') {
//         formData.value.lugar_nacimiento = '';
//     }
// });

</script>

<template>
    <div>
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <UserCircleIcon class="h-6 w-6" />
            DATOS PERSONALES DEL ESTUDIANTE
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <div class="grid grid-cols-2 gap-4">
                <FormLabelError label="Tipo Doc. " required>
                    <BaseSelect v-model="formData.tipo_documento" :options="['DNI', 'CARNET EXT.']" class="W-28" />
                </FormLabelError>

                <div class="flex gap-2">
                    <FormInput v-model="formData.nro_documento" label="Nro Doc. *" />
                    <button type="button" @click="buscarDNI"
                        class="px-2 py-0 bg-cetpro text-white rounded-lg hover:bg-cetpro-light transition">
                        Buscar
                    </button>
                </div>
            </div>
            <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno *"
                :error-message="errors.apellido_paterno" />
            <FormInput v-model="formData.apellido_materno" label="Apellido Materno *"
                :error-message="errors.apellido_materno" />
            <FormInput v-model="formData.nombre" label="Nombres *" :error-message="errors.nombre" />

            <FormLabelError label="Sexo *" :error-message="errors.sexo">
                <v-select v-model="formData.sexo" :options="opcionesSexo" label="name" :reduce="opcion => opcion.value"
                    placeholder="Seleccione sexo" :clearable="false" />
            </FormLabelError>


            <FormLabelError label="Fecha de Nacimiento *" :error-message="errors.fecha_nacimiento">
                <FormInput v-model="formData.fecha_nacimiento" type="date" />
            </FormLabelError>

            <FormLabelError label="Departamento de Nacimiento *" :error-message="errors.departamento_nacimiento">
                <v-select v-model="formData.departamento_nacimiento" :options="departamentos"
                    placeholder="Buscar departamento..." />
            </FormLabelError>

            <FormLabelError label="Provincia de Nacimiento *" :error-message="errors.provincia_nacimiento">
                <v-select v-model="formData.provincia_nacimiento" :options="provincias"
                    placeholder="Buscar provincia..." :disabled="!formData.departamento_nacimiento" />
            </FormLabelError>

            <FormLabelError label="Distrito de Nacimiento *" :error-message="errors.distrito_nacimiento">
                <v-select v-model="formData.distrito_nacimiento" :options="distritos" placeholder="Buscar distrito..."
                    :disabled="!formData.provincia_nacimiento" />
            </FormLabelError>

            <FormInput v-if="mostrarOtroDistrito" v-model="formData.lugar_nacimiento" label="Especifique otro lugar" />

            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                <FormInput v-model="formData.celular" label="Celular *" :error-message="errors.celular" maxlength="9" />
                <FormInput v-model="formData.correo" label="Correo Electrónico (Opcional)" type="email" />
                <FormInput v-model="formData.direccion_residencia" label="Dirección de Residencia *"
                    :error-message="errors.direccion_residencia" />

                <FormLabelError label="Estado Civil *" :error-message="errors.estado_civil">
                    <v-select v-model="formData.estado_civil" :options="opcionesEstadoCivil"
                        placeholder="Seleccione estado" />
                </FormLabelError>

                <FormInput v-model="formData.grado_instruccion" label="Grado de Instrucción" />
                <FormInput v-model="formData.pais_nacimiento" label="País de Nacimiento" />
            </div>
        </div>
    </div>
</template>