<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<template>
    <div class="w-full space-y-4 py-4 px-6">
        <h2 class="text-cetpro dark:text-cetpro-light font-bold text-3xl">
            Módulo de Matrículas
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
            Seleccione una opción para gestionar las matrículas de los estudiantes.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
            <div @click="router.push({ name: 'matricula.registrar' })" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer border border-transparent hover:border-cetpro">
                <UserPlusIcon class="h-12 w-12 text-cetpro mx-auto mb-4" />
                <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-gray-200">
                    Matricular Estudiante
                </h3>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400 mt-2">
                    Registrar un nuevo estudiante o matricular uno existente en un grupo.
                </p>
            </div>
            <div @click="router.push({ name: 'matricula.grupos' })" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer border border-transparent hover:border-cetpro">
                <UserGroupIcon class="h-12 w-12 text-cetpro mx-auto mb-4" />
                <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-gray-200">
                    Lista por Grupos
                </h3>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400 mt-2">
                    Ver, editar y gestionar los estudiantes matriculados en cada grupo.
                </p>
            </div>
            <div @click="router.push({ name: 'matricula.reservas' })" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer border border-transparent hover:border-cetpro">
                <ClipboardDocumentCheckIcon class="h-12 w-12 text-cetpro mx-auto mb-4" />
                <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-gray-200">
                    Estudiantes con Reserva
                </h3>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400 mt-2">
                    Gestionar las reservas de matrícula y descargar los documentos correspondientes.
                </p>
            </div>
        </div>
    </div>
</template>