<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  label?: string;
  tooltipPosition?: 'top' | 'right' | 'bottom' | 'left';
  variant?: 'default' | 'primary' | 'accent' | 'success' | 'warning' | 'destructive';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  srOnly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  label: '',
  tooltipPosition: 'top',
  variant: 'default',
  size: 'md',
  disabled: false,
  srOnly: true,
});

const emit = defineEmits(['click']);

// Computar clases basadas en el variant
const buttonClasses = computed(() => {
  const baseClasses = 'transition-colors rounded-md p-1.5 cursor-pointer flex items-center justify-center';

  // Clases específicas de tamaño
  const sizeClasses = {
    sm: 'h-7 w-7',
    md: 'h-8 w-8',
    lg: 'h-9 w-9',
  }[props.size];

  // Clases específicas de variante
  const variantClasses = {
    default: 'bg-transparent hover:bg-muted/60 text-foreground hover:text-foreground/90',
    primary: 'bg-transparent hover:bg-primary/20 text-primary hover:text-primary/90',
    accent: 'bg-transparent hover:bg-accent/20 text-accent hover:text-accent/90',
    success: 'bg-transparent hover:bg-emerald-500/20 text-emerald-600 hover:text-emerald-600/90',
    warning: 'bg-transparent hover:bg-amber-500/20 text-amber-600 hover:text-amber-600/90',
    destructive: 'bg-transparent hover:bg-destructive/10 text-destructive hover:text-destructive/90',
  }[props.variant];

  // Clases específicas de estado
  const stateClasses = props.disabled ? 'opacity-50 cursor-not-allowed' : '';

  return `${baseClasses} ${sizeClasses} ${variantClasses} ${stateClasses}`;
});

// Manejar el clic
const handleClick = (event: MouseEvent) => {
  if (!props.disabled) {
    emit('click', event);
  }
};
</script>

<template>
  <button
    :class="buttonClasses"
    @click="handleClick"
    :disabled="disabled"
    :aria-label="label"
    :data-tooltip-position="tooltipPosition"
    :title="label"
  >
    <slot></slot>
    <span v-if="label && srOnly" class="sr-only">{{ label }}</span>
    <span v-if="label && !srOnly" class="ml-2">{{ label }}</span>
  </button>
</template>
