<script setup>
import { ref, watch, computed } from "vue";
import Slider from "../../ui/Slider.vue";
import FormInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import * as yup from "yup";
import useCapacidadTerminalStore from "../../../store/CapacidadTerminal/UseCapacidadTerminalStore";
import BaseSelectGrupo from "../../ui/BaseSelectGrupo.vue";
const props = defineProps({
  show: Boolean,
  fechasSeleccionadas: {
    type: Array,
    default: () => [],
  },
  blockToEdit: {
    type: Object,
    default: null,
  },
  idGrupo: {
    type: Array,
    default: () => [],
  },
});


const capacidadStore = useCapacidadTerminalStore();

if(props.idGrupo){
  if (!capacidadStore.capacidadTerminal?.length)
  await capacidadStore.loadCapacidadTerminal(props.idGrupo);
}


const emit = defineEmits(["hide", "save"]);

const { runYupValidation } = useValidation();
const { showToast } = useModalToast();
const { store: createSesion, update: updateSesion, saving, updating } = useHttpRequest("/sesion");

const isEditing = computed(() => !!props.blockToEdit?.id);

const initialForm = () => ({
  nombre_sesion: "",
  id_capacidad: "",
  descripcion: "",
  archivo_sesion: null,
});

const form = ref(initialForm());
const formErrors = ref({});
const inputFile = ref(null);

// Reset o carga de datos al abrir el slider
watch(
  () => props.show,
  (visible) => {
    if (visible) {
      if (isEditing.value && props.blockToEdit) {
        form.value = {
          nombre_sesion: props.blockToEdit.title?.replace(/^Sesión:\s*/, "") || "",
          descripcion: props.blockToEdit.description || "",
          archivo_sesion: null,
        };
      } else {
        form.value = initialForm();
        if (inputFile.value) inputFile.value.value = "";
      }
      formErrors.value = {};
    }
  }
);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  form.value.archivo_sesion = file || null;
};

const schema = yup.object({
  nombre_sesion: yup
    .string()
    .required("El tema de la sesión es obligatorio.")
    .min(3, "Debe tener al menos 3 caracteres."),
  descripcion: yup.string().nullable(),
  id_capacidad: yup
    .string()
    .required("Debe seleccionar una capacidad terminal.")
});


const handleSubmit = async () => {
  if (saving.value || updating.value) return;
  formErrors.value = {};

  const { validated, errors } = await runYupValidation(schema, form.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  const formData = new FormData();
  formData.append("nombre_sesion", form.value.nombre_sesion);
  formData.append("descripcion", form.value.descripcion || "");
  if (form.value.archivo_sesion) {
    formData.append("archivo_sesion", form.value.archivo_sesion);
  }
  formData.append("fechas", JSON.stringify(props.fechasSeleccionadas));

  const response = isEditing.value
    ? await updateSesion(props.blockToEdit.id, formData)
    : await createSesion(formData);

  if (response?.id) {
    showToast(`Sesión ${isEditing.value ? "actualizada" : "creada"} correctamente.`);
    emit("save", response);
    emit("hide");
    form.value = initialForm();
    formErrors.value = {};
  }
};

</script>

<template>
  <Slider :show="show" @hide="emit('hide')" :title="isEditing ? 'Editar Sesión' : 'Programar Sesiones'">
    <form @submit.prevent="handleSubmit" class="p-4 space-y-4 font-inter">
      <div>
        <FormLabelError label="Capacidad terminal *" :error="formErrors.id_capacidad">
          <BaseSelectGrupo v-model="form.id_capacidad" :options="capacidadStore.capacidadTerminal"
            label="nombre_capacidad" value-prop="id" placeholder="Seleccione una capacidad" />

        </FormLabelError>

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Fechas seleccionadas
        </label>
        <div class="mt-2 flex flex-wrap gap-2">
          <span v-for="fecha in fechasSeleccionadas" :key="fecha"
            class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full dark:bg-blue-900 dark:text-blue-300">
            {{
              new Date(fecha + "T00:00:00").toLocaleDateString("es-PE", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric",
              })
            }}
          </span>
        </div>
      </div>

      <FormInput v-model="form.nombre_sesion" label="Tema de la Sesión" :error="formErrors?.nombre_sesion" required />

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Descripción (opcional)
        </label>
        <textarea v-model="form.descripcion" rows="4"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
        <FormLabelError v-if="formErrors?.descripcion" :error="formErrors.descripcion" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Archivo de la Sesión (opcional)
        </label>
        <input ref="inputFile" type="file" @change="handleFileChange"
          class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600" />
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <Button :title="isEditing ? 'Guardar Cambios' : 'Guardar Sesiones'" :loading="saving || updating"
          :disabled="saving || updating" class="w-full md:w-auto" />
        <Button title="Cancelar" variant="outline" @click="emit('hide')" class="bg-red-500 text-white" />
      </div>
    </form>
  </Slider>
</template>

