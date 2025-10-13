<script setup>
import { ref, watch } from 'vue';
import { ArrowUpTrayIcon, XCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null
    },
    label: {
        type: String,
        required: true
    },
    errorMessage: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const fileName = ref('');

watch(() => props.modelValue, (newFile) => {
    fileName.value = newFile ? newFile.name : '';
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        emit('update:modelValue', file);
    }
};

const clearFile = () => {
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
        </label>
        <div 
            @click="triggerFileInput"
            class="mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md cursor-pointer hover:border-cetpro dark:hover:border-cetpro-light"
        >
            <div class="space-y-1 text-center">
                <ArrowUpTrayIcon class="mx-auto h-10 w-10 text-gray-400" />
                <div v-if="!fileName" class="flex text-sm text-gray-600 dark:text-gray-400">
                    <p class="pl-1">Haz clic para seleccionar un archivo</p>
                </div>
                 <div v-else class="text-sm font-semibold text-cetpro dark:text-cetpro-light flex items-center gap-2">
                    <span>{{ fileName }}</span>
                    <button @click.stop="clearFile" class="text-red-500 hover:text-red-700">
                        <XCircleIcon class="h-5 w-5" />
                    </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">Cualquier tipo de archivo</p>
            </div>
            <input ref="fileInput" type="file" @change="handleFileChange" class="sr-only" />
        </div>
        <p v-if="errorMessage" class="mt-1 text-xs text-red-500">{{ errorMessage }}</p>
    </div>
</template>