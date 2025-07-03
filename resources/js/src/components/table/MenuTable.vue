<template>
  <div class="relative inline-block text-left" ref="menuRef">
    <button @click="toggleMenu" class="text-xl hover:text-cetpro dark:hover:text-cetpro-light">
      ⋮
    </button>

    <transition name="fade">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-[180px] origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 z-20"
      >
        <div class="py-1 text-sm text-gray-700 dark:text-gray-200">
          <button
            v-if="actions.view"
            @click="emitAndClose('view')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <EyeIcon class="w-5 h-5" />
            Ver {{ entityLabel }}
          </button>

          <button
            v-if="actions.edit"
            @click="emitAndClose('edit')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <PencilIcon class="w-5 h-5" />
            Editar {{ entityLabel }}
          </button>

          <button
            v-if="actions.delete"
            @click="emitAndClose('delete')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <TrashIcon class="w-5 h-5" />
            Eliminar {{ entityLabel }}
          </button>

          <button
            v-if="actions.download"
            @click="emitAndClose('download')"
            class="flex items-center w-full gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <ArrowDownTrayIcon class="w-5 h-5" />
            Descargar {{ entityLabel }}
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'


const props = defineProps({
  actions: {
    type: Object,
    default: () => ({
      view: false,
      edit: false,
      delete: false,
      download: false,
    }),
  },
  entityLabel: {
    type: String,
    default: 'elemento', // si no pasas nada, dirá: "Ver elemento"
  },
})

const emit = defineEmits(['view', 'edit', 'delete', 'download'])

const isOpen = ref(false)
const toggleMenu = () => (isOpen.value = !isOpen.value)
const emitAndClose = (action) => {
  emit(action)
  isOpen.value = false
}

const menuRef = ref(null)
const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
