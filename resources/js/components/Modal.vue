<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch, defineProps, defineEmits } from 'vue';

const props = defineProps<{
    show: boolean;
    title: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
    closeOnClickOutside?: boolean;
    closeOnEsc?: boolean;
}>();

const emit = defineEmits(['close', 'update:show']);

const isOpen = ref(props.show);
const modalRef = ref<HTMLElement | null>(null);

// Observar cambios en la prop show
watch(
    () => props.show,
    (value) => {
        isOpen.value = value;
        if (value) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    },
);

// Cerrar modal
const closeModal = () => {
    isOpen.value = false;
    emit('close');
    emit('update:show', false);
};

// Manejar tecla ESC
const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.closeOnEsc && isOpen.value) {
        closeModal();
    }
};

// Manejar clic fuera del modal
const handleClickOutside = (event: MouseEvent) => {
    if (props.closeOnClickOutside && isOpen.value && modalRef.value && !modalRef.value.contains(event.target as Node)) {
        closeModal();
    }
};

// Calcular clases para el tamaño del modal
const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    full: 'max-w-full',
}[props.size || 'md'];

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('mousedown', handleClickOutside);
    if (isOpen.value) {
        document.body.classList.add('overflow-hidden');
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('mousedown', handleClickOutside);
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isOpen" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center px-4">
                <Transition
                    enter-active-class="transition ease-out duration-300 transform"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-200 transform"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="isOpen"
                        ref="modalRef"
                        class="w-full overflow-hidden rounded-lg bg-card shadow-xl ring-1 ring-black/5 dark:ring-white/10"
                        :class="[sizeClasses]"
                    >
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h3 class="text-lg font-medium">
                                {{ title }}
                            </h3>
                            <button
                                type="button"
                                @click="closeModal"
                                class="text-muted-foreground hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-full p-1 transition-colors cursor-pointer"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="px-6 py-4">
                            <slot></slot>
                        </div>
                        <div v-if="$slots.footer" class="border-t border-border px-6 py-4 bg-muted/50">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
