<script setup>
import { ref, onMounted } from 'vue';
import Table from '../../components/table/Table.vue';
import THead from '../../components/table/THead.vue';
import TBody from '../../components/table/TBody.vue';
import Tr from '../../components/table/Tr.vue';
import Th from '../../components/table/Th.vue';
import Td from '../../components/table/Td.vue';
import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useMatriculaStore from '../../store/Matricula/useMatriculaStore';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const matriculaStore = useMatriculaStore()


const alumnos = ref([]);

onMounted(async () => {
  console.log(`Cargando alumnos para el módulo con ID: ${props.id}`);

  await matriculaStore.fetchMatriculadosPorGrupoExtendido(props.id);
  alumnos.value = matriculaStore.matriculadosPorGrupoExtendido.estudiantes;
});



</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-alumnos-docente','ver-alumnos-docente']">
    <div class="w-full space-y-4">
      <div class="flex-between">
        <h2 class="text-cetpro dark:text-cetpro-light font-bold text-2xl">
          Lista de Alumnos Asignados
        </h2>
      </div>

      <Table>
        <THead>
          <Th>N°</Th>
          <Th>Apellidos y Nombres</Th>
          <Th>DNI</Th>
          <Th>Estado</Th>
          <Th class="text-center">Acciones</Th>
        </THead>
        <TBody>
          <Tr v-for="(alumno, index) in alumnos" :key="alumno.id">
            <Td>{{ index + 1 }}</Td>
            <Td>{{ alumno.apellidos_nombres }}</Td>
            <Td>{{ alumno.nro_documento }}</Td>
            <Td>
              <span :class="alumno.estado === 'Matriculado' ? 'text-green-600' : 'text-red-600'">
                {{ alumno.estado }}
              </span>
            </Td>
            <Td class="text-center">
            </Td>
          </Tr>
        </TBody>
      </Table>
    </div>
  </AuthorizationFallback>
</template>