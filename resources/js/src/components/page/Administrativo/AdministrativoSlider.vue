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

import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';


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
  if (!props.admin?.id) return ["todo-acceso-administrativos", "crear-administrativos"];
  else return ["todo-acceso-administrativos", "editar-administrativos"];
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

const turnos = [
  { value: 'MAÑANA', name: 'MAÑANA' },
  { value: 'TARDE', name: 'TARDE' },
  { value: 'NOCHE', name: 'NOCHE' }
];


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
      }
      formErrors.value = {};
    }
  }
);

const schema = yup.object().shape({
  turno: yup.string().nullable(),
  local: yup.string().nullable(),
}).test({
  name: "turno-o-local",
  message: "Debe ingresar al menos un turno o un local",
  test(value, ctx) {
    const hasTurno = value.turno && value.turno.trim() !== "";
    const hasLocal = value.local && value.local.trim() !== "";
    if (!hasTurno && !hasLocal) {
      // asigna el error a "turno" en vez de _error
      return ctx.createError({ path: "turno" });
    }
    return true;
  }
});




const onSubmit = async () => {
  if (saving.value || updating.value) return;
  let data = {
    ...formData.value,
    turno: formData.value.turno?.value || null, // 👈 aquí
  };

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
formData.value = initialFormData();
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

          <FormLabelError label="Turno" required :error="formErrors?.turno">
            <BaseSelectGrupo v-model="formData.turno" :options="turnos" la placeholder="Seleccione un turno" />
          </FormLabelError>

          <FormInput v-model="formData.local" label="Local" :error="formErrors?.local" />
        </div>

        <Button :title="admin?.administrativo ? 'Guardar Cambios' : 'Agregar Datos'" key="submit-btn"
          :loading-title="admin?.administrativo ? 'Guardando...' : 'Agregando...'" class="!mt-6 !w-full"
          :loading="saving || updating" @click="onSubmit" />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
