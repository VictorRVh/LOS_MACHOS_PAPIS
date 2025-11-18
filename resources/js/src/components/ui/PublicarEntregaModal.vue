<script setup>
import { ref, computed, onMounted } from "vue";

const props = defineProps({
  programacion: { type: Object, required: true },
  periodoId: { type: Number, required: true },
  grupos: { type: Array, default: () => [] } // si luego quieres cargar desde backend
});

const emit = defineEmits(["close", "masivo", "personalizado"]);

// Grupos seleccionados
const selectedGroups = ref([]);

// Seleccionar / deseleccionar todos
const toggleAll = () => {
  if (selectedGroups.value.length === props.grupos.length) {
    selectedGroups.value = [];
  } else {
    selectedGroups.value = props.grupos.map(g => g.id);
  }
};

// Validación
const canSubmitPersonalizado = computed(() => selectedGroups.value.length > 0);

// Enviar personalizado
const enviarPersonalizado = () => {
  if (!canSubmitPersonalizado.value) return;
  emit("personalizado", selectedGroups.value);
};

// Enviar masivo
const enviarMasivo = () => {
  emit("masivo");
};
</script>

<template>
  <!-- Overlay -->
  <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
    <!-- Modal -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6 space-y-6">

      <!-- Título -->
      <header class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
          Publicar Programación
        </h2>

        <button
          @click="emit('close')"
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          ✕
        </button>
      </header>

      <!-- Contenido -->
      <div class="space-y-4">
        <p class="text-gray-700 dark:text-gray-300">
          Selecciona cómo deseas publicar la entrega para los docentes.
        </p>

        <!-- Botón Masivo -->
        <button
          @click="enviarMasivo"
          class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg shadow"
        >
          Publicar para todos los grupos
        </button>

        <!-- Divider -->
        <div class="flex items-center my-3">
          <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
          <span class="px-3 text-sm text-gray-500 dark:text-gray-400">o</span>
          <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
        </div>

        <!-- Publicación Personalizada -->
        <div>
          <p class="font-medium mb-2 text-gray-800 dark:text-gray-200">
            Publicación Personalizada
          </p>

          <!-- Seleccionar todos -->
          <button
            class="text-xs text-cetpro hover:underline mb-2"
            @click="toggleAll"
          >
            {{ selectedGroups.length === grupos.length ? "Deseleccionar todos" : "Seleccionar todos" }}
          </button>

          <!-- Lista de grupos -->
          <div class="max-h-40 overflow-y-auto border rounded-lg p-2 space-y-2
            border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">

            <label
              v-for="grupo in grupos"
              :key="grupo.id"
              class="flex items-center space-x-2 cursor-pointer"
            >
              <input
                type="checkbox"
                v-model="selectedGroups"
                :value="grupo.id"
                class="w-4 h-4"
              />
              <span class="text-gray-700 dark:text-gray-300">{{ grupo.nombre_grupo }}</span>
            </label>

          </div>

        </div>

      </div>

      <!-- Footer -->
      <footer class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="emit('close')"
          class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
        >
          Cancelar
        </button>

        <button
          @click="enviarPersonalizado"
          :disabled="!canSubmitPersonalizado"
          class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Publicar Personalizado
        </button>
      </footer>

    </div>
  </div>
</template>

<style scoped>
/* Animación suave */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
