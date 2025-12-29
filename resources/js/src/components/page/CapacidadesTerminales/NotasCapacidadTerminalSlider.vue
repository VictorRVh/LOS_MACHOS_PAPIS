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
  idCapacidadNote: { type: String, required: true, default: "" },
  estadoCapacidad: { type: Object, default: null },
  alumnosNotas: { type: Array, default: () => [] },
});

const emit = defineEmits(["hide"]);

const { store: saveNotas, saving } = useHttpRequest("nota_capacidad_terminal");
const { showToast } = useModalToast();
const userStore = useStudentsStore();
const estudianteNotasStore = useStudentsNotasStore();
const capacidadTerminal = useCapacidadTerminalStore();

const listNotes = ref([]);

const puedeGuardarNotas = computed(() => {
  return props.estadoCapacidad?.puede_subir_notas ?? false;
});

const mensajeAdvertencia = computed(() => {
  if (!props.estadoCapacidad) return null;

  if (!props.estadoCapacidad.puede_subir_notas) {
    return {
      tipo: 'error',
      texto: props.estadoCapacidad.mensaje
    };
  }

  const fechaLimite = new Date(props.estadoCapacidad.fecha_limite_subida);
  const ahora = new Date();
  const horasRestantes = (fechaLimite - ahora) / (1000 * 60 * 60);

  if (horasRestantes > 0 && horasRestantes <= 24) {
    return {
      tipo: 'warning',
      texto: `Quedan menos de ${Math.floor(horasRestantes)} horas para el cierre de subida de notas.`
    };
  }

  return null;
});

const initialNotesData = () => {
  return props.alumnosNotas.map((est) => ({
    id_estudiante: est.id_estudiante,
    fullName: est.apellidos_nombres,
    nota: est.capacidades?.find(c => c.id_capacidad === props.idCapacidadNote)?.nota_capacidad ?? null,
    matriculado: est.matriculado
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

    if (note.matriculado === 0) continue;

    const value = String(note.nota).trim();

    // Validar solo si tiene contenido o si es requerido
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

  if (!puedeGuardarNotas.value) {
    showToast(
      props.estadoCapacidad?.mensaje || "No puede subir notas en este momento.",
      "error"
    );
    return;
  }

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
    if (response?.success === false) {
      showToast(response.message || "Error al guardar notas.", "error");

      // Si el error es por fecha, cerrar el slider
      if (response.estado !== undefined && response.estado !== 1) {
        setTimeout(() => {
          emit("hide");
        }, 2000);
      }
      return;
    }

    if (response?.message === "Notas registradas correctamente" || response?.success === true) {
      // Recargar datos del store
      await estudianteNotasStore.loadEstudiantes(props.idgroup);
      await capacidadTerminal.loadCapacidadTerminal(props.idgroup);

      showToast("Notas guardadas exitosamente.", "success");
      resetForm();
      emit("hide");
    } else {
      throw new Error("Error al guardar");
    }
  } catch (error) {
    if (error.response?.status === 403) {
      const errorData = error.response.data;
      showToast(errorData.message || "No tiene permiso para subir notas.", "error");

      // Cerrar slider después de mostrar error
      setTimeout(() => {
        emit("hide");
      }, 2000);
    } else {
      showToast("Error al guardar notas. Inténtalo de nuevo.", "error");
    }
  }
};

const fechaLimiteFormateada = computed(() => {
  if (!props.estadoCapacidad?.fecha_limite_subida) return '';

  const fecha = new Date(props.estadoCapacidad.fecha_limite_subida);
  return fecha.toLocaleString('es-PE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});
</script>

<template>
  <AuthorizationFallback
    :permissions="['todo-acceso-capacidad-terminal-notas-docente', 'editar-capacidad-terminal-notas-docente']">
    <div v-if="show" class="w-full space-y-4 py-6">
      <!-- Header -->
      <header class="flex justify-between items-start">
        <div>
          <h2 class="text-black font-bold text-2xl">Asignar Notas - Unidad Didactica</h2>
          <!-- ✅ NUEVO: Mostrar información de estado -->
          <p v-if="estadoCapacidad" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Estado: <span :class="{
              'text-green-600 font-semibold': estadoCapacidad.status === 1,
              'text-yellow-600 font-semibold': estadoCapacidad.status === 0,
              'text-red-600 font-semibold': estadoCapacidad.status === 4,
            }">
              {{ estadoCapacidad.status_texto }}
            </span>
            <span v-if="puedeGuardarNotas" class="ml-2">
              | Fecha límite: {{ fechaLimiteFormateada }}
            </span>
          </p>
        </div>
      </header>

      <!-- ✅ NUEVO: Alert de advertencia/error -->
      <div v-if="mensajeAdvertencia" class="p-4 rounded-lg border" :class="{
        'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/20': mensajeAdvertencia.tipo === 'warning',
        'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20': mensajeAdvertencia.tipo === 'error',
      }">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd" />
          </svg>
          <p class="text-sm font-medium">{{ mensajeAdvertencia.texto }}</p>
        </div>
      </div>

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

              <!-- Si el alumno está retirado -->
              <template v-if="user.matriculado == 2">
                <Td class="text-center">
                  <span class="px-3 py-1 rounded bg-red-100 text-red-700 font-semibold text-sm uppercase tracking-wide">
                    RETIRADO POR INASISTENCIA
                  </span>
                </Td>
              </template>

              <!-- Si el alumno está activo -->
              <template v-else>
                <Td class="w-[110px]">
                  <CustomInput v-model="user.nota" type="text" maxlength="4" :disabled="!puedeGuardarNotas"
                    :input-class="[
                      'text-center',
                      !puedeGuardarNotas && 'bg-gray-100 cursor-not-allowed',
                      user.nota === null || user.nota === ''
                        ? 'text-gray-500'
                        : parseFloat(user.nota) <= 10
                          ? 'text-red-600 font-bold'
                          : 'text-black font-bold',
                    ]" @input="(e) => onNotaInput(e, index)" />
                </Td>
              </template>
            </Tr>

          </TBody>
        </Table>

        <!-- Botón Guardar -->
        <div class="flex justify-end gap-4 mt-4">
          <Button title="Cancelar" variant="secondary" @click="emit('hide')" />
          <Button title="Guardar" :loading-title="saving ? 'Guardando...' : 'Guardar'" :loading="saving"
            :disabled="saving || !puedeGuardarNotas" :class="{
              'opacity-50 cursor-not-allowed': !puedeGuardarNotas,
              '!w-[180px]': true
            }" @click="onSubmit" />
        </div>

        <!-- ✅ NUEVO: Texto informativo debajo del botón -->
        <p v-if="!puedeGuardarNotas" class="text-sm text-red-600 dark:text-red-400 text-right mt-2">
          No puede guardar notas en este momento
        </p>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
/* estilos globales por Tailwind */
</style>
