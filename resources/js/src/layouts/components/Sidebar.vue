<script setup>

import usePermissions from '@/composables/usePermissions';

const { hasPermission, userPermissions } = usePermissions();

/*

if (hasPermission(['ver-usuarios', 'editar-usuarios'])) {
  console.log("Tiene permiso funcoianisd");
}
else{
    console.log("no tiene permiso")
}
*/

const navLinks = [
    // { name: 'Inicio', routeName: 'users', icon: HomeIcon },
    // { name: 'Grupos', routeName: 'users', icon: UserGroupIcon },
    // { name: 'Programas A.', routeName: 'users', icon: AcademicCapIcon },
    // { name: 'Calificaciones', routeName: 'users', icon: ClipboardDocumentListIcon },
    { name: 'Usuarios', routeName: 'users', icon: "UserGroupIcon" ,permissions: ["todo-acceso-usuarios", "icono-usuarios"]},
    { name: 'Roles', routeName: 'roles', icon: "ShieldCheckIcon" ,permissions: ["todo-acceso-roles", "icono-roles"]},
    { name: 'Permisos', routeName: 'permissions', icon: "KeyIcon" ,permissions: [ "todo-acceso-permisos","icono-permisos"]},
    { name: 'Docentes', routeName: 'docente', icon: "KeyIcon" ,permissions: [ "todo-acceso-permisos","icono-permisos"]},
    { name: 'Convenios', routeName: 'convenio', icon: "KeyIcon" ,permissions: [ "todo-acceso-permisos","icono-permisos"]},
    { name: 'Periodo', routeName: 'periodo', icon: "KeyIcon" ,permissions: [ "todo-acceso-permisos","icono-permisos"]},

];

//  v-show="item.name === 'Home' || hasPermission(item.permissions)

</script>

<template>
    <aside class="w-24 bg-cetpro dark:bg-gray-800 text-gray-800 dark:text-gray-300 flex flex-col shrink-0">
        <div class="h-20 flex items-center justify-center border-b border-cetpro-dark">
            <RouterLink :to="{ name: 'users' }">
                <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12">
            </RouterLink>
        </div>
        <nav class="flex-1">
            <ul class="flex flex-col items-center py-4">
                <li v-for="link in navLinks" :key="link.name" class="w-full">
                    <RouterLink :to="{ name: link.routeName }" v-slot="{ isActive, href, navigate }"  v-show="link.name === 'Home' || hasPermission(link.permissions)" >
                        <a
                            :href="href"
                            @click="navigate"
                            class="flex flex-col items-center justify-center w-full h-20 transition-colors "
                            :class="[
                                isActive
                                    ? 'bg-cetpro-dark text-white border-l-4 border-white'
                                    : 'text-cetpro-text/80 hover:bg-cetpro-light hover:text-white'
                            ]"
                        >
                        
                            <component :is="link.icon" class="h-7 w-7" />
                            <span class="text-xs mt-1 font-medium">{{ link.name }}</span>
                        </a>
                    </RouterLink>

                </li>
            </ul>
        </nav>
    </aside>
</template>