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

const opcionesSexo = [{ name: 'Masculino', value: "M" }, { name: 'Femenino', value: "F" }, { name: 'Otro', value: "O" }];
const opcionesEstadoCivil = ['SOLTERO(A)', 'CASADO(A)', 'VIUDO(A)', 'DIVORCIADO(A)', 'CONVIVIENTE'];
const opcionesGradoInstruccion = ['Primaria incompleta', 'Primaria completa', 'Secundaria incompleta', 'Secundaria completa', 'Superior incompleta', 'Superior completa'];
const opcionesLenguaMaterna = ['Castellano', 'Quechua', 'Aymara', 'Ashaninka', 'Awajun', 'Otros'];
const opcionesEquiposVirtuales = ['Laptop', 'Computadora', 'Tablet', 'Celular'];
const opcionesSiNo = ["Si", "No"];
const opcionesDiscapacidad = ["Discapacidad intelectual - Retardo mental leve", "Transtorno del espectro autista", "Discapacidad intelectual - Retardo mental moderado", "Discapacidad visual - Baja visión", "Discapacidad visual - Ceguera", "Discapacidad auditiva - Hipoacusia", "Discapacidad auditiva - Sordera total", "Otros"];

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
    if (newDist !== 'OTRO') {
        formData.value.lugar_nacimiento = '';
    }
});

const buscarDNI = async () => {
    const { tipo_documento: tipo, nro_documento: numero } = formData.value;

    // --- Validaciones ---
    if (!tipo) return showToast("Debe seleccionar un tipo de documento");
    if (!numero) return showToast("Debe ingresar un número de documento");
    if (tipo === "DNI" && numero.length !== 8)
        return showToast("El DNI debe tener 8 dígitos");
    if (tipo === "CARNET EXT." && numero.length < 9)
        return showToast("El Carnet de Extranjería debe tener al menos 9 caracteres");

    try {
        const response = await busquedaDni({ tipo_documento: tipo, dni: numero });
        if (response.error) return showToast(response.error);

        const d = adaptDniResponse(response.data);

        // Rellenar formulario automáticamente
        Object.assign(formData.value, {
            tipo_documento: d.tipo_documento ?? "",
            nro_documento: d.nro_documento ?? "",
            apellido_paterno: d.apellido_paterno ?? "",
            apellido_materno: d.apellido_materno ?? "",
            nombre: d.nombre ?? "",
            sexo: d.sexo ?? "",
            fecha_nacimiento: d.fecha_nacimiento ?? "",
            pais_nacimiento: d.pais_nacimiento ?? "PERÚ",
            departamento_nacimiento: d.departamento_nacimiento,
            provincia_nacimiento: d.provincia_nacimiento,
            distrito_nacimiento: d.distrito_nacimiento,
            lugar_nacimiento: d.lugar_nacimiento ?? "",
            direccion_residencia: d.direccion_residencia ?? "",
            celular_personal: d.celular_personal ?? "",
            correo_electronico: d.correo_electronico ?? "",
            estado_civil: d.estado_civil ?? "",
            grado_instruccion: d.grado_instruccion ?? "",
            anio_egreso: d.anio_egreso ?? "",
            lengua_materna: d.lengua_materna ?? "",
            trabaja: d.trabaja ?? "",
            detalle_trabajo: d.detalle_trabajo ?? "",
            carga_familiar: d.carga_familiar ?? "",
            detalle_carga_familiar: d.detalle_carga_familiar ?? "",
            internet_casa: d.internet_casa ?? "",
            tipo_internet: d.tipo_internet ?? "",
            equipos_virtuales: autoParseJson(d.equipos_virtuales, []),
            discapacidad: d.discapacidad ?? "",
            tipo_discapacidad: d.tipo_discapacidad ?? "",
            celular_referencia: d.celular_referencia ?? "",
            parentesco_referencia: d.parentesco_referencia ?? "",
        });

        // Actualizar provincias y distritos
        actualizarUbigeo();

        showToast("Datos encontrados correctamente");
    } catch (error) {
        console.error(error);
        showToast("Error al buscar el documento");
    }
};

const actualizarUbigeo = () => {
    const dep = normalizeUbigeo(formData.value.departamento_nacimiento);
    const prov = normalizeUbigeo(formData.value.provincia_nacimiento);

    // Provincias
    const depData = ubigeo.find(d => normalizeUbigeo(d.departamento) === dep);
    provincias.value = depData ? depData.provincias.map(p => p.provincia) : [];

    // Distritos
    const provData = depData && prov
        ? depData.provincias.find(p => normalizeUbigeo(p.provincia) === prov)
        : null;
    distritos.value = provData ? [...provData.distritos, 'OTRO'] : [];

    mostrarOtroDistrito.value = formData.value.distrito_nacimiento === 'OTRO';
};

const normalizeUbigeo = value => value?.toString().trim().toUpperCase() ?? null;

const autoParseJson = (value, fallback = []) => {
    try { return typeof value === "string" ? JSON.parse(value) : (value ?? fallback); }
    catch { return fallback; }
};

const adaptDniResponse = d => ({
    ...d,
    departamento_nacimiento: normalizeUbigeo(d.departamento_nacimiento),
    provincia_nacimiento: normalizeUbigeo(d.provincia_nacimiento),
    distrito_nacimiento: normalizeUbigeo(d.distrito_nacimiento),
    celular: d.celular_personal ?? "",
    equipos_virtuales: autoParseJson(d.equipos_virtuales, []),
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