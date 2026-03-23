import { Spanish } from 'flatpickr/dist/l10n/es.js'

export const formatDateMask = (value = '') => {
  const digits = String(value).replace(/\D/g, '').slice(0, 8)

  if (digits.length <= 2) return digits
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`

  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`
}

const isValidDateParts = (day, month, year) => {
  const parsed = new Date(year, month - 1, day)

  return (
    parsed.getFullYear() === year &&
    parsed.getMonth() === month - 1 &&
    parsed.getDate() === day
  )
}

export const attachDateMask = (instance, options = {}) => {
  const input = instance?.altInput

  if (!input) return

  const placeholder = options.placeholder || 'dd/mm/aaaa'
  input.placeholder = placeholder

  if (input.dataset.dateMaskBound === '1') return

  const syncMask = () => {
    input.value = formatDateMask(input.value)
  }

  const syncInstanceValue = () => {
    const digits = input.value.replace(/\D/g, '').slice(0, 8)
    input.value = formatDateMask(digits)

    if (!digits.length) {
      instance.clear()
      return
    }

    if (digits.length !== 8) return

    const day = Number(digits.slice(0, 2))
    const month = Number(digits.slice(2, 4))
    const year = Number(digits.slice(4, 8))

    if (!isValidDateParts(day, month, year)) return

    const isoDate = `${String(year).padStart(4, '0')}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    instance.setDate(isoDate, true, 'Y-m-d')
  }

  input.addEventListener('input', syncMask)
  input.addEventListener('blur', syncInstanceValue)
  input.dataset.dateMaskBound = '1'
}

export const createDatePickerConfig = (options = {}) => ({
  locale: Spanish,
  dateFormat: 'Y-m-d',
  altInput: true,
  altInputClass: options.altInputClass || 'flatpickr-alt-input',
  altFormat: 'd/m/Y',
  allowInput: true,
  disableMobile: true,
  monthSelectorType: 'static',
  onReady: (selectedDates, dateStr, instance) => {
    attachDateMask(instance, { placeholder: options.placeholder })
    options.onReady?.(selectedDates, dateStr, instance)
  },
  onChange: (selectedDates, dateStr, instance) => {
    attachDateMask(instance, { placeholder: options.placeholder })
    options.onChange?.(selectedDates, dateStr, instance)
  },
})
