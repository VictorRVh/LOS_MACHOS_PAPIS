<script setup>
import { computed } from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const props = defineProps({
  modelValue: [String, Number, null], // Solo el ID
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: 'Etiqueta'
  },
  placeholder: {
    type: String,
    default: 'Seleccione una opción'
  },
  clearable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue'])

// Buscar el objeto a partir del ID recibido
const selectedOption = computed(() => {
  return props.options.find(opt => opt.id === props.modelValue) || null
})
</script>

<template>
  <v-select
    :modelValue="selectedOption"
    @update:modelValue="emit('update:modelValue', $event?.id || null)"
    :options="props.options"
    :label="props.label"
    :placeholder="props.placeholder"
    :clearable="props.clearable"
    class="text-sm"
  >
    <template #no-options>
      No hay opciones disponibles
    </template>
    <template #no-options-found>
      No se encontraron coincidencias
    </template>
  </v-select>
</template>
