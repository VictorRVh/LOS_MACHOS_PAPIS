<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import FormInput from '../../components/ui/FormInput.vue';
import Button from '../../components/ui/Button.vue';
import FormLabelError from '../../components/ui/FormLabelError.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import useModalToast from '../../composables/useModalToast';
import { Bars3Icon } from '@heroicons/vue/24/outline';

const router = useRouter();
const { showToast } = useModalToast();

// --- SIMULACIÓN DE DATOS Y ESTADOS ---
const buscandoDni = ref(false);
const saving = ref(false);
const formData = ref({
    tipo_documento: 'DNI - DOC. NACIONAL DE IDENTIDAD',
    pais_nacimiento: 'Perú',
    departamento_nacimiento: 'Puno',
    provincia_nacimiento: 'Puno',
    distrito_nacimiento: 'Puno',
    lugar_nacimiento: 'Puno',
    direccion_residencia: 'Av. El Sol 123',
    condicion: 'G | Gratuito',
});
const formErrors = ref({});

// Opciones simuladas para los selectores
const ciclos = ref([{ id: 1, label: 'Ciclo Regular 2024-I' }, { id: 2, label: 'Ciclo de Verano 2024' }]);
const especialidades = ref([{ id: 1, label: 'Computación e Informática' }, { id: 2, label: 'Asistencia de Cocina' }]);
const grupos = ref([{ id: 1, label: 'Grupo A - Mañana' }, { id: 2, label: 'Grupo B - Tarde' }]);
const opcionesSimples = ref(['Opción 1', 'Opción 2', 'Opción 3']);
// --- FIN SIMULACIÓN ---

function buscarPorDNI() {
    buscandoDni.value = true;
    setTimeout(() => {
        formData.value.apellido_paterno = 'QUISPE';
        formData.value.apellido_materno = 'MAMANI';
        formData.value.nombre = 'JUAN CARLOS';
        buscandoDni.value = false;
        showToast('Datos de DNI encontrados (simulado).');
    }, 1000);
}

function onSubmit() {
    saving.value = true;
    setTimeout(() => {
        showToast('Estudiante registrado y matriculado con éxito (simulado).');
        saving.value = false;
        router.push({ name: 'matricula.index' });
    }, 1500);
}
</script>

<template>
    <div class="p-4 sm:p-6 bg-gray-100 dark:bg-gray-900/50 font-inter min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            <div class="p-4 sm:p-6 space-y-8">
                
                <!-- SECCIÓN 1: DATOS ACADÉMICOS -->
                <section>
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-cetpro dark:text-cetpro-light border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        <Bars3Icon class="h-6 w-6"/> DATOS ACADÉMICOS
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <FormLabelError label="Ciclo *" class="lg:col-span-1">
                            <vSelect :options="ciclos" placeholder="Seleccione Ciclo"/>
                        </FormLabelError>
                        <FormLabelError label="Especialidad/Opción ocupacional *" class="lg:col-span-2">
                            <vSelect :options="especialidades" placeholder="Seleccione Opción"/>
                        </FormLabelError>
                        <FormLabelError label="Grupo *" class="lg:col-span-1">
                            <vSelect :options="grupos" placeholder="Seleccionar..."/>
                        </FormLabelError>
                        <FormInput label="Convenio" class="lg:col-span-1"/>
                        <FormInput label="Duración" />
                        <FormInput label="Horas" />
                        <FormInput label="Turno" />
                        <FormInput label="Sección" />
                    </div>
                </section>

                <!-- SECCIÓN 2: DATOS DEL ESTUDIANTE -->
                <section>
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-cetpro dark:text-cetpro-light border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        <Bars3Icon class="h-6 w-6"/> DATOS DEL ESTUDIANTE
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-4">
                        <!-- Columna 1 -->
                        <div class="space-y-4">
                            <FormLabelError label="Tipo documento *">
                                <vSelect v-model="formData.tipo_documento" :options="['DNI - DOC. NACIONAL DE IDENTIDAD']" :clearable="false"/>
                            </FormLabelError>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Num. documento *</label>
                                <div class="relative flex items-center">
                                   <FormInput v-model="formData.nro_documento" maxlength="8" class="pr-10" />
                                   <button @click="buscarPorDNI" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                                       <svg v-if="!buscandoDni" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                       <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                   </button>
                                </div>
                            </div>
                            <FormInput v-model="formData.apellido_paterno" label="Apellido paterno *" required disabled/>
                            <FormInput v-model="formData.apellido_materno" label="Apellido materno *" required disabled/>
                            <FormInput v-model="formData.nombre" label="Nombres *" required disabled/>
                            <FormLabelError label="Sexo *"><vSelect :options="['Masculino', 'Femenino']" placeholder="Seleccione ..."/></FormLabelError>
                            <FormInput v-model="formData.fecha_nacimiento" label="Fecha de nacimiento *" type="date"/>
                        </div>
                        <!-- Columna 2 -->
                        <div class="space-y-4">
                            <FormInput v-model="formData.pais_nacimiento" label="País de nacimiento *" required disabled/>
                            <FormInput v-model="formData.departamento_nacimiento" label="Departamento de nacimiento *" required disabled/>
                            <FormInput v-model="formData.provincia_nacimiento" label="Provincia de nacimiento *" required disabled/>
                            <FormInput v-model="formData.distrito_nacimiento" label="Distrito de nacimiento *" required disabled/>
                            <FormInput v-model="formData.lugar_nacimiento" label="Lugar de nacimiento *" required disabled/>
                            <FormInput v-model="formData.direccion_residencia" label="Dirección de residencia *" required/>
                            <FormInput label="Correo electrónico"/>
                            <FormInput label="Celular personal (R)"/>
                        </div>
                         <!-- Columna 3 -->
                        <div class="space-y-4">
                           <FormLabelError label="Estado civil según DNI *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Grado de instrucción *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Trabaja *"><vSelect :options="['Sí', 'No']" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormInput label="Puesto de trabajo"/>
                           <FormLabelError label="Tiene carga familiar? *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Tiene internet en casa? *"><vSelect :options="['Sí', 'No']" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Tipo operador celular *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Equipo/clases virtuales *"><v-select :options="opcionesSimples" placeholder="Seleccione uno o varios" multiple/></FormLabelError>
                           <FormLabelError label="Presenta discapacidad? *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormInput label="Celular de referencia (R)"/>
                           <FormLabelError label="Parentesco con referencia *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                           <FormLabelError label="Lengua originaria *"><vSelect :options="opcionesSimples" placeholder="Seleccionar ..."/></FormLabelError>
                        </div>
                    </div>
                </section>
                
                 <!-- SECCIÓN 3: DATOS DE PAGO -->
                <section>
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-cetpro dark:text-cetpro-light border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        <Bars3Icon class="h-6 w-6"/> DATOS DE PAGO
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <FormLabelError label="Condición">
                             <vSelect v-model="formData.condicion" :options="['G | Gratuito']" :clearable="false"/>
                        </FormLabelError>
                        <FormInput v-model="formData.nro_recibo" label="N° Recibo" />
                        <FormInput v-model="formData.aporte" label="Aporte S/." type="number" step="0.01"/>
                    </div>
                </section>
            </div>
            <div class="p-4 sm:p-6 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <Button variant="outline" @click="router.push({ name: 'matricula.index' })" title="Cancelar"/>
                <Button @click="onSubmit" :loading="saving" title="Matricular"/>
            </div>
        </div>
    </div>
</template>