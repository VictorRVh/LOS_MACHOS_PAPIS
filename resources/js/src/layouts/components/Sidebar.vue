<script setup>
import {
    HomeIcon, UserGroupIcon, AcademicCapIcon, ClipboardDocumentListIcon,
    ShieldCheckIcon, KeyIcon
} from '@heroicons/vue/24/outline';

const navLinks = [
    { name: 'Inicio', routeName: 'users', icon: HomeIcon },
    { name: 'Grupos', routeName: 'users', icon: UserGroupIcon },
    { name: 'Programas A.', routeName: 'users', icon: AcademicCapIcon },
    { name: 'Calificaciones', routeName: 'users', icon: ClipboardDocumentListIcon },
    { name: 'Usuarios', routeName: 'users', icon: UserGroupIcon },
    { name: 'Roles', routeName: 'roles', icon: ShieldCheckIcon },
    { name: 'Permisos', routeName: 'permissions', icon: KeyIcon },
];
</script>

<template>
    <!-- El aside ahora es más angosto, como en Canvas -->
    <aside class="w-24 bg-cetpro text-white flex flex-col shrink-0">
        <!-- Logo -->
        <div class="h-20 flex items-center justify-center border-b border-cetpro-dark">
            <RouterLink :to="{ name: 'users' }">
                <!-- Aquí solo cabe el icono del logo -->
                <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12">
            </RouterLink>
        </div>

        <!-- Navegación con el estilo "Canvas" -->
        <nav class="flex-1">
            <ul class="flex flex-col items-center py-4">
                <li v-for="link in navLinks" :key="link.name" class="w-full">
                    <RouterLink :to="{ name: link.routeName }" v-slot="{ isActive, href, navigate }">
                        <a
                            :href="href"
                            @click="navigate"
                            class="flex flex-col items-center justify-center w-full h-20 transition-colors"
                            :class="[
                                isActive
                                    ? 'bg-cetpro-dark text-white border-l-4 border-white' // Activo: fondo oscuro y borde blanco
                                    : 'text-cetpro-text/80 hover:bg-cetpro-light hover:text-white' // Inactivo
                            ]"
                        >
                            <!-- ESTA ES LA PUTA MAGIA: -->
                            <!-- 1. ICONO GRANDE -->
                            <component :is="link.icon" class="h-7 w-7" />
                            <!-- 2. TEXTO PEQUEÑO ABAJO -->
                            <span class="text-xs mt-1 font-medium">{{ link.name }}</span>
                        </a>
                    </RouterLink>
                </li>
            </ul>
        </nav>
    </aside>
</template>