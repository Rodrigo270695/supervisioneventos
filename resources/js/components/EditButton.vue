<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import ActionButton from './ActionButton.vue';

interface Props {
    size?: 'sm' | 'md' | 'lg';
    disabled?: boolean;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    disabled: false,
    label: 'Editar',
});

const emit = defineEmits(['click']);

const handleClick = (event: MouseEvent) => {
    emit('click', event);
};
</script>

<template>
    <ActionButton :variant="'accent'" :size="size" :disabled="disabled" :label="label" @click="handleClick">
        <Pencil class="h-4 w-4" />
    </ActionButton>
</template>
