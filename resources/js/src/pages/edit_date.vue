<script setup>
import { ref, onMounted,computed } from 'vue';
import * as yup from 'yup';
import useUserStore from '@/store/useUserStore';
import useHttpRequest from '@/composables/useHttpRequest';
import useValidation from '@/composables/useValidation';
import useModalToast from '@/composables/useModalToast';

// Importando tus componentes de UI reutilizables
import FormInput from '@/components/ui/FormInput.vue';
import Button from '@/components/ui/Button.vue';
// No necesitamos Slider aquí porque esta es una página completa, no un panel lateral.

// 1. STORES Y COMPOSABLES
const userStore = useUserStore();
const { update: updateUser, updating: profileUpdating } = useHttpRequest('/users-update');
const { update: updatePassword, updating: passwordUpdating } = useHttpRequest('/users-update-password'); // Asumiendo endpoint diferente
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

// 2. ESTADO LOCAL DEL FORMULARIO
const profileData = ref({
  name: '',
  apellido_paterno: '',
  apellido_materno: '',
  usuario: '',
  dni: '',
  email: '',
  telefono: '',
  direccion: '',
  fecha_nacimiento: '',
});
const passwordData = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const profileErrors = ref({});
const passwordErrors = ref({});

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const currentPasswordType = computed(() => showCurrentPassword.value ? 'text' : 'password');
const passwordType = computed(() => showPassword.value ? 'text' : 'password');
const confirmPasswordType = computed(() => showConfirmPassword.value ? 'text' : 'password');

const toggleCurrentPassword = () => {
  showCurrentPassword.value = !showCurrentPassword.value;
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const toggleConfirmPassword = () => {
  showConfirmPassword.value = !showConfirmPassword.value;
};

// 3. CARGA INICIAL DE DATOS
onMounted(() => {
  if (userStore.user) {
    // Llenar el formulario de perfil con los datos del store
    Object.keys(profileData.value).forEach(key => {
      if (userStore.user.hasOwnProperty(key)) {
        profileData.value[key] = userStore.user[key];
      }
    });
  }
});

// 4. ESQUEMAS DE VALIDACIÓN
const profileSchema = yup.object({
  name: yup.string().required('El nombre es requerido.'),
  apellido_paterno: yup.string().required('El apellido paterno es requerido.'),
  email: yup.string().email('Debe ser un email válido.').required('El email es requerido.'),
  // Otros campos pueden ser opcionales
});

const passwordSchema = yup.object({
  current_password: yup.string().required('La contraseña actual es requerida.'),
  password: yup.string().required('La nueva contraseña es requerida.').min(8, 'Debe tener al menos 8 caracteres.'),
  password_confirmation: yup.string().required('La confirmación es requerida.').oneOf([yup.ref('password'), null], 'Las contraseñas no coinciden.'),
});

// 5. LÓGICA DE ACTUALIZACIÓN
const handleUpdateProfile = async () => {
  const { validated, errors } = await runYupValidation(profileSchema, profileData.value);
  if (!validated) {
    profileErrors.value = errors;
    return;
  }
  profileErrors.value = {};

  const updatedUser = await updateUser(userStore.user.id, profileData.value);
  if (updatedUser) {
    userStore.setUser(updatedUser);
    showToast('Perfil actualizado correctamente.', 'success');
  }
};

const handleChangePassword = async () => {
  const { validated, errors } = await runYupValidation(passwordSchema, passwordData.value);
  if (!validated) {
    passwordErrors.value = errors;
    return;
  }
  passwordErrors.value = {};

  // Ajusta el endpoint si es necesario
  const result = await updatePassword(userStore.user.id, passwordData.value);
  if (result) {
    showToast('Contraseña actualizada correctamente.', 'success');
    // Limpiar campos de contraseña después de un cambio exitoso
    Object.keys(passwordData.value).forEach(key => passwordData.value[key] = '');
  }
};
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
    <!-- Contenedor principal que alinea los formularios en fila -->
    <div class="flex flex-col lg:flex-row gap-8">

      <!-- Formulario de Información del Perfil -->
      <div class="flex-1 bg-white dark:bg-slate-800 rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
          Información del Perfil
        </h2>
        <hr class="my-4 border-t-2 border-cetpro dark:border-cetpro-light" />

        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="profileData.name" label="Nombres" :error="profileErrors.name" required />
            <FormInput v-model="profileData.apellido_paterno" label="Apellido Paterno"
              :error="profileErrors.apellido_paterno" required />
            <FormInput v-model="profileData.apellido_materno" label="Apellido Materno"
              :error="profileErrors.apellido_materno" />
            <FormInput v-model="profileData.email" type="email" label="Email" :error="profileErrors.email" required />
            <FormInput v-model="profileData.dni" label="DNI" :error="profileErrors.dni" />
            <FormInput v-model="profileData.telefono" label="Teléfono" :error="profileErrors.telefono" />
          </div>
          <FormInput v-model="profileData.direccion" label="Dirección" :error="profileErrors.direccion" />
          <FormInput v-model="profileData.fecha_nacimiento" type="date" label="Fecha de Nacimiento"
            :error="profileErrors.fecha_nacimiento" />

          <div class="pt-4">
            <Button :title="profileUpdating ? 'Guardando...' : 'Guardar Cambios de Perfil'" :loading="profileUpdating"
              @click="handleUpdateProfile" class="w-full" />
          </div>
        </div>
      </div>

      <!-- Formulario de Cambio de Contraseña -->
      <div class="flex-1 bg-white dark:bg-slate-800 rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
          Cambiar Contraseña
        </h2>
        <hr class="my-4 border-t-2 border-cetpro dark:border-cetpro-light" />

        <div class="space-y-4">
          <!-- Contraseña Actual -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Contraseña Actual</label>

            <div class="relative">
              <input v-model="passwordData.current_password" :type="currentPasswordType" class="w-full rounded-md 
      bg-gray-100 text-gray-900 border-gray-300
      dark:bg-gray-800 dark:text-white dark:border-gray-600
      p-3 pr-10" placeholder="••••••••" />

              <button type="button" @click="toggleCurrentPassword"
                class="absolute inset-y-0 right-0 flex items-center pr-3">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" :class="['h-5 w-5', showCurrentPassword ? 'text-cetpro' : 'text-gray-400']">

                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.current_password" class="text-xs text-red-500">
              {{ passwordErrors.current_password }}
            </p>
          </div>

          <!-- Nueva Contraseña -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Nueva Contraseña</label>

            <div class="relative">
              <input v-model="passwordData.password" :type="passwordType" class="w-full rounded-md 
      bg-gray-100 text-gray-900 border-gray-300
      dark:bg-gray-800 dark:text-white dark:border-gray-600
      p-3 pr-10" placeholder="••••••••" />

              <button type="button" @click="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" :class="['h-5 w-5', showPassword ? 'text-cetpro' : 'text-gray-400']">

                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.password" class="text-xs text-red-500">
              {{ passwordErrors.password }}
            </p>
          </div>

          <!-- Confirmar Contraseña -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>

            <div class="relative">
              <input v-model="passwordData.password_confirmation" :type="confirmPasswordType" class="w-full rounded-md 
      bg-gray-100 text-gray-900 border-gray-300
      dark:bg-gray-800 dark:text-white dark:border-gray-600
      p-3 pr-10" placeholder="••••••••" />

              <button type="button" @click="toggleConfirmPassword"
                class="absolute inset-y-0 right-0 flex items-center pr-3">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" :class="['h-5 w-5', showConfirmPassword ? 'text-cetpro' : 'text-gray-400']">

                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.password_confirmation" class="text-xs text-red-500">
              {{ passwordErrors.password_confirmation }}
            </p>
          </div>
          <div class="pt-4">
            <Button :title="passwordUpdating ? 'Actualizando...' : 'Actualizar Contraseña'" :loading="passwordUpdating"
              @click="handleChangePassword" class="w-full" />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>