<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  modelValue: [String, Number, Object, null],
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Seleccione una opción' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'change']);

const updateValue = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
};
</script>

<template>
  <v-select
    :options="options"
    label="name"
    :reduce="option => option?.id ?? option"
    :modelValue="modelValue"
    @update:modelValue="updateValue"
    :placeholder="placeholder"
    :disabled="disabled"
    class="w-full"
  />
</template>

<style scoped>
.v-select {
  --vs-border-radius: 0.375rem;
  --vs-border-color: #d1d5db;
  --vs-border-width: 1px;
  --vs-dropdown-z-index: 100;
}
</style>
