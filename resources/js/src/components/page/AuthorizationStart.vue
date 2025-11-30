<script setup>
import { computed } from 'vue'
import useUserStore from '../../store/useUserStore'

const props = defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
})

const userStore = useUserStore()

const isAuthorized = computed(() => {
    if (props.permissions.length === 0) return true

    const userPermissions = userStore.user?.permissions || []
    return userPermissions.some(p => props.permissions.includes(p?.name))
})
</script>

<template>
    <template v-if="isAuthorized">
        <slot></slot>
    </template>

    <!-- Tarjeta en blanco estilo UNAP -->
    <!-- Vista cuando NO tiene permisos -->
    <!-- Vista cuando NO tiene permisos → Mostrar información institucional -->
    <!-- Pantalla completa de bienvenida -->
    <div v-else class="w-full h-[90vh] flex items-center justify-center bg-gray-50 dark:bg-slate-900">
        <div class="text-center max-w-3xl px-6">

            <!-- Escudo / logo si quieres después -->
            <!-- <img src="/logo_unap.png" class="mx-auto w-28 mb-6" /> -->

            <!-- Título principal -->
            <h1 class="text-3xl md:text-4xl font-bold text-gray-700 dark:text-white mb-3">
                Bienvenido a nuestro Sistema de Gestión Académica
            </h1>

            <!-- Subtítulo institucional -->
            <h2 class="text-xl md:text-2xl font-semibold text-cetpro dark:text-cetpro-dark mb-8">
                CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA – PUNO
            </h2>

            <!-- Línea decorativa -->
            <div class="w-32 h-1 bg-cetpro dark:bg-cetpro-dark mx-auto rounded mb-8"></div>

            <!-- Mensaje suave -->
            <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed">
                Seleccione una opción del menú lateral para comenzar.
            </p>
        </div>
    </div>



</template>
