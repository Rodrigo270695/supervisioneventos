<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const emit = defineEmits(['decode', 'error']);
const qrScanner = ref<Html5Qrcode | null>(null);
const error = ref('');
const scanning = ref(false);

// Tamaños adaptativos para el área de escaneo - Ahora más grandes
const SCAN_REGION_WIDTH = window.innerWidth < 768 ? Math.min(320, window.innerWidth - 32) : 400;
const SCAN_REGION_HEIGHT = SCAN_REGION_WIDTH;

const startScanner = async () => {
    try {
        const devices = await Html5Qrcode.getCameras();

        if (devices.length === 0) {
            throw new Error('No se encontraron cámaras disponibles');
        }

        const cameraId = devices[0].id;

        qrScanner.value = new Html5Qrcode("qr-reader", {
            formatsToSupport: [ 'QR_CODE' ],
            verbose: false
        });

        await qrScanner.value.start(
            cameraId,
            {
                fps: 10,
                qrbox: {
                    width: SCAN_REGION_WIDTH,
                    height: SCAN_REGION_HEIGHT
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                disableFlip: false,
            },
            (decodedText) => {
                emit('decode', decodedText);
            },
            () => {} // Silenciar mensajes de escaneo
        );

        scanning.value = true;

        // Personalizar la apariencia del escáner
        customizeScanner();
    } catch (err: any) {
        error.value = err.message;
        emit('error', err);
    }
};

const customizeScanner = () => {
    const style = document.createElement('style');
    style.textContent = `
        #qr-reader__dashboard_section {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }
        #qr-reader__status_span {
            display: none !important;
        }
        #qr-reader__scan_region > img {
            display: none !important;
        }
        #qr-reader {
            border: none !important;
            background: transparent !important;
            width: 100% !important;
            max-width: 100% !important;
            min-height: 450px !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
        }
        #qr-reader__scan_region {
            border: none !important;
            min-height: 450px !important;
        }
        #qr-reader video {
            background: black;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 0.5rem;
        }
        #qr-reader__dashboard button {
            padding: 0.5rem 1rem !important;
            background-color: rgb(var(--color-primary)) !important;
            color: white !important;
            border-radius: 0.375rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            margin: 0.5rem !important;
        }
        #qr-reader__scan_region > div:not(#qr-reader__scan_region_dashline) {
            display: none !important;
        }
        #qr-reader__scan_region_dashline {
            border: 3px solid white !important;
            border-radius: 8px !important;
            box-shadow: 0 0 0 4px rgba(255,255,255,0.1) !important;
        }
    `;
    document.head.appendChild(style);
};

const stopScanner = async () => {
    if (qrScanner.value && scanning.value) {
        try {
            await qrScanner.value.stop();
            scanning.value = false;
        } catch (err) {
            error.value = err.message;
        }
    }
};

onMounted(() => {
    startScanner();
});

onBeforeUnmount(() => {
    stopScanner();
});
</script>

<template>
    <div class="relative overflow-hidden rounded-lg bg-black">
        <!-- Contenedor del escáner -->
        <div id="qr-reader" class="w-full"></div>

        <!-- Mensaje de error -->
        <div v-if="error" class="absolute inset-0 flex items-center justify-center bg-black/50">
            <div class="rounded-lg bg-destructive/15 p-4 text-destructive">
                {{ error }}
            </div>
        </div>

        <!-- Marco de escaneo -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="relative" :style="{ width: `${SCAN_REGION_WIDTH}px`, height: `${SCAN_REGION_HEIGHT}px` }">
                <!-- Esquinas decorativas -->
                <div class="absolute top-0 left-0 w-12 h-12 border-t-4 border-l-4 border-white/90"></div>
                <div class="absolute top-0 right-0 w-12 h-12 border-t-4 border-r-4 border-white/90"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 border-b-4 border-l-4 border-white/90"></div>
                <div class="absolute bottom-0 right-0 w-12 h-12 border-b-4 border-r-4 border-white/90"></div>

                <!-- Línea de escaneo animada -->
                <div class="absolute inset-x-0 h-1 bg-primary/80 animate-scan-line"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
#qr-reader {
    width: 100%;
    min-height: 450px;
}

@keyframes scanLine {
    0% {
        top: 0;
    }
    50% {
        top: 100%;
    }
    100% {
        top: 0;
    }
}

.animate-scan-line {
    animation: scanLine 3s linear infinite;
}
</style>
