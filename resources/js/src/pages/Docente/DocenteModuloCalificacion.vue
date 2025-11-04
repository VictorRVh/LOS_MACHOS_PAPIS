<script setup>
import { defineProps, watch, ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import CreateButton from "../../components/ui/CreateButton.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useStudentsStore from "../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../store/CapacidadTerminal/UseCapacidadTerminalStore";
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

const capacidadSeleccionada = ref(null); // ✅ Nuevo: capacidad terminal seleccionada
const unidadesFiltradas = ref([]);       // ✅ Unidades filtradas según la capacidad seleccionada

// ✅ Cargar datos al montar

if (!userStore.estudiantes?.length) {
    await userStore.loadEstudiantes(props.id);
}
if (!capacidadTerminal?.capacidadTerminal?.length) {
    await capacidadTerminal.loadCapacidadTerminal(props.id);
}

        // estudiantes,
        // loadEstudiantes,
        // estudianteLoading,
        // estudianteFirstTimeLoading,

// ✅ Recalcular unidades cuando cambia la capacidad seleccionada
watch(capacidadSeleccionada, (newCapacidad) => {
    if (newCapacidad) {
        unidadesFiltradas.value = userStore.estudiantes
            .map((u) => ({
                ...u,
                notas: u.estudiante?.notas?.filter(
                    (n) => n.id_capacidad_terminal === newCapacidad.id
                ),
            }))
            .filter((u) => u.notas.length > 0);
    } else {
        unidadesFiltradas.value = [];
    }
});

// ✅ Ir a la vista de notas de una unidad
const verNotasUnidad = () => {
    if (capacidadSeleccionada.value) {
        router.push({
            name: "capacidadTerminalNotas",
            params: { idgroup: props.id, id: capacidadSeleccionada.value.id },
        });
    } else {
        console.error("Selecciona una capacidad terminal primero.");
    }
};
</script>


<template>
    <AuthorizationFallback :permissions="['todo-acceso-capacidad-terminal-notas-docente', 'ver-capacidad-terminal-notas-docente']">
        <div class="w-full space-y-4 py-6">
            <div class="flex justify-between">
                <h2 class="text-black font-bold text-2xl dark:text-white">Estudiantes Matriculados</h2>
            </div>

            <div class="w-full">
                <Table class="border-collapse divide-y divide-transparent">
                    <THead>
                        <Tr>
                            <Th>#</Th>
                            <Th>Estudiante</Th>
                            <Th>DNI</Th>
                            <Th>Sexo</Th>
                            <Th>Fecha de Nacimiento</Th>
                            <Th>Turno</Th>
                            <Th>Reserva</Th>
                        </Tr>
                    </THead>
                    <TBody>
                        <Tr v-for="(mat, index) in estudiantes" :key="mat.id_matricula">
                            <Td>{{ index + 1 }}</Td>
                            <Td>{{ mat.estudiante }}</Td>
                            <Td>{{ mat.nro_documento }}</Td>
                            <Td>{{ mat.sexo }}</Td>
                            <Td>{{ mat.fecha_nacimiento }}</Td>
                            <Td>{{ mat.turno }}</Td>
                            <Td>{{ mat.reserva ? 'Sí' : 'No' }}</Td>
                        </Tr>
                    </TBody>
                </Table>
            </div>
        </div>
    </AuthorizationFallback>
</template>


<style scoped>
/* No se requiere CSS adicional, todo está gestionado con Tailwind */
</style>