<script setup>
import { ref } from 'vue';
import FormInput from '../components/ui/FormInput.vue';
import Button from '../components/ui/Button.vue';

import useHttpRequest from '../composables/useHttpRequest';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';

import { string, object } from 'yup';

const { store: login, saving: loggingIn } = useHttpRequest('/login');
const { runYupValidation } = useValidation();
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();

const formData = ref({
  email: null,
  password: null,
});
const formErrors = ref({});

const schema = object().shape({
  email: string().email().nullable().required(),
  password: string().nullable().required(),
});

const onSignIn = async () => {
  if (loggingIn.value) return;
  const { validated, data, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    return;
  }

  formErrors.value = {};
  const user = await login(data);
  if (user?.id) {
    userStore.setUser(user);
    await pushToRoute({ name: 'users' });
  }
};
</script>

<template>
  <section class="min-h-screen flex flex-col md:flex-row text-[#222] font-inter bg-[#f4fafd]">
    <!-- Left Panel flex flex-col justify-center-->
    <div class="md:w-full p-12 bg-[#EAF6FD]">
      <div class="border-l-8 border-[#00A0E3] m-12 px-4">
        <h1 class="text-3xl font-extrabold leading-snug text-[#00A0E3] uppercase">
          CENTRO DE EDUCACIÓN      <br />
          TÉCNICO PRODUCTIVA PUNO
        </h1>
        <p class="text-lg mt-2 text-gray-800">Sistema de Gestión Académica.</p>
      </div>

      <!-- Card box -->
      <div class=" mt-10 m-auto bg-white shadow-md rounded-xl w- max-w-[600px] overflow-hidden">
        <!-- Header -->
        <div class="bg-[#00AEEF] text-white text-center font-semibold py-2">
          Nuestras Plataformas
        </div>

        <!-- Body -->
        <div class="p-6 flex flex-col items-center">
          <img
            src="/img/imagenLogin.png"
            alt="Ilustración educativa"
            class="w-40 mb-6"
          />
          <button
            class="bg-[#FFD000] text-white font-bold text-sm py-2 px-6 rounded-md hover:bg-[#e6bc00] transition-colors"
          >
            PAGINA WEB
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: login ya hecho -->
    <div class="md:w-1/2 flex flex-col justify-center items-center px-6 py-12 bg-white">
      <img src="/img/insignia.png" alt="Logo CETPRO" class="w-24 mb-4" />
      <h2 class="text-2xl font-bold text-[#00AEEF] mb-6">Bienvenido</h2>

      <!-- Formulario -->
      <div class="w-full max-w-sm">
        <FormInput
          v-model="formData.email"
          label="Usuario"
          :error="formErrors?.email"
        />
        <div class="mt-4">
          <FormInput
            v-model="formData.password"
            label="Clave"
            type="password"
            :error="formErrors?.password"
            show-password
          />
        </div>

        <div class="flex items-center mt-4">
          <input id="remember" type="checkbox" class="mr-2" />
          <label for="remember" class="text-sm text-gray-700">Recuerdame</label>
        </div>

        <Button
          title="Ingresar"
          class="!w-full mt-6 bg-[#00AEEF] text-white"
          loading-title="Ingresando..."
          :loading="loggingIn"
          @click="onSignIn"
        />
      </div>
    </div>

    <!-- Footer -->
    <footer class="w-full text-center text-xs text-gray-500 py-4 absolute bottom-0">
      © 2025 Todos los derechos reservados. CETPRO Puno — Educación Técnica para el Futuro.
    </footer>
  </section>
</template>
