<script setup>
import { computed, ref, watch, onMounted } from "vue";
import FormInput from "../../ui/FormInput.vue";
import FormLabelError from "../../ui/FormLabelError.vue";
import Button from "../../ui/Button.vue";
import AuthorizationFallback from "../../../components/page/AuthorizationFallback.vue";
import useValidation from "../../../composables/useValidation";
import useHttpRequest from "../../../composables/useHttpRequest";
import useModalToast from "../../../composables/useModalToast";
import useCapacidadesStore from "../../../store/CapacidadTerminal/UseCapacidadUnidadesStore";
import useCompetenciasStore from "../../../store/CapacidadTerminal/UseCompetenciasStore";
import BaseSelectGrupo from "../../ui/BaseSelectGrupo.vue";



import * as yup from "yup";

/* =========================
   PROPS
========================= */
const props = defineProps({
    show: Boolean,
    capacidad: {
        type: Object,
        default: null,
    },
    idGrupo: {
        type: String,
        required: true, // para UNIDADES
    },
    idModulo: {
        type: String,
        required: true, // SOLO para COMPETENCIAS
    },
});

const emit = defineEmits(["hide","saved"]);

/* =========================
   STORES
========================= */
const capacidadesStore = useCapacidadesStore();
const competenciasStore = useCompetenciasStore();

/* =========================
   DATA
========================= */
const unidades = ref([]);
const capacidades = ref([]);

/* =========================
   COMPOSABLES
========================= */
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const {
    store: createCompetencia,
    saving,
    update: updateCompetencia,
    updating,
} = useHttpRequest("/capacidad_competencia");

/* =========================
   FORM
========================= */
const initialFormData = () => ({
    id_capacidad_terminal: null,
    id_competencia: null,
    descripcion: "",
});

const formData = ref(initialFormData());
const formErrors = ref({});
const isEditing = computed(() => !!props.capacidad?.id);

/* =========================
   PERMISOS
========================= */
const requiredPermissions = computed(() =>
    isEditing.value
        ? ["todo-acceso-capacidad-terminal-docente", "editar-capacidad-terminal-docente"]
        : ["todo-acceso-capacidad-terminal-docente", "crear-capacidad-terminal-docente"]
);

/* =========================
   LOAD DATA
========================= */
onMounted(async () => {
    // 🔹 UNIDADES (por id)
    await capacidadesStore.loadUnidadesDidacticas(props.idGrupo);
    unidades.value = capacidadesStore.unidadesDidacticas;

    // 🔹 COMPETENCIAS (SOLO por idModulo)
    await competenciasStore.loadCompetencias(props.idModulo);
    capacidades.value = competenciasStore.competencias;
});

/* =========================
   WATCH EDIT
========================= */
watch(
    () => props.capacidad,
    async (data) => {
        if (!props.show || !data?.id) return;
        console.log("ID competencia recibida:", data.id_competencia);
        console.log("Opciones competencias:", capacidades.value);

        // Asignar valores (IDs correctos)
        formData.value.id_capacidad_terminal = data.id_capacidad_terminal;
        formData.value.id_competencia = data.id_competencia;
        formData.value.descripcion = data.descripcion;

        formErrors.value = {};
    },
    { immediate: true }
);


/* =========================
   VALIDACIÓN
========================= */
const schema = yup.object({
    id_capacidad_terminal: yup.string().required("Debe seleccionar una unidad."),
    id_competencia: yup.string().required("Debe seleccionar una capacidad."),
    descripcion: yup.string().required("La descripción es obligatoria."),
});

/* =========================
   ACTIONS
========================= */
const onCancel = () => {
    formData.value = initialFormData();
    formErrors.value = {};
    emit("hide");
};

const onSubmit = async () => {
    if (saving.value || updating.value) return;

    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    const response = isEditing.value
        ? await updateCompetencia(props.capacidad.id, formData.value)
        : await createCompetencia(formData.value);

    if (response?.data?.id) {
        showToast(`Capacidad ${isEditing.value ? "editada" : "creada"} correctamente`);
        emit("saved");
        onCancel();
        
    }
};
</script>

<template>
    <AuthorizationFallback :permissions="requiredPermissions">
        <h2 class="text-lg font-semibold text-cetpro dark:text-cetpro-light mb-2">
            {{ isEditing ? "Editar Capacidad" : "Agregar Capacidad" }}
        </h2>

        <hr class="border-t-2 border-cetpro dark:border-cetpro-light mb-4" />

        <div class="space-y-3 font-inter">
            <!-- CAPACIDAD / COMPETENCIA -->
            <FormLabelError label="Unidad de competencia" required :error="formErrors?.id_competencia">
                <BaseSelectGrupo v-model="formData.id_competencia" :options="capacidades" label="nombre" value="tipo"
                    placeholder="Seleccione una capacidad" />

            </FormLabelError>
            <!-- UNIDAD DIDÁCTICA -->
            <FormLabelError label="Unidad didáctica" required :error="formErrors?.id_capacidad_terminal">
                <BaseSelectGrupo v-model="formData.id_capacidad_terminal" :options="unidades" label="descripcion"
                    value="id" placeholder="Seleccione una unidad" />
            </FormLabelError>



            <!-- DESCRIPCIÓN -->
            <FormInput v-model="formData.descripcion" label="Descripción de la capacidad" type="textarea" :maxlength="225"
                required :error="formErrors?.descripcion" />
            <!-- Contador -->
            <div class="flex justify-end">
                <span class="text-xs" :class="formData.descripcion.length > 200
                    ? 'text-red-500'
                    : 'text-gray-400'">
                    {{ formData.descripcion.length }}/225
                </span>
            </div>

            <div class="flex gap-2 mt-4">
                <Button :title="isEditing ? 'Guardar cambios' : 'Crear Capacidad'"
                    :loading-title="isEditing ? 'Guardando...' : 'Creando...'" :loading="saving || updating"
                    :disabled="saving || updating" class="!w-full" @click="onSubmit" />

                <Button v-if="isEditing" title="Cancelar" variant="outline"
                    class="bg-red-500 hover:bg-red-600 text-white" @click="onCancel" />
            </div>
        </div>
    </AuthorizationFallback>
</template>
