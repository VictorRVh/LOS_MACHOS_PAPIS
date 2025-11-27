<script setup>
import { computed, ref, watch } from "vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import CheckBox from "../../ui/CheckBox.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import usePeriodosStore from "../../../store/Periodo/usePeriodoStore";
import usePeriodosStatus from '../../../store/Periodo/usePeriodoStatusStore'
const props = defineProps({
  show: {
    type: Boolean,
    default: () => false,
  },
  periodo: {
    type: [Object, null],
    default: () => null,
  },
});
const emit = defineEmits(["hide"]);

const periodosStore = usePeriodosStore();
const ListStatusPerido = usePeriodosStatus();
const { store: createPeriodo, saving, update: updatePeriodo, updating } = useHttpRequest("/periodo");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  return props.periodo?.id
    ? ["todo-acceso-periodos", "editar-periodos"]
    : ["todo-acceso-periodos", "crear-periodos"];
});

const title = computed(() =>
  props.periodo
    ? `Editar Periodo "${props.periodo?.nombre_periodo}"`
    : "Agregar nuevo periodo"
);

const initialFormData = () => ({
  nombre_periodo: null,
  descripcion: null, // Si no usas descripción, puedes quitarlo
  status: 0,
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.periodo?.id);

const onCancelEdit = () => {
  formData.value = initialFormData();
  formErrors.value = {};
  emit("hide");
};

watch(
  () => props.periodo,
  (newPeriodo) => {
    formErrors.value = {}; // Limpia errores al cambiar
    if (props.show && newPeriodo?.id) {
      formData.value = { ...initialFormData(), ...newPeriodo };
    } else {
      formData.value = initialFormData();
    }
    formErrors.value = {};
  },
  { immediate: true }
);

const schema = yup.object().shape({
  nombre_periodo: yup
    .string()
    .required("El periodo es obligatorio.")
    .matches(
      /^\d{4}-(I|II|III|IV)$/,
      "Formato inválido. Usa: 2024-I, 2024-II, 2024-III o 2024-IV"
    ),
  status: yup.boolean().required(),
});

const onSubmit = async () => {
  if (saving.value || updating.value) return;
  formErrors.value = {};

  const { validated, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }



  const response = props.periodo?.id
    ? await updatePeriodo(props.periodo?.id, formData.value)
    : await createPeriodo(formData.value);

  if (response?.id) {
    formData.value = initialFormData();
    formErrors.value = {};
    showToast(`Periodo ${props.periodo?.id ? "actualizado" : "creado"} exitosamente.`);
    await periodosStore.loadPeriodos();
    await ListStatusPerido.loadPeriodos();
    onCancelEdit();
  }
};

const onPeriodoInput = (e) => {
  let value = e.target.value.toUpperCase();

  // 1. Permitir números, guion, I y V
  value = value.replace(/[^0-9IV-]/g, "");

  // 2. Insertar guion automáticamente después de 4 dígitos si no se está borrando
  if (/^\d{4}$/.test(value) && e.inputType !== "deleteContentBackward") {
    value = value + "-";
  }

  // 3. Validar parte del periodo (I, II, III, IV)
  if (value.includes("-")) {
    const [year, period = ""] = value.split("-");

    // Mantener solo I y V
    let clean = period.replace(/[^IV]/g, "");

    // Limitar a max 3 caracteres (III) o 2 si es IV
    if (clean.length > 3) clean = clean.slice(0, 3);

    // Aceptar solo opciones válidas:
    // I, II, III, IV
    const validOptions = ["I", "II", "III", "IV"];

    // Si no coincide con ninguna opción válida, limpiar lo inválido
    if (!validOptions.some(opt => opt.startsWith(clean))) {
      // ejemplo: si escribe "V" → limpiar
      clean = "";
    }

    value = `${year}-${clean}`;
  }

  // Limitar longitud máxima a 8 ("2024-III")
  value = value.slice(0, 8);

  formData.value.nombre_periodo = value;
};

</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-4 px-4 space-y-2 font-inter max-w-lg mx-auto">
      
      <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
        {{ isEditing ? "Editar periodo" : "Agregar nuevo periodo" }}
      </h3>
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />


      <FormInput v-model="formData.nombre_periodo" :focus="show" label="Periodo" :error="formErrors?.nombre_periodo"
        required placeholder="2024-I" @input="onPeriodoInput" />

      <CheckBox v-model="formData.status" label="Estado" class="flex items-center" />

      <div class="w-full space-y-2 pt-2">
        <div class="flex flex-col md:flex-row gap-2">
          <Button :title="periodo?.id ? 'Guardar Cambios' : 'Crear Periodo'"
            :loading-title="periodo?.id ? 'Guardando...' : 'Creando...'" :loading="saving || updating"
            :disabled="saving || updating" key="submit-btn" @click="onSubmit" class="w-full" />

          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
            class="bg-red-500 text-white px-4 md:w-auto" />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>
