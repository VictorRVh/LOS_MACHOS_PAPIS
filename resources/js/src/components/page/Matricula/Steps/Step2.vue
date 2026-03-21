<script setup>
import { computed, ref, watch } from "vue";
import { MagnifyingGlassIcon, UserCircleIcon } from "@heroicons/vue/24/outline";
import FormInput from "../../../ui/FormInput.vue";
import FormLabelError from "../../../ui/FormLabelError.vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import BaseSelect from "../../../ui/BaseSelect.vue";
import ubigeo from "../../../../utils/ubigeo";
import useModalToast from "../../../../composables/useModalToast";
import useHttpRequest from "../../../../composables/useHttpRequest";

const { showToast } = useModalToast();
const { store: busquedaDni, saving } = useHttpRequest("/buscar-documento");

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  edit: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);
const mostrarOtroDistrito = ref(false);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const opcionesSexo = [
  { name: "Masculino", value: "M" },
  { name: "Femenino", value: "F" },
  { name: "Otro", value: "O" },
];
const opcionesEstadoCivil = ["SOLTERO(A)", "CASADO(A)", "VIUDO(A)", "DIVORCIADO(A)", "CONVIVIENTE"];
const opcionesGradoInstruccion = ["Primaria incompleta", "Primaria completa", "Secundaria incompleta", "Secundaria completa", "Superior incompleta", "Superior completa"];
const opcionesOperadores = ["CLARO", "MOVISTAR", "ENTEL", "BITEL", "WOW", "OTRO"];
const opcionesLenguaMaterna = ["Castellano", "Quechua", "Aymara", "Ashaninka", "Awajun", "Otros"];
const opcionesEquiposVirtuales = ["Laptop", "Computadora", "Tablet", "Celular"];
const opcionesSiNo = ["Si", "No"];
const opcionesDiscapacidad = [
  "Discapacidad intelectual - Retardo mental leve",
  "Transtorno del espectro autista",
  "Discapacidad intelectual - Retardo mental moderado",
  "Discapacidad visual - Baja vision",
  "Discapacidad visual - Ceguera",
  "Discapacidad auditiva - Hipoacusia",
  "Discapacidad auditiva - Sordera total",
  "Otros",
];

const departamentos = ref(ubigeo.map((dep) => dep.departamento));
const provincias = ref([]);
const distritos = ref([]);
const watchActive = ref(true);

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

const adaptDniResponse = (d) => ({
  ...d,
  departamento_nacimiento: d.departamento_nacimiento?.toUpperCase() ?? "",
  provincia_nacimiento: d.provincia_nacimiento?.toUpperCase() ?? "",
  distrito_nacimiento: d.distrito_nacimiento?.toUpperCase() ?? "",
  celular: d.celular_personal ?? "",
  equipos_virtuales: autoParseJson(d.equipos_virtuales, []),
});

const buscarDNI = async () => {
  const { tipo_documento: tipo, nro_documento: numero } = formData.value;

  try {
    const response = await busquedaDni({
      tipo_documento: tipo,
      dni: numero,
    });

    if (response.error || !response.data) {
      showToast("No se encontraron datos. Complete la informacion manualmente.");
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
    showToast("Error inesperado al buscar el documento.");
    watchActive.value = true;
  }
};

const actualizarListas = () => {
  const depNorm = normalize(formData.value.departamento_nacimiento);
  const provNorm = normalize(formData.value.provincia_nacimiento);

  const depData = ubigeo.find((d) => normalize(d.departamento) === depNorm);
  provincias.value = depData ? depData.provincias.map((p) => p.provincia) : [];

  const provData = depData?.provincias.find((p) => normalize(p.provincia) === provNorm);
  distritos.value = provData ? [...provData.distritos, "OTRO"] : [];
};

watch(() => formData.value.departamento_nacimiento, (nuevo) => {
  if (!watchActive.value) return;
  const depNorm = normalize(nuevo);
  const depData = ubigeo.find((d) => normalize(d.departamento) === depNorm);
  provincias.value = depData ? depData.provincias.map((p) => p.provincia) : [];
  distritos.value = [];
});

watch(() => formData.value.provincia_nacimiento, (nuevo) => {
  if (!watchActive.value) return;
  const depNorm = normalize(formData.value.departamento_nacimiento);
  const provNorm = normalize(nuevo);
  const depData = ubigeo.find((d) => normalize(d.departamento) === depNorm);
  const provData = depData?.provincias.find((p) => normalize(p.provincia) === provNorm);
  distritos.value = provData ? [...provData.distritos, "OTRO"] : [];
});

watch(
  () => formData.value.distrito_nacimiento,
  (nuevo) => {
    if (!watchActive.value) return;
    const distNorm = normalize(nuevo);
    mostrarOtroDistrito.value = distNorm === "OTRO";
    if (distNorm !== "OTRO") formData.value.lugar_nacimiento = "";
  },
  { immediate: true }
);
</script>

<template>
  <div class="relative space-y-4">
    <transition name="fade">
      <div
        v-if="saving"
        class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/75 backdrop-blur-sm dark:bg-slate-900/75"
      >
        <svg class="mb-3 h-10 w-10 animate-spin text-cetpro" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Buscando documento...</p>
      </div>
    </transition>

    <section class="border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-800/60">
      <div class="flex items-start gap-2.5">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center border border-cetpro/20 bg-cetpro/10 text-cetpro dark:border-cetpro-light/20 dark:bg-cetpro-light/10 dark:text-cetpro-light">
          <UserCircleIcon class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Identificacion</p>
          <h4 class="mt-1 text-[15px] font-semibold text-slate-900 dark:text-slate-100">Datos personales del estudiante</h4>
          <p class="mt-1 text-[13px] leading-5 text-slate-500 dark:text-slate-400">
            Registre la informacion principal del estudiante y valide el documento cuando corresponda.
          </p>
        </div>
      </div>

      <div class="mt-4 grid gap-3 xl:grid-cols-[1.1fr_1fr_1fr_1fr]">
        <div class="xl:col-span-2">
          <label class="mb-1 block text-[12px] font-medium text-slate-700 dark:text-slate-200">Documento *</label>
          <div class="grid gap-2 sm:grid-cols-[160px_minmax(0,1fr)_42px]">
            <BaseSelect v-model="formData.tipo_documento" :options="['DNI', 'CARNET EXT.']" />
            <FormInput v-model="formData.nro_documento" label="" :error="errors.nro_documento" />
            <button
              v-if="!edit"
              type="button"
              @click="buscarDNI"
              :disabled="saving"
              class="flex h-[42px] items-center justify-center border border-cetpro bg-cetpro text-white transition hover:bg-cetpro-dark disabled:cursor-not-allowed disabled:opacity-70"
            >
              <MagnifyingGlassIcon class="h-4 w-4" />
            </button>
          </div>
        </div>

        <FormInput v-model="formData.apellido_paterno" label="Apellido paterno *" :error="errors.apellido_paterno" />
        <FormInput v-model="formData.apellido_materno" label="Apellido materno *" :error="errors.apellido_materno" />
      </div>

      <div class="mt-3 grid gap-3 md:grid-cols-4">
        <FormInput v-model="formData.nombre" label="Nombres *" :error="errors.nombre" />
        <FormLabelError label="Sexo *" :error="errors.sexo">
          <v-select v-model="formData.sexo" :options="opcionesSexo" label="name" :reduce="(opcion) => opcion.value" placeholder="Seleccione sexo" :clearable="false" />
        </FormLabelError>
        <FormLabelError label="Fecha de nacimiento *" :error="errors.fecha_nacimiento">
          <FormInput v-model="formData.fecha_nacimiento" type="date" label="" />
        </FormLabelError>
        <FormInput v-model="formData.pais_nacimiento" label="Pais de nacimiento" />
      </div>
    </section>

    <section class="border border-slate-200 bg-white px-4 py-4 dark:border-slate-700 dark:bg-slate-900">
      <div class="flex flex-col gap-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Ubicacion y contacto</p>
        <h4 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">Datos de residencia y comunicacion</h4>
      </div>

      <div class="mt-4 grid gap-3 md:grid-cols-3">
        <FormLabelError label="Departamento de nacimiento" :error="errors.departamento_nacimiento">
          <v-select v-model="formData.departamento_nacimiento" :options="departamentos" placeholder="Buscar departamento..." />
        </FormLabelError>
        <FormLabelError label="Provincia de nacimiento" :error="errors.provincia_nacimiento">
          <v-select v-model="formData.provincia_nacimiento" :options="provincias" placeholder="Buscar provincia..." :disabled="!formData.departamento_nacimiento" />
        </FormLabelError>
        <FormLabelError label="Distrito de nacimiento" :error="errors.distrito_nacimiento">
          <v-select v-model="formData.distrito_nacimiento" :options="distritos" placeholder="Buscar distrito..." :disabled="!formData.provincia_nacimiento" />
        </FormLabelError>
        <FormInput v-if="mostrarOtroDistrito" v-model="formData.lugar_nacimiento" label="Especifique otro lugar" />
        <FormInput v-model="formData.direccion_residencia" label="Direccion de residencia *" :error="errors.direccion_residencia" class="md:col-span-2" />
        <FormInput v-model="formData.celular_personal" label="Celular personal *" :error="errors.celular_personal" maxlength="9" />
        <FormInput v-model="formData.celular_referencia" label="Celular de referencia" :error="errors.celular_referencia" />
        <FormInput v-model="formData.parentesco_referencia" label="Parentesco de referencia" />
        <FormInput v-model="formData.correo_electronico" label="Correo electronico" type="email" :error="errors.correo_electronico" />
      </div>
    </section>

    <section class="border border-slate-200 bg-white px-4 py-4 dark:border-slate-700 dark:bg-slate-900">
      <div class="flex flex-col gap-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Perfil complementario</p>
        <h4 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">Informacion adicional para seguimiento</h4>
      </div>

      <div class="mt-4 grid gap-3 md:grid-cols-3">
        <FormLabelError label="Estado civil *" :error="errors.estado_civil">
          <v-select v-model="formData.estado_civil" :options="opcionesEstadoCivil" placeholder="Seleccione estado" />
        </FormLabelError>
        <FormLabelError label="Grado de instruccion" :error="errors.grado_instruccion">
          <v-select v-model="formData.grado_instruccion" :options="opcionesGradoInstruccion" placeholder="Seleccione grado" />
        </FormLabelError>
        <FormInput v-model="formData.anio_egreso" label="Ano de egreso" />
        <FormLabelError label="Lengua materna" :error="errors.lengua_materna">
          <v-select v-model="formData.lengua_materna" :options="opcionesLenguaMaterna" placeholder="Seleccione lengua" />
        </FormLabelError>
        <FormLabelError label="Trabaja actualmente" :error="errors.trabaja">
          <v-select v-model="formData.trabaja" :options="opcionesSiNo" placeholder="Seleccione opcion" />
        </FormLabelError>
        <FormInput v-if="formData.trabaja === 'Si'" v-model="formData.detalle_trabajo" label="Ocupacion o centro laboral" />
        <FormLabelError label="Carga familiar" :error="errors.carga_familiar">
          <v-select v-model="formData.carga_familiar" :options="opcionesSiNo" placeholder="Seleccione opcion" />
        </FormLabelError>
        <FormInput v-if="formData.carga_familiar === 'Si'" v-model="formData.detalle_carga_familiar" label="Numero de personas a cargo" />
        <FormLabelError label="Internet en casa" :error="errors.internet_casa">
          <v-select v-model="formData.internet_casa" :options="opcionesSiNo" placeholder="Seleccione opcion" />
        </FormLabelError>
        <FormInput v-if="formData.internet_casa === 'Si'" v-model="formData.tipo_internet" label="Tipo de conexion" />
        <FormLabelError label="Operador celular" :error="errors.tipo_operador">
          <v-select v-model="formData.tipo_operador" :options="opcionesOperadores" placeholder="Seleccione operador" />
        </FormLabelError>
        <FormInput v-if="formData.tipo_operador === 'OTRO'" v-model="formData.otro_operador" label="Especifique operador" />
        <FormLabelError label="Equipos disponibles" :error="errors.equipos_virtuales">
          <v-select v-model="formData.equipos_virtuales" :options="opcionesEquiposVirtuales" placeholder="Seleccione equipos" multiple :close-on-select="false" />
        </FormLabelError>
        <FormLabelError label="Tiene discapacidad" :error="errors.discapacidad">
          <v-select v-model="formData.discapacidad" :options="opcionesSiNo" placeholder="Seleccione opcion" />
        </FormLabelError>
        <FormLabelError v-if="formData.discapacidad === 'Si'" label="Tipo de discapacidad" :error="errors.tipo_discapacidad">
          <v-select v-model="formData.tipo_discapacidad" :options="opcionesDiscapacidad" placeholder="Seleccione discapacidad" />
        </FormLabelError>
      </div>
    </section>
  </div>
</template>
