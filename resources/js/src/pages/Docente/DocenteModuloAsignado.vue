<script setup>
import { computed } from "vue";
import { useRouter } from 'vue-router';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import DocenteSlider from '../../components/page/Docente/DocenteSlider.vue';
import useDocenteStore from "../../store/Docente/useDocenteStore";
import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";
import ChangePasswordModal from "../../components/page/ChangePasswordModal.vue";
import { ArrowRightIcon, UsersIcon, BookOpenIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';


const router = useRouter();
const docenteStore = useDocenteStore();

if (!docenteStore.modulosAsignados?.length) await docenteStore.loadModulosAsignados();

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
  const start = new Date(startDate).getTime();
  const end = new Date(endDate).getTime();
  const now = new Date().getTime();

  if (now < start) return { percent: 0, status: 'Próximamente' };
  if (now > end) return { percent: 100, status: 'Finalizado' };

  const totalDuration = end - start;
  if (totalDuration <= 0) return { percent: 100, status: 'Finalizado' };
  
  const elapsed = now - start;
  const percent = Math.round((elapsed / totalDuration) * 100);
  
  return { percent: Math.min(100, percent), status: `${percent}% Completado` };
};
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
                     class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-transparent hover:border-cetpro flex flex-col transition-all duration-300 group">
                    
                    <div class="p-4 bg-cetpro-dark rounded-t-lg text-cetpro-text">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-sm uppercase tracking-wider">{{ modulo.especialidad }}</h3>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                  :class="{
                                      'bg-green-500/80 text-white': modulo.matriculados > 0,
                                      'bg-yellow-500/80 text-white': modulo.matriculados === 0
                                  }">
                                {{ modulo.matriculados > 0 ? 'Activo' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 flex-grow">
                        <h4 class="text-xl font-extrabold text-gray-800 dark:text-white mb-3">{{ modulo.modulo }}</h4>

                        <!-- INICIO: BARRA DE PROGRESO -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-cetpro dark:text-cetpro-light">Progreso</span>
                                <span v-if="getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).status === 'Finalizado'" class="text-xs font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <CheckCircleIcon class="w-4 h-4" />
                                    Finalizado
                                </span>
                                <span v-else class="text-xs font-bold text-gray-600 dark:text-gray-300">
                                    {{ getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).status }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-cetpro h-2.5 rounded-full transition-all duration-500 ease-out" 
                                     :style="{ width: getModuleProgress(modulo.fecha_inicio, modulo.fecha_fin).percent + '%' }">
                                </div>
                            </div>
                        </div>
                        <!-- FIN: BARRA DE PROGRESO -->

                        <div class="space-y-2 text-sm">
                            <p class="text-gray-600 dark:text-gray-300">
                                <span class="font-semibold">Docente:</span> {{ modulo.docente }}
                            </p>
                            <div class="grid grid-cols-2 gap-x-4 pt-2">
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Inicio:</p>
                                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ modulo.fecha_inicio }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Fin:</p>
                                    <p class="font-medium text-gray-700 dark:text-gray-200">{{ modulo.fecha_fin }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-auto p-4 border-t-2 border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 rounded-b-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 text-xs">
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Sección</p>
                                    <p class="font-bold text-lg text-gray-700 dark:text-gray-200">{{ modulo.seccion }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Turno</p>
                                    <p class="font-bold text-lg text-gray-700 dark:text-gray-200">{{ modulo.turno }}</p>
                                </div>
                                <div class="pl-2 border-l border-gray-200 dark:border-gray-600">
                                    <p class="font-semibold text-gray-500 dark:text-gray-400">Matric.</p>
                                    <div class="flex items-center gap-1.5">
                                        <UsersIcon class="w-4 h-4 text-gray-400" />
                                        <p class="font-bold text-lg text-gray-700 dark:text-gray-200">{{ modulo.matriculados }}</p>
                                    </div>
                                </div>
                            </div>
                            <button @click="verAlumnos(modulo)" 
                                    class="bg-cetpro hover:bg-cetpro-light text-cetpro-text font-bold text-sm px-5 py-2.5 rounded-lg flex items-center gap-2 transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-cetpro-light focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Gestionar
                                <ArrowRightIcon class="w-4 h-4 transform transition-transform group-hover:translate-x-1" />
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