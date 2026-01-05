<template>
  <!-- Fondo oscuro -->
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="rounded-xl shadow-2xl w-[500px] max-w-xl animate-fadeIn">

      <UserInfoCard
        title="Competencias del módulo"
        subtitle="Listado"
        :info="infoList"
        @close="emitClose"
      />

    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import UserInfoCard from "@/components/table/ViewInfo.vue";

const props = defineProps({
  show: { type: Boolean, default: false },
  modulo: { type: Object, default: null }
});

const emit = defineEmits(["close"]);
const emitClose = () => emit("close");

/* =========================
   MAPEAR COMPETENCIAS
========================= */
const infoList = computed(() => {
  if (!props.modulo?.competencias?.length) {
    return [
      { label: "Sin competencias", value: "Este módulo no tiene competencias registradas" }
    ];
  }

  return props.modulo.competencias.map((c, index) => ({
    label: `Competencia ${index + 1} · ${formatTipo(c.tipo)}`,
    value: c.descripcion
  }));
});

/* =========================
   FORMATEAR TIPO
========================= */
const formatTipo = (tipo) => {
  if (!tipo) return "Sin tipo";

  return tipo === "c4ca4238a0b923820dcc509a6f75849b"
    ? "Competencia técnica"
    : "Competencia para la empleabilidad";
};
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
