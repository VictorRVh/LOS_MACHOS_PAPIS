<script setup>
/* ================== IMPORTACIONES ================== */
import { defineProps, ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";

// Components
import Table from "../../table/Table.vue";
import THead from "../../table/THead.vue";
import TBody from "../../table/TBody.vue";
import Tr from "../../table/Tr.vue";
import Th from "../../table/Th.vue";
import Td from "../../table/Td.vue";
import CustomInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";

// Composables
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import useStudentsStore from "../../../store/Estudiante/UseEstudianteAddNotasStore";

/* ================== PROPS ================== */
const props = defineProps({
  idgroup: { type: String, required: true },
  idCapacidadNote: { type: String, required: true },
});

/* ================== ESTADOS ================== */
const router = useRouter();
const listNotes = ref([]);
const isSubmitting = ref(false);

const { store: createUnit } = useHttpRequest("nota_capacidad_terminal");
const { showToast } = useModalToast();
const userStore = useStudentsStore();

/* ================== FUNCIONES ================== */

/** Cargar estudiantes y preparar notas */
const loadGroupData = async () => {
  try {
    await userStore.loadAlumnosNotas(props.idgroup);

    listNotes.value = userStore.alumnosNotas.map((element) => ({
      fullName: `${element?.apellido_paterno} ${element?.apellido_materno}, ${element?.nombre}`,
      nota: null,
      id_estudiante: element?.id,
    }));
  } catch (error) {
    console.error("Error cargando estudiantes:", error);
    showToast("Error al cargar el grupo de estudiantes.", "error");
  }
};

/** Validar las notas antes de enviar */
/** Validar las notas antes de enviar */
const validateNotes = () => {
  for (const note of listNotes.value) {
    // Permitir '00', '01', etc. al validar
    const notaStr = String(note.nota).padStart(2, '0');
    const parsedNote = parseFloat(notaStr);

    if (notaStr === "" || isNaN(parsedNote) || parsedNote < 0 || parsedNote > 20) {
      showToast(
        `La nota para ${note.fullName} debe ser un número entre 00 y 20.`,
        "error"
      );
      return false;
    }
  }
  return true;
};

/** Validar mientras se escribe en el input de nota */
const validateInput = (event, idx) => {
  let value = event.target.value.replace(/\D/g, ""); // elimina caracteres no numéricos
  if (value.length > 2) value = value.slice(0, 2); // máximo 2 dígitos
  listNotes.value[idx].nota = value;
};


/** Enviar las notas al servidor */
const submitNotes = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    if (!validateNotes()) return (isSubmitting.value = false);

    const payload = {
      id_capacidad_terminal: props.idCapacidadNote,
      id_grupo: props.idgroup,
      notas: listNotes.value.map((n) => ({
        id_estudiante: n.id_estudiante,
        nota: parseFloat(String(n.nota).padStart(2, '0')),
      })),
    };
    
    console.log("los datos de docentes: ",payload)
    const response = await createUnit(payload);

    if (response?.status === 201) {
      showToast("Notas guardadas exitosamente", "success");
      router.push(`/notasUnit/${props.idgroup}`);
      listNotes.value = [];
    } else {
      throw new Error("Error al guardar");
    }
  } catch (error) {
    console.error("Error en el envío de notas:", error);
    showToast("Error al guardar notas. Inténtalo de nuevo.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

/* ================== CICLOS DE VIDA ================== */
onMounted(loadGroupData);
watch(() => props.idgroup, loadGroupData);
</script>


<template>
  <AuthorizationFallback
    :permissions="['todo-acceso-capacidad-terminal-notas-docente', 'editar-capacidad-terminal-notas-docente']">
    <div class="w-full space-y-4 py-6">
      <!-- Cabecera -->
      <header class="flex justify-between">
        <h2 class="text-black font-bold text-2xl">Asignar Notas - Capacidad Terminal</h2>
      </header>

      <!-- Tabla de estudiantes -->
      <section class="w-full">
        <Table class="border-collapse">
          <THead>

            <Th>#</Th>
            <Th>Nombre Completo</Th>
            <Th>Nota</Th>

          </THead>

          <TBody>
            <Tr v-for="(user, index) in listNotes" :key="user.id_estudiante">
              <Td class="py-2 px-4">{{ index + 1 }}</Td>
              <Td class="py-2 px-4">{{ user.fullName }}</Td>
              <Td class="w-[110px] px-4">
                <CustomInput v-model="user.nota" type="text" maxlength="2" :input-class="[
                  'text-center',
                  user.nota === null || user.nota === ''
                    ? 'text-gray-500 dark:text-gray-400'
                    : parseFloat(user.nota) <= 10
                      ? 'text-red-600 dark:text-red-400'
                      : 'text-black dark:text-white'
                ]" @input="(e) => validateInput(e, index)" />

              </Td>
            </Tr>
          </TBody>
        </Table>

        <!-- Botón para guardar -->
        <div class="flex justify-end w-[180px] mt-4">
          <Button title="Guardar" :loading-title="isSubmitting ? 'Guardando...' : 'Creando...'" :loading="isSubmitting"
            :disabled="isSubmitting" class="!w-full" @click="submitNotes" />
        </div>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
/* Tailwind se encarga del diseño */
</style>
