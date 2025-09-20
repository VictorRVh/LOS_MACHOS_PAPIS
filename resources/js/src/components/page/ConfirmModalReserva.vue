<!-- ModalConfirmarReserva.vue -->
<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-md p-6">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
        Confirmar Reserva
      </h2>

      <p class="text-gray-600 dark:text-gray-300">
        ¿Pasar a <strong>RESERVA</strong> a
        <span class="font-medium">{{ estudiante?.estudiante }}</span>?
        <!-- <span>{{ estudiante }}</span> -->
      </p>

      <div class="flex justify-end gap-3 mt-6">
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          No
        </button>
        <button
          @click="$emit('confirm')"
          class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600"
        >
          Sí
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  estudiante: Object
})

defineEmits(['close', 'confirm'])
</script>
