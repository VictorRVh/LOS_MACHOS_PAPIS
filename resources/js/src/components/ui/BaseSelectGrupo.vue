<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  modelValue: [String, Number, Object, null],
  options: { type: Array, default: () => [] },
  label: { type: String, default: 'name' },
  placeholder: { type: String, default: 'Seleccione una opción' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'change']);

const updateValue = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
};
</script>

<!-- SELECT ANTERIOR -->

<!-- <template>
  <v-select
    :options="options"
    :label="label"
    :reduce="option => option?.id ?? option"
    :modelValue="modelValue"
    @update:modelValue="updateValue"
    :placeholder="placeholder"
    :disabled="disabled"
    class="w-full"
  />
</template> -->


<template>
  
  <v-select
    :options="options || []"
    :label="label"
    :reduce="option => option?.id ?? option"
    :modelValue="modelValue"
    @update:modelValue="updateValue"
    :placeholder="placeholder"
    :disabled="disabled"
    class="w-full">

     <!-- Slot para cuando no hay ninguna opción disponible -->
    <template #no-options>
      No hay opciones disponibles
    </template>

    <!-- Slot para cuando no se encuentra ninguna coincidencia -->
    <template #no-options-found>
      No se encontraron coincidencias
    </template>

  </v-select>

</template>

<style scoped>
.v-select {
  --vs-border-radius: 0.375rem;
  --vs-border-color: #d1d5db;
  --vs-border-width: 1px;
  --vs-dropdown-z-index: 100;
}
</style>
