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
    default: 'Etiqueta'
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
    class="base-v-select w-full text-sm"
  >
    <template #no-options>
      No hay opciones disponibles
    </template>
    <template #no-options-found>
      No se encontraron coincidencias
    </template>
  </v-select>
</template>

<style scoped>
:deep(.base-v-select) {
  --vs-border-width: 0;
  --vs-border-radius: 4px;
  --vs-dropdown-z-index: 100;
  --vs-controls-color: #64748b;
  --vs-selected-color: #0f172a;
  --vs-search-input-color: #0f172a;
  --vs-search-input-placeholder-color: #94a3b8;
  --vs-dropdown-bg: #ffffff;
  --vs-dropdown-color: #0f172a;
}

:deep(.base-v-select .vs__dropdown-toggle) {
  @apply h-9 min-h-9 rounded-[3px] border border-slate-300 bg-white px-3 py-0 text-sm text-slate-800 shadow-none transition-colors duration-150;
}

:deep(.base-v-select:hover .vs__dropdown-toggle) {
  @apply border-cetpro/45;
}

:deep(.base-v-select.vs--open .vs__dropdown-toggle),
:deep(.base-v-select.vs--focused .vs__dropdown-toggle) {
  @apply border-cetpro ring-2 ring-cetpro/15;
}

:deep(.base-v-select .vs__selected-options) {
  @apply min-h-0 flex-nowrap items-center gap-1 py-0;
}

:deep(.base-v-select .vs__selected) {
  @apply m-0 leading-5 text-sm text-slate-800;
}

:deep(.base-v-select .vs__search) {
  @apply m-0 leading-5;
}

:deep(.base-v-select .vs__search::placeholder) {
  @apply text-sm leading-5 text-slate-400;
}

:deep(.base-v-select .vs__actions) {
  @apply items-center pr-0.5;
}

:deep(.base-v-select .vs__clear) {
  @apply text-slate-400 transition-colors duration-150;
}

:deep(.base-v-select .vs__clear:hover) {
  @apply text-slate-600;
}

:deep(.base-v-select .vs__open-indicator) {
  @apply text-slate-500 transition-transform duration-150;
}

:deep(.base-v-select .vs__open-indicator),
:deep(.base-v-select .vs__clear) {
  transform: scale(0.9);
}

:deep(.base-v-select.vs--open .vs__open-indicator) {
  transform: rotate(180deg);
}

:deep(.base-v-select .vs__dropdown-menu) {
  @apply mt-1 max-h-[180px] overflow-y-auto rounded-sm border border-slate-200 bg-white py-1 text-sm text-slate-800 shadow-sm;
}

:deep(.base-v-select .vs__dropdown-option) {
  @apply px-3 py-2 text-sm text-slate-700;
}

:deep(.base-v-select .vs__dropdown-option--highlight) {
  @apply bg-cetpro/10 text-cetpro;
}

:deep(.base-v-select .vs__dropdown-option--selected) {
  @apply bg-cetpro/10 font-medium text-cetpro;
}

:deep(.dark .base-v-select) {
  --vs-controls-color: #94a3b8;
  --vs-selected-color: #e2e8f0;
  --vs-search-input-color: #f8fafc;
  --vs-search-input-placeholder-color: #94a3b8;
  --vs-dropdown-bg: #0f172a;
  --vs-dropdown-color: #e2e8f0;
}

:deep(.dark .base-v-select .vs__dropdown-toggle) {
  @apply border-slate-600 bg-slate-800 text-slate-100;
}

:deep(.dark .base-v-select:hover .vs__dropdown-toggle) {
  @apply border-cetpro-light/55;
}

:deep(.dark .base-v-select.vs--open .vs__dropdown-toggle),
:deep(.dark .base-v-select.vs--focused .vs__dropdown-toggle) {
  @apply border-cetpro-light ring-cetpro-light/20;
}

:deep(.dark .base-v-select .vs__selected) {
  @apply text-slate-100;
}

:deep(.dark .base-v-select .vs__search::placeholder) {
  @apply text-slate-400;
}

:deep(.dark .base-v-select .vs__clear) {
  @apply text-slate-500;
}

:deep(.dark .base-v-select .vs__clear:hover) {
  @apply text-slate-300;
}

:deep(.dark .base-v-select .vs__open-indicator) {
  @apply text-slate-400;
}

:deep(.dark .base-v-select .vs__dropdown-menu) {
  @apply border-slate-700 bg-slate-900 text-slate-100;
}

:deep(.dark .base-v-select .vs__dropdown-option) {
  @apply text-slate-200;
}

:deep(.dark .base-v-select .vs__dropdown-option--highlight) {
  @apply bg-cetpro-light/10 text-cetpro-light;
}

:deep(.dark .base-v-select .vs__dropdown-option--selected) {
  @apply bg-cetpro-light/10 text-cetpro-light;
}
</style>
