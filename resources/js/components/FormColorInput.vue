<script setup lang="ts">
import { cn } from '@/lib/utils';
import FormLabel from '@/components/FormLabel.vue';

interface Props {
  id?: string;
  label?: string;
  modelValue: string;
  error?: string;
  required?: boolean;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
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
    <FormLabel
      v-if="label"
      :for="id"
      :required="required"
    >
      {{ label }}
    </FormLabel>

    <div class="flex items-center space-x-2">
      <input
        :id="id"
        type="color"
        :value="modelValue"
        class="h-10 w-10 rounded-md border border-input bg-background shadow-sm cursor-pointer"
        @input="updateValue"
        :required="required"
      />

      <input
        :value="modelValue"
        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        :class="error ? 'border-destructive ring-destructive/10' : ''"
        readonly
        :title="'Utilice el selector de color para cambiar el valor'"
      />
    </div>

    <p v-if="error" class="mt-1 text-sm text-destructive">
      {{ error }}
    </p>
  </div>
</template>
