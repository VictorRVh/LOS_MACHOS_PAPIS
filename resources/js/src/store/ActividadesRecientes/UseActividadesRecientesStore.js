import { ref } from 'vue';
import { defineStore } from 'pinia';
import useHttpRequest from '../../composables/useHttpRequest';

const useActividadesStore = defineStore('actividades-recientes', () => {
    const {
        index: getActividadesRecientes,
        loading: actividadesRecientesLoading,
        initialLoading: actividadesRecientesFirstTimeLoading,
    } = useHttpRequest('/actividades_recientes');

    const {
        indexWithParams: getActividadesRecientesFiltrado,
        loading: actividadesRecientesFiltradoLoading,
        initialLoading: actividadesRecientesFIltradoFirstTimeLoading,
    } = useHttpRequest('/actividades_recientes_fecha');


    const actividadesRecientes = ref([]);
    const actividadesRecientesPorFecha = ref([]);

    const timeAgo = (dateString) => {
        const now = new Date();
        const date = new Date(dateString);
        const diffMs = now - date;

        const diffMinutes = Math.floor(diffMs / 60000);
        if (diffMinutes < 1) return 'hace unos segundos';
        if (diffMinutes < 60) return `hace ${diffMinutes} minutos`;

        const diffHours = Math.floor(diffMinutes / 60);
        if (diffHours < 24) return `hace ${diffHours} horas`;

        const diffDays = Math.floor(diffHours / 24);
        return `hace ${diffDays} días`;
    };

    const loadActividadesRecientes = async () => {
        const res = await getActividadesRecientes();

        actividadesRecientes.value = res.map(act => {
            const fullName = act?.actor || "Usuario";

            return {
                role: act?.role.toLowerCase() ?? 'sin-rol',

                actor: fullName,

                accion: act.accion,

                accionColor:
                    act.accion === 'Agregado' ? 'text-blue-500' :
                        act.accion === 'Actualizado' ? 'text-green-500' :
                            act.accion === 'Eliminado' ? 'text-red-500' :
                                'text-gray-500',

                detalle: act.detalle,

                tiempo: timeAgo(act.created_at),
            };
        });
    };

    const loadActividadesPorFechas = async (fechaInicio, fechaFin) => {
        const res = await getActividadesRecientesFiltrado({
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
        });

        actividadesRecientesPorFecha.value = res.map(act => {
            const fullName = act?.actor || "Usuario";

            return {
                role: act?.role?.toLowerCase() ?? 'sin-rol',

                actor: fullName,

                accion: act.accion,

                accionColor:
                    act.accion === 'Agregado' ? 'text-blue-500' :
                        act.accion === 'Actualizado' ? 'text-green-500' :
                            act.accion === 'Eliminado' ? 'text-red-500' :
                                'text-gray-500',

                detalle: act.detalle,

                tiempo: timeAgo(act.created_at),
            };
        });
    };


    return {
        actividadesRecientes,
        loadActividadesRecientes,
        actividadesRecientesLoading,
        actividadesRecientesFirstTimeLoading,
        loadActividadesPorFechas,
        actividadesRecientesPorFecha
    };
});

export default useActividadesStore;
