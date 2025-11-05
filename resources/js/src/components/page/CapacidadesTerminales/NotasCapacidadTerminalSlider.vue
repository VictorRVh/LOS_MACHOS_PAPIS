<script setup>
// ================== IMPORTACIONES ==================
import { defineProps, ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";

// Components
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import CustomInput from "../../ui/FormInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

// Composables
import useHttpRequest from "../../composables/useHttpRequest";
import useModalToast from "../../composables/useModalToast";
import useStudentsStore from "../../store/Grupo/useGrupoStore";

// ================== PROPS ==================
const props = defineProps({
  idgroup: { type: String, default: null },
  idExperiencie: { type: String, default: null },
  idUnitNote: { type: String, default: null },
  idType: { type: String, default: null },
  idTypeUnit: { type: String, default: null },
});

// ================== CONSTANTES ==================
const TYPE_EXPERIENCE = "657870657269656e636961";

const router = useRouter();
const listNotes = ref([]);
const isSubmitting = ref(false);

const url = props.idType === TYPE_EXPERIENCE
  ? "/registrar_nota_experiencia"
  : "/registrar_notas_unidades";



const { store: createUnit, saving } = useHttpRequest(url);
const { showToast } = useModalToast();
const userStore = useStudentsStore();

// ================== FUNCIONES ==================

// Cargar estudiantes y preparar el array de notas
const loadGroupData = async () => {
  try {
    await userStore.loadGroupStudent(props.idgroup);

    listNotes.value = userStore.student.estudiantes.map((element) => ({
      fullName: `${element.estudiante?.name} ${element.estudiante?.apellido_paterno} ${element.estudiante?.apellido_materno}`,
      nota: null,
      [props.idType === TYPE_EXPERIENCE ? "id_experiencia" : "id_unidad_didactica"]:
        props.idType === TYPE_EXPERIENCE ? props.idExperiencie : props.idUnitNote,
      id_estudiante: element.estudiante?.id,
      id_grupo: props.idgroup,
    }));

  } catch (error) {
    console.error("Error cargando estudiantes:", error);
    showToast("Error al cargar el grupo de estudiantes.", "error");
  }
};

// Validar las notas antes de enviar
const validateNotes = () => {
  for (const note of listNotes.value) {
    const parsedNote = parseFloat(note.nota);

    if (note.nota === null || isNaN(parsedNote) || parsedNote < 0 || parsedNote > 20) {
      showToast(
        `La nota para ${note.fullName} debe ser un número entre 0 y 20.`,
        "error"
      );
      return false;
    }
  }
  return true;
};

// Enviar las notas al servidor
// Enviar las notas al servidor
const submitNote = async () => {
  if (isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    if (!validateNotes()) {
      isSubmitting.value = false;
      return;
    }

    const response = await createUnit({ notas: listNotes.value });

    if (response?.status === 201) {
      // Limpia el formulario para evitar doble envío
      listNotes.value = [];

      // Redirige después de guardar
      const routeName = props.idType === TYPE_EXPERIENCE
        ? `/notasExperience/${props.idgroup}`
        : `/notasUnit/${props.idgroup}`;

      router.push(routeName);
      showToast("Notas guardadas exitosamente", "success");

    } else {
      throw new Error("Error al guardar");
    }

  } catch (error) {
    console.log("Error en el envío de notas:", error);
    showToast("Error al guardar notas. Inténtalo de nuevo.", "error");
  } finally {
    // Activa el botón de nuevo si hubo error
    isSubmitting.value = false;
  }
};


// ================== CICLOS DE VIDA ==================
onMounted(loadGroupData);

// Observar cambios en el grupo para recargar los estudiantes
watch(() => props.idgroup, loadGroupData);
</script>

<template>
  <AuthorizationFallback :permissions="['groups-all', 'groups-view']">
    <div class="w-full space-y-4 py-6">
      <!-- Cabecera -->
      <header class="flex justify-between">
        <h2 class="text-black font-bold text-2xl">Estudiantes</h2>
      </header>

      <!-- Tabla de estudiantes -->
      <section class="w-full">
        <Table class="border-collapse ">
          <THead>
            <Tr>
              <Th>Id</Th>
              <Th>Nombre Completo</Th>
              <Th>Nota</Th>
            </Tr>
          </THead>

          <TBody>
            <Tr v-for="(user, index) in listNotes" :key="user.id_estudiante">
              <Td class="py-2 px-4 border-0 text-black">
                {{ index + 1 }}
              </Td>
              <Td class="py-2 px-4 border-0 text-black">
                {{ user?.fullName }}
              </Td>
              <Td class="  border-0 w-[100px]">
                <CustomInput v-model="user.nota" :input-class="[
                  ' text-center',
                  user.nota === null
                    ? 'text-gray-500 dark:text-gray-400'
                    : parseFloat(user.nota) <= 10
                      ? 'text-red-600 dark:text-red-400'
                      : 'text-black dark:text-white'
                ]" />
              </Td>

            </Tr>
          </TBody>
        </Table>

        <!-- Botón para guardar -->
        <div class="flex justify-end w-[180px] mt-4">
          <Button :title="'Guardar'" :loading-title="isSubmitting ? 'Guardando...' : 'Creando...'"
            :loading="isSubmitting" :disabled="isSubmitting" class="!w-full" @click="submitNote" />
        </div>
      </section>
    </div>
  </AuthorizationFallback>
</template>

<style scoped>
/* Todo el diseño se maneja con Tailwind */
</style>