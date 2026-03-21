<script setup>
import { computed, ref } from 'vue';
import { generateReporteEspecialidadEstudiante } from '../../pdf/ReporteEspecialidadEstudiante';
const saving = ref(false);

const form = ref({
  nro_documento: '',
  fecha_nacimiento: '',
});

const formErrors = ref({});
const error = ref('');
const resultado = ref(null);
const consultaRealizada = ref(false);

const nombreEstudiante = computed(
  () => resultado.value?.estudiante?.nombre_completo || ''
);

const exportandoPdf = ref(false);

const consultarNotas = async () => {
  consultaRealizada.value = true;
  error.value = '';
  formErrors.value = {};
  resultado.value = null;

  if (!form.value.nro_documento.trim()) {
    formErrors.value.nro_documento = 'Ingresa el DNI.';
  }

  if (!form.value.fecha_nacimiento) {
    formErrors.value.fecha_nacimiento = 'Ingresa la fecha de nacimiento.';
  }

  if (Object.keys(formErrors.value).length > 0) {
    return;
  }

  try {
    saving.value = true;

    const httpResponse = await fetch('/api/consulta-notas-publica', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nro_documento: form.value.nro_documento.trim(),
        fecha_nacimiento: form.value.fecha_nacimiento,
      }),
    });

    const rawResponse = await httpResponse.text();
    const response = rawResponse ? JSON.parse(rawResponse) : {};

    if (response?.success) {
      resultado.value = response.data;
      return;
    }

    error.value = response?.message || (!httpResponse.ok ? 'La consulta pública no respondió correctamente.' : 'No se encontraron notas.');
  } catch (err) {
    error.value = 'No se pudo completar la consulta.';
  } finally {
    saving.value = false;
  }
};

const formatoFecha = (fecha) => {
  if (!fecha) return 'No disponible';
  const parsed = new Date(fecha);
  if (Number.isNaN(parsed.getTime())) return fecha;
  return parsed.toLocaleDateString('es-PE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const notaClass = (nota) => {
  if (nota === null || nota === undefined) return 'bg-slate-100 text-slate-500';
  if (nota >= 13) return 'bg-cetpro/10 text-cetpro';
  return 'bg-slate-200 text-slate-700';
};

const exportarEspecialidadPDF = async (especialidad) => {
  if (!resultado.value?.estudiante || !especialidad || exportandoPdf.value) return;

  try {
    exportandoPdf.value = true;
    await generateReporteEspecialidadEstudiante(resultado.value.estudiante, especialidad, {
      cetproPath: "/cetprodata-publica",
    });
  } finally {
    exportandoPdf.value = false;
  }
};
</script>

<template>
  <section class="min-h-screen bg-slate-100 text-slate-800">
    <div class="mx-auto max-w-[1320px] px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
      <header class="border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <img src="/img/insignia.png" alt="CETPRO Puno" class="h-12 w-12 object-contain" />
            <div>
              <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">CETPRO Puno</p>
              <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                Consulta pública de notas
              </h1>
            </div>
          </div>

          <router-link
            to="/"
            class="inline-flex items-center justify-center border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          >
            Volver
          </router-link>
        </div>
      </header>

      <div class="grid gap-6 pt-6 lg:grid-cols-[360px_minmax(0,1fr)] lg:items-start">
        <aside class="border border-slate-200 bg-white p-6 shadow-sm">
          <div class="space-y-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Verificación</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Ingresar datos</h2>
            <p class="text-sm leading-6 text-slate-600">
              La consulta requiere el número de documento y la fecha de nacimiento del estudiante.
            </p>
          </div>

          <form class="mt-8 space-y-5" @submit.prevent="consultarNotas">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">DNI</label>
              <input
                v-model="form.nro_documento"
                type="text"
                maxlength="15"
                class="w-full border border-slate-300 bg-slate-50 px-4 py-3 text-[15px] outline-none transition focus:border-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-200"
                placeholder="Ingrese DNI"
              />
              <p v-if="formErrors.nro_documento" class="text-sm text-red-600">{{ formErrors.nro_documento }}</p>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Fecha de nacimiento</label>
              <input
                v-model="form.fecha_nacimiento"
                type="date"
                lang="es-PE"
                class="w-full border border-slate-300 bg-slate-50 px-4 py-3 text-[15px] outline-none transition focus:border-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-200"
              />
              <p v-if="formErrors.fecha_nacimiento" class="text-sm text-red-600">{{ formErrors.fecha_nacimiento }}</p>
              <p v-else class="text-xs text-slate-500">DD = dia, MM = mes, AAAA = anio</p>
            </div>

            <button
              type="submit"
              :disabled="saving"
              class="w-full border border-cetpro bg-cetpro px-4 py-3 text-sm font-semibold text-white transition hover:bg-cetpro-dark disabled:cursor-not-allowed disabled:opacity-70"
            >
              {{ saving ? 'Consultando' : 'Consultar notas' }}
            </button>
          </form>

          <div class="mt-8 border-t border-slate-200 pt-5">
            <p class="text-xs leading-6 text-slate-500">
              Esta vista solo muestra notas registradas y no permite acceder a módulos internos, usuario ni trámites administrativos.
            </p>
          </div>
        </aside>

        <main class="min-w-0">
          <div v-if="!consultaRealizada" class="border border-slate-200 bg-white px-8 py-16 shadow-sm sm:px-12">
            <div class="max-w-2xl space-y-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Información</p>
              <h3 class="text-3xl font-semibold tracking-tight text-slate-900">
                Consulta de calificaciones
              </h3>
              <p class="text-[15px] leading-7 text-slate-600">
                Ingrese DNI y fecha de nacimiento para ver los registros disponibles.
              </p>
            </div>
          </div>

          <div v-else-if="error" class="border border-red-200 bg-white px-8 py-16 shadow-sm">
            <div class="max-w-2xl space-y-4">
              <div class="h-px w-16 bg-red-300"></div>
              <h3 class="text-2xl font-semibold tracking-tight text-slate-900">No se encontraron resultados</h3>
              <p class="text-[15px] leading-7 text-slate-500">{{ error }}</p>
            </div>
          </div>

          <div v-else-if="resultado" class="space-y-8">
            <section class="border border-slate-200 bg-white px-8 py-8 shadow-sm">
              <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="space-y-3">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Estudiante</p>
                  <h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">{{ nombreEstudiante }}</h2>
                  <p class="text-sm text-slate-500">DNI {{ resultado.estudiante.nro_documento }}</p>
                </div>

                <div class="grid gap-px overflow-hidden border border-slate-200 bg-slate-200 sm:grid-cols-2">
                  <div class="bg-slate-50 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Especialidades</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ resultado.historial_academico.length }}</p>
                  </div>
                  <div class="bg-slate-50 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Consulta</p>
                    <p class="mt-2 text-sm font-medium text-slate-700">Notas académicas registradas</p>
                  </div>
                </div>
              </div>
            </section>

            <section
              v-for="especialidad in resultado.historial_academico"
              :key="especialidad.id"
              class="border border-slate-200 bg-white shadow-sm"
            >
              <div class="border-b border-slate-200 px-8 py-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                  <div class="space-y-2">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-cetpro">
                      {{ especialidad.programa.nombre }}
                    </p>
                    <h3 class="text-2xl font-semibold tracking-tight text-slate-900">{{ especialidad.nombre }}</h3>
                  </div>
                  <div class="flex items-center gap-3">
                    <p class="text-sm text-slate-600">{{ especialidad.total_modulos }} módulo(s)</p>
                    <button
                      type="button"
                      class="border border-cetpro bg-white px-4 py-2 text-sm font-medium text-cetpro transition hover:bg-cetpro hover:text-white"
                      @click="exportarEspecialidadPDF(especialidad)"
                    >
                      {{ exportandoPdf ? 'Generando PDF...' : 'Descargar PDF' }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="space-y-8 px-8 py-8">
                <div v-for="periodo in especialidad.periodos" :key="periodo.id" class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="h-px w-12 bg-cetpro/30"></div>
                    <p class="text-sm font-semibold text-cetpro">{{ periodo.nombre }}</p>
                  </div>

                  <div class="overflow-x-auto border border-slate-200">
                    <table class="min-w-[900px] divide-y divide-slate-200 text-sm">
                      <thead class="bg-cetpro/[0.06]">
                        <tr class="text-left">
                          <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Modulo</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Seccion</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Turno</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Inicio</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Fin</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">U1</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">U2</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">EF</th>
                          <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-cetpro">Prom.</th>
                        </tr>
                      </thead>

                      <tbody class="divide-y divide-slate-200 bg-white">
                        <tr
                          v-for="modulo in periodo.modulos"
                          :key="modulo.matricula_id"
                          class="align-middle"
                        >
                          <td class="px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cetpro/80">
                              Modulo {{ modulo.modulo.numero }}
                            </p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                              {{ modulo.modulo.descripcion }}
                            </p>
                          </td>

                          <td class="px-4 py-4 text-center text-sm text-slate-700">
                            {{ modulo.grupo.seccion }}
                          </td>

                          <td class="px-4 py-4 text-center text-sm text-slate-700">
                            {{ modulo.grupo.turno === 'M' ? 'Manana' : modulo.grupo.turno === 'T' ? 'Tarde' : modulo.grupo.turno === 'N' ? 'Noche' : modulo.grupo.turno }}
                          </td>

                          <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                            {{ formatoFecha(modulo.grupo.fecha_inicio) }}
                          </td>

                          <td class="px-4 py-4 text-center text-sm text-slate-600 whitespace-nowrap">
                            {{ formatoFecha(modulo.grupo.fecha_fin) }}
                          </td>

                          <td
                            v-for="unidad in modulo.notas_unidades"
                            :key="`${modulo.matricula_id}-${unidad.numero_unidad}`"
                            class="px-4 py-4 text-center"
                          >
                            <div
                              class="inline-flex min-w-[52px] items-center justify-center px-3 py-2 text-sm font-semibold"
                              :class="notaClass(unidad.nota)"
                            >
                              {{ unidad.nota ?? '--' }}
                            </div>
                          </td>

                          <td class="px-4 py-4 text-center">
                            <div class="inline-flex min-w-[64px] items-center justify-center bg-cetpro/10 px-3 py-2 text-sm font-bold text-cetpro">
                              {{ modulo.promedio_notas ?? '--' }}
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </main>
      </div>
    </div>
  </section>
</template>
