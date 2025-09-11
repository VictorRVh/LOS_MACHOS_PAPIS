<script setup>
import { ref, watch } from 'vue';
import { CodeBracketIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import Slider from '../../ui/Slider.vue';
import SaveButton from '../../ui/SaveButton.vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  documentoData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['hide', 'submitted']);

const form = ref({
    titulo: 'documento de Sesiones',
    fecha_inicio: '02/04/2025',
    fecha_finalizacion: '02/04/2025',
    estado: 'Habilitado',
    documento: null,
    notas: '',
});

watch(() => props.documentoData, (newData) => {
  if (newData) {
    // Aquí podrías llenar el form si estuvieras editando
  }
});

const handleFileChange = (event) => {
    form.value.documento = event.target.files[0];
};

const handleSubmit = () => {
    console.log('Subiendo documento:', form.value);
    emit('submitted');
    emit('hide');
};
</script>

<template>
  <Slider :show="show" @hide="emit('hide')" :title="''">
    <template #title>
      <div class="flex items-center w-full p-4 border-b-4 border-cetpro dark:border-blue-500">
        <CodeBracketIcon class="h-6 w-6 mr-3 text-gray-700 dark:text-gray-200" />
        <span class="font-semibold text-lg text-gray-800 dark:text-gray-100">Subir documento</span>
      </div>
    </template>

    <form @submit.prevent="handleSubmit" class="p-6">
      <div class="grid grid-cols-4 gap-x-8 gap-y-4 pb-6 mb-6">
        <div>
            <span class="block text-sm font-bold text-cetpro dark:text-blue-400">Título de documento</span>
            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ form.titulo }}</p>
        </div>
        <div>
            <span class="block text-sm font-bold text-cetpro dark:text-blue-400">Fecha de inicio</span>
            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ form.fecha_inicio }}</p>
        </div>
        <div>
            <span class="block text-sm font-bold text-cetpro dark:text-blue-400">Fecha de finalización</span>
            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ form.fecha_finalizacion }}</p>
        </div>
        <div>
            <span class="block text-sm font-bold text-cetpro dark:text-blue-400">Estado</span>
            <p class="mt-1 text-sm font-semibold text-green-600 dark:text-green-400">{{ form.estado }}</p>
        </div>
      </div>

      <div class="space-y-6">
        <div>
            <label class="block text-sm font-bold text-cetpro dark:text-blue-400 mb-2">Documento</label>
            <label for="file-upload" class="relative flex items-center w-full px-3 py-2 border border-cetpro dark:border-gray-600 rounded-md cursor-pointer hover:border-cetpro-light focus-within:ring-2 focus-within:ring-cetpro-light focus-within:border-cetpro-light">
                <ArrowUpTrayIcon class="h-5 w-5 text-gray-400 mr-2" />
                <span class="text-sm text-gray-500">{{ form.documento ? form.documento.name : 'cargar documento desde su pc' }}</span>
                <input id="file-upload" type="file" class="sr-only" @change="handleFileChange">
            </label>
        </div>
        <div>
            <label class="block text-sm font-bold text-cetpro dark:text-blue-400 mb-2">Notas <span class="text-gray-400 font-normal">(opcional*)</span></label>
            <textarea v-model="form.notas" rows="4" class="w-full border border-cetpro dark:bg-slate-700 dark:border-gray-600 rounded-md shadow-sm focus:ring-2 focus:ring-cetpro-light focus:border-cetpro-light"></textarea>
        </div>
      </div>
    </form>
    
    <template #footer>
      <SaveButton @click="handleSubmit">Subir documento</SaveButton>
    </template>
  </Slider>
</template>