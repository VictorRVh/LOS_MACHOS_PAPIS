<script setup>
import { computed, onMounted } from "vue";
import AuthorizationFallback from "../components/page/AuthorizationFallback.vue";
import Button from "../components/ui/Button.vue";
import useSlider from "../composables/useSlider";
import useCetproStore from "../store/useCetproStore";
import CetproForm from "../components/page/infoCetproSlider.vue";

const cetproStore = useCetproStore();

onMounted(async () => {
  await cetproStore.loadCetpro();
});

const { slider, sliderData, showSlider, hideSlider } = useSlider("cetpro-form");

const cetpro = computed(() => cetproStore.cetpro);
const hasCetpro = computed(() => !!cetpro.value?.id);
</script>

<template>
  <AuthorizationFallback :permissions="['ver-informacion-cetpro']">

    <div class="p-6">

      <!-- CARD -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow ">

        <!-- HEADER -->
        <div
          class="px-6 py-4 -b bg-gray-50 dark:bg-gray-900
                 text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"
        >
          ⚙ DATOS DEL CENTRO DE EDUCACIÓN TÉCNICO – PRODUCTIVA
        </div>

        <!-- BODY -->
        <div class="p-6">

          <!-- SI EXISTE -->
          <template v-if="hasCetpro">

            <div class="space-y-4 text-sm">

              <!-- FILA -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">CETPRO</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.cetpro }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Tipo de Gestión</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.tipo_gestion }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">R.D. Autorización</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.rd_autorizacion }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">R.D. Conversión</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.rd_conversion || "—" }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">UGEL</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.ugel }}
                  </div>
                </div>
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">DRE</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.dre }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Región</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.region }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Provincia</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.provincia }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Distrito</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.distrito }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Lugar</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.lugar }}
                  </div>
                </div>

                <div class="flex">
                  <div class="w-48 text-right pr-4 font-medium text-gray-600">Dirección</div>
                  <div class="flex-1  rounded px-3 py-2 bg-gray-50">
                    {{ cetpro.direccion }} {{ cetpro.numero }}
                  </div>
                </div>
              </div>

            </div>

            <!-- BOTÓN -->
            <div class="flex justify-end pt-6 -t mt-6">
              <Button
                title="Editar información"
                @click="showSlider(true, cetpro)"
              />
            </div>

          </template>

          <!-- SI NO EXISTE -->
          <template v-else>
            <div class="text-center py-12 text-gray-500 italic">
              No se ha registrado información del CETPRO.
            </div>

            <div class="flex justify-center">
              <Button
                title="Registrar información del CETPRO"
                @click="showSlider(true, null)"
              />
            </div>
          </template>

        </div>
      </div>

      <!-- SLIDER -->
      <CetproForm
        :show="slider"
        :cetpro="sliderData"
        @hide="hideSlider"
      />
    </div>

  </AuthorizationFallback>
</template>
