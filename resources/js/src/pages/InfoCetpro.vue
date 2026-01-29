<script setup>
import { computed } from "vue";
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import Button from "../../components/ui/Button.vue";
import useSlider from "../../composables/useSlider";
import useCetproStore from "../../store/Cetpro/useCetproStore";
import CetproForm from "../../components/page/CetproForm.vue";

const cetproStore = useCetproStore();

// cargar CETPRO (solo uno)
if (!cetproStore.cetpro) {
  await cetproStore.loadCetpro();
}

const { slider, sliderData, showSlider, hideSlider } = useSlider("cetpro-form");

const cetpro = computed(() => cetproStore.cetpro);
const hasCetpro = computed(() => !!cetpro.value?.id);
</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-cetpro', 'ver-cetpro']">
    <div class="p-6 space-y-4">

      <h2 class="text-2xl font-bold text-cetpro dark:text-cetpro-light">
        Información del CETPRO
      </h2>

      <!-- CARD -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 space-y-3">

        <!-- CUANDO EXISTE CETPRO -->
        <template v-if="hasCetpro">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

            <div><b>CETPRO:</b> {{ cetpro.cetpro }}</div>
            <div><b>Tipo de gestión:</b> {{ cetpro.tipo_gestion }}</div>

            <div><b>R.D. Autorización:</b> {{ cetpro.rd_autorizacion }}</div>
            <div><b>R.D. Conversión:</b> {{ cetpro.rd_conversion || "-" }}</div>

            <div><b>UGEL:</b> {{ cetpro.ugel }}</div>
            <div><b>DRE:</b> {{ cetpro.dre }}</div>

            <div><b>Región:</b> {{ cetpro.region }}</div>
            <div><b>Provincia:</b> {{ cetpro.provincia }}</div>
            <div><b>Distrito:</b> {{ cetpro.distrito }}</div>

            <div class="md:col-span-2">
              <b>Dirección:</b> {{ cetpro.direccion }} {{ cetpro.numero }}
            </div>
          </div>

          <div class="pt-4">
            <Button
              title="Editar información"
              @click="showSlider(true, cetpro)"
            />
          </div>
        </template>

        <!-- CUANDO NO EXISTE CETPRO -->
        <template v-else>
          <p class="text-gray-500 italic">
            No se ha registrado información del CETPRO.
          </p>

          <Button
            title="Agregar información del CETPRO"
            @click="showSlider(true, null)"
          />
        </template>

      </div>

      <!-- FORMULARIO (SLIDER / MODAL) -->
      <CetproForm
        :show="slider"
        :cetpro="sliderData"
        @hide="hideSlider"
      />
    </div>
  </AuthorizationFallback>
</template>