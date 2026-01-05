<script setup>
import { computed, ref, watch } from "vue";
import * as yup from "yup";

import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";

import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../AuthorizationFallback.vue";
import BaseSelectModulo from "../../ui/BaseSelectCiclo.vue";

import useModuloStore from "../../../store/Modulos/useModulosStore";
const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    modulo: {
        type: [Object, null],
        default: () => null,
    },
    indexModulo: {
        type: [Object, null],
        default: () => null,
    },
    especialidad: {
        type: String,
        default: () => []
    },
});

const emit = defineEmits(["hide"]);
const moduloStore = useModuloStore();

const { store: createModulo, saving, update: updateModulo, updating } =
    useHttpRequest("/modulo");

const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const requiredPermissions = computed(() => [
    "todo-acceso-modulos",
    props.modulo?.id ? "editar-modulos" : "crear-modulos"
]);

/* =========================
   FORM DATA
========================= */
const initialFormData = () => ({
    numero_modulo: null,
    descripcion: null,
    creditos: null,
    horas: null,
    nro_capacidades: null,
    id_especialidad: props.especialidad,

    competencias: []
});

const formData = ref(initialFormData());
const formErrors = ref({});

const isEditing = computed(() => !!props.modulo?.id);

const onCancelEdit = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};


/* =========================
   COMPETENCIAS DINÁMICAS
========================= */
const addCompetencia = () => {
    formData.value.competencias.push({
        tipo: null,
        descripcion: ""
    });
};

const removeCompetencia = (index) => {
    if (formData.value.competencias.length > 0) {
        formData.value.competencias.splice(index, 1);
    }
};

/* =========================
   WATCH EDIT
========================= */
watch(
    () => props.modulo,
    (newRole) => {
        if (props.show && newRole?.id) {

            const baseData = initialFormData();

            formData.value = {
                ...baseData,
                ...Object.keys(baseData).reduce((acc, key) => {
                    if (newRole[key] !== undefined) acc[key] = newRole[key];
                    return acc;
                }, {})
            }; formErrors.value = {};
        }
    },
    { immediate: true }
);

/* =========================
   VALIDACIÓN
========================= */
const schema = yup.object({
    numero_modulo: yup.number().required(),
    descripcion: yup.string().required(),
    creditos: yup.number().required(),
    horas: yup.number().required(),
    nro_capacidades: yup.number().required(),
    id_especialidad: yup.string().required(),

    competencias: yup.array().of(
        yup.object({
            tipo: yup.string().required("Ingrese el tipo"),
            descripcion: yup
                .string()
                .required("Ingrese descripción")
                .max(225)
        })
    )
});

/* =========================
   SUBMIT
========================= */
const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        console.log(formErrors.value)
        return;
    }

    const response = props.modulo?.id
        ? await updateModulo(props.modulo.id, formData.value)
        : await createModulo(formData.value);

    if (response?.id) {
        showToast(`Módulo ${props.modulo?.id ? "editado" : "creado"} correctamente`);
        moduloStore.loadModuloById(props.especialidad);
        emit("hide");
        formData.value = initialFormData();
        formErrors.value = {};
    }
};


</script>


<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <div class="bg-white space-y-3 dark:bg-gray-800 rounded-lg shadow-md p-6 h-fit sticky top-6">
            <h3 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
                {{ isEditing ? "Editar módulo" : "Agregar nuevo módulo" }}
            </h3>
            <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />
            <FormInput v-model="formData.descripcion" :focus="show" label="Nombre del módulo"
                :error="formErrors?.descripcion" required />


            <FormLabelError label="Número de módulo" required :error="formErrors?.numero_modulo">
                <BaseSelectModulo v-model="formData.numero_modulo" :options="props.indexModulo" label="name"
                    placeholder="Seleccione un módulo" />
            </FormLabelError>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <FormInput v-model="formData.creditos" :focus="show" label="Créditos" :error="formErrors?.creditos"
                    required type="number" />
                <FormInput v-model="formData.horas" :focus="show" label="Horas" :error="formErrors?.horas" required
                    type="number" />
            </div>


            <FormInput v-model="formData.nro_capacidades" :focus="show" label="Número de unidades didácticas"
                :error="formErrors?.nro_capacidades" required type="number" />



            <!-- ===================== -->
            <!-- COMPETENCIAS -->
            <!-- ===================== -->

            <div class="space-y-3">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-cetpro" />
                        <h4 class="font-semibold">Competencias</h4>
                    </div>

                    <!-- Add -->
                    <button title="Agregar una nueva competencia" type="button" @click="addCompetencia"
                        class="flex items-center gap-1 text-sm text-cetpro hover:text-cetpro-dark transition">
                        <PlusIcon class="w-4 h-4" />
                        <span>Agregar</span>
                    </button>
                </div>

                <!-- Cards -->
                <div v-for="(competencia, index) in formData.competencias" :key="index" class="relative rounded-xl border border-gray-200 dark:border-gray-700
               bg-gray-50 dark:bg-gray-900 p-4 space-y-3">
                    <!-- Eliminar -->
                    <button title="Eliminar" v-if="formData.competencias.length > 0" type="button"
                        @click="removeCompetencia(index)"
                        class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition">
                        <TrashIcon class="w-4 h-4" />
                    </button>

                    <!-- Tipo -->

                    <FormInput v-model="competencia.tipo" label="Nombre de la competencia" />
                    <!-- Descripción -->
                    <FormInput v-model="competencia.descripcion" type="textarea" label="Descripción" :maxlength="225" />

                    <!-- Contador -->
                    <div class="flex justify-end">
                        <span class="text-xs" :class="competencia.descripcion.length > 200
                            ? 'text-red-500'
                            : 'text-gray-400'">
                            {{ competencia.descripcion.length }}/225
                        </span>
                    </div>
                </div>
            </div>

            <div class="w-full space-y-3">

                <div class="flex gap-2 mt-1">
                    <!-- Botón Guardar: ancho completo -->
                    <Button :title="modulo?.id ? 'Guardar Cambios' : 'Crear Módulo'"
                        :loading-title="role?.id ? 'Guardando...' : 'Creando...'" :disabled="saving || updating"
                        :loading="saving || updating" key="submit-btn" @click="onSubmit" class="!w-full" />

                    <!-- Botón Cancelar: ancho flexible solo si se está editando -->
                    <Button v-if="isEditing" title="Cancelar" variant="outline" @click="onCancelEdit"
                        class="bg-red-500 active:bg-red-500 dark:bg-cc-10 active:dark:bg-cc-10 text-white dark:text-red-200 hover:bg-red-600 dark:hover:bg-cc-12 cursor-pointer px-4" />
                </div>
            </div>
        </div>
    </AuthorizationFallback>
</template>
