<script setup lang="ts">
import { cn } from '@/lib/utils';
import FormLabel from '@/components/FormLabel.vue';

interface Props {
  id?: string;
  label?: string;
  modelValue: string;
  placeholder?: string;
  error?: string;
  required?: boolean;
  rows?: number;
  class?: string;
  textareaClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  rows: 3,
  required: false,
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
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

    <textarea
      :id="id"
      :value="modelValue"
      :rows="rows"
      :placeholder="placeholder"
      :class="cn(
        'w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        error ? 'border-destructive ring-destructive/10' : '',
        textareaClass
      )"
      @input="updateValue"
      :required="required"
    ></textarea>

    <p v-if="error" class="mt-1 text-sm text-destructive">
      {{ error }}
    </p>
  </div>
</template>
