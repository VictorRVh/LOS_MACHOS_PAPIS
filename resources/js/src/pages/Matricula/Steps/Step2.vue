<script setup>
import { computed, ref } from 'vue';
import FormInput from '../../../components/ui/FormInput.vue';
import FormLabelError from '../../../components/ui/FormLabelError.vue';
import Button from '../../../components/ui/Button.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { Bars3Icon } from '@heroicons/vue/24/outline';

import useEstudianteStore from '../../../store/estudiante/useEstudianteStore';
import useModalToast from '../../../composables/useModalToast';
import { debounce } from 'lodash';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const estudianteStore = useEstudianteStore();
const { showToast } = useModalToast();
const buscandoDni = ref(false);

const buscarPorDNI = debounce(async () => {
    if (!formData.value.nro_documento || formData.value.nro_documento.length !== 8) {
        showToast('El DNI debe tener 8 dígitos.', 'error');
        return;
    }
    buscandoDni.value = true;
    const response = await estudianteStore.buscarPorDni(formData.value.nro_documento);
    if (response.success) {
        const estudiante = response.data;
        formData.value.nombre = estudiante.name || '';
        formData.value.apellido_paterno = estudiante.first_last_name || '';
        formData.value.apellido_materno = estudiante.second_last_name || '';
        showToast('Estudiante encontrado.', 'success');
    } else {
        showToast(response.message || 'Estudiante no encontrado.', 'warning');
    }
    buscandoDni.value = false;
}, 500);

</script>

<template>
    <div>
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <Bars3Icon class="h-6 w-6" />
            DATOS DEL ESTUDIANTE
        </h3>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="flex gap-2">
                        <FormInput v-model="formData.nro_documento" label="DNI *" maxlength="8" placeholder="Ingrese DNI" />
                        <Button 
                            title="Buscar" 
                            :loading-title="'...'" 
                            :loading="buscandoDni"
                            class="!w-24 !h-10 !mt-6 !text-sm" 
                            @click="buscarPorDNI" 
                        />
                    </div>
                </div>
                <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno *" required disabled />
                <FormInput v-model="formData.apellido_materno" label="Apellido Materno *" required disabled />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FormInput v-model="formData.nombre" label="Nombres *" class="md:col-span-1" required disabled />
                <FormLabelError label="Sexo *">
                    <vSelect v-model="formData.sexo" :options="['Masculino', 'Femenino']" placeholder="Seleccione..."></vSelect>
                </FormLabelError>
                <FormInput v-model="formData.pais_nacimiento" label="País de Nacimiento *" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FormInput v-model="formData.departamento_nacimiento" label="Departamento de nacimiento" />
                <FormInput v-model="formData.provincia_nacimiento" label="Provincia de nacimiento" />
                <FormInput v-model="formData.distrito_nacimiento" label="Distrito de nacimiento" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FormInput v-model="formData.lugar_nacimiento" label="Lugar de nacimiento" />
                <FormInput v-model="formData.direccion_residencia" label="Dirección de residencia" class="md:col-span-2" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <FormInput v-model="formData.fecha_nacimiento" label="Fecha de nacimiento *" type="date" />
                 <FormLabelError label="Estado civil *">
                     <vSelect v-model="formData.estado_civil" :options="['Soltero(a)', 'Casado(a)', 'Conviviente', 'Divorciado(a)', 'Viudo(a)']" placeholder="Seleccionar..."></vSelect>
                 </FormLabelError>
                 <FormLabelError label="Grado de instrucción *">
                     <vSelect v-model="formData.grado_instruccion" :options="['Primaria Incompleta', 'Primaria Completa', 'Secundaria Incompleta', 'Secundaria Completa', 'Superior']" placeholder="Seleccionar..."></vSelect>
                 </FormLabelError>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FormLabelError label="¿Trabaja? *">
                    <vSelect v-model="formData.trabaja" :options="['Sí', 'No']" placeholder="Seleccionar..."></vSelect>
                </FormLabelError>
                <FormInput v-model="formData.puesto_trabajo" label="Puesto de trabajo" />
                <FormInput v-model="formData.correo" label="Correo electrónico" type="email" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="formData.celular" label="Celular personal" type="tel" />
            </div>
        </div>
    </div>
</template>