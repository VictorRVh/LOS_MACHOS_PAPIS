<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  labelKey: {
    type: String,
    default: 'Nombre',
  },
});
const emit = defineEmits(['remove']);
</script>

<template>
  <ul class="flex flex-wrap gap-2 mt-2">
    <li
      v-for="item in items"
      :key="item.id"
      class="inline-flex items-center rounded-full text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300 px-3 py-1"
    >
      {{ item[labelKey] }}
      <span
        class="ml-2 text-red-500 cursor-pointer hover:text-red-700 dark:hover:text-red-300"
        @click="emit('remove', item)"
      >
        ✕
      </span>
    </li>
  </ul>
</template>
