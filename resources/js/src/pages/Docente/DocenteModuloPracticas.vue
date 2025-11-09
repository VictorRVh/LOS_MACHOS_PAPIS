<script setup>
import { ref, onMounted, useSlots } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';
import useProgramacionAdmintore from '../../store/Documento/useDocumentoStore';
import Slider from "../../components/ui/Slider.vue";
import Button from '../../components/ui/Button.vue';
import FormInput from '../../components/ui/FormInput.vue';
import FormInputFile from "../../components/ui/FormFileInput.vue";
import useModalToast from '../../composables/useModalToast';
import useHttpRequest from '../../composables/useHttpRequest';
import useExperienciaFormativaStore from '../../store/ExperienciaFormativa/useExperienciaFormativa';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const { showToast } = useModalToast();
const { saving: isSavingExp, store: guardarExperienciaFormativa } = useHttpRequest('/experiencia_formativa');
const { saving: isSaving, store: guardarNotaExperienciaFormativa } = useHttpRequest('/nota_experiencia_formativa');

const experienciaFormativaStore = useExperienciaFormativaStore();
const documentoStore = useProgramacionAdmintore();

const alumnos = ref([]);
const showModal = ref(false);
const selectedAlumno = ref(null);
const saving = ref(false);
const idCarpetaGrupo = ref([])
const idExperienciaFormativa = ref(null);

const nuevaExperiencia = ref({
    nombre_experiencia: "",
    fecha_inicio: "",
    fecha_fin: "",
    horas: "",
});

onMounted(async () => {

    try {
        await experienciaFormativaStore.loadgetExperienciaFormativaByGrupo(props.id);

        const response = experienciaFormativaStore.ExperienciaFormativaPorGrupo

        await documentoStore.loadGetProgramacionByGrupo(props.id);
        idCarpetaGrupo.value = documentoStore.programacionPorGrupo;

        if (response) {

        
            const exp = response.data.experiencia;
            const est = response.data.estudiantes;

            console.log('esrurur', est)

            nuevaExperiencia.value = {
                id: exp.id,
                nombre_experiencia: exp.nombre_experiencia,
                fecha_inicio: exp.fecha_inicio,
                fecha_fin: exp.fecha_fin,
                horas: exp.horas,
            };

            idExperienciaFormativa.value = exp.id;

            alumnos.value = est.map(a => ({
                ...a,
                lugar: a.lugar || "",
                documento: a.documento || ""
            }));
        }
    } catch (error) {
        console.log("No existe experiencia formativa para este grupo aún.");
    }
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

async function guardarExperiencia() {
    if (
        !nuevaExperiencia.value.nombre_experiencia ||
        !nuevaExperiencia.value.fecha_inicio ||
        !nuevaExperiencia.value.fecha_fin ||
        !nuevaExperiencia.value.horas
    ) {
        showToast("Todos los campos son obligatorios", "error");
        return;
    }

    try {
        isSavingExp.value = true;

        const response = await guardarExperienciaFormativa({
            ...nuevaExperiencia.value,
            id_grupo: props.id,
            parentId: idCarpetaGrupo.value.carpeta_raiz.id,
        });

        const experiencia = response?.data?.data; 

        if (experiencia) {
            nuevaExperiencia.value = { ...experiencia };

            idExperienciaFormativa.value = experiencia.id;

            showToast("Experiencia formativa registrada correctamente", "success");
        }
    } catch (error) {
        console.error("Error al guardar experiencia:", error);
        showToast("Error al registrar la experiencia", "error");
    } finally {
        isSavingExp.value = false;
    }
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
        form.append("id_experiencia", idExperienciaFormativa.value);
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

            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg text-cetpro mb-3">Registrar Nueva Experiencia Formativa</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <FormInput v-model="nuevaExperiencia.nombre_experiencia" label="Nombre de la experiencia *"
                        placeholder="Ej. Prácticas Clínicas 2025" />
                    <FormInput type="date" v-model="nuevaExperiencia.fecha_inicio" label="Fecha Inicio *" />
                    <FormInput type="date" v-model="nuevaExperiencia.fecha_fin" label="Fecha Fin *" />
                    <FormInput v-model="nuevaExperiencia.horas" label="Horas *" type="number" min="1"
                        placeholder="Ej. 120" />
                </div>

                <div class="flex justify-end mt-4">
                    <Button title="Guardar Experiencia" :loading="isSavingExp" :disabled="isSavingExp"
                        @click="guardarExperiencia" />
                </div>
            </div>

            <div class="flex-between mt-6">
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
                    <Tr v-for="(alumno, index) in alumnos" :key="alumno.id_estudiante">
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

            <!--  Modal para calificar -->
            <Slider :show="showModal" title="Registrar Nota de Prácticas" @hide="showModal = false">
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg text-gray-700 dark:text-gray-200">
                        Alumno: {{ selectedAlumno?.apellidos_nombres }}
                    </h3>

                    <FormInput v-model="formData.lugar" label="Lugar de prácticas *"
                        placeholder="Ej. Clínica Odontovida" />
                    <FormInputFile v-model="formData.documento" label="Documento (Informe o Evidencia) *" />

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

                    <div class="flex justify-end">
                        <Button title="Guardar Nota" :loading="saving" :disabled="saving" @click="onSubmit" />
                    </div>
                </div>
            </Slider>
        </div>
    </AuthorizationFallback>
</template>
