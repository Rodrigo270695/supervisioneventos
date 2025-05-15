import { ref } from 'vue';

interface ToastState {
    show: boolean;
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
}

// Estado del toast compartido entre todas las instancias
const toast = ref<ToastState>({
    show: false,
    message: '',
    type: 'success',
    duration: 3000
});

export function useToast() {
    // Mostrar toast
    const showToast = (
        message: string,
        type: 'success' | 'error' | 'info' | 'warning' = 'success',
        duration: number = 3000
    ) => {
        // Limpiar cualquier toast anterior
        if (toast.value.show) {
            toast.value.show = false;
            // Pequeña pausa para permitir que se cierre el toast anterior
            setTimeout(() => {
                displayToast(message, type, duration);
            }, 100);
        } else {
            displayToast(message, type, duration);
        }
    };

    // Función interna para mostrar el toast
    const displayToast = (
        message: string,
        type: 'success' | 'error' | 'info' | 'warning',
        duration: number
    ) => {
        // Asegurarnos que ciertos mensajes sean tratados como error
        let validType = type;

        // Si el mensaje contiene palabras relacionadas con error, forzar tipo 'error'
        if (message.toLowerCase().includes('error') ||
            message.toLowerCase().includes('falló') ||
            message.toLowerCase().includes('fallo') ||
            message.toLowerCase().includes('no se pudo')) {
            validType = 'error';
        } else {
            // Asegurarse de que el tipo sea uno de los valores permitidos
            validType = ['success', 'error', 'info', 'warning'].includes(type)
                ? type
                : 'info';
        }

        toast.value = {
            show: true,
            message,
            type: validType,
            duration
        };

        setTimeout(() => {
            toast.value.show = false;
        }, duration);
    };

    // Ocultar toast
    const hideToast = () => {
        toast.value.show = false;
    };

    return {
        toast,
        showToast,
        hideToast
    };
}
