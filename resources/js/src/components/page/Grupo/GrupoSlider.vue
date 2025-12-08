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
import * as yup from "yup";

const props = defineProps({
  show: { type: Boolean, default: () => false },
  grupo: { type: [Object, null], default: () => null },
});
const emit = defineEmits(['hide','updated']);

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
  props.grupo ? `Actualizar grupo "${props.grupo?.especialidad || ''} - ${props.grupo?.seccion}"` : 'Crear nuevo grupo'
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

    console.log("Grupo recibido →", props.grupo);

    // Cargar listas base
    if (!programaStore.programa.length) await programaStore.loadPrograma();
    if (!convenioStore.convenios.length) await convenioStore.loadConvenios();
    if (!periodoStore.periodos?.length) await periodoStore.loadPeriodos();

    // Programas para el primer select
    programas.value = programaStore.programa.programas.map(p => ({
      id: p.id,
      name: p.nameCiclo,
    }));

    // 👉 SI ES EDICIÓN (YA NO USAS id_grupo, SOLO id)
    if (props.grupo?.id) {

      const programaId = props.grupo.id_programa;
      const especialidadId = props.grupo.id_especialidad;

      // cargar especialidades
      if (programaId) {
        await grupoStore.loadEspecialidades(programaId);
      }

      // cargar módulos
      if (especialidadId) {
        await grupoStore.loadModulos(especialidadId);


        //formData.value.id_modulo = props.grupo.id_modulo;
      }

      // formData EXACTO a tus datos planos
      formData.value = {
        id_programa: props.grupo.id_programa,
        id_especialidad: props.grupo.id_especialidad,
        id_modulo: props.grupo.id_modulo,
        id_periodo: props.grupo.id_periodo,
        id_convenio: props.grupo.id_convenio,
        fecha_inicio: props.grupo.fecha_inicio,
        fecha_fin: props.grupo.fecha_fin,
        fecha_entrega_acta: props.grupo.fecha_entrega_acta,
        seccion: props.grupo.seccion,
        turno: turnos.find(t => t.value === props.grupo.turno) ?? null,
        id_docente: props.grupo.id_docente,
        status: props.grupo.status,
      };

      if (props.grupo?.id) {
        
        await loadDocentesDisponibles();
      }


    } else {
      // crear nuevo
      formData.value = initialFormData();
      formErrors.value = {};
    }
  }
);


const secciones = ref([]);



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

const onModuloChange = async (moduloId) => {
  // módulo ya seleccionado, ahora sí podemos cargar docentes
  await loadDocentesDisponibles();
};


const loadDocentesDisponibles = async () => {

  console.log("perido: ", formData.value.id_periodo," id_modulo: ", formData.value.id_modulo)
  if (!formData.value.turno || !formData.value.id_periodo || !formData.value.id_modulo) return;
  await docenteStore.loadDocentesDisponibles({
    turno: typeof formData.value.turno === "object"
      ? formData.value.turno.value
      : formData.value.turno,
    id_periodo: formData.value.id_periodo,
    id_modulo: formData.value.id_modulo,
    id_grupo: props.grupo?.id ?? null,
  });

  secciones.value = docenteStore.docentesDisponibles?.secciones;
  docenteStore.docentesDisponibles = docenteStore.docentesDisponibles?.docentes;

};


const schema = yup.object({
  // id_programa: yup.string().required("El programa es obligatorio"),
  // id_especialidad: yup.string().required("La especialidad es obligatoria"),
  // id_modulo: yup.string().required("El módulo es obligatorio"),
  // id_periodo: yup.string().required("El periodo es obligatorio"),
  // turno: yup.string().required("El turno es obligatorio"),
  // id_convenio: yup.string().required("El convenio es obligatorio"),

  fecha_inicio: yup
    .date()
    .required("La fecha de inicio es obligatoria"),

  fecha_fin: yup
    .date()
    .required("La fecha de fin es obligatoria")
    .min(yup.ref("fecha_inicio"), "La fecha de fin no puede ser antes de la fecha de inicio"),

  fecha_entrega_acta: yup
    .date()
    .required("La fecha de entrega del acta es obligatoria")
    .min(yup.ref("fecha_fin"), "La entrega del acta debe ser después de la fecha de fin"),
  seccion: yup
    .string()
    .required("La sección es obligatoria")
  // seccion: yup.string().nullable(),
  // status: yup.boolean(),
});


const onSubmit = async () => {
  if (saving.value || updating.value) return;

  let data = {
    ...formData.value,
    turno: formData.value.turno?.value ?? formData.value.turno,
  };

  const { validated, errors } = await runYupValidation(schema, data);

  console.log('errores', errors)

  if (!validated) {
    formErrors.value = errors;
    return;
  }
  formErrors.value = {};

  const response = props.grupo?.id
    ? await updateGrupo(props.grupo?.id, data)
    : await createGrupo(data);

  if (response?.data.id) {
    showToast(`Grupo ${props.grupo?.id ? "editado" : "creado"} exitosamente.`);
    grupoStore.loadGrupos();
    emit("updated");
    if (!props.grupo?.id) {
      formData.value = initialFormData();
    }
    formErrors.value = {};
    emit("hide");
  }
};
</script>

<template>
  <Slider :show="show" :title="title" @hide="emit('hide')">
    <AuthorizationFallback :permissions="['todo-acceso-grupos', grupo?.id ? 'editar-grupos' : 'crear-grupos']">
      <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

      <div class="mt-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormLabelError label="Ciclo Académico" required>
            <BaseSelectGrupo v-model="formData.id_programa" :options="programas" label="name"
              placeholder="Seleccione un programa" @change="onProgramaChange"
              :loading="programaStore.programasFirstTimeLoading" />
          </FormLabelError>

          <FormLabelError label="Programa de estudio" required>
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
              placeholder="Seleccione un docente" :disabled="!formData.turno || !formData.id_periodo"
              :loading="docenteStore.docentesDisponiblesLoading" />
          </FormLabelError>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


          <FormLabelError label="Modalidad" required>
            <BaseSelectCiclo v-model="formData.id_convenio" :options="convenioStore.convenios"
              label="nombre_institucion" placeholder="Seleccione una modalidad" />
          </FormLabelError>


          <FormInput v-model="formData.fecha_inicio" label="Fecha Inicio" type="date"
            :error="formErrors?.fecha_inicio" />
          <FormInput v-model="formData.fecha_fin" label="Fecha Fin" type="date" :error="formErrors?.fecha_fin" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormInput v-model="formData.fecha_entrega_acta" label="Entrega Acta" type="date"
            :error="formErrors?.fecha_entrega_acta" />
          <FormLabelError label="Sección" :error="formErrors?.seccion" required>

            <BaseSelectGrupo v-model="formData.seccion" :options="secciones" label="nombre"
              placeholder="Seleccione una sección" style="--vs-dropdown-max-height: 90px" />
          </FormLabelError>

          <CheckBox v-model="formData.status" label="Habilitado" class="mt-8 pl-4 flex justify-center items-center" />
        </div>

        <Button :title="grupo?.id_grupo ? 'Guardar Cambios' : 'Crear Grupo'"
          :loading-title="grupo?.id_grupo ? 'Guardando...' : 'Creando...'" class="!mt-6 !w-full"
          :disabled="saving || updating" :loading="saving || updating" @click="onSubmit" />
      </div>
    </AuthorizationFallback>
  </Slider>
</template>
