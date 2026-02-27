<script setup>

import axios from 'axios' // tu instancia
import { onMounted } from 'vue'
import Table from "../../components/table/Table.vue";
import THead from "../../components/table/THead.vue";
import TBody from "../../components/table/TBody.vue";
import Tr from "../../components/table/Tr.vue";
import Th from "../../components/table/Th.vue";
import Td from "../../components/table/Td.vue";
import EditButton from "../../components/ui/EditButton.vue";
import DeleteButton from "../../components/ui/DeleteButton.vue";
import BaseButton from "../../components/ui/Button.vue"

import AuthorizationFallback from "../../components/page/AuthorizationFallback.vue";
import useSlider from "../../composables/useSlider";


import useModalToast from "../../composables/useModalToast";
import useHttpRequest from "../../composables/useHttpRequest";

import periodoSlider from "../../components/page/Periodo/PeriodoSlider.vue";
import usePeriodosStore from "../../store/Periodo/usePeriodoStore";


const periodosStore = usePeriodosStore();

if (!periodosStore.periodos.length) await periodosStore.loadPeriodos();

const { slider, sliderData, showSlider, hideSlider } = useSlider("role-crud");
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePeriodo, deleting } = useHttpRequest("/periodo");

const onDelete = (periodo) => {

  if (deleting.value) return;

  showConfirmModal(null, async (confirmed) => {
    if (!confirmed) return;

    const isDeleted = await deletePeriodo(periodo?.id);
    console.log(isDeleted)
    if (isDeleted) {
      showToast(`Periodo "${periodo?.nombre_periodo}" eliminado exitosamente...`);
      periodosStore.loadPeriodos();

    }
  });
};



const descargarDocumento = async (idPeriodo,nombre) => {
  try {
    const response = await axios.get(`reportes/matriculaInstitucional/${idPeriodo}`, { responseType: "blob" });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `Historial ${nombre} Matrícula Institucional.xlsx`);
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

const descargarReporteCertificado = async (idPeriodo,nombre) => {
  try {
    const response = await axios.get(
      `reportes/certificadosPorPeriodo/${idPeriodo}`,
      { responseType: "blob" }
    );

    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement("a");

    link.href = url;
    link.setAttribute("download", `Historial ${nombre} Certificados Emitidos.xlsx`);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

  } catch (error) {
    console.error("Error descargando reporte:", error);
  }
};

</script>

<template>
  <AuthorizationFallback :permissions="['todo-acceso-periodos', 'ver-periodos']">
    <div class="flex justify-between items-center p-4">
      <h2 class="text-cetpro ml-2 dark:text-cetpro-light font-bold text-2xl">Periodos</h2>
    </div>
    <div class="flex flex-col lg:flex-row px-6 gap-6">
      <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">

        <periodoSlider :show="slider" :periodo="sliderData" @hide="hideSlider" />
      </div>
      <div class="w-full lg:w-2/3">
        <Table>
          <THead>
            <Th>Id</Th>
            <Th>Periodo</Th>
            <Th>Estado</Th>
            <Th class="text-center">Acciones</Th>
          </THead>

          <TBody>
            <Tr v-for="(periodo, index) in periodosStore.periodos" :key="periodo.id">
              <Td>{{ index + 1 }}</Td>
              <Td>{{ periodo?.nombre_periodo }}</Td>
              <Td>
                <span :class="periodo.status === 1
                  ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900'
                  : 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900'
                  " class="px-2 py-1 text-xs rounded-md font-semibold inline-flex items-center gap-1">
                  <span v-if="periodo.status === 1"> Activo ✓ </span>
                  <span v-else="periodo.status === 0"> Inactivo X </span>
                </span>
              </Td>
              <Td class="align-middle">
                <div class="flex items-center justify-center gap-1">
                  <EditButton @click="showSlider(true, periodo)" />
                  <DeleteButton @click="onDelete(periodo)" />

                  <BaseButton title="MATRÍCULA  INSTITUCIONAL" @click="descargarDocumento(periodo.id,periodo?.nombre_periodo)"
                    class="px-1 h-[35px] bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                    <template #icon>

                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                      </svg>

                    </template>
                  </BaseButton>

                  <BaseButton title="CERTIFICADOS" @click="descargarReporteCertificado(periodo.id)"
                    class="px-1 h-[35px] bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                    <template #icon>

                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                      </svg>

                    </template>
                  </BaseButton>
                </div>
              </Td>
            </Tr>
          </TBody>
        </Table>
      </div>

    </div>
  </AuthorizationFallback>
</template>