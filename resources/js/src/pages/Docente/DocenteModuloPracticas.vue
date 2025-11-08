<script setup>
import { ref, onMounted } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore';
import Button from '../../components/ui/Button.vue';
import Slider from '../../components/ui/Slider.vue';
import FormInput from '../../components/ui/FormInput.vue';
import FormInputFile from "../../components/ui/FormFileInput.vue";
import useModalToast from '../../composables/useModalToast';
import useHttpRequest from '../../composables/useHttpRequest';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const { showToast } = useModalToast();
const { saving: isSaving, store: guardarNotaExperienciaFormativa } = useHttpRequest('/nota_experiencia_formativa');

const matriculaStore = useMatriculaStore();
const documentoStore = useProgramacionAdmintore();

const alumnos = ref([]);
const showModal = ref(false);
const selectedAlumno = ref(null);
const saving = ref(false);
const idCarpetaGrupo = ref([])

const idExperienciaFormativa = '2b6aa766-0cfb-4870-85ea-41cdfef6a78e'

onMounted(async () => {
    console.log(`Cargando alumnos para el módulo con ID: ${props.id}`);

    await documentoStore.loadGetProgramacionByGrupo(props.id)
    idCarpetaGrupo.value = documentoStore.programacionPorGrupo

    await matriculaStore.fetchMatriculadosPorGrupoExtendido(props.id);
    alumnos.value = matriculaStore.matriculadosPorGrupoExtendido.estudiantes;
});

const formData = ref({
    lugar: "",
    documento: null,
});

function abrirModal(alumno) {

    console.log('alimnos', alumno)
    selectedAlumno.value = alumno;
    formData.value = { lugar: "", documento: null };
    showModal.value = true;
}

function removeFile() {
    formData.value.documento = null;
}

async function onSubmit() {
    if (!formData.value.lugar || !formData.value.documento) {
        alert("Debe llenar el lugar y adjuntar el documento.");
        return;
    }

    try {
        saving.value = true;
        const form = new FormData();
        // form.append("id_experiencia", props.idExperiencia);
        form.append("id_experiencia", idExperienciaFormativa);
        // aca deberia ir el ID DE MATRICULA O ID DEL ESTUDIANTE
        form.append("id_estudiante", selectedAlumno.value.id_estudiante);
        form.append("id_grupo", props.id);
        form.append("lugar", formData.value.lugar);
        form.append("file", formData.value.documento);
        form.append("parentFolderId", idCarpetaGrupo.value.carpeta_raiz.id);

        const response = await guardarNotaExperienciaFormativa(form)

        console.log('respuesta del response', response)

        showModal.value = false;
    } catch (error) {
        console.error("Error al guardar:", error);
    } finally {
        saving.value = false;
    }
}

</script>

<template>
    <AuthorizationFallback :permissions="['ver-mis-modulos']">
        <div class="w-full space-y-4">
            <div class="flex-between">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">
                    Lista de Alumnos Asignados
                </h2>
            </div>

            <Table>
                <THead>
                    <Th>N°</Th>
                    <Th>Apellidos y Nombres</Th>
                    <Th>DNI</Th>
                    <Th>Estado</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(alumno, index) in alumnos" :key="alumno.id">
                        <Td>{{ index + 1 }}</Td>
                        <Td>{{ alumno.apellidos_nombres }}</Td>
                        <Td>{{ alumno.nro_documento }}</Td>
                        <Td>
                            <span :class="alumno.estado === 'Matriculado' ? 'text-green-600' : 'text-red-600'">
                                {{ alumno.estado }}
                            </span>
                        </Td>
                        <Td class="text-center">
                            <Button title="Calificar" color="primary" size="sm" @click="abrirModal(alumno)" />
                        </Td>

                    </Tr>
                </TBody>
            </Table>

            <Slider :show="showModal" title="Registrar Nota de Prácticas" @hide="showModal = false">
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg text-gray-700 dark:text-gray-200">
                        Alumno: {{ selectedAlumno?.apellidos_nombres }}
                    </h3>

                    <!-- 🏢 Campo Lugar -->
                    <FormInput v-model="formData.lugar" label="Lugar de prácticas *"
                        placeholder="Ej. Clínica Odontovida" />

                    <!-- 📎 Archivo -->
                    <FormInputFile v-model="formData.documento" label="Documento (Informe o Evidencia) *" />

                    <!-- 🧾 Vista previa archivo -->
                    <div v-if="formData.documento" class="mt-3">
                        <div
                            class="flex items-center justify-between text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <div class="flex items-center gap-2 truncate">
                                <span class="truncate">{{ formData.documento.name }}</span>
                            </div>
                            <button @click="removeFile" type="button" class="text-red-500 hover:text-red-700">
                                Quitar
                            </button>
                        </div>
                    </div>

                    <!-- 🚀 Botón Guardar -->
                    <div class="flex justify-end">
                        <Button title="Guardar Nota" :loading="saving" :disabled="saving" @click="onSubmit" />
                    </div>
                </div>
            </Slider>


        </div>
    </AuthorizationFallback>
</template>