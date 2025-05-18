<script setup lang="ts">
import { computed } from 'vue';
import { CalendarDays, Clock, MapPin, Users, FileText, LayoutGrid, UserPlus } from 'lucide-vue-next';

interface EventType {
    id: number;
    name: string;
    color: string;
}

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
    eventType?: EventType;
}

interface Props {
    event: Event;
    onTabChange: (tab: string) => void;
}

const props = defineProps<Props>();

// Formatear la fecha del evento
const formattedDate = computed(() => {
    if (!props.event.event_date) return '';

    const eventDate = new Date(props.event.event_date);
    return eventDate.toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Formatear horario
const formattedTime = computed(() => {
    if (!props.event.start_time) return '';

    let timeStr = props.event.start_time;
    if (props.event.end_time) {
        timeStr += ` - ${props.event.end_time}`;
    }
    return timeStr;
});
</script>

<template>
    <div class="animate-in fade-in duration-200">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Panel con detalles del evento -->
            <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5 col-span-2">
                <h2 class="text-lg font-medium mb-4">Detalles del evento</h2>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <CalendarDays class="h-5 w-5 text-primary mt-0.5" />
                        <div>
                            <p class="text-sm text-muted-foreground">Fecha</p>
                            <p class="font-medium">{{ formattedDate }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Clock class="h-5 w-5 text-primary mt-0.5" />
                        <div>
                            <p class="text-sm text-muted-foreground">Horario</p>
                            <p class="font-medium">{{ formattedTime }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <MapPin class="h-5 w-5 text-primary mt-0.5" />
                        <div>
                            <p class="text-sm text-muted-foreground">Ubicación</p>
                            <p class="font-medium">{{ event.address }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Users class="h-5 w-5 text-primary mt-0.5" />
                        <div>
                            <p class="text-sm text-muted-foreground">Capacidad</p>
                            <p class="font-medium">{{ event.capacity }} personas</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-border/40">
                    <h3 class="text-base font-medium mb-3">Descripción</h3>
                    <p class="text-muted-foreground" v-if="event.description">{{ event.description }}</p>
                    <p class="text-muted-foreground italic" v-else>No hay descripción disponible</p>
                </div>
            </div>

            <!-- Panel lateral con accesos rápidos -->
            <div class="space-y-4">
                <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
                    <h3 class="text-base font-medium mb-3">Acciones rápidas</h3>
                    <div class="space-y-2">
                        <button
                            @click="onTabChange('hosts')"
                            class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                        >
                            <Users class="h-4 w-4 text-primary" />
                            <span>Gestionar anfitriones</span>
                        </button>

                        <button
                            @click="onTabChange('times')"
                            class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                        >
                            <Clock class="h-4 w-4 text-primary" />
                            <span>Gestionar tiempos</span>
                        </button>

                        <button
                            @click="onTabChange('layout')"
                            class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                        >
                            <LayoutGrid class="h-4 w-4 text-primary" />
                            <span>Diseñar planos</span>
                        </button>

                        <button
                            @click="onTabChange('guests')"
                            class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                        >
                            <UserPlus class="h-4 w-4 text-primary" />
                            <span>Administrar invitados</span>
                        </button>

                        <button
                            @click="onTabChange('notes')"
                            class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                        >
                            <FileText class="h-4 w-4 text-primary" />
                            <span>Gestionar notas</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
