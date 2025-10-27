<script setup>
import { ref } from 'vue';
import Slider from '../../ui/Slider.vue';
import FormInput from '../../ui/FormInput.vue';
import SaveButton from '../../ui/SaveButton.vue';

const props = defineProps({
    show: Boolean,
    fechasSeleccionadas: {
        type: Array,
        default: () => []
    },
});
const emit = defineEmits(['hide', 'save']);

const form = ref({
    nombre_sesion: '',
    descripcion: ''
});

const handleSubmit = () => {
    emit('save', form.value);
};
</script>

<template>
    <Slider :show="show" @hide="emit('hide')" title="Programar Sesiones">
        <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fechas a programar</label>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span v-for="fecha in fechasSeleccionadas" :key="fecha" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full dark:bg-blue-900 dark:text-blue-300">
                        {{ new Date(fecha + 'T00:00:00').toLocaleDateString('es-PE', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) }}
                    </span>
                </div>
            </div>

            <FormInput v-model="form.nombre_sesion" label="Tema de la Sesión" required />
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
                <textarea v-model="form.descripcion" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </textarea>
            </div>

            <div class="flex justify-end pt-4">
                <SaveButton title="Guardar Sesiones"/>
            </div>
        </form>
    </Slider>
</template>