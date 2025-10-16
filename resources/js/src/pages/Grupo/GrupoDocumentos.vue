<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../utils/axios';
import useModalToast from '../../composables/useModalToast';

const fileIcons = {
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'NewspaperIcon',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'ChartBarIcon',
  'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PresentationChartLineIcon',
  'application/pdf': 'DocumentArrowDownIcon',
  'image/jpeg': 'PhotoIcon',
  'image/png': 'PhotoIcon',
  'image/gif': 'PhotoIcon',
  'video/mp4': 'VideoCameraIcon',
  'audio/mpeg': 'MusicalNoteIcon',
  default: 'DocumentIcon',
};

const getFileIcon = (mimeType) => fileIcons[mimeType] || fileIcons.default;

const items = ref([]);
const isLoading = ref(true);
const error = ref(null);
const breadcrumbs = ref([]);
const rootFolderId = ref('0AB477u4EnjP6Uk9PVA');
const currentFolderId = ref(null);
const { showToast } = useModalToast();
const showModal = ref(false);
const modalMode = ref('');
const modalData = ref({});
const newName = ref('');

const fetchItems = async (folderId, folderName = 'Raíz') => {
  isLoading.value = true;
  error.value = null;
  currentFolderId.value = folderId;
  try {
    const { data } = await axios.get('/drive/files', { params: { folderId } });
    items.value = data;
    updateBreadcrumbs(folderId, folderName);
  } catch (err) {
    console.error("Error al cargar archivos:", err.response?.data || err.message);
    error.value = "Ocurrió un error inesperado al cargar los archivos.";
    showToast(error.value, 'error');
  } finally {
    isLoading.value = false;
  }
};

const updateBreadcrumbs = (folderId, folderName) => {
    if (folderId === rootFolderId.value) {
      breadcrumbs.value = [{ id: folderId, name: 'SISTEMACETPRO' }];
    } else {
        const index = breadcrumbs.value.findIndex(b => b.id === folderId);
        if (index !== -1) breadcrumbs.value.splice(index + 1);
        else breadcrumbs.value.push({ id: folderId, name: folderName });
    }
};

const openItem = (item) => {
  if (isFolder(item)) fetchItems(item.id, item.name);
  else if (item.webViewLink) window.open(item.webViewLink, '_blank');
  else showToast('Este archivo no tiene un enlace para visualizar.', 'warning');
};

const goToBreadcrumb = (index) => {
  const folder = breadcrumbs.value[index];
  fetchItems(folder.id, folder.name);
};

const goUp = () => {
  if (breadcrumbs.value.length > 1) goToBreadcrumb(breadcrumbs.value.length - 2);
};

const isFolder = (item) => item.mimeType === 'application/vnd.google-apps.folder';

onMounted(() => {
  fetchItems(rootFolderId.value, 'SISTEMACETPRO');
});

const openModal = (mode, item = null) => {
  modalMode.value = mode;
  modalData.value = item ? { ...item } : {};
  newName.value = mode === 'rename' ? item.name : '';
  showModal.value = true;
};

const closeModal = () => showModal.value = false;

const handleModalSubmit = async () => {
  if (!newName.value.trim()) return;
  if (modalMode.value === 'create') await createNewFolder();
  else if (modalMode.value === 'rename') await renameItem();
};

const createNewFolder = async () => {
  try {
    await axios.post('/drive/folder', {
      folderName: newName.value,
      parentFolderId: currentFolderId.value,
    });
    showToast('Carpeta creada.', 'success');
    await fetchItems(currentFolderId.value);
    closeModal();
  } catch (err) {
    showToast('Error al crear.', 'error');
    closeModal();
  }
};

const renameItem = async () => {
    try {
        await axios.patch(`/drive/file/${modalData.value.id}/rename`, {
            newName: newName.value,
        });
        showToast('Nombre actualizado.', 'success');
        await fetchItems(currentFolderId.value);
        closeModal();
    } catch (err) {
        showToast('Error al renombrar.', 'error');
        closeModal();
    }
};

const deleteItem = async (item) => {
  if (!confirm(`¿Estás seguro de que quieres eliminar "${item.name}"?`)) return;
  isLoading.value = true;
  try {
    await axios.delete(`/drive/file/${item.id}`);
    showToast('Elemento eliminado.', 'success');
    await fetchItems(currentFolderId.value);
  } catch (err) {
    console.error("Error al eliminar:", err.response?.data || err.message);
    showToast('Error al eliminar. El archivo podría no existir o ya fue eliminado.', 'error');
    await fetchItems(currentFolderId.value);
  } finally {
    isLoading.value = false;
  }
};

const onFileChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);
  formData.append('parentFolderId', currentFolderId.value);
  isLoading.value = true;
  try {
    await axios.post('/drive/upload', formData);
    showToast('Archivo subido.', 'success');
    await fetchItems(currentFolderId.value);
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
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-2 bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 shadow-sm">
        <nav class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 overflow-x-auto whitespace-nowrap py-1">
            <button @click="goToBreadcrumb(0)" class="flex-shrink-0 p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <HomeIcon class="h-5 w-5 text-gray-600 dark:text-gray-300"/>
            </button>
            <template v-for="(crumb, index) in breadcrumbs.slice(1)" :key="crumb.id">
                <ChevronRightIcon class="h-5 w-5 mx-1 text-gray-400 flex-shrink-0"/>
                <button @click="goToBreadcrumb(index + 1)" class="hover:text-cetpro-dark dark:hover:text-cetpro-light truncate flex-shrink-0" :title="crumb.name">
                    {{ crumb.name }}
                </button>
            </template>
        </nav>

        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="fetchItems(currentFolderId)" :disabled="isLoading" title="Refrescar" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                <ArrowPathIcon class="h-5 w-5" :class="{'animate-spin': isLoading}" />
            </button>
            <button v-if="breadcrumbs.length > 1" @click="goUp" title="Subir un nivel" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                <ArrowUturnLeftIcon class="h-5 w-5"/>
            </button>
            <button @click="$refs.fileInput.click()" title="Subir archivo" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300">
                <ArrowUpTrayIcon class="h-5 w-5"/>
            </button>
            <input type="file" @change="onFileChange" ref="fileInput" class="hidden" accept="*/*"/>
            <button @click="openModal('create')" title="Crear carpeta" class="p-2 rounded-full bg-cetpro hover:bg-cetpro-dark text-white transition">
                <FolderPlusIcon class="h-5 w-5"/>
            </button>
        </div>
    </div>
    
    <div v-if="isLoading" class="text-center p-12 text-gray-500">Cargando...</div>
    <div v-else-if="error" class="text-center p-12 bg-red-50 dark:bg-red-900/20 rounded-lg">
        <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-400"/>
        <p class="mt-4 text-red-600 dark:text-red-300">{{ error }}</p>
    </div>
    <div v-else-if="items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div v-for="item in items" :key="item.id" class="group relative">
            <div @click="openItem(item)" @contextmenu.prevent="openModal('rename', item)" class="flex flex-col items-center justify-center p-3 text-center bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 cursor-pointer hover:border-cetpro dark:hover:border-cetpro-light hover:shadow-lg transition-all aspect-[4/3]">
                <component :is="isFolder(item) ? 'FolderIcon' : getFileIcon(item.mimeType)" class="h-12 w-12 mb-2 flex-shrink-0" :class="isFolder(item) ? 'text-cetpro dark:text-cetpro-light' : 'text-gray-400 dark:text-gray-500'"/>
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 break-words w-full line-clamp-2" :title="item.name">{{ item.name }}</p>
            </div>
            <button @click="deleteItem(item)" class="absolute top-1 right-1 p-1.5 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm rounded-full text-gray-500 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                <TrashIcon class="h-4 w-4"/>
            </button>
        </div>
    </div>
    <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
      <RectangleGroupIcon class="mx-auto h-16 w-16 text-gray-400"/>
      <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Carpeta Vacía</h3>
      <p class="mt-1 text-sm text-gray-500">Sube un archivo o crea una nueva carpeta para empezar.</p>
    </div>
  </div>

  <Transition name="modal-fade">
    <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ modalMode === 'create' ? 'Crear Nueva Carpeta' : 'Renombrar Elemento' }}</h3>
          <p v-if="modalMode === 'rename'" class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate" :title="modalData.name">Nombre actual: {{ modalData.name }}</p>
          <div class="mt-4">
            <label for="newNameInput" class="sr-only">Nuevo nombre</label>
            <input v-model="newName" @keyup.enter="handleModalSubmit" id="newNameInput" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-cetpro focus:border-cetpro dark:placeholder-gray-400" :placeholder="modalMode === 'create' ? 'Ej: Tareas de Verano' : 'Nuevo nombre'" autofocus />
          </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3 rounded-b-lg">
          <button @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</button>
          <button @click="handleModalSubmit" class="px-4 py-2 text-sm font-medium text-white bg-cetpro hover:bg-cetpro-dark rounded-md">{{ modalMode === 'create' ? 'Crear' : 'Guardar' }}</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>