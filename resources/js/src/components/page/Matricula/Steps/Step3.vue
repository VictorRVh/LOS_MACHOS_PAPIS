<script setup>
import { computed } from 'vue';
import FormInput from '../../../ui/FormInput.vue';
import FormLabelError from '../../../ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { Bars3Icon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: { type: Object, required: true },
    nameGrupo: { type: String, },
    edit: { type: Boolean, default: false }
});
const emit = defineEmits(['update:modelValue']);

const formData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

</script>

<template>
    <div>
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-700 dark:text-white mb-6">
            <Bars3Icon class="h-6 w-6" />
            DATOS DE PAGO Y CONFIRMACIÓN
        </h3>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <FormLabelError label="Condición">
                    <vSelect v-model="formData.condicion"
                        :options="['G | Gratuito', 'P | Pagante', 'B | Beca', 'S | Semibeca']" :clearable="false" />
                </FormLabelError>
                <FormInput v-model="formData.nro_recibo" label="N° Recibo / Voucher" />
                <FormInput v-model="formData.aporte" label="Aporte S/." type="number" step="0.01" />
            </div>
            <div v-if="!edit" class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center">
                    <InformationCircleIcon class="h-6 w-6 text-blue-500 mr-3" />
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-gray-200">Resumen de Matrícula</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Por favor, revise que los datos sean
                            correctos antes de finalizar el proceso.</p>
                    </div>
                </div>

                <ul class="mt-4 text-sm space-y-2 pl-9">
                    <li>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Estudiante: </span>
                        <span class="text-gray-600 dark:text-gray-400"> {{ formData.nombre }} {{
                            formData.apellido_paterno }} {{ formData.apellido_materno }}</span>
                    </li>
                    <li>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">DNI: </span>
                        <span class="text-gray-600 dark:text-gray-400"> {{ formData.nro_documento }}</span>
                    </li>
                    <li>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Detalles de grupo: </span>
                        <span class="text-gray-600 dark:text-gray-400"> {{ props.nameGrupo || 'No seleccionado'
                            }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>