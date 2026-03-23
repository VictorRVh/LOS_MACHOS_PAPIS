<script setup>
import { computed } from 'vue'
import useUserStore from '../../store/useUserStore'

const props = defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
})

const userStore = useUserStore()

const isAuthorized = computed(() => {
    if (props.permissions.length === 0) return true

    const userPermissions = userStore.user?.permissions || []
    return userPermissions.some(p => props.permissions.includes(p?.name))
})

const displayName = computed(() => {
    const user = userStore.user || {}
    return [user.name, user.apellido_paterno, user.apellido_materno].filter(Boolean).join(' ') || 'Docente'
})

const primaryRole = computed(() => {
    return userStore.user?.roles?.[0]?.name || 'docente'
})
</script>

<template>
    <template v-if="isAuthorized">
        <slot></slot>
    </template>

    <div v-else class="min-h-[calc(100vh-8rem)] bg-slate-100 px-4 py-5 dark:bg-slate-900 sm:px-5 lg:px-6">
        <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-6xl items-center">
            <section class="relative w-full overflow-hidden border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="absolute inset-y-0 left-0 hidden w-[38%] border-r border-white/10 bg-[linear-gradient(180deg,_#0b6f99_0%,_#0c6085_100%)] lg:block">
                    <img
                        src="/img/computacion-en-la-nube.png"
                        alt=""
                        class="absolute inset-0 h-full w-full scale-[1.04] object-contain object-center opacity-[0.08]"
                    />
                    <div class="relative z-10 flex h-full flex-col justify-between px-8 py-8 text-white">
                        <div class="space-y-4">
                            <div class="flex h-16 w-16 items-center justify-center border border-white/60 bg-white/80 p-2 shadow-[0_10px_24px_rgba(15,23,42,0.10)]">
                                <img src="/img/cetproLOGOO.png" alt="Logo CETPRO Puno" class="h-full w-full object-contain" />
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cyan-100/80">
                                    CETPRO Puno
                                </p>
                                <h2 class="max-w-[10ch] text-[2rem] font-extrabold uppercase leading-none tracking-[-0.04em]">
                                    Gestion Academica
                                </h2>
                            </div>
                        </div>

                        <p class="max-w-[22rem] text-[13px] leading-6 text-slate-100/92">
                            Entorno institucional para seguimiento academico, planificacion docente y acceso ordenado a los modulos de trabajo.
                        </p>
                    </div>
                </div>

                <div class="grid min-h-[30rem] grid-cols-1 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.35fr)]">
                    <div class="hidden lg:block"></div>

                    <div class="relative flex items-center px-5 py-6 sm:px-8 lg:px-10 xl:px-12">
                        <div class="w-full max-w-3xl space-y-6">
                            <div class="space-y-3">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cetpro/80">
                                    Panel docente
                                </p>
                                <div class="space-y-2">
                                    <h1 class="max-w-[18ch] text-[2rem] font-semibold leading-[1.02] tracking-tight text-slate-900 dark:text-white sm:text-[2.4rem]">
                                        Bienvenido al espacio de trabajo docente
                                    </h1>
                                    <p class="max-w-2xl text-[14px] leading-7 text-slate-600 dark:text-slate-300">
                                        Revisa tus modulos asignados, organiza tus actividades y accede a las herramientas del sistema desde una vista clara y consistente.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                                <div class="border border-slate-200 bg-slate-50/85 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/60">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">
                                        Usuario activo
                                    </p>
                                    <p class="mt-2 text-[1.2rem] font-semibold text-slate-900 dark:text-white">
                                        {{ displayName }}
                                    </p>
                                    <p class="mt-1 text-[12px] uppercase tracking-[0.18em] text-cetpro/80">
                                        {{ primaryRole }}
                                    </p>
                                </div>

                                <div class="border border-slate-200 bg-white px-4 py-4 dark:border-slate-700 dark:bg-slate-800">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">
                                        Inicio recomendado
                                    </p>
                                    <p class="mt-2 text-[14px] leading-6 text-slate-700 dark:text-slate-200">
                                        Selecciona un modulo del menu lateral para comenzar tu jornada de trabajo.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="h-px w-16 bg-cetpro"></div>
                                <p class="text-[11px] font-medium uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                                    Entorno institucional activo
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
