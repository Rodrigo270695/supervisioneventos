<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  modelValue: string
  placeholder?: string
  maxLength?: number
  required?: boolean
  autofocus?: boolean
  tabindex?: number
  class?: HTMLAttributes['class']
  inputClass?: HTMLAttributes['class']
  counterClass?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const localValue = ref(props.modelValue || '')
const maxLen = props.maxLength || 8

// Actualizar modelo cuando cambia el valor local
watch(localValue, (newValue) => {
  emits('update:modelValue', newValue)
})

// Actualizar valor local cuando cambia el modelo
watch(() => props.modelValue, (newValue) => {
  if (newValue !== localValue.value) {
    localValue.value = newValue
  }
})

// Contador de caracteres
const charCount = computed(() => {
  return `${localValue.value.length}/${maxLen}`
})

// Permitir solo números
const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value

  // Filtrar para que solo acepte números
  const numericValue = value.replace(/\D/g, '')

  // Limitar la longitud máxima
  if (numericValue.length > maxLen) {
    localValue.value = numericValue.slice(0, maxLen)
  } else {
    localValue.value = numericValue
  }
}

// Prevenir caracteres no numéricos
const handleKeyDown = (event: KeyboardEvent) => {
  // Permitir: backspace, delete, tab, escape, enter y teclas de navegación
  if ([46, 8, 9, 27, 13, 110].indexOf(event.keyCode) !== -1 ||
    // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
    (event.keyCode === 65 && event.ctrlKey === true) ||
    (event.keyCode === 67 && event.ctrlKey === true) ||
    (event.keyCode === 86 && event.ctrlKey === true) ||
    (event.keyCode === 88 && event.ctrlKey === true) ||
    // Permitir: home, end, left, right
    (event.keyCode >= 35 && event.keyCode <= 39)) {
    return
  }

  // Bloquear entrada si no es un número
  if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) &&
      (event.keyCode < 96 || event.keyCode > 105)) {
    event.preventDefault()
  }
}
</script>

<template>
  <div :class="cn('relative w-full', props.class)">
    <input
      type="text"
      :value="localValue"
      @input="handleInput"
      @keydown="handleKeyDown"
      :placeholder="placeholder"
      :maxlength="maxLen"
      :required="required"
      :autofocus="autofocus"
      :tabindex="tabindex"
      autocomplete="off"
      inputmode="numeric"
      pattern="[0-9]*"
      :class="cn(
        'w-full rounded-xl border-0 bg-neutral-light/50 py-3 pl-10 pr-16 text-base text-neutral-dark shadow-sm ring-1 ring-inset ring-neutral-medium/10 transition-all duration-300 placeholder:text-neutral-medium/50 placeholder:text-base placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-inset focus:ring-navy/30 dark:bg-neutral-dark/50 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/80 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-navy/50 dark:placeholder:text-neutral-light/30 sm:pl-12 sm:pr-20 font-sans h-[46px]',
        inputClass
      )"
    />
    <div
      :class="cn(
        'absolute right-4 top-1/2 -translate-y-1/2 select-none rounded-full bg-neutral-light px-2 py-0.5 text-xs font-medium text-neutral-dark/70 dark:bg-neutral-dark/70 dark:text-neutral-light/70',
        counterClass,
        {'text-navy dark:text-navy-light': localValue.length === maxLen}
      )"
    >
      {{ charCount }}
    </div>
  </div>
</template>
