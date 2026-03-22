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
  { text: 'Unidades Didacticas', to: { name: 'grupo.capacidades.terminales', params: { id: groupId } } },
  { text: 'Practicas', to: { name: 'grupo.practicas', params: { id: groupId } } },
  { text: 'Alumnos', to: { name: 'grupo.alumnos', params: { id: groupId } } },
];

const tituloPrincipal = computed(() => {
  if (!infoGrupo.value) return 'Grupo';
  return `${infoGrupo.value.especialidad}`;
});

const subTitulo = computed(() => {
  if (!infoGrupo.value) return 'Cargando detalles...';
  return `Modulo ${infoGrupo.value.modulo} · Seccion ${infoGrupo.value.seccion}`;
});

onMounted(async () => {
  try {
    if (!grupoStore?.infoGrupo?.length) {
      await grupoStore.loadInfoGrupo(groupId);
    }
    infoGrupo.value = grupoStore.infoGrupo;
    breadcrumb.setTextItemAuto(`${infoGrupo?.value?.especialidad} · M ${infoGrupo?.value?.modulo} · Grupo ${infoGrupo?.value?.seccion}`, groupId, "grupo", { name: 'grupo.detalle', params: { groupId } });
  } catch (error) {
    console.error("Error al cargar la informacion del grupo:", error);
    errorAlCargar.value = true;
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="flex h-full flex-col rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div v-if="isLoading" class="space-y-4 p-6">
      <div class="h-8 w-3/4 animate-pulse rounded-md bg-gray-200 dark:bg-gray-700"></div>
      <div class="h-6 w-1/2 animate-pulse rounded-md bg-gray-200 dark:bg-gray-700"></div>
      <div class="mt-2 h-10 w-full border-b border-gray-200 dark:border-gray-700"></div>
    </div>

    <div v-else-if="errorAlCargar" class="rounded-lg bg-red-50 p-6 dark:bg-red-900/50">
      <h1 class="text-xl font-bold text-red-700 dark:text-red-300">Error de Carga</h1>
      <p class="text-red-600 dark:text-red-400">No se pudo obtener la informacion del grupo.</p>
    </div>

    <template v-else>
      <header class="px-6 pt-5">
        <h2 class="truncate text-2xl font-bold text-gray-800 dark:text-gray-200">{{ tituloPrincipal }}</h2>
        <p class="text-md text-gray-500 dark:text-gray-400">{{ subTitulo }}</p>
      </header>

      <nav class="mt-4 border-b border-gray-200 px-6 dark:border-gray-700">
        <div class="custom-scrollbar-nav flex overflow-x-auto space-x-4 sm:space-x-6">
          <router-link
            v-for="link in navLinks"
            :key="link.text"
            :to="link.to"
            class="whitespace-nowrap border-b-2 border-transparent px-1 py-3 text-sm font-medium text-gray-500 transition-colors duration-200 hover:border-cetpro/50 hover:text-cetpro-dark dark:text-gray-400 dark:hover:text-cetpro-light"
            active-class="!text-cetpro !dark:text-cetpro-light !border-cetpro font-semibold"
          >
            {{ link.text }}
          </router-link>
        </div>
      </nav>

      <div class="flex-grow p-6">
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
