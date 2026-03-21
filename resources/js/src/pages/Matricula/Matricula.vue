<script setup>
import { UserPlusIcon, UserGroupIcon, ClipboardDocumentCheckIcon } from "@heroicons/vue/24/outline";
import { useRoute, useRouter } from "vue-router";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";

const route = useRoute();
const router = useRouter();

const navLinks = [
  { text: "Matricular estudiante", to: { name: "matricula.registrar" }, icon: UserPlusIcon },
  { text: "Lista por grupos", to: { name: "matricula.grupos" }, icon: UserGroupIcon },
  { text: "Estudiantes con reserva", to: { name: "matricula.reservas" }, icon: ClipboardDocumentCheckIcon },
];

const isActive = (to) => route.name === to.name;
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-matrículas', 'ver-matrículas']">
    <div class="space-y-2 bg-slate-100 px-3 py-2 transition-colors duration-300 dark:bg-slate-800">
      <section class="border border-slate-200 bg-white px-4 py-2 shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-2 xl:flex-row xl:items-center xl:justify-between">
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
              Gestion institucional
            </p>
            <div class="mt-1 flex flex-col gap-1 xl:flex-row xl:items-center xl:gap-3">
              <h1 class="text-[1rem] font-semibold tracking-tight text-cetpro dark:text-cetpro-light">
                Modulo de matriculas
              </h1>
              <p class="text-[12px] text-slate-500 dark:text-slate-400">
                Registro, grupos y reservas.
              </p>
            </div>
          </div>

          <nav class="overflow-x-auto">
            <div class="flex min-w-max items-center gap-1.5 border-b border-slate-200 dark:border-slate-700">
              <button
                v-for="link in navLinks"
                :key="link.text"
                type="button"
                @click="router.push(link.to)"
                class="flex items-center gap-2 border-b-[2px] px-2.5 py-1.5 text-[12px] font-medium whitespace-nowrap transition-colors duration-200"
                :class="
                  isActive(link.to)
                    ? 'border-cetpro text-cetpro dark:border-cetpro-light dark:text-cetpro-light'
                    : 'border-transparent text-slate-600 hover:border-cetpro/40 hover:text-cetpro dark:text-slate-300 dark:hover:border-cetpro-light/40 dark:hover:text-cetpro-light'
                "
              >
                <component :is="link.icon" class="h-4 w-4" />
                <span>{{ link.text }}</span>
              </button>
            </div>
          </nav>
        </div>
      </section>

      <section class="border border-slate-200 bg-white shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <router-view />
      </section>
    </div>
  </AuthorizationFallback>
</template>
