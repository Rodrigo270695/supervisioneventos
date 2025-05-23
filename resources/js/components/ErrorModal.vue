<script setup lang="ts">
import { XCircle } from 'lucide-vue-next';

defineProps<{
    show: boolean;
    error: string;
    eventName?: string;
    eventStatus?: string;
}>();

const emit = defineEmits(['close']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white dark:bg-neutral-dark/95 p-6 text-left shadow-xl transition-all">
            <!-- Icono y título -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <XCircle class="h-6 w-6 text-red-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-neutral-dark dark:text-white">
                        Error de acceso
                    </h3>
                    <p class="mt-2 text-sm text-neutral-medium dark:text-neutral-light/70">
                        {{ error }}
                    </p>

                    <!-- Información adicional del evento si está disponible -->
                    <div v-if="eventName" class="mt-4 rounded-md bg-neutral-light/50 dark:bg-neutral-dark/50 p-3">
                        <p class="text-sm font-medium text-neutral-dark dark:text-white">
                            {{ eventName }}
                        </p>
                        <p v-if="eventStatus" class="mt-1 text-xs text-neutral-medium dark:text-neutral-light/70">
                            Estado: {{ eventStatus }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botón de cerrar -->
            <div class="mt-6 flex justify-end">
                <button
                    @click="emit('close')"
                    class="cursor-pointer inline-flex items-center justify-center rounded-md bg-red-500/10 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 dark:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500 dark:hover:text-white transition-colors"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>
