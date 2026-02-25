<script setup>
import axios from "axios";
import { ref } from "vue";
import useHttpRequest from "../../composables/useHttpRequest";

const { store: buscar, saving } = useHttpRequest("/buscarEstudiante");
const { store: egresado } = useHttpRequest("/egresados");

const query = ref("");
const loading = ref(false);
const estudiante = ref(null);
const historialAcademico = ref([]);
const hasSearched = ref(false);
const error = ref("");

const especialidadAbierta = ref(null)

const showEgresadoModal = ref(false);
const selectedEspecialidadId = ref(null);
const selectedEstudianteId = ref(null);

const buscarEstudiante = async () => {
    if (!query.value.trim()) return;

    loading.value = true;
    hasSearched.value = true;
    error.value = "";

    try {

        estudiante.value = null;
        historialAcademico.value = [];

        const response = await buscar({
            nro_documento: query.value.trim()
        });

        if (response.success) {
            estudiante.value = response.data.estudiante;
            historialAcademico.value = response.data.historial_academico;
        } else {
            error.value = response.message || "No se encontró el estudiante";
        }
    } catch (err) {
        console.error(err);
        error.value = err.response?.data?.message || "Error al buscar estudiante";
    } finally {
        loading.value = false;
    }
};

const toggleEspecialidad = (id) => {
    especialidadAbierta.value =
        especialidadAbierta.value === id ? null : id
}

const calcularEdad = (fecha) => {
    const hoy = new Date();
    const nacimiento = new Date(fecha);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();

    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }

    return edad;
};

const formatearFecha = (fecha) => {
    if (!fecha) return "";
    const f = new Date(fecha);
    const dia = String(f.getDate()).padStart(2, '0');
    const mes = String(f.getMonth() + 1).padStart(2, '0');
    const anio = f.getFullYear();
    return `${dia}/${mes}/${anio}`;
};

const esFinalizado = (fechaFin) => {
    if (!fechaFin) return false;
    const hoy = new Date();
    const fin = new Date(fechaFin);
    return hoy > fin;
};

const pasarEgresado = (especialidadId, estudianteId) => {

    selectedEspecialidadId.value = especialidadId;
    selectedEstudianteId.value = estudianteId;

    showEgresadoModal.value = true;
};

const confirmarEgresado = async () => {
    try {

        const response = await egresado({
            id_estudiante: selectedEstudianteId.value,
            id_especialidad: selectedEspecialidadId.value,
        });

        console.log(response);

        showEgresadoModal.value = false;

        // opcional: limpiar
        selectedEspecialidadId.value = null;
        selectedEstudianteId.value = null;

    } catch (error) {
        console.error(error);
    }
};

</script>

<template>
    <div class="p-6 space-y-4">

        <h1 class="text-xl font-bold text-gray-700 dark:text-gray-200 mb-2">
            Buscar Estudiante
        </h1>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-lg">
            <div class="flex items-center bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden shadow-inner">

                <input v-model="query" @keyup.enter="buscarEstudiante"
                    class="flex-1 px-6 py-3 bg-transparent focus:outline-none text-gray-700 dark:text-gray-200"
                    type="text" placeholder="Ingrese DNI del estudiante..." />

                <button @click="buscarEstudiante" :disabled="loading"
                    class="px-6 py-3 bg-cetpro text-white font-semibold hover:bg-opacity-90 transition disabled:opacity-50">
                    {{ loading ? '...' : 'Buscar' }}
                </button>

            </div>
        </div>

        <div v-if="error"
            class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg">
            {{ error }}
        </div>

        <div v-if="estudiante" class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-4">
                Información Personal
            </h2>

            <div
                class="flex flex-col md:flex-row items-start md:items-center md:justify-between gap-6 p-4 bg-gray-50 dark:bg-slate-800/50 rounded-xl">

                <!-- Avatar + Nombre -->
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 bg-gray-200 dark:bg-slate-600 rounded-full flex items-center justify-center text-3xl">
                        👤
                    </div>

                    <div>
                        <p class="text-xl font-semibold text-cetpro">{{ estudiante.nombre_completo }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            DNI: {{ estudiante.nro_documento }}
                        </p>
                    </div>
                </div>

                <!-- Datos resumidos en una línea -->
                <div class="flex flex-wrap items-center gap-6 text-sm">

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Sexo</p>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ estudiante.sexo === 'M' ? 'Masculino' : 'Femenino' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Edad</p>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ calcularEdad(estudiante.fecha_nacimiento) }} años
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Lugar de Nacimiento</p>
                        <p class="font-semibold text-gray-700 dark:text-gray-200 max-w-md">
                            {{ estudiante.lugar_nacimiento.distrito }},
                            {{ estudiante.lugar_nacimiento.provincia }},
                            {{ estudiante.lugar_nacimiento.departamento }},
                            {{ estudiante.lugar_nacimiento.pais }}
                        </p>
                    </div>

                </div>
            </div>

        </div>

        <div v-if="historialAcademico.length > 0" class="space-y-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Historial Académico
            </h2>

            <div v-for="especialidad in historialAcademico" :key="especialidad.id"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow border border-gray-100 dark:border-slate-700">

                <!-- 🔽 Header del desplegable -->
                <button @click="toggleEspecialidad(especialidad.id)"
                    class="w-full flex justify-between items-center p-5 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/40 transition rounded-2xl">

                    <div>
                        <h3 class="text-lg font-bold text-cetpro">{{ especialidad.nombre }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ especialidad.total_modulos }} módulos
                        </p>
                    </div>

                    <span class="text-gray-500 dark:text-gray-300 transition-transform duration-300"
                        :class="especialidadAbierta === especialidad.id ? 'rotate-180' : ''">
                        ▼
                    </span>
                </button>

                <!-- 🔥 Contenido colapsable -->
                <transition name="fade">
                    <div v-if="especialidadAbierta === especialidad.id"
                        class="p-6 space-y-5 border-t dark:border-slate-700">

                        <!-- Periodos -->
                        <div v-for="periodo in especialidad.periodos" :key="periodo.id" class="space-y-4">

                            <div class="flex items-center gap-3">
                                <span
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                                    {{ periodo.nombre }}
                                </span>
                            </div>

                            <!-- Módulos -->
                            <div class="space-y-4 ml-4">
                                <div v-for="item in periodo.modulos" :key="item.matricula_id"
                                    class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-md transition">

                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                                Módulo {{ item.modulo.numero }}: {{ item.modulo.descripcion }}
                                            </p>

                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ item.modulo.creditos }} créditos ·
                                                {{ item.modulo.horas }} horas ·
                                                {{ item.modulo.nro_capacidades }} capacidades
                                            </p>
                                        </div>

                                        <span :class="esFinalizado(item.grupo.fecha_fin)
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                            : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'"
                                            class="px-3 py-1 rounded-full text-xs font-medium">
                                            {{ esFinalizado(item.grupo.fecha_fin) ? 'Finalizado' : 'En curso' }}
                                        </span>
                                    </div>

                                    <!-- Badges -->
                                    <div class="flex flex-wrap gap-2 mt-4 text-xs">
                                        <span class="badge-purple">Sección: {{ item.grupo.seccion || 'N/A' }}</span>
                                        <span class="badge-green">Turno: {{ item.grupo.turno || item.matricula.turno
                                        }}</span>

                                        <span :class="item.matricula.matriculado ? 'badge-blue' : 'badge-yellow'">
                                            {{ item.matricula.matriculado ? 'Matriculado' : 'Reserva' }}
                                        </span>
                                    </div>

                                    <!-- Fechas -->
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                                        Inicio: {{ formatearFecha(item.grupo.fecha_inicio) }} ·
                                        Fin: {{ formatearFecha(item.grupo.fecha_fin) }}
                                    </p>

                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-4">
                                <button :disabled="especialidad.es_egresado"
                                    @click="!especialidad.es_egresado && pasarEgresado(especialidad.especialidad_programa, estudiante.id)"
                                    :class="especialidad.es_egresado
                                        ? 'bg-gray-400 cursor-not-allowed'
                                        : 'bg-cetpro hover:bg-cetpro-dark'"
                                    class="text-white font-semibold px-6 py-2 rounded-lg shadow-google-sm transition-all duration-200">
                                    {{ especialidad.es_egresado ? 'YA ES EGRESADO' : 'PASAR A EGRESADO' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- 📭 Sin resultados -->
        <div v-if="hasSearched && !loading && !estudiante && !error"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">
                No se encontró ningún estudiante con ese documento.
            </p>
        </div>
    </div>

    <div v-if="showEgresadoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
            <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">
                ¿Desea pasar a este estudiante a Egresado?
            </h2>

            <!-- ACCIONES -->
            <div class="flex justify-end gap-2">
                <button @click="showEgresadoModal = false" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
                    Cancelar
                </button>

                <button @click="confirmarEgresado"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">
                    Sí
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all .25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>