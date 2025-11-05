<script setup>
import { computed, ref } from "vue";
import FormLabelError from "./FormLabelError.vue";
import { v4 } from "uuid";

const props = defineProps({
  modelValue: {
    required: false,
  },
  label: {
    type: String,
  },
  labelClass: {
    type: String,
    default: () => "",
  },
  placeholder: {
    type: String,
  },
  disabled: {
    type: Boolean,
    default: () => false,
  },
  inputClass: {
    type: String,
    default: () => "",
  },
  error: {
    type: [String, null],
    default: () => null,
  },
  errorClass: {
    type: String,
    default: () => "",
  },
  focus: {
    type: Boolean,
    default: () => false,
  },
  required: {
    type: Boolean,
    default: () => false,
  },
  rows: {
    type: Number,
    default: () => 3,
  },
  cols: {
    type: Number,
    default: () => 30,
  },
  vModelOnBlur: {
    type: Boolean,
    default: () => false,
  },
});

const emit = defineEmits(["update:modelValue", "focus", "blur"]);

const value = computed({
  get: () => props.modelValue,
  set: (value) => {
    if (!props.vModelOnBlur) emit("update:modelValue", value);
  },
});
const id = ref(`textarea-${v4()}`);

const onFocus = (event) => {
  emit("focus", event?.target?.value ? event.target.value : null, event);
};

const onBlur = (event) => {
  const val = event?.target?.value ? event.target.value : null;
  emit("blur", val, event);
  if (props.vModelOnBlur) emit("update:modelValue", val);
};
</script>

<template>
  <FormLabelError
    :label="label"
    :label-class="labelClass"
    :error="error"
    error-class="errorClass"
    :required="required"
  >
    <textarea
      v-model="value"
      :id="id"
      :rows="rows"
      :cols="cols"
      class="bg-gray-50 border border-gray-300 sm:text-sm rounded-sm block w-full p-2.5
       dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-300
       dark:text-gray-200 focus:ring-0.5 focus:ring-active focus:border-active
       dark:focus:ring-active dark:focus:border-active outline-none"
      :placeholder="placeholder ? placeholder : ''"
      :class="[disabled ? 'cursor-not-allowed' : '', inputClass ? inputClass : '']"
      :disabled="disabled"
      @focus="onFocus"
      @blur="onBlur"
    ></textarea>
  </FormLabelError>
</template>