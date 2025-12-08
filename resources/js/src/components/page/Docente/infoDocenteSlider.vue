<template>
  <!-- Fondo oscuro -->
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <!-- Modal -->
    <div class="rounded-xl shadow-2xl  w-[500px] max-w-xl animate-fadeIn">

      <!-- Card con información -->
      <UserInfoCard 
        v-if="data" 
        :title="`${data.apellido_paterno} ${data.apellido_materno}, ${data.name}`"
        :avatarName="`${data.name} ${data.apellido_paterno[0]}`" 
        subtitle="Docente" 
        :info="infoList"
        @close="emitClose" 
      />

    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import UserInfoCard from "@/components/table/ViewInfo.vue";

/* PROPS */
const props = defineProps({
    show: { type: Boolean, default: false },
    data: { type: Object, default: null }
});

/* EMITS */
const emit = defineEmits(["close"]);
const emitClose = () => emit("close");

/* LISTA DE INFORMACIÓN (Docente) */
const infoList = computed(() => {
    if (!props.data) return [];

    return [
        { label: "DNI", value: props.data.dni },
        { label: "Correo", value: props.data.email },
        { label: "Teléfono", value: props.data.telefono },
        { label: "Dirección", value: props.data.direccion },
        { label: "Fecha Nacimiento", value: props.data.fecha_nacimiento },
        { label: "Código Modular", value: props.data.codigo_modular },
        { label: "Especialidad", value: props.data.especialidad },
        { label: "Condición", value: props.data.condicion },
        { label: "Escala Magisterial", value: props.data.escala_magisterial },
        { label: "RD Nombramiento", value: props.data.rd_nombramiento },
    ];
});
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
