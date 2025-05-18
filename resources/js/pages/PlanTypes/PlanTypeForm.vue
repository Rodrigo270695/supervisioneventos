<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import FormInput from '@/components/FormInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';

interface PlanType {
    id?: number;
    name: string;
    description: string | null;
}

const props = defineProps<{
    planType?: PlanType;
    mode: 'create' | 'edit';
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Toast global
const { showToast } = useToast();

// Crear formulario basado en el modo
const form = useForm({
    id: props.planType?.id ?? null,
    name: props.planType?.name ?? '',
    description: props.planType?.description ?? '',
});

// Determinar la URL y método según el modo
const submitForm = () => {
    if (props.mode === 'create') {
        form.post(route('plan-types.store'), {
            onSuccess: () => {
                emit('success', 'Tipo de plano creado exitosamente');
                form.reset();
            }
        });
    } else {
        form.put(route('plan-types.update', props.planType?.id), {
            onSuccess: () => {
                emit('success', 'Tipo de plano actualizado exitosamente');
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

// Watch para actualizar el formulario cuando cambia el modo o el tipo de plano
watch(
    [() => props.mode, () => props.planType],
    () => {
        // Reiniciar el formulario
        form.id = props.planType?.id ?? null;
        form.name = props.planType?.name ?? '';
        form.description = props.planType?.description ?? '';
        form.clearErrors();
    }
);
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <FormInput
                id="name"
                label="Nombre"
                v-model="form.name"
                :error="form.errors.name"
                :maxlength="30"
                required
            />

            <FormTextarea
                id="description"
                label="Descripción"
                v-model="form.description"
                :error="form.errors.description"
                :rows="3"
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
