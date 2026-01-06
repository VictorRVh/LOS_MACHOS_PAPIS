<template>
  <div
    class="rounded-2xl shadow-xl w-full max-w-xl max-h-[80vh] overflow-y-auto
    border bg-white dark:bg-gray-800
    border-gray-200 dark:border-gray-700 animate-fadeIn"
  >
    <!-- HEADER -->
    <div class="text-center pt-3 pb-2 px-4 bg-cetpro rounded-t-2xl">
      <h2 class="text-lg font-semibold text-white">
        {{ title }}
      </h2>

      <p v-if="subtitle" class="text-xs text-cetpro-light mt-1">
        {{ subtitle }}
      </p>
    </div>

    <!-- SECTION TITLE -->
    <div v-if="sectionTitle" class="px-4 mt-4">
      <div
        class="py-1 rounded text-center font-medium text-xs
        bg-gray-100 dark:bg-gray-700
        text-gray-700 dark:text-gray-200"
      >
        {{ sectionTitle }}
      </div>
    </div>

    <!-- ========================= -->
    <!-- LISTA DEFAULT (USUARIOS) -->
    <!-- ========================= -->
    <div
      v-if="layout === 'default'"
      class="px-6 mt-3 space-y-2 text-xs"
    >
      <div
        v-for="(item, index) in info"
        :key="index"
        class="flex justify-between border-b py-1
        border-gray-200 dark:border-gray-700"
      >
        <span class="font-semibold text-gray-700 dark:text-gray-300">
          {{ item.label }}:
        </span>

        <span
          class="text-right text-gray-800 dark:text-gray-200 truncate max-w-[60%]"
        >
          {{ item.value }}
        </span>
      </div>
    </div>

    <!-- ========================= -->
    <!-- LISTA STACKED (COMPETENCIAS) -->
    <!-- ========================= -->
    <div
      v-else
      class="px-6 mt-3 space-y-3 text-sm"
    >
      <div
        v-for="(item, index) in info"
        :key="index"
        class="border-b pb-2
        border-gray-200 dark:border-gray-700"
      >
        <div class="font-semibold text-gray-700 dark:text-gray-300">
          {{ item.label }}
        </div>

        <div
          class="mt-1 text-gray-800 dark:text-gray-200
          whitespace-pre-line break-words"
        >
          {{ item.value }}
        </div>
      </div>
    </div>

    <!-- SLOT -->
    <slot />

    <!-- FOOTER -->
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
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: "" },
  sectionTitle: { type: String, default: "Información" },

  info: {
    type: Array,
    default: () => []
  },

  /* 🔥 NUEVO */
  layout: {
    type: String,
    default: "default" // default | stacked
  }
});
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
