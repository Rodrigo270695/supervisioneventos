<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

interface Props {
    for?: string;
    required?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
});
</script>

<template>
    <Label :for="props.for" :class="cn('text-foreground mb-2 block text-sm font-medium', props.class)">
        <slot />
        <span v-if="required" class="text-destructive ml-1" aria-hidden="true">*</span>
    </Label>
</template>
