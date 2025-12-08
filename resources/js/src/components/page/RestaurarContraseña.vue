<script setup>
import { ref, watch } from "vue";
import Slider from "../ui/Slider.vue";
import Button from "../ui/Button.vue";
import useHttpRequest from "../../composables/useHttpRequest";
import useValidation from "../../composables/useValidation";
import useModalToast from "../../composables/useModalToast";
import * as yup from "yup";

const props = defineProps({
    show: { type: Boolean, default: false },
    userId: { type: Number, required: true },
});

const emit = defineEmits(["hide"]);

const { update, updating } = useHttpRequest("/users");
const { runYupValidation } = useValidation();
const { showToast } = useModalToast();

const formData = ref({
    password: "",
    confirm_password: "",
});

const formErrors = ref({});

watch(
    () => props.show,
    (val) => {
        if (val) {
            formData.value = { password: "", confirm_password: "" };
            formErrors.value = {};
        }
    }
);

const schema = yup.object().shape({
    password: yup
        .string()
        .required("La contraseña es obligatoria")
        .min(8, "Debe tener mínimo 8 caracteres"),
    confirm_password: yup
        .string()
        .oneOf([yup.ref("password")], "Las contraseñas no coinciden")
        .required("Debe confirmar la contraseña"),
});

const onSubmit = async () => {
    const { validated, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        return;
    }

    const response = await update(props.userId, {
        password: formData.value.password,
    });

    if (response?.id) {
        showToast("Contraseña actualizada correctamente.");
        emit("hide");
    }
};
</script>

<template>
    <Slider 
        :show="show" 
        title="Restaurar Contraseña" 
        @hide="emit('hide')" 
        :key="userId"
    >
        <div class="space-y-4 mt-4">

            <!-- Password -->
            <div>
                <input
                    v-model="formData.password"
                    type="password"
                    autocomplete="off"
                    autocorrect="off"
                    spellcheck="false"
                    placeholder="Nueva contraseña"
                    class="bg-gray-50 border border-gray-300 rounded-sm w-full p-2
                           focus:ring-2 focus:ring-cetpro-light outline-none"
                />
                <p v-if="formErrors.password" class="text-red-500 text-sm mt-1">
                    {{ formErrors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <input
                    v-model="formData.confirm_password"
                    type="password"
                    autocomplete="off"
                    autocorrect="off"
                    spellcheck="false"
                    placeholder="Repetir contraseña"
                    class="bg-gray-50 border border-gray-300 rounded-sm w-full p-2
                           focus:ring-2 focus:ring-cetpro-light outline-none"
                />
                <p v-if="formErrors.confirm_password" class="text-red-500 text-sm mt-1">
                    {{ formErrors.confirm_password }}
                </p>
            </div>

            <div class="flex gap-3 mt-6">
                <Button 
                    title="Guardar" 
                    :loading="updating" 
                    :disabled="updating"
                    class="w-full h-10"
                    @click="onSubmit" 
                />

                <Button 
                    title="Cancelar" 
                    variant="outline"
                    @click="emit('hide')"
                    class="bg-red-500 text-white hover:bg-red-600 h-10 w-24"
                />
            </div>
        </div>
    </Slider>
</template>
