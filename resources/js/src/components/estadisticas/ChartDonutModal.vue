<script setup>
import { computed } from "vue";

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: "Gráfico" },
  subtitle: { type: String, default: "" },
  series: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close"]);

const normalized = computed(() => {
  return (props.series || [])
    .map((s) => ({
      label: s.label || "",
      value: Number(s.value || 0),
      color: s.color || "#0ea5e9",
    }))
    .filter((s) => s.value > 0);
});

const total = computed(() => normalized.value.reduce((a, c) => a + c.value, 0));

const donutStyle = computed(() => {
  if (!normalized.value.length || total.value <= 0) {
    return {
      background: "conic-gradient(#e2e8f0 0 360deg)",
    };
  }

  let start = 0;
  const parts = normalized.value.map((s) => {
    const angle = (s.value / total.value) * 360;
    const from = start;
    const to = start + angle;
    start = to;
    return `${s.color} ${from}deg ${to}deg`;
  });

  return {
    background: `conic-gradient(${parts.join(", ")})`,
  };
});

const percent = (value) => {
  if (!total.value) return "0.0";
  return ((Number(value) / total.value) * 100).toFixed(1);
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-slate-200">
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
        <div>
          <h3 class="text-lg font-extrabold text-slate-800">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-slate-500">{{ subtitle }}</p>
        </div>
        <button
          class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold"
          @click="emit('close')"
        >
          Cerrar
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div class="flex items-center justify-center">
          <div class="relative h-64 w-64 rounded-full shadow-inner" :style="donutStyle">
            <div
              class="absolute inset-[22%] rounded-full bg-white border border-slate-200 flex flex-col items-center justify-center"
            >
              <span class="text-xs uppercase tracking-wider text-slate-500">Total</span>
              <span class="text-2xl font-black text-slate-800">{{ total }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div
            v-for="item in normalized"
            :key="item.label"
            class="rounded-xl border border-slate-200 p-3 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: item.color }"></span>
              <span class="font-semibold text-slate-700">{{ item.label }}</span>
            </div>
            <div class="text-right">
              <div class="font-extrabold text-slate-800">{{ item.value }}</div>
              <div class="text-xs text-slate-500">{{ percent(item.value) }}%</div>
            </div>
          </div>
          <div v-if="!normalized.length" class="text-sm text-slate-500">
            No hay datos para graficar.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
