<script setup>
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const props = defineProps({
    modelValue: [String, Number, Object, Array, Boolean, null],
    options: {
        type: Array,
        default: () => []
    },
    label: {
        type: String,
        default: 'label'
    },
    placeholder: {
        type: String,
        default: 'Seleccione una opción'
    },
    multiple: {
        type: Boolean,
        default: false
    },
    clearable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <v-select
    :modelValue="props.modelValue"
    @update:modelValue="emit('update:modelValue', $event)"
    :options="props.options"
    :label="props.label"
    :placeholder="props.placeholder"
    :multiple="props.multiple"
    :clearable="props.clearable"
    class="text-sm"
  >
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
