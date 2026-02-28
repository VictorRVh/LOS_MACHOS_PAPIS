<script setup>
import { computed, onMounted } from "vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";
import Button from "../components/ui/Button.vue";
import useSlider from "../composables/useSlider";
import useCetproStore from "../store/useCetproStore";
import CetproForm from "../components/page/infoCetproSlider.vue";
import {
  AcademicCapIcon,
  BuildingOffice2Icon,
  BuildingOfficeIcon,
  CalendarDaysIcon,
  IdentificationIcon,
  MapPinIcon,
  MapIcon,
  HomeIcon,
  HashtagIcon,
  ClipboardDocumentCheckIcon,
  UserCircleIcon,
} from "@heroicons/vue/24/outline";

const cetproStore = useCetproStore();

onMounted(async () => {
  await cetproStore.loadCetpro();
});

const { slider, sliderData, showSlider, hideSlider } = useSlider("cetpro-form");

const cetpro = computed(() => cetproStore.cetpro);
const hasCetpro = computed(() => !!cetpro.value?.id);

const detailItems = computed(() => [
  { label: "CETPRO", value: cetpro.value?.cetpro, icon: BuildingOffice2Icon },
  { label: "Director(a)", value: cetpro.value?.director, icon: UserCircleIcon },
  { label: "Tipo de Gestión", value: cetpro.value?.tipo_gestion, icon: ClipboardDocumentCheckIcon },
  { label: "Año", value: cetpro.value?.anio, icon: CalendarDaysIcon },
  { label: "R.D. Autorización", value: cetpro.value?.rd_autorizacion, icon: IdentificationIcon },
  { label: "R.D. Conversión", value: cetpro.value?.rd_conversion, icon: AcademicCapIcon },
  { label: "UGEL", value: cetpro.value?.ugel, icon: BuildingOfficeIcon },
  { label: "DRE", value: cetpro.value?.dre, icon: BuildingOfficeIcon },
  { label: "Región", value: cetpro.value?.region, icon: MapIcon },
  { label: "Provincia", value: cetpro.value?.provincia, icon: MapPinIcon },
  { label: "Distrito", value: cetpro.value?.distrito, icon: MapPinIcon },
  { label: "Lugar", value: cetpro.value?.lugar, icon: HomeIcon },
  {
    label: "Dirección",
    value: [cetpro.value?.direccion, cetpro.value?.numero].filter(Boolean).join(" "),
    icon: HashtagIcon,
  },
]);
</script>

<template>
  <AuthorizationFallback :permissions="['ver-información-cetpro']">
    <div class="p-4 sm:p-6">
      <div
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
      >
        <div
          class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white p-5 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800"
        >
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
              Ajustes Institucionales
            </p>
            <h2 class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">
              Datos del Centro de Educación Técnico - Productiva
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Información general del CETPRO para documentos y reportes oficiales.
            </p>
          </div>
        </div>

        <div class="p-4 sm:p-6">
          <template v-if="hasCetpro">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
              <article
                v-for="item in detailItems"
                :key="item.label"
                class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 transition-colors hover:bg-slate-100/80 dark:border-slate-700 dark:bg-slate-900/40 dark:hover:bg-slate-900/70"
              >
                <div class="flex items-start gap-3">
                  <div
                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600"
                  >
                    <component :is="item.icon" class="h-5 w-5" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                      {{ item.label }}
                    </p>
                    <p class="mt-1 break-words text-sm font-medium text-slate-800 dark:text-slate-100">
                      {{ item.value || "No registrado" }}
                    </p>
                  </div>
                </div>
              </article>
            </div>

            <div class="mt-6 flex flex-col justify-end gap-3 sm:flex-row">
              <Button title="Editar información" @click="showSlider(true, cetpro)" />
            </div>
          </template>

          <template v-else>
            <div
              class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-10 text-center dark:border-slate-600 dark:bg-slate-900/30"
            >
              <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600"
              >
                <BuildingOffice2Icon class="h-6 w-6" />
              </div>
              <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-300">
                No se ha registrado información del CETPRO.
              </p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Agrega los datos institucionales para completar los formatos oficiales.
              </p>
              <div class="mt-5">
                <Button title="Registrar información del CETPRO" @click="showSlider(true, null)" />
              </div>
            </div>
          </template>
        </div>
      </div>

      <CetproForm :show="slider" :cetpro="sliderData" @hide="hideSlider" />
    </div>
  </AuthorizationFallback>
</template>
