<script setup lang="ts">
import { Edit, Eye, Users, Calendar, Map, UserPlus, Clock, FileText } from 'lucide-vue-next';
import { ref, defineProps, defineEmits, onMounted, onUnmounted, watch } from 'vue';

interface Event {
    id: number;
    name: string;
    event_type_id: number;
    capacity: number;
    event_date: string;
    start_time: string;
    end_time: string | null;
    address: string;
    description: string | null;
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    eventType?: {
        id: number;
        name: string;
        color: string;
    };
}

interface Props {
    event: Event | null;
    show: boolean;
    position: { x: number, y: number };
}

interface Emits {
    (e: 'edit'): void;
    (e: 'view'): void;
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Manejador de clic fuera del menú para cerrarlo
const handleClickOutside = () => {
    emit('close');
};

// Configurar y eliminar listener cuando cambia show
watch(() => props.show, (newVal) => {
    if (newVal) {
        // Agregar un listener para cerrar el menú al hacer clic fuera
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside);
        }, 100);
    } else {
        document.removeEventListener('click', handleClickOutside);
    }
});

// Asegurar que el listener se elimine cuando se desmonta el componente
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleEdit = () => {
    emit('edit');
};

const handleView = () => {
    emit('view');
};
</script>

<template>
    <div
        v-if="show && event"
        class="fixed z-50 bg-popover rounded-lg shadow-lg border border-border overflow-hidden"
        :style="{
            left: `${position.x}px`,
            top: `${position.y}px`,
            transform: 'translate(-50%, -55%)'
        }"
    >
        <div class="py-1">
            <button
                @click="handleEdit"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <Edit class="h-4 w-4" />
                <span>Editar Evento</span>
            </button>

            <button
                @click="handleView"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <Eye class="h-4 w-4" />
                <span>Ver Detalles</span>
            </button>

            <div class="border-t my-1"></div>

            <a
                :href="event ? route('events.show', event.id) + '#hosts' : ''"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <Users class="h-4 w-4" />
                <span>Gestionar Anfitriones</span>
            </a>

            <a
                :href="event ? route('events.show', event.id) + '#times' : ''"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <Clock class="h-4 w-4" />
                <span>Gestionar Tiempos</span>
            </a>

            <a
                :href="event ? route('events.show', event.id) + '#layout' : ''"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <Map class="h-4 w-4" />
                <span>Diseñar Planos</span>
            </a>

            <a
                :href="event ? route('events.show', event.id) + '#guests' : ''"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <UserPlus class="h-4 w-4" />
                <span>Administrar Invitados</span>
            </a>

            <a
                :href="event ? route('events.show', event.id) + '#notes' : ''"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted text-left cursor-pointer"
            >
                <FileText class="h-4 w-4" />
                <span>Gestionar Notas</span>
            </a>
        </div>
    </div>
</template>
