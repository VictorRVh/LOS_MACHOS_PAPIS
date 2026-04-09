<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import useGrupoStore from '../../store/Grupo/useGrupoStore';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';
import ModuleHeader from '../../components/page/ModuleHeader.vue';
import ModuleNavigation from '../../components/page/ModuleNavigation.vue';

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
  { text: 'Unidades Didácticas', to: { name: 'grupo.capacidades.terminales', params: { id: groupId } } },
  { text: 'Prácticas', to: { name: 'grupo.practicas', params: { id: groupId } } },
  { text: 'Alumnos', to: { name: 'grupo.alumnos', params: { id: groupId } } },
];

const tituloPrincipal = computed(() => {
  if (!infoGrupo.value) return 'Grupo';
  return `${infoGrupo.value.especialidad}`;
});

const subTitulo = computed(() => {
  if (!infoGrupo.value) return 'Cargando detalles...';
  return `Módulo ${infoGrupo.value.modulo} · Seccion ${infoGrupo.value.seccion} · Turno ${infoGrupo.value.turno}`;
});

const metadataItems = computed(() => {
  if (!infoGrupo.value) return [];

  return [
    { label: 'Módulo', value: infoGrupo.value.modulo },
    { label: 'Sección', value: infoGrupo.value.seccion },
    { label: 'Turno', value: infoGrupo.value.turno },
  ];
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
      <ModuleHeader :title="tituloPrincipal" :metadata="metadataItems" actions-target-id="grupo-header-actions" />
      <ModuleNavigation :links="navLinks" />

      <div class="flex-grow p-6">
        <router-view />
      </div>
    </template>
  </div>
</template>
