<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">Crear Rol</h1>
    <p class="mb-4">Aquí puedes crear un nuevo rol.</p>
    <router-link
      to="/roles/crear/confirmar"
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      Ir a Confirmar
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'RolesCrear'
};
</script>
