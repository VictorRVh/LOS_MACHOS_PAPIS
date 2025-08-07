<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';

import useDocenteStore from '../../../store/Docente/useDocenteStore';
import useProgramaStore from '../../../store/Programa/useProgramaStore';
import useGrupoStore from '../../../store/Grupo/useGrupoStore';
import useConvenioStore from '../../../store/Convenio/useConvenioStore';

import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';
import BaseSelectCiclo from '../../ui/BaseSelectCiclo.vue';

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import useCicloStore from '../../../store/Ciclo/useCicloStore';
import usePeriodoStore from '../../../store/Periodo/usePeriodoStore'

const props = defineProps({
    show: { type: Boolean, default: () => false },
    grupo: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide']);

const docenteStore = useDocenteStore();
const programaStore = useProgramaStore();
const grupoStore = useGrupoStore();
const convenioStore = useConvenioStore();
const cicloStore = useCicloStore();
const periodoStore = usePeriodoStore();

const { store: createGrupo, saving, update: updateGrupo, updating } = useHttpRequest('/grupo');
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

if (!programaStore.programa.length) await programaStore.loadPrograma();
const programas = ref(
  programaStore?.programa?.programas?.map(p => ({
    id: p.id,
    name: p.nameCiclo,
  }))
);

if (!convenioStore.convenios.length) await convenioStore.loadConvenios();
if (!docenteStore.docentes?.length) await docenteStore.loadDocentes();
if (!docenteStore.docentesGrupo?.length) await docenteStore.loadDocentesGrupo();
if (!cicloStore.ciclo?.length) await cicloStore.loadCiclo();
if (!periodoStore.periodos?.length) await periodoStore.loadPeriodos();

const title = computed(() =>
  props.grupo ? `Actualizar Docente "${props.grupo?.name}"` : 'Añadir Nuevo  Docente'
);

const initialFormData = () => ({
  id_programa: null,
  id_especialidad: null,
  id_modulo: null,
  id_periodo: null,
  id_convenio: null,
  fecha_inicio: null,
  fecha_fin: null,
  fecha_entrega_acta: null,
  seccion: null,
  turno: null,
  id_docente: null,
  status: 0,
});

const formData = ref(initialFormData());
const formErrors = ref({});

watch(() => props.show, () => {
  if (props.show) {
    if (props.grupo?.id) {
      formData.value = Object.entries(initialFormData()).reduce(
        (r, [key, val]) => ({ ...r, [key]: props.grupo[key] || val }),
        {}
      );
    } else {
      formData.value = initialFormData();
      formErrors.value = {};
    }
  }
});

const onProgramaChange = async (programaId) => {
  formData.value.id_especialidad = null;
  formData.value.id_modulo = null;
  formData.value.id_periodo = null;
  await grupoStore.loadEspecialidades(programaId);
};

const onEspecialidadChange = async (especialidadId) => {
  formData.value.id_modulo = null;
  formData.value.id_periodo = null;
  await grupoStore.loadModulos(especialidadId);
};

const onSubmit = async () => {
  if (saving.value || updating.value) return;

  let data = {
    ...formData.value,
  };

  const response = props.grupo?.id
    ? await updateGrupo(props.grupo?.id, data)
    : await createGrupo(data);

  if (response?.data.id) {
    showToast(`Grupo ${props.grupo?.id ? "editado" : "creado"} exitosamente.`);
    grupoStore.loadGrupos();

    if (!props.grupo?.id) {
      formData.value = initialFormData();
      formErrors.value = {};
    }
    emit("hide");
  }
};
</script>

<template>
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="['todo-acceso-usuarios', grupo?.id ? 'editar-usuarios' : 'crear-usuarios']">
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

      <div class="mt-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormLabelError label="Programa" required>
            <BaseSelectGrupo
              v-model="formData.id_programa"
              :options="programas"
              label="name"
              placeholder="Seleccione un programa"
              @change="onProgramaChange"
              :loading="grupoStore.especialidadByProgramLoading"
            />
          </FormLabelError>

          <FormLabelError label="Especialidad" required>
            <BaseSelectGrupo
              v-model="formData.id_especialidad"
              :options="grupoStore.especialidades"
              label="nombre_especialidad"
              placeholder="Seleccione una especialidad"
              @change="onEspecialidadChange"
              :loading="grupoStore.moduloByEspecialidadLoading"
            />
          </FormLabelError>

          <FormLabelError label="Módulos" required>
            <BaseSelectGrupo
              v-model="formData.id_modulo"
              :options="grupoStore.modulos"
              label="nombre_modulo"
              placeholder="Seleccione un módulo"
              @change="onModuloChange"
              :loading="grupoStore.docenteperiodoByModuloLoading"
            />
          </FormLabelError>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormLabelError label="Periodo" required>
            <BaseSelectCiclo v-model="formData.id_periodo" :options="periodoStore.periodos" label="nombre_periodo"
          placeholder="Seleccione un ciclo" />
          </FormLabelError>

          <FormLabelError label="Convenio" required>
            <BaseSelectCiclo
              v-model="formData.id_convenio"
              :options="convenioStore.convenios"
              label="nombre_institucion"
              placeholder="Seleccione un convenio"
            />
          </FormLabelError>

          <FormLabelError label="Docente">
            <BaseSelectCiclo
              v-model="formData.id_docente"
              :options="docenteStore.docentesGrupo"
              label="nombre"
              placeholder="Seleccione un docente"
            />
          </FormLabelError>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput v-model="formData.fecha_inicio" label="Fecha Inicio" type="date" />
          <FormInput v-model="formData.fecha_fin" label="Fecha Fin" type="date" />
          <FormInput v-model="formData.fecha_entrega_acta" label="Entrega Acta" type="date" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput v-model="formData.seccion" label="Sección" />
          <FormInput v-model="formData.turno" label="Turno" />
          <CheckBox v-model="formData.status" label="Habilitado" class="mt-8 pl-4 flex justify-center items-center" />
        </div>

        <Button
          :title="grupo?.id ? 'Guardar Cambios' : 'Crear Usuario'"
          :loading-title="grupo?.id ? 'Guardando...' : 'Creando...'"
          class="!mt-6 !w-full"
          :loading="saving || updating"
          @click="onSubmit"
        />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
