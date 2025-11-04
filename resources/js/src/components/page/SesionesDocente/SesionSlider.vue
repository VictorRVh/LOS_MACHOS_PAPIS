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
import useProgramacionStore from "../../../store/Sesion/useProgramacionDocenteStore"


const props = defineProps({
  show: Boolean,
  fechasSeleccionadas: Array,
  blockToEdit: Object,
  idGrupo: [String, Number],
  sesion: Object,
});

const emit = defineEmits(["hide", "save"]);

const capacidadStore = useCapacidadTerminalStore();
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();
const {
  store: createSesion,
  update: updateSesion,
  saving,
  updating,
} = useHttpRequest("/programacion_sesion_docente");

// Estado para edición
const isEditing = computed(() => !!props.blockToEdit?.id);
const programacionSesion = useProgramacionStore();
// Formulario y errores
const initialForm = () => ({
  nombre_sesion: "",
  id_capacidad: "",
  id_entrega: props.sesion?.id || "",
  descripcion: "",
  archivo_sesion: null,
});

const form = ref(initialForm());
const formErrors = ref({});
const inputFile = ref(null);

// 🔍 Solo cargamos capacidades y llenamos datos cuando el modal se abre
watch(
  () => props.show,
  async (isVisible) => {
    if (isVisible) {
      await capacidadStore.loadCapacidadTerminal(props.idGrupo);

      if (isEditing.value) {
        form.value = {
          ...initialForm(),
          ...props.blockToEdit,
        };

        if (!Array.isArray(form.value.calendario_admin)) {
          form.value.calendario_admin = [];
        }
      } else {
        form.value = initialForm();
      }

      formErrors.value = {};
    }
  }
);

const schema = yup.object({
  nombre_sesion: yup
    .string()
    .required("El tema de la sesión es obligatorio.")
    .min(3, "Debe tener al menos 3 caracteres."),
  descripcion: yup.string().nullable(),
  id_capacidad: yup
    .string()
    .required("Debe seleccionar una capacidad terminal."),
  id_entrega: yup.string().required("Debe asignar una entrega válida."),
});

const onSubmit = async () => {
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
  formData.append("id_capacidad", form.value.id_capacidad);
  formData.append("id_entrega", form.value.id_entrega);

  // ✅ Enviar array correctamente
  props.fechasSeleccionadas.forEach((fecha, index) => {
    formData.append(`fechas[${index}]`, fecha);
  });

  if (form.value.archivo_sesion) {
    formData.append("archivo_sesion", form.value.archivo_sesion);
  }

  const response = isEditing.value
    ? await updateSesion(props.blockToEdit.id, formData)
    : await createSesion(formData);

  console.log("response: ", response);

  if (response?.sesion?.id) {
    closeAndReset();
    await programacionSesion.loadSesiones(props.sesion?.id);
    showToast(
      `Sesión ${isEditing.value ? "actualizada" : "creada"} correctamente.`
    );
    emit("save", response);

    // 🔄 Limpiar selección visual en calendario desde aquí con un evento emitido:
    emit('clear-selection');
  }

};

// Reset general del modal
const closeAndReset = () => {
  form.value = initialForm();
  formErrors.value = {};
  emit("hide");
};
</script>

<template>
  <Slider :show="show" @hide="closeAndReset" :title="isEditing ? 'Editar Sesión' : 'Programar Sesiones'">
    <!-- CAPACIDAD TERMINAL -->
    <FormLabelError label="Capacidad terminal *" :error="formErrors.id_capacidad">
      <BaseSelectGrupo v-model="form.id_capacidad" :options="capacidadStore.capacidadTerminal" label="nombre_capacidad"
        value-prop="id" placeholder="Seleccione una capacidad" :loading="capacidadStore.sesionesLoading" />
    </FormLabelError>

    <!-- FECHAS -->
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fechas seleccionadas</label>
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

    <!-- TEMA -->
    <FormInput v-model="form.nombre_sesion" label="Tema de la Sesión" :error="formErrors?.nombre_sesion" required />

    <!-- DESCRIPCIÓN -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (opcional)</label>
      <textarea v-model="form.descripcion" rows="4"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
      <FormLabelError v-if="formErrors?.descripcion" :error="formErrors.descripcion" />
    </div>

    <!-- BOTONES -->
    <div class="flex gap-2 mt-5">
      <Button :title="blockToEdit?.id ? 'Guardar Cambios' : 'Crear programación'" key="submit-btn"
        :disabled="saving || updating" :loading-title="blockToEdit?.id ? 'Guardando...' : 'Creando...'" class="!w-full"
        :loading="saving || updating" @click="onSubmit" />

      <Button title="Cancelar" variant="outline" @click="closeAndReset"
        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
    </div>
  </Slider>
</template>
