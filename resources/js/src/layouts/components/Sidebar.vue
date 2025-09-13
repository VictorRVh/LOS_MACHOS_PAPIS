<script setup>
import usePermissions from '@/composables/usePermissions';
import {
  UsersIcon,
  ShieldCheckIcon,
  KeyIcon,
  AcademicCapIcon,
  PresentationChartLineIcon,
  CalendarDaysIcon,
  BuildingOffice2Icon,
  SparklesIcon,
  UserGroupIcon,
  RectangleStackIcon,
  IdentificationIcon,
  TagIcon,
  NewspaperIcon
} from '@heroicons/vue/24/outline';

const { hasPermission } = usePermissions();

const navLinks = [
    { name: 'Usuarios', routeName: 'users', icon: UsersIcon, permissions: ["todo-acceso-usuarios", "icono-usuarios"]},
    { name: 'Roles', routeName: 'roles', icon: ShieldCheckIcon, permissions: ["todo-acceso-roles", "icono-roles"]},
    { name: 'Permisos', routeName: 'permissions', icon: KeyIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Docentes', routeName: 'docente', icon: AcademicCapIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Convenios', routeName: 'convenio', icon: PresentationChartLineIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Periodo', routeName: 'periodo', icon: CalendarDaysIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Administrativos', routeName: 'administrativos', icon: NewspaperIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Especialidad', routeName: 'especialidad', icon: AcademicCapIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Comisión', routeName: 'comision', icon: UserGroupIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Programa', routeName: 'programa', icon: RectangleStackIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Matricula', routeName: 'matricula.index', icon: IdentificationIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'Grupo', routeName: 'grupo', icon: TagIcon, permissions: ["todo-acceso-permisos","icono-permisos"]},
    { name: 'moduloAsignado', routeName: 'moduloAsignado', icon: AcademicCapIcon, permissions: ["ver-mis-modulos"]},
];

</script>

<template>
    <aside class="w-24 bg-cetpro dark:bg-gray-800 text-gray-800 dark:text-gray-300 flex flex-col shrink-0">
        <div class="h-20 flex items-center justify-center border-b border-cetpro-dark flex-shrink-0">
            <RouterLink :to="{ name: 'start' }">
                <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12">
            </RouterLink>
        </div>
        
        <nav class="flex-1 overflow-y-auto min-h-0 custom-scrollbar">
            <ul class="flex flex-col items-center py-1">
                <li v-for="link in navLinks" :key="link.name" class="w-full">
                    <RouterLink :to="{ name: link.routeName }" v-slot="{ isActive, href, navigate }" v-show="hasPermission(link.permissions)" >
                        <a
                            :href="href"
                            @click="navigate"
                            :title="link.name"
                            class="flex flex-col items-center justify-center w-full h-[60px] transition-colors "
                            :class="[
                                isActive
                                    ? 'bg-cetpro-dark text-white border-l-4 border-white'
                                    : 'text-cetpro-text/80 hover:bg-cetpro-light hover:text-white'
                            ]"
                        >
                            <component :is="link.icon" class="h-8 w-8" />
                            <span class="text-xs mt-1 font-medium">{{ link.name }}</span>
                        </a>
                    </RouterLink>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background-color: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  border: 2px solid transparent;
  background-clip: content-box;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.4);
}
</style>