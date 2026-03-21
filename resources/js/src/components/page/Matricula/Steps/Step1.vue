<script setup>
import { computed } from "vue";
import FormInput from "../../../ui/FormInput.vue";
import FormLabelError from "../../../ui/FormLabelError.vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import useEspecialidadStore from "../../../../store/Especialidad/useEspecialidadStore";

const props = defineProps({
  modelValue: { type: Object, required: true },
  programas: { type: Object, required: true },
  nameGrupo: { type: String, required: true },
  errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:modelValue", "cambiarVariable"]);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const especialidadStore = useEspecialidadStore();

const handleProgramaChange = (programaId) => {
  formData.value.id_especialidad = null;
  formData.value.id_grupo = null;
  resetGrupoData();
  especialidadStore.especialidadPrograma = [];

  if (programaId) {
    especialidadStore.loadEspecialidadPrograma(programaId);
  }
};

const handleEspecialidadChange = (especialidadId) => {
  formData.value.id_grupo = null;
  especialidadStore.gruposDisponibles = [];

  if (especialidadId) {
    especialidadStore.loadGrupoEspecialidad(especialidadId);
  }

  resetGrupoData();
};

const handleGrupoChange = (grupoId) => {
  const grupoSeleccionado = especialidadStore.gruposDisponibles.find((g) => g.id === grupoId);

  if (grupoSeleccionado) {
    formData.value.convenio = grupoSeleccionado.convenio || "";
    formData.value.duracion = grupoSeleccionado.duracion || "";
    formData.value.horas = grupoSeleccionado.horas || "";
    formData.value.turno = grupoSeleccionado.turno || "";
    formData.value.seccion = grupoSeleccionado.seccion || "";
    emit("cambiarVariable", grupoSeleccionado.nombre_grupo);
  } else {
    resetGrupoData();
  }
};

const resetGrupoData = () => {
  formData.value.convenio = "";
  formData.value.duracion = "";
  formData.value.horas = "";
  formData.value.turno = "";
  formData.value.seccion = "";
  emit("cambiarVariable", "");
};
</script>

<template>
  <div class="space-y-3">
    <section class="border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800/60">
      <div class="flex flex-col gap-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
          Configuracion academica
        </p>
        <h3 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">
          Seleccione programa, especialidad y grupo
        </h3>
      </div>

      <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-3">
        <FormLabelError label="Programa de estudio *" :error="errors.id_programa">
          <v-select
            v-model="formData.id_programa"
            :options="programas"
            label="nameCiclo"
            :reduce="(p) => p.id"
            placeholder="Seleccione programa"
            @update:modelValue="handleProgramaChange"
          />
        </FormLabelError>

        <FormLabelError label="Especialidad *" :error="errors.id_especialidad">
          <v-select
            v-model="formData.id_especialidad"
            :options="especialidadStore.especialidadPrograma"
            :disabled="!formData.id_programa"
            label="nombre_especialidad"
            :reduce="(e) => e.id"
            placeholder="Seleccione especialidad"
            :loading="especialidadStore.especialidadByCicloLoading"
            @update:modelValue="handleEspecialidadChange"
          />
        </FormLabelError>

        <FormLabelError label="Grupo *" :error="errors.id_grupo">
          <v-select
            v-model="formData.id_grupo"
            :options="especialidadStore.gruposDisponibles"
            :disabled="!formData.id_especialidad"
            label="nombre_grupo"
            :reduce="(g) => g.id"
            placeholder="Seleccione grupo"
            :loading="especialidadStore.grupoByEspecialidadLoading"
            @update:modelValue="handleGrupoChange"
          />
        </FormLabelError>
      </div>
    </section>

    <section class="border border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900">
      <div class="flex flex-col gap-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
          Resumen del grupo
        </p>
        <h3 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">
          Datos operativos de la seccion seleccionada
        </h3>
      </div>

      <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-5">
        <FormInput v-model="formData.convenio" label="Convenio" disabled />
        <FormInput v-model="formData.duracion" label="Duracion" disabled />
        <FormInput v-model="formData.horas" label="Horas" disabled />
        <FormInput v-model="formData.turno" label="Turno" disabled />
        <FormInput v-model="formData.seccion" label="Seccion" disabled />
      </div>

      <div class="mt-3 border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/70">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
          Grupo seleccionado
        </p>
        <p class="mt-1 text-[13px] font-medium text-slate-800 dark:text-slate-100">
          {{ nameGrupo || "Aun no se ha seleccionado un grupo." }}
        </p>
      </div>
    </section>
  </div>
</template>
