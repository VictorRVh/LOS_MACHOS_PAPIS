<template>
  <div class="flex justify-end items-center w-full max-w-5xl gap-4">
    <!-- Resultado dinámico -->
    <div class="flex text-gray-800 dark:text-gray-300 items-center gap-1 text-md">
      <span>Resultados:</span>
      <span
        class="border border-gray-300 bg-white text-gray-800 text-sm px-3 py-1 rounded-md shadow-sm min-w-[40px] text-center"
      >
        {{ totalResultados }}
      </span>
    </div>

    <!-- Botón de orden -->
    <div class="relative">
      <button
        @click="toggleOrdenMenu"
        class="w-8 h-8 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center border"
      >
        <AdjustmentsVerticalIcon class="w-7 h-7 text-gray-700 dark:text-gray-200" />
      </button>

      <!-- Menú desplegable de orden -->
      <div
        v-if="mostrarOrden"
        class="absolute right-0 mt-2 w-16 bg-white dark:bg-gray-800 border rounded shadow-md z-50"
      >
        <ul>
          <li>
            <button
              class="w-full text-left px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
              @click="cambiarOrden('asc')"
            >
              A - Z
            </button>
          </li>
          <li>
            <button
              class="w-full text-left px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
              @click="cambiarOrden('desc')"
            >
              Z - A
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Campo de búsqueda -->
    <div class="relative w-64">
      <input
        type="text"
        v-model="searchQuery"
        @input="emitSearch"
        placeholder="Buscar"
        class="w-full h-10 pl-4 pr-10 text-sm border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <!-- Ícono lupa -->
      <div
        class="absolute inset-y-0 right-0 flex items-center justify-center bg-gray-800 w-10 h-full rounded-r-md cursor-pointer hover:bg-gray-700"
      >
        <MagnifyingGlassIcon class="w-5 h-5 text-white" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
  totalResultados: { type: Number, default: 0 },
  campoOrden: { type: String, default: "id" }, // por defecto, "apellidos"
});

const emit = defineEmits(["search"]);

const searchQuery = ref("");
const orden = ref("asc");
const mostrarOrden = ref(false);

function emitSearch() {
  emit("search", {
    query: searchQuery.value,
    orderDirection: orden.value,
    orderBy: props.campoOrden,
  });
  
}

function toggleOrdenMenu() {
  mostrarOrden.value = !mostrarOrden.value;
}

function cambiarOrden(direccion) {
  orden.value = direccion;
  mostrarOrden.value = false;
  emitSearch();
}
</script>
