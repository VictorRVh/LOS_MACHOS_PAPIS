<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-[2px]">
    <div class="w-full max-w-[32rem] border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.22)] animate-fade-in-scale">
      <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cetpro/80">Acceso seguro</p>
        <h2 class="mt-1 text-[1.8rem] font-semibold tracking-tight text-slate-900">Crea tu nueva contraseña</h2>
        <p class="mt-2 max-w-md text-[13px] leading-6 text-slate-600">
          Por tu seguridad, establece una contraseña personal antes de continuar en la plataforma.
        </p>
      </div>

      <form @submit.prevent="onSubmit" @keydown.enter.exact.prevent="onSubmit" class="space-y-4 px-6 py-5 sm:px-7">
        <div class="space-y-1.5">
          <label class="block text-[13px] font-medium text-slate-700">Nueva contraseña</label>

          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
              <LockClosedIcon class="h-4 w-4" />
            </span>

            <input
              v-model="form.password"
              :type="showPass ? 'text' : 'password'"
              class="w-full rounded-none border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-10 text-[14px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:bg-white focus:ring-1 focus:ring-slate-200"
              placeholder="Mínimo 8 caracteres"
              autocomplete="new-password"
              required
            />

            <button
              type="button"
              @click="showPass = !showPass"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-200"
            >
              <EyeIcon v-if="!showPass" class="h-4 w-4" />
              <EyeSlashIcon v-else class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="block text-[13px] font-medium text-slate-700">Confirmar contraseña</label>

          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
              <LockClosedIcon class="h-4 w-4" />
            </span>

            <input
              v-model="form.password_confirmation"
              :type="showConfirm ? 'text' : 'password'"
              class="w-full rounded-none border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-10 text-[14px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:bg-white focus:ring-1 focus:ring-slate-200"
              placeholder="Vuelve a escribir la contraseña"
              autocomplete="new-password"
              required
            />

            <button
              type="button"
              @click="showConfirm = !showConfirm"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-200"
            >
              <EyeIcon v-if="!showConfirm" class="h-4 w-4" />
              <EyeSlashIcon v-else class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div v-if="error" class="border border-red-200 bg-red-50 px-3 py-2 text-red-700">
          <p class="text-xs">{{ error }}</p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
          <button
            type="button"
            class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-4 text-[14px] font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-200"
            @click="closeModal"
          >
            Cancelar
          </button>

          <button
            type="submit"
            class="inline-flex h-10 items-center justify-center border border-cetpro bg-cetpro px-5 text-[14px] font-semibold text-white transition hover:bg-cetpro-dark focus:outline-none focus:ring-2 focus:ring-cetpro/20"
          >
            Guardar y continuar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import useUserStore from '@/store/useUserStore';
import useModalToast from "../../composables/useModalToast";
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

  if (form.value.password.length < 8) {
    error.value = 'La contraseña debe tener al menos 8 caracteres.';
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
