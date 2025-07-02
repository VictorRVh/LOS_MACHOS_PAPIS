<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
      <h2 class="text-lg font-bold mb-4">Cambiar contraseña</h2>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium">Nueva contraseña</label>
          <input v-model="form.password" type="password" class="mt-1 w-full border px-3 py-2 rounded"
            placeholder="Mínimo 6 caracteres" />
        </div>

        <div>
          <label class="block text-sm font-medium">Confirmar contraseña</label>
          <input v-model="form.password_confirmation" type="password" class="mt-1 w-full border px-3 py-2 rounded" />
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
      </div>

      <div class="flex justify-end mt-6 gap-2">
        <button class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded" @click="closeModal">
          Cancelar
        </button>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" @click="onSubmit">
          Cambiar contraseña
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'success']);

const form = ref({
  password: '',
  password_confirmation: '',
});
const error = ref('');

const closeModal = () => {
  form.value = { password: '', password_confirmation: '' };
  error.value = '';
  emit('close');
};

const onSubmit = async () => {
  error.value = '';

  if (form.value.password.length < 6) {
    error.value = 'La contraseña debe tener al menos 6 caracteres';
    return;
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden';
    return;
  }

  try {

    await axios.get('/sanctum/csrf-cookie');

    await axios.post('/auth/reset_password', {
      nueva_password: form.value.password,
      nueva_password_confirmation: form.value.password_confirmation,
    });

    emit('success');
    closeModal();
  } catch (err) {
    error.value = err.response?.data?.message || 'Ocurrió un error';
  }
};
</script>
