<script setup>
import { computed } from "vue";
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
  if (programaId) especialidadStore.loadEspecialidadPrograma(programaId);
};

const handleEspecialidadChange = (especialidadId) => {
  formData.value.id_grupo = null;
  especialidadStore.gruposDisponibles = [];
  if (especialidadId) especialidadStore.loadGrupoEspecialidad(especialidadId);
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

const readonlyItems = computed(() => [
  { label: "Convenio", value: formData.value.convenio },
  { label: "Duracion", value: formData.value.duracion },
  { label: "Horas", value: formData.value.horas },
  { label: "Turno", value: formData.value.turno },
  { label: "Seccion", value: formData.value.seccion },
]);
</script>

<template>
  <div class="space-y-2">
    <section class="grid gap-2 xl:grid-cols-[minmax(0,1.55fr)_minmax(250px,0.72fr)]">
      <div class="border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800/60">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
          Seleccion guiada
        </p>
        <h4 class="mt-0.5 text-[14px] font-semibold text-slate-900 dark:text-slate-100">
          Secuencia academica del registro
        </h4>
        <p class="mt-0.5 text-[12px] leading-5 text-slate-500 dark:text-slate-400">
          Seleccione programa, especialidad y grupo.
        </p>

        <div class="mt-3 grid gap-3 lg:grid-cols-3">
          <div class="space-y-1.5">
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
          </div>

          <div class="space-y-1.5">
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
          </div>

          <div class="space-y-1.5">
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
        </div>
      </div>

      <aside class="border border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
          Grupo seleccionado
        </p>
        <h4 class="mt-0.5 text-[14px] font-semibold text-slate-900 dark:text-slate-100">
          {{ nameGrupo || "Aun no se ha definido un grupo" }}
        </h4>
        <p class="mt-0.5 text-[12px] leading-5 text-slate-500 dark:text-slate-400">
          Datos de referencia del grupo.
        </p>

        <dl class="mt-2 space-y-1">
          <div
            v-for="item in readonlyItems"
            :key="item.label"
            class="flex items-start justify-between gap-3 border-b border-slate-200 py-1.5 last:border-b-0 dark:border-slate-700"
          >
            <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
              {{ item.label }}
            </dt>
            <dd class="text-right text-[12px] font-medium text-slate-800 dark:text-slate-100">
              {{ item.value || "No definido" }}
            </dd>
          </div>
        </dl>
      </aside>
    </section>
  </div>
</template>

<style scoped>
:deep(.vs__dropdown-toggle) {
  @apply h-9 min-h-9 rounded-[3px] border border-slate-300 bg-white px-3 py-0 text-sm text-slate-800 shadow-none transition-colors duration-150;
}

:deep(.vs--open .vs__dropdown-toggle),
:deep(.vs--focused .vs__dropdown-toggle) {
  @apply border-cetpro ring-2 ring-cetpro/15;
}

:deep(.vs__selected-options) {
  @apply min-h-0 flex-nowrap items-center gap-1 py-0;
}

:deep(.vs__selected) {
  @apply m-0 leading-5 text-sm text-slate-800;
}

:deep(.vs__search) {
  @apply m-0 leading-5 bg-transparent text-slate-800;
}

:deep(.vs__search::placeholder) {
  @apply text-sm leading-5 text-slate-400;
}

:deep(.vs__actions) {
  @apply items-center pr-0.5;
}

:deep(.vs__open-indicator) {
  @apply text-slate-500 transition-transform duration-150;
}

:deep(.vs--open .vs__open-indicator) {
  transform: rotate(180deg);
}

:deep(.vs__clear) {
  @apply text-slate-400 transition-colors duration-150;
}

:deep(.vs__dropdown-menu) {
  @apply mt-1 rounded-sm border border-slate-200 bg-white py-1 text-sm text-slate-800 shadow-sm;
}

:deep(.vs__dropdown-option) {
  @apply px-3 py-2 text-sm text-slate-700;
}

:deep(.vs__dropdown-option--highlight) {
  @apply bg-cetpro/10 text-cetpro;
}

:deep(.vs__dropdown-option--selected) {
  @apply bg-cetpro/10 font-medium text-cetpro;
}

:deep(.vs--disabled .vs__dropdown-toggle) {
  @apply cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400;
}

:deep(.dark .vs__dropdown-toggle) {
  @apply border-slate-600 bg-slate-800 text-slate-100;
}

:deep(.dark .vs--open .vs__dropdown-toggle),
:deep(.dark .vs--focused .vs__dropdown-toggle) {
  @apply border-cetpro-light ring-cetpro-light/20;
}

:deep(.dark .vs__selected) {
  @apply text-slate-100;
}

:deep(.dark .vs__search) {
  @apply bg-transparent text-slate-100;
}

:deep(.dark .vs__search::placeholder) {
  @apply text-slate-400;
}

:deep(.dark .vs__open-indicator) {
  @apply text-slate-400;
}

:deep(.dark .vs__clear) {
  @apply text-slate-500;
}

:deep(.dark .vs__dropdown-menu) {
  @apply border-slate-700 bg-slate-900 text-slate-100;
}

:deep(.dark .vs__dropdown-option) {
  @apply text-slate-200;
}

:deep(.dark .vs__dropdown-option--highlight) {
  @apply bg-cetpro-light/10 text-cetpro-light;
}

:deep(.dark .vs__dropdown-option--selected) {
  @apply bg-cetpro-light/10 text-cetpro-light;
}

:deep(.dark .vs--disabled .vs__dropdown-toggle) {
  @apply border-slate-700 bg-slate-900 text-slate-500;
}
</style>
