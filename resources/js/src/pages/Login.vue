<script setup>
import { ref } from 'vue';
import FormInput from '../components/ui/FormInput.vue';
import Button from '../components/ui/Button.vue';
import ChangePasswordModal from '../components/page/ChangePasswordModal.vue';

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
const showModal = ref(false); // 👈 visibilidad del modal

// Guardar usuario para login después de cambiar contraseña
const lastUser = ref(null);

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

    if (response?.requiereCambioPassword) {
        // guardar user_id temporal para el cambio de contraseña
        userStore.setUserIdTemporal(response.user_id);
        lastUser.value = {
            usuario: formData.value.usuario,
            password: formData.value.password,
        };
        showModal.value = true; // mostrar modal
        return;
    }

    if (response?.user?.id) {
        userStore.setUser(response.user);
        userStore.setRequiereCambioPassword(response.requiereCambioPassword);
        await pushToRoute({ name: 'start' });
    }
};

const onPasswordChanged = async (newPassword) => {
    // 👈 Se ejecuta cuando el modal cambia la contraseña
    formData.value.password = newPassword;

    const response = await login({
        usuario: lastUser.value.usuario,
        password: newPassword,
    });

    if (response?.user?.id) {
        userStore.setUser(response.user);
        showModal.value = false;
        await pushToRoute({ name: 'start' });
    }
};
</script>

<template>
    <section class="min-h-screen flex flex-col md:flex-row text-[#222] font-inter bg-[#f4fafd] relative">
        <!-- Panel Izquierdo -->
        <div class="md:w-3/5 h-[300px] md:h-screen relative flex items-center justify-center overflow-hidden">
            <img src="/img/logoFijo.svg" alt="Login" class="max-w-[100%] max-h-[100%] object-contain" />
        </div>

        <!-- Panel Derecho -->
        <div class="md:w-2/5 flex flex-col justify-center items-center px-6 py-12 bg-white">
            <img src="/img/insignia.png" alt="Logo CETPRO" class="w-24 mb-4" />
            <h2 class="text-2xl font-bold text-[#00AEEF] mb-6">Bienvenido</h2>

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

        <footer class="w-full text-center text-xs text-gray-500 py-4 absolute bottom-0">
            © 2025 Todos los derechos reservados. CETPRO Puno — Educación Técnica para el Futuro.
        </footer>

        <!-- Modal -->
        <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" @close="showModal = false" />
    </section>
</template>
