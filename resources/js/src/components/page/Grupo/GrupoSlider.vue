<script setup>
import { computed, ref, watch } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import Button from '../../ui/Button.vue';
import AuthorizationFallback from '../AuthorizationFallback.vue';

import useDocenteStore from '../../../store/Docente/useDocenteStore';
import useProgramaStore from '../../../store/Programa/useProgramaStatusStore';
import useGrupoStore from '../../../store/Grupo/useGrupoStore';
import useConvenioStore from '../../../store/Convenio/useConvenioStore';

import BaseSelectGrupo from '../../ui/BaseSelectGrupo.vue';
import BaseSelectCiclo from '../../ui/BaseSelectCiclo.vue';
import CheckBox from "../../ui/CheckBox.vue";

import useValidation from '../../../composables/useValidation';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import useCicloStore from '../../../store/Ciclo/useCicloStore';
import usePeriodoStore from '../../../store/Periodo/usePeriodoStatusStore'

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


if (!cicloStore.ciclo?.length) await cicloStore.loadCiclo();

const title = computed(() =>
  props.grupo ? `Actualizar grupo "${props.grupo?.especialidad?.nombre} - ${props.grupo?.seccion}"` : 'Crear nuevo grupo'
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
const programas = ref([]);

const turnos = [
  { value: 'M', name: 'Mañana' },
  { value: 'T', name: 'Tarde' },
  { value: 'N', name: 'Noche' }
];

watch(
  () => props.show,
  async (isShown) => {
    if (!isShown) return;

    // PRIMERO CARGAMOS LOS PROGRAMAS AL ABRIR EL MODAL
    if (!programaStore.programa.length) await programaStore.loadPrograma();

    // DAMOS FORMATO PARA EL SELECT
    console.log("verificamos- ", programaStore.programa.programas)
    programas.value = programaStore.programa.programas.map(p => ({
      id: p.id,
      name: p.nameCiclo,
    })) ?? [];

    // 1) Asegurarnos que programas/convenios/docentes/períodos ya están cargados (si no, cargarlos)
    if (!convenioStore.convenios.length) await convenioStore.loadConvenios();
    // if (!periodoStore.periodos?.length) await periodoStore.loadPeriodos();
    await periodoStore.loadPeriodos();

    if (props.grupo?.id_grupo) {

      // 2) Cargar las opciones dependientes en el orden correcto
      const programaId = props.grupo.programa?.id || null;
      const especialidadId = props.grupo.especialidad?.id || null;

      if (programaId) {
        await grupoStore.loadEspecialidades(programaId);
      }

      if (especialidadId) {
        await grupoStore.loadModulos(especialidadId);
      }

      // 3) Ahora setear formData (las options ya contienen los objetos)
      formData.value = {
        id_programa: programaId,
        id_especialidad: especialidadId,
        id_modulo: props.grupo.modulo?.id || null,
        id_periodo: props.grupo.periodo?.id || null,
        id_convenio: props.grupo.convenio?.id || null,
        fecha_inicio: props.grupo.fecha_inicio || null,
        fecha_fin: props.grupo.fecha_fin || null,
        fecha_entrega_acta: props.grupo.entrega_acta ?? null,
        seccion: props.grupo.seccion ?? null,
        turno: props.grupo.turno ?? null,
        id_docente: props.grupo.docente?.id || null,
        status: props.grupo.status ?? 0
      };

      const docenteAsignado = props.grupo.docente;
      if (
        docenteAsignado &&
        !docenteStore.docentesDisponibles.some(d => d.id === docenteAsignado.id)
      ) {
        docenteStore.docentesDisponibles.push(docenteAsignado);
      }

    } else {
      formData.value = initialFormData();
      formErrors.value = {};
    }
  }
);


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


const onTurnoChange = async () => {
  await loadDocentesDisponibles();
};

const onPeriodoChange = async () => {
  await loadDocentesDisponibles();
};

const loadDocentesDisponibles = async () => {
  if (!formData.value.turno || !formData.value.id_periodo) return;

  console.log('ENTRANDO ACA POR LA PUTA')

  await docenteStore.loadDocentesDisponibles({
    turno: formData.value.turno.value,
    id_periodo: formData.value.id_periodo,
  });
};


const onSubmit = async () => {
  if (saving.value || updating.value) return;

  let data = {
    ...formData.value,
    turno: formData.value.turno?.value ?? formData.value.turno,
  };

  const response = props.grupo?.id_grupo
    ? await updateGrupo(props.grupo?.id_grupo, data)
    : await createGrupo(data);

  if (response?.data.id) {
    showToast(`Grupo ${props.grupo?.id_grupo ? "editado" : "creado"} exitosamente.`);
    grupoStore.loadGrupos();

    if (!props.grupo?.id_grupo) {
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormLabelError label="Programa" required>
            <BaseSelectGrupo v-model="formData.id_programa" :options="programas" label="name"
              placeholder="Seleccione un programa" @change="onProgramaChange"
              :loading="programaStore.programasFirstTimeLoading" />
          </FormLabelError>

          <FormLabelError label="Especialidad" required>
            <BaseSelectGrupo v-model="formData.id_especialidad" :options="grupoStore.especialidades"
              label="nombre_especialidad" placeholder="Seleccione una especialidad" @change="onEspecialidadChange"
              :loading="grupoStore.especialidadByProgramLoading" :disabled="!formData.id_programa" />
          </FormLabelError>


        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormLabelError label="Módulos" required>
            <BaseSelectGrupo v-model="formData.id_modulo" :options="grupoStore.modulos" label="nombre_modulo"
              placeholder="Seleccione un módulo" @change="onModuloChange"
              :loading="grupoStore.moduloByEspecialidadLoading" :disabled="!formData.id_especialidad" />
          </FormLabelError>

          <FormLabelError label="Periodo" required>
            <BaseSelectGrupo v-model="formData.id_periodo" :options="periodoStore.periodos" label="nombre_periodo"
              placeholder="Seleccione un ciclo" @change="onPeriodoChange" />
          </FormLabelError>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <FormLabelError label="Turno" required>
            <BaseSelectGrupo v-model="formData.turno" :options="turnos" placeholder="Seleccione un turno"
              @change="onTurnoChange" />
          </FormLabelError>

          <FormLabelError label="Docente">
            <BaseSelectCiclo v-model="formData.id_docente" :options="docenteStore.docentesDisponibles" label="nombre"
              placeholder="Seleccione un docente" :disabled="!formData.turno || !formData.id_periodo" />
          </FormLabelError>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


          <FormLabelError label="Convenio" required>
            <BaseSelectCiclo v-model="formData.id_convenio" :options="convenioStore.convenios"
              label="nombre_institucion" placeholder="Seleccione un convenio" />
          </FormLabelError>
          <FormInput v-model="formData.fecha_inicio" label="Fecha Inicio" type="date" />
          <FormInput v-model="formData.fecha_fin" label="Fecha Fin" type="date" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput v-model="formData.fecha_entrega_acta" label="Entrega Acta" type="date" />
          <FormInput v-model="formData.seccion" label="Sección" />
          <CheckBox v-model="formData.status" label="Habilitado" class="mt-8 pl-4 flex justify-center items-center" />
        </div>

        <Button :title="grupo?.id ? 'Guardar Cambios' : 'Crear Grupo'"
          :loading-title="grupo?.id ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full" :loading="saving || updating"
          @click="onSubmit" />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
