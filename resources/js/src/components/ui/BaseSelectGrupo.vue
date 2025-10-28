<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  modelValue: [String, Number, Object, null],
  options: { type: Array, default: () => [] },
  label: { type: String, default: 'name' },
  placeholder: { type: String, default: 'Seleccione una opción' },
  disabled: { type: Boolean, default: false },

  // 🔹 NUEVOS PARAMETROS DE SCROLL
  scrollable: { type: Boolean, default: true }, // activar/desactivar scroll
  maxHeight: { type: [String, Number], default: 180 }, // altura máxima del dropdown en px
});

const emit = defineEmits(['update:modelValue', 'change']);

const updateValue = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
};
</script>

<template>
  <v-select
    :options="options || []"
    :label="label"
    :reduce="option => option?.id ?? option"
    :modelValue="modelValue"
    @update:modelValue="updateValue"
    :placeholder="placeholder"
    :disabled="disabled"
    class="w-full"
    :menu-style="scrollable ? { 'max-height': maxHeight + 'px', 'overflow-y': 'auto' } : {}"
  >
    <!-- Slot cuando no hay opciones -->
    <template #no-options>
      No hay opciones disponibles
    </template>

    <!-- Slot cuando no hay coincidencias -->
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

/* 🔹 Scroll global para el dropdown del vue-select */
.vs__dropdown-menu {
  max-height: var(--vs-dropdown-max-height, 180px); /* valor por defecto */
  overflow-y: auto;
  scrollbar-width: thin;
}

/* 🔹 Scroll personalizado en navegadores webkit */
.vs__dropdown-menu::-webkit-scrollbar {
  width: 6px;
}

.vs__dropdown-menu::-webkit-scrollbar-thumb {
  background-color: #9ca3af;
  border-radius: 3px;
}

.vs__dropdown-menu::-webkit-scrollbar-thumb:hover {
  background-color: #6b7280;
}
</style>
