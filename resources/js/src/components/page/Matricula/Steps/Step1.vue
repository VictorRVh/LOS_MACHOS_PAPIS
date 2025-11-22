<script setup>
import { computed } from 'vue';
import FormInput from '../../../ui/FormInput.vue';
import FormLabelError from '../../../ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { Bars3Icon } from '@heroicons/vue/24/outline';
import useEspecialidadStore from '../../../../store/Especialidad/useEspecialidadStore';

const props = defineProps({
  modelValue: { type: Object, required: true },
  programas: { type: Object, required: true },
  nameGrupo: { type: String, required: true }
});

const emit = defineEmits(['update:modelValue', 'cambiarVariable']);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const especialidadStore = useEspecialidadStore();

const handleProgramaChange = (programaId) => {
  formData.value.id_especialidad = null;
  formData.value.id_grupo = null;
  resetGrupoData();
  especialidadStore.especialidadPrograma = [];
  if (programaId) { especialidadStore.loadEspecialidadPrograma(programaId); }
};

const handleEspecialidadChange = (especialidadId) => {
  console.log('entrando aca', especialidadId)
  formData.value.id_grupo = null;
  especialidadStore.gruposDisponibles = [];
  if (especialidadId) { especialidadStore.loadGrupoEspecialidad(especialidadId); }
  resetGrupoData();
};

const handleGrupoChange = (grupoId) => {
  const grupoSeleccionado = especialidadStore.gruposDisponibles.find(g => g.id === grupoId);

  if (grupoSeleccionado) {
    // guardar detalles en el formData
    formData.value.convenio = grupoSeleccionado.convenio || '';
    formData.value.duracion = grupoSeleccionado.duracion || '';
    formData.value.horas = grupoSeleccionado.horas || '';
    formData.value.turno = grupoSeleccionado.turno || '';
    formData.value.seccion = grupoSeleccionado.seccion || '';

    // 🔹 emitir el nombre del grupo al padre
    emit('cambiarVariable', grupoSeleccionado.nombre_grupo);
  } else {
    resetGrupoData();
  }
};

const resetGrupoData = () => {
  formData.value.convenio = '';
  formData.value.duracion = '';
  formData.value.horas = '';
  formData.value.turno = '';
  formData.value.seccion = '';
};
</script>

<template>
  <div>
    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
      <Bars3Icon class="h-6 w-6" />
      DATOS ACADÉMICOS
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Programa -->
      <FormLabelError label="Programa de Estudio *">
        <v-select v-model="formData.id_programa" :options="programas" label="nameCiclo" :reduce="p => p.id"
          placeholder="Seleccione Programa" @update:modelValue="handleProgramaChange" />
      </FormLabelError>

      <!-- Especialidad -->
      <FormLabelError label="Especialidad *">
        <v-select v-model="formData.id_especialidad" :options="especialidadStore.especialidadPrograma"
          :disabled="!formData.id_programa" label="nombre_especialidad" :reduce="e => e.id"
          placeholder="Seleccione Especialidad" @update:modelValue="handleEspecialidadChange" :loading="especialidadStore.especialidadByCicloLoading" />
      </FormLabelError>

      <!-- Grupo -->
      <FormLabelError label="Grupo *">
        <v-select v-model="formData.id_grupo" :options="especialidadStore.gruposDisponibles"
          :disabled="!formData.id_especialidad" label="nombre_grupo" :reduce="g => g.id" placeholder="Seleccionar Grupo"
          @update:modelValue="handleGrupoChange" :loading="especialidadStore.grupoByEspecialidadLoading" />
      </FormLabelError>
    </div>

    <!-- Campos extra -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6 mt-6">
      <FormInput v-model="formData.convenio" label="Convenio" disabled />
      <FormInput v-model="formData.duracion" label="Duración" disabled />
      <FormInput v-model="formData.horas" label="Horas" disabled />
      <FormInput v-model="formData.turno" label="Turno" disabled />
      <FormInput v-model="formData.seccion" label="Sección" disabled />
    </div>
  </div>
</template>
