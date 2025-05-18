<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <FormSelect
                id="time_type_id"
                label="Tipo de Tiempo"
                v-model="form.time_type_id"
                :error="form.errors.time_type_id"
                :options="timeTypes"
                option-label="name"
                option-value="id"
                required
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FormInput
                    id="start_time"
                    label="Hora de Inicio"
                    type="time"
                    v-model="form.start_time"
                    :error="form.errors.start_time"
                    required
                />

                <FormInput
                    id="end_time"
                    label="Hora de Fin"
                    type="time"
                    v-model="form.end_time"
                    :error="form.errors.end_time"
                />
            </div>

            <FormTextarea
                id="description"
                label="Descripción"
                v-model="form.description"
                :error="form.errors.description"
                :rows="3"
                placeholder="Agrega una descripción o notas adicionales..."
            />
        </div>

        <div class="mt-6 flex justify-end">
            <button
                type="button"
                @click="resetForm"
                class="cursor-pointer mr-3 inline-flex items-center justify-center whitespace-nowrap rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                Cancelar
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                {{ buttonText }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import FormInput from '@/components/FormInput.vue';
import FormSelect from '@/components/FormSelect.vue';
import FormTextarea from '@/components/FormTextarea.vue';

interface TimeType {
    id: number;
    name: string;
}

interface Time {
    id?: number;
    event_id: number;
    time_type_id: number;
    start_time: string;
    end_time: string | null;
    description: string | null;
}

const props = defineProps<{
    time?: Time;
    mode: 'create' | 'edit';
    eventId: number;
    timeTypes: TimeType[];
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Toast global
const { showToast } = useToast();

// Crear formulario basado en el modo
const form = useForm({
    id: props.time?.id ?? null,
    event_id: props.eventId,
    time_type_id: props.time?.time_type_id ?? '',
    start_time: props.time?.start_time ?? '',
    end_time: props.time?.end_time ?? '',
    description: props.time?.description ?? '',
});

// Determinar la URL y método según el modo
const submitForm = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            emit('success', props.mode === 'create' ? 'Tiempo registrado exitosamente' : 'Tiempo actualizado exitosamente');
            if (props.mode === 'create') {
                form.reset();
            }
        }
    };

    if (props.mode === 'create') {
        form.post(route('times.store'), options);
    } else {
        form.put(route('times.update', props.time?.id), options);
    }
};

// Texto del botón según el estado del formulario
const buttonText = computed(() => {
    if (form.processing) {
        return 'Guardando...';
    }
    return props.mode === 'create' ? 'Crear' : 'Actualizar';
});

// Resetear formulario
const resetForm = () => {
    form.reset();
    form.clearErrors();
    emit('cancel');
};

// Watch para actualizar el formulario cuando cambia el modo o el tiempo
watch(
    [() => props.mode, () => props.time],
    () => {
        form.id = props.time?.id ?? null;
        form.event_id = props.eventId;
        form.time_type_id = props.time?.time_type_id ?? '';
        form.start_time = props.time?.start_time ?? '';
        form.end_time = props.time?.end_time ?? '';
        form.description = props.time?.description ?? '';
        form.clearErrors();
    }
);
</script>
