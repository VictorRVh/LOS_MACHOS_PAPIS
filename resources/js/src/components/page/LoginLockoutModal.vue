<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[320] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-[2px]"
  >
    <div class="w-full max-w-[32rem] border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.22)] animate-fade-in-scale">
      <div class="border-b border-slate-200 px-6 py-5 sm:px-7">
        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cetpro/80">Acceso protegido</p>
        <h2 class="mt-1 text-[1.8rem] font-semibold tracking-tight text-slate-900">Espera antes de reintentar</h2>
        <p class="mt-2 max-w-md text-[13px] leading-6 text-slate-600">
          Detectamos varios intentos fallidos. Por seguridad, el acceso se bloqueó temporalmente.
        </p>
      </div>

      <div class="space-y-4 px-6 py-5 sm:px-7">
        <div class="border border-slate-200 bg-slate-50 px-4 py-4">
          <p class="text-[13px] leading-6 text-slate-700">
            {{ message }}
          </p>
        </div>

        <div class="border border-cetpro/15 bg-cetpro/5 px-4 py-4">
          <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-cetpro/80">Tiempo restante</p>
          <p class="mt-2 text-[2rem] font-semibold leading-none text-cetpro">
            {{ remainingSeconds }}s
          </p>
        </div>

        <div class="flex justify-end border-t border-slate-200 pt-4">
          <button
            type="button"
            class="inline-flex h-10 items-center justify-center border border-cetpro bg-cetpro px-5 text-[14px] font-semibold text-white transition hover:bg-cetpro-dark focus:outline-none focus:ring-2 focus:ring-cetpro/20 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="remainingSeconds > 0"
            @click="$emit('close')"
          >
            {{ remainingSeconds > 0 ? `Disponible en ${remainingSeconds}s` : 'Entendido' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  remainingSeconds: {
    type: Number,
    default: 0,
  },
  message: {
    type: String,
    default: 'Demasiados intentos fallidos.',
  },
});

defineEmits(['close']);
</script>
