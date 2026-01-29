<script setup>
import { useLayoutStore } from '@/store/useLayoutStore';
import usePermissions from '@/composables/usePermissions';
import {
    UsersIcon, ShieldCheckIcon, KeyIcon, AcademicCapIcon, PresentationChartLineIcon,
    CalendarDaysIcon, NewspaperIcon, UserGroupIcon, RectangleStackIcon, IdentificationIcon, TagIcon, XMarkIcon, DocumentDuplicateIcon,
    ClipboardDocumentListIcon, CurrencyDollarIcon, Cog8ToothIcon
} from '@heroicons/vue/24/outline';

const { hasPermission } = usePermissions();
const layoutStore = useLayoutStore();

const navLinks = [
    { name: 'Usuarios', routeName: 'users', icon: UsersIcon, permissions: ["todo-acceso-usuarios", "icono-usuario"] },
    { name: 'Roles', routeName: 'roles', icon: ShieldCheckIcon, permissions: ["todo-acceso-roles", "icono-roles"] },
    { name: 'Permisos', routeName: 'permissions', icon: KeyIcon, permissions: ["todo-acceso-permisos", "icono-permisos"] },
    { name: 'Docentes', routeName: 'docente', icon: AcademicCapIcon, permissions: ["todo-acceso-docentes", "icono-docentes"] },
    { name: 'Modalidades', routeName: 'convenio', icon: PresentationChartLineIcon, permissions: ["todo-acceso-modalidades", "icono-modalidades"] },
    { name: 'Periodos', routeName: 'periodo', icon: CalendarDaysIcon, permissions: ["todo-acceso-periodos", "icono-periodos"] },
    { name: 'Administrativos', routeName: 'administrativos', icon: NewspaperIcon, permissions: ["todo-acceso-administrativos", "icono-administrativos"] },
    { name: 'Programa de Estudios', routeName: 'especialidad', icon: AcademicCapIcon, permissions: ["todo-acceso-programas-de-estudio", "icono-programas-de-estudio"] },
    { name: 'Comisión', routeName: 'comision', icon: UserGroupIcon, permissions: ["todo-acceso-comisiones", "icono-comisiones"] },
    { name: 'Ciclo Académico', routeName: 'programa', icon: RectangleStackIcon, permissions: ["todo-acceso-ciclo-academico", "icono-ciclo-academico"] },
    { name: 'Doc. Curricular', routeName: 'documentos', icon: DocumentDuplicateIcon, permissions: ["todo-documento-programado", "icono-periodos"] },
    { name: 'Matricula', routeName: 'matricula.index', icon: IdentificationIcon, permissions: ["todo-acceso-matriculas", "icono-matriculas"] },
    { name: 'Grupo', routeName: 'grupo', icon: TagIcon, permissions: ["todo-acceso-grupos", "icono-grupos"] },
    { name: 'Mis Módulos', routeName: 'moduloAsignado', icon: AcademicCapIcon, permissions: ["ver-mis-modulos", "ver-estudiantes-asignados"] },
    { name: 'Comisión', routeName: 'comsion.docente', icon: AcademicCapIcon, permissions: ["ver-comsion-docente"] },
    { name: 'Egresados', routeName: 'egresados', icon: AcademicCapIcon, permissions: ["ver-grupos"] },
    { name: 'Ingresos', routeName: 'ingresos', icon: CurrencyDollarIcon, permissions: ["ver-grupos"] },
    { name: 'Ajustes', routeName: 'cetpro.index', icon: Cog8ToothIcon, permissions: ["ver-informacion-cetpro"] },


];

const checkVisibility = (link) => {
    if (!link.permissions || link.permissions.length === 0) {
        return true;
    }
    return hasPermission(link.permissions);
};

</script>

<template>
    <div>
        <aside
            class="bg-cetpro dark:bg-gray-800 text-cetpro-text hidden lg:flex flex-col shrink-0 h-screen transition-all duration-300 ease-in-out"
            :class="layoutStore.isSidebarCollapsed ? 'w-25' : 'w-38'">
            <div class="h-20 flex items-center justify-between border-b border-cetpro-white/50 shrink-0 px-4 gap-2">
                <RouterLink :to="{ name: 'start' }" v-if="!layoutStore.isSidebarCollapsed">
                    <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12 shrink-0">
                </RouterLink>
                <button @click="layoutStore.toggleSidebar"
                    class="pl-2 rounded-full text-cetpro-text/60 hover:bg-cetpro-light hover:text-white"
                    :class="layoutStore.isSidebarCollapsed ? 'mx-auto' : 'ml-auto'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 flex flex-col overflow-y-auto custom-scrollbar">
                <ul class="py-0">
                    <li v-for="link in navLinks" :key="link.name">
                        <RouterLink v-if="checkVisibility(link)"
                            :to="link.routeName === 'docente.grupo.sesiones' ? { name: link.routeName, params: { id: 'default' } } : { name: link.routeName }"
                            v-slot="{ isActive }"
                            class="relative flex flex-col items-center justify-center w-full h-[60px] transition-colors text-cetpro-text/80 hover:bg-cetpro-light hover:text-white group"
                            :class="{ '!bg-cetpro-dark !text-white': isActive }">
                            <span v-if="isActive"
                                class="absolute left-0 top-0 h-full w-1 bg-white rounded-r-full"></span>
                            <component :is="link.icon" class="h-7 w-7 shrink-0" />
                            <span v-if="!layoutStore.isSidebarCollapsed"
                                class="mt-0 text-xs font-medium whitespace-normal break-words text-center leading-tight px-1 max-w-[6rem]">
                                {{ link.name }}
                            </span>

                            <div v-if="layoutStore.isSidebarCollapsed"
                                class="absolute left-full ml-2 hidden group-hover:block w-max bg-gray-800 text-white text-xs rounded py-1 px-2 z-40">
                                {{ link.name }}</div>
                        </RouterLink>
                    </li>
                </ul>
            </nav>
        </aside>

        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-cetpro dark:bg-gray-800 text-cetpro-text flex-col shrink-0 transition-transform duration-300 ease-in-out lg:hidden"
            :class="layoutStore.isSidebarOpenMobile ? 'translate-x-0' : '-translate-x-full'">
            <div class="h-20 flex items-center justify-between border-b border-cetpro-dark/50 px-4">
                <RouterLink :to="{ name: 'start' }">
                    <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12">
                </RouterLink>
                <button @click="layoutStore.toggleSidebarMobile"
                    class="p-2 text-cetpro-text/80 rounded-full hover:bg-cetpro-light hover:text-white">
                    <XMarkIcon class="h-6 w-6" />
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto custom-scrollbar">
                <ul class="py-4">
                    <li v-for="link in navLinks" :key="link.name">
                        <RouterLink v-if="checkVisibility(link)"
                            :to="link.routeName === 'docente.grupo.sesiones' ? { name: link.routeName, params: { id: 'default' } } : { name: link.routeName }"
                            v-slot="{ isActive }" @click="layoutStore.toggleSidebarMobile"
                            class="flex items-center px-6 py-3 text-cetpro-text/80 hover:bg-cetpro-light hover:text-white"
                            :class="{ '!bg-cetpro-dark !text-white font-semibold': isActive }">
                            <component :is="link.icon" class="h-6 w-6 mr-4" />
                            <span>{{ link.name }}</span>
                        </RouterLink>
                    </li>
                </ul>
            </nav>
        </aside>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: theme('colors.cetpro.dark');
    border-radius: 20px;
    border: 3px solid theme('colors.cetpro.DEFAULT');
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: theme('colors.cetpro.light');
}
</style>