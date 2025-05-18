<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Información de capacidad -->
        <div class="bg-muted/50 rounded-lg p-4 mb-4">
            <div class="flex items-center gap-2 text-sm">
                <Users class="h-4 w-4 text-primary" />
                <span class="text-muted-foreground">
                    Capacidad restante: <strong>{{ remainingCapacity }}</strong> personas
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <FormInput
                id="first_name"
                label="Nombre"
                v-model="form.first_name"
                :error="form.errors.first_name"
                placeholder="Ingrese el nombre"
                required
            />

            <!-- Apellido -->
            <FormInput
                id="last_name"
                label="Apellido"
                v-model="form.last_name"
                :error="form.errors.last_name"
                placeholder="Ingrese el apellido"
                required
            />

            <!-- DNI -->
            <FormInput
                id="dni"
                label="DNI"
                v-model="form.dni"
                :error="form.errors.dni"
                placeholder="Ingrese el DNI"
                :maxlength="8"
                required
            />

            <!-- Número de Mesa -->
            <FormInput
                id="table_number"
                label="Número de Mesa"
                type="number"
                v-model="form.table_number"
                :error="form.errors.table_number"
                placeholder="Ingrese el número de mesa"
                min="1"
                required
            />

            <!-- Pases -->
            <div>
                <FormInput
                    id="passes"
                    label="Número de Pases"
                    type="number"
                    v-model="form.passes"
                    :error="form.errors.passes"
                    placeholder="Ingrese el número de pases"
                    min="1"
                    :max="Math.min(10, remainingCapacity)"
                    required
                />
                <p class="mt-1 text-sm text-muted-foreground">
                    Máximo {{ Math.min(10, remainingCapacity) }} pases por invitado
                </p>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end gap-3">
            <button
                type="button"
                @click="$emit('cancel')"
                class="cursor-pointer mr-3 inline-flex items-center justify-center whitespace-nowrap rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                :disabled="form.processing"
            >
                Cancelar
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                {{ mode === 'create' ? 'Registrar' : 'Actualizar' }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import FormInput from '@/components/FormInput.vue';
import { computed } from 'vue';
import { Users } from 'lucide-vue-next';

interface Guest {
    id?: number;
    event_id: number;
    first_name: string;
    last_name: string;
    dni: string;
    table_number: number;
    passes: number;
    used_passes?: number;
    qr_code?: string;
}

interface Props {
    mode: 'create' | 'edit';
    eventId: number;
    guest?: Guest | null;
    event: {
        capacity: number;
        totalGuests: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    guest: null
});

const emit = defineEmits(['success', 'cancel']);

// Calcular capacidad restante
const remainingCapacity = computed(() => {
    return props.event.capacity - props.event.totalGuests;
});

// Inicializar formulario
const form = useForm({
    event_id: props.eventId,
    first_name: props.guest?.first_name || '',
    last_name: props.guest?.last_name || '',
    dni: props.guest?.dni || '',
    table_number: props.guest?.table_number || 1,
    passes: props.guest?.passes || 1,
});

// Enviar formulario
const submit = () => {
    if (props.mode === 'create') {
        form.post(route('guests.store', props.eventId), {
            onSuccess: () => {
                form.reset();
                emit('success');
            },
        });
    } else if (props.guest?.id) {
        form.put(route('guests.update', props.guest.id), {
            onSuccess: () => emit('success'),
        });
    }
};
</script>
