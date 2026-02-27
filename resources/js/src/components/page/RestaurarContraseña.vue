<script setup>
import { ref, watch } from "vue";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/outline";
import Slider from "../ui/Slider.vue";
import Button from "../ui/Button.vue";
import useHttpRequest from "../../composables/useHttpRequest";
import useValidation from "../../composables/useValidation";
import useModalToast from "../../composables/useModalToast";
import AuthorizationFallback from './AuthorizationFallback.vue';
import * as yup from "yup";

const props = defineProps({
    show: { type: Boolean, default: false },
    user: { type: Object, required: true },
});

const emit = defineEmits(["hide"]);

const { update, updating } = useHttpRequest("/usersRestaurarPassword");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const formData = ref({
    password: "",
    confirm_password: "",
});

const formErrors = ref({});
const showPassword = ref(false);
const showConfirm = ref(false);

watch(() => props.show, val => {
    if (val) {
        formData.value = { password: "", confirm_password: "" };
        formErrors.value = {};
    }
});

const schema = yup.object().shape({
    password: yup.string()
        .required("La contraseña es obligatoria")
        .min(8, "Debe tener mínimo 8 caracteres"),
    confirm_password: yup.string()
        .oneOf([yup.ref("password")], "Las contraseñas no coinciden")
        .required("Debe confirmar la contraseña"),
});

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);

    if (!validated) {
        formErrors.value = errors;
        return;
    }
    const response = await update(props.user?.id, {
        password: formData.value.password,
    });

    if (response?.user?.id) {
        showToast("Contraseña restaurada correctamente.");
        emit("hide");
    }
};
</script>

<template>

    <Slider :show="show" title="Restaurar contraseña para el usuario" @hide="emit('hide')" :key="user?.id">
        <AuthorizationFallback :permissions="['todo-acceso-usuarios', 'editar-usuarios']">
            <div class="space-y-6 mt-4">

                <!-- Datos del usuario -->
                <div class="p-4 border rounded-md bg-gray-50 dark:bg-gray-800 dark:border-gray-700 
                        text-gray-700 dark:text-gray-200">
                    <p class="font-semibold text-lg">
                        {{ user.name }} {{ user.apellido_paterno }} {{ user.apellido_materno }}
                    </p>
                    <p class="text-sm mt-1">
                        DNI: <span class="font-medium">{{ user.dni }}</span>
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <div class="relative w-full">
                        <input v-model="formData.password" :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password" placeholder="Nueva contraseña" class="w-full p-3 rounded-md border 
                        bg-gray-50 dark:bg-gray-800 
                        border-gray-300 dark:border-gray-600 
                        text-gray-700 dark:text-gray-100 
                        placeholder-gray-400 dark:placeholder-gray-500
                        focus:ring-2 focus:ring-cetpro-light focus:outline-none" />

                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300"
                            @click="showPassword = !showPassword">
                            <EyeIcon v-if="!showPassword" class="w-6 h-6" />
                            <EyeSlashIcon v-else class="w-6 h-6" />
                        </button>
                    </div>

                    <p v-if="formErrors.password" class="text-red-500 text-sm mt-1">
                        {{ formErrors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <div class="relative w-full">
                        <input v-model="formData.confirm_password" :type="showConfirm ? 'text' : 'password'"
                            autocomplete="new-password" placeholder="Repetir contraseña" class="w-full p-3 rounded-md border 
                        bg-gray-50 dark:bg-gray-800 
                        border-gray-300 dark:border-gray-600 
                        text-gray-700 dark:text-gray-100 
                        placeholder-gray-400 dark:placeholder-gray-500
                        focus:ring-2 focus:ring-cetpro-light focus:outline-none" />

                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300"
                            @click="showConfirm = !showConfirm">
                            <EyeIcon v-if="!showConfirm" class="w-6 h-6" />
                            <EyeSlashIcon v-else class="w-6 h-6" />
                        </button>
                    </div>

                    <p v-if="formErrors.confirm_password" class="text-red-500 text-sm mt-1">
                        {{ formErrors.confirm_password }}
                    </p>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 mt-6">
                    <Button title="Restaurar contraseña" :loading="updating" :disabled="updating" class="w-full h-10"
                        @click="onSubmit" />

                    <Button title="Cancelar" variant="outline" @click="emit('hide')" class="bg-red-500 text-white hover:bg-red-600 
                    dark:bg-red-600 dark:hover:bg-red-700 h-10 w-24" />
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>

</template>
