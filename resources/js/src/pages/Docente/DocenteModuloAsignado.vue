<script setup>
import { computed, ref, onMounted } from "vue";
import { useRouter } from 'vue-router';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import DocenteSlider from '../../components/page/Docente/DocenteSlider.vue';
import useDocenteStore from "../../store/Docente/useDocenteStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";
import { 
  ArrowRightIcon, UsersIcon, BookOpenIcon, CheckCircleIcon, 
  UserCircleIcon, CalendarDaysIcon, FireIcon
} from '@heroicons/vue/24/outline';


const router = useRouter();
const docenteStore = useDocenteStore();

await docenteStore.loadModulosAsignados();

const { slider, sliderData, showSlider, hideSlider } = useSlider("docente-crud");
const { showToast } = useModalToast();

const modulos = computed(() => docenteStore.modulosAsignados);

const verAlumnos = (modulo) => {
  const grupoId = modulo.id_grupo || modulo.id;
  if (!grupoId) {
    console.error("No se encontró el ID del grupo en el objeto:", modulo);
    showToast('Error: No se pudo encontrar el ID del grupo.', 'error');
    return;
  }
  
  router.push({
    name: 'docente.modulo.detalle',
    params: { id: grupoId }
  });
};

const getModuleProgress = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const now = new Date();

  // Reset hours to compare dates only
  start.setHours(0, 0, 0, 0);
  end.setHours(0, 0, 0, 0);
  now.setHours(0, 0, 0, 0);

  const totalDuration = (end.getTime() - start.getTime()) / (1000 * 3600 * 24);
  const elapsed = (now.getTime() - start.getTime()) / (1000 * 3600 * 24);
  const daysLeft = Math.round((end.getTime() - now.getTime()) / (1000 * 3600 * 24));

  if (now < start) return { percent: 0, statusText: 'Aún no inicia', daysText: `Faltan ${Math.round((start.getTime() - now.getTime()) / (1000 * 3600 * 24))} días` };
  if (now > end) return { percent: 100, statusText: 'Finalizado', daysText: `Finalizó hace ${Math.abs(daysLeft)} días` };

  const percent = totalDuration > 0 ? Math.round((elapsed / totalDuration) * 100) : 100;
  
  return { 
    percent: Math.min(100, percent), 
    statusText: `${percent}%`, 
    daysText: daysLeft > 0 ? `Faltan ${daysLeft} días` : 'Último día' 
  };
};

// For animation
const isMounted = ref(false);
onMounted(() => {
    setTimeout(() => {
        isMounted.value = true;
    }, 100);
});
</script>

<template>
    <AuthorizationFallback :permissions="['ver-mis-modulos']">
        <div class="w-full space-y-6 py-6 px-4 sm:px-6">
            <header>
                <h2 class="text-gray-900 dark:text-gray-50 font-bold text-3xl">Mis Módulos</h2>
                <p class="font-inter text-md text-gray-500 dark:text-gray-400">Gestiona tus módulos asignados de forma centralizada.</p>
            </header>
            
            <div v-if="modulos.length === 0" class="text-center py-24 text-gray-500 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <BookOpenIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <p class="text-lg font-semibold">Aún no tienes módulos asignados.</p>
                <p class="text-sm">Cuando te asignen un módulo, aparecerá aquí.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="modulo in modulos" :key="modulo.id_grupo || modulo.id" 
                     class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700/50 flex flex-col transition-all duration-300 group hover:shadow-cetpro/20 hover:shadow-2xl hover:-translate-y-1.5">
                    
                    <div class="p-4 bg-cetpro-dark rounded-t-lg text-cetpro-text">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-sm uppercase tracking-wider">{{ modulo.especialidad }}</h3>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full flex items-center gap-1"
                                  :class="{
                                      'bg-green-500/80 text-white': modulo.matriculados > 0,
                                      'bg-yellow-500/80 text-black': modulo.matriculados === 0
                                  }">
                                <FireIcon v-if="modulo.matriculados > 0" class="w-3 h-3" />
                                {{ modulo.matriculados > 0 ? 'Activo' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 flex-grow flex flex-col">
                        <h4 class="text-xl font-extrabold text-gray-900 dark:text-white mb-3">{{ modulo.modulo }}</h4>

                        <!-- INICIO: BARRA DE PROGRESO "GOD" -->
                        <div class="mb-5">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).daysText }}
                                </span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).statusText }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700/50 rounded-full h-3 overflow-hidden border border-gray-300 dark:border-gray-600">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out flex items-center justify-center" 
                                     :class="{
                                         'bg-gradient-to-r from-cetpro-light to-cetpro animate-pulse': getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent > 0 && getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent < 80,
                                         'bg-gradient-to-r from-yellow-400 to-orange-500': getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent >= 80 && getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent < 100,
                                         'bg-gradient-to-r from-green-400 to-green-600': getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent === 100
                                     }"
                                     :style="{ width: isMounted ? getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent + '%' : '0%' }">
                                     <div v-if="getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent === 100" class="text-white">
                                         <CheckCircleIcon class="w-3 h-3" />
                                     </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN: BARRA DE PROGRESO "GOD" -->

                        <div class="space-y-3 text-sm flex-grow">
                             <div class="flex items-center gap-3">
                                <UserCircleIcon class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <p class="text-gray-700 dark:text-gray-300">
                                    <span class="font-semibold text-gray-500">Docente:</span> {{ modulo.docente }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <CalendarDaysIcon class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <p class="text-gray-700 dark:text-gray-300">
                                    <span class="font-semibold text-gray-500">Periodo:</span> {{ modulo.fecha_inicio }} al {{ modulo.fecha_fin }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-auto p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 rounded-b-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 text-xs">
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Sección</p>
                                    <p class="font-bold text-lg text-gray-800 dark:text-gray-100">{{ modulo.seccion }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Turno</p>
                                    <p class="font-bold text-lg text-gray-800 dark:text-gray-100">{{ modulo.turno }}</p>
                                </div>
                                <div class="pl-2 border-l border-gray-200 dark:border-gray-600">
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Matric.</p>
                                    <div class="flex items-center gap-1.5">
                                        <UsersIcon class="w-5 h-5 text-gray-400" />
                                        <p class="font-bold text-lg text-gray-800 dark:text-gray-100">{{ modulo.matriculados }}</p>
                                    </div>
                                </div>
                            </div>
                            <button @click="verAlumnos(modulo)" 
                                    class="bg-cetpro hover:bg-cetpro-light text-cetpro-text font-bold text-sm px-5 py-2.5 rounded-lg flex items-center gap-2 transition-all duration-300 shadow-lg hover:shadow-cetpro/40 focus:outline-none focus:ring-4 focus:ring-cetpro-light/50 active:shadow-inner">
                                Gestionar
                                <ArrowRightIcon class="w-4 h-4 transform transition-transform group-hover:translate-x-1.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <DocenteSlider :show="slider" :docente="sliderData" @hide="hideSlider" />
    </AuthorizationFallback>
    <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" />
</template>