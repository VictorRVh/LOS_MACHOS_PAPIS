<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../utils/axios';
import { ArrowDownTrayIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    id: { type: String, required: true }
});

const programacion = ref(null);
const grupos = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await axios.get(`/entregas-admin/${props.id}`);
        programacion.value = data.programacion;
        grupos.value = data.grupos;
    } catch (error) {
        console.error("Error al cargar los detalles de la programación:", error);
    } finally {
        loading.value = false;
    }
});

const formatFechaHora = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-PE', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};
</script>

<template>
    <div class="p-4 md:p-6">
        <div v-if="loading" class="text-center py-10">Cargando...</div>
        <div v-else-if="programacion">
            <header class="mb-6 border-b pb-4">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ programacion.tipo_entrega }}</h1>
                <p class="text-gray-500 mt-1">{{ programacion.descripcion }}</p>
                <div v-if="programacion.documentos && programacion.documentos.length > 0" class="mt-4">
                    <h3 class="text-sm font-semibold text-gray-600 mb-2">Documentos Guía Adjuntos:</h3>
                    <div class="flex flex-wrap gap-3">
                        <a v-for="doc in programacion.documentos" :key="doc.id" :href="doc.url" target="_blank"
                           class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium px-3 py-1.5 rounded-full transition-colors">
                            <ArrowDownTrayIcon class="h-4 w-4" />
                            <span>{{ doc.nombre_original }}</span>
                        </a>
                    </div>
                </div>
            </header>

            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Estado de Entrega por Grupo</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="grupo in grupos" :key="grupo.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-md font-semibold text-cetpro dark:text-cetpro-light truncate">{{ grupo.modulo.descripcion }} - {{ grupo.seccion }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Docente: {{ grupo.docente ? `${grupo.docente.name} ${grupo.docente.apellido_paterno}` : 'No Asignado' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4 ml-4">
                                <!-- Estado de la entrega -->
                                <div v-if="grupo.entrega" class="text-right">
                                    <div class="flex items-center gap-2 text-green-600">
                                        <CheckCircleIcon class="h-5 w-5" />
                                        <span class="font-semibold">Entregado</span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ formatFechaHora(grupo.entrega.fecha_entrega) }}</p>
                                </div>
                                <div v-else class="text-right">
                                    <div class="flex items-center gap-2 text-yellow-600">
                                        <ClockIcon class="h-5 w-5" />
                                        <span class="font-semibold">Pendiente</span>
                                    </div>
                                </div>
                                <!-- Botón para ver archivo -->
                                <a v-if="grupo.entrega && grupo.entrega.archivo" :href="grupo.entrega.archivo_url" target="_blank"
                                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-md text-sm transition-colors">
                                    Ver Archivo
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div v-else class="text-center py-10">No se encontró la programación.</div>
    </div>
</template>
