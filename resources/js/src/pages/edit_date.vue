<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import * as yup from 'yup';
import useUserStore from '@/store/useUserStore';
import useHttpRequest from '@/composables/useHttpRequest';
import useValidation from '@/composables/useValidation';
import useModalToast from '@/composables/useModalToast';

import FormInput from '@/components/ui/FormInput.vue';
import DatePickerInput from '@/components/ui/DatePickerInput.vue';
import Button from '@/components/ui/Button.vue';

const MAX_AVATAR_SIZE = 5 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/png', 'image/jpeg'];

const userStore = useUserStore();
const { updateFormData: updateUser, updating: profileUpdating } = useHttpRequest('/users-update');
const { update: updatePassword, updating: passwordUpdating } = useHttpRequest('/users-update-password');
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

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

const avatarInput = ref(null);
const avatarFile = ref(null);
const avatarPreviewUrl = ref(null);

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const currentPasswordType = computed(() => (showCurrentPassword.value ? 'text' : 'password'));
const passwordType = computed(() => (showPassword.value ? 'text' : 'password'));
const confirmPasswordType = computed(() => (showConfirmPassword.value ? 'text' : 'password'));
const currentAvatarUrl = computed(() => avatarPreviewUrl.value || userStore.user?.avatar_url || null);

const profileInitials = computed(() => {
  const firstName = profileData.value.name?.trim()?.[0] || userStore.user?.name?.[0] || '';
  const lastName = profileData.value.apellido_paterno?.trim()?.[0] || userStore.user?.apellido_paterno?.[0] || '';
  return `${firstName}${lastName}`.toUpperCase() || 'U';
});

const avatarButtonLabel = computed(() => (currentAvatarUrl.value ? 'Reemplazar foto' : 'Agregar foto'));

const toggleCurrentPassword = () => {
  showCurrentPassword.value = !showCurrentPassword.value;
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const toggleConfirmPassword = () => {
  showConfirmPassword.value = !showConfirmPassword.value;
};

const openAvatarPicker = () => {
  avatarInput.value?.click();
};

const clearAvatarPreview = () => {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value);
    avatarPreviewUrl.value = null;
  }
};

const handleAvatarChange = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
    showToast('Solo se permiten imágenes PNG o JPG.', 'warning');
    event.target.value = '';
    return;
  }

  if (file.size > MAX_AVATAR_SIZE) {
    showToast('La foto de perfil no debe superar los 5 MB.', 'warning');
    event.target.value = '';
    return;
  }

  clearAvatarPreview();
  avatarFile.value = file;
  avatarPreviewUrl.value = URL.createObjectURL(file);
};

onMounted(() => {
  if (userStore.user) {
    Object.keys(profileData.value).forEach(key => {
      if (Object.prototype.hasOwnProperty.call(userStore.user, key)) {
        profileData.value[key] = userStore.user[key] ?? '';
      }
    });
  }
});

onBeforeUnmount(() => {
  clearAvatarPreview();
});

const profileSchema = yup.object({
  name: yup.string().required('El nombre es requerido.'),
  apellido_paterno: yup.string().required('El apellido paterno es requerido.'),
  email: yup.string().email('Debe ser un email válido.').required('El email es requerido.'),
});

const passwordSchema = yup.object({
  current_password: yup.string().required('La contraseña actual es requerida.'),
  password: yup.string().required('La nueva contraseña es requerida.').min(8, 'Debe tener al menos 8 caracteres.'),
  password_confirmation: yup.string().required('La confirmación es requerida.').oneOf([yup.ref('password'), null], 'Las contraseñas no coinciden.'),
});

const handleUpdateProfile = async () => {
  const { validated, errors } = await runYupValidation(profileSchema, profileData.value);
  if (!validated) {
    profileErrors.value = errors;
    return;
  }

  profileErrors.value = {};

  const formData = new FormData();

  Object.entries(profileData.value).forEach(([key, value]) => {
    formData.append(key, value ?? '');
  });

  if (avatarFile.value) {
    formData.append('avatar', avatarFile.value);
  }

  const updatedUser = await updateUser(userStore.user.id, formData);
  if (updatedUser) {
    userStore.setUser(updatedUser);
    avatarFile.value = null;
    clearAvatarPreview();
    if (avatarInput.value) {
      avatarInput.value.value = '';
    }
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

  const result = await updatePassword(userStore.user.id, passwordData.value);
  if (result) {
    showToast('Contraseña actualizada correctamente.', 'success');
    Object.keys(passwordData.value).forEach(key => {
      passwordData.value[key] = '';
    });
  }
};
</script>

<template>
  <div class="mx-auto max-w-6xl px-4 py-2 sm:px-5 lg:px-6">
    <div class="grid grid-cols-1 gap-2.5 xl:grid-cols-[minmax(0,1.2fr)_minmax(300px,0.8fr)] xl:items-start">
      <section class="border border-slate-200/80 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between gap-3 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-800 dark:text-white">
            Perfil
          </h2>
        </div>

        <div class="space-y-2.5">
          <div class="grid gap-4 border-y border-slate-200/80 py-3 dark:border-slate-700 lg:grid-cols-[96px_minmax(0,1fr)] lg:items-center">
            <div class="flex flex-col items-center gap-2">
              <div class="h-24 w-24 overflow-hidden rounded-full border border-slate-200 bg-slate-50 shadow-sm shadow-slate-200/50 dark:border-slate-600 dark:bg-slate-900 dark:shadow-none">
                <img
                  v-if="currentAvatarUrl"
                  :src="currentAvatarUrl"
                  alt="Foto de perfil"
                  class="h-full w-full object-cover"
                />
                <div v-else class="flex h-full w-full items-center justify-center bg-cetpro text-xl font-semibold text-white">
                  {{ profileInitials }}
                </div>
              </div>

              <input
                ref="avatarInput"
                type="file"
                accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                class="hidden"
                @change="handleAvatarChange"
              />

              <button
                type="button"
                class="inline-flex h-7 w-full items-center justify-center rounded-md border border-cetpro/20 bg-cetpro/10 px-2 text-[11px] font-semibold text-cetpro transition-colors duration-150 hover:bg-cetpro/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cetpro/20 dark:border-cetpro-light/25 dark:bg-cetpro-light/10 dark:text-cetpro-light dark:hover:bg-cetpro-light/15"
                @click="openAvatarPicker"
              >
                {{ avatarButtonLabel }}
              </button>
            </div>
            <div class="space-y-1.5">
              <div class="grid grid-cols-1 gap-x-3 gap-y-1.5 md:grid-cols-2">
                <FormInput
                  v-model="profileData.name"
                  label="Nombres"
                  :error="profileErrors.name"
                  required
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
                <FormInput
                  v-model="profileData.apellido_paterno"
                  label="Apellido Paterno"
                  :error="profileErrors.apellido_paterno"
                  required
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
                <FormInput
                  v-model="profileData.apellido_materno"
                  label="Apellido Materno"
                  :error="profileErrors.apellido_materno"
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
                <FormInput
                  v-model="profileData.email"
                  type="email"
                  label="Email"
                  :error="profileErrors.email"
                  required
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
                <FormInput
                  v-model="profileData.dni"
                  label="DNI"
                  :error="profileErrors.dni"
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
                <FormInput
                  v-model="profileData.telefono"
                  label="Teléfono"
                  :error="profileErrors.telefono"
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-x-3 gap-y-1.5 md:grid-cols-[minmax(0,1fr)_180px]">
            <div>
              <FormInput
                v-model="profileData.direccion"
                label="Dirección"
                :error="profileErrors.direccion"
                label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                error-class="mt-1 text-[11px]"
              />
            </div>
              <div>
                <DatePickerInput
                  v-model="profileData.fecha_nacimiento"
                  label="Fecha de Nacimiento"
                  :error="profileErrors.fecha_nacimiento"
                  label-class="mb-0.5 text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300"
                  input-class="min-h-0 h-8.5 rounded-md px-3 py-1 text-[13px]"
                  error-class="mt-1 text-[11px]"
                />
              </div>
          </div>

          <div class="flex justify-start pt-1.5">
            <Button
              :title="profileUpdating ? 'Guardando...' : 'Guardar Cambios de Perfil'"
              :loading="profileUpdating"
              size="sm"
              @click="handleUpdateProfile"
              class="h-8.5 rounded-md px-4"
            />
          </div>
        </div>
      </section>

      <section class="self-start border border-slate-200/80 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between gap-3 pb-2">
          <h2 class="text-[15px] font-semibold text-slate-800 dark:text-white">
            Seguridad
          </h2>
        </div>

        <div class="space-y-1.5">
          <div class="relative">
            <label class="mb-0.5 block text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300">Contraseña Actual</label>

            <div class="relative">
              <input
                v-model="passwordData.current_password"
                :type="currentPasswordType"
                class="h-8.5 w-full rounded-md border border-slate-300 bg-white px-3 pr-10 text-[13px] text-gray-900 transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:outline-none focus:ring-2 focus:ring-cetpro/15 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
                placeholder="••••••••"
              />

              <button type="button" @click="toggleCurrentPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="['h-4 w-4', showCurrentPassword ? 'text-cetpro' : 'text-gray-400']">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.current_password" class="mt-1 text-[11px] text-red-500">
              {{ passwordErrors.current_password }}
            </p>
          </div>

          <div class="relative">
            <label class="mb-0.5 block text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300">Nueva Contraseña</label>

            <div class="relative">
              <input
                v-model="passwordData.password"
                :type="passwordType"
                class="h-8.5 w-full rounded-md border border-slate-300 bg-white px-3 pr-10 text-[13px] text-gray-900 transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:outline-none focus:ring-2 focus:ring-cetpro/15 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
                placeholder="••••••••"
              />

              <button type="button" @click="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="['h-4 w-4', showPassword ? 'text-cetpro' : 'text-gray-400']">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.password" class="mt-1 text-[11px] text-red-500">
              {{ passwordErrors.password }}
            </p>
          </div>

          <div class="relative">
            <label class="mb-0.5 block text-[11px] font-semibold tracking-[0.12em] text-slate-700 dark:text-slate-300">Confirmar Contraseña</label>

            <div class="relative">
              <input
                v-model="passwordData.password_confirmation"
                :type="confirmPasswordType"
                class="h-8.5 w-full rounded-md border border-slate-300 bg-white px-3 pr-10 text-[13px] text-gray-900 transition-colors duration-150 hover:border-cetpro/45 focus:border-cetpro focus:outline-none focus:ring-2 focus:ring-cetpro/15 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20"
                placeholder="••••••••"
              />

              <button type="button" @click="toggleConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="['h-4 w-4', showConfirmPassword ? 'text-cetpro' : 'text-gray-400']">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <p v-if="passwordErrors.password_confirmation" class="mt-1 text-[11px] text-red-500">
              {{ passwordErrors.password_confirmation }}
            </p>
          </div>

          <div class="flex justify-start pt-1">
            <Button
              :title="passwordUpdating ? 'Actualizando...' : 'Actualizar Contraseña'"
              :loading="passwordUpdating"
              size="sm"
              @click="handleChangePassword"
              class="h-8.5 rounded-md px-4"
            />
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
