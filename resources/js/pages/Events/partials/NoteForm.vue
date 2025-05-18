<template>
    <form @submit.prevent="submitForm" class="space-y-4">
        <FormTextarea
            id="description"
            label="Descripción"
            v-model="form.description"
            :error="form.errors.description"
            :rows="3"
            required
            placeholder="Ingresa la descripción de la nota..."
        />

        <FormInput
            id="amount"
            label="Monto"
            type="number"
            step="0.01"
            min="0"
            v-model="form.amount"
            :error="form.errors.amount"
            required
            placeholder="0.00"
        />

        <div class="flex justify-end gap-3 mt-6">
            <button
                type="button"
                @click="$emit('cancel')"
                class="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                Cancelar
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                {{ mode === 'create' ? 'Crear' : 'Actualizar' }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import FormInput from '@/components/FormInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';

interface Note {
    id: number;
    event_id: number;
    description: string;
    amount: number;
}

interface Props {
    mode: 'create' | 'edit';
    eventId: number;
    note?: Note;
}

const props = withDefaults(defineProps<Props>(), {
    note: undefined
});

const emit = defineEmits(['success', 'cancel']);

// Formulario
const form = useForm({
    event_id: props.eventId,
    description: props.note?.description ?? '',
    amount: props.note?.amount ?? ''
});

// Enviar formulario
const submitForm = () => {
    if (props.mode === 'create') {
        form.post(route('notes.store'), {
            onSuccess: () => {
                form.reset();
                emit('success');
            }
        });
    } else if (props.note) {
        form.put(route('notes.update', props.note.id), {
            onSuccess: () => emit('success')
        });
    }
};
</script>
