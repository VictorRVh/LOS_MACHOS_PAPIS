<script setup>
import { computed, ref, watch } from "vue";
import useValidation from "../../../composables/useValidation";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelectCiclo from "../../ui/BaseSelectCiclo.vue";
import CheckBox from "../../ui/CheckBox.vue";
import useProgramaStore from "../../../store/Programa/useProgramaStore";
import { storeToRefs } from "pinia";

const props = defineProps({
  show: Boolean,
  programa: [Object, null],
  ciclo: Array,
});
const emit = defineEmits(["hide"]);

const programaStore = useProgramaStore();
const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

const { programaLoading } = storeToRefs(programaStore);

const requiredPermissions = computed(() => {
  return props.programa?.id
    ? ["todo-acceso-roles", "editar-roles"]
    : ["todo-acceso-roles", "crear-roles"];
});

const title = computed(() =>
  props.programa
    ? `Editar programa "${props.programa?.nombre_programa}"`
    : "Agregar nuevo programa"
);

const initialFormData = () => ({
  id_ciclo: null,
  año: null,
  numero_rd: null,
  status: 0,
  descripcion: null,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.programa?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide");
};

watch(
  () => props.programa,
  (newVal) => {
    formErrors.value = {};
    if (props.show && newVal?.id) {
      formData.value = { ...initialFormData(), ...newVal };
    } else {
      formData.value = initialFormData();
    }
  },
  { immediate: true }
);

const schema = yup.object().shape({
  id_ciclo: yup.string().nullable().required(),
  numero_rd: yup.string().nullable().required(),
  status: yup.bool().nullable().required(),
  descripcion: yup.string().nullable().required(),
  año: yup
    .string()
    .required("El año es obligatorio.")
    .test(
      "formato-año",
      "Formato inválido. Usa '2024' o '2024-2025' según el ciclo.",
      function (value) {
        const ciclo = props.ciclo.find(
          (c) => c.id === formData.value.id_ciclo
        );
        const nombreCiclo = ciclo?.nombre_ciclo?.toLowerCase();

        if (nombreCiclo?.includes("auxiliar")) {
          return /^\d{4}$/.test(value); // Solo un año
        }

        if (nombreCiclo?.includes("técnico")) {
          return /^\d{4}-\d{4}$/.test(value); // Rango de años
        }

        return true;
      }
    ),
});


const onCicloChange = () => {
  formData.value.año = "";
  formErrors.value.año = "";
};
const añoPlaceholder = computed(() => {
  const ciclo = props.ciclo.find((c) => c.id === formData.value.id_ciclo);
  const nombreCiclo = ciclo?.nombre_ciclo?.toLowerCase();

  if (nombreCiclo?.includes("ciclo auxiliar técnico")) return "2024";
  if (nombreCiclo?.includes("ciclo técnico")) return "2024-2025";
  return "2024 o 2024-2025";
});

let lastAñoValue = ""; // fuera del handler

const onAñoInput = (e) => {
  let raw = e.target.value;
  let val = raw.replace(/[^0-9]/g, ""); // Solo números

  const ciclo = props.ciclo.find((c) => c.id === formData.value.id_ciclo);
  const nombreCiclo = ciclo?.nombre_ciclo?.toLowerCase() || "";

  if (nombreCiclo.includes("auxiliar")) {
    // Solo 4 dígitos
    val = val.slice(0, 4);
    formData.value.año = val;
    lastAñoValue = val;
    return;
  }

  if (nombreCiclo.includes("técnico")) {
    const year1 = val.slice(0, 4);
    const year2 = val.slice(4, 8);

    // Detectar si el usuario está borrando el guion
    const isDeletingHyphen =
      lastAñoValue.endsWith("-") && !raw.endsWith("-");

    if (isDeletingHyphen) {
      val = year1; // Dejarlo sin guion
    } else if (year1.length === 4 && year2.length === 0) {
      val = year1 + "-"; // Insertar guion automáticamente
    } else if (year2.length > 0) {
      val = `${year1}-${year2}`;
    } else {
      val = year1;
    }

    formData.value.año = val;
    lastAñoValue = val;
    return;
  }

  // Default
  val = val.slice(0, 4);
  formData.value.año = val;
  lastAñoValue = val;
};


const onSubmit = async () => {
  formErrors.value = {};
  const { isValid, errors } = await runYupValidation(schema, formData.value);

  // if (!isValid) {
  //   formErrors.value = errors;
  //   return;
  // }

  try {
    if (isEditing.value) {
      await programaStore.updatePrograma(formData.value);
      showToast("Programa actualizado correctamente", "success");
    } else {
      await programaStore.addPrograma(formData.value);
      showToast("Programa creado correctamente", "success");
    }
    onCancelEdit();
  } catch (error) {
    console.error("Error al guardar el programa:", error);

    // Intentar extraer el mensaje más específico posible
    const message =
      error?.response?.data?.message || // si viene de axios con backend
      error?.message || // si es un error JS
      "Ocurrió un error al guardar el programa"; // fallback

    showToast(message, "error");
  }

};
</script>


<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">

      <FormLabelError label="Ciclo" required :error="formErrors?.id_ciclo">
        <BaseSelectCiclo v-model="formData.id_ciclo" :options="ciclo" @change="onCicloChange" label="nombre_ciclo"
          placeholder="Seleccione un ciclo" />
      </FormLabelError>

      <FormInput v-model="formData.numero_rd" :focus="show" label="Numero R.D." :error="formErrors?.numero_rd"
        required />

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormInput v-model="formData.año" label="Año" :error="formErrors?.año" required @input="onAñoInput"
          :placeholder="añoPlaceholder" />

        <CheckBox v-model="formData.status" label="Estado" class="mt-8 pl-4 flex justify-center items-centers" />

      </div>


      <FormInput v-model="formData.descripcion" :focus="show" label="Descripcion" :error="formErrors?.descripcion"
        required />


      <div class="w-full space-y-3">

        <div class="flex gap-2 mt-1">
          <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Programa'"
            :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :loading="programaLoading" key="submit-btn"
            @click="onSubmit" class="!w-full" />

          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>y