<template>
  <!-- Overlay de fondo -->
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 transition-opacity duration-300 ease-in-out">
    
    <!-- Contenedor del Modal -->
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md m-4 transform transition-all duration-300 ease-in-out scale-95 opacity-0 animate-fade-in-scale">
      
      <!-- Encabezado con Insignia y Títulos -->
      <div class="flex flex-col items-center text-center mb-6">
        <!-- 
          IMPORTANTE: Asegúrate de que tu imagen 'insignia.png' 
          esté en la carpeta `public/img/` de tu proyecto.
        -->
        
        <h2 class="text-2xl font-bold text-gray-800">Crea tu nueva contraseña</h2>
        <p class="text-sm text-gray-500 mt-1">Por tu seguridad, es necesario que establezcas una contraseña personal para tu cuenta.</p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="onSubmit" class="space-y-4">
        
        <!-- Campo Nueva Contraseña -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
              </svg>
            </span>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="block w-full border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition"
              placeholder="Mínimo 6 caracteres"
              required
            />
          </div>
        </div>

        <!-- Campo Confirmar Contraseña -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
          <div class="relative">
             <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
              </svg>
            </span>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="block w-full border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition"
              placeholder="Vuelve a escribir la contraseña"
              required
            />
          </div>
        </div>
        
        <!-- Mensaje de Error -->
        <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 p-3 rounded-md" role="alert">
          <p class="text-sm">{{ error }}</p>
        </div>
      </form>
      
      <!-- Botones de Acción -->
      <div class="flex justify-end mt-8 gap-3">
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition"
          @click="closeModal"
        >
          Cancelar
        </button>
        <button
          type="submit"
          class="px-6 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 shadow-sm transition"
          @click="onSubmit"
        >
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

const emit = defineEmits(['close', 'success']);
const userStore = useUserStore();

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
    error.value = 'La contraseña debe tener al menos 6 caracteres.';
    return;
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden. Por favor, verifica.';
    return;
  }

  try {
    await axios.post('/auth/reset_password', {
      user_id: userStore.userIdTemporal,
      nueva_password: form.value.password,
      nueva_password_confirmation: form.value.password_confirmation,
    });

    emit('success', form.value.password);
    closeModal();
  } catch (err) {
    error.value = err.response?.data?.message || 'Ocurrió un error al intentar cambiar la contraseña.';
  }
};
</script>

<style>
/* Animación para la entrada del modal */
@keyframes fade-in-scale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in-scale {
  animation: fade-in-scale 0.3s ease-out forwards;
}
</style>