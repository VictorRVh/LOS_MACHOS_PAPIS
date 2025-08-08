<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';
import Slider from '../../ui/Slider.vue';
import Button from '../../ui/Button.vue';
import FormLabelError from '../../ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import useModalToast from '../../../composables/useModalToast';

const props = defineProps({
    show: { type: Boolean, default: false },
});
const emit = defineEmits(['hide', 'matricula-simulada']);

const { showToast } = useModalToast();

// --- SIMULACIÓN DE DATOS ---
const fakeStudentDB = [
    { id: 1, nombre: 'Ana Lucía', apellidos: 'García Torres', nro_documento: '76543210' },
    { id: 2, nombre: 'Luis Miguel', apellidos: 'Ramírez Soto', nro_documento: '71234567' },
    { id: 3, nombre: 'Carla Sofía', apellidos: 'Mendoza Luna', nro_documento: '78901234' },
    { id: 4, nombre: 'Jorge Andrés', apellidos: 'Castillo Vega', nro_documento: '75554433' },
    { id: 5, nombre: 'David Alonso', apellidos: 'Flores Díaz', nro_documento: '72221100' },
];

const gruposDisponibles = ref([
    { id: 1, nombre_grupo: 'Computación e Informática - Turno Mañana' },
    { id: 2, nombre_grupo: 'Asistencia de Cocina - Turno Tarde' },
    { id: 3, nombre_grupo: 'Peluquería Básica - Turno Noche' },
]);
// --- FIN SIMULACIÓN ---

const estudianteSeleccionado = ref(null);
const grupoSeleccionado = ref(null);
const estudiantesEncontrados = ref([]);
const buscandoEstudiantes = ref(false);

const buscarEstudiantes = debounce((search, loading) => {
    if (search.length < 2) return;
    loading(true);
    buscandoEstudiantes.value = true;
    setTimeout(() => {
        estudiantesEncontrados.value = fakeStudentDB.filter(e =>
            (e.nombre + ' ' + e.apellidos).toLowerCase().includes(search.toLowerCase()) ||
            e.nro_documento.includes(search)
        );
        loading(false);
        buscandoEstudiantes.value = false;
    }, 500);
}, 350);

const getOptionLabel = (option) => {
    return `${option.nombre || ''} ${option.apellidos || ''} - (${option.nro_documento || ''})`;
}

function onSubmit() {
    if (!estudianteSeleccionado.value || !grupoSeleccionado.value) {
        showToast('Debes seleccionar un estudiante y un grupo.', 'error');
        return;
    }

    const nuevaMatricula = {
        id: Math.floor(Math.random() * 1000) + 500,
        estudiante: estudianteSeleccionado.value,
        grupo: grupoSeleccionado.value,
        created_at: new Date().toISOString(),
    };

    showToast(`Simulación: "${estudianteSeleccionado.value.nombre}" matriculado en "${grupoSeleccionado.value.nombre_grupo}"`);
    emit('matricula-simulada', nuevaMatricula);
    emit('hide');
    
    // Limpiar formulario para la próxima vez
    estudianteSeleccionado.value = null;
    grupoSeleccionado.value = null;
}
</script>

<template>
    <Slider :show="show" title="Realizar Nueva Matrícula" @hide="emit('hide')">
        <div class="p-4 space-y-4 font-inter">
            <hr class="border-t-2 border-cetpro dark:border-cetpro-light" />
            
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

            <FormLabelError label="Seleccionar Grupo" required>
                <v-select
                    v-model="grupoSeleccionado"
                    :options="gruposDisponibles"
                    label="nombre_grupo"
                    placeholder="Seleccione un grupo"
                ></v-select>
            </FormLabelError>

            <Button title="Confirmar Matrícula" @click="onSubmit" class="!mt-6 !w-full"/>
        </div>
    </Slider>
</template>