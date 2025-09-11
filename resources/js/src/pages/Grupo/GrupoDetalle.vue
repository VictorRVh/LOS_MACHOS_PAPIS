<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const nombreGrupo = ref('');

onMounted(() => {
  // Aquí harías una llamada a la API para obtener los datos del grupo por su ID
  // Ejemplo: GET /api/grupos/${props.id}
  nombreGrupo.value = `Grupo ${props.id}`; // Simulación
});
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
      {{ nombreGrupo }}
    </h1>
    
    <!-- Aquí es donde se renderizarán las vistas del submenú -->
    <router-view />
  </div>
</template>