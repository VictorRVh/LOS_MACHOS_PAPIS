<template>
  <div
    class="rounded-2xl shadow-xl w-full overflow-hidden border
    bg-white dark:bg-gray-800
    border-gray-200 dark:border-gray-700"
  >

    <!-- HEADER AZUL -->
    <div v-if="showHeader" class="bg-cetpro h-28 relative">
      <div
        class="absolute left-1/2 transform -translate-x-1/2 -bottom-12
        w-24 h-24 bg-white dark:bg-gray-700
        rounded-full shadow-md flex items-center justify-center"
      >
        <img
          :src="avatarSrc"
          alt="avatar"
          class="w-20 h-20 rounded-full object-cover"
        />
      </div>
    </div>

    <!-- TITULO/TEXTO DEBAJO DEL AVATAR -->
    <div class="text-center mt-14">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
        {{ title }}
      </h2>

      <p
        v-if="subtitle"
        class="text-gray-500 dark:text-gray-400 text-sm -mt-1"
      >
        {{ subtitle }}
      </p>
    </div>

    <!-- SECCIÓN DE INFORMACIÓN -->
    <div class="px-4 mt-6">
      <div
        class="py-2 rounded text-center font-medium
        bg-gray-100 dark:bg-gray-700
        text-gray-700 dark:text-gray-200"
      >
        {{ sectionTitle }}
      </div>
    </div>

    <!-- LISTA -->
    <div class="px-10 mt-4 space-y-3 text-sm">
      <div
        v-for="(item, index) in info"
        :key="index"
        class="flex justify-between border-b py-1
        border-gray-200 dark:border-gray-700"
      >
        <span class="font-semibold text-gray-700 dark:text-gray-300">
          {{ item.label }}:
        </span>

        <span class="text-right text-gray-800 dark:text-gray-200">
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
        text-white px-4 py-2 rounded-md"
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
