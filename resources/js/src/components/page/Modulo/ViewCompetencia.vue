<template>
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <BaseInfoCard title="Competencias del módulo" section-title="Listado de competencias" :info="infoList"
      layout="stacked" @close="emitClose" />
  </div>
</template>

<script setup>
import { computed } from "vue";
import BaseInfoCard from "../../table/ViewInfo.vue";

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

    console.log(props.modulo)
    
    return [
      {
        label: "Sin competencias",
        value: "Este módulo no tiene competencias registradas"
      }
    ];
  }
console.log("modulo: ",props.modulo)
  return props.modulo.competencias.map((c, index) => ({
    label: `${index + 1}. ${c.nombre}`,
    value: c.descripcion || "Sin descripción"
  }));
});


/* =========================
   FORMATEAR TIPO
========================= */
const formatTipo = (tipo) => {
  const tipos = {
    c4ca4238a0b923820dcc509a6f75849b: "Competencia técnica",
    c81e728d9d4c2f636f067f89cc14862c: "Competencia para la empleabilidad"
  };

  return tipos[tipo] || "Sin tipo";
};
</script>
