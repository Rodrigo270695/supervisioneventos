<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import ActionButton from './ActionButton.vue';

interface Props {
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  label?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  disabled: false,
  label: 'Eliminar',
});

const emit = defineEmits(['click']);

const handleClick = (event: MouseEvent) => {
  emit('click', event);
};
</script>

<template>
  <ActionButton
    variant="destructive"
    :size="size"
    :disabled="disabled"
    :label="label"
    @click="handleClick"
  >
    <Trash2 class="h-4 w-4" />
  </ActionButton>
</template>
