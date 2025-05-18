<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <FormSelect
                id="host_type_id"
                label="Tipo de Anfitrión"
                v-model="form.host_type_id"
                :error="form.errors.host_type_id"
                :options="hostTypes"
                option-label="name"
                option-value="id"
                required
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FormInput
                    id="nombres"
                    label="Nombres"
                    v-model="form.nombres"
                    :error="form.errors.nombres"
                    :maxlength="100"
                    required
                /> 

                <FormInput
                    id="apellidos"
                    label="Apellidos"
                    v-model="form.apellidos"
                    :error="form.errors.apellidos"
                    :maxlength="100"
                    required
                />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FormInput
                    id="dni"
                    label="DNI"
                    v-model="form.dni"
                    :error="form.errors.dni"
                    :maxlength="8"
                    required
                />

                <FormInput
                    id="edad"
                    label="Edad"
                    type="number"
                    v-model="form.edad"
                    :error="form.errors.edad"
                    min="18"
                    max="100"
                />
            </div>

            <FormInput
                id="correo"
                label="Correo Electrónico"
                type="email"
                v-model="form.correo"
                :error="form.errors.correo"
                :maxlength="255"
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

interface HostType {
    id: number;
    name: string;
}

interface Host {
    id?: number;
    event_id: number;
    host_type_id: number;
    nombres: string;
    apellidos: string;
    dni: string;
    edad: number;
    correo: string;
}

const props = defineProps<{
    host?: Host;
    mode: 'create' | 'edit';
    eventId: number;
    hostTypes: HostType[];
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Toast global
const { showToast } = useToast();

// Crear formulario basado en el modo
const form = useForm({
    id: props.host?.id ?? null,
    event_id: props.eventId,
    host_type_id: props.host?.host_type_id ?? '',
    nombres: props.host?.nombres ?? '',
    apellidos: props.host?.apellidos ?? '',
    dni: props.host?.dni ?? '',
    edad: props.host?.edad ?? '',
    correo: props.host?.correo ?? '',
});

// Determinar la URL y método según el modo
const submitForm = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            emit('success', props.mode === 'create' ? 'Anfitrión registrado exitosamente' : 'Anfitrión actualizado exitosamente');
            if (props.mode === 'create') {
                form.reset();
            }
        }
    };

    if (props.mode === 'create') {
        form.post(route('hosts.store'), options);
    } else {
        form.put(route('hosts.update', props.host?.id), options);
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

// Watch para actualizar el formulario cuando cambia el modo o el anfitrión
watch(
    [() => props.mode, () => props.host],
    () => {
        form.id = props.host?.id ?? null;
        form.event_id = props.eventId;
        form.host_type_id = props.host?.host_type_id ?? '';
        form.nombres = props.host?.nombres ?? '';
        form.apellidos = props.host?.apellidos ?? '';
        form.dni = props.host?.dni ?? '';
        form.edad = props.host?.edad ?? '';
        form.correo = props.host?.correo ?? '';
        form.clearErrors();
    }
);
</script>
