<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { AlertTriangle } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
    itemName?: string;
    processingDelete: boolean;
    message?: string;
    warningMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Confirmar eliminación',
    message: '¿Estás seguro de que deseas eliminar este elemento?',
    warningMessage: 'Esta acción no se puede deshacer.',
});

const emit = defineEmits(['close', 'confirm']);

const closeModal = () => {
    emit('close');
};

const confirmDelete = () => {
    emit('confirm');
};
</script>

<template>
    <Modal :show="show" :title="title" size="sm" :closeOnClickOutside="false" :closeOnEsc="true" @close="closeModal">
        <div class="space-y-4">
            <div class="bg-destructive/10 border-destructive/20 rounded-lg border p-4">
                <p class="text-foreground flex items-start gap-3">
                    <span class="text-destructive mt-0.5">
                        <AlertTriangle class="h-5 w-5" />
                    </span>
                    <span v-if="itemName">
                        {{ message.replace('este elemento', `${itemName}`) }}
                    </span>
                    <span v-else>
                        {{ message }}
                    </span>
                </p>
            </div>
            <p class="text-muted-foreground text-sm">{{ warningMessage }}</p>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                @click="closeModal"
                class="border-input bg-background hover:bg-muted inline-flex cursor-pointer items-center justify-center rounded-md border px-4 py-2 text-sm font-medium shadow-sm transition-colors duration-200"
            >
                Cancelar
            </button>
            <button
                type="button"
                @click="confirmDelete"
                :disabled="processingDelete"
                class="bg-destructive text-destructive-foreground hover:bg-destructive/90 inline-flex cursor-pointer items-center justify-center rounded-md px-4 py-2 text-sm font-medium shadow-sm transition-colors duration-200"
            >
                {{ processingDelete ? 'Eliminando...' : 'Eliminar' }}
            </button>
        </div>
    </Modal>
</template>
