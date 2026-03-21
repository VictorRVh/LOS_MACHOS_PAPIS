<script setup>
import { UserPlusIcon, UserGroupIcon, ClipboardDocumentCheckIcon } from "@heroicons/vue/24/outline";
import { useRoute, useRouter } from "vue-router";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

const route = useRoute();
const router = useRouter();

const navLinks = [
  {
    text: "Matricular estudiante",
    description: "Registro guiado por pasos",
    to: { name: "matricula.registrar" },
    icon: UserPlusIcon,
  },
  {
    text: "Lista por grupos",
    description: "Seguimiento de matriculas",
    to: { name: "matricula.grupos" },
    icon: UserGroupIcon,
  },
  {
    text: "Estudiantes con reserva",
    description: "Pendientes por confirmar",
    to: { name: "matricula.reservas" },
    icon: ClipboardDocumentCheckIcon,
  },
];

const isActive = (to) => route.name === to.name;
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-matrículas', 'ver-matrículas']">
    <div class="space-y-3 bg-slate-100 px-3 py-2.5 transition-colors duration-300 dark:bg-slate-800">
      <section class="border border-slate-200 bg-white px-4 py-3 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Gestion institucional
            </p>
            <h1 class="mt-1 text-[1.2rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
              Modulo de matriculas
            </h1>
            <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">
              Registro, control por grupos y seguimiento de reservas en una sola vista operativa.
            </p>
          </div>

          <nav class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            <button
              v-for="link in navLinks"
              :key="link.text"
              type="button"
              @click="router.push(link.to)"
              class="min-w-[210px] border px-3 py-2 text-left transition-colors duration-200"
              :class="
                isActive(link.to)
                  ? 'border-cetpro bg-cetpro/5 text-cetpro dark:border-cetpro-light dark:bg-cetpro-light/10 dark:text-cetpro-light'
                  : 'border-slate-200 bg-white text-slate-700 hover:border-cetpro/40 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cetpro-light/50 dark:hover:bg-slate-800'
              "
            >
              <div class="flex items-start gap-2.5">
                <div
                  class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center border"
                  :class="
                    isActive(link.to)
                      ? 'border-cetpro/20 bg-cetpro/10 dark:border-cetpro-light/20 dark:bg-cetpro-light/10'
                      : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800'
                  "
                >
                  <component :is="link.icon" class="h-4 w-4" />
                </div>
                <div class="min-w-0">
                  <p class="text-[13px] font-semibold leading-tight">{{ link.text }}</p>
                  <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                    {{ link.description }}
                  </p>
                </div>
              </div>
            </button>
          </nav>
        </div>
      </section>

      <section class="border border-slate-200 bg-white shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <router-view />
      </section>
    </div>
  </AuthorizationFallback>
</template>
