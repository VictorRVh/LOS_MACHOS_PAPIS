<script setup>

import { defineProps, ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";


import Table from "../../table/Table.vue";
import THead from "../../table/THead.vue";
import TBody from "../../table/TBody.vue";
import Tr from "../../table/Tr.vue";
import Th from "../../table/Th.vue";
import Td from "../../table/Td.vue";
import CustomInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";


import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import useStudentsStore from "../../../store/Estudiante/UseEstudianteAddNotasStore";
import useStudentsNotasStore from "../../../store/Estudiante/UseEstudianteGrupoStore";
import useCapacidadTerminalStore from "../../../store/Estudiante/UseEstudianteCapacidadGrupoStore";


const props = defineProps({
  show: { type: Boolean, default: false },
  idgroup: { type: String, required: true },
  idCapacidadNote: { type: String, required: true ,default:""},
});

const emit = defineEmits(["hide"]);
console.log("capaicdad: ",props.idCapacidadNote)

const router = useRouter();
const { store: saveNotas, saving } = useHttpRequest("nota_capacidad_terminal");
const { showToast } = useModalToast();
const userStore = useStudentsStore();
const estudianteNotasStore = useStudentsNotasStore();
const capacidadTerminal = useCapacidadTerminalStore();

const listNotes = ref([]);

const initialNotesData = () => {
  return userStore.alumnosNotas.map((est) => ({
    id_estudiante: est?.id,
    fullName: `${est?.apellido_paterno} ${est?.apellido_materno}, ${est?.nombre}`,
    nota: null,
  }));
};

const resetForm = () => {
  listNotes.value = initialNotesData();
};

const loadGroupData = async () => {
  try {
    await userStore.loadAlumnosNotas(props.idgroup);
    resetForm();
  } catch (error) {
    console.error("Error cargando estudiantes:", error);
    showToast("Error al cargar el grupo de estudiantes.", "error");
  }
};

onMounted(loadGroupData);
watch(() => props.idgroup, loadGroupData);

const validateNotes = () => {
  const regex = /^(?:\d{1,2}(?:\.\d)?|20(?:\.0)?)$/;

  for (const note of listNotes.value) {
    const value = String(note.nota).trim();

    if (!regex.test(value)) {
      showToast(
        `La nota para ${note.fullName} debe ser un número entre 0 y 20, entero o con un decimal (ej: 05, 8.6, 15.7).`,
        "error"
      );
      return false;
    }
  }
  return true;
};

const onNotaInput = (event, idx) => {
  let value = event.target.value
    .replace(/[^0-9.]/g, "")   // solo números y punto
    .replace(/(\..*)\./g, "$1"); // evitar dos puntos
  const match = value.match(/^(\d{0,2})(?:\.(\d{0,1}))?/);
  value = match ? match[0] : "";

  listNotes.value[idx].nota = value;
};

const onSubmit = async () => {
  if (saving.value) return;

  if (!validateNotes()) return;

  const payload = {
    id_capacidad_terminal: props.idCapacidadNote,
    id_grupo: props.idgroup,
    notas: listNotes.value.map((n) => ({
      id_estudiante: n.id_estudiante,
      nota: String(n.nota)
    })),
  };

  try {
    const response = await saveNotas(payload);
    if (response?.message === "Notas registradas correctamente") {
      // Recargar datos del store
      estudianteNotasStore.loadEstudiantes(props.idgroup);
      capacidadTerminal.loadCapacidadTerminal(props.idgroup);

      showToast("Notas guardadas exitosamente.", "success");
      resetForm(); // limpiar campos
      emit("hide"); // cerrar formulario
    } else {
      throw new Error("Error al guardar");
    }
  } catch (error) {
    console.error("Error al enviar las notas:", error);
    showToast("Error al guardar notas. Inténtalo de nuevo.", "error");
  }
};
</script>

<template>
  <AuthorizationFallback
    :permissions="['todo-acceso-capacidad-terminal-notas-docente', 'editar-capacidad-terminal-notas-docente']">
    <div v-if="show" class="w-full space-y-4 py-6">
      <!-- Header -->
      <header class="flex justify-between">
        <h2 class="text-black font-bold text-2xl">Asignar Notas - Capacidad Terminal</h2>
      </header>

      <!-- Tabla de Estudiantes -->
      <section class="w-full">
        <Table class="border-collapse">
          <THead>
            <Th>#</Th>
            <Th>Nombre Completo</Th>
            <Th>Nota</Th>
          </THead>

          <TBody>
            <Tr v-for="(user, index) in listNotes" :key="user.id_estudiante">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ user.fullName }}</Td>
              <Td class="w-[110px]">
                <CustomInput v-model="user.nota" type="text" maxlength="2" :input-class="[
                  'text-center',
                  user.nota === null || user.nota === ''
                    ? 'text-gray-500'
                    : parseFloat(user.nota) <= 10
                      ? 'text-red-600 font-bold'
                      : 'text-black font-bold',
                ]" @input="(e) => onNotaInput(e, index)" />
              </Td>
            </Tr>
          </TBody>
        </Table>

        <!-- Botón Guardar -->
        <div class="flex justify-end w-[180px] mt-4">
          <Button title="Guardar" :loading-title="saving ? 'Guardando...' : 'Crear...'" :loading="saving"
            :disabled="saving" class="!w-full" @click="onSubmit" />
        </div>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
/* estilos globales por Tailwind */
</style>
