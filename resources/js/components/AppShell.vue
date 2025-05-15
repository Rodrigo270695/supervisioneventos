<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import Toast from '@/components/Toast.vue';
import { usePage } from '@inertiajs/vue3';
import { SharedData } from '@/types';
import { ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage<SharedData>().props.sidebarOpen;

// Toast global
const toast = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error' | 'info' | 'warning',
});

// Función para mostrar toast - expuesta globalmente
const showToast = (message: string, type: 'success' | 'error' | 'info' | 'warning') => {
    toast.value = {
        show: true,
        message,
        type,
    };

    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Exponer el método showToast globalmente
defineExpose({ showToast });
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>

    <!-- Toast de notificación global -->
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" :duration="3000" @close="toast.show = false" />
</template>
