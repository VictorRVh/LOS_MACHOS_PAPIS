<script setup>
import { ref } from 'vue';
import FormInput from '../components/ui/FormInput.vue';
import Button from '../components/ui/Button.vue';

import useHttpRequest from '../composables/useHttpRequest';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';

import { string, object } from 'yup';
import axios from 'axios';

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

    const response = await login(data); 

    if (response?.user?.id) {
        userStore.setUser(response.user);
        // userStore.setRequiereCambioPassword(response.requiereCambioPassword); 

        await pushToRoute({ name: 'start' }); 
    }
};

</script>

<template>
    <section class="min-h-screen flex flex-col md:flex-row text-[#222] font-inter bg-[#f4fafd] relative">
        <!-- Panel Izquierdo: Imagen SVG como fondo -->
        <!-- Panel izquierdo con imagen SVG como hijo y object-cover aplicado correctamente -->
        <div class="md:w-3/5 h-[300px] md:h-screen relative flex items-center justify-center overflow-hidden">
            <img src="/img/logoFijo.svg" alt="Login" class="max-w-[100%] max-h-[100%] object-contain" />
        </div>

        <!-- Panel Derecho: Formulario -->
        <div class="md:w-2/5 flex flex-col justify-center items-center px-6 py-12 bg-white">
            <img src="/img/insignia.png" alt="Logo CETPRO" class="w-24 mb-4" />
            <h2 class="text-2xl font-bold text-[#00AEEF] mb-6">Bienvenido</h2>

            <!-- Formulario -->
            <div class="w-full max-w-sm">
                <FormInput v-model="formData.usuario" label="Usuario" :error="formErrors?.usuario" />
                <div class="mt-4">
                    <FormInput v-model="formData.password" label="Clave" type="password" :error="formErrors?.password"
                        show-password />
                </div>

                <div class="flex items-center mt-4">
                    <input id="remember" type="checkbox" class="mr-2" />
                    <label for="remember" class="text-sm text-gray-700">Recuerdame</label>
                </div>

                <Button title="Ingresar" class="!w-full mt-6 bg-[#fecc01] text-white" loading-title="Ingresando..."
                    :loading="loggingIn" @click="onSignIn" />
            </div>
        </div>

        <!-- Footer -->
        <footer class="w-full text-center text-xs text-gray-500 py-4 absolute bottom-0">
            © 2025 Todos los derechos reservados. CETPRO Puno — Educación Técnica para el Futuro.
        </footer>
    </section>
</template>
