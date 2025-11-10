<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import useComisionesStore from '../../store/Comision/useComisionDocenteStore'
import useDocenteStore from '../../store/useUserStore'
import { UsersIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'
import { AcademicCapIcon } from '@heroicons/vue/24/solid'

const route = useRoute()
const comisionesStore = useComisionesStore()
const docenteStore = useDocenteStore()

// Obtenemos el id del docente desde la ruta o del usuario logueado
const idDocente = route.params.id || docenteStore.user?.id || 1

const cargando = ref(false)

if (!comisionesStore.comisiones?.length) await comisionesStore.loadComisiones(idDocente);
 console.log("dato usauroa", useDocenteStore.user)
</script>

<template>
  <section class="p-4">
    <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2">
      <ClipboardDocumentListIcon class="w-7 h-7 text-blue-600" />
      Mis Comisiones
    </h2>

    <!-- Loader -->
    <div v-if="cargando" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="n in 3"
        :key="n"
        class="animate-pulse bg-gray-200 dark:bg-gray-700 rounded-xl h-32"
      ></div>
    </div>

    <!-- Sin comisiones -->
    <div
      v-else-if="!comisionesStore.comisiones.comisiones?.length"
      class="text-center text-gray-500 mt-8"
    >
      <AcademicCapIcon class="w-10 h-10 mx-auto text-gray-400 mb-2" />
      <p>No perteneces a ninguna comisión actualmente.</p>
    </div>

    <!-- Grid de comisiones -->
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
    >
      <div
        v-for="comision in comisionesStore.comisiones.comisiones"
        :key="comision.id"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 border border-gray-100 dark:border-gray-700 transition-transform transform hover:scale-[1.02] hover:shadow-lg"
      >
        <div class="flex items-center gap-2 mb-2">
          <UsersIcon class="w-6 h-6 text-blue-600" />
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            {{ comision.titulo }}
          </h3>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">
          {{ comision.descripcion }}
        </p>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Puedes personalizar la altura de las tarjetas aquí si deseas */
</style>
