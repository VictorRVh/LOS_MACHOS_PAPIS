<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">

    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md m-4 animate-fade-in-scale">

      <!-- Título -->
      <div class="flex flex-col items-center text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Crea tu nueva contraseña</h2>
        <p class="text-sm text-gray-500 mt-1">Por tu seguridad, establece una contraseña personal.</p>
      </div>

      <form @submit.prevent="onSubmit" class="space-y-4">

        <!-- Nueva contraseña -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>

          <div class="relative">

            <!-- Icono izquierda -->
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <LockClosedIcon class="h-5 w-5 text-gray-400" />
            </span>

            <!-- Input -->
            <input v-model="form.password" :type="showPass ? 'text' : 'password'" class="block w-full border-gray-300 rounded-lg pl-10 pr-10 py-2 
                     focus:ring-2 focus:ring-cyan-500 transition" placeholder="Mínimo 6 caracteres" required />

            <!-- Icono mostrar/ocultar -->
            <button type="button" @click="showPass = !showPass"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
              <EyeIcon v-if="!showPass" class="h-5 w-5" />
              <EyeSlashIcon v-else class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Confirmar contraseña -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>

          <div class="relative">

            <!-- Icono izquierda -->
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <LockClosedIcon class="h-5 w-5 text-gray-400" />
            </span>

            <input v-model="form.password_confirmation" :type="showConfirm ? 'text' : 'password'" class="block w-full border-gray-300 rounded-lg pl-10 pr-10 py-2 
                     focus:ring-2 focus:ring-cyan-500 transition" placeholder="Vuelve a escribir la contraseña"
              required />

            <!-- Icono mostrar/ocultar -->
            <button type="button" @click="showConfirm = !showConfirm"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
              <EyeIcon v-if="!showConfirm" class="h-5 w-5" />
              <EyeSlashIcon v-else class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 p-3 rounded-md">
          <p class="text-sm">{{ error }}</p>
        </div>
      </form>

      <!-- Botones -->
      <div class="flex justify-end mt-8 gap-3">
        <button type="button" class="px-4 py-2 bg-gray-100 rounded-lg" @click="closeModal">
          Cancelar
        </button>

        <button type="submit" class="px-6 py-2 bg-cyan-600 text-white rounded-lg" @click="onSubmit">
          Guardar y Continuar
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import useUserStore from '@/store/useUserStore';
import useModalToast from "../../composables/useModalToast";
// IMPORTAR ICONOS OFICIALES (esto faltaba)
import { EyeIcon, EyeSlashIcon, LockClosedIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(['close', 'success']);
const userStore = useUserStore();
const { showToast } = useModalToast();
const form = ref({
  password: '',
  password_confirmation: '',
});

const error = ref('');

const showPass = ref(false);
const showConfirm = ref(false);

const closeModal = () => {
  form.value = { password: '', password_confirmation: '' };
  error.value = '';
  emit('close');
};

const onSubmit = async () => {
  error.value = '';

  if (form.value.password.length < 6) {
    error.value = 'La contraseña debe tener al menos 6 caracteres.';
    return;
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden.';
    return;
  }

  try {
    await axios.post('/auth/reset_password', {
      user_id: userStore.userIdTemporal,
      nueva_password: form.value.password,
      nueva_password_confirmation: form.value.password_confirmation,
    });

    emit('success', form.value.password);
    showToast("Contraseña actualizada correctamente.");
    closeModal();
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al intentar cambiar la contraseña.';
  }
};
</script>
