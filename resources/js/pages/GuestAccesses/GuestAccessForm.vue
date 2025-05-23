<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import FormInput from '@/components/FormInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import FormSelect from '@/components/FormSelect.vue';

interface GuestData {
    id: number;
    first_name: string;
    last_name: string;
    table_number: number;
    total_passes: number;
    available_passes: number;
    event_name: string;
}

const props = defineProps<{
    guestData?: GuestData;
    qrCode: string;
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

const form = useForm({
    qr_code: props.qrCode,
    people_count: 1,
    access_type: 'entry',
    observations: '',
});

const accessTypeOptions = [
    { id: 'entry', name: 'Entrada' },
    { id: 'exit', name: 'Salida' },
];

// Texto del botón según el estado del formulario
const buttonText = computed(() => {
    if (form.processing) {
        return 'Registrando...';
    }
    return 'Registrar Acceso';
});

const submitForm = () => {
    form.post('/guest-accesses', {
        preserveScroll: true,
        onSuccess: () => {
            emit('success', 'Acceso registrado exitosamente');
            form.reset();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Error al registrar el acceso';
            emit('error', errorMessage);
        }
    });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    emit('cancel');
};

// Watch para actualizar el formulario cuando cambia el QR
watch(
    () => props.qrCode,
    (newQrCode) => {
        form.qr_code = newQrCode;
        form.reset('people_count', 'access_type', 'observations');
        form.clearErrors();
    }
);
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-4">
        <!-- Información del invitado -->
        <div v-if="guestData" class="rounded-lg border p-4">
            <h3 class="mb-2 font-semibold">Información del Invitado</h3>
            <p><span class="font-medium">Nombre:</span> {{ guestData.first_name }} {{ guestData.last_name }}</p>
            <p><span class="font-medium">Mesa:</span> {{ guestData.table_number }}</p>
            <p><span class="font-medium">Evento:</span> {{ guestData.event_name }}</p>
            <p><span class="font-medium">Pases disponibles:</span> {{ guestData.available_passes }} de {{ guestData.total_passes }}</p>
        </div>

        <FormInput
            id="people_count"
            v-model="form.people_count"
            :error="form.errors.people_count"
            label="Número de personas"
            type="number"
            min="1"
            :max="guestData?.available_passes"
            required
        />

        <FormSelect
            id="access_type"
            v-model="form.access_type"
            :options="accessTypeOptions"
            :error="form.errors.access_type"
            label="Tipo de acceso"
            required
        />

        <FormTextarea
            id="observations"
            v-model="form.observations"
            :error="form.errors.observations"
            label="Observaciones"
            :rows="3"
        />

        <div class="flex justify-end space-x-3">
            <button
                type="button"
                @click="resetForm"
                class="cursor-pointer rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                Cancelar
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="cursor-pointer rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                {{ buttonText }}
            </button>
        </div>
    </form>
</template>
