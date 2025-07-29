<script setup>
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const props = defineProps({
  modelValue: [String, Number, Object, null],
  options: { type: Array, default: () => [] },
  label: { type: String, default: 'label' }, 
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
  :label="label"
  :placeholder="placeholder"
  :disabled="disabled"
  :reduce="option => option?.id ?? option"
  :modelValue="modelValue"
  @update:modelValue="updateValue"
  class="w-full"
  />
</template>
