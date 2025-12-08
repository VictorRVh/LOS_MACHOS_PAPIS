<template>
    <!-- Fondo oscuro -->
    <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <!-- Modal -->
        <div class="rounded-xl shadow-2xl  w-[500px] max-w-xl animate-fadeIn">

            <!-- Card con información -->
            <UserInfoCard v-if="data" :title="`${data.apellidos}, ${data.nombre}`"
                :avatarName="`${data.nombre} ${data.apellidos[0]}`" subtitle="Estudiante" :info="infoList"
                @close="emitClose" />

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

/* LISTA DE INFORMACIÓN (Estudiante + Pago) */
const infoList = computed(() => {
    if (!props.data) return [];

    return [
        // Datos del estudiante
        { label: "DNI", value: props.data.nro_documento },
        { label: "Celular", value: props.data.celular_personal },
        { label: "Correo", value: props.data.correo_electronico },
        { label: "Sexo", value: props.data.sexo },
        { label: "Fecha Matrícula", value: props.data.created_at },

        // SECCIÓN DE PAGO
        { label: "Condición de Pago", value: props.data.condicion },
        { label: "Nº Recibo", value: props.data.nro_recibo },
        { label: "Aporte", value: `S/ ${props.data.aporte}` },
        // { label: "Estado del Pago", value: pagoEstadoTexto(props.data.estado_pago) }
    ];
});

/* Convertir estado del pago a texto */
// function pagoEstadoTexto(code) {
//     const estados = {
//         0: "Pendiente",
//         1: "Activo",
//         2: "Desactivo",
//         3: "Anulado"
//     };
//     return estados[code] ?? "Desconocido";
// }
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
