<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { RouterLink } from 'vue-router'
import {
  ClockIcon,
  ArchiveBoxXMarkIcon, XCircleIcon
} from '@heroicons/vue/24/outline'
import Button from '@/components/ui/Button.vue'
import useNotificacionesStore from '../store/Notificaciones/UseNotificacionesStore'

const route = useRoute()
/* Estado local */
const notificacionesStore = useNotificacionesStore();

const allNotifications = ref([])
const loading = ref(null)
const highlightedId = ref(null)

/* Computed */
const unreadNotifications = computed(() => allNotifications.value.filter(n => !n.isRead))
const readNotifications = computed(() => allNotifications.value.filter(n => n.isRead))

/* Cargar */
const loadNotifications = async () => {
  await notificacionesStore.loadNotificaciones();
  loading.value = notificacionesStore.notificacionesLoading;

  allNotifications.value = notificacionesStore.notificaciones.map(n => ({
    id: n.id,
    icon: ClockIcon,
    titulo: n.titulo,
    descripcion: n.descripcion,
    link: n.link,
    // time: formatDistanceToNow(new Date(n.created_at), {
    //   addSuffix: true,
    //   locale: es
    // }),
    isRead: n.leido == 1,
  }));
}

/* Marcar todo como leído */
const markAllAsRead = async () => {
  await notificacionesStore.loadNotificacionesMarcarTodo();
  allNotifications.value.forEach(n => n.isRead = true);
  loadNotifications()
}

const markOneAsRead = async (n) => {
  await notificacionesStore.loadNotificacionesMarcarLeido(n.id);
  n.isRead = true;
  await loadNotifications()
}

onMounted(async () => {
  await loadNotifications()

  // ── Highlight desde query param ──────────────────────────
  const id = route.query.highlight
  if (id) {
    highlightedId.value = Number(id)

    // Scroll suave hacia la tarjeta
    await nextTick()
    const el = document.getElementById(`notif-${id}`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' })

    // Quitar el highlight después de 2.5 s
    setTimeout(() => { highlightedId.value = null }, 2500)
  }
})
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto">

    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Todas las Notificaciones</h1>

      <div class="flex gap-2">
        <button variant="outline" @click="markAllAsRead">
          Marcar todas como leídas
        </button>

        <!-- <button variant="destructive" @click="clearRead">
          <ArchiveBoxXMarkIcon class="w-5 h-5 mr-1" />
          Limpiar leídas
        </button> -->
      </div>
    </header>

    <!-- LISTA -->
    <div v-if="!loading && allNotifications.length > 0" class="space-y-4">

      <div v-for="n in allNotifications" :key="n.id" :id="`notif-${n.id}`" class="flex items-start gap-4 p-4 rounded-lg shadow-sm border
         transition-colors duration-700
         bg-white dark:bg-gray-800
         border-gray-200 dark:border-gray-700" :class="{
          'bg-blue-50 dark:bg-blue-900/30 border-blue-400 dark:border-blue-500 ring-2 ring-blue-300':
            highlightedId === n.id
        }">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
          <ClockIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
        </div>

        <div class="flex-grow">
          <p class="font-semibold text-gray-900 dark:text-gray-100">
            {{ n.titulo }}
          </p>
          <p class="text-gray-600 dark:text-gray-300">
            {{ n.descripcion }}
          </p>

          <p class="text-xs text-gray-400 mt-1">
            {{ n.time }}
          </p>

          <button v-if="!n.isRead" @click="markOneAsRead(n)" type="button" class="text-amber-500 hover:text-amber-700">
            Marcar como leído
          </button>

          <!-- <RouterLink :to="n.link" class="text-sm text-cetpro hover:underline mt-2 inline-block">
            Ver detalle →
          </RouterLink> -->
        </div>

        <span v-if="!n.isRead" class="w-3 h-3 rounded-full bg-cetpro" title="No leído"></span>
      </div>
    </div>

    <!-- SIN NOTIFICACIONES -->
    <div v-else-if="!loading" class="text-center text-gray-500 mt-16">
      No hay notificaciones.
    </div>

    <!-- LOADING -->
    <div v-else class="text-center py-20 text-gray-500">
      Cargando notificaciones...
    </div>

  </div>
</template>
