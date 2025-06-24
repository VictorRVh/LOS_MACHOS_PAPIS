<script setup>
import { onMounted } from 'vue';
import Table from '../components/table/Table.vue';
import THead from '../components/table/THead.vue';
import TBody from '../components/table/TBody.vue';
import Tr from '../components/table/Tr.vue';
import Th from '../components/table/Th.vue';
import Td from '../components/table/Td.vue';
import AuthorizationFallback from '../components/page/AuthorizationFallback.vue';
import usePermissionStore from '../store/usePermissionStore';

const permissionStore = usePermissionStore();

onMounted(() => {
    if (!permissionStore.permissions.length) {
        permissionStore.loadPermissions();
    }
});
</script>

<template>
    <AuthorizationFallback :permissions="['permissions-all', 'permissions-view']">
        <div class="w-full space-y-8 py-10 px-6">
            <div class="flex-between">
                <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">Listado de Permisos</h2>
            </div>

            <Table>
                <THead>
                    <Th>Id</Th>
                    <Th>Permiso</Th>
                    <Th>Descripción</Th>
                </THead>
                <TBody>
                    <Tr v-for="permission in permissionStore.permissions" :key="permission.id">
                        <Td>{{ permission?.id }}</Td>
                        <Td>{{ permission?.name }}</Td>
                        <Td class="text-slate-500">Permiso del sistema</Td>
                    </Tr>
                </TBody>
            </Table>
        </div>
    </AuthorizationFallback>
</template>