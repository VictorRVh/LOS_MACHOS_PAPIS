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
  usuario: null,
  password: null,
});
const formErrors = ref({});

const schema = object().shape({
  usuario: string().nullable().required(),
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
  <main class="min-h-screen flex flex-col lg:flex-row font-sans bg-white">

    <!-- ======================================================= -->
    <!-- PANEL IZQUIERDO: AHORA EN PROPORCIÓN 6/8 (w-3/4)        -->
    <!-- ======================================================= -->
    <div class="hidden lg:flex w-full flex-col justify-center p-8 xl:p-12 lg:w-3/5 bg-gray-50/50">
      <!-- Contenedor con ancho máximo aumentado para el nuevo layout -->
      <div class="w-full max-w-4xl lg:max-w-5xl mx-auto">
        <!-- Título principal -->
        <div class="mb-10">
          <h1 class="text-4xl xl:text-5xl font-extrabold text-[#00AEEF] uppercase leading-tight">
            <span class="block">Centro de Educación</span>
            <span class="block mt-1">Técnico Productiva Puno</span>
          </h1>
          <p class="text-xl xl:text-2xl mt-2 text-gray-500">Sistema de Gestión Académica.</p>
        </div>
        
        <!-- Tarjeta más ancha para llenar el nuevo espacio -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-blue-900/10 flex flex-col">
          <div class="flex items-center gap-4 p-6 text-center border-b border-gray-100">
            <!-- Ícono nuevo integrado -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7 text-gray-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-700">Nuestras Plataformas</h2>
          </div>
          <div class="flex-grow flex items-center justify-center p-10 xl:p-14">
            <!-- Imagen agrandada -->
            <img src="/img/imagenLogin.png" alt="Ilustración educativa" class="w-72 xl:w-96 h-auto" />
          </div>
          <a href="#" class="block w-full text-center bg-yellow-400 py-6 xl:py-7 text-yellow-900 text-xl xl:text-2xl font-bold tracking-wider transition-colors hover:bg-yellow-500 rounded-b-3xl">
              PÁGINA WEB
          </a>
        </div>
        <footer class="w-full text-center text-xs text-gray-400 pt-8 flex-shrink-0">
          © {{ new Date().getFullYear() }} CETPRO Puno — Todos los derechos reservados.
        </footer>
      </div>
    </div>


    <!-- ======================================================= -->
    <!-- PANEL DERECHO: AHORA EN PROPORCIÓN 2/8 (w-1/4)        -->
    <!-- ======================================================= -->
    <div class="flex w-full flex-col items-center justify-center p-8 lg:w-2/5 border-l border-gray-100">
      <div class="w-full max-w-sm">
        
        <!-- Título que SÓLO aparece en móvil -->
        <div class="block lg:hidden mb-8 text-center">
          <h1 class="text-2xl font-extrabold text-[#00AEEF]">CETPRO Puno</h1>
          <p class="text-gray-500 mt-1">Sistema de Gestión Académica</p>
        </div>

        <div class="text-center">
            <img src="/img/insignia.png" alt="Logo CETPRO" class="w-24 h-auto mx-auto mb-4" />
            <h2 class="text-3xl font-bold text-[#00AEEF]">Bienvenido</h2>
            <p class="text-gray-500 mt-1">Ingresa tus credenciales para acceder.</p>
        </div>

        <form @submit.prevent="onSignIn" class="mt-8 space-y-6">
          <FormInput
            v-model="formData.usuario"
            label="Usuario"
            :error="formErrors?.usuario"
          />
          
          <FormInput
            v-model="formData.password"
            label="Clave"
            type="password"
            :error="formErrors?.password"
            show-password
          />
        
          <div class="flex items-center">
            <input id="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#00AEEF] focus:ring-2 focus:ring-[#00AEEF]/50"/>
            <label for="remember" class="ml-2 block text-sm text-gray-700">Recuérdame</label>
          </div>

          <div class="pt-2">
             <Button
                title="Ingresar"
                loading-title="Ingresando..."
                :loading="loggingIn"
                @click="onSignIn"
                class="w-full !bg-[#00AEEF] hover:!bg-[#0095c7] !py-3 !text-base !font-bold !text-white !rounded-lg shadow-lg shadow-blue-500/20"
            />
          </div>
        </form>

        <footer class="lg:hidden mt-16 text-center text-xs text-gray-400">
            © {{ new Date().getFullYear() }} CETPRO Puno — Todos los derechos reservados.
        </footer>
      </div>
    </div>
  </main>
</template>