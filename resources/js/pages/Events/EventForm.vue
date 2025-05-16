<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue';
import FormInput from '@/components/FormInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import FormSelect from '@/components/FormSelect.vue';

interface Event {
    id?: number;
    event_type_id: number;
    name: string;
    capacity: number;
    event_date: string;
    start_time: string;
    end_time: string | null;
    address: string;
    description: string | null;
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
}

interface EventType {
    id: number;
    name: string;
    color: string;
}

const props = defineProps<{
    event?: Event;
    eventTypes: EventType[];
    mode: 'create' | 'edit';
    selectedDate?: string;
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Función para formatear la hora en formato HH:MM
const formatTimeString = (timeStr: string | null | undefined): string => {
    if (!timeStr) return '';

    // Si ya está en formato HH:MM, devolverlo sin cambios
    if (/^\d{2}:\d{2}$/.test(timeStr)) return timeStr;

    try {
        // Intentar convertir otras posibles representaciones de hora
        if (timeStr.includes('T')) {
            // Si viene con formato ISO
            const date = new Date(timeStr);
            return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
        } else if (timeStr.includes(' ')) {
            // Si tiene formato de hora con segundos o espacios
            const parts = timeStr.split(' ')[1].split(':');
            return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
        } else if (timeStr.includes(':')) {
            // Si tiene formato HH:MM:SS
            const parts = timeStr.split(':');
            return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
        }
    } catch (e) {
        console.error('Error formateando la hora:', e);
    }

    return timeStr; // Devolver el original si no se pudo formatear
};

// Crear formulario basado en el modo
const form = useForm({
    id: props.event?.id ?? null,
    event_type_id: props.event?.event_type_id ?? '',
    name: props.event?.name ?? '',
    capacity: props.event?.capacity ?? '',
    event_date: props.selectedDate || props.event?.event_date || '',
    start_time: formatTimeString(props.event?.start_time) ?? '',
    end_time: formatTimeString(props.event?.end_time) ?? '',
    address: props.event?.address ?? '',
    description: props.event?.description ?? '',
    status: props.event?.status ?? 'scheduled',
});

const statusOptions = [
    { id: 'scheduled', name: 'Programado' },
    { id: 'in_progress', name: 'En Progreso' },
    { id: 'completed', name: 'Completado' },
    { id: 'cancelled', name: 'Cancelado' },
];

// Determinar la URL y método según el modo
const submitForm = () => {
    // Asegurar que las horas estén en formato correcto antes de enviar
    form.start_time = formatTimeString(form.start_time);
    if (form.end_time) {
        form.end_time = formatTimeString(form.end_time);
    }

    if (props.mode === 'create') {
        form.post(route('events.store'), {
            onSuccess: () => {
                emit('success', 'Evento creado exitosamente');
                form.reset();
                // Recargar la página para actualizar el calendario
                window.location.reload();
            }
        });
    } else {
        form.put(route('events.update', props.event?.id), {
            onSuccess: () => {
                emit('success', 'Evento actualizado exitosamente');
                // Recargar la página para actualizar el calendario
                window.location.reload();
            }
        });
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

// Watch para actualizar el formulario cuando cambia el modo, el evento o la fecha seleccionada
watch(
    [() => props.mode, () => props.event, () => props.selectedDate],
    () => {
        form.id = props.event?.id ?? null;
        form.event_type_id = props.event?.event_type_id ?? '';
        form.name = props.event?.name ?? '';
        form.capacity = props.event?.capacity ?? '';
        // Usamos la fecha seleccionada si existe, sino la fecha del evento, sino vacío
        form.event_date = props.selectedDate || props.event?.event_date || '';
        form.start_time = formatTimeString(props.event?.start_time) ?? '';
        form.end_time = formatTimeString(props.event?.end_time) ?? '';
        form.address = props.event?.address ?? '';
        form.description = props.event?.description ?? '';
        form.status = props.event?.status ?? 'scheduled';
        form.clearErrors();
    }
);
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormSelect
                    id="event_type_id"
                    label="Tipo de Evento"
                    v-model="form.event_type_id"
                    :options="eventTypes"
                    :error="form.errors.event_type_id"
                    required
                    placeholder="Seleccione un tipo de evento"
                />

                <FormInput
                    id="name"
                    label="Nombre"
                    v-model="form.name"
                    :error="form.errors.name"
                    :maxlength="30"
                    required
                />

                <FormInput
                    id="capacity"
                    label="Capacidad"
                    type="number"
                    v-model="form.capacity"
                    :error="form.errors.capacity"
                    required
                />

                <FormInput
                    id="event_date"
                    label="Fecha"
                    type="date"
                    v-model="form.event_date"
                    :error="form.errors.event_date"
                    required
                />

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

            <FormInput
                id="address"
                label="Dirección"
                v-model="form.address"
                :error="form.errors.address"
                required
            />

            <FormTextarea
                id="description"
                label="Descripción"
                v-model="form.description"
                :error="form.errors.description"
                :rows="3"
            />

            <FormSelect
                id="status"
                label="Estado"
                v-model="form.status"
                :options="statusOptions"
                :error="form.errors.status"
                required
                placeholder="Seleccione un estado"
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
