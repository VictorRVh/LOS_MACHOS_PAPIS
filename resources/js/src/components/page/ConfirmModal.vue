<script setup>
import { ref, computed } from 'vue';
import emitter from 'tiny-emitter/instance';
import useUtils from '../../composables/useUtils';

const { isObject, isEmptyObject } = useUtils();

const show = ref(false);
const initialModalData = computed(() => ({
    message: '¿Estás seguro de eliminar?',
    actionButton: {
        class: 'bg-red-500 active:bg-red-500 hover:bg-red-600 shadow-google',
        text: 'Procesar',
    },
    returnButton: {
        class: 'bg-green-500 active:bg-green-500 hover:bg-green-600 shadow-google',
        text: 'Volver',
    },
}));
const setModalData = (data) => {
    if (!data || !isObject(data) || isEmptyObject(data))
        return initialModalData.value;
    const { message, actionButton, returnButton } = data;

    const mergedData = {
        ...initialModalData.value,
        message: message ? message : initialModalData.value.message,
        actionButton: actionButton
            ? actionButton
            : initialModalData.value.actionButton,
        returnButton: returnButton
            ? returnButton
            : initialModalData.value.returnButton,
    };

    return mergedData;
};
const modalData = ref(initialModalData.value);

const onButtonClick = (callback, confirmed) => {
    show.value = false;
    callback(confirmed);
};

emitter.on('show-confirm-modal', (data, callback) => {
    show.value = true;
    modalData.value = setModalData(data);

    setTimeout(() => {
        const actionButton = document.querySelector('#action-button');
        const returnButton = document.querySelector('#return-button');
        const cancelButton = document.querySelector('#cancel-button');
        const overlay = document.querySelector('#confirm-modal-overlay');

        if (actionButton)
            actionButton.addEventListener('click', () => {
                onButtonClick(callback, true);
                actionButton.removeEventListener('click', () => {
                    onButtonClick(callback, true);
                });
            });
        if (returnButton)
            returnButton.addEventListener('click', () => {
                onButtonClick(callback, false);
                returnButton.removeEventListener('click', () => {
                    onButtonClick(callback, false);
                });
            });

        if (returnButton)
            cancelButton.addEventListener('click', () => {
                onButtonClick(callback, null);
                cancelButton.removeEventListener('click', () => {
                    onButtonClick(callback, null);
                });
            });
        if (overlay)
            overlay.addEventListener('click', () => {
                onButtonClick(callback, null);
                overlay.removeEventListener('click', () => {
                    onButtonClick(callback, null);
                });
            });
    }, 100);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
            enter-active-class="transition-opacity duration-300 ease-out"
            leave-active-class="transition-opacity duration-200 ease-in"
        >
            <div
                v-if="show"
                id="confirm-modal-overlay"
                class="fixed inset-0 z-[313] bg-black/60 backdrop-blur-sm"
            ></div>
        </Transition>

        <Transition
            enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95"
            enter-active-class="transition transform duration-300 ease-out"
            leave-active-class="transition transform duration-200 ease-in"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[314] flex items-center justify-center p-4"
                role="dialog" aria-modal="true"
            >
                <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800 shadow-2xl border dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                                </svg>
                            </div>
                            <div class="flex-grow text-left">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">
                                    Confirmar Acción
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ modalData.message }}
                                    </p>
                                </div>
                            </div>
                             <span id="cancel-button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-row-reverse gap-3 bg-gray-50 dark:bg-gray-800/50 px-6 py-4 rounded-b-lg">
                        <button
                            id="action-button"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors sm:ml-3 sm:w-auto bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                        >
                            {{ modalData.actionButton.text }}
                        </button>
                        <button
                            id="return-button"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 transition-colors sm:mt-0 sm:w-auto bg-white text-gray-900 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-600"
                        >
                            {{ modalData.returnButton.text }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>