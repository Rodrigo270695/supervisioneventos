<script setup lang="ts">
import FormLabel from '@/components/FormLabel.vue';
import { cn } from '@/lib/utils';

interface Props {
    id?: string;
    label?: string;
    modelValue: string | number;
    placeholder?: string;
    error?: string;
    required?: boolean;
    type?: string;
    maxlength?: number;
    class?: string;
    inputClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div :class="['space-y-1', props.class]">
        <FormLabel v-if="label" :for="id" :required="required">
            {{ label }}
        </FormLabel>

        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :maxlength="maxlength"
            :placeholder="placeholder"
            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            :class="error ? 'border-destructive ring-destructive/10' : ''"
            @input="updateValue"
            :required="required"
        />

        <p v-if="error" class="text-destructive mt-1 text-sm">
            {{ error }}
        </p>
    </div>
</template>
