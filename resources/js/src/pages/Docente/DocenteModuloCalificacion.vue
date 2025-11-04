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
    <AuthorizationFallback
        :permissions="['todo-acceso-capacidad-terminal-notas-docente', 'ver-capacidad-terminal-notas-docente']">
        <div class="w-full space-y-4 py-6">
            <div class="flex justify-between">
                <h2 class="text-black font-bold text-2xl dark:text-white">Estudiantes</h2>
            </div>

            <div class="flex justify-between">
                <BaseSelectGrupo v-model="capacidadSeleccionada" :options="capacidadTerminal?.capacidadTerminal"
                    label="nombre_capacidad" placeholder="Seleccione una capacidad terminal" />
                <CreateButton @click="verNotasUnidad" value="Ver Notas" />
            </div>


            <div class="w-full">
                <!-- Tabla con eliminación de líneas internas -->
                <Table class="border-collapse divide-y divide-transparent">
                    <THead>
                        <Tr>
                            <Th>Id</Th>
                            <Th>Nombre</Th>
                            <Th>Apellido Paterno</Th>
                            <Th>Apellido Materno</Th>
                            <Th>DNI</Th>
                            <Th v-for="i in lengthUnit" :key="i">Unidad</Th>
                        </Tr>
                    </THead>
                    <TBody>
                        <Tr v-for="(user, index) in unidadesFiltradas" :key="user.id">
                            <Td class="py-2 px-4 border-0 text-black">{{ index + 1 }}</Td>
                            <!-- resto de columnas del estudiante -->
                            <Td class="py-2 px-4 border-0 text-black" v-for="note in user.notas" :key="note.id_nota">
                                <span :class="[
                                    'px-2 py-1 rounded-full',
                                    note.nota <= 10
                                        ? 'text-red-600 dark:text-red-500 font-bold'
                                        : 'text-green-600 dark:text-green-300 font-bold'
                                ]">
                                    {{ note.nota }}
                                </span>
                            </Td>
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