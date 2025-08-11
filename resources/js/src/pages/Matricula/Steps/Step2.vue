<script setup>
import { computed } from 'vue';
import FormInput from '../../../components/ui/FormInput.vue';
import FormLabelError from '../../../components/ui/FormLabelError.vue';
import BaseSelect from '../../../components/ui/BaseSelect.vue';
import { UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const opcionesSexo = ['MASCULINO', 'FEMENINO'];
const opcionesEstadoCivil = ['SOLTERO(A)', 'CASADO(A)', 'VIUDO(A)', 'DIVORCIADO(A)', 'CONVIVIENTE'];
</script>

<template>
    <div>
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <UserIcon class="h-6 w-6" />
            DATOS PERSONALES DEL ESTUDIANTE
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <FormInput v-model="formData.apellido_paterno" label="Apellido Paterno *" />
            <FormInput v-model="formData.apellido_materno" label="Apellido Materno *" />
            <FormInput v-model="formData.nombre" label="Nombres *" />
            
            <div class="grid grid-cols-2 gap-4">
                <FormLabelError label="Tipo Doc. *">
                     <BaseSelect v-model="formData.tipo_documento" :options="['DNI', 'CARNET EXT.']" />
                </FormLabelError>
                <FormInput v-model="formData.nro_documento" label="Nro Doc. *" />
            </div>

            <FormLabelError label="Sexo *">
                <BaseSelect v-model="formData.sexo" :options="opcionesSexo" placeholder="Seleccione sexo" />
            </FormLabelError>
            <FormInput v-model="formData.fecha_nacimiento" label="Fecha de Nacimiento *" type="date" />
            
            <FormInput v-model="formData.celular" label="Celular *" />
            <FormInput v-model="formData.correo" label="Correo Electrónico *" type="email" />
            <FormInput v-model="formData.direccion_residencia" label="Dirección de Residencia *" />
            
            <FormLabelError label="Estado Civil *">
                <BaseSelect v-model="formData.estado_civil" :options="opcionesEstadoCivil" placeholder="Seleccione estado"/>
            </FormLabelError>
            <FormInput v-model="formData.grado_instruccion" label="Grado de Instrucción" />
            <FormInput v-model="formData.lugar_nacimiento" label="Lugar de Nacimiento" />
        </div>
    </div>
</template>