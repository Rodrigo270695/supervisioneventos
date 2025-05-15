<script setup lang="ts">
import { Plus } from 'lucide-vue-next';

interface Props {
    label?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Crear nuevo',
    disabled: false,
});

const emit = defineEmits(['click']);

const handleClick = (event: MouseEvent) => {
    if (!props.disabled) {
        emit('click', event);
    }
};
</script>

<template>
    <button
        @click="handleClick"
        :disabled="disabled"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring transition-all duration-200 relative overflow-hidden group cursor-pointer"
    >
        <div class="absolute inset-0 w-0 bg-white/20 transition-all duration-300 ease-out group-hover:w-full"></div>
        <Plus class="mr-2 h-4 w-4 relative z-10" />
        <span class="relative z-10">{{ label }}</span>
    </button>
</template>
