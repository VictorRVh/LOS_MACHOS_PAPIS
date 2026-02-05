<script setup>
import { ref, onMounted, useSlots, computed } from 'vue';
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
import { DocumentTextIcon } from '@heroicons/vue/24/outline'
import BaseSelectCiclo from '../../components/ui/BaseSelectCiclo.vue';
import FormLabelError from '../../components/ui/FormLabelError.vue';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const { showToast } = useModalToast();
const { saving: isSavingExp, store: guardarExperienciaFormativa, update: actualizarExperienciaFormativa } = useHttpRequest('/experiencia_formativa');
const { saving: isSaving, store: guardarNotaExperienciaFormativa } = useHttpRequest('/nota_experiencia_formativa');

const experienciaFormativaStore = useExperienciaFormativaStore();

const alumnos = ref([]);
const showModal = ref(false);
const selectedAlumno = ref(null);
const saving = ref(false);
const idCarpetaGrupo = ref([])
const idExperienciaFormativa = ref(null);
const idPracticasDrive = ref(null)

const nuevaExperiencia = ref({
    fecha_inicio: "",
    fecha_fin: "",
    creditos: "",
    horas: "",
});

const modalidadPracticas = [
    { id: 1, label: 'PPP INTERNAS' },
    { id: 2, label: 'PPP EXTERNAS' },
    { id: 3, label: 'NO HIZO PRACTICAS' }
];

onMounted(async () => {
    try {
        await experienciaFormativaStore.loadGetExperienciaFormativaByGrupo(props.id);

        const response = experienciaFormativaStore.experienciaFormativaPorGrupo;

        await experienciaFormativaStore.loadDriveFolderId(props.id)

        idCarpetaGrupo.value = experienciaFormativaStore.driveFolderId

        if (response?.data) {
            const exp = response.data.experiencia;
            const est = response.data.estudiantes || [];
            idPracticasDrive.value = response.data.drive_folder_id;

            if (exp) {
                nuevaExperiencia.value = {
                    id: exp.id,
                    fecha_inicio: exp.fecha_inicio,
                    fecha_fin: exp.fecha_fin,
                    creditos: exp.creditos,
                    horas: exp.horas,
                };
                idExperienciaFormativa.value = exp.id;
            } else {
                nuevaExperiencia.value = {
                    id: null,
                    fecha_inicio: "",
                    fecha_fin: "",
                    creditos: "",
                    horas: "",
                };
                idExperienciaFormativa.value = null;
            }

            alumnos.value = est.map(a => ({
                ...a,
                lugar: a.lugar || null,
                documento: a.documento || null,
            }));
        } else {
            console.warn("No se recibió información del grupo.");
        }

    } catch (error) {
        // console.log("Error al cargar experiencia formativa:", error);
        showToast("Error al cargar experiencia formativa", "error");

    }
});

const existeExperiencia = computed(() => {
    return idExperienciaFormativa.value !== null;
});

const formData = ref({
    tipo_practicas: null,
    documento: null,
    nota: 0,
    // observacion: ""
});

function abrirModal(alumno) {
    selectedAlumno.value = alumno;
    formData.value = { lugar: "", documento: null };
    showModal.value = true;
}

function removeFile() {
    formData.value.documento = null;
}

async function guardarExperiencia() {
    if (
        !nuevaExperiencia.value.fecha_inicio ||
        !nuevaExperiencia.value.fecha_fin ||
        !nuevaExperiencia.value.creditos ||
        !nuevaExperiencia.value.horas
    ) {
        showToast("Todos los campos son obligatorios", "error");
        return;
    }

    try {
        isSavingExp.value = true;

        let response;

        // SI YA EXISTE → ACTUALIZAR
        if (existeExperiencia.value) {
            response = await actualizarExperienciaFormativa(idExperienciaFormativa.value, {
                ...nuevaExperiencia.value,
                id_grupo: props.id,
                parentId: idCarpetaGrupo.value.drive_folder_id,
            });

            showToast("Experiencia actualizada correctamente.", "success")
        }
        // SI NO EXISTE → CREAR
        else {
            response = await guardarExperienciaFormativa({
                ...nuevaExperiencia.value,
                id_grupo: props.id,
                parentId: idCarpetaGrupo.value.drive_folder_id,
            });

            showToast("Experiencia registrada correctamente.", "success")
        }

        const experiencia = response?.data;

        if (experiencia) {
            nuevaExperiencia.value = { ...experiencia };
            idExperienciaFormativa.value = experiencia.id;
            idPracticasDrive.value = experiencia.drive_folder_id;
        }
    } catch (error) {
        console.error("Error al guardar experiencia:", error);
        showToast("Error al procesar la experiencia", "error");
    } finally {
        isSavingExp.value = false;
    }
}

async function onSubmit() {
    const nota = parseFloat(formData.value.nota);

    if (isNaN(nota) || nota < 0 || nota > 20) {
        showToast("La nota debe estar entre 0 y 20.", "error");
        return; // 🚫 NO ENVÍA NADA
    }

    if (!formData.value.tipo_practicas || !formData.value.documento) {
        showToast("Debe seleccionar la modalidad y adjuntar el documento.", "warning");
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
        form.append("tipo_practicas", formData.value.tipo_practicas);
        form.append("file", formData.value.documento);
        form.append("nota", formData.value.nota);
        // form.append("observacion", observacion.value || "");
        form.append("parentFolderId", idPracticasDrive.value);

        await guardarNotaExperienciaFormativa(form);

        await experienciaFormativaStore.loadGetExperienciaFormativaByGrupo(props.id);

        const est = experienciaFormativaStore.experienciaFormativaPorGrupo.data.estudiantes;

        alumnos.value = est.map(a => ({
            ...a,
            lugar: a.lugar || "",
            documento: a.documento || "",
        }));

        showToast("Datos guardados correctamente.", "success");

        showModal.value = false;
    } catch (error) {
        console.error("Error al guardar:", error);
    } finally {
        saving.value = false;
    }
}
const onNotaInput = (event, idx) => {
    let value = event.target.value
        .replace(/[^0-9.]/g, "")   // solo números y punto
        .replace(/(\..*)\./g, "$1"); // evitar dos puntos
    const match = value.match(/^(\d{0,2})(?:\.(\d{0,1}))?/);
    value = match ? match[0] : "";

    listNotes.value[idx].nota = value;
};
</script>

<template>
    <AuthorizationFallback :permissions="['ver-mis-modulos']">
        <div class="w-full space-y-4">

            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg text-cetpro mb-3">Registrar Nueva Experiencia Formativa</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <FormInput type="date" v-model="nuevaExperiencia.fecha_inicio" label="Fecha Inicio" required />
                    <FormInput type="date" v-model="nuevaExperiencia.fecha_fin" label="Fecha Fin" required />
                    <FormInput v-model="nuevaExperiencia.creditos" label="Creditos" type="number" min="1"
                        placeholder="Ej. 10" required />
                    <FormInput v-model="nuevaExperiencia.horas" label="Horas" type="number" min="1"
                        placeholder="Ej. 120" required />
                    <div class="flex justify-end mt-6">
                        <!-- Si NO hay experiencia => botón GUARDAR -->
                        <Button v-if="!existeExperiencia" title="Guardar Experiencia" :loading="isSavingExp"
                            :disabled="isSavingExp" @click="guardarExperiencia" />

                        <!-- Si SÍ hay experiencia => botón EDITAR -->
                        <Button v-else title="Editar Experiencia" color="secondary" :loading="isSavingExp"
                            :disabled="isSavingExp" @click="guardarExperiencia" />

                    </div>
                </div>


            </div>


            <Table>
                <THead>
                    <Th>N°</Th>
                    <Th>Apellidos y Nombres</Th>
                    <Th>Modalidad</Th>
                    <Th>Nota</Th>
                    <Th>Archivo</Th>
                    <Th class="text-center">Acciones</Th>
                </THead>
                <TBody>
                    <Tr v-for="(alumno, index) in alumnos" :key="alumno.id_estudiante">

                        <!-- Alumno está retirado -->
                        <template v-if="alumno.matriculado == 2">
                            <Td>{{ index + 1 }}</Td>
                            <Td class="font-medium whitespace-nowrap">{{ alumno.apellidos_nombres }}</Td>
                            <Td :colspan="3" class="text-center">
                                <span
                                    class="px-3 py-1 rounded bg-red-100 text-red-700 font-semibold text-sm uppercase tracking-wide">
                                    RETIRADO POR INASISTENCIA
                                </span>
                            </Td>
                        </template>

                        <!-- Alumno no retirado -->
                        <template v-else>
                            <Td>{{ index + 1 }}</Td>
                            <Td>{{ alumno.apellidos_nombres }}</Td>
                            <Td>{{ alumno.tipo_practicas_texto }}</Td>
                            <Td>{{ alumno.nota }}</Td>
                            <Td class="text-center">
                                <a v-if="alumno.documento_url" :href="alumno.documento_url" target="_blank"
                                    class="inline-flex items-center justify-center text-blue-600 hover:text-blue-800"
                                    title="Abrir documento">
                                    <DocumentTextIcon class="w-6 h-6" />
                                </a>
                                <span v-else class="text-gray-400">—</span>
                            </Td>
                            <Td class="text-center">
                                <!-- Si NO hay experiencia => botón deshabilitado -->
                                <Button v-if="!existeExperiencia" title="Calificar" color="primary" size="sm"
                                    @click="showToast('Debe registrar una experiencia formativa primero.', 'warning')" />

                                <Button v-else-if="alumno.lugar !== null || alumno.documento_id !== null"
                                    title="Ya calificado" color="secondary" size="sm" disabled />

                                <!-- Si SÍ hay experiencia => botón normal -->
                                <Button v-else title="Calificar" color="primary" size="sm"
                                    @click="abrirModal(alumno)" />
                            </Td>
                        </template>

                    </Tr>

                </TBody>
            </Table>

            <Slider :show="showModal" title="Registrar Nota de Prácticas" @hide="showModal = false">
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg text-gray-700 dark:text-gray-200">
                        Alumno: {{ selectedAlumno?.apellidos_nombres }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <!-- MODALIDAD (más ancho) -->
                        <div class="md:col-span-3">
                            <FormLabelError label="Modalidad" required :error="formErrors?.tipo_practicas">
                                <BaseSelectCiclo v-model="formData.tipo_practicas" :options="modalidadPracticas"
                                    label="label" placeholder="Seleccione la modalidad" />
                            </FormLabelError>
                        </div>

                        <!-- NOTA (más pequeña) -->
                        <FormInput label="Nota" v-model="formData.nota" type="text" maxlength="4" :input-class="[
                            'text-center',
                            'bg-gray-100',
                            formData.nota === '' || formData.nota === null
                                ? 'text-gray-500'
                                : parseFloat(formData.nota) < 0 || parseFloat(formData.nota) > 20
                                    ? 'text-red-600 font-bold'
                                    : parseFloat(formData.nota) < 11
                                        ? 'text-black-500 font-bold'
                                        : 'text-black-600 font-bold',
                        ]" @input="onNotaInput" required />
                    </div>

                    <FormInputFile v-model="formData.documento" label="Documento (Informe o Evidencia) *" />
                    <!-- <label for="observacion"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observación
                        General</label>
                    <textarea id="observacion" v-model="formData.observacion" rows="2"
                        class="w-full text-sm bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea> -->

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
