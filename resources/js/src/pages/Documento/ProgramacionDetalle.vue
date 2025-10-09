<script setup>
import { ref, onMounted, computed } from 'vue';
import { ClockIcon, CheckCircleIcon, ExclamationTriangleIcon, EllipsisVerticalIcon, CalendarDaysIcon, DocumentArrowUpIcon, ChatBubbleBottomCenterTextIcon } from '@heroicons/vue/24/outline';
import useModalToast from '../../composables/useModalToast';
import useProgramacionSubidosStore from "../../store/Documento/useDocumentoSubidoStore";

const props = defineProps({
    id: { type: String, required: true }
});

const programacionSubidosList = useProgramacionSubidosStore();

const { showToast, showConfirmModal, showPromptModal } = useModalToast();
const loading = ref(true);
const programacion = ref(null);
const allGrupos = ref([]);
const openMenuId = ref(null);

const currentPage = ref(1);
const itemsPerPage = 6;

if (!programacionSubidosList?.programacionSubidos?.length) await programacionSubidosList.loadgetProgramacionSubidos();

console.log("datos porgrmacin: ",programacionSubidosList?.programacionSubidos)

const paginatedGrupos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return allGrupos.value.slice(start, end);
});
const totalPages = computed(() => Math.ceil(allGrupos.value.length / itemsPerPage));

const changePage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
};

const fetchData = async () => {
    loading.value = true;
    await new Promise(resolve => setTimeout(resolve, 800));
    programacion.value = { tipo_entrega: 'Entrega de Sílabo Mensual - Octubre' };
    allGrupos.value = Array.from({ length: 20 }, (_, i) => ({
        id: `grupo-${i + 1}`,
        especialidad: ['MECÁNICA', 'PELUQUERÍA', 'COMPUTACIÓN', 'CARPINTERÍA'][i % 4],
        modulo: `Módulo ${String.fromCharCode(65 + i % 4)}`,
        seccion: String.fromCharCode(65 + i),
        turno: ['Mañana', 'Tarde', 'Noche'][i % 3],
        docente: { name: `Docente ${i + 1}`, apellido_paterno: 'Apellido' },
        activoParaEntrega: i % 4 !== 0,
        entrega: i % 3 === 0 ? {
            id: `entrega-${i + 1}`,
            fecha_entrega: new Date(Date.now() - (i * 1000 * 3600 * 24)).toISOString(),
            archivo_url: '#',
            estado: i % 6 === 0 ? 'Entregado con retraso' : 'Entregado a tiempo',
        } : null
    }));
    loading.value = false;
};

onMounted(fetchData);

const formatFechaHora = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-PE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const toggleMenu = (grupoId) => {
    openMenuId.value = openMenuId.value === grupoId ? null : grupoId;
};

const toggleGrupoActivo = (grupo) => {
    grupo.activoParaEntrega = !grupo.activoParaEntrega;
    const action = grupo.activoParaEntrega ? 'habilitado' : 'deshabilitado';
    showToast(`Grupo ${grupo.seccion} ${action} para la entrega.`, 'info');
};

const habilitarEntrega = (grupo) => {
    showPromptModal({
        title: `Habilitar Plazo para Grupo ${grupo.seccion}`,
        text: 'Seleccione los días de prórroga (1-5):',
        inputType: 'select',
        inputOptions: { '1': '1 día', '2': '2 días', '3': '3 días', '4': '4 días', '5': '5 días' },
        confirmButtonText: 'Habilitar',
    }, (days) => {
        if (days) {
            showToast(`Plazo extendido por ${days} día(s) para el grupo ${grupo.seccion}.`, 'success');
        }
    });
};

const handleAction = (action, grupo) => {
    openMenuId.value = null;
    if (action === 'Habilitar Entrega') {
        habilitarEntrega(grupo);
    } else {
        showToast(`Acción '${action}' para el grupo '${grupo.seccion}'. Lógica pendiente.`, 'info');
    }
};
</script>

<template>
    <div class="p-4 md:p-6 space-y-6">
        <div v-if="loading" class="text-center py-20">Cargando...</div>
        
        <div v-else-if="programacion">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ programacion.tipo_entrega }}</h1>
            </header>

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-4">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300">Estado de Entrega por Grupo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="grupo in paginatedGrupos" :key="grupo.id" 
                     class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col transition-shadow"
                     :class="{'shadow-green-500/50 shadow-lg': grupo.activoParaEntrega, 'shadow-red-500/50 shadow-lg': !grupo.activoParaEntrega}">
                    <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-cetpro dark:text-cetpro-light uppercase truncate">{{ grupo.especialidad }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ grupo.modulo }} | Sección {{ grupo.seccion }}</p>
                        </div>
                        <div class="relative">
                            <button @click.stop="toggleMenu(grupo.id)" class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                <EllipsisVerticalIcon class="h-5 w-5"/>
                            </button>
                            <transition name="fade">
                                <div v-if="openMenuId === grupo.id" class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-900 rounded-md shadow-lg z-20 border border-gray-200 dark:border-gray-700">
                                  <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                    <li><a href="#" @click.prevent="" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"><DocumentArrowUpIcon class="h-5 w-5"/>Subir Formato/Guía</a></li>
                                    <li><a href="#" @click.prevent="" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"><ChatBubbleBottomCenterTextIcon class="h-5 w-5"/>Agregar Observación</a></li>
                                    <li><hr class="my-1 dark:border-gray-700"></li>
                                    <li><a href="#" @click.prevent="handleAction('Habilitar Entrega', grupo)" class="flex items-center gap-3 px-4 py-2 text-yellow-600 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-800"><CalendarDaysIcon class="h-5 w-5"/>Habilitar Plazo Extra</a></li>
                                  </ul>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="p-4 flex-grow">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ grupo.docente ? `${grupo.docente.name} ${grupo.docente.apellido_paterno}` : 'Docente No Asignado' }}
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t dark:border-gray-700 flex items-center justify-between">
                        <div v-if="grupo.entrega" class="flex items-center gap-2" :class="grupo.entrega.estado === 'Entregado con retraso' ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400'">
                           <CheckCircleIcon class="h-5 w-5" />
                           <div class="text-sm font-semibold">
                               <p>{{ grupo.entrega.estado === 'Entregado con retraso' ? 'Entregado Tarde' : 'Entregado' }}</p>
                               <p class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ formatFechaHora(grupo.entrega.fecha_entrega) }}</p>
                           </div>
                        </div>
                        <div v-else class="flex items-center gap-2 text-red-600 dark:text-red-400">
                            <ClockIcon class="h-5 w-5"/>
                            <span class="text-sm font-semibold">Pendiente</span>
                        </div>
                        
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input type="checkbox" :checked="grupo.activoParaEntrega" @change="toggleGrupoActivo(grupo)" class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-cetpro"></div>
                        </label>
                    </div>
                </div>
            </div>
            
            <div v-if="totalPages > 1" class="flex items-center justify-center pt-6">
                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50">Anterior</button>
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-300">Página {{ currentPage }} de {{ totalPages }}</span>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50">Siguiente</button>
                </nav>
            </div>
        </div>
        
        <div v-else class="text-center py-20">
            <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">No se encontró la programación</h3>
        </div>
        <div v-if="openMenuId" @click.stop="openMenuId = null" class="fixed inset-0 z-10"></div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s ease-out, transform 0.15s ease-out;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>