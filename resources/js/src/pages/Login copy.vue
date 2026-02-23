<script setup>
/*
  LoginProfessional.vue
  Mejoras incluidas:
  - Manejo robusto de validación (Yup)
  - Feedback accesible (aria-live)
  - Indicador de fuerza de contraseña (simple)
  - Gestión de errores con mensajes claros
  - Prevención de doble submit, loading states
  - Toggle de visibilidad de contraseña accesible
  - Enfoque automático y manejo de focus en errores
  - No se loguean contraseñas en consola
  - CSRF + login request (axios)
  - Señales visuales mejoradas (animaciones sutiles, sombras, contrastes)
*/

import { ref, computed, nextTick, onMounted } from 'vue';
import { object, string } from 'yup';
import axios from 'axios';
import Button from '../components/ui/Button.vue';
import ChangePasswordModal from '../components/page/ChangePasswordModal.vue';
import useHttpRequest from '../composables/useHttpRequest';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';

const { store: login, saving: loggingIn } = useHttpRequest('/login');
const { runYupValidation } = useValidation();
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();

const formData = ref({
  usuario: '',
  password: '',
});
const formErrors = ref({});
const showModal = ref(false);
const lastUser = ref(null);
const rememberMe = ref(false);

const isPasswordVisible = ref(false);
const passwordInputType = computed(() => (isPasswordVisible.value ? 'text' : 'password'));
const passwordStrength = ref(0);
const serverError = ref('');
const infoMessage = ref('');

const focusField = ref(null); // ref para manejar focus dinámico

// Yup schema
const schema = object({
  usuario: string().trim().required('El usuario es obligatorio.'),
  password: string().required('La contraseña es obligatoria.'),
});

// Simple password strength estimator (0..4)
function estimatePasswordStrength(pw = '') {
  let score = 0;
  if (pw.length >= 8) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  return score; // 0..4
}

// toggle visibility accessible
function togglePasswordVisibility() {
  isPasswordVisible.value = !isPasswordVisible.value;
  // move focus back to input after toggle to keep keyboard flow
  nextTick(() => {
    const el = document.getElementById('password');
    if (el) el.focus();
  });
}

// detect password strength live
function onPasswordInput(e) {
  passwordStrength.value = estimatePasswordStrength(e.target.value || '');
}

onMounted(() => {
  // autofocus usuario on mount
  nextTick(() => {
    const el = document.getElementById('usuario');
    if (el) el.focus();
  });
});

// submit
const onSignIn = async () => {
  serverError.value = '';
  infoMessage.value = '';
  if (loggingIn.value) return;
  const { validated, data, errors } = await runYupValidation(schema, formData.value);
  if (!validated) {
    formErrors.value = errors;
    // focus first error field (accessible)
    const firstKey = Object.keys(errors)[0];
    nextTick(() => {
      const el = document.getElementById(firstKey);
      if (el) el.focus();
    });
    return;
  }
  formErrors.value = {};
  try {
    // Ensure CSRF cookie (Laravel Sanctum style)
    await axios.get('/sanctum/csrf-cookie');
    const response = await login(data);

    // response handling robusto
    if (response?.requiereCambioPassword) {
      userStore.setUserIdTemporal(response.user_id);
      lastUser.value = { usuario: formData.value.usuario };
      showModal.value = true;
      infoMessage.value = 'Se requiere cambio de contraseña. Sigue los pasos.';
      return;
    }
    if (response?.user?.id) {
      // Set user and navigate
      userStore.setUser(response.user);
      userStore.setRequiereCambioPassword(!!response.requiereCambioPassword);
      // Remember me cookie optional: let backend set it securely via response
      await pushToRoute({ name: 'start' });
      return;
    }

    // fallback
    serverError.value = 'Credenciales inválidas. Intenta nuevamente.';
  } catch (err) {
    // Manejo de errores: muestra mensaje amigable, sin filtrar info técnica al usuario
    serverError.value = err?.response?.data?.message || 'Error de conexión. Intenta más tarde.';
  }
};

const onPasswordChanged = async (newPassword) => {
  // login automático tras cambio de contraseña
  try {
    await axios.get('/sanctum/csrf-cookie');
    formData.value.password = newPassword;
    const response = await login({
      usuario: lastUser.value?.usuario,
      password: newPassword,
    });
    if (response?.user?.id) {
      userStore.setUser(response.user);
      showModal.value = false;
      await pushToRoute({ name: 'start' });
    }
  } catch (err) {
    serverError.value = 'No se pudo ingresar tras cambiar la contraseña.';
  }
};
</script>

<template>
  <section class="min-h-screen grid grid-cols-1 lg:grid-cols-2 font-inter bg-slate-50">
    <!-- Left promotional panel (mejorada) -->
    <aside
      class="hidden lg:flex flex-col justify-between p-16 xl:p-24 relative overflow-hidden bg-gradient-to-br from-cetpro to-cetpro-dark text-white"
      aria-hidden="true"
    >
      <div class="absolute inset-0 opacity-6 pointer-events-none">
        <!-- subtle decorative SVG -->
        <svg class="w-full h-full" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
              <stop offset="0" stop-color="#0ea5a9" stop-opacity="0.06" />
              <stop offset="1" stop-color="#7c3aed" stop-opacity="0.06" />
            </linearGradient>
          </defs>
          <rect width="100%" height="100%" fill="url(#g)"></rect>
        </svg>
      </div>

      <div class="relative z-10">
        <img src="/img/insignia.png" alt="Insignia CETPRO" class="w-20 h-20 mb-6 opacity-90" />
        <h1 class="text-6xl font-extrabold tracking-tight leading-tight">Gestión</h1>
        <h2 class="text-6xl font-extrabold tracking-tight leading-tight">Académica</h2>
        <p class="text-slate-100 max-w-md mt-6">Plataforma oficial del Centro de Educación Técnico-Productiva de PUNO.</p>
      </div>

      <div class="relative z-10 text-slate-200">
        <p class="text-sm">Acceso seguro para personal autorizado.</p>
      </div>
    </aside>

    <!-- Right: login form -->
    <main class="flex items-center justify-center p-6 sm:p-12 bg-white">
      <div class="w-full max-w-md">
        <div class="mb-8">
          <h2 class="text-3xl font-bold text-gray-800">Iniciar Sesión</h2>
          <p class="text-gray-500 mt-1">Accede con tu usuario institucional.</p>
        </div>

        <!-- Live region for form-level messages -->
        <div aria-live="polite" class="sr-only" v-if="infoMessage">{{ infoMessage }}</div>

        <form @submit.prevent="onSignIn" class="space-y-6" novalidate>
          <div>
            <label for="usuario" class="block text-sm font-medium text-gray-700">Usuario</label>
            <div class="mt-1 relative">
              <input
                id="usuario"
                v-model="formData.usuario"
                type="text"
                autocomplete="username"
                :aria-invalid="!!formErrors.usuario"
                :aria-describedby="formErrors.usuario ? 'usuario-error' : null"
                @keydown.enter.prevent="() => document.getElementById('password').focus()"
                class="w-full rounded-md bg-gray-50 border border-gray-200 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-cetpro focus:border-cetpro transition"
              />
            </div>
            <p v-if="formErrors.usuario" id="usuario-error" class="mt-1 text-xs text-red-600">{{ formErrors.usuario }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="formData.password"
                :type="passwordInputType"
                autocomplete="current-password"
                @input="onPasswordInput"
                :aria-invalid="!!formErrors.password"
                :aria-describedby="formErrors.password ? 'password-error password-strength' : 'password-strength'"
                class="w-full rounded-md bg-gray-50 border border-gray-200 p-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-cetpro focus:border-cetpro transition"
              />
              <button
                type="button"
                @click="togglePasswordVisibility"
                :aria-pressed="isPasswordVisible"
                :title="isPasswordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
              >
                <svg v-if="!isPasswordVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cetpro" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.58 10.58A3 3 0 0113.42 13.42" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.88 5.63C11.08 5.22 12.52 5 14 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-1.496 0-2.93-.22-4.13-.63M3 3l18 18" />
                </svg>
              </button>
            </div>

            <!-- Strength meter -->
            <div class="mt-2" id="password-strength" aria-hidden="false">
              <div class="flex items-center gap-3">
                <div class="w-full bg-gray-200 rounded h-2 overflow-hidden">
                  <div
                    :class="[
                      'h-2 transition-all',
                      passwordStrength === 0 ? 'w-0 bg-gray-300' :
                      passwordStrength === 1 ? 'w-1/4 bg-red-500' :
                      passwordStrength === 2 ? 'w-1/2 bg-yellow-400' :
                      passwordStrength === 3 ? 'w-3/4 bg-green-400' :
                      'w-full bg-green-600'
                    ]"
                  />
                </div>
                <span class="text-xs text-gray-500">
                  {{ passwordStrength === 0 ? 'Muy débil' : passwordStrength === 1 ? 'Débil' : passwordStrength === 2 ? 'Aceptable' : passwordStrength === 3 ? 'Fuerte' : 'Excelente' }}
                </span>
              </div>
            </div>

            <p v-if="formErrors.password" id="password-error" class="mt-1 text-xs text-red-600">{{ formErrors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center">
              <input id="remember-me" v-model="rememberMe" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-cetpro focus:ring-cetpro" />
              <span class="ml-2 text-sm text-gray-700">Recuérdame</span>
            </label>

            <!-- 'Forgot password' kept for UX even if admin handles reset: directs to contact/flow -->
            <a href="/soporte/recuperacion" class="text-sm text-cetpro hover:underline">¿Olvidaste tu contraseña?</a>
          </div>

          <div>
            <Button
              title="Ingresar"
              type="submit"
              class="!w-full !py-3 !text-base !font-semibold bg-cetpro hover:bg-cetpro-dark text-white shadow-lg transition-shadow duration-300"
              loading-title="Ingresando..."
              :loading="loggingIn"
            />
          </div>

          <!-- Server error alert -->
          <div v-if="serverError" class="rounded-md bg-red-50 p-3 text-sm text-red-700">
            {{ serverError }}
          </div>
        </form>

        <footer class="w-full text-center text-xs text-gray-400 pt-8 mt-6">
          © 2025 Todos los derechos reservados. CETPRO PUNO.
        </footer>

        <!-- Modal -->
        <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" @close="showModal = false" />
      </div>
    </main>
  </section>
</template>

<style scoped>
/* Animaciones sutiles */
@keyframes float-slow {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
aside img { animation: float-slow 6s ease-in-out infinite; }

/* A11y helper */
.sr-only {
  position: absolute !important;
  width: 1px; height: 1px;
  padding: 0; margin: -1px; overflow: hidden;
  clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}

/* small refinement to focus outlines for keyboard users */
:focus { outline-offset: 2px; }
</style>
//////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import * as THREE from 'three';

// --- Lógica de Autenticación (sin cambios) ---
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

const isAuthenticating = ref(false);
const authFailed = ref(false);
const isMounted = ref(false);

const passwordInputType = computed(() => isPasswordVisible.value ? 'text' : 'password');
const togglePasswordVisibility = () => { isPasswordVisible.value = !isPasswordVisible.value; };
const schema = object().shape({
    usuario: string().nullable().required('El usuario es requerido.'),
    password: string().nullable().required('La contraseña es requerida.'),
});

const onSignIn = async () => {
     if (isAuthenticating.value) return;
    const { validated, data, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        authFailed.value = true;
        setTimeout(() => authFailed.value = false, 500);
        return;
    }
    formErrors.value = {};
    isAuthenticating.value = true;
    await axios.get('/sanctum/csrf-cookie');
    const response = await login(data);
    await new Promise(resolve => setTimeout(resolve, 1200));
    if (response?.requiereCambioPassword) {
        userStore.setUserIdTemporal(response.user_id);
        lastUser.value = { usuario: formData.value.usuario };
        showModal.value = true;
        isAuthenticating.value = false;
        return;
    }
    if (response?.user?.id) {
        userStore.setUser(response.user);
        userStore.setRequiereCambioPassword(response.requiereCambioPassword);
        await pushToRoute({ name: 'start' });
    } else {
        isAuthenticating.value = false;
        authFailed.value = true;
        setTimeout(() => authFailed.value = false, 500);
        formErrors.value.api = response?.message || 'Credenciales incorrectas.';
    }
};

// --- LÓGICA WEBGL PARA LA "OBRA DE ARTE" ---
const artContainer = ref(null);
let renderer, scene, camera, particles, mouse;

const initWebGLArt = () => {
    if (!artContainer.value) return;
    const container = artContainer.value;

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.z = 2.5;

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    container.appendChild(renderer.domElement);

    const particleCount = 2000;
    const positions = new Float32Array(particleCount * 3);
    for (let i = 0; i < particleCount * 3; i++) {
        positions[i] = (Math.random() - 0.5) * 5;
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const material = new THREE.PointsMaterial({
        size: 0.02,
        color: 0x0ea5e9,
        blending: THREE.AdditiveBlending,
        transparent: true,
        depthWrite: false,
    });

    particles = new THREE.Points(geometry, material);
    scene.add(particles);

    mouse = new THREE.Vector2();
    window.addEventListener('mousemove', (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    });

    animate();
};

const clock = new THREE.Clock();
const animate = () => {
    if (!renderer) return;
    const elapsedTime = clock.getElapsedTime();
    requestAnimationFrame(animate);

    particles.rotation.y = elapsedTime * 0.1;
    particles.rotation.x = -mouse.y * 0.2;
    particles.rotation.y += mouse.x * 0.2;
    
    renderer.render(scene, camera);
};

const handleResize = () => {
    if (!artContainer.value || !renderer) return;
    const container = artContainer.value;
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
};

onMounted(() => {
    initWebGLArt();
    window.addEventListener('resize', handleResize);
    setTimeout(() => { isMounted.value = true; }, 100);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <main class="gala-container" :class="{ 'scene-loaded': isMounted }">
        <div class="left-pane">
            <div class="title-container">
                <h1 class="title-word title-gestion">Gestión</h1>
                <h1 class="title-word title-academica">Académica</h1>
            </div>
            <div ref="artContainer" class="art-piece"></div>
             <p class="institution-name">
                Centro de Educación Técnico-Productiva de PUNO.
            </p>
        </div>

        <div class="right-pane">
            <form @submit.prevent="onSignIn" class="minimalist-form" :class="{ 'auth-failed': authFailed }">
                <div class="form-header">
                    <img src="/img/insignia.png" alt="Insignia" class="form-insignia" />
                    <h2>Acceso a la Plataforma</h2>
                </div>

                <div class="field">
                    <label for="usuario">Usuario</label>
                    <input id="usuario" v-model="formData.usuario" type="text" />
                    <p v-if="formErrors.usuario" class="error">{{ formErrors.usuario }}</p>
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" v-model="formData.password" :type="passwordInputType" />
                    <p v-if="formErrors.password" class="error">{{ formErrors.password }}</p>
                </div>

                <div class="actions">
                     <a href="#" class="form-link">¿Olvidó su contraseña?</a>
                </div>

                 <p v-if="formErrors.api" class="error api-error">{{ formErrors.api }}</p>

                <button type="submit" class="submit-action" :disabled="loggingIn">
                    <span v-if="!loggingIn">Ingresar</span>
                    <span v-else class="loader"></span>
                </button>
            </form>
            <footer class="form-footer">© {{ new Date().getFullYear() }} CETPRO PUNO.</footer>
        </div>
        <ChangePasswordModal v-if="showModal" @success="onPasswordChanged" @close="showModal = false" />
    </main>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@800&family=Inter:wght@400;500&display=swap');

:root {
    --color-background: #F4F4F4;
    --color-text-primary: #111827;
    --color-text-secondary: #6B7280;
    --color-brand: #0284C7;
    --color-border: #D1D5DB;
    --color-error: #EF4444;
    --font-heading: 'Montserrat', sans-serif;
    --font-body: 'Inter', sans-serif;
}

/* --- Estructura y Animación de Gala --- */
.gala-container {
    display: grid;
    grid-template-columns: 1fr; /* Stacked by default */
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    background-color: var(--color-background);
    color: var(--color-text-primary);
    font-family: var(--font-body);
}

/* === LA CORRECCIÓN ESTÁ AQUÍ === */
@media (min-width: 1024px) {
    .gala-container {
        grid-template-columns: 3fr 2fr; /* Asymmetrical split on large screens */
    }
}

.left-pane, .right-pane {
    padding: 3rem 4rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: opacity 1.2s cubic-bezier(0.16, 1, 0.3, 1), transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
}

/* --- Animaciones de Entrada Coreografiadas --- */
.left-pane { opacity: 0; transform: translateX(-30px); }
.right-pane { opacity: 0; transform: translateX(30px); }
.scene-loaded .left-pane { opacity: 1; transform: translateX(0); }
.scene-loaded .right-pane { opacity: 1; transform: translateX(0); transition-delay: 200ms; }

/* --- Panel Izquierdo: La Declaración Artística --- */
.left-pane {
    justify-content: space-between;
    gap: 2rem;
}
.title-container {
    position: relative;
    user-select: none;
    z-index: 1;
}
.title-word {
    font-family: var(--font-heading);
    font-size: clamp(4rem, 11vw, 10rem);
    font-weight: 800;
    line-height: 0.9;
    letter-spacing: -0.05em;
    text-transform: uppercase;
}
.title-academica {
    margin-left: 5vw;
}
.art-piece {
    position: absolute;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%);
    width: 35vw;
    height: 35vw;
    max-width: 500px;
    max-height: 500px;
    z-index: 0;
    opacity: 0;
    transition: opacity 1s ease 500ms;
}
.scene-loaded .art-piece { opacity: 1; }
.institution-name {
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    max-width: 250px;
    z-index: 1;
}

/* --- Panel Derecho: La Funcionalidad Elegante --- */
.right-pane {
    background-color: white;
}
.minimalist-form {
    width: 100%;
    max-width: 380px;
    margin: auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.form-header {
    text-align: center;
    margin-bottom: 1rem;
}
.form-insignia { width: 40px; margin: 0 auto 0.5rem; }
.form-header h2 { font-size: 1.25rem; font-weight: 500; }

.field {
    position: relative;
    display: flex;
    flex-direction: column;
}
.field label {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.5rem;
}
.field input {
    background: none;
    border: none;
    border-bottom: 1px solid var(--color-border);
    padding: 0.75rem 0.25rem;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s;
}
.field input:focus {
    border-color: var(--color-brand);
}
.actions {
    display: flex;
    justify-content: flex-end;
}
.form-link {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    text-decoration: none;
    transition: color 0.2s;
}
.form-link:hover { color: var(--color-brand); }
.error { font-size: 0.8rem; color: var(--color-error); margin-top: 0.25rem; }
.api-error { text-align: center; }

/* --- Botón --- */
.submit-action {
    background-color: var(--color-text-primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    display: flex; justify-content: center; align-items: center;
}
.submit-action:hover:not(:disabled) {
    background-color: #000;
    transform: translateY(-2px);
}
.submit-action:disabled { background-color: var(--color-text-secondary); cursor: not-allowed; }
.loader {
    width: 20px; height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.form-footer {
    text-align: center;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-top: auto;
    padding-top: 2rem;
}

/* --- Responsividad --- */
@media (max-width: 1023px) {
    .gala-container { grid-template-columns: 1fr; }
    .left-pane { display: none; }
    .right-pane { background-color: var(--color-background); }
    .minimalist-form { background-color: white; padding: 2rem; border-radius: 12px; }
}
</style>