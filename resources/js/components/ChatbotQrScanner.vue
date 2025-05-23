<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import ErrorModal from '@/components/ErrorModal.vue';
import { router } from '@inertiajs/vue3';

const emit = defineEmits(['decode', 'error']);
const qrScanner = ref<Html5Qrcode | null>(null);
const scanning = ref(false);

// Modal de error
const showErrorModal = ref(false);
const errorMessage = ref('');
const eventName = ref('');
const eventStatus = ref('');

// Tamaños adaptativos para el área de escaneo - Optimizado para invitaciones
const qrboxFunction = (viewfinderWidth: number, viewfinderHeight: number) => {
    const minEdgePercentage = 0.7;
    const minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
    const qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
    return {
        width: qrboxSize,
        height: qrboxSize
    };
};

// Iniciar el escáner
const startScanner = async () => {
    if (!qrScanner.value) return;

    try {
        await qrScanner.value.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: qrboxFunction,
                aspectRatio: 1.0
            },
            async (decodedText: string) => {
                try {
                    router.visit(route('chatbot.show', { qr_data: decodedText }), {
                        preserveState: true,
                        onError: (errors) => {
                            // Si hay un error, mostrar el modal
                            if (errors.error) {
                                errorMessage.value = errors.error;
                                eventName.value = errors.eventName || '';
                                eventStatus.value = errors.eventStatus || '';
                                showErrorModal.value = true;
                                emit('error', errors.error);
                            }
                        },
                        onSuccess: () => {
                            // La redirección será manejada automáticamente por Inertia
                        }
                    });
                } catch (error) {
                    errorMessage.value = 'Error al procesar el código QR';
                    showErrorModal.value = true;
                    emit('error', 'Error al procesar el código QR');
                }
            },
            () => {} // Silenciar mensajes de escaneo
        );
        scanning.value = true;
    } catch (err) {
        emit('error', err);
    }
};

// Detener el escáner
const stopScanner = async () => {
    if (qrScanner.value && scanning.value) {
        try {
            await qrScanner.value.stop();
            scanning.value = false;
        } catch (err) {
            console.error('Error al detener el escáner:', err);
        }
    }
};

const closeErrorModal = () => {
    showErrorModal.value = false;
};

onMounted(() => {
    qrScanner.value = new Html5Qrcode("qr-reader", {
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    });
    startScanner();
});

onBeforeUnmount(() => {
    stopScanner();
});
</script>

<template>
    <div class="relative">
        <div id="qr-reader" class="w-full overflow-hidden rounded-lg">
            <div class="relative aspect-square w-full">
                <!-- Área de escaneo -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="h-[70%] w-[70%] rounded-lg border-2 border-dashed border-white/50"></div>
                </div>
            </div>
        </div>

        <ErrorModal
            :show="showErrorModal"
            :error="errorMessage"
            :event-name="eventName"
            :event-status="eventStatus"
            @close="closeErrorModal"
        />
    </div>
</template>
