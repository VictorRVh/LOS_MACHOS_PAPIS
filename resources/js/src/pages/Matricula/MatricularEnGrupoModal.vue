<script setup>
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import FormLabelError from '../../ui/FormLabelError.vue';
import Button from '../../ui/Button.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import useModalToast from '../../../composables/useModalToast';

const props = defineProps({
    show: { type: Boolean, default: false },
    grupo: { type: Object, required: true },
});
const emit = defineEmits(['close', 'matricula-creada']);

const { showToast } = useModalToast();

// --- SIMULACIÓN DE DATOS ---
const fakeStudentDB = [
    { id: 1, nombre: 'Ana Lucía', apellidos: 'García Torres', nro_documento: '76543210' },
    { id: 2, nombre: 'Luis Miguel', apellidos: 'Ramírez Soto', nro_documento: '71234567' },
    { id: 3, nombre: 'Carla Sofía', apellidos: 'Mendoza Luna', nro_documento: '78901234' },
    { id: 4, nombre: 'Jorge Andrés', apellidos: 'Castillo Vega', nro_documento: '75554433' },
    { id: 5, nombre: 'David Alonso', apellidos: 'Flores Díaz', nro_documento: '72221100' },
];
// --- FIN SIMULACIÓN ---

const estudianteSeleccionado = ref(null);
const estudiantesEncontrados = ref([]);
const buscandoEstudiantes = ref(false);

watch(() => props.show, (newVal) => {
    if (!newVal) {
        estudianteSeleccionado.value = null;
        estudiantesEncontrados.value = [];
    }
});

const buscarEstudiantes = debounce((search, loading) => {
    if (search.length < 2) {
        estudiantesEncontrados.value = [];
        return;
    }
    loading(true);
    setTimeout(() => {
        estudiantesEncontrados.value = fakeStudentDB.filter(e =>
            (e.nombre + ' ' + e.apellidos).toLowerCase().includes(search.toLowerCase()) ||
            e.nro_documento.includes(search)
        );
        loading(false);
    }, 500);
}, 350);

const getOptionLabel = (option) => {
    return `${option.nombre || ''} ${option.apellidos || ''} - (${option.nro_documento || ''})`;
}

function onSubmit() {
    if (!estudianteSeleccionado.value) {
        showToast('Debes seleccionar un estudiante.', 'error');
        return;
    }

    const nuevaMatricula = {
        id: Math.floor(Math.random() * 1000) + 500,
        estudiante: estudianteSeleccionado.value,
        created_at: new Date().toISOString(),
    };

    showToast(`Simulación: "${estudianteSeleccionado.value.nombre}" matriculado en el grupo.`);
    emit('matricula-creada', nuevaMatricula);
    emit('close');
}
</script>

<template>
    <div v-if="show" @click.self="emit('close')" class="fixed inset-0 bg-black bg-opacity-60 z-40 flex items-center justify-center font-inter">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-5 border-b dark:border-gray-700">
                <h2 class="text-xl font-bold text-cetpro dark:text-cetpro-light">
                    Matricular Estudiante en: {{ grupo.nombre_grupo }}
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <FormLabelError label="Buscar Estudiante (YA REGISTRADO)" required>
                    <v-select
                        v-model="estudianteSeleccionado"
                        :options="estudiantesEncontrados"
                        :get-option-label="getOptionLabel"
                        placeholder="Escriba un nombre o DNI..."
                        :loading="buscandoEstudiantes"
                        @search="buscarEstudiantes"
                    >
                        <template #no-options>Escriba al menos 2 letras para buscar...</template>
                    </v-select>
                </FormLabelError>
            </div>
            <div class="p-5 border-t dark:border-gray-700 flex justify-end gap-3">
                <Button @click="emit('close')" variant="outline" title="Cancelar"/>
                <Button @click="onSubmit" title="Confirmar Matrícula"/>
            </div>
        </div>
    </div>
</template>