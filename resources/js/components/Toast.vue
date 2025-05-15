<script setup lang="ts">
import { AlertCircle, AlertTriangle, CheckCircle, Info, X } from 'lucide-vue-next';
import { onMounted, ref, watch, defineProps, defineEmits } from 'vue';

const props = defineProps<{
    message: string;
    type?: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
    show: boolean;
}>();

const emit = defineEmits(['close']);
const visible = ref(props.show);
const timeout = ref<null | ReturnType<typeof setTimeout>>(null);
const progress = ref(100);
const animationFrame = ref<number | null>(null);
const startTime = ref<number>(0);

// Observar cambios en la propiedad show
watch(
    () => props.show,
    (value) => {
        visible.value = value;
        if (value && props.duration) {
            resetTimer();
        }
    },
);

onMounted(() => {
    if (visible.value && props.duration) {
        resetTimer();
    }
});

// Reiniciar el temporizador y la barra de progreso
function resetTimer() {
    if (timeout.value) clearTimeout(timeout.value);
    if (animationFrame.value) cancelAnimationFrame(animationFrame.value);

    progress.value = 100;
    startTime.value = performance.now();

    timeout.value = setTimeout(() => {
        close();
    }, props.duration);

    animateProgress();
}

// Animar la barra de progreso
function animateProgress() {
    animationFrame.value = requestAnimationFrame(() => {
        const elapsed = performance.now() - startTime.value;
        const duration = props.duration || 3000;
        progress.value = 100 - (elapsed / duration) * 100;

        if (progress.value > 0) {
            animateProgress();
        }
    });
}

// Cerrar el toast
function close() {
    visible.value = false;
    emit('close');
    if (animationFrame.value) {
        cancelAnimationFrame(animationFrame.value);
    }
}

// Pausar la barra de progreso al pasar el mouse
function pauseTimer() {
    if (timeout.value) clearTimeout(timeout.value);
    if (animationFrame.value) cancelAnimationFrame(animationFrame.value);
}

// Reanudar la barra de progreso al quitar el mouse
function resumeTimer() {
    if (props.duration) {
        const remainingTime = (props.duration * progress.value) / 100;
        startTime.value = performance.now() - (props.duration - remainingTime);

        timeout.value = setTimeout(() => {
            close();
        }, remainingTime);

        animateProgress();
    }
}

// Definir iconos según el tipo
function getIcon() {
    switch (props.type) {
        case 'success':
            return CheckCircle;
        case 'error':
            return AlertCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
        default:
            return Info;
    }
}

// Definir clases según el tipo
function getTypeClasses() {
    const type = props.type || 'info';

    const classes = {
        success: {
            bg: 'bg-emerald-50 dark:bg-emerald-950/30',
            border: 'border-emerald-500/30',
            icon: 'text-emerald-500',
            text: 'text-emerald-900 dark:text-emerald-100',
            progress: 'bg-emerald-500'
        },
        error: {
            bg: 'bg-red-100 dark:bg-red-900/40',
            border: 'border-red-500',
            icon: 'text-red-600 dark:text-red-400',
            text: 'text-red-800 dark:text-red-200',
            progress: 'bg-red-600'
        },
        warning: {
            bg: 'bg-amber-50 dark:bg-amber-950/30',
            border: 'border-amber-500/30',
            icon: 'text-amber-500',
            text: 'text-amber-900 dark:text-amber-100',
            progress: 'bg-amber-500'
        },
        info: {
            bg: 'bg-blue-50 dark:bg-blue-950/30',
            border: 'border-blue-500/30',
            icon: 'text-blue-500',
            text: 'text-blue-900 dark:text-blue-100',
            progress: 'bg-blue-500',
        },
    };

    return classes[type];
}

const typeClasses = getTypeClasses();
const IconComponent = getIcon();
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 scale-95"
        enter-to-class="translate-y-0 opacity-100 scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="visible"
            class="fixed bottom-5 right-5 max-w-md w-full sm:w-auto z-50"
            @mouseenter="pauseTimer"
            @mouseleave="resumeTimer"
        >
            <div
                class="rounded-lg shadow-lg overflow-hidden flex flex-col"
                :class="[
                    typeClasses.bg,
                    typeClasses.border,
                    props.type === 'error' ? 'border-l-4' : 'border backdrop-blur-sm'
                ]"
            >
                <div class="p-4 flex items-start gap-3">
                    <div :class="[typeClasses.icon, 'mt-0.5 flex-shrink-0']">
                        <component :is="IconComponent" :size="18" />
                    </div>

                    <div class="flex-grow" :class="typeClasses.text">
                        <div class="text-sm font-medium">{{ message }}</div>
                    </div>

                    <button
                        @click="close"
                        class="flex-shrink-0 -mt-1 -mr-1 p-1 rounded-full opacity-70 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="[typeClasses.text, 'hover:bg-black/5 focus:ring-black/30']"
                    >
                        <X :size="16" />
                    </button>
                </div>

                <!-- Barra de progreso -->
                <div class="h-0.5 w-full bg-black/5">
                    <div
                        class="h-full transition-all ease-linear"
                        :class="typeClasses.progress"
                        :style="{width: `${progress}%`}"
                    ></div>
                </div>
            </div>
        </div>
    </Transition>
</template>
