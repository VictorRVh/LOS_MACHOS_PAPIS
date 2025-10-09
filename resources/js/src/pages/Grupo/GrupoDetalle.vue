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
  if (!infoGrupo.value) return 'Cargando detalles...';
  return `Módulo ${infoGrupo.value.modulo_numero}: ${infoGrupo.value.modulo_nombre} | Sección ${infoGrupo.value.seccion}`;
});

onMounted(async () => {
  try {
    if (!grupoStore?.infoGrupo?.length) {
      await grupoStore.loadInfoGrupo(groupId);
    }
    infoGrupo.value = grupoStore.infoGrupo;
    breadcrumb.setTextItemAuto(`${infoGrupo?.value?.especialidad} | M: ${infoGrupo?.value?.modulo} | Grupo: ${infoGrupo?.value?.seccion}`, groupId, "grupo", { name: 'grupo.detalle', params: { groupId } });
  } catch (error) {
    console.error("Error al cargar la información del grupo:", error);
    errorAlCargar.value = true;
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full">
    <div v-if="isLoading" class="p-6 space-y-4">
      <div class="h-8 w-3/4 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
      <div class="h-6 w-1/2 bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse"></div>
      <div class="h-10 w-full border-b border-gray-200 dark:border-gray-700 mt-2"></div>
    </div>
    
    <div v-else-if="errorAlCargar" class="p-6 bg-red-50 dark:bg-red-900/50 rounded-lg">
      <h1 class="text-xl font-bold text-red-700 dark:text-red-300">Error de Carga</h1>
      <p class="text-red-600 dark:text-red-400">No se pudo obtener la información del grupo.</p>
    </div>

    <template v-else>
      <header class="px-6 pt-5">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 truncate">{{ tituloPrincipal }}</h2>
        <p class="text-md text-gray-500 dark:text-gray-400">{{ subTitulo }}</p>
      </header>

      <nav class="mt-4 px-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-4 sm:space-x-6 overflow-x-auto custom-scrollbar-nav">
          <router-link
            v-for="link in navLinks"
            :key="link.text"
            :to="link.to"
            class="py-3 px-1 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-cetpro-dark dark:hover:text-cetpro-light hover:border-cetpro/50 transition-colors duration-200 whitespace-nowrap"
            active-class="!text-cetpro !dark:text-cetpro-light !border-cetpro font-semibold"
          >
            {{ link.text }}
          </router-link>
        </div>
      </nav>
      
      <div class="p-6 flex-grow">
        <router-view />
      </div>
    </template>
  </div>
</template>

<style scoped>
.custom-scrollbar-nav::-webkit-scrollbar {
  height: 4px;
}
.custom-scrollbar-nav::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar-nav::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 20px;
}
.dark .custom-scrollbar-nav::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>