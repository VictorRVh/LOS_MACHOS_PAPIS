<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import FormInput from "../ui/FormInput.vue";
import Button from "../ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import Slider from "../ui/Slider.vue";
import useValidation from "../../composables/useValidation";
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";
import useCetproStore from "../../store/useCetproStore";
import {
  BuildingOffice2Icon,
  IdentificationIcon,
  MapPinIcon,
  ClipboardDocumentListIcon,
} from "@heroicons/vue/24/outline";
import * as yup from "yup";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["hide"]);
const cetproStore = useCetproStore();

const { store: saveCetpro, saving } = useHttpRequest("/cetprodata");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => [
  "todo-acceso-cetpro",
  "editar-informacion-cetpro",
]);

const title = computed(() => "Datos del CETPRO");

const initialFormData = () => ({
  cetpro: "",
  anio: "Año de la Esperanza y el Fortalecimiento de la Democracia",
  rd_autorizacion: "",
  rd_conversion: "",
  ugel: "",
  dre: "",
  tipo_gestion: "",
  region: "",
  provincia: "",
  distrito: "",
  lugar: "",
  direccion: "",
  numero: "",
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(
  () => props.show,
  async (open) => {
    if (!open) return;

    try {
      const { data } = await axios.get("/cetprodata");
      if (data) {
        formData.value = {
          ...initialFormData(),
          ...data,
        };
      }
    } catch (e) {
      formData.value = initialFormData();
    }
  }
);

const schema = yup.object({
  cetpro: yup.string().required("El CETPRO es obligatorio"),
  anio: yup.string().required("El campo Año es obligatorio"),
  rd_autorizacion: yup.string().required("La R.D. de autorización es obligatoria"),
  rd_conversion: yup.string().nullable(),
  ugel: yup.string().required("UGEL es obligatorio"),
  dre: yup.string().required("DRE es obligatorio"),
  tipo_gestion: yup.string().required("Tipo de gestión es obligatorio"),
  region: yup.string().required("Región es obligatoria"),
  provincia: yup.string().required("Provincia es obligatoria"),
  distrito: yup.string().required("Distrito es obligatorio"),
  lugar: yup.string().nullable(),
  direccion: yup.string().nullable(),
  numero: yup.string().nullable(),
});

const onSubmit = async () => {
  if (saving.value) return;

  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  formErrors.value = {};

  const response = await saveCetpro(formData.value);

  if (response) {
    showToast("Datos del CETPRO guardados correctamente");
    emit("hide");
    await cetproStore.loadCetpro();
  }
};
</script>

<template>
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="requiredPermissions">
      <div class="space-y-5 font-inter">
        <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
          <div class="mb-4 flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-600">
              <BuildingOffice2Icon class="h-4 w-4 text-slate-600 dark:text-slate-300" />
            </div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Datos Institucionales</h3>
          </div>

          <div class="space-y-4">
            <FormInput
              v-model="formData.cetpro"
              label="CETPRO"
              :error="formErrors.cetpro"
              required
              :uppercase="true"
            />
            <FormInput
              v-model="formData.anio"
              label="Año"
              :error="formErrors.anio"
              required
            />
            <FormInput
              v-model="formData.tipo_gestion"
              label="Tipo de Gestión"
              :error="formErrors.tipo_gestion"
              required
              :uppercase="true"
            />
          </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
          <div class="mb-4 flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-600">
              <IdentificationIcon class="h-4 w-4 text-slate-600 dark:text-slate-300" />
            </div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Resoluciones</h3>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <FormInput
              v-model="formData.rd_autorizacion"
              label="R.D. de Autorización"
              :error="formErrors.rd_autorizacion"
              required
              :uppercase="true"
            />
            <FormInput
              v-model="formData.rd_conversion"
              label="R.D. de Conversión"
              :uppercase="true"
            />
          </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
          <div class="mb-4 flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-600">
              <MapPinIcon class="h-4 w-4 text-slate-600 dark:text-slate-300" />
            </div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Ubicación</h3>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <FormInput v-model="formData.ugel" label="UGEL" required :uppercase="true" />
            <FormInput v-model="formData.dre" label="DRE" required :uppercase="true" />
            <FormInput v-model="formData.region" label="Región" required :uppercase="true" />
            <FormInput v-model="formData.provincia" label="Provincia" required :uppercase="true" />
            <FormInput v-model="formData.distrito" label="Distrito" required :uppercase="true" />
            <FormInput v-model="formData.lugar" label="Lugar" :uppercase="true" />
            <FormInput v-model="formData.direccion" label="Dirección" :uppercase="true" class="md:col-span-2" />
            <FormInput v-model="formData.numero" label="Número" />
          </div>
        </section>

        <section class="sticky bottom-0 rounded-xl border border-slate-200 bg-white/95 p-3 backdrop-blur dark:border-slate-700 dark:bg-slate-800/90">
          <div class="flex items-center gap-2 pb-3">
            <ClipboardDocumentListIcon class="h-4 w-4 text-slate-500" />
            <p class="text-xs text-slate-500 dark:text-slate-400">Verifica los datos antes de guardar.</p>
          </div>
          <Button
            title="Guardar datos del CETPRO"
            :loading="saving"
            class="!w-full"
            @click="onSubmit"
          />
        </section>
      </div>
    </AuthorizationFallback>
  </Slider>
</template>