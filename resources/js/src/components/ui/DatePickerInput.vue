<script setup>
import { computed, ref } from 'vue'
import flatPickr from 'vue-flatpickr-component'
import FormLabelError from './FormLabelError.vue'
import { v4 } from 'uuid'
import { createDatePickerConfig } from '../../utils/datePickerConfig'

const props = defineProps({
  modelValue: {
    required: false,
  },
  label: {
    type: String,
    default: () => '',
  },
  labelClass: {
    type: String,
    default: () => '',
  },
  placeholder: {
    type: String,
    default: () => 'dd/mm/aaaa',
  },
  disabled: {
    type: Boolean,
    default: () => false,
  },
  inputClass: {
    type: String,
    default: () => '',
  },
  error: {
    type: [String, null],
    default: () => null,
  },
  errorClass: {
    type: String,
    default: () => '',
  },
  required: {
    type: Boolean,
    default: () => false,
  },
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

const id = ref(`date-input-${v4()}`)

const dateInputClass = computed(() => [
  'flatpickr-alt-input',
  'block min-h-9 w-full rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 placeholder:text-slate-400 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 read-only:border-slate-200 read-only:bg-slate-50 read-only:text-slate-600 read-only:hover:border-slate-300 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:placeholder:text-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20 dark:read-only:border-slate-700 dark:read-only:bg-slate-800/70 dark:read-only:text-slate-300 dark:disabled:border-slate-700 dark:disabled:bg-slate-900 dark:disabled:text-slate-500 dark:disabled:placeholder:text-slate-500',
  props.error ? 'border-red-300 bg-red-50/40 text-slate-900 hover:border-red-400 focus:border-red-500 focus:ring-red-100 dark:border-red-800 dark:bg-red-950/20 dark:focus:border-red-400 dark:focus:ring-red-950/40' : '',
  props.disabled ? 'cursor-not-allowed' : '',
  props.inputClass || '',
].join(' '))

const datePickerConfig = computed(() => createDatePickerConfig({
  altInputClass: dateInputClass.value,
  placeholder: props.placeholder,
  onReady: (_selectedDates, _dateStr, instance) => {
    if (instance.altInput) {
      instance.altInput.id = id.value
      instance.altInput.disabled = props.disabled
      instance.altInput.readOnly = false
    }
  },
}))

const onDateOpen = (_selectedDates, dateStr, instance) => {
  emit('focus', dateStr || instance?.input?.value || null, instance)
}

const onDateClose = (_selectedDates, dateStr, instance) => {
  emit('blur', dateStr || instance?.input?.value || null, instance)
}
</script>

<template>
  <FormLabelError
    :label="label"
    :label-class="labelClass"
    :error="error"
    :error-class="errorClass"
    :required="required"
  >
    <flat-pickr
      v-model="value"
      :config="datePickerConfig"
      :disabled="disabled"
      @on-open="onDateOpen"
      @on-close="onDateClose"
    />
  </FormLabelError>
</template>
