<script setup>
import { computed, ref, watch } from "vue";
import useValidation from "../../../composables/useValidation";
import useModalToast from "../../../composables/useModalToast";
import useHttpRequest from "../../../composables/useHttpRequest";
import * as yup from "yup";

import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelectCiclo from "../../ui/BaseSelectCiclo.vue";
import CheckBox from "../../ui/CheckBox.vue";
import useProgramaStore from "../../../store/Programa/useProgramaStore";
import useProgramaStatusStore from "../../../store/Programa/useProgramaStatusStore";

const props = defineProps({
  show: Boolean,
  programa: [Object, null],
  ciclo: Array,
});
const emit = defineEmits(["hide", "programa-guardado"]);

const programaStore = useProgramaStore();
const programaStatusStore = useProgramaStatusStore();
const { showToast } = useModalToast();
const { runYupValidation } = useValidation();

// 🔧 Usamos el nuevo composable HTTP
const { store: createPrograma, update: updatePrograma, saving, updating } = useHttpRequest("/programa_estudio");

const requiredPermissions = computed(() =>
  props.programa?.id
    ? ["todo-acceso-ciclo-academico", "editar-ciclo-academico"]
    : ["todo-acceso-ciclo-academico", "crear-ciclo-academico"]
);

const title = computed(() =>
  props.programa?.id
    ? `Editar ciclo académico "${props.programa?.nombre_programa}"`
    : "Agregar nuevo ciclo académico"
);

const initialFormData = () => ({
  id_ciclo: null,
  año: "",
  numero_rd: null,
  status: 0,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.programa?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide");
};

// 🔁 Sincroniza datos cuando abres el modal o cambias de programa
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

// 🧮 Validaciones dinámicas por tipo de ciclo
const selectedCicloInfo = computed(() => {
  if (!formData.value.id_ciclo || !props.ciclo) return null;
  return props.ciclo.find((c) => c.id === formData.value.id_ciclo);
});

const isAuxiliar = computed(() =>
  selectedCicloInfo.value?.nombre_ciclo?.toLowerCase().includes("auxiliar")
);
const isTecnico = computed(() =>
  selectedCicloInfo.value?.nombre_ciclo?.toLowerCase().includes("técnico")
);

const añoPlaceholder = computed(() => {
  if (isAuxiliar.value) return "2024";
  if (isTecnico.value) return "2024-2025";
  return "Seleccione un ciclo";
});

const onCicloChange = () => {
  formData.value.año = "";
  formErrors.value.año = "";
};

const onAñoInput = (e) => {
  let value = e.target.value;
  if (isAuxiliar.value) {
    formData.value.año = value.replace(/[^0-9]/g, "").slice(0, 4);
    return;
  }
  if (isTecnico.value) {
    const [year1_raw, year2_raw] = value.split("-");
    let year1 = (year1_raw || "").replace(/[^0-9]/g, "").slice(0, 4);
    let year2 = (year2_raw || "").replace(/[^0-9]/g, "").slice(0, 4);
    let result = year1;
    if (year2_raw !== undefined) {
      result += "-" + year2;
    } else if (year1.length === 4 && e.inputType !== "deleteContentBackward") {
      result += "-";
    }
    formData.value.año = result;
    return;
  }
  formData.value.año = value.replace(/[^0-9]/g, "").slice(0, 4);
};

// ✅ Validación Yup
const schema = yup.object().shape({
  id_ciclo: yup.string().nullable().required("El ciclo es obligatorio."),
  numero_rd: yup.string().nullable().required("El N° de R.D. es obligatorio."),
  status: yup.bool().required(),
  año: yup
    .string()
    .required("El año es obligatorio.")
    .test("formato-año-valido", function (value) {
      if (!value)
        return this.createError({ message: "El año es obligatorio." });

      const cicloNombre =
        selectedCicloInfo.value?.nombre_ciclo?.toLowerCase() || "";
      if (cicloNombre.includes("auxiliar")) {
        if (!/^\d{4}$/.test(value)) {
          return this.createError({
            message: "Formato inválido. Use '2024'.",
          });
        }
      } else if (cicloNombre.includes("técnico")) {
        if (!/^\d{4}-\d{4}$/.test(value)) {
          return this.createError({
            message: "Formato inválido. Use '2024-2025'.",
          });
        }
      } else {
        return this.createError({
          message: "Seleccione un ciclo para validar el año.",
        });
      }
      return true;
    }),
});

// 🧾 Guardar programa (crear/editar)
const onSubmit = async () => {
  if (saving.value || updating.value) return;

  formErrors.value = {};
  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  try {
    const response = isEditing.value
      ? await updatePrograma(formData.value.id, formData.value)
      : await createPrograma(formData.value);

    if (response?.id) {
      showToast(
        `Ciclo académico ${isEditing.value ? "actualizado" : "creado"} correctamente`,
        "success"
      );
      emit("programa-guardado", response);
      onCancelEdit();
      programaStore.loadProgramas();
      programaStatusStore.loadPrograma();
      formData.value = initialFormData();
      formErrors.value = {};
    }
  } catch (error) {
    const message =
      error?.response?.data?.message ||
      "Ocurrió un error al guardar el ciclo académico";
    showToast(message, "error");
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">

    <div class="bg-white space-y-3 dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
      <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
        {{ isEditing ? "Editar ciclo académico" : "Agregar nuevo ciclo académico" }}
      </h3>
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
      <FormLabelError label="Ciclo" required :error="formErrors?.id_ciclo">
        <BaseSelectCiclo v-model="formData.id_ciclo" :options="ciclo" @change="onCicloChange" label="nombre_ciclo"
          placeholder="Seleccione un ciclo" />
      </FormLabelError>

      <!-- Número RD -->
      <FormInput v-model="formData.numero_rd" :focus="show" label="Número R.D." :error="formErrors?.numero_rd"
        required />

      <!-- Año y Estado -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormInput v-model="formData.año" label="Año" :error="formErrors?.año" required @input="onAñoInput"
          :placeholder="añoPlaceholder" />
        <CheckBox v-model="formData.status" label="Estado" class="mt-8 pl-4 flex justify-center items-centers" />
      </div>

      <!-- Descripción -->


      <!-- Botones -->
      <div class="w-full space-y-3">
        <div class="flex gap-2 mt-1">
          <Button :title="isEditing ? 'Guardar Cambios' : 'Crear ciclo académico'"
            :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :loading="saving || updating"
            :disabled="saving || updating" key="submit-btn" @click="onSubmit" class="!w-full" />
          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
