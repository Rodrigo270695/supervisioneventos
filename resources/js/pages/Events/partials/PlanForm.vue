<template>
    <form @submit.prevent="submitForm">
        <div class="space-y-4">
            <FormSelect
                id="plan_type_id"
                label="Tipo de Plano"
                v-model="form.plan_type_id"
                :error="form.errors.plan_type_id"
                :options="planTypes"
                option-label="name"
                option-value="id"
                required
            />

            <div class="space-y-2">
                <label class="text-sm font-medium">Imagen del Plano</label>
                <div class="flex items-center gap-4">
                    <!-- Vista previa de la imagen -->
                    <div v-if="imagePreview || (mode === 'edit' && plan?.image)" class="relative w-40 h-24 rounded-lg overflow-hidden border border-border/40">
                        <img
                            :src="imagePreview || '/storage/' + plan?.image"
                            alt="Vista previa del plano"
                            class="w-full h-full object-cover"
                        >
                        <button
                            type="button"
                            @click="clearImage"
                            class="absolute top-1 right-1 p-1 bg-black/60 rounded-full hover:bg-black/80 transition-colors"
                        >
                            <X class="h-4 w-4 text-white" />
                        </button>
                    </div>

                    <!-- Input de archivo -->
                    <div
                        class="flex-1 border-2 border-dashed border-border/40 rounded-lg p-4 text-center cursor-pointer hover:border-primary/60 transition-colors"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent
                        @drop.prevent="handleDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleFileChange"
                        >
                        <Upload class="h-6 w-6 mx-auto text-muted-foreground" />
                        <p class="mt-2 text-sm text-muted-foreground">
                            Arrastra una imagen o haz clic para seleccionar
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">
                            PNG, JPG o JPEG (máx. 3MB)
                        </p>
                    </div>
                </div>
                <p v-if="form.errors.image" class="text-sm text-destructive mt-1">
                    {{ form.errors.image }}
                </p>
            </div>

            <FormTextarea
                id="description"
                label="Descripción"
                v-model="form.description"
                :error="form.errors.description"
                :maxlength="500"
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

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FormSelect from '@/components/FormSelect.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import { Upload, X } from 'lucide-vue-next';

interface PlanType {
    id: number;
    name: string;
}

interface Plan {
    id?: number;
    event_id: number;
    plan_type_id: number;
    image: string;
    description: string | null;
}

const props = defineProps<{
    plan?: Plan;
    mode: 'create' | 'edit';
    eventId: number;
    planTypes: PlanType[];
}>();

const emit = defineEmits(['success', 'error', 'cancel']);

// Referencias
const fileInput = ref<HTMLInputElement | null>(null);

// Vista previa de la imagen
const imagePreview = ref<string | null>(null);

// Crear formulario basado en el modo
const form = useForm({
    _method: props.mode === 'edit' ? 'PUT' : 'POST',
    id: props.plan?.id ?? null,
    event_id: props.eventId,
    plan_type_id: props.plan?.plan_type_id ?? '',
    image: null as File | null,
    description: props.plan?.description ?? '',
});

// Manejar cambio de archivo
const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        if (validateFile(file)) {
            form.image = file;
            createImagePreview(file);
        }
    }
};

// Manejar arrastrar y soltar
const handleDrop = (event: DragEvent) => {
    const file = event.dataTransfer?.files[0];
    if (file && validateFile(file)) {
        form.image = file;
        createImagePreview(file);
    }
};

// Validar archivo
const validateFile = (file: File): boolean => {
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    const maxSize = 3 * 1024 * 1024; // 3MB

    if (!validTypes.includes(file.type)) {
        emit('error', 'El archivo debe ser una imagen PNG, JPG o JPEG');
        return false;
    }

    if (file.size > maxSize) {
        emit('error', 'La imagen no debe pesar más de 3MB');
        return false;
    }

    return true;
};

// Crear vista previa de la imagen
const createImagePreview = (file: File) => {
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

// Limpiar imagen
const clearImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Determinar la URL y método según el modo
const submitForm = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit('success', props.mode === 'create' ? 'Plano creado exitosamente' : 'Plano actualizado exitosamente');
            if (props.mode === 'create') {
                form.reset();
                clearImage();
            }
        },
        onError: (errors) => {
            emit('error', 'Error al procesar el plano: ' + Object.values(errors)[0]);
        }
    };

    if (props.mode === 'create') {
        form.post(route('plans.store'), options);
    } else {
        form.transform((data) => {
            // Si no hay una nueva imagen, no la incluimos en la petición
            if (!data.image) {
                delete data.image;
            }
            return data;
        }).post(route('plans.update', props.plan?.id), options);
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
    clearImage();
    emit('cancel');
};

// Watch para actualizar el formulario cuando cambia el modo o el plano
watch(
    [() => props.mode, () => props.plan],
    () => {
        form.id = props.plan?.id ?? null;
        form.event_id = props.eventId;
        form.plan_type_id = props.plan?.plan_type_id ?? '';
        form.image = null;
        form.description = props.plan?.description ?? '';
        form.clearErrors();
        imagePreview.value = null;
    }
);
</script>
