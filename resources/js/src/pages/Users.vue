<script setup>
import { ref, computed } from 'vue'

import Table from '../components/table/Table.vue';
import THead from '../components/table/THead.vue';
import TBody from '../components/table/TBody.vue';
import Tr from '../components/table/Tr.vue';
import Th from '../components/table/Th.vue';
import Td from '../components/table/Td.vue';
import SearchBar from '../components/head_table/headSearch.vue'

import CreateButton from '../components/ui/CreateButton.vue';
import EditButton from '../components/ui/EditButton.vue';
import DeleteButton from '../components/ui/DeleteButton.vue';
import AuthorizationFallback from '../components/page/AuthorizationFallback.vue';
import UserSlider from '../components/page/UserSlider.vue';

import useUserStore from '../store/useUserStore';
import useRoleStore from '../store/useRoleStore';
import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';

const userStore = useUserStore();
const roleStore = useRoleStore();

if (!userStore.users?.length) await userStore.loadUsers();
if (!roleStore.roles?.length) await roleStore.loadRoles();

const { slider, sliderData, showSlider, hideSlider } = useSlider('user-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest('/users');

const onDelete = (user) => {
    if (deleting.value) return;

    showConfirmModal(null, async (confirmed) => {
        if (!confirmed) return;

        const isDeleted = await deleteUser(user?.id);
        if (isDeleted) {
            showToast(`"${user?.name}" deleted successfully...`);
            userStore.loadUsers();
        }
    });
};

const usuarios = ref(userStore.users)

const filtro = ref({ field: 'name', query: '' })

function filtrarUsuarios(payload) {
    filtro.value = payload
}

const usuariosFiltrados = computed(() => {
    if (!filtro.value.query) return usuarios.value
    return usuarios.value.filter(user =>
        String(user[filtro.value.field]).toLowerCase().includes(filtro.value.query.toLowerCase())
    )
})


</script>

<template>
    <AuthorizationFallback :permissions="['users-all', 'users-view']">
       
        <div class="w-full space-y-8 py-10 px-6">
            <div class="flex-between">
                
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Usuarios</h2>
                
               
                <CreateButton @click="showSlider(true)" />
                
            </div>
<<<<<<< HEAD

            
=======
            <div class="flex-between space-y-4 py-6 px-6 flex-row-reverse">
                <SearchBar :options="[
                    { label: 'Nombre', value: 'name' },
                    { label: 'Apellido', value: 'apellido_paterno' },
                    { label: 'DNI', value: 'dni' }
                ]" placeholder="Buscar usuario"
                 @search="filtrarUsuarios" 
                 :totalResultados="usuariosFiltrados.length"
                  />

                <div class="text-2xl font-inter">
                    Lista de usuarios
                </div>
            </div>
>>>>>>> 824508029a079ddc00468a3d91c2fe2325a5dfef
            <Table>
                <THead>
                    <Th>Nro</Th>
                    <Th>Nombres</Th>
                    <Th>Apellidos</Th>
                    <Th>Dni</Th>
                    <Th>Correo</Th>
                    <Th>Rol</Th>
                    <Th>Fecha de Creación</Th>
                    <Th>Estado</Th>
                    <Th class="text-center">Opciones</Th>
                </THead>

               
                <TBody>
<<<<<<< HEAD
                    <Tr v-for="(user, index) in userStore.users" :key="index">
                        <Td><span class="text-gray-800 dark:text-gray-300">{{ index + 1 }}</span></Td>
=======
                    <Tr v-for="(user, index) in usuariosFiltrados" :key="index">
                        <Td><span class="text-gray-800">{{ index + 1 }}</span></Td>
>>>>>>> 824508029a079ddc00468a3d91c2fe2325a5dfef
                        <Td>{{ user.name }}</Td>
                        <Td>{{ user.apellido_paterno }}</Td>
                        <Td>{{ user.dni }}</Td>
                        <Td>{{ user.email }}</Td>
                        <Td>
                            
                            <span
                                class="bg-gray-800 text-white text-xs px-2 py-1 rounded-full font-bold">
                                DIRECTOR(a)
                            </span>
                        </Td>
                        <Td>{{ user.created_at.slice(0, 10) }}</Td>
                        <Td>
                            
                            <span :class="user.status === 1
                                ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                                : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'"
                                class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1">
<<<<<<< HEAD
                                Activo
                                <span v-if="user.status === 1">✓</span>
=======
                                <!-- {{ user.status }} -->
                                activo
                                <span>↗</span>
>>>>>>> 824508029a079ddc00468a3d91c2fe2325a5dfef
                            </span>
                        </Td>
                        <Td class="text-center text-gray-600 dark:text-gray-200">
                          
                            <button class="hover:text-cetpro dark:hover:text-cetpro-light text-xl">⋮</button>
                        </Td>
                    </Tr>
                </TBody>
            </Table>
        </div>

        <UserSlider :show="slider" :user="sliderData" @hide="hideSlider" />
    </AuthorizationFallback>
</template>