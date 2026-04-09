<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import Button from '../components/ui/Button.vue';
import ChangePasswordModal from '../components/page/ChangePasswordModal.vue';
import LoginLockoutModal from '../components/page/LoginLockoutModal.vue';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';
import useModalToast from '../composables/useModalToast';
import { string, object } from 'yup';
import axios from 'axios';

const { runYupValidation } = useValidation();
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const { showToast } = useModalToast();

const formData = ref({
    usuario: null,
    password: null,
});
const formErrors = ref({});
const showModal = ref(false);
const lastUser = ref(null);
const rememberMe = ref(false);
const currentYear = new Date().getFullYear();
const loggingIn = ref(false);
const showLockoutModal = ref(false);
const lockoutMessage = ref('');
const lockoutRemainingSeconds = ref(0);
const lockoutTimer = ref(null);

const isPasswordVisible = ref(false);
const passwordInputType = computed(() => isPasswordVisible.value ? 'text' : 'password');
const isLoginBlocked = computed(() => lockoutRemainingSeconds.value > 0);

const togglePasswordVisibility = () => {
    isPasswordVisible.value = !isPasswordVisible.value;
};

const schema = object().shape({
    usuario: string().nullable().required('El campo usuario es obligatorio.'),
    password: string().nullable().required('El campo clave es obligatorio.'),
});

const stopLockoutTimer = () => {
    if (lockoutTimer.value) {
        clearInterval(lockoutTimer.value);
        lockoutTimer.value = null;
    }
};

const closeLockoutModal = () => {
    if (lockoutRemainingSeconds.value > 0) return;
    showLockoutModal.value = false;
    lockoutMessage.value = '';
};

const openLockoutModal = (seconds, message) => {
    lockoutRemainingSeconds.value = seconds;
    lockoutMessage.value = message;
    showLockoutModal.value = true;

    stopLockoutTimer();

    lockoutTimer.value = setInterval(() => {
        if (lockoutRemainingSeconds.value <= 1) {
            lockoutRemainingSeconds.value = 0;
            stopLockoutTimer();
            return;
        }

        lockoutRemainingSeconds.value -= 1;
    }, 1000);
};

const handleLoginError = (error) => {
    const errorData = error?.response?.data ?? {};
    const errorText = errorData?.errorText ?? 'Ocurrió un error al iniciar sesión.';
    const lockoutMatch = errorText.match(/(\d+)\s+segundos/i);

    if (lockoutMatch) {
        openLockoutModal(Number(lockoutMatch[1]), errorText);
        return;
    }

    showToast(
        `${errorData?.errorMessage ?? 'Error'}${errorText ? `\r\n${errorText}` : ''}`,
        'error',
    );
};

const executeLogin = async (payload) => {
    loggingIn.value = true;

    try {
        const response = await axios.post('/login', payload);
        return response.data;
    } catch (error) {
        handleLoginError(error);
        throw error;
    } finally {
        loggingIn.value = false;
    }
};

const onSignIn = async () => {
    if (loggingIn.value || isLoginBlocked.value) return;
    const { validated, data, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};
    await axios.get('/sanctum/csrf-cookie');

    let response = null;

    try {
        response = await executeLogin(data);
    } catch {
        return;
    }

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

    let response = null;

    try {
        response = await executeLogin({
            usuario: lastUser.value.usuario,
            password: newPassword,
        });
    } catch {
        return;
    }

    if (response?.user?.id) {
        userStore.setUser(response.user);
        showModal.value = false;
        await pushToRoute({ name: 'start' });
    }
};

onBeforeUnmount(() => {
    stopLockoutTimer();
});
</script>

<template>
    <section class="h-screen overflow-y-auto overscroll-y-contain bg-slate-100 font-inter text-slate-800 lg:overflow-hidden">
        <div class="flex min-h-full w-full flex-col px-0 py-0 sm:px-0 sm:py-0 lg:h-screen">
            <div class="grid min-h-full grid-cols-1 bg-white shadow-sm lg:h-screen lg:min-h-0 lg:overflow-hidden lg:grid-cols-[minmax(0,1fr)_minmax(460px,0.84fr)] xl:grid-cols-[minmax(0,1fr)_minmax(520px,0.86fr)]">
        <aside class="login-brand relative hidden lg:flex items-center overflow-hidden border-r border-slate-200">
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-[linear-gradient(180deg,_rgba(255,255,255,0.03),_rgba(255,255,255,0.01))]"></div>
                <img src="/img/computacion-en-la-nube.png" alt="" class="login-watermark absolute inset-0 h-full w-full object-contain object-center opacity-[0.08] scale-[1.08]" />
                <div class="node" style="top: 10%; left: 12%;"></div>
                <div class="node" style="top: 18%; left: 78%; width: 6px; height: 6px;"></div>
                <div class="node" style="top: 47%; left: 60%; width: 3px; height: 3px;"></div>
                <div class="node" style="top: 72%; left: 18%;"></div>
                <div class="node" style="top: 84%; left: 82%; width: 6px; height: 6px;"></div>
            </div>

            <div class="relative z-10 mx-auto flex w-full max-w-[42rem] flex-col gap-8">
                <div class="flex max-w-[30rem] flex-col gap-5">
                    <div class="flex h-20 w-20 items-center justify-center border border-white/65 bg-white/80 p-2 backdrop-blur-sm shadow-[0_10px_24px_rgba(15,23,42,0.10)] opacity-0 animate-fade-in-up">
                        <img src="/img/CETPRO_Image.png" alt="Logo CETPRO Puno" class="h-full w-full object-contain" />
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2 opacity-0 animate-fade-in-up delay-100">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cyan-100/80">
                                CETPRO Puno
                            </p>
                            <h1 class="balance-title max-w-[10ch] text-white">
                                Gestión Académica
                            </h1>
                        </div>

                        <p class="max-w-[24rem] text-[13px] leading-6 text-slate-200 opacity-0 animate-fade-in-up delay-200">
                            Plataforma oficial del Centro de Educación Técnico-Productiva de Puno, organizada para un acceso claro, rápido y consistente.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 w-full max-w-[38rem] opacity-0 animate-fade-in-up delay-300">
                    <div class="border border-white/30 bg-white/5 p-4">
                        <div class="flex items-end justify-between gap-4">
                            <div class="min-w-0 flex-1 space-y-3">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-[#f4d89f]">
                                    Acceso para estudiantes
                                </p>
                                <p class="max-w-[14ch] text-[1.45rem] font-semibold leading-tight text-white">
                                    Consulta pública de notas
                                </p>
                                <p class="max-w-[24rem] text-[12px] leading-5 text-slate-200/92">
                                    Revisa calificaciones registradas desde una vista externa, separada del acceso académico institucional.
                                </p>
                            </div>

                            <router-link
                                :to="{ name: 'consulta.notas.publica' }"
                                class="inline-flex shrink-0 items-center justify-center border border-slate-950 bg-slate-950 px-3 py-2 text-[13px] font-semibold text-white transition hover:bg-[#12384f] focus:outline-none focus:ring-2 focus:ring-white/30"
                            >
                                Ir a consulta de notas
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="relative flex min-h-full items-center justify-center bg-white px-2 py-2 sm:px-3 sm:py-3 lg:px-5 xl:px-6">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,_#edf3f8_0%,_#ffffff_20%)] lg:hidden"></div>

            <div class="relative z-10 w-full max-w-[36rem] xl:max-w-[40rem]">
                <div class="mb-3 border border-slate-200 bg-white px-3 py-3 shadow-sm opacity-0 animate-fade-in-up lg:hidden">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center border border-slate-200 bg-white p-1.5">
                            <img src="/img/CETPRO_Image.png" alt="Logo CETPRO Puno" class="h-full w-full object-contain" />
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">CETPRO Puno</p>
                            <h1 class="mt-1 text-[1.75rem] font-semibold leading-none text-slate-900">Gestión Académica</h1>
                        </div>
                    </div>
                    <p class="mt-4 max-w-xl text-[13px] leading-5 text-slate-600">
                        Acceso institucional con una interfaz simple, legible y adaptada a distintas resoluciones.
                    </p>
                </div>

                <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:min-h-[430px] lg:p-5 xl:min-h-[450px] xl:p-6">
                    <div class="mb-4 opacity-0 animate-fade-in delay-100">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cetpro/80">Acceso institucional</p>
                        <h2 class="mt-1 text-[1.7rem] font-semibold tracking-tight text-slate-900 xl:text-[2.15rem]">Iniciar sesión</h2>
                        <p class="mt-1 max-w-md text-[13px] leading-6 text-slate-600">
                            Ingresa con tu usuario y contraseña para continuar en la plataforma.
                        </p>
                    </div>

                    <form @submit.prevent="onSignIn" class="space-y-3.5 opacity-0 animate-fade-in delay-200">
                        <div class="space-y-1.5">
                            <label for="usuario" class="block text-[13px] font-medium text-slate-700">Usuario</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                </span>
                                <input
                                    id="usuario"
                                    v-model="formData.usuario"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Nombre de usuario"
                                    :class="[
                                        'w-full rounded-none border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-3 text-[14px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:bg-white focus:ring-1 focus:ring-slate-200',
                                        { 'border-red-500 focus:border-red-500 focus:ring-red-500/10': formErrors.usuario }
                                    ]"
                                />
                            </div>
                            <p v-if="formErrors.usuario" class="text-xs text-red-600">{{ formErrors.usuario }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="password" class="block text-[13px] font-medium text-slate-700">Contraseña</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                </span>
                                <input
                                    id="password"
                                    v-model="formData.password"
                                    :type="passwordInputType"
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    :class="[
                                        'w-full rounded-none border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-10 text-[14px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:bg-white focus:ring-1 focus:ring-slate-200',
                                        { 'border-red-500 focus:border-red-500 focus:ring-red-500/10': formErrors.password }
                                    ]"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <button
                                        type="button"
                                        @click="togglePasswordVisibility"
                                        class="p-1 text-slate-400 transition hover:text-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-200"
                                        :aria-label="isPasswordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            :class="['h-4 w-4', isPasswordVisible ? 'text-cetpro' : 'text-slate-400']">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p v-if="formErrors.password" class="text-xs text-red-600">{{ formErrors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-1.5">
                            <label for="remember-me" class="inline-flex cursor-pointer items-center gap-2 text-[13px] text-slate-600">
                                <input id="remember-me" v-model="rememberMe" type="checkbox" class="h-3.5 w-3.5 rounded-none border-slate-300 text-cetpro focus:ring-cetpro-light">
                                <span>Recuérdame</span>
                            </label>
                            <span class="hidden text-[10px] font-medium uppercase tracking-[0.24em] text-slate-400 sm:inline">
                                Acceso seguro
                            </span>
                        </div>

                        <Button
                            :title="isLoginBlocked ? `Espera ${lockoutRemainingSeconds}s` : 'Ingresar'"
                            type="submit"
                            class="!mt-2 !w-full !rounded-none !py-2.5 !text-[15px] !font-semibold !border !border-cetpro bg-cetpro hover:bg-cetpro-dark text-white transition-colors duration-200"
                            loading-title="Ingresando..."
                            :loading="loggingIn"
                            :disabled="isLoginBlocked"
                        />
                    </form>

                    <div class="mt-4 border-t border-slate-200 pt-3 opacity-0 animate-fade-in delay-300 lg:hidden">
                        <div class="border border-slate-200 bg-white px-3 py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-slate-500">
                                Acceso para estudiantes
                            </p>
                            <p class="mt-2 text-xl font-semibold leading-tight text-slate-900">
                                Consulta pública de notas
                            </p>
                            <p class="mt-2 text-[13px] leading-6 text-slate-600">
                                Revisa calificaciones registradas desde una vista externa, separada del acceso académico institucional.
                            </p>
                            <router-link
                                :to="{ name: 'consulta.notas.publica' }"
                                class="mt-4 inline-flex w-full items-center justify-center border border-slate-300 bg-white px-4 py-2.5 text-[13px] font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-200"
                            >
                                Ir a consulta de notas
                            </router-link>
                        </div>
                    </div>
                </div>

                <footer class="relative z-10 w-full px-1 pt-2 text-center text-[11px] text-slate-400 opacity-0 animate-fade-in delay-300">
                    © {{ currentYear }} Todos los derechos reservados. CETPRO PUNO.
                </footer>
            </div>
        </main>
            </div>
        </div>

        <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" @close="showModal = false" />
        <LoginLockoutModal
            :show="showLockoutModal"
            :remaining-seconds="lockoutRemainingSeconds"
            :message="lockoutMessage"
            @close="closeLockoutModal"
        />
    </section>
</template>

<style scoped>
.login-brand {
    padding: clamp(1rem, 1.2vw, 1.35rem);
    background:
        linear-gradient(180deg, #0b6f99 0%, #0c6085 100%);
}

.balance-title {
    font-size: clamp(2rem, 3vw, 3.45rem);
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.035em;
    text-transform: uppercase;
    text-wrap: balance;
}

.node {
    @apply absolute w-4 h-4 rounded-full;
    background: rgba(186, 230, 253, 0.18);
    box-shadow: 0 0 0 4px rgba(186, 230, 253, 0.04);
    animation: float 6s ease-in-out infinite;
}

.login-watermark {
    animation: drift 18s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

.node:nth-child(2) { animation-duration: 7s; animation-delay: 1s; }
.node:nth-child(3) { animation-duration: 8s; }
.node:nth-child(4) { animation-duration: 9s; animation-delay: 2s; }
.node:nth-child(5) { animation-duration: 7.5s; }

@keyframes drift {
    0% { transform: scale(1.08) translate3d(0, 0, 0); }
    50% { transform: scale(1.1) translate3d(10px, -8px, 0); }
    100% { transform: scale(1.08) translate3d(0, 0, 0); }
}

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

@media (max-width: 1023px) {
    .login-brand {
        padding: 0;
    }
}

@media (min-width: 1024px) and (max-width: 1365px) {
    .balance-title {
        font-size: clamp(1.9rem, 2.7vw, 3rem);
    }
}

@media (prefers-reduced-motion: reduce) {
    .login-watermark,
    .node,
    .animate-fade-in,
    .animate-fade-in-up {
        animation: none !important;
    }
}
</style>
