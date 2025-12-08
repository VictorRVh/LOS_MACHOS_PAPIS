<template>
  <!-- Fondo oscuro -->
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <!-- Modal -->
    <div class="rounded-xl shadow-2xl w-full max-w-xl animate-fadeIn">

      <!-- Card con información -->
      <UserInfoCard 
        v-if="data" 
        :title="`${data.apellido_paterno} ${data.apellido_materno}, ${data.name}`"
        :avatarName="`${data.name} ${data.apellido_paterno?.[0] || ''}`"
        subtitle="Usuario"
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

/* FORMATEAR FECHAS BONITO */
const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);

    return date.toLocaleString("es-PE", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true
    });
};

/* LISTA DE INFORMACIÓN (Usuario) */
const infoList = computed(() => {
    if (!props.data) return [];

    return [
        { label: "Usuario", value: props.data.usuario },
        { label: "DNI", value: props.data.dni },
        { label: "Correo", value: props.data.email },
        { label: "Teléfono", value: props.data.telefono },
        { label: "Dirección", value: props.data.direccion },
        { label: "Fecha Nacimiento", value: props.data.fecha_nacimiento },

        /* CUENTA INICIAL */
        { 
          label: "Cuenta Inicial", 
          value: props.data.password_cambiada == 0 
                    ? "Sí, es cuenta inicial" 
                    : "No, ya fue cambiada"
        },

        /* ESTADO */
        { 
          label: "Estado", 
          value: props.data.status ? "Activo" : "Inactivo" 
        },

        /* FECHAS FORMATEADAS BONITO */
        { label: "Creado", value: formatDate(props.data.created_at) },
        { label: "Actualizado", value: formatDate(props.data.updated_at) },
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
