<script setup>
import { computed } from "vue";
import { CreditCardIcon, InformationCircleIcon } from "@heroicons/vue/24/outline";
import FormInput from "../../../ui/FormInput.vue";
import FormLabelError from "../../../ui/FormLabelError.vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

const props = defineProps({
  modelValue: { type: Object, required: true },
  nameGrupo: { type: String },
  edit: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const condiciones = [
  { label: "G | Gratuito", value: "Gratuito" },
  { label: "P | Pagante", value: "Pagante" },
  { label: "B | Becado", value: "Becado" },
  { label: "S | Semibeca", value: "Semibeca" },
];
</script>

<template>
  <div class="space-y-3">
    <section class="border border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-700 dark:bg-slate-800/60">
      <div class="flex items-center gap-2">
        <CreditCardIcon class="h-5 w-5 text-cetpro dark:text-cetpro-light" />
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
            Registro economico
          </p>
          <h3 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">
            Datos de pago y condicion de matricula
          </h3>
        </div>
      </div>

      <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
        <FormLabelError label="Condicion">
          <v-select v-model="formData.condicion" :options="condiciones" label="label" :reduce="(option) => option.value" :clearable="false" />
        </FormLabelError>
        <FormInput v-model="formData.nro_recibo" label="Nro. recibo / voucher" />
        <FormInput v-model="formData.aporte" label="Aporte S/." type="number" step="0.01" />
      </div>
    </section>

    <section
      v-if="!edit"
      class="border border-slate-200 bg-white px-3 py-3 dark:border-slate-700 dark:bg-slate-900"
    >
      <div class="flex items-start gap-2.5">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center border border-cetpro/20 bg-cetpro/10 text-cetpro dark:border-cetpro-light/20 dark:bg-cetpro-light/10 dark:text-cetpro-light">
          <InformationCircleIcon class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
            Resumen previo
          </p>
          <h3 class="mt-1 text-[15px] font-semibold text-slate-900 dark:text-slate-100">
            Verificacion final antes de registrar
          </h3>
          <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">
            Revise los datos principales del estudiante y del grupo antes de confirmar la matricula.
          </p>
        </div>
      </div>

      <div class="mt-3 grid gap-3 md:grid-cols-3">
        <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-slate-50 px-3 py-2 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-800/70">
          <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            Estudiante
          </p>
          <p class="mt-1 text-[13px] font-medium text-slate-800 dark:text-slate-100">
            {{ formData.nombre }} {{ formData.apellido_paterno }} {{ formData.apellido_materno }}
          </p>
        </div>

        <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-slate-50 px-3 py-2 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-800/70">
          <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            Documento
          </p>
          <p class="mt-1 text-[13px] font-medium text-slate-800 dark:text-slate-100">
            {{ formData.tipo_documento }} - {{ formData.nro_documento || "No registrado" }}
          </p>
        </div>

        <div class="border border-slate-200 border-l-[3px] border-l-cetpro bg-slate-50 px-3 py-2 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-800/70">
          <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            Grupo
          </p>
          <p class="mt-1 text-[13px] font-medium text-slate-800 dark:text-slate-100">
            {{ props.nameGrupo || "No seleccionado" }}
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
