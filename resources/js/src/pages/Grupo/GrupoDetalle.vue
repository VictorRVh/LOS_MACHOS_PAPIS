<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import useGrupoStore from '../../store/Grupo/useGrupoStore';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';

const route = useRoute();
const grupoStore = useGrupoStore();
const infoGrupo = ref(null);
const isLoading = ref(true);
const errorAlCargar = ref(false);
const groupId = route.params.id;
const breadcrumb = useBreadcrumbStore();


const navLinks = [
  { text: 'Documentos', to: { name: 'grupo.documentos', params: { id: groupId } } },
  { text: 'Sesiones y asistencia', to: { name: 'grupo.asistencia', params: { id: groupId } } },
  { text: 'Calificaciones', to: { name: 'grupo.calificaciones', params: { id: groupId } } },
  { text: 'Prácticas', to: { name: 'grupo.practicas', params: { id: groupId } } },
  { text: 'Alumnos', to: { name: 'grupo.alumnos', params: { id: groupId } } },
];

const tituloPrincipal = computed(() => {
  if (!infoGrupo.value) return 'Grupo';
  return `Especialidad: ${infoGrupo.value.especialidad}`;
});

const subTitulo = computed(() => {
  if (!infoGrupo.value) return 'Detalles no disponibles';
  return `Módulo ${infoGrupo.value.modulo_numero} | Sección ${infoGrupo.value.seccion}`;
});

onMounted(async () => {

  isLoading.value = true;
  errorAlCargar.value = false;
 
  try {
   
    await grupoStore.loadInfoGrupo(groupId);
    infoGrupo.value = grupoStore.infoGrupo;  
    console.log("mostrar datos: ",infoGrupo.value);
    breadcrumb.setTextItemAuto(`${infoGrupo?.value?.especialidad} | M: ${infoGrupo?.value?.modulo} | Grupo: ${infoGrupo?.value?.seccion}`, groupId, "grupo");
  } catch (error) {
    
    console.error(error);
    errorAlCargar.value = true;
  } finally {
    isLoading.value = false;
  
  }
});
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <header v-if="isLoading" class="mb-4 space-y-2">
      <div class="h-9 w-3/4 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
      <div class="h-6 w-1/2 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
    </header>

    <header v-else-if="errorAlCargar" class="mb-4 p-4 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 rounded-md">
        <h1 class="text-2xl font-bold text-red-700 dark:text-red-300">Error de Carga</h1>
        <p class="text-red-600 dark:text-red-400">No se pudo cargar la información del grupo. Revisa la consola (F12) para ver el error. Probablemente tu sesión expiró.</p>
    </header>

    <header v-else class="mb-2">
      <h2 class="text-base sm:text-lg  font-bold text-gray-600 dark:text-gray-200 truncate">
        {{ tituloPrincipal }}
      </h2>
      <p class="text-md sm:text-lg text-gray-500 dark:text-gray-400">
        {{ subTitulo }}
      </p>
    </header>

    <nav class="relative">
      <div class="overflow-x-auto whitespace-nowrap pb-2 custom-scrollbar-nav">
        <div class="flex space-x-1 sm:space-x-2 border-b border-gray-200 dark:border-gray-700">
          <router-link
            v-for="link in navLinks"
            :key="link.text"
            :to="link.to"
            class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 text-gray-500 dark:text-gray-400 border-transparent hover:text-cetpro-dark hover:border-cetpro-light dark:hover:text-cetpro-light transition-colors duration-200"
            active-class="!text-cetpro !dark:text-cetpro-light !border-cetpro font-semibold"
          >
            {{ link.text }}
          </router-link>
        </div>
      </div>
    </nav>

    <div class="mt-4">
      <router-view />
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar-nav::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-nav::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-nav::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 20px; }
.dark .custom-scrollbar-nav::-webkit-scrollbar-thumb { background-color: #4b5563; }
</style>