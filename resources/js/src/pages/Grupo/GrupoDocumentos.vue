<script setup>
import { ref, onMounted, computed } from 'vue';
import { FolderIcon, DocumentIcon, ArrowUpRightIcon, ArrowPathIcon, ExclamationTriangleIcon, ArrowUturnLeftIcon, HomeIcon, ChevronRightIcon, TrashIcon } from '@heroicons/vue/24/outline';
import axios from '../../utils/axios';
import useModalToast from '../../composables/useModalToast';

// --- ESTADO ---
const items = ref([]); // Archivos y carpetas
const isLoading = ref(true);
const error = ref(null);
const breadcrumbs = ref([]);
// --- ¡¡¡AQUÍ ESTÁ EL CAMBIO!!! ---
const rootFolderId = ref('1QzkLSshtODVGAYdWH2Vm3ZKBYjJHZsri'); // ID REAL DE TU CARPETA
const currentFolderId = ref(null);
const { showToast } = useModalToast();

// --- LÓGICA DE NAVEGACIÓN ---
const fetchItems = async (folderId, folderName = 'Inicio') => {
  isLoading.value = true;
  error.value = null;
  currentFolderId.value = folderId;

  try {
    const { data } = await axios.get('/drive/files', {
      params: { folderId: folderId }
    });
    items.value = data;
    
    // Actualizar breadcrumbs
    if (folderId === rootFolderId.value) {
      breadcrumbs.value = [{ id: folderId, name: 'Sistema CETPRO' }];
    } else {
        const index = breadcrumbs.value.findIndex(b => b.id === folderId);
        if (index !== -1) {
            breadcrumbs.value.splice(index + 1);
        } else {
            breadcrumbs.value.push({ id: folderId, name: folderName });
        }
    }

  } catch (err) {
    console.error("Error al cargar archivos de Google Drive:", err);
    error.value = "Ocurrió un error inesperado al cargar los archivos.";
    showToast(error.value, 'error');
  } finally {
    isLoading.value = false;
  }
};

const openItem = (item) => {
  if (item.mimeType === 'application/vnd.google-apps.folder') {
    fetchItems(item.id, item.name);
  } else {
    if (item.webViewLink) {
      window.open(item.webViewLink, '_blank');
    } else {
      showToast('Este archivo no tiene un enlace para visualizar.', 'warning');
    }
  }
};

const goToBreadcrumb = (index) => {
  const folder = breadcrumbs.value[index];
  fetchItems(folder.id, folder.name);
};

const goUp = () => {
    if (breadcrumbs.value.length > 1) {
        const parentIndex = breadcrumbs.value.length - 2;
        goToBreadcrumb(parentIndex);
    }
};

const isFolder = (item) => item.mimeType === 'application/vnd.google-apps.folder';

onMounted(() => {
    fetchItems(rootFolderId.value, 'Sistema CETPRO');
});

// --- LÓGICA DE ACCIONES (CREAR, BORRAR, SUBIR) ---

const createNewFolder = async () => {
    const folderName = prompt("Nombre de la nueva carpeta:");
    if (!folderName) return;

    try {
        await axios.post('/drive/folder', {
            folderName: folderName,
            parentFolderId: currentFolderId.value
        });
        showToast('Carpeta creada con éxito.', 'success');
        fetchItems(currentFolderId.value);
    } catch (err) {
        showToast('Error al crear la carpeta.', 'error');
    }
}

const deleteItem = async (item) => {
    if (!confirm(`¿Estás seguro de que quieres eliminar "${item.name}"? Esta acción no se puede deshacer.`)) {
        return;
    }
    try {
        await axios.delete(`/drive/file/${item.id}`);
        showToast('Elemento eliminado con éxito.', 'success');
        items.value = items.value.filter(i => i.id !== item.id); // Eliminar de la vista al instante
    } catch (err) {
        showToast('Error al eliminar el elemento.', 'error');
    }
}

const onFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('parentFolderId', currentFolderId.value);

    try {
        isLoading.value = true;
        await axios.post('/drive/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showToast('Archivo subido con éxito.', 'success');
        fetchItems(currentFolderId.value);
    } catch (err) {
        showToast('Error al subir el archivo.', 'error');
    } finally {
        isLoading.value = false;
        event.target.value = null;
    }
};

</script>

<template>
  <div class="space-y-4">
    <!-- Barra de Acciones y Navegación -->
    <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border dark:border-gray-700">
        <!-- Breadcrumbs -->
        <nav class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
            <button @click="goToBreadcrumb(0)" class="hover:text-cetpro">
                <HomeIcon class="h-5 w-5"/>
            </button>
            <template v-for="(crumb, index) in breadcrumbs.slice(1)" :key="crumb.id">
                <ChevronRightIcon class="h-5 w-5 mx-1 text-gray-400"/>
                <button @click="goToBreadcrumb(index + 1)" class="hover:text-cetpro truncate" :title="crumb.name">
                    {{ crumb.name }}
                </button>
            </template>
        </nav>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-2">
            <button v-if="breadcrumbs.length > 1" @click="goUp" title="Subir un nivel" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <ArrowUturnLeftIcon class="h-5 w-5"/>
            </button>
            <button @click="$refs.fileInput.click()" title="Subir archivo" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3.75 3.75M12 9.75l3.75 3.75M17.25 12a4.5 4.5 0 01-9 0 4.5 4.5 0 019 0z" /></svg>
            </button>
            <input type="file" @change="onFileChange" ref="fileInput" class="hidden"/>
            <button @click="createNewFolder" title="Crear carpeta" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
            </button>
        </div>
    </div>
    
    <!-- Contenido -->
    <div v-if="isLoading" class="text-center p-8">Cargando...</div>
    <div v-else-if="error" class="text-center p-8 text-red-500">{{ error }}</div>
    <div v-else-if="items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="item in items" :key="item.id" class="group relative">
            <div @click="openItem(item)" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 aspect-square">
                <component :is="isFolder(item) ? FolderIcon : DocumentIcon" class="h-16 w-16 mb-2" :class="isFolder(item) ? 'text-cetpro' : 'text-gray-500'"/>
                <p class="text-center text-sm font-medium text-gray-800 dark:text-gray-200 truncate w-full" :title="item.name">{{ item.name }}</p>
            </div>
            <button @click="deleteItem(item)" class="absolute top-1 right-1 p-1 bg-white dark:bg-gray-800 rounded-full text-gray-500 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                <TrashIcon class="h-4 w-4"/>
            </button>
        </div>
    </div>
    <div v-else class="text-center p-8 text-gray-500">Esta carpeta está vacía.</div>
  </div>
</template>