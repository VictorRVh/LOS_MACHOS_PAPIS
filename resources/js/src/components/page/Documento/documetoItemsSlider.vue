<script setup>
import { ref, computed, watch } from "vue";
import * as yup from "yup";
import { PaperClipIcon, XCircleIcon, ArrowPathIcon, TrashIcon, CloudArrowDownIcon } from "@heroicons/vue/24/outline";
import axios from "axios";

import Slider from "../../ui/Slider.vue";
import FormInputFile from "../../ui/FormFileInput.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../page/AuthorizationFallback.vue";

import useModalToast from "../../../composables/useModalToast";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";

const props = defineProps({
    grupo: { type: Object, required: true },
    show: { type: Boolean, required: true },
});

const emit = defineEmits(["hide"]);


const { runYupValidation } = useValidation();

// 📡 Composables HTTP
const { store, saving } = useHttpRequest("/drive/upload");
const { show: listarArchivos, loading: loadingFiles } = useHttpRequest("/drive/files");
const { destroy: eliminarArchivo } = useHttpRequest("/drive/file"); // <- DELETE /drive/file/{id}
const { showConfirmModal, showToast } = useModalToast();
// 📁 archivos existentes
const archivos = ref([]);

// 📄 formulario
const formData = ref({ documento: null });
const formErrors = ref({});

// ✅ Validación Yup
const schema = yup.object().shape({
    documento: yup.mixed().required("Debe seleccionar un archivo.").nullable(),
});

// 🔐 permisos
const requiredPermissions = computed(() => [
    "todo-acceso-programación-documentos-subidos",
    "editar-programación-documentos-subidos",
]);

// 🧹 limpiar archivo seleccionado
const removeFile = () => {
    formData.value.documento = null;
};

// 📂 cargar archivos
const loadFiles = async () => {
    const folderId = props?.grupo?.carpetas_drive;
    if (!folderId) return (archivos.value = []);
    console.log("📁 ID de carpeta:", folderId);
    try {
        const response = await listarArchivos(folderId);

        // 👇 Aquí está el cambio importante
        archivos.value = Array.isArray(response) ? response : [];

    } catch (error) {
        console.error(error);
        showToast("Error al cargar archivos.", "error");
        archivos.value = [];
    }
};


// 🧩 Reaccionar a cambios de grupo
watch(
    () => props.grupo,
    async (nuevoGrupo) => {
        if (nuevoGrupo?.carpetas_drive?.trim()) {
            await loadFiles();
        } else {
            archivos.value = [];
        }
    },
    { immediate: true, deep: true }
);

// 🚀 subir archivo
const onSubmit = async () => {
    if (saving.value) return;
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    formErrors.value = {};
    const form = new FormData();
    form.append("file", formData.value?.documento);
    form.append("parentFolderId", props.grupo?.carpetas_drive);

    try {
        const response = await store(form);
        if (response?.id) {
            showToast("Archivo subido con éxito.", "success");
            formData.value.documento = null;
            await loadFiles();
        } else {
            showToast("Error al subir el archivo.", "error");
        }
    } catch (error) {
        console.error(error);
        showToast("Error al subir el archivo.", "error");
    }
};

// 🗑️ eliminar archivo
const deleteFile = async (file) => {
    // Mostrar modal de confirmación reutilizable
    showConfirmModal(`¿Seguro que deseas eliminar "${file.name}"?`, async (confirmed) => {
        if (!confirmed) return;

        try {
            // Llamada DELETE al endpoint /drive/file/{id}
            await eliminarArchivo(file.id);

            // Eliminar el archivo del array local
            const index = archivos.value.findIndex((f) => f.id === file.id);
            if (index !== -1) archivos.value.splice(index, 1);

            showToast("Archivo eliminado con éxito.", "success");
        } catch (error) {
            console.error("❌ Error al eliminar archivo:", error);
            showToast("No se pudo eliminar el archivo. Intenta nuevamente.", "error");
        }
    });
};


// 📥 descargar archivo
const downloadFile = async (file) => {
    try {
        // Llama a tu backend pasando el ID del archivo
        const response = await axios.get(`/drive/file/${file.id}/download`, {
            responseType: "blob",
        });

        // Crea un enlace temporal para forzar la descarga
        const blob = new Blob([response.data]);
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", file.name);
        document.body.appendChild(link);
        link.click();

        // Limpieza del enlace temporal
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        showToast(`Descargando "${file.name}"...`, "info");
    } catch (error) {
        console.error("Error al descargar archivo:", error);
        showToast("No se pudo descargar el archivo.", "error");
    }
};


// 📛 título dinámico
const title = computed(
    () =>
        `Subir archivo para: ${props.grupo?.grupo_detalle?.nombre_especialidad || ""
        } - Sección ${props.grupo?.grupo_detalle?.seccion || ""}`
);
</script>

<template>
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">
            <!-- 🗂 campo archivo -->
            <FormInputFile v-model="formData.documento" label="Documento principal *"
                :error-message="formErrors.documento" />

            <!-- 🧾 vista previa -->
            <div v-if="formData.documento" class="mt-3">
                <div class="flex items-center justify-between text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md">
                    <div class="flex items-center gap-2 truncate">
                        <PaperClipIcon class="h-4 w-4 text-gray-500" />
                        <span class="truncate">{{ formData.documento.name }}</span>
                    </div>
                    <button @click="removeFile" type="button" class="text-red-500 hover:text-red-700">
                        <XCircleIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- 🚀 botón subir -->
            <div class="flex justify-end gap-2 pt-4">
                <Button title="Subir Archivo" type="button" :loading="saving" :disabled="saving" @click="onSubmit" />
            </div>

            <!-- 📁 lista de archivos -->
            <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-2">
                    Archivos existentes
                </h3>

                <div v-if="loadingFiles" class="flex items-center gap-2 text-gray-500">
                    <ArrowPathIcon class="animate-spin h-5 w-5" />
                    <span>Cargando archivos...</span>
                </div>

                <ul v-else-if="archivos.length" class="space-y-2">
                    <li v-for="file in archivos" :key="file.id"
                        class="flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-700 rounded-md text-sm">
                        <div class="flex items-center gap-2 truncate">
                            <PaperClipIcon class="h-4 w-4" />
                            <a :href="file.webViewLink" target="_blank"
                                class="text-blue-600 dark:text-blue-400 hover:underline truncate">
                                {{ file.name }}
                            </a>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="downloadFile(file)" class="text-green-600 hover:text-green-800"
                                title="Descargar">
                                <CloudArrowDownIcon class="h-5 w-5" />
                            </button>
                            <button @click="deleteFile(file)" class="text-red-600 hover:text-red-800" title="Eliminar">
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </li>
                </ul>

                <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                    No hay archivos en esta carpeta.
                </p>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>
