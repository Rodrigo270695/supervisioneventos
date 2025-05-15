<script setup lang="ts">
interface Props {
    justify?: 'start' | 'center' | 'end' | 'between' | 'around' | 'evenly';
    gap?: 'none' | 'xs' | 'sm' | 'md' | 'lg';
    vertical?: boolean;
    wrap?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    justify: 'end',
    gap: 'md',
    vertical: false,
    wrap: false,
});

// Computar las clases del contenedor basadas en props
const containerClasses = {
    // Alineación
    'justify-start': props.justify === 'start',
    'justify-center': props.justify === 'center',
    'justify-end': props.justify === 'end',
    'justify-between': props.justify === 'between',
    'justify-around': props.justify === 'around',
    'justify-evenly': props.justify === 'evenly',

    // Dirección
    'flex-col': props.vertical,
    'flex-row': !props.vertical,

    // Espaciado
    'gap-0': props.gap === 'none',
    'gap-1': props.gap === 'xs',
    'gap-2': props.gap === 'sm',
    'gap-3': props.gap === 'md',
    'gap-4': props.gap === 'lg',

    // Wrap
    'flex-wrap': props.wrap,
    'flex-nowrap': !props.wrap,
};
</script>

<template>
    <div class="flex items-center" :class="containerClasses">
        <slot></slot>
    </div>
</template>
