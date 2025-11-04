<script setup>
import { computed, ref, watch } from 'vue';
import FormInput from '../../../components/ui/FormInput.vue';
import FormLabelError from '../../../components/ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import ubigeo from '../../../utils/ubigeo';
import BaseSelect from '../../../components/ui/BaseSelect.vue';
import useModalToast from '../../../composables/useModalToast';
import useHttpRequest from '../../../composables/useHttpRequest';


const { showConfirmModal, showToast } = useModalToast();
const { store: busquedaDni, saving, update: updateModulo, updating } = useHttpRequest(
    "/buscar-documento"
);

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
const opcionesGradoInstruccion = ['Primaria incompleta', 'Primaria completa', 'Secundaria incompleta', 'Secundaria completa', 'Superior incompleta', 'Superior incompleta'];
const opcionesLenguaMaterna = ['Castellano', 'Quechua', 'Aymara', 'Ashaninka', 'Awajun', 'Otros'];
const opcionesEquiposVirtuales = ['Laptop', 'Computadora', 'Tablet', 'Celular'];
const opcionesSiNo = ["Si", "No"];
const opcionesDiscapacidad = ["Discapacidad intelectual - Retardo mental leve", "Transtorno del espectro autista", "Discapacidad intelectual - Retardo mental moderado", "Discapacidad visual - Baja visión", "Discapacidad visual - Ceguera", "Discapacidad auditiva - Hipoacusia", "Discapacidad auditiva - Sordera total", "Otros"];

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

        const response = await busquedaDni({
            tipo_documento: tipo,
            dni: numero,
        });

        console.log('busqueda dni', response)

        if (response.error) {
            showToast(response.error);
            return;
        }

        const d = response.data ?? data;

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

</script>

<template>
    <div class="relative">

        <transition name="fade">
            <div v-if="saving"
                class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 flex flex-col items-center justify-center z-50 backdrop-blur-sm">
                <svg class="animate-spin h-10 w-10 text-cetpro mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <p class="text-gray-700 dark:text-gray-200 font-semibold text-lg">
                    Buscando documento...
                </p>
            </div>
        </transition>

        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <UserCircleIcon class="h-6 w-6" />
            DATOS PERSONALES DEL ESTUDIANTE
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">

            <!-- Tipo y número de documento -->
            <div class="flex gap-4 items-end col-span-2 lg:col-span-1">
                <FormLabelError label="Tipo Doc." required class="flex-1">
                    <BaseSelect v-model="formData.tipo_documento" :options="['DNI', 'CARNET EXT.']" />
                </FormLabelError>

                <div class="flex items-end gap-2 flex-1">
                    <FormInput v-model="formData.nro_documento" label="Nro Doc. *" />
                    <button type="button" @click="buscarDNI" :disabled="saving"
                        class="px-4 py-2.5 bg-cetpro text-white rounded-lg hover:bg-cetpro-light transition flex items-center justify-center disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="!saving">Buscar</span>
                        <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Otros campos -->
            <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno"
                :error-message="errors.apellido_paterno" required />
            <FormInput v-model="formData.apellido_materno" label="Apellido Materno"
                :error-message="errors.apellido_materno" required />
            <FormInput v-model="formData.nombre" label="Nombres" :error-message="errors.nombre" />

            <FormLabelError label="Sexo" :error-message="errors.sexo">
                <v-select v-model="formData.sexo" :options="opcionesSexo" label="name" :reduce="opcion => opcion.value"
                    placeholder="Seleccione sexo" :clearable="false" />
            </FormLabelError>

            <FormLabelError label="Fecha de Nacimiento" :error-message="errors.fecha_nacimiento">
                <FormInput v-model="formData.fecha_nacimiento" type="date" />
            </FormLabelError>

            <FormLabelError label="Departamento de Nacimiento" :error-message="errors.departamento_nacimiento">
                <v-select v-model="formData.departamento_nacimiento" :options="departamentos"
                    placeholder="Buscar departamento..." />
            </FormLabelError>

            <FormLabelError label="Provincia de Nacimiento" :error-message="errors.provincia_nacimiento">
                <v-select v-model="formData.provincia_nacimiento" :options="provincias"
                    placeholder="Buscar provincia..." :disabled="!formData.departamento_nacimiento" />
            </FormLabelError>

            <FormLabelError label="Distrito de Nacimiento" :error-message="errors.distrito_nacimiento">
                <v-select v-model="formData.distrito_nacimiento" :options="distritos" placeholder="Buscar distrito..."
                    :disabled="!formData.provincia_nacimiento" />
            </FormLabelError>

            <FormInput v-if="mostrarOtroDistrito" v-model="formData.lugar_nacimiento" label="Especifique otro lugar" />

            <!-- Información adicional -->
            <FormInput v-model="formData.celular" label="Celular" :error-message="errors.celular" maxlength="9" />
            <FormInput v-model="formData.correo_electronico" label="Correo Electrónico (Opcional)" type="email" />
            <FormInput v-model="formData.direccion_residencia" label="Dirección de residencia *"
                :error-message="errors.direccion_residencia" />

            <FormLabelError label="Estado Civil" :error-message="errors.estado_civil">
                <v-select v-model="formData.estado_civil" :options="opcionesEstadoCivil"
                    placeholder="Seleccione estado" />
            </FormLabelError>

            <FormLabelError label="Grado de instrucción *" :error-message="errors.grado_instruccion">
                <v-select v-model="formData.grado_instruccion" :options="opcionesGradoInstruccion"
                    placeholder="Seleccione grado" />
            </FormLabelError>

            <FormInput v-model="formData.pais_nacimiento" label="País de Nacimiento" />
            <FormInput v-model="formData.anio_egreso" label="Año de egreso (colegio)" />

            <FormLabelError label="Lengua materna" :error-message="errors.lengua_materna">
                <v-select v-model="formData.lengua_materna" :options="opcionesLenguaMaterna"
                    placeholder="Seleccione lengua" />
            </FormLabelError>

            <FormLabelError label="¿Trabaja?" :error-message="errors.trabaja">
                <v-select v-model="formData.trabaja" :options="opcionesSiNo" placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.trabaja === 'Si'" v-model="formData.detalle_trabajo"
                label="Especifique ocupación o centro laboral" />

            <FormLabelError label="¿Tiene carga familiar?" :error-message="errors.carga_familiar">
                <v-select v-model="formData.carga_familiar" :options="opcionesSiNo"
                    placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.carga_familiar === 'Si'" v-model="formData.detalle_carga_familiar"
                label="Nro de personas a cargo" />

            <FormLabelError label="¿Tiene internet en casa?" :error-message="errors.internet_casa">
                <v-select v-model="formData.internet_casa" :options="opcionesSiNo" placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.internet_casa === 'Si'" v-model="formData.tipo_internet"
                label="Tipo de conexión (Wifi, datos móviles, etc.)" />

            <FormLabelError label="Equipos virtuales en casa" :error-message="errors.equipos_virtuales">
                <v-select v-model="formData.equipos_virtuales" :options="opcionesEquiposVirtuales"
                    placeholder="Seleccione los equipos disponibles" multiple :close-on-select="false" />
            </FormLabelError>

            <FormLabelError label="¿Tiene discapacidad?" :error-message="errors.discapacidad">
                <v-select v-model="formData.discapacidad" :options="opcionesSiNo" placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormLabelError v-if="formData.discapacidad === 'Si'" label="Tipo de discapacidad"
                :error-message="errors.tipo_discapacidad">
                <v-select v-model="formData.tipo_discapacidad" :options="opcionesDiscapacidad"
                    placeholder="Seleccione la discapacidad" />
            </FormLabelError>

            <FormInput v-model="formData.celular_referencia" label="Celular de referencia" />
            <FormInput v-model="formData.parentesco_referencia" label="Parentesco con referencia" />

        </div>

    </div>
</template>