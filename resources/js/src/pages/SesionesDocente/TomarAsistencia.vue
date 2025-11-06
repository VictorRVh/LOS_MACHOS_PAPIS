<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import Spinner from '../../components/ui/Spinner.vue';
import Button from '../../components/ui/Button.vue';
import SaveButton from '../../components/ui/SaveButton.vue';

const fakeAlumnosDB = [
  { id: 101, nombre_completo: 'QUISPE FLORES, MARIA ELENA' },
  { id: 102, nombre_completo: 'MAMANI CHAVEZ, CARLOS ALBERTO' },
  { id: 103, nombre_completo: 'GOMEZ PEREZ, ANA SOFIA' },
  { id: 104, nombre_completo: 'RODRIGUEZ DIAZ, LUIS FERNANDO' },
];
const fakeAsistenciasDB = {
  '2025-11-20': [
    { alumno_id: 101, estado: 'Presente' }, { alumno_id: 102, estado: 'Presente' },
    { alumno_id: 103, estado: 'Falta' }, { alumno_id: 104, estado: 'Tardanza' },
  ],
  '2025-11-21': [
    { alumno_id: 101, estado: 'Presente' }, { alumno_id: 102, estado: 'Falta' },
    { alumno_id: 103, estado: 'Falta' }, { alumno_id: 104, estado: 'Presente' },
  ],
};
const fakeSesionData = {
    'sesion-test-999': {
        id: 'sesion-test-999',
        title: 'Sesión de Prueba (SIMULACIÓN)',
        dates: [new Date().toISOString().slice(0, 10), '2025-11-20', '2025-11-21']
    }
};
const useHttpRequest = () => {
  const loading = ref(false);
  const get = (url) => {
    loading.value = true;
    return new Promise(resolve => {
      setTimeout(() => {
        if (url.includes('/api/sesiones/')) { const id = url.split('/').pop(); resolve(fakeSesionData[id]); }
        if (url.includes('/api/asistencia/alumnos/')) { resolve(fakeAlumnosDB); }
        else if (url.includes('/api/asistencia/')) {
          const fecha = url.split('fecha=')[1];
          const hoy = new Date().toISOString().slice(0, 10);
          resolve(fakeAsistenciasDB[fecha] || fakeAsistenciasDB[hoy] || []);
        }
        loading.value = false;
      }, 800);
    });
  };
  const post = (url, payload) => new Promise(resolve => { setTimeout(() => resolve({ success: true }), 1500); });
  return { get, post, loading };
};

const route = useRoute();
const router = useRouter();
const sesionId = route.params.id;

const { get, post, loading } = useHttpRequest();
const isSaving = ref(false);

const sesion = ref(null);
const fechaSeleccionada = ref(null);
const alumnos = ref([]);

const fetchAlumnosYAsistencia = async (bloqueId, fecha) => {
  if (!bloqueId || !fecha) return;
  try {
    const [listaAlumnos, asistenciaGuardada] = await Promise.all([
      get(`/api/asistencia/alumnos/${bloqueId}`),
      get(`/api/asistencia/${bloqueId}?fecha=${fecha}`)
    ]);
    if (listaAlumnos && Array.isArray(listaAlumnos)) {
      alumnos.value = listaAlumnos.map(alumno => {
        const registro = asistenciaGuardada?.find(a => a.alumno_id === alumno.id);
        return { ...alumno, estado: registro ? registro.estado : 'Falta' };
      });
    }
  } catch (error) {
    alumnos.value = [];
  }
};

onMounted(async () => {
    sesion.value = await get(`/api/sesiones/${sesionId}`);
    if (sesion.value && sesion.value.dates.length > 0) {
        const hoy = new Date().toISOString().slice(0, 10);
        fechaSeleccionada.value = sesion.value.dates.includes(hoy) ? hoy : sesion.value.dates[0];
    }
});

watch(fechaSeleccionada, (nuevaFecha) => {
  if (nuevaFecha) {
    fetchAlumnosYAsistencia(sesionId, nuevaFecha);
  }
});

const handleGuardar = async () => {
  isSaving.value = true;
  await post(`/api/asistencia/${sesionId}`, {
      fecha: fechaSeleccionada.value,
      asistencias: alumnos.value.map(a => ({ alumno_id: a.id, estado: a.estado }))
  });
  isSaving.value = false;
  router.back();
};
</script>

<template>
  <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div v-if="loading && !sesion" class="flex justify-center items-center h-96">
      <Spinner />
    </div>
    <div v-else-if="sesion" class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Registro de Asistencia</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ sesion.title }}</p>
          </div>
          <Button @click="router.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-800">
            Volver
          </Button>
        </div>
      </div>
      <div class="p-4 sm:p-6">
        <div class="mb-6 max-w-sm">
            <label for="fecha-sesion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Seleccione la fecha de la sesión:
            </label>
            <select v-model="fechaSeleccionada" id="fecha-sesion" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option v-for="fecha in sesion.dates" :key="fecha" :value="fecha">
                {{ new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
              </option>
            </select>
        </div>
        
        <div v-if="loading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>
        <div v-else>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Lista de Estudiantes</h3>
            <Table>
              <THead>
                <Tr>
                  <Th>N°</Th>
                  <Th>Estudiante</Th>
                  <Th class="text-center">Estado</Th>
                </Tr>
              </THead>
              <TBody>
                <Tr v-for="(alumno, index) in alumnos" :key="alumno.id">
                  <Td>{{ index + 1 }}</Td>
                  <Td>{{ alumno.nombre_completo }}</Td>
                  <Td>
                    <div class="flex items-center justify-center gap-x-4 sm:gap-x-6 text-sm">
                      <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="alumno.estado" value="Presente" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"><span>Presente</span></label>
                      <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="alumno.estado" value="Tardanza" class="h-4 w-4 text-yellow-600 border-gray-300 focus:ring-yellow-500"><span>Tardanza</span></label>
                      <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="alumno.estado" value="Falta" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500"><span>Falta</span></label>
                    </div>
                  </Td>
                </Tr>
              </TBody>
            </Table>
        </div>
      </div>
      <div class="p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">
        <SaveButton @click="handleGuardar" :disabled="isSaving || loading">{{ isSaving ? 'Guardando...' : 'Guardar Asistencia' }}</SaveButton>
      </div>
    </div>
  </div>
</template>