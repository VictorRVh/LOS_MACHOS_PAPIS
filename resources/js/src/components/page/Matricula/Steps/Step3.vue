<script setup>
import { computed } from "vue";
import { CreditCardIcon, InformationCircleIcon } from "@heroicons/vue/24/outline";
import FormInput from "../../../ui/FormInput.vue";
import FormLabelError from "../../../ui/FormLabelError.vue";
import BaseSelectGrupo from "../../../ui/BaseSelectGrupo.vue";

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

const resumenItems = computed(() => [
  {
    label: "Estudiante",
    value: `${formData.value.nombre || ""} ${formData.value.apellido_paterno || ""} ${formData.value.apellido_materno || ""}`.trim(),
  },
  {
    label: "Documento",
    value: `${formData.value.tipo_documento || ""} - ${formData.value.nro_documento || "No registrado"}`,
  },
  {
    label: "Grupo",
    value: props.nameGrupo || "No seleccionado",
  },
  {
    label: "Condicion",
    value: formData.value.condicion || "No definida",
  },
  {
    label: "Recibo",
    value: formData.value.nro_recibo || "No registrado",
  },
  {
    label: "Aporte",
    value: formData.value.aporte ? `S/. ${formData.value.aporte}` : "No registrado",
  },
]);
</script>

<template>
  <div class="space-y-4">
    <section class="border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-800/60">
      <div class="flex items-start gap-2.5">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center border border-cetpro/20 bg-cetpro/10 text-cetpro dark:border-cetpro-light/20 dark:bg-cetpro-light/10 dark:text-cetpro-light">
          <CreditCardIcon class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
            Cierre del proceso
          </p>
          <h4 class="mt-1 text-[15px] font-semibold text-slate-900 dark:text-slate-100">
            Pago y confirmacion de la matricula
          </h4>
          <p class="mt-1 text-[13px] leading-5 text-slate-500 dark:text-slate-400">
            Registre la condicion economica del estudiante y revise el resumen final antes de confirmar.
          </p>
        </div>
      </div>

      <div class="mt-4 grid gap-3 md:grid-cols-3">
        <FormLabelError label="Condicion">
          <BaseSelectGrupo v-model="formData.condicion" :options="condiciones" label="label" value-prop="value" :clearable="false" />
        </FormLabelError>
        <FormInput v-model="formData.nro_recibo" label="Nro. recibo / voucher" />
        <FormInput v-model="formData.aporte" label="Aporte S/." type="number" step="0.01" />
      </div>
    </section>

    <section v-if="!edit" class="border border-slate-200 bg-white px-4 py-4 dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-start gap-2.5">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center border border-cetpro/20 bg-cetpro/10 text-cetpro dark:border-cetpro-light/20 dark:bg-cetpro-light/10 dark:text-cetpro-light">
          <InformationCircleIcon class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
            Revision final
          </p>
          <h4 class="mt-1 text-[15px] font-semibold text-slate-900 dark:text-slate-100">
            Resumen operativo del registro
          </h4>
          <p class="mt-1 text-[13px] leading-5 text-slate-500 dark:text-slate-400">
            Confirme que los datos principales del estudiante, grupo y pago sean correctos antes de registrar la matricula.
          </p>
        </div>
      </div>

      <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="item in resumenItems"
          :key="item.label"
          class="border border-slate-200 border-l-[3px] border-l-cetpro bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:border-l-cetpro-light dark:bg-slate-800/70"
        >
          <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            {{ item.label }}
          </p>
          <p class="mt-1 text-[13px] font-medium text-slate-800 dark:text-slate-100">
            {{ item.value || "No registrado" }}
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
