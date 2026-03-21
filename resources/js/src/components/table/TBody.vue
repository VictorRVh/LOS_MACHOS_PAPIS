<template>
  <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-gray-800">
    <slot></slot>

    <tr v-if="filas < 2 && filas > 0">
      <td :colspan="columnas" style="height: 50px;"></td>
    </tr>
  </tbody>
</template>

<script setup>
const props = defineProps({
  filas: { type: Number, default: 0 },
  columnas: { type: Number, required: true }
});
</script>
