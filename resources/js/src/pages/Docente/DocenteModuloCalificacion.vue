<script setup>
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";

import { defineProps } from "vue";

import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";

import BaseButton from "../../components/ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import NotasEstudianteSlider from "../../components/page/CapacidadesTerminales/NotasCapacidadTerminalSlider.vue";
import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

import useStudentsStore from "../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";

import useSlider from "../../composables/useSlider";
import useModalToast from "../../composables/useModalToast";
import useTableData from "../../composables/tabla/useTableData";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const userStore = useStudentsStore();
const capacidadStore = useCapacidadTerminalStore();

const { showConfirmModal, showToast } = useModalToast();


const { slider, sliderData, showSlider, hideSlider } = useSlider("capacidad-terminal-notas");


const capacidadSeleccionada = ref(null);

if (!userStore.estudiantes?.length) {
    await userStore.loadEstudiantes(props.id);
}
if (!capacidadStore?.capacidadTerminal?.capacidades?.length) {
    await capacidadStore.loadCapacidadTerminal(props.id);
}

/* storeToRefs para reactividad segura */
const { estudiantes } = storeToRefs(userStore);
const { capacidadTerminal } = storeToRefs(capacidadStore);


const lengthUnit = computed(() => capacidadTerminal.value?.cantidad_capacidades ?? (capacidadTerminal.value?.capacidades?.length ?? 0));


const opcionesCapacidades = computed(() => capacidadTerminal.value?.capacidades ?? []);

/* Normalizar capacidades de cada estudiante: asegurar longitud lengthUnit con objetos { nota_capacidad: null } */
const estudiantesNormalizados = computed(() => {
    const n = Number(lengthUnit.value) || 0;
    return (estudiantes.value ?? []).map((est) => {
        
        const capacidades = Array.from({ length: n }, (_, i) => {
           
            const cap = (est.capacidades && est.capacidades[i]) ? est.capacidades[i] : { nota_capacidad: null, id_capacidad: `empty-${i}` };
          
            return {
                ...cap,
                nota_capacidad: cap?.nota_capacidad ?? null,
            };
        });
        return {
            ...est,
            capacidades,
        };
    });
});


const {
    query,
    orderBy,
    orderDirection,
    pagina,
    itemsPorPagina,
    paginados: estudiantesPaginados,
    totalPaginas,
    ordenados: estudiantesOrdenados,
    filtrar: filtrarEstudiantes
} = useTableData(estudiantesNormalizados, {
    defaultOrderBy: "apellidos_nombres", // adapta según tu campo
    searchFields: ["apellidos_nombres", "dni", "apellidos", "nombres"], // campos de búsqueda comunes
});

/* ---------- Acciones: ver/abrir slider ---------- */
const verNotasUnidad = () => {
    if (!capacidadSeleccionada.value) {
        showToast("Selecciona una capacidad terminal primero.", "warning");
        return;
    }

    // Pasar datos necesarios al slider: capacidad seleccionada + id del grupo (props.id)
    showSlider(true, {
        capacidad: capacidadSeleccionada.value,
        idGroup: props.id,
        idType: "capacidad",
    });

};

const onHideSlider = () => {
    hideSlider();
    capacidadSeleccionada.value = null;
    userStore.loadEstudiantes(props.id);
};


</script>

<template>
    <AuthorizationFallback :permissions="[
        'todo-acceso-capacidad-terminal-notas-docente',
        'ver-capacidad-terminal-notas-docente'
    ]">
        <div v-if="!slider" class="w-full space-y-4">
            <!-- Cabecera: select de capacidades y botón -->
            <div class="m-2">
                <div class="flex justify-between items-center gap-4 w-full">
                    <BaseSelectGrupo v-model="capacidadSeleccionada" :options="opcionesCapacidades"
                        label="nombre_capacidad" placeholder="Seleccione una capacidad terminal" class="w-2/5" />
                    <BaseButton :title="'Asignar Nota'" @click="verNotasUnidad" class="px-6 py-2" />
                </div>

                <div class="flex-between flex-row-reverse my-5">
                    <div class="font-inter text-md w-full">Notas Capacidades Terminales</div>
                </div>
            </div>

            <!-- Tabla: estudiantes con columnas por unidad -->
            <div class="w-full">
                <Table :paginacion="true" :current-page="pagina" :total-pages="totalPaginas"
                    @changePage="pagina = $event">
                    <THead>
                        <Th>N°</Th>
                        <Th>Nombre</Th>
                        <Th v-for="i in lengthUnit" :key="`unidad-head-${i}`" class="text-center">Unidad {{ i }}</Th>
                    </THead>

                    <TBody>
                        <Tr v-for="(user, index) in estudiantesPaginados" :key="user.id_estudiante ?? index">
                            <Td class="py-2 px-4 border-0 text-black">{{ (pagina - 1) * itemsPorPagina + index + 1 }}
                            </Td>
                            <Td class="py-2 px-4 border-0 text-black">{{ user?.apellidos_nombres }}</Td>

                            <Td v-for="(cap, i) in user.capacidades"
                                :key="cap.id_capacidad ?? `cap-${i}-${user.id_estudiante}`"
                                class="py-2 px-4 border-0 text-center text-black">
                                <span v-if="cap?.nota_capacidad !== null" :class="[
                                    'px-2 py-1 rounded-full',
                                    cap.nota_capacidad <= 10
                                        ? 'text-red-600 dark:text-red-500 font-bold'
                                        : 'text-green-600 dark:text-green-300 font-bold'
                                ]">
                                    {{ cap.nota_capacidad }}
                                </span>

                                <span v-else class="nota-vacia">--</span>
                            </Td>
                        </Tr>
                    </TBody>
                </Table>
            </div>
        </div>
        <!-- Slider para asignar notas (composable controla 'slider' y 'sliderData') -->
        <NotasEstudianteSlider v-if="slider" :show="slider" :idgroup="props.id"
            :id-capacidad-note="capacidadSeleccionada" :idType="'capacidad'" @hide="onHideSlider" />

    </AuthorizationFallback>
</template>

<style scoped>
.nota-vacia {
    @apply text-gray-400 italic;
}
</style>