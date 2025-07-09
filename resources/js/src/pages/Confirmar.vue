<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">Confirmar Rol</h1>
    <p class="mb-4">¿Estás seguro de que deseas guardar este nuevo rol?</p>
    <router-link
      to="/roles"
      class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
    >
      Confirmar y volver
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'Confirmar'
};
</script>
