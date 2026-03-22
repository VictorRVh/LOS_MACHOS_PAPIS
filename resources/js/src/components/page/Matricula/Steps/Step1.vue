<script setup>
import { computed } from "vue";
import FormLabelError from "../../../ui/FormLabelError.vue";
import BaseSelectGrupo from "../../../ui/BaseSelectGrupo.vue";
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

const formatGroupName = (value) =>
  (value || "")
    .split("|")
    .map((part) => part.trim())
    .filter(Boolean)
    .join(" · ");
</script>

<template>
  <div class="space-y-2">
    <section class="grid gap-2 xl:grid-cols-[minmax(0,1.55fr)_minmax(250px,0.72fr)]">
      <div class="border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800/60">
        <div class="grid gap-3 lg:grid-cols-2">
          <div class="space-y-1.5">
            <FormLabelError label="Programa de estudio *" :error="errors.id_programa">
              <BaseSelectGrupo
                v-model="formData.id_programa"
                :options="programas"
                label="nameCiclo"
                value-prop="id"
                placeholder="Seleccione programa"
                @change="handleProgramaChange"
              />
            </FormLabelError>
          </div>

          <div class="space-y-1.5">
            <FormLabelError label="Especialidad *" :error="errors.id_especialidad">
              <BaseSelectGrupo
                v-model="formData.id_especialidad"
                :options="especialidadStore.especialidadPrograma"
                :disabled="!formData.id_programa"
                label="nombre_especialidad"
                value-prop="id"
                placeholder="Seleccione especialidad"
                :loading="especialidadStore.especialidadByCicloLoading"
                @change="handleEspecialidadChange"
              />
            </FormLabelError>
          </div>

          <div class="space-y-1.5 lg:col-span-2">
            <FormLabelError label="Grupo *" :error="errors.id_grupo">
              <BaseSelectGrupo
                v-model="formData.id_grupo"
                :options="especialidadStore.gruposDisponibles"
                :disabled="!formData.id_especialidad"
                label="nombre_grupo"
                value-prop="id"
                placeholder="Seleccione grupo"
                :loading="especialidadStore.grupoByEspecialidadLoading"
                @change="handleGrupoChange"
              />
            </FormLabelError>
          </div>
        </div>
      </div>

      <aside class="border border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900">
        <h4 class="text-[14px] font-semibold text-slate-900 dark:text-slate-100">
          {{ formatGroupName(nameGrupo) || "Aun no se ha definido un grupo" }}
        </h4>

        <dl class="mt-3 space-y-1">
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
