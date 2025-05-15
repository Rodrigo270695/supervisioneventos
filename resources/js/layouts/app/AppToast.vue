<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import Toast from '@/components/Toast.vue';
import { usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

const page = usePage();
const { toast, hideToast, showToast } = useToast();

// Función para mostrar los mensajes flash
function handleFlashMessages() {
    const flash = page.props.flash;

    if (flash.success) {
        showToast(flash.success, 'success');
    }

    if (flash.error) {
        showToast(flash.error, 'error');
    }

    if (flash.warning) {
        showToast(flash.warning, 'warning');
    }

    if (flash.info) {
        showToast(flash.info, 'info');
    }
}

// Detectar cambios en los mensajes flash
watch(() => page.props.flash, handleFlashMessages, { deep: true });

// Verificar mensajes flash al montar el componente
onMounted(() => {
    handleFlashMessages();
});
</script>

<template>
    <!-- Toast global que estará presente en todas las vistas -->
    <Toast
        :show="toast.show"
        :message="toast.message"
        :type="toast.type"
        :duration="toast.duration"
        @close="hideToast"
    />
</template>
