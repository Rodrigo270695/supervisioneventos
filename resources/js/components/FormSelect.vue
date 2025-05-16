<script setup lang="ts">
interface Option {
    id: number | string;
    name: string;
    [key: string]: any;
}

defineProps<{
    id: string;
    label: string;
    modelValue: string | number;
    options: Option[];
    error?: string;
    required?: boolean;
    valueKey?: string;
    labelKey?: string;
    placeholder?: string;
}>();
</script>

<template>
    <div class="relative">
        <label :for="id" class="block text-sm font-medium text-foreground mb-1">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </label>
        <div class="relative">
            <select
                :id="id"
                :value="modelValue"
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
                class="focus:ring-primary focus:border-primary block w-full rounded-md border border-input bg-background text-foreground shadow-sm px-3 py-2 pr-10 sm:text-sm transition-colors duration-150 appearance-none"
                :class="{ 'border-destructive': error }"
                :required="required"
            >
                <option disabled value="" :selected="modelValue === ''">
                    {{ placeholder || 'Seleccione una opción' }}
                </option>
                <option v-for="option in options" :key="option.id" :value="option[valueKey || 'id']">
                    {{ option[labelKey || 'name'] }}
                </option>
            </select>
            <!-- Icono de flecha -->
            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <p v-if="error" class="mt-1 text-sm text-destructive">{{ error }}</p>
    </div>
</template>
