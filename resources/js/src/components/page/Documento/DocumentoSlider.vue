<script setup>
import { ref, watch, computed } from "vue";
import * as yup from "yup";

import FormInput from "../../ui/FormInput.vue";
import CheckBox from "../../ui/CheckBox.vue";
import Button from "../../ui/Button.vue";
import BaseSelectGrupo from "../../ui/BaseSelectGrupo.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useProgramacionSubidostore from "../../../store/Documento/useDocumentoSubidoStore";

const props = defineProps({
  programacionToEdit: {
    type: Object,
    default: null,
  },
  periodos: {
    type: Array,
    required: true,
  },
  selectedPeriodoId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(["form-submitted", "cancel-edit"]);

const { showToast } = useModalToast();
const { runYupValidation } = useValidation();
const { store, update, saving, updating } = useHttpRequest("/entrega_docente_admin");

const isEditing = ref(false);
const formErrors = ref({});
const programacionDocumento = useProgramacionSubidostore();

const initialFormData = () => ({
  id_periodo: "",
  nombre_entrega: "",
  tipo_entrega: "",
  fecha_inicio: "",
  fecha_fin: "",
});

const formData = ref(initialFormData());

// ✅ Opciones estáticas + "Otro"
const tiposEntrega = [
  { id: "1", nombre: "Subida de notas" },
  { id: "2", nombre: "Subida de sesiones" },
  { id: "3", nombre: "Subida de sílabo" },
  { id: "4", nombre: "Subida de materiales" },
  { id: "99", nombre: "Otro" },
];

const schema = yup.object().shape({
  id_periodo: yup.string().required("El periodo es requerido."),
  tipo_entrega: yup.string().required("El tipo de entrega es requerido."),
  fecha_inicio: yup.date().required("La fecha de inicio es requerida."),
  fecha_fin: yup
    .date()
    .required("La fecha de fin es requerida.")
    .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior a la de inicio."),
  // Si el tipo es "Otro", nombre_entrega es obligatorio
  nombre_entrega: yup.string().when("tipo_entrega", {
    is: (val) => val == "99",
    then: (schema) => schema.required("Debe ingresar el nombre de la entrega."),
  }),
});

const requiredPermissions = computed(() => {
  return props.comision?.id
    ? ["todo-acceso-documento-programado", "editar-documento-programado"]
    : ["todo-acceso-documento-programado", "crear-documento-programado"];
});

// 🧩 Watch para llenar datos si se edita
watch(
  () => props.programacionToEdit,
  (newVal) => {
    if (newVal) {
      // 🔹 Copiar valores base
      formData.value = Object.entries(initialFormData()).reduce((r, [key, val]) => {
        if (newVal[key]) return { ...r, [key]: newVal[key] };
        return { ...r, [key]: val };
      }, {});

      // 🔹 Autoasignar periodo actual
      formData.value.id_periodo = props.selectedPeriodoId;

      // 🔹 Limpiar hora de las fechas
      const takeDateOnly = (str) => {
        if (!str) return "";
        const datePart = String(str).split(" ")[0].split("T")[0];
        if (datePart.includes("/")) {
          const [d, m, y] = datePart.split("/");
          return `${y}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
        }
        return datePart;
      };

      formData.value.fecha_inicio = takeDateOnly(newVal.fecha_inicio);
      formData.value.fecha_fin = takeDateOnly(newVal.fecha_fin);

      // 🔹 Si el tipo_entrega viene como número o string, asegurar formato correcto
      formData.value.tipo_entrega = String(newVal.tipo_entrega);

      // 🔹 Buscar el nombre correspondiente según tipo_entrega
      const tipo = tiposEntrega.find(t => t.id === formData.value.tipo_entrega);
      formData.value.nombre_entrega = tipo ? tipo.nombre : (newVal.nombre_entrega || "");

      isEditing.value = true;
      formErrors.value = {};
    } else {
      resetForm();
    }
  },
  { deep: true }
);



// 🧹 Reset
const resetForm = () => {
  formData.value = initialFormData();
  isEditing.value = false;
  formErrors.value = {};
  emit("cancel-edit");
};

// ✅ Cuando cambia el tipo de entrega
watch(
  () => formData.value.tipo_entrega,
  (val) => {
    if (val == "99") {
      // Otro → dejar nombre_entrega vacío para que lo escriba
      formData.value.nombre_entrega = "";
    } else {
      // Valor normal → asignar el nombre automáticamente
      const tipo = tiposEntrega.find((t) => t.id === val);
      formData.value.nombre_entrega = tipo ? tipo.nombre : "";
    }
  }
);

// 📨 Enviar formulario
const onSubmit = async () => {
  const data = { ...formData.value };

  const { validated, errors } = await runYupValidation(schema, data);
  if (!validated) {
    formErrors.value = errors;
    return;
  }
  formErrors.value = {};

  const now = new Date();
  const horaActual = now.toTimeString().slice(0, 8);
  const horaFin = "23:59:59";

  const formatDateTime = (fecha, hora) => {
    const date = new Date(fecha);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day} ${hora}`;
  };

  data.fecha_inicio = formatDateTime(data.fecha_inicio, horaActual);
  data.fecha_fin = formatDateTime(data.fecha_fin, horaFin);

  try {
    let response;
    if (isEditing.value) {
      response = await update(props?.programacionToEdit?.id, data);
    } else {
      response = await store(data);
    }

    if (response) {
      showToast(`Programación ${isEditing.value ? "actualizada" : "creada"} con éxito.`, "success");
      emit("form-submitted");
      await programacionDocumento.loadgetProgramacionSubidos(props.selectedPeriodoId);
      resetForm();
    }
  } catch (error) {
    console.log(error)
    const msg = error.response?.data?.message || 'Error al subir el archivo';
    showToast(msg, 'error');
  }
};
</script>

<template>
  <AuthorizationFallback :permissions="requiredPermissions">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
      <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
        {{ isEditing ? "Editar Programación" : "Nueva Programación" }}
      </h3>
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

      <form @submit.prevent="onSubmit" class="space-y-4">
        <!-- Periodo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periodo Académico</label>
          <BaseSelectGrupo v-model="formData.id_periodo" :options="periodos" label="nombre_periodo" value-prop="id"
            placeholder="Seleccione un Periodo" />
        </div>

        <!-- ✅ Tipo de entrega -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Entrega *</label>
          <BaseSelectGrupo v-model="formData.tipo_entrega" :options="tiposEntrega" label="nombre" value-prop="id"
            placeholder="Seleccione tipo de entrega" :error-message="formErrors.tipo_entrega" />
        </div>

        <!-- 👇 Campo dinámico si elige “Otro” -->
        <div v-if="formData.tipo_entrega == '99'">
          <FormInput v-model="formData.nombre_entrega" label="Nombre de la Entrega *"
            placeholder="Ej: Subida de proyectos, Entrega final, etc." :error-message="formErrors.nombre_entrega" />
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-2 gap-4">
          <FormInput v-model="formData.fecha_inicio" label="Fecha de Inicio *" type="date"
            :error-message="formErrors.fecha_inicio" />
          <FormInput v-model="formData.fecha_fin" label="Fecha de Fin *" type="date"
            :error-message="formErrors.fecha_fin" />
        </div>

        <!-- Publicar -->
      

        <!-- Botones -->
        <div class="flex gap-2 pt-2">
          <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Programación'" type="submit"
            :loading="saving || updating" class="w-full" />
          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="resetForm" />
        </div>
      </form>
    </div>
  </AuthorizationFallback>
</template>
