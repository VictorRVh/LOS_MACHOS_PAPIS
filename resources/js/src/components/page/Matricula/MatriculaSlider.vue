<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import {
  AcademicCapIcon,
  CreditCardIcon,
  IdentificationIcon,
} from "@heroicons/vue/24/outline";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import useProgramaStore from "../../../store/Programa/useProgramaStatusStore";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import Step1 from "./Steps/Step1.vue";
import Step2 from "./Steps/Step2.vue";
import Step3 from "./Steps/Step3.vue";
import Button from "../../ui/Button.vue";
import * as yup from "yup";

const router = useRouter();
const { showToast } = useModalToast();
const { store, saving } = useHttpRequest("/matricula");
const programaStore = useProgramaStore();

const isLoading = ref(true);
const currentStep = ref(1);
const nameGrupo = ref("");

const stepMeta = [
  {
    number: 1,
    title: "Datos academicos",
    description: "Programa, especialidad y grupo",
    icon: AcademicCapIcon,
  },
  {
    number: 2,
    title: "Datos del estudiante",
    description: "Identidad, contacto y perfil",
    icon: IdentificationIcon,
  },
  {
    number: 3,
    title: "Pago y confirmacion",
    description: "Condicion, aporte y revision final",
    icon: CreditCardIcon,
  },
];

const currentStepMeta = computed(() => stepMeta.find((step) => step.number === currentStep.value));

const formData = reactive({
  id_programa: null,
  id_especialidad: null,
  id_grupo: null,
  convenio: "",
  duracion: "",
  horas: "",
  turno: "",
  seccion: "",
  tipo_documento: "DNI",
  nro_documento: "",
  apellido_paterno: "",
  apellido_materno: "",
  nombre: "",
  sexo: "",
  fecha_nacimiento: "",
  pais_nacimiento: "PERU",
  departamento_nacimiento: "",
  provincia_nacimiento: "",
  distrito_nacimiento: "",
  lugar_nacimiento: "",
  direccion_residencia: "",
  correo_electronico: "",
  celular_personal: "",
  estado_civil: "",
  grado_instruccion: "",
  trabaja: "",
  detalle_trabajo: "",
  carga_familiar: "",
  detalle_carga_familiar: "",
  internet_casa: "",
  tipo_internet: "",
  tipo_operador: "",
  otro_operador: "",
  equipo_clases: [],
  discapacidad: "",
  tipo_discapacidad: "",
  celular_referencia: "",
  parentesco_referencia: "",
  lengua_materna: "",
  condicion: "Gratuito",
  nro_recibo: "",
  aporte: "",
  anio_egreso: "",
});

const stepErrors = reactive({
  1: {},
  2: {},
  3: {},
});

onMounted(async () => {
  try {
    await programaStore.loadPrograma();
  } catch (error) {
    showToast("No se pudieron cargar los datos necesarios.", "error");
  } finally {
    isLoading.value = false;
  }
});

const stepSchemas = {
  1: yup.object({
    id_programa: yup.string().required("Debe seleccionar un programa"),
    id_especialidad: yup.string().required("Debe seleccionar una especialidad"),
    id_grupo: yup.string().required("Debe seleccionar un grupo"),
  }),
  2: yup.object({
    tipo_documento: yup.string().required("Tipo de documento es requerido"),
    nro_documento: yup.string().required("Nro. de documento es requerido"),
    apellido_paterno: yup.string().required("Apellido paterno es requerido"),
    apellido_materno: yup.string().required("Apellido materno es requerido"),
    nombre: yup.string().required("El nombre es requerido"),
    sexo: yup.string().required("El sexo es requerido"),
    fecha_nacimiento: yup
      .date()
      .required("Fecha de nacimiento es requerida")
      .max(new Date(new Date().setFullYear(new Date().getFullYear() - 12)), "El estudiante debe ser mayor de 12 anos")
      .min(new Date(new Date().setFullYear(new Date().getFullYear() - 100)), "La edad no puede ser mayor a 100 anos"),
    celular_personal: yup
      .string()
      .required("Celular es requerido")
      .matches(/^\d{9}$/, "El celular debe tener 9 numeros"),
    celular_referencia: yup.string().notRequired().matches(/^\d{9}$/, "El celular debe tener 9 numeros"),
    correo_electronico: yup.string().email("Debe ser un correo valido").notRequired(),
    direccion_residencia: yup.string().required("La direccion es requerida"),
    estado_civil: yup.string().required("Estado civil es requerido"),
  }),
  3: yup.object({}),
};

const validateCurrentStep = async () => {
  const schema = stepSchemas[currentStep.value];

  try {
    await schema.validate(formData, { abortEarly: false });
    stepErrors[currentStep.value] = {};
    return true;
  } catch (err) {
    const errors = {};
    err.inner.forEach((e) => {
      errors[e.path] = e.message;
    });
    stepErrors[currentStep.value] = errors;
    return false;
  }
};

const nextStep = async () => {
  const isValid = await validateCurrentStep();
  if (!isValid) {
    showToast("Por favor complete los campos obligatorios del paso actual.", "error");
    return;
  }
  if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--;
};

const onSubmit = async () => {
  const isValid = await validateCurrentStep();
  if (!isValid) {
    showToast("Faltan datos por completar.", "error");
    return;
  }

  const response = await store(formData);
  if (response.data?.matricula?.id) {
    showToast("Matricula realizada con exito.", "success");
    router.push({ name: "matricula.grupos.alumnos", params: { id: formData.id_grupo } });
    return;
  }

  showToast("Hubo un error al procesar la matricula.", "error");
};
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-matrículas', 'crear-matrículas']">
    <div class="bg-white p-3 transition-colors duration-300 dark:bg-slate-900">
      <div
        v-if="isLoading"
        class="flex min-h-[420px] items-center justify-center border border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
      >
        <p class="text-sm font-medium">Cargando datos del formulario...</p>
      </div>

      <div v-else class="space-y-3">
        <section class="border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800/70">
          <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                Proceso de registro
              </p>
              <h2 class="mt-1 text-[1.05rem] font-semibold text-slate-900 dark:text-slate-100">
                Matricular estudiante
              </h2>
              <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">
                Complete la informacion academica, personal y de pago para registrar la matricula.
              </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3">
              <div
                v-for="step in stepMeta"
                :key="step.number"
                class="min-w-[200px] border px-3 py-2 transition-colors"
                :class="
                  currentStep === step.number
                    ? 'border-cetpro bg-white text-cetpro dark:border-cetpro-light dark:bg-slate-900 dark:text-cetpro-light'
                    : currentStep > step.number
                      ? 'border-cetpro/30 bg-cetpro/5 text-slate-700 dark:border-cetpro-light/30 dark:bg-cetpro-light/10 dark:text-slate-200'
                      : 'border-slate-200 bg-white text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400'
                "
              >
                <div class="flex items-start gap-2.5">
                  <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center border"
                    :class="
                      currentStep >= step.number
                        ? 'border-cetpro/20 bg-cetpro/10 dark:border-cetpro-light/20 dark:bg-cetpro-light/10'
                        : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800'
                    "
                  >
                    <component :is="step.icon" class="h-4 w-4" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]">Paso {{ step.number }}</p>
                    <p class="mt-1 text-[13px] font-semibold leading-tight">{{ step.title }}</p>
                    <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                      {{ step.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
          <div class="border-b border-slate-200 px-3 py-2.5 dark:border-slate-700">
            <div class="flex items-center gap-2">
              <component :is="currentStepMeta.icon" class="h-4 w-4 text-cetpro dark:text-cetpro-light" />
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                  Paso {{ currentStep }}
                </p>
                <p class="text-[14px] font-semibold text-slate-900 dark:text-slate-100">
                  {{ currentStepMeta.title }}
                </p>
              </div>
            </div>
          </div>

          <div class="p-3">
            <Step1
              v-show="currentStep === 1"
              v-model="formData"
              :programas="programaStore.programa.programas"
              :nameGrupo="nameGrupo"
              :errors="stepErrors[1]"
              @cambiarVariable="nameGrupo = $event"
            />
            <Step2 v-show="currentStep === 2" v-model="formData" :errors="stepErrors[2]" />
            <Step3 v-show="currentStep === 3" v-model="formData" :nameGrupo="nameGrupo" />
          </div>
        </section>

        <section class="flex items-center justify-between border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[12px] text-slate-500 dark:text-slate-400">
            Revise cada paso antes de continuar para evitar observaciones posteriores.
          </p>
          <div class="flex items-center gap-2">
            <Button v-if="currentStep > 1" variant="outline" @click="prevStep" title="Anterior" />
            <Button v-if="currentStep < 3" @click="nextStep" title="Siguiente" />
            <Button v-if="currentStep === 3" @click="onSubmit" :loading="saving" title="Confirmar y matricular" />
          </div>
        </section>
      </div>
    </div>
  </AuthorizationFallback>
</template>
