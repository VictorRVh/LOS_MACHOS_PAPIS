import { ref, computed } from 'vue';
import { defineStore } from 'pinia';

export const useSesionStore = defineStore('sesion', () => {
    const sesiones = ref([]);
    const loading = ref(false);

    const calendarEvents = computed(() => {
        return sesiones.value.map(sesion => ({
            id: sesion.id,
            title: sesion.nombre_sesion,
            start: sesion.fecha_inicio,
            end: sesion.fecha_fin,
            allDay: false,
            extendedProps: {
                descripcion: sesion.descripcion
            }
        }));
    });

    const loadSesiones = async (grupoId) => {
        loading.value = true;
        
        // --- SIMULACIÓN DE API ---
        await new Promise(resolve => setTimeout(resolve, 800));
        sesiones.value = [
            {
                id: '1d1a6f8a-4f5b-4b1c-8b1a-2c7e1b1a7d1e',
                nombre_sesion: 'Introducción a Cortes de Cabello',
                descripcion: 'Repaso de herramientas y técnicas básicas.',
                fecha_inicio: '2025-10-27T08:00:00',
                fecha_fin: '2025-10-27T11:00:00',
                id_grupo: grupoId,
                id_docente: 'doc-123'
            },
            {
                id: '2e2b7g9b-5g6c-5c2d-9c2b-3d8f2c2b8e2f',
                nombre_sesion: 'Técnicas de Colorimetría I',
                descripcion: 'Teoría del color y primeras aplicaciones.',
                fecha_inicio: '2025-10-29T08:00:00',
                fecha_fin: '2025-10-29T11:00:00',
                id_grupo: grupoId,
                id_docente: 'doc-123'
            }
        ];
        // --- FIN DE SIMULACIÓN ---
        
        loading.value = false;
    };

    return {
        sesiones,
        loading,
        calendarEvents,
        loadSesiones,
    };
});

export default useSesionStore;