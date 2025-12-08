<template>
  <div
    class="rounded-2xl shadow-xl w-full max-w-xl max-h-[80vh] overflow-y-auto border
    bg-white dark:bg-gray-800
    border-gray-200 dark:border-gray-700"
  >

    <!-- HEADER AZUL -->

    <!-- TITULO/TEXTO DEBAJO DEL AVATAR -->
    <div class="text-center pt-3  pb-1 px-4 bg-cetpro">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
        {{ title }}
      </h2>

      <p
        v-if="subtitle"
        class="text-gray-100 dark:text-gray-300 text-xs my-1"
      >
        {{ subtitle }}
      </p>
    </div>

    <!-- SECCIÓN DE INFORMACIÓN -->
    <div class="px-4 mt-4">
      <div
        class="py-1 rounded text-center font-medium text-xs
        bg-gray-100 dark:bg-gray-700
        text-gray-700 dark:text-gray-200"
      >
        {{ sectionTitle }}
      </div>
    </div>

    <!-- LISTA -->
    <div class="px-6 mt-3 space-y-2 text-xs">
      <div
        v-for="(item, index) in info"
        :key="index"
        class="flex justify-between border-b py-1
        border-gray-200 dark:border-gray-700"
      >
        <span class="font-semibold text-gray-700 dark:text-gray-300">
          {{ item.label }}:
        </span>

        <span class="text-right text-gray-800 dark:text-gray-200 truncate max-w-[60%]">
          {{ item.value }}
        </span>
      </div>
    </div>

    <!-- SLOT -->
    <div>
      <slot></slot>
    </div>

    <!-- BOTÓN CERRAR -->
    <div class="p-4 flex justify-end">
      <button
        @click="$emit('close')"
        class="bg-green-600 hover:bg-green-700
        text-white px-4 py-2 rounded-md text-sm"
      >
        Cerrar
      </button>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  subtitle: String,
  sectionTitle: {
    type: String,
    default: "Información"
  },

  avatarName: { type: String, default: "Usuario" },
  avatar: String,
  showHeader: { type: Boolean, default: true },

  info: {
    type: Array,
    default: () => []
  }
});

const avatarSrc = computed(() =>
  props.avatar ||
  `https://ui-avatars.com/api/?name=${encodeURIComponent(props.avatarName)}&background=random&size=128`
);
</script>

<style scoped>
/* animación de entrada */
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
