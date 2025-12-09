<template>
  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
    <!-- Filas reales -->
    <slot></slot>

    <!-- Fila invisible si hay menos de 2 filas -->
    <tr v-if="filas < 2 && filas > 0">
      <td :colspan="columnas" style="height: 50px;"></td>
    </tr>
  </tbody>
</template>

<script setup>
const props = defineProps({
  filas: { type: Number, default: 0 },       // Número de filas reales
  columnas: { type: Number, required: true } // Número de columnas de la tabla
});



</script>
