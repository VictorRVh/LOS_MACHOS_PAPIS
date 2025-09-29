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
  },
  { immediate: true }
);

const schema = yup.object().shape({
  nombre_periodo: yup
    .string()
    .required("El periodo es obligatorio.")
    .matches(/^\d{4}-(I|II)$/, "Formato inválido. Usa: 2024-I o 2024-II"),
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
    showToast(`Periodo ${props.periodo?.id ? "actualizado" : "creado"} exitosamente.`);
    await periodosStore.loadPeriodos();
    onCancelEdit();
  }
};

const onPeriodoInput = (e) => {
  let value = e.target.value.toUpperCase();

  // 1. Limpiar caracteres no válidos (solo permite números, guion e 'I')
  value = value.replace(/[^0-9I-]/g, '');

  // 2. Limitar la longitud total
  if (value.length > 7) {
    value = value.slice(0, 7);
  }

  // 3. Insertar guion SOLO si aún no existe y el usuario ha escrito 4 dígitos
  //    y no está borrando
  if (/^\d{4}$/.test(value) && e.inputType !== 'deleteContentBackward') {
    value = value + '-';
  }

  // 4. Validar semestre (solo 'I' o 'II')
  if (value.includes('-')) {
    const [year, semester = ''] = value.split('-');

    let cleanSemester = semester.replace(/[^I]/g, ''); // eliminar todo excepto "I"
    if (cleanSemester.length > 2) cleanSemester = cleanSemester.slice(0, 2); // máximo "II"

    value = `${year}-${cleanSemester}`;
  }

  formData.value.nombre_periodo = value;
};

</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="mt-4 px-4 space-y-2 font-inter max-w-lg mx-auto">
      <FormInput
        v-model="formData.nombre_periodo"
        :focus="show"
        label="Periodo"
        :error="formErrors?.nombre_periodo"
        required
        placeholder="2024-I"
        @input="onPeriodoInput"
      />

      <CheckBox
        v-model="formData.status"
        label="Estado"
        class="flex items-center"
      />

      <div class="w-full space-y-2 pt-2">
        <div class="flex flex-col md:flex-row gap-2">
          <Button
            :title="periodo?.id ? 'Guardar Cambios' : 'Crear Periodo'"
            :loading-title="periodo?.id ? 'Guardando...' : 'Creando...'"
            :loading="saving || updating"
            key="submit-btn"
            @click="onSubmit"
            class="w-full"
          />

          <Button
            v-if="isEditing"
            title="Cancelar"
            variant="outline"
            @click="onCancelEdit"
            class="bg-red-500 text-white px-4 md:w-auto"
          />
        </div>
      </div>
    </div>
  </AuthorizationFallback>
</template>

