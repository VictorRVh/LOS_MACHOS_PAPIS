<template>
  <div class="flex justify-end items-center w-full max-w-5xl gap-4">
    <!-- Resultado dinámico -->
    <div class="flex items-center gap-1 text-sm text-gray-600">
      <span>Resultado:</span>
      <span class="bg-blue-100 text-blue-800 font-semibold px-2 py-0.5 rounded-full text-xs">
        {{ totalResultados }}
      </span>
    </div>

    <!-- Selector de campo -->
    <select
      v-model="selected"
      class="h-10 px-3 py-2 text-sm border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>

    <!-- Campo de búsqueda con lupa -->
    <div class="relative w-64">
      <input
        type="text"
        v-model="searchQuery"
        @input="onInput"
        class="w-full h-10 pl-4 pr-10 text-sm border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <!-- Ícono lupa :placeholder="placeholder" -->
      <div
       
        class="absolute inset-y-0 right-0 flex items-center justify-center bg-gray-800 w-10 h-full rounded-r-md cursor-pointer hover:bg-gray-700"
      >
        <MagnifyingGlassIcon class="w-5 h-5 text-white" />
      </div>
    </div>
  </div>
</template>

  
  <script setup>
  import { ref } from 'vue'
  import { MagnifyingGlassIcon } from '@heroicons/vue/24/solid'
  
  const props = defineProps({
    placeholder: { type: String, default: 'Buscar por nombre, apellido o DNI' },
    options: { type: Array, default: () => [] },
    totalResultados: { type: Number, default: 0 }, // total filtrado
    mostrando: { type: Number, default: 0 }, // cuántas filas se muestran (por paginación o límite)
  })
  
  const emit = defineEmits(['search'])
  
  const selected = ref(props.options.length > 0 ? props.options[0].value : '')
  const searchQuery = ref('')
  function onInput() {
    emit('search', { field: selected.value, query: searchQuery.value })
  }
  </script>
  