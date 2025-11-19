<script setup>
import { ref, computed } from "vue";
import BaseSelect from "./BaseSelect.vue";

const props = defineProps({
  programacion: { type: Object, required: true },
  periodoId: { type: Number, required: true },
  grupos: { type: Array, default: () => [] }
});

const emit = defineEmits(["close", "masivo", "personalizado"]);

// Grupos seleccionados
const selectedGroups = ref([]);
const selectedGroup = ref(null);

// Opciones para el select (grupos no seleccionados)
const grupoOptions = computed(() => {
  const selectedIds = selectedGroups.value.map(g => g.id);
  return props.grupos.filter(g => !selectedIds.includes(g.id));
});

// Validación
const canSubmitPersonalizado = computed(() => selectedGroups.value.length > 0);

// Verificar si se pueden añadir todos
const canShowAddAllGroups = computed(() => {
  return grupoOptions.value.length > 0;
});

// Cuando se selecciona un grupo del select
const onGroupSelect = (grupo) => {
  if (grupo && !selectedGroups.value.find(g => g.id === grupo.id)) {
    selectedGroups.value.push(grupo);
    selectedGroup.value = null; // Limpiar el select
  }
};

// Remover un grupo seleccionado
const onGroupRemove = (grupo) => {
  selectedGroups.value = selectedGroups.value.filter(g => g.id !== grupo.id);
};

// Añadir todos los grupos
const onAddAllGroups = () => {
  selectedGroups.value = [...props.grupos];
};

// Enviar personalizado
const enviarPersonalizado = () => {
  if (!canSubmitPersonalizado.value) return;
  const groupIds = selectedGroups.value.map(g => g.id);
  emit("personalizado", groupIds);
  emit("close"); // Cerrar el modal inmediatamente
};

// Enviar masivo
const enviarMasivo = () => {
  emit("masivo");
  emit("close"); // Cerrar el modal inmediatamente
};
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <!-- Overlay con backdrop-blur -->
      <div 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        @click.self="emit('close')"
      >
        <!-- Modal -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 relative">
          
          <!-- Header -->
          <div class="flex justify-between items-start mb-4">
            <h2 class="font-bold text-lg text-cetpro dark:text-cetpro-light">
              Publicar Programación
            </h2>

            <button
              @click="emit('close')"
              class="text-xl text-gray-500 hover:text-red-600 transition-colors"
            >
              ✕
            </button>
          </div>

          <!-- Contenido -->
          <div class="space-y-5">
            <p class="text-gray-700 dark:text-gray-300">
              Selecciona cómo deseas publicar la entrega para los docentes.
            </p>

            <!-- Botón Masivo -->
            <button
              @click="enviarMasivo"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg shadow transition-colors"
            >
              Publicar para todos los grupos
            </button>

            <!-- Divider -->
            <div class="flex items-center my-4">
              <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
              <span class="px-3 text-sm text-gray-500 dark:text-gray-400">o</span>
              <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
            </div>

            <!-- Publicación Personalizada -->
            <div class="space-y-3">
              <!-- BaseSelect para añadir grupos -->
              <div>
                <label class="text-sm font-semibold dark:text-slate-300 block mb-2">
                  Añadir grupo
                </label>
                <BaseSelect 
                  v-model="selectedGroup" 
                  :options="grupoOptions" 
                  label="nombre_grupo"
                  placeholder="Seleccione un grupo" 
                  @update:modelValue="onGroupSelect"
                />
              </div>

              <!-- Grupos seleccionados -->
              <div class="w-full space-y-3">
                <div class="flex justify-between items-center gap-6">
                  <label class="text-sm font-semibold dark:text-slate-300">
                    Grupos Seleccionados
                  </label>
                  <div 
                    v-if="canShowAddAllGroups"
                    class="cursor-pointer text-sm font-bold text-sky-500 hover:underline dark:text-sky-400"
                    @click="onAddAllGroups"
                  >
                    Añadir todos los grupos
                  </div>
                </div>

                <!-- SelectedChips -->
                <div class="max-h-[130px] overflow-y-auto pr-1 md:max-h-[90px]">
                  <ul v-if="selectedGroups.length > 0" class="flex flex-wrap gap-2 mt-1">
                    <li
                      v-for="grupo in selectedGroups"
                      :key="grupo.id"
                      class="inline-flex items-center rounded-full text-[11px] font-medium bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300 px-2.5 py-1"
                    >
                      {{ grupo.nombre_grupo }}
                      <span
                        class="ml-2 text-red-500 cursor-pointer hover:text-red-700 dark:hover:text-red-300"
                        @click="onGroupRemove(grupo)"
                      >
                        ✕
                      </span>
                    </li>
                  </ul>
                  <div 
                    v-else
                    class="text-center text-gray-500 dark:text-gray-400 py-4 text-sm"
                  >
                    No hay grupos seleccionados
                  </div>
                </div>
              </div>

            </div>

          </div>

          <!-- Footer -->
          <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
              @click="enviarPersonalizado"
              :disabled="!canSubmitPersonalizado"
              class="w-full px-5 py-2.5 rounded-lg bg-cetpro hover:bg-cetpro-dark text-white font-semibold 
                     disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-cetpro transition-colors"
            >
              Publicar Personalizado
            </button>

            <button
              @click="emit('close')"
              class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 
                     text-white font-medium transition-colors"
            >
              Cancelar
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>