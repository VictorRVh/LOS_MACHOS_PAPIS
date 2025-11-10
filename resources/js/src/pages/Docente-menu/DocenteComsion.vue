<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import useComisionesStore from '../../store/Comision/useComisionDocenteStore'
import useDocenteStore from '../../store/useUserStore'

// Heroicons
import {
  UsersIcon,
  ClipboardDocumentListIcon,
  EnvelopeIcon,
  PhoneIcon,
} from '@heroicons/vue/24/outline'
import { AcademicCapIcon } from '@heroicons/vue/24/solid'

const route = useRoute()
const comisionesStore = useComisionesStore()
const docenteStore = useDocenteStore()

watch(
  () => docenteStore.user,
  async (nuevoUsuario) => {
    if (nuevoUsuario && nuevoUsuario.id) {
      console.log('Usuario cargado:', nuevoUsuario)
      await comisionesStore.loadComisiones(nuevoUsuario.id)
    }
  },
  { immediate: true }
)
</script>

<template>
  <section class="p-6">
    <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
      <ClipboardDocumentListIcon class="w-8 h-8 text-blue-600" />
      Mis Comisiones
    </h2>

    <!-- Loader -->
    <div
      v-if="comisionesStore?.comisionesLoading"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="n in 3"
        :key="n"
        class="animate-pulse bg-gray-200 dark:bg-gray-700 rounded-2xl h-40"
      ></div>
    </div>

    <!-- Sin comisiones -->
    <div
      v-else-if="!comisionesStore.comisiones.comisiones?.length"
      class="text-center text-gray-500 mt-10"
    >
      <AcademicCapIcon class="w-12 h-12 mx-auto text-gray-400 mb-3" />
      <p class="text-lg">No perteneces a ninguna comisión actualmente.</p>
    </div>

    <!-- Grid de comisiones -->
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="comision in comisionesStore.comisiones.comisiones"
        :key="comision.id"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 transition-transform transform hover:scale-[1.02] hover:shadow-lg"
      >
        <!-- Encabezado -->
        <div class="flex flex-col items-center mb-4 text-center">
          <UsersIcon class="w-12 h-12 text-blue-600 mb-2" />
          <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            {{ comision.titulo }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ comision.descripcion }}
          </p>
        </div>

        <!-- Integrantes -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
          <h4 class="text-base font-semibold mb-2 flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <UsersIcon class="w-5 h-5 text-blue-500" />
            Integrantes
          </h4>

          <ul class="space-y-2 max-h-48 overflow-y-auto pr-1">
            <li
              v-for="user in comision.usuarios"
              :key="user.id"
              class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-3"
            >
              <div class="flex flex-col">
                <span class="font-medium text-gray-800 dark:text-gray-100">
                  {{ user.nombre_completo }}
                </span>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                  <EnvelopeIcon class="w-4 h-4 text-blue-500" />
                  <span>{{ user.email }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                  <PhoneIcon class="w-4 h-4 text-green-500" />
                  <span>{{ user.telefono || 'Sin teléfono' }}</span>
                </div>
              </div>
              <span
                class="text-xs text-gray-500 dark:text-gray-400 font-mono"
              >DNI: {{ user.dni }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
ul::-webkit-scrollbar {
  width: 6px;
}
ul::-webkit-scrollbar-thumb {
  background-color: #a0aec0;
  border-radius: 10px;
}
ul::-webkit-scrollbar-thumb:hover {
  background-color: #718096;
}
</style>
