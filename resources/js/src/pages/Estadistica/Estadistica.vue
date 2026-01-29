<template>
  <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
    <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-xl shadow p-4">
      <p class="text-sm text-gray-500">{{ stat.label }}</p>
      <p class="text-2xl font-bold">{{ stat.value }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const stats = ref([
  { label: 'Usuarios', value: 120 },
  { label: 'Cursos', value: 35 },
  { label: 'Módulos', value: 18 },
  { label: 'Reportes', value: 7 },
])
</script>

<style scoped>
/* Puedes personalizar estilos aquí si no usas Tailwind */
</style>
