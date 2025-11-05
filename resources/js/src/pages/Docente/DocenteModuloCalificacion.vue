<script setup>
import { defineProps, watch, ref } from "vue";
import { useRouter } from "vue-router";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import BaseButton from "../../components/ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useStudentsStore from "../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../store/Estudiante/UseEstudianteCapacidadGrupoStore";
import NotasEstudianteSlider from "../../components/page/CapacidadesTerminales/NotasCapacidadTerminalSlider.vue";

import BaseSelectGrupo from "../../components/ui/BaseSelectGrupo.vue";

const router = useRouter();

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const userStore = useStudentsStore();
const capacidadTerminal = useCapacidadTerminalStore();

const capacidadSeleccionada = ref(null);
const unidadesFiltradas = ref([]);
const lengthUnit = ref(0);
const slider = ref(false); // Estado para mostrar / ocultar slider
const sliderData = ref(null); // Datos que se pasan al slider


// ✅ Cargar datos al montar
if (!userStore.estudiantes?.length) {
    await userStore.loadEstudiantes(props.id);
}
if (!capacidadTerminal?.capacidadTerminal?.capacidades?.length) {
    await capacidadTerminal.loadCapacidadTerminal(props.id);
}

// Total de unidades = cantidad de capacidades del grupo
lengthUnit.value = capacidadTerminal?.capacidadTerminal?.cantidad_capacidades ?? 0;

// ✅ Rellenar capacidades vacías con nota_capacidad null
if (userStore.estudiantes?.length) {
    userStore.estudiantes = userStore.estudiantes.map((est) => {
        const capacidadesCompletas = Array.from({ length: lengthUnit.value }, (_, i) => {
            return est.capacidades[i] || { nota_capacidad: null };
        });
        return {
            ...est,
            capacidades: capacidadesCompletas,
        };
    });
}

// ✅ Recalcular unidades cuando cambia la capacidad seleccionada
watch(capacidadSeleccionada, () => {
    unidadesFiltradas.value = userStore.estudiantes ?? [];
});


// Mostrar el slider con la capacidad seleccionada
const verNotasUnidad = () => {
    if (capacidadSeleccionada.value) {
        sliderData.value = capacidadSeleccionada.value; // Asignar datos
        slider.value = true; // Mostrar slider
    } else {
        console.error("Selecciona una capacidad terminal primero.");
    }
};

const hideSlider = () => {
    slider.value = false;
    sliderData.value = null;
};



</script>

<template>
    <AuthorizationFallback :permissions="[
        'todo-acceso-capacidad-terminal-notas-docente',
        'ver-capacidad-terminal-notas-docente',
    ]">
        <div class="w-full space-y-4 ">

            <div class="m-2">
                <div class="flex justify-between items-center gap-4 w-full">

                    <BaseSelectGrupo v-model="capacidadSeleccionada"
                        :options="capacidadTerminal?.capacidadTerminal?.capacidades" label="nombre_capacidad"
                        placeholder="Seleccione una capacidad terminal" class="w-2/5" />


                    <BaseButton :title="'Asignar Nota'" @click="verNotasUnidad" class="px-6 py-2" />


                </div>

                <div class="flex-between flex-row-reverse my-5">
                    <div class="font-inter text-md w-full">Notas Capacidades Terminales</div>
                </div>
            </div>

            <div class="w-full">
                <Table class="border-collapse divide-y divide-transparent">
                    <THead>

                        <Th>Id</Th>
                        <Th>Nombre</Th>
                        <Th v-for="i in lengthUnit" :key="i" class="text-center">
                            Unidad {{ i }}
                        </Th>
                    </THead>
                    <TBody>
                        <Tr v-for="(user, index) in userStore.estudiantes" :key="user.id_estudiante">
                            <Td class="py-2 px-4 border-0 text-black">{{ index + 1 }}</Td>
                            <Td class="py-2 px-4 border-0 text-black">
                                {{ user?.apellidos_nombres }}
                            </Td>

                            <Td v-for="(cap, i) in user.capacidades" :key="cap.id_capacidad"
                                class="py-2 px-4 border-0 text-center text-black">
                                <span v-if="cap?.nota_capacidad !== null" :class="[
                                    'px-2 py-1 rounded-full',
                                    cap.nota_capacidad <= 10
                                        ? 'text-red-600 dark:text-red-500 font-bold'
                                        : 'text-green-600 dark:text-green-300 font-bold',
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
        <NotasEstudianteSlider v-if="slider" :show="slider" :idgroup="props.id" :id-capacidad-note="capacidadSeleccionada"
            :idType="'capacidad'" @hide="hideSlider" />
    </AuthorizationFallback>
</template>

<style scoped>
.nota-vacia {
    @apply text-gray-400 italic;
}
</style>
