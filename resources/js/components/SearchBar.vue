<script setup lang="ts">
import { debounce } from 'lodash';
import { Search, X } from 'lucide-vue-next';
import { ref, watch, withDefaults, defineProps, defineEmits } from 'vue';

interface Props {
    modelValue: string;
    placeholder?: string;
    debounceTime?: number;
    minWidth?: string;
    autoFocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Buscar...',
    debounceTime: 300,
    minWidth: '200px',
    autoFocus: false,
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

const localValue = ref(props.modelValue);

// Observar cambios en la propiedad modelValue externa
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = newValue;
    },
);

// Observar cambios en el valor local
const debouncedSearch = debounce((value: string) => {
    emit('search', value);
}, props.debounceTime);

watch(localValue, (value) => {
    emit('update:modelValue', value);
    debouncedSearch(value);
});

// Limpiar la búsqueda
const clearSearch = () => {
    localValue.value = '';
    emit('update:modelValue', '');
    emit('clear');
};
</script>

<template>
    <div class="relative flex-1" :style="{ minWidth: minWidth }">
        <!-- Icono de búsqueda -->
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <Search class="h-4 w-4 text-muted-foreground" />
        </div>

        <!-- Campo de búsqueda -->
        <input
            v-model="localValue"
            type="text"
            :placeholder="placeholder"
            class="w-full rounded-md border border-input bg-background/50 py-2.5 pl-10 pr-10 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-ring shadow-sm transition-all duration-200"
            :autofocus="autoFocus"
        />

        <!-- Botón para limpiar -->
        <button
            v-if="localValue"
            @click="clearSearch"
            type="button"
            class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-muted-foreground hover:text-foreground transition-colors duration-150"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
