<script setup>
import { inject } from 'vue';
import userMenu from './UserMenu.vue'
import useHttpRequest from '../../composables/useHttpRequest';
import useUserStore from '../../store/useUserStore';
import useRoleStore from '../../store/useRoleStore';
import usePermissionStore from '../../store/usePermissionStore';
import useAppRouter from '../../composables/useAppRouter';
// ÍCONOS FINALES: Usuario, Mundo-Africa, Flecha-Izquierda
import { UserCircleIcon, GlobeEuropeAfricaIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';





const { isDarkMode, updateDarkMode } = inject('theme');
const { index: logout } = useHttpRequest('/logout');


const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        userStore.users = [];
        roleStore.roles = [];
        permissionStore.permissions = [];

        await pushToRoute({ name: 'login' });
    }
};


</script>

<template>

    <div class="bg-white dark:bg-cc-18 border-b-2 border-cetpro">

        <!-- HEADER SUPERIOR: LOGO Y USUARIO -->
        <header class="h-16 flex items-center justify-between px-4">
            <!-- LADO IZQUIERDO: Flecha y Logo -->
            <div class="flex items-center gap-4">
                <button class="text-gray-600 hover:text-cetpro">
                    <!-- CAMBIADO A LA FLECHA SIMPLE -->
                    <ArrowLeftIcon class="h-7 w-7" />
                </button>
                <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12">
            </div>

            <!-- LADO DERECHO: Usuario y Notificaciones -->
            <div class="flex items-center gap-4">
                <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">COORDINADOR</span>

                <!-- La caja con el borde celestito a la izquierda -->
                <div class="flex items-center gap-3 pl-4 border-l-2 border-cetpro">
                    <span v-if="userStore.user" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ userStore.user.name }}
                    </span>
                    <div class="relative flex justify-end items-center gap-4">
                        <!-- Botón que despliega el menú -->
                        <button @click="toggleMenu">
                            <UserCircleIcon class="h-10 w-10 text-gray-500" />
                        </button>

                        <!-- Menú desplegable -->
                        <Transition name="fade">
                            <userMenu v-if="isMenuOpen" class="absolute right-0 top-12" :nombre="userStore.user?.nombre"
                                :apellido="userStore.user?.apellido" @logout="onLogout"
                                @toggle-theme="updateDarkMode(!isDarkMode)" />
                        </Transition>
                    </div>
                </div>

                <!-- CAMBIADO AL MUNDO QUE PEDISTE, CON SU NOTIFICACIÓN -->
                <button class="relative text-gray-500 hover:text-cetpro">
                    <GlobeEuropeAfricaIcon class="h-7 w-7" />
                    <span
                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs text-white">5</span>
                </button>
            </div>
        </header>

        <!-- BREADCRUMBS (La parte de abajo) -->
        <div class="h-10 flex items-center px-4 border-t border-gray-200 dark:border-cc-21">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Inicio / Grupos / Grupo A
            </p>
        </div>
    </div>
</template>