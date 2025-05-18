<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormInput
                    id="name"
                    label="Nombre completo"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                />

                <FormInput
                    id="dni"
                    label="DNI"
                    v-model="form.dni"
                    :error="form.errors.dni"
                    :maxlength="8"
                    required
                    @keypress="onlyNumeric"
                />

                <FormInput
                    id="email"
                    label="Correo electrónico (opcional)"
                    type="email"
                    v-model="form.email"
                    :error="form.errors.email"
                />
            </div>

            <FormSelect
                id="event_id"
                label="Evento asignado"
                v-model="form.event_id"
                :options="events"
                :error="form.errors.event_id"
                required
                placeholder="Seleccione un evento"
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
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import FormInput from '@/components/FormInput.vue';
import FormSelect from '@/components/FormSelect.vue';

interface SecurityUser {
    id?: number;
    name: string;
    dni: string;
    email?: string;
    events?: Event[];
}

interface Event {
    id: number;
    name: string;
}

interface Props {
    securityUser?: SecurityUser;
    events: Event[];
    mode: 'create' | 'edit';
}

const props = defineProps<Props>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Crear formulario basado en el modo
const form = useForm({
    id: props.securityUser?.id ?? null,
    name: props.securityUser?.name ?? '',
    dni: props.securityUser?.dni ?? '',
    email: props.securityUser?.email ?? '',
    event_id: props.securityUser?.events?.[0]?.id ?? '',
    // El password será el mismo DNI, se maneja en el backend
});

// Validación para input numérico
const onlyNumeric = (event: KeyboardEvent) => {
    if ([46, 8, 9, 27, 13, 110].indexOf(event.keyCode) !== -1 ||
        (event.keyCode === 65 && event.ctrlKey === true) ||
        (event.keyCode === 67 && event.ctrlKey === true) ||
        (event.keyCode === 86 && event.ctrlKey === true) ||
        (event.keyCode === 88 && event.ctrlKey === true) ||
        (event.keyCode >= 35 && event.keyCode <= 39)) {
        return;
    }
    if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) &&
        (event.keyCode < 96 || event.keyCode > 105)) {
        event.preventDefault();
    }
};

// Determinar la URL y método según el modo
const submitForm = () => {
    if (props.mode === 'create') {
        form.post(route('security.store'), {
            onSuccess: () => {
                emit('success', 'Personal de seguridad registrado exitosamente');
                form.reset();
            },
            onError: () => emit('error', 'Error al registrar el personal de seguridad'),
        });
    } else {
        form.put(route('security.update', props.securityUser?.id), {
            onSuccess: () => emit('success', 'Personal de seguridad actualizado exitosamente'),
            onError: () => emit('error', 'Error al actualizar el personal de seguridad'),
        });
    }
};

// Texto del botón según el estado del formulario
const buttonText = computed(() => {
    if (form.processing) {
        return 'Guardando...';
    }
    return props.mode === 'create' ? 'Registrar' : 'Actualizar';
});

// Resetear formulario
const resetForm = () => {
    form.reset();
    form.clearErrors();
    emit('cancel');
};
</script>
