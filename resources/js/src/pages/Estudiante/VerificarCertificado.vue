<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const codigo = route.params.codigo

const cargando = ref(true)
const valido = ref(false)
const mensaje = ref('')

// --- AÑO DINÁMICO ---
const currentYear = new Date().getFullYear()

// --- DATOS SIMULADOS PARA MAYOR TRANSPARENCIA ---
const certificadoInfo = ref({
  nombreEstudiante: null,
  emitidoPor: null,
  fechaEmision: null,
})

onMounted(async () => {
  try {
    const response = await fetch(`/verificar-certificado/${codigo}`)

    if (!response.ok) {
      throw new Error('No válido')
    }

    const data = await response.json()

    valido.value = data.estado
    mensaje.value = data.mensaje

    // Si el certificado es válido, rellenamos los datos simulados
    if (data.estado) {
      certificadoInfo.value = {
        nombreEstudiante: "Juan Alberto Pérez Gonzales", // Reemplazar con data.estudiante.nombre
        emitidoPor: "Dirección Académica CETPRO Puno",   // Reemplazar con data.emisor
        fechaEmision: "15 de Mayo, 2024"                // Reemplazar con data.fecha
      }
    }

  } catch (error) {
    valido.value = false
    mensaje.value = 'El código de verificación no fue encontrado o el certificado ha sido invalidado.'
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <div class="relative min-h-screen bg-gray-100 px-4 py-8 dark:bg-slate-900 sm:px-6 lg:px-8">
    <div class="flex min-h-[90vh] items-center justify-center">
      <Transition
        appear
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0 translate-y-5"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div class="relative w-full max-w-3xl overflow-hidden bg-white shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
          
          <img 
            src="/img/cetprologoHorizontal.png" 
            alt="Sello de agua oficial" 
            class="absolute inset-0 z-0 h-full w-full object-contain p-24 opacity-[0.02] grayscale"
          >
          
          <div class="relative z-10">
            <header class="flex items-center justify-between border-b bg-white/50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
              <img src="/img/cetprologoHorizontal.png" alt="Logo CETPRO Puno" class="h-12 sm:h-16">
              <img src="/img/LogoMinisterio.png" alt="Logo Ministerio de Educación" class="h-12 sm:h-16">
            </header>

            <main class="p-6 sm:p-8 text-center">
              <div v-if="cargando" class="flex flex-col items-center justify-center py-10">
                <div class="h-14 w-14 animate-spin rounded-full border-4 border-gray-200 border-t-cetpro dark:border-gray-600 dark:border-t-cetpro-light"></div>
                <p class="mt-4 text-base text-gray-600 dark:text-gray-400">Verificando autenticidad del certificado...</p>
              </div>

              <div v-else>
                <div :class="[valido ? 'text-green-500' : 'text-red-500']" class="mx-auto flex h-20 w-20 items-center justify-center">
                  <svg v-if="valido" class="h-20 w-20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" class="stroke-current opacity-20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.75 12L10.58 14.83L16.25 9.17004" class="stroke-current" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <svg v-else class="h-20 w-20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" class="stroke-current opacity-20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.5 9.50002L9.5 14.5M9.5 9.50002L14.5 14.5" class="stroke-current" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>

                <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                  {{ valido ? 'Certificado Auténtico' : 'Certificado No Válido' }}
                </h1>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-400">{{ mensaje }}</p>

                <div v-if="valido" class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                  <dl class="grid grid-cols-1 gap-x-8 gap-y-6 text-left sm:grid-cols-2">
                    <div class="sm:col-span-2">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estudiante</dt>
                      <dd class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-200">{{ certificadoInfo.nombreEstudiante }}</dd>
                    </div>
                    <div class="flex flex-col">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Emitido por</dt>
                      <dd class="mt-1 flex-grow font-semibold text-gray-800 dark:text-gray-200">{{ certificadoInfo.emitidoPor }}</dd>
                    </div>
                     <div class="flex flex-col">
                      <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Emisión</dt>
                      <dd class="mt-1 flex-grow font-semibold text-gray-800 dark:text-gray-200">{{ certificadoInfo.fechaEmision }}</dd>
                    </div>
                  </dl>
                </div>
                
                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código de Verificación</dt>
                  <dd class="mt-1 font-mono text-xl tracking-widest text-cetpro dark:text-cetpro-light">{{ codigo }}</dd>
                </div>
              </div>
            </main>
            
            <footer class="border-t p-6 dark:border-gray-700">
               <button @click="router.push('/')" class="w-full rounded-lg bg-cetpro py-3 text-base font-semibold text-white shadow-md transition-all hover:bg-cetpro-dark focus:outline-none focus:ring-2 focus:ring-cetpro-light focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Volver al Portal
              </button>
            </footer>
          </div>
        </div>
      </Transition>
    </div>
    <div class="absolute bottom-4 left-0 w-full text-center text-xs text-gray-400 dark:text-gray-500">
      © {{ currentYear }} CETPRO Puno. Plataforma Oficial de Verificación.
    </div>
  </div>
</template>