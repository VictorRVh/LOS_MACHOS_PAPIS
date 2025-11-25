<script setup>
import { computed, ref, watch } from 'vue';
import FormInput from '../../../ui/FormInput.vue';
import FormLabelError from '../../../ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import ubigeo from '../../../../utils/ubigeo';
import BaseSelect from '../../../ui/BaseSelect.vue';
import useModalToast from '../../../../composables/useModalToast';
import useHttpRequest from '../../../../composables/useHttpRequest';

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

// ---------- OPCIONES ----------
const opcionesSexo = [
    { name: 'Masculino', value: "M" },
    { name: 'Femenino', value: "F" },
    { name: 'Otro', value: "O" }
];

const opcionesEstadoCivil = [
    'SOLTERO(A)', 'CASADO(A)', 'VIUDO(A)', 'DIVORCIADO(A)', 'CONVIVIENTE'
];

const opcionesGradoInstruccion = [
    'Primaria incompleta', 'Primaria completa',
    'Secundaria incompleta', 'Secundaria completa',
    'Superior incompleta', 'Superior completa'
];

const opcionesLenguaMaterna = [
    'Castellano', 'Quechua', 'Aymara', 'Ashaninka', 'Awajun', 'Otros'
];

const opcionesEquiposVirtuales = ['Laptop', 'Computadora', 'Tablet', 'Celular'];
const opcionesSiNo = ["Si", "No"];
const opcionesDiscapacidad = [
    "Discapacidad intelectual - Retardo mental leve",
    "Transtorno del espectro autista",
    "Discapacidad intelectual - Retardo mental moderado",
    "Discapacidad visual - Baja visión",
    "Discapacidad visual - Ceguera",
    "Discapacidad auditiva - Hipoacusia",
    "Discapacidad auditiva - Sordera total",
    "Otros"
];

// ---------- UBIGEO ----------
const departamentos = ref(ubigeo.map(dep => dep.departamento));
const provincias = ref([]);
const distritos = ref([]);
const mostrarOtroDistrito = ref(false);

// controla watchers
const watchActive = ref(true);

// ---------- NORMALIZAR ----------
function normalize(str) {
    return str
        ?.normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/ñ/gi, "n")
        .replace(/[^a-zA-Z ]/g, "")
        .toUpperCase()
        .trim();
}

const autoParseJson = (value, fallback = []) => {
    try {
        return typeof value === "string" ? JSON.parse(value) : (value ?? fallback);
    } catch {
        return fallback;
    }
};

// ---------- ADAPTAR RESPUESTA ----------
const adaptDniResponse = d => ({
    ...d,
    departamento_nacimiento: d.departamento_nacimiento?.toUpperCase() ?? "",
    provincia_nacimiento: d.provincia_nacimiento?.toUpperCase() ?? "",
    distrito_nacimiento: d.distrito_nacimiento?.toUpperCase() ?? "",
    celular: d.celular_personal ?? "",
    equipos_virtuales: autoParseJson(d.equipos_virtuales, []),
});

// ---------- BUSCAR DNI ----------
const buscarDNI = async () => {
    const { tipo_documento: tipo, nro_documento: numero } = formData.value;

    try {
        const response = await busquedaDni({
            tipo_documento: tipo,
            dni: numero,
        });

        if (response.error) {
            showToast("Error al buscar documento, llene manual.");
            watchActive.value = true;
            return;
        }

        if (!response.data) {
            showToast("Sin datos, llene manual.");
            watchActive.value = true;
            return;
        }

        watchActive.value = false;

        const d = adaptDniResponse(response.data);

        Object.assign(formData.value, { ...formData.value, ...d });

        actualizarListas();

        watchActive.value = true;

        showToast("Datos encontrados.");
    } catch (e) {
        showToast("Error inesperado.");
        watchActive.value = true;
    }
};

// ---------- SOLO LLENA LISTAS (NO AUTOSELECT) ----------
const actualizarListas = () => {
    const depNorm = normalize(formData.value.departamento_nacimiento);
    const provNorm = normalize(formData.value.provincia_nacimiento);

    const depData = ubigeo.find(d => normalize(d.departamento) === depNorm);
    provincias.value = depData ? depData.provincias.map(p => p.provincia) : [];

    const provData = depData?.provincias.find(p => normalize(p.provincia) === provNorm);
    distritos.value = provData ? [...provData.distritos, "OTRO"] : [];
};

// ---------- WATCH DEPARTAMENTO ----------
watch(() => formData.value.departamento_nacimiento, (nuevo) => {
    if (!watchActive.value) return;

    const depNorm = normalize(nuevo);
    const depData = ubigeo.find(d => normalize(d.departamento) === depNorm);

    provincias.value = depData ? depData.provincias.map(p => p.provincia) : [];
    distritos.value = [];
});

// ---------- WATCH PROVINCIA ----------
watch(() => formData.value.provincia_nacimiento, (nuevo) => {
    if (!watchActive.value) return;

    const depNorm = normalize(formData.value.departamento_nacimiento);
    const provNorm = normalize(nuevo);

    const depData = ubigeo.find(d => normalize(d.departamento) === depNorm);
    const provData = depData?.provincias.find(p => normalize(p.provincia) === provNorm);

    distritos.value = provData ? [...provData.distritos, "OTRO"] : [];
});

// ---------- WATCH DISTRITO ----------
watch(() => formData.value.distrito_nacimiento, (nuevo) => {
    if (!watchActive.value) return;

    const distNorm = normalize(nuevo);

    mostrarOtroDistrito.value = distNorm === "OTRO";

    if (distNorm !== "OTRO") {
        formData.value.lugar_nacimiento = "";
    }
});
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
                    <button type="button" @click="buscarDNI" :disabled="saving" class="px-3 py-2 bg-cetpro text-white rounded-lg flex items-center gap-2 
           hover:bg-cetpro-light transition disabled:opacity-70 disabled:cursor-not-allowed">

                        <svg v-if="saving" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        <MagnifyingGlassIcon v-else class="w-5 h-5" />

                    </button>

                </div>
            </div>

            <!-- Otros campos -->
            <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno" :error="errors.apellido_paterno"
                required />
            <FormInput v-model="formData.apellido_materno" label="Apellido Materno" :error="errors.apellido_materno"
                required />
            <FormInput v-model="formData.nombre" label="Nombres" :error="errors.nombre" />

            <FormLabelError label="Sexo" :error="errors.sexo">
                <v-select v-model="formData.sexo" :options="opcionesSexo" label="name" :reduce="opcion => opcion.value"
                    placeholder="Seleccione sexo" :clearable="false" />
            </FormLabelError>

            <FormLabelError label="Fecha de Nacimiento" :error="errors.fecha_nacimiento">
                <FormInput v-model="formData.fecha_nacimiento" type="date" />
            </FormLabelError>

            <FormLabelError label="Departamento de Nacimiento" :error="errors.departamento_nacimiento">
                <v-select v-model="formData.departamento_nacimiento" :options="departamentos"
                    placeholder="Buscar departamento..." />
            </FormLabelError>

            <FormLabelError label="Provincia de Nacimiento" :error="errors.provincia_nacimiento">
                <v-select v-model="formData.provincia_nacimiento" :options="provincias"
                    placeholder="Buscar provincia..." :disabled="!formData.departamento_nacimiento" />
            </FormLabelError>

            <FormLabelError label="Distrito de Nacimiento" :error="errors.distrito_nacimiento">
                <v-select v-model="formData.distrito_nacimiento" :options="distritos" placeholder="Buscar distrito..."
                    :disabled="!formData.provincia_nacimiento" />
            </FormLabelError>

            <FormInput v-if="mostrarOtroDistrito" v-model="formData.lugar_nacimiento" label="Especifique otro lugar" />

            <!-- Información adicional -->
            <FormInput v-model="formData.celular_personal" label="Celular" :error="errors.celular_personal"
                maxlength="9" />
            <FormInput v-model="formData.correo_electronico" label="Correo Electrónico (Opcional)" type="email" />
            <FormInput v-model="formData.direccion_residencia" label="Dirección de residencia *"
                :error="errors.direccion_residencia" />

            <FormLabelError label="Estado Civil" :error="errors.estado_civil">
                <v-select v-model="formData.estado_civil" :options="opcionesEstadoCivil"
                    placeholder="Seleccione estado" />
            </FormLabelError>

            <FormLabelError label="Grado de instrucción *" :error="errors.grado_instruccion">
                <v-select v-model="formData.grado_instruccion" :options="opcionesGradoInstruccion"
                    placeholder="Seleccione grado" />
            </FormLabelError>

            <FormInput v-model="formData.pais_nacimiento" label="País de Nacimiento" />
            <FormInput v-model="formData.anio_egreso" label="Año de egreso (colegio)" />

            <FormLabelError label="Lengua materna" :error="errors.lengua_materna">
                <v-select v-model="formData.lengua_materna" :options="opcionesLenguaMaterna"
                    placeholder="Seleccione lengua" />
            </FormLabelError>

            <FormLabelError label="¿Trabaja?" :error="errors.trabaja">
                <v-select v-model="formData.trabaja" :options="opcionesSiNo" placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.trabaja === 'Si'" v-model="formData.detalle_trabajo"
                label="Especifique ocupación o centro laboral" />

            <FormLabelError label="¿Tiene carga familiar?" :error="errors.carga_familiar">
                <v-select v-model="formData.carga_familiar" :options="opcionesSiNo"
                    placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.carga_familiar === 'Si'" v-model="formData.detalle_carga_familiar"
                label="Nro de personas a cargo" />

            <FormLabelError label="¿Tiene internet en casa?" :error="errors.internet_casa">
                <v-select v-model="formData.internet_casa" :options="opcionesSiNo"
                    placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormInput v-if="formData.internet_casa === 'Si'" v-model="formData.tipo_internet"
                label="Tipo de conexión (Wifi, datos móviles, etc.)" />

            <FormLabelError label="Equipos virtuales en casa" :error="errors.equipos_virtuales">
                <v-select v-model="formData.equipos_virtuales" :options="opcionesEquiposVirtuales"
                    placeholder="Seleccione los equipos disponibles" multiple :close-on-select="false" />
            </FormLabelError>

            <FormLabelError label="¿Tiene discapacidad?" :error="errors.discapacidad">
                <v-select v-model="formData.discapacidad" :options="opcionesSiNo" placeholder="Seleccione una opción" />
            </FormLabelError>

            <FormLabelError v-if="formData.discapacidad === 'Si'" label="Tipo de discapacidad"
                :error="errors.tipo_discapacidad">
                <v-select v-model="formData.tipo_discapacidad" :options="opcionesDiscapacidad"
                    placeholder="Seleccione la discapacidad" />
            </FormLabelError>

            <FormInput v-model="formData.celular_referencia" label="Celular de referencia" />
            <FormInput v-model="formData.parentesco_referencia" label="Parentesco con referencia" />

        </div>

    </div>
</template>