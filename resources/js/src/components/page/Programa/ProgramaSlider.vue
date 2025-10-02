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
const emit = defineEmits(["hide", "programa-guardado"]);

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
  props.programa?.id
    ? `Editar programa "${props.programa?.nombre_programa}"`
    : "Agregar nuevo programa"
);

const initialFormData = () => ({
  id_ciclo: null,
  año: '',
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
    formErrors.value = {};
  },
  { immediate: true }
);

const selectedCicloInfo = computed(() => {
    if (!formData.value.id_ciclo || !props.ciclo) return null;
    return props.ciclo.find(c => c.id === formData.value.id_ciclo);
});

const isAuxiliar = computed(() => {
    return selectedCicloInfo.value?.nombre_ciclo?.toLowerCase().includes('auxiliar');
});

const isTecnico = computed(() => {
    return selectedCicloInfo.value?.nombre_ciclo?.toLowerCase().includes('técnico');
});

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
    formData.value.año = value.replace(/[^0-9]/g, '').slice(0, 4);
    return;
  }
  if (isTecnico.value) {
    const [year1_raw, year2_raw] = value.split('-');
    let year1 = (year1_raw || '').replace(/[^0-9]/g, '').slice(0, 4);
    let year2 = (year2_raw || '').replace(/[^0-9]/g, '').slice(0, 4);
    let result = year1;
    if (year2_raw !== undefined) {
      result += '-' + year2;
    } 
    else if (year1.length === 4 && e.inputType !== 'deleteContentBackward') {
      result += '-';
    }
    formData.value.año = result;
    return;
  }
  formData.value.año = value.replace(/[^0-9]/g, '').slice(0, 4);
};

const schema = yup.object().shape({
  id_ciclo: yup.string().nullable().required("El ciclo es obligatorio."),
  numero_rd: yup.string().nullable().required("El N° de R.D. es obligatorio."),
  status: yup.bool().required(),
  descripcion: yup.string().nullable(),
  año: yup.string()
    .required("El año es obligatorio.")
    .test(
      "formato-año-valido",
      function (value) {
        if (!value) return this.createError({ message: "El año es obligatorio." });
        const cicloNombre = selectedCicloInfo.value?.nombre_ciclo?.toLowerCase() || '';
        if (cicloNombre.includes("auxiliar")) {
            if (!/^\d{4}$/.test(value)) {
                return this.createError({ message: "Formato inválido. Use '2024'." });
            }
        } else if (cicloNombre.includes("técnico")) {
            if (!/^\d{4}-\d{4}$/.test(value)) {
                return this.createError({ message: "Formato inválido. Use '2024-2025'." });
            }
        } else {
            return this.createError({ message: "Seleccione un ciclo para validar el año." });
        }
        return true;
      }
    ),
});

const onSubmit = async () => {
  formErrors.value = {};
  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }
  try {
    let programaGuardado;
    if (isEditing.value) {
      programaGuardado = await programaStore.updatePrograma(formData.value.id, formData.value);
      showToast("Programa actualizado correctamente", "success");
    } else {
      programaGuardado = await programaStore.addPrograma(formData.value);
      showToast("Programa creado correctamente", "success");
    }
    emit("programa-guardado", programaGuardado || formData.value);
    onCancelEdit();
  } catch (error) {
    const message = error?.response?.data?.message || "Ocurrió un error al guardar el programa";
    showToast(message, "error");
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-2 space-y-1.5 font-inter">
      <FormLabelError label="Ciclo" required :error="formErrors?.id_ciclo">
        <BaseSelectCiclo 
            v-model="formData.id_ciclo" 
            :options="ciclo" 
            @change="onCicloChange" 
            label="nombre_ciclo"
            placeholder="Seleccione un ciclo" />
      </FormLabelError>

      <FormInput 
        v-model="formData.numero_rd" 
        :focus="show" 
        label="Numero R.D." 
        :error="formErrors?.numero_rd"
        required />

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormInput 
            v-model="formData.año" 
            label="Año" 
            :error="formErrors?.año" 
            required 
            @input="onAñoInput"
            :placeholder="añoPlaceholder" />

        <CheckBox 
            v-model="formData.status" 
            label="Estado" 
            class="mt-8 pl-4 flex justify-center items-centers" />
      </div>

      <FormInput 
        v-model="formData.descripcion" 
        :focus="show" 
        label="Descripcion" 
        :error="formErrors?.descripcion"
        required />

      <div class="w-full space-y-3">
        <div class="flex gap-2 mt-1">
          <Button 
            :title="isEditing ? 'Guardar Cambios' : 'Crear Programa'"
            :loading-title="isEditing ? 'Guardando...' : 'Creando...'" 
            :loading="programaLoading" 
            key="submit-btn"
            @click="onSubmit" 
            class="!w-full" />

          <Button 
            v-if="isEditing" 
            title="Cancelar" 
            variant="outline" 
            @click="onCancelEdit"
            class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>