<script setup>
import { computed, ref, watch } from "vue";
import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import VSelect from "vue-select";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";

import useAdminStore from "../../../store/Administrativo/useAdministrativoStore";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useUtils from "../../../composables/useUtils";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import SelectedChips from "../../ui/selectedChips.vue";
import CheckBox from "../../ui/CheckBox.vue";
import BaseSelect from "../../ui/BaseSelect.vue";

const props = defineProps({
  show: { type: Boolean, default: () => false },
  admin: { type: [Object, null], default: () => null },
});
const emit = defineEmits(["hide"]);

const adminStore = useAdminStore();

const { store: createUser, saving, update: updateUser, updating } = useHttpRequest(
  "personal_administrativo"
);
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => {
  if (!props.admin?.id) return ["todo-acceso-usuarios", "crear-usuarios"];
  else return ["todo-acceso-usuarios", "editar-usuarios"];
});

const title = computed(() =>
  props.admin?.administrativo
    ? `Actualizar Local y Turno para "${props.admin?.name}"`
    : `Añadir Local y Turno para  "${props.admin?.name}"`
);

const initialFormData = () => ({
  id_usuario: props.admin?.id,
  turno: "",
  local: "",
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(
  () => props.show,
  () => {
    if (props.show) {
      //console.log("silder administrativo: ",props.admin)
      if (props.admin?.administrativo) {
        formData.value = Object.entries(initialFormData()).reduce(
          (r, [key, val]) => ({ ...r, [key]: props.admin?.administrativo[key] || val }),
          {}
        );
      } else {
        formData.value = initialFormData();
        formErrors.value = {};
      }
    }
  }
);

const schema = yup.object().shape({
  turno: yup.string(),
  local: yup.string(),
});

const onSubmit = async () => {
  if (saving.value || updating.value) return;
  let data = { ...formData.value };
  const { validated, errors } = await runYupValidation(schema, data);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  // console.log(formData.value)
  //console.log()
  const response = props.admin?.id
    ? await updateUser(props.admin?.id, data)
    : await createUser(data);

  if (response?.data?.id) {
    showToast(`Datos ${props.admin?.administrativo ? "actualizado" : "agregado"} correctamente.`);

    adminStore.loadUsers();
    emit("hide");
  }
};
</script>

<template>
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="requiredPermissions">
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

      <div class="mt-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormInput
            :model-value="formData.turno"
            label="Turno"
            :error="formErrors?.turno"
            @update:modelValue="(val) => (formData.turno = val.toUpperCase())"
          />

          <FormInput v-model="formData.local" label="Local" :error="formErrors?.local" />
        </div>

        <Button
          :title="admin?.administrativo ? 'Guardar Cambios' : 'Agregar Datos'"
          key="submit-btn"
          :loading-title="admin?.administrativo ? 'Guardando...' : 'Agregando...'"
          class="!mt-6 !w-full"
          :loading="saving || updating"
          @click="onSubmit"
        />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
