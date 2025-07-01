<script setup>
defineProps({
  modelValue: {
    type: [Number, String],
    default: 0,             
  },
  label: {
    type: String,
    default: '',
  },
  activeValue: {
    type: [Number, String],
    default: 1,
  },
  inactiveValue: {
    type: [Number, String],
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="flex items-center gap-2">
    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">{{ label }}</label>
    <button type="button"
      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
      :class="modelValue ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
      @click="emit('update:modelValue', !modelValue)">
      <span
        class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        :class="modelValue ? 'translate-x-5' : 'translate-x-0'" />
    </button>
  </div>
</template>
