<script setup>
import { ref, computed } from 'vue';
import Button from '../components/ui/Button.vue';
import ChangePasswordModal from '../components/page/ChangePasswordModal.vue';
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
const showModal = ref(false);
const lastUser = ref(null);
const rememberMe = ref(false);

const isPasswordVisible = ref(false);
const passwordInputType = computed(() => isPasswordVisible.value ? 'text' : 'password');

const togglePasswordVisibility = () => {
    isPasswordVisible.value = !isPasswordVisible.value;
};

const schema = object().shape({
    usuario: string().nullable().required('El campo usuario es obligatorio.'),
    password: string().nullable().required('El campo clave es obligatorio.'),
});

const onSignIn = async () => {
    if (loggingIn.value) return;
    const { validated, data, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};
    await axios.get('/sanctum/csrf-cookie');
    const response = await login(data);
    if (response?.requiereCambioPassword) {
        userStore.setUserIdTemporal(response.user_id);
        lastUser.value = { usuario: formData.value.usuario };
        showModal.value = true;
        return;
    }
    if (response?.user?.id) {
        userStore.setUser(response.user);
        userStore.setRequiereCambioPassword(response.requiereCambioPassword);
        await pushToRoute({ name: 'start' });
    }
};

const onPasswordChanged = async (newPassword) => {
    await axios.get('/sanctum/csrf-cookie');
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
    <section class="min-h-screen grid grid-cols-1 lg:grid-cols-2 font-inter bg-slate-50">
        
        <div class="hidden lg:flex flex-col justify-between p-16 xl:p-24 relative overflow-hidden bg-cetpro-dark">
            <div class="absolute inset-0 z-0">
                <img src="/img/computacion-en-la-nube.png" alt="Marca de Agua" class="absolute inset-0 w-full h-full object-contain object-center opacity-5 transform scale-125" />
                <div class="node" style="top: 10%; left: 20%;"></div>
                <div class="node" style="top: 25%; left: 70%; width: 6px; height: 6px;"></div>
                <div class="node" style="top: 50%; left: 50%; width: 3px; height: 3px;"></div>
                <div class="node" style="top: 70%; left: 15%;"></div>
                <div class="node" style="top: 85%; left: 80%; width: 6px; height: 6px;"></div>
            </div>

            <div class="relative z-10">
                <img src="/img/insignia.png" alt="Insignia" class="w-20 h-20 mb-12 opacity-0 animate-fade-in-up" />
                
                <h1 class="text-7xl xl:text-8xl font-black text-white uppercase tracking-tighter leading-none opacity-0 animate-fade-in-up delay-100">
                    Gestión
                </h1>
                <h1 class="text-7xl xl:text-8xl font-black text-white uppercase tracking-tighter leading-none opacity-0 animate-fade-in-up delay-200">
                    Académica
                </h1>
            </div>
            <div class="relative z-10">
                <p class="text-slate-300 max-w-sm opacity-0 animate-fade-in-up delay-300">
                    Plataforma oficial del Centro de Educación Técnico-Productiva de Puno.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-center p-6 sm:p-12 bg-white">
            <div class="w-full max-w-sm">
                <div class="mb-10 opacity-0 animate-fade-in delay-100">
                    <h2 class="text-3xl font-bold text-gray-800">Iniciar Sesión</h2>
                    <p class="text-gray-500 mt-1">Bienvenido de nuevo.</p>
                </div>
                
                <form @submit.prevent="onSignIn" class="space-y-6 opacity-0 animate-fade-in delay-200">
                    <div class="relative">
                        <span class="absolute hidden lg:flex inset-y-0 -left-12 items-center">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                        </span>
                        <div>
                             <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                            <input
                                id="usuario"
                                v-model="formData.usuario"
                                type="text"
                                placeholder="Nombre de usuario"
                                :class="[
                                    'w-full rounded-md bg-gray-100 border-gray-300 sm:text-sm block p-3 focus:ring-1 focus:ring-cetpro focus:border-cetpro outline-none transition',
                                    { 'border-red-500 ring-red-500': formErrors.usuario }
                                ]"
                            />
                            <p v-if="formErrors.usuario" class="mt-1 text-xs text-red-600">{{ formErrors.usuario }}</p>
                        </div>
                    </div>
                   
                    <div class="relative">
                        <span class="absolute hidden lg:flex inset-y-0 -left-12 items-center">
                             <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                        </span>
                        <div>
                             <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                            <div class="relative w-full">
                                <input
                                    id="password"
                                    v-model="formData.password"
                                    :type="passwordInputType"
                                    placeholder="••••••••"
                                    :class="[
                                        'w-full rounded-md bg-gray-100 border-gray-300 sm:text-sm block p-3 pr-10 focus:ring-1 focus:ring-cetpro focus:border-cetpro outline-none transition',
                                        { 'border-red-500 ring-red-500': formErrors.password }
                                    ]"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" @click="togglePasswordVisibility" class="hover:text-gray-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                            :class="['h-6 w-6', isPasswordVisible ? 'text-cetpro' : 'text-gray-400']">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="formErrors.password" class="mt-1 text-xs text-red-600">{{ formErrors.password }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" v-model="rememberMe" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-cetpro focus:ring-cetpro-light">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Recuérdame</label>
                    </div>
                    
                    <Button 
                        title="Ingresar" 
                        type="submit"
                        class="!w-full !py-3 !text-base !font-semibold bg-cetpro hover:bg-cetpro-dark text-white shadow-lg shadow-cyan-500/30 transition-shadow duration-300" 
                        loading-title="Ingresando..."
                        :loading="loggingIn" 
                    />
                </form>

                <footer class="w-full text-center text-xs text-gray-400 pt-12 mt-auto opacity-0 animate-fade-in delay-300">
                    © 2025 Todos los derechos reservados. CETPRO Puno.
                </footer>
            </div>
        </div>

        <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" @close="showModal = false" />
    </section>
</template>

<style scoped>
.node {
    @apply absolute w-4 h-4 bg-cetpro-light/10 rounded-full;
    animation: float 4s ease-in-out infinite;
}
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}
.node:nth-child(2) { animation-duration: 4s; animation-delay: 1s; }
.node:nth-child(3) { animation-duration: 5s; }
.node:nth-child(4) { animation-duration: 6s; animation-delay: 2s; }
.node:nth-child(5) { animation-duration: 4.5s; }

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    animation-fill-mode: both;
}
.animate-fade-in-up {
    animation: fade-in-up 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    animation-fill-mode: both;
}
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
</style>