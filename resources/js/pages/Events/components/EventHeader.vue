<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface EventType {
    id: number;
    name: string;
    color: string;
}

interface Event {
    id: number;
    name: string;
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    eventType?: EventType;
}

interface Props {
    event: Event;
    onEdit: () => void;
}

defineProps<Props>();

// Color de fondo para el indicador de estado
const statusColors = {
    scheduled: 'bg-blue-500 dark:bg-blue-600',
    in_progress: 'bg-amber-500 dark:bg-amber-600',
    completed: 'bg-green-500 dark:bg-green-600',
    cancelled: 'bg-red-500 dark:bg-red-600'
};

// Texto para el estado
const statusText = {
    scheduled: 'Programado',
    in_progress: 'En Progreso',
    completed: 'Completado',
    cancelled: 'Cancelado'
};
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground md:text-2xl lg:text-3xl">{{ event.name }}</h1>

                <!-- Badge de tipo de evento -->
                <span
                    v-if="event.eventType"
                    class="text-xs rounded-full px-2.5 py-0.5 font-medium"
                    :style="{
                        backgroundColor: `${event.eventType.color}20`,
                        color: event.eventType.color
                    }"
                >
                    {{ event.eventType.name }}
                </span>
            </div>

            <!-- Indicador de estado -->
            <div class="mt-2 flex items-center gap-2">
                <span
                    class="inline-flex h-2.5 w-2.5 rounded-full"
                    :class="statusColors[event.status]"
                ></span>
                <span class="text-sm text-muted-foreground">{{ statusText[event.status] }}</span>
            </div>
        </div>

        <div class="flex gap-2">
            <Link
                href="/events"
                class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-muted transition-colors"
            >
                <ArrowLeft class="h-3.5 w-3.5" />
                <span>Volver</span>
            </Link>
            <button
                @click="onEdit"
                class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-muted transition-colors"
            >
                <Pencil class="h-3.5 w-3.5" />
                <span>Editar</span>
            </button>
        </div>
    </div>
</template>
