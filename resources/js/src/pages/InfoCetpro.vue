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
  { label: "Tipo de gestión", value: cetpro.value?.tipo_gestion, icon: ClipboardDocumentCheckIcon },
  { label: "Año", value: cetpro.value?.anio, icon: CalendarDaysIcon },
  { label: "R.D. autorización", value: cetpro.value?.rd_autorizacion, icon: IdentificationIcon },
  { label: "R.D. conversión", value: cetpro.value?.rd_conversion, icon: AcademicCapIcon },
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
    <div class="bg-slate-100 p-3 transition-colors duration-300 dark:bg-slate-800">
      <section class="overflow-hidden border border-slate-200 bg-white shadow-sm transition-colors duration-300 dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
          <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
            Ajustes institucionales
          </p>
          <h2 class="mt-1 text-[1.15rem] font-semibold tracking-tight text-slate-800 dark:text-slate-100">
            Datos del Centro de Educación Técnico - Productiva
          </h2>
          <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">
            Información general del CETPRO para documentos y reportes oficiales.
          </p>
        </div>

        <div class="p-3 sm:p-4">
          <template v-if="hasCetpro">
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
              <article
                v-for="item in detailItems"
                :key="item.label"
                class="border border-slate-200 bg-slate-50 px-3 py-2.5 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-800/90"
              >
                <div class="flex items-start gap-3">
                  <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center border border-slate-200 bg-white text-slate-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300">
                    <component :is="item.icon" class="h-4 w-4" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                      {{ item.label }}
                    </p>
                    <p class="mt-1 break-words text-[13px] font-medium text-slate-800 dark:text-slate-100">
                      {{ item.value || "No registrado" }}
                    </p>
                  </div>
                </div>
              </article>
            </div>

            <div class="mt-4 flex flex-col justify-end gap-3 sm:flex-row">
              <Button title="Editar información" @click="showSlider(true, cetpro)" />
            </div>
          </template>

          <template v-else>
            <div class="border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center dark:border-slate-600 dark:bg-slate-800/60">
              <div class="mx-auto flex h-10 w-10 items-center justify-center border border-slate-200 bg-white text-slate-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300">
                <BuildingOffice2Icon class="h-5 w-5" />
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
      </section>

      <CetproForm :show="slider" :cetpro="sliderData" @hide="hideSlider" />
    </div>
  </AuthorizationFallback>
</template>
