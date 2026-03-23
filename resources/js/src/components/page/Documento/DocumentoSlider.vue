<script setup>
import { ref, watch, computed } from "vue";
import flatPickr from 'vue-flatpickr-component';
import * as yup from "yup";

import FormInput from "../../ui/FormInput.vue";
import CheckBox from "../../ui/CheckBox.vue";
import Button from "../../ui/Button.vue";
import BaseSelectGrupo from "../../ui/BaseSelectGrupo.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useProgramacionSubidostore from "../../../store/Documento/useDocumentoSubidoStore";
import { createDatePickerConfig } from "../../../utils/datePickerConfig";

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

const datePickerConfig = createDatePickerConfig();

const tiposEntrega = [
  { id: "1", nombre: "Subida de unidades didácticas" },
  { id: "2", nombre: "Subida de sesiones" },
  { id: "3", nombre: "Subida de sílabo" },
  { id: "4", nombre: "Subida de materiales" },
  { id: "99", nombre: "Otro" },
];


yup.setLocale({
  mixed: {
    required: "Este campo es obligatorio.",
  },
  string: {
    email: "Debe ser un correo válido.",
  },
  date: {
    min: "La fecha no puede ser anterior a ${min}",
    max: "La fecha no puede ser posterior a ${max}",
    typeError: "Debe ser una fecha válida.",
  },
});


const schema = yup.object().shape({
  id_periodo: yup.string().required("El periodo es requerido."),
  tipo_entrega: yup.string().required("El tipo de entrega es requerido."),
  fecha_inicio: yup.date().required("La fecha de inicio es requerida.").typeError("Debe ingresar una fecha válida"),
  fecha_fin: yup.date()
    .required("La fecha de fin es requerida.")
    .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser anterior a la de inicio.")
    .typeError("Debe ingresar una fecha válida"),
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

      // 🔹 Asignar nombre_entrega correctamente
      if (formData.value.tipo_entrega === "99") {
        // Si es "Otro", usar el nombre real que viene de la BD
        formData.value.nombre_entrega = newVal.nombre_entrega || "";
      } else {
        const tipo = tiposEntrega.find(t => t.id === formData.value.tipo_entrega);
        formData.value.nombre_entrega = tipo ? tipo.nombre : "";
      }

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
    if (val !== "99") {
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
    const [year, month, day] = fecha.split("-");
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
      resetForm();
      showToast(`Programación ${isEditing.value ? "actualizada" : "creada"} con éxito.`, "success");
      emit("form-submitted");
      await programacionDocumento.loadgetProgramacionSubidos(props.selectedPeriodoId);

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
          <FormLabelError label="Periodo Académico *" :error="formErrors.id_periodo">
            <BaseSelectGrupo v-model="formData.id_periodo" :options="periodos" label="nombre_periodo" value-prop="id"
              placeholder="Seleccione un Periodo" />
          </FormLabelError>

        </div>

        <!-- ✅ Tipo de entrega -->
        <div>
          <FormLabelError label="Tipo de Entrega *" :error="formErrors.tipo_entrega">
            <BaseSelectGrupo v-model="formData.tipo_entrega" :options="tiposEntrega" label="nombre" value-prop="id"
              placeholder="Seleccione tipo de entrega" />
          </FormLabelError>

        </div>

        <!-- 👇 Campo dinámico si elige “Otro” -->
        <div v-if="formData.tipo_entrega == '99'">
          <FormInput v-model="formData.nombre_entrega" label="Nombre de la Entrega *"
            placeholder="Ej: Subida de proyectos, Entrega final, etc." :error="formErrors.nombre_entrega" />
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-2 gap-4">
          <FormLabelError label="Fecha de Inicio *" :error="formErrors.fecha_inicio">
            <flat-pickr
              v-model="formData.fecha_inicio"
              :config="datePickerConfig"
              class="h-10 w-full rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
            />
          </FormLabelError>

          <FormLabelError label="Fecha de Fin *" :error="formErrors.fecha_fin">
            <flat-pickr
              v-model="formData.fecha_fin"
              :config="datePickerConfig"
              class="h-10 w-full rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
            />
          </FormLabelError>
        </div>

        <!-- Publicar -->


        <!-- Botones -->
        <div class="flex gap-2 pt-2">
          <Button :title="isEditing ? 'Guardar Cambios' : 'Crear Programación'" type="submit"
            :loading="saving || updating" class="w-full" :disabled="saving || updating" />
          <Button v-if="isEditing" title="Cancelar" variant="outline" @click="resetForm" />
        </div>
      </form>
    </div>
  </AuthorizationFallback>
</template>
