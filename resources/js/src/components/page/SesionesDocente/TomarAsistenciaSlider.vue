<script setup>
import { ref, watch, computed } from 'vue';
import useHttpRequest from '../../../composables/useHttpRequest';
import useModalToast from '../../../composables/useModalToast';
import Button from '@/components/ui/Button.vue';
import Spinner from '@/components/ui/Spinner.vue';
import { 
  CheckBadgeIcon, NoSymbolIcon, ClockIcon, ShieldCheckIcon, ArrowPathIcon, 
  ClipboardDocumentCheckIcon, UserGroupIcon, ArrowLeftOnRectangleIcon, XMarkIcon, PencilSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  show: Boolean,
  grupoId: { type: String, required: true },
  sesionId: { type: String, required: true },
});

const emit = defineEmits(['hide']);

const { showToast } = useModalToast();

const { show: getAlumnosDelGrupo, loading: alumnosLoading } = useHttpRequest('/lista_alumnos_notas');
const { show: fetchSesionDetalle } = useHttpRequest('/programacion_sesion_docente');
const { saving: isSaving, store: guardarAsistencias, indexWithParams: fetchAsistencias } = useHttpRequest('/asistencias');

const alumnos = ref([]);
const sesionInfo = ref(null);
const asistencias = ref({});
const observacionGeneral = ref('');
const isEditing = ref(false);
const isLoadingData = ref(true);
const fechaDeHoy = new Date();
const fechaDeHoyString = fechaDeHoy.toISOString().slice(0, 10);

const sesionTitulo = computed(() => (sesionInfo.value?.nombre_sesion) || 'Asistencia General del Día');

const estadoAsistencia = [
  { key: 'asistio',  label: 'Asistió',  value: 1, icon: CheckBadgeIcon,           color: 'text-green-600 dark:text-green-400',   hover: 'hover:bg-green-100 dark:hover:bg-green-900/50' },
  { key: 'falto',    label: 'Faltó',     value: 2, icon: NoSymbolIcon,             color: 'text-red-600 dark:text-red-400',       hover: 'hover:bg-red-100 dark:hover:bg-red-900/50' },
  { key: 'tardanza', label: 'Tardanza',  value: 3, icon: ClockIcon,                color: 'text-yellow-600 dark:text-yellow-400', hover: 'hover:bg-yellow-100 dark:hover:bg-yellow-900/50' },
  { key: 'permiso',  label: 'Permiso',   value: 4, icon: ShieldCheckIcon,          color: 'text-blue-600 dark:text-blue-400',     hover: 'hover:bg-blue-100 dark:hover:bg-blue-900/50' },
];

const estadoRetirado = { key: 'retirado', label: 'Retirado', value: 5, icon: ArrowLeftOnRectangleIcon };

const alumnosOrdenados = computed(() => {
  return [...alumnos.value].sort((a, b) => {
    const nameA = `${a.apellido_paterno} ${a.apellido_materno} ${a.nombre}`.toLowerCase();
    const nameB = `${b.apellido_paterno} ${b.apellido_materno} ${b.nombre}`.toLowerCase();
    if (nameA < nameB) return -1;
    if (nameA > nameB) return 1;
    return 0;
  });
});

const loadData = async () => {
  isLoadingData.value = true;
  try {
    const [sesionData, alumnosData, asistenciasPreviasData] = await Promise.all([
      fetchSesionDetalle(props.sesionId),
      getAlumnosDelGrupo(props.grupoId),
      fetchAsistencias({ sesion_id: props.sesionId, fecha: fechaDeHoyString })
    ]);
    
    sesionInfo.value = sesionData;
    alumnos.value = alumnosData || [];

    if (Array.isArray(asistenciasPreviasData) && asistenciasPreviasData.length > 0) {
      asistenciasPreviasData.forEach(asistencia => {
        if (!asistencia.estudiante_id) return;
        const estadoEncontrado = [...estadoAsistencia, estadoRetirado].find(e => e.value === asistencia.asistencia);
        if (estadoEncontrado) {
          asistencias.value[asistencia.estudiante_id] = estadoEncontrado.key;
        }
        if (asistencia.observacion) {
          observacionGeneral.value = asistencia.observacion;
        }
      });
    }
  } catch (error) {
    console.error("Error al cargar datos:", error);
    showToast("Error al cargar los datos.", "error");
  } finally {
    isLoadingData.value = false;
  }
};

watch(() => props.show, (isVisible) => {
  if (isVisible) {
    isEditing.value = false;
    loadData();
  }
});

const marcarAsistencia = (alumnoId, estadoKey) => {
  if (asistencias.value[alumnoId] === 'retirado') return;
  asistencias.value[alumnoId] = asistencias.value[alumnoId] === estadoKey ? undefined : estadoKey;
};

const onSubmit = async () => {
  const payload = alumnos.value.map(alumno => {
    const estadoKey = asistencias.value[alumno.id] || 'falto';
    const estadoObj = [...estadoAsistencia, estadoRetirado].find(e => e.key === estadoKey);
    return {
      id_estudiante: alumno.id,
      id_sesion: props.sesionId,
      id_grupo: props.grupoId,
      fecha_actual: fechaDeHoyString,
      asistencia: estadoObj.value,
      observacion: observacionGeneral.value || null,
    };
  });

  const response = await guardarAsistencias({ asistencias: payload });
  if (response) {
    showToast("Asistencia guardada.", "success");
    isEditing.value = false;
  }
};

const getEstadoSeleccionado = (alumnoId) => {
  const estadoKey = asistencias.value[alumnoId];
  return [...estadoAsistencia, estadoRetirado].find(e => e.key === estadoKey);
};

const close = () => emit('hide');
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 bg-black/70 flex justify-center items-center z-50" @click="close">
        <div class="w-[90vw] max-w-screen-xl h-[90vh] bg-gray-100 dark:bg-gray-900 rounded-lg shadow-2xl flex flex-col" @click.stop>
          
          <header class="p-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800 rounded-t-lg">
            <div>
              <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ sesionTitulo }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Fecha: {{ fechaDeHoy.toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' }) }}
              </p>
            </div>
            <div class="flex items-center gap-4">
              <div class="hidden sm:flex items-center justify-center gap-4 flex-wrap">
                <div v-for="estado in estadoAsistencia" :key="estado.key" class="flex items-center gap-1.5">
                  <component :is="estado.icon" :class="estado.color" class="w-5 h-5" />
                  <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ estado.label }}</span>
                </div>
              </div>
              <button @click="close" class="p-2 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>
          </header>

          <main class="flex-grow overflow-y-auto">
            <div v-if="isLoadingData" class="flex items-center justify-center h-full"><Spinner /></div>
            <div v-else-if="alumnos.length === 0" class="flex flex-col items-center justify-center h-full text-center text-gray-500">
              <UserGroupIcon class="w-20 h-20 text-gray-400 mb-4" />
              <p class="text-xl font-semibold">No hay Alumnos Matriculados</p>
            </div>
            
            <table v-else class="min-w-full bg-white dark:bg-gray-800 text-sm">
              <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700 z-10">
                <tr>
                  <th class="py-2 px-3 text-left w-12">N°</th>
                  <th class="py-2 px-3 text-left">Apellidos y Nombres</th>
                  <th class="py-2 px-3 text-left w-32">DNI</th>
                  <th v-if="!isEditing" class="py-2 px-3 text-center w-24">Asist.</th>
                  <th v-if="!isEditing" class="py-2 px-3 text-center w-24">Faltas</th>
                  <th v-if="!isEditing" class="py-2 px-3 text-center w-24">Tard.</th>
                  <th v-else class="py-2 px-3 text-center w-64">Marcar Asistencia de Hoy</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(alumno, index) in alumnosOrdenados" :key="alumno.id" class="border-b border-gray-200 dark:border-gray-700/50">
                  <td class="py-2 px-3 font-mono text-gray-400 dark:text-gray-500">{{ (index + 1) }}</td>
                  <td class="py-2 px-3 font-semibold text-gray-800 dark:text-white">{{ alumno.apellido_paterno }} {{ alumno.apellido_materno }}, {{ alumno.nombre }}</td>
                  <td class="py-2 px-3 text-gray-500 dark:text-gray-400">{{ alumno.nro_documento }}</td>
                  
                  <template v-if="!isEditing">
                    <td class="py-2 px-3 text-center font-medium text-gray-600 dark:text-gray-300">{{ alumno.asistencias_count || 0 }}</td>
                    <td class="py-2 px-3 text-center font-medium text-gray-600 dark:text-gray-300">{{ alumno.faltas_count || 0 }}</td>
                    <td class="py-2 px-3 text-center font-medium text-gray-600 dark:text-gray-300">{{ alumno.tardanzas_count || 0 }}</td>
                  </template>

                  <td v-else class="py-2 px-3">
                    <div class="flex items-center justify-center gap-1.5 flex-shrink-0">
                      <template v-if="!getEstadoSeleccionado(alumno.id)">
                        <button v-for="estado in estadoAsistencia" :key="estado.key" @click="marcarAsistencia(alumno.id, estado.key)"
                          :class="[estado.color, estado.hover]" class="p-1.5 rounded-full transition-colors duration-200" :title="estado.label">
                          <component :is="estado.icon" class="w-5 h-5" />
                        </button>
                      </template>
                      <template v-else>
                        <div class="px-2.5 py-1 rounded-full flex items-center gap-2 text-xs font-bold" :class="{
                          'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400': getEstadoSeleccionado(alumno.id).key === 'asistio',
                          'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400': getEstadoSeleccionado(alumno.id).key === 'falto',
                          'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400': getEstadoSeleccionado(alumno.id).key === 'tardanza',
                          'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400': getEstadoSeleccionado(alumno.id).key === 'permiso',
                          'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed': getEstadoSeleccionado(alumno.id).key === 'retirado',
                        }">
                          <component :is="getEstadoSeleccionado(alumno.id).icon" class="w-4 h-4" />
                          <span>{{ getEstadoSeleccionado(alumno.id).label }}</span>
                          <button v-if="getEstadoSeleccionado(alumno.id).key !== 'retirado'" @click="marcarAsistencia(alumno.id, null)" class="ml-1 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" title="Deshacer">
                            <ArrowPathIcon class="w-3 h-3" />
                          </button>
                        </div>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </main>

          <footer v-if="!isLoadingData && alumnos.length > 0" class="p-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-lg">
             <div v-if="isEditing" class="mb-4">
                <label for="observacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observación General</label>
                <textarea id="observacion" v-model="observacionGeneral" rows="2" class="w-full text-sm bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
             </div>
            <div class="flex justify-end gap-3">
              <Button title="Cancelar" variant="secondary" @click="isEditing ? isEditing = false : close()" />
              <Button v-if="!isEditing" title="Tomar Asistencia Hoy" @click="isEditing = true">
                <template #icon><PencilSquareIcon class="w-5 h-5" /></template>
              </Button>
              <Button v-else title="Guardar Asistencias" :loading="isSaving" loading-title="Guardando..." @click="onSubmit">
                <template #icon><ClipboardDocumentCheckIcon class="w-5 h-5" /></template>
              </Button>
            </div>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .dark\:bg-gray-900, .modal-leave-active .dark\:bg-gray-900 { transition: all 0.3s ease-in-out; }
.modal-enter-from .dark\:bg-gray-900, .modal-leave-to .dark\:bg-gray-900 { transform: scale(0.95); opacity: 0; }
</style>