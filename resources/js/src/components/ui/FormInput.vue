<script setup>
import { computed, ref } from 'vue'
import FormLabelError from './FormLabelError.vue'
import { v4 } from 'uuid'

const props = defineProps({
  modelValue: {
    required: false,
  },
  type: {
    type: String,
    default: () => 'text',
  },
  label: {
    type: String,
  },
  labelClass: {
    type: String,
    default: () => '',
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
  focus: {
    type: Boolean,
    default: () => false,
  },
  required: {
    type: Boolean,
    default: () => false,
  },
  step: {
    type: [String, Number, null],
    default: () => null,
  },
  vModelOnBlur: {
    type: Boolean,
    default: () => false,
  },
  uppercase: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const value = computed({
  get: () => props.modelValue,
  set: (val) => {
    const newVal = props.uppercase ? val?.toUpperCase() : val
    if (!props.vModelOnBlur) emit('update:modelValue', newVal)
  },
})

const id = ref(`input-${v4()}`)

const onKeydown = (event) => {
  if (props.type !== 'number') return
  disableKeys(event, ['e', 'E'])
}

const onFocus = (event) => {
  emit('focus', event?.target?.value ? event.target.value : null, event)
}

const onBlur = (event) => {
  const val = event?.target?.value ? event.target.value : null
  const newVal = props.uppercase ? val?.toUpperCase() : val
  emit('blur', newVal, event)
  if (props.vModelOnBlur) emit('update:modelValue', newVal)
}

const disableKeys = (event, keys = ['e', 'E', '+', '-']) => {
  if (!keys) return
  keys.includes(event.key) && event.preventDefault()
}
</script>

<template>
  <FormLabelError
    :label="label"
    :label-class="labelClass"
    :error="error"
    error-class="errorClass"
    :required="required"
  >
    <input
      v-model="value"
      v-focus.select="focus"
      :id="id"
      :type="type"
      :step="step ? step : type === 'number' ? 'any' : null"
      class="block min-h-9 w-full rounded-[3px] border border-slate-300 bg-white px-3 py-1.5 text-sm leading-5 text-slate-800 outline-none transition-colors duration-150 placeholder:text-slate-400 hover:border-cetpro/45 focus:border-cetpro focus:ring-2 focus:ring-cetpro/15 read-only:border-slate-200 read-only:bg-slate-50 read-only:text-slate-600 read-only:hover:border-slate-300 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:placeholder:text-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400 dark:hover:border-cetpro-light/55 dark:focus:border-cetpro-light dark:focus:ring-cetpro-light/20 dark:read-only:border-slate-700 dark:read-only:bg-slate-800/70 dark:read-only:text-slate-300 dark:disabled:border-slate-700 dark:disabled:bg-slate-900 dark:disabled:text-slate-500 dark:disabled:placeholder:text-slate-500"
      :placeholder="placeholder ? placeholder : ''"
      :class="[
        error ? 'border-red-300 bg-red-50/40 text-slate-900 hover:border-red-400 focus:border-red-500 focus:ring-red-100 dark:border-red-800 dark:bg-red-950/20 dark:focus:border-red-400 dark:focus:ring-red-950/40' : '',
        disabled ? 'cursor-not-allowed' : '',
        inputClass ? inputClass : '',
        uppercase ? 'uppercase' : '',
      ]"
      :disabled="disabled"
      @keydown="onKeydown"
      @focus="onFocus"
      @blur="onBlur"
    />
  </FormLabelError>
</template>
