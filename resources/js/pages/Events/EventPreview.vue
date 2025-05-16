<script setup lang="ts">
import { Clock, MapPin, Users, Info } from 'lucide-vue-next';
import { defineProps } from 'vue';

interface PreviewEvent {
    title: string;
    typeName: string;
    start: Date;
    end: Date | null;
    address: string;
    capacity: number;
    description: string | null;
    status: string;
}

interface Props {
    event: PreviewEvent | null;
    show: boolean;
    position: { top: string, left: string };
}

defineProps<Props>();
</script>

<template>
    <div
        v-if="show && event"
        class="bg-popover dark:bg-popover/90 backdrop-blur-sm fixed z-50 max-w-xs rounded-lg p-4 text-popover-foreground shadow-lg border border-border/30"
        :style="{ top: position.top, left: position.left }"
    >
        <h3 class="mb-2 text-base font-semibold">{{ event.title }}</h3>
        <div v-if="event.typeName" class="mb-3 inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
            {{ event.typeName }}
        </div>

        <div class="mb-2 flex flex-col space-y-2">
            <div v-if="event.start" class="flex items-center text-sm">
                <Clock class="mr-2 h-4 w-4 text-muted-foreground" />
                <span>{{ new Date(event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                <span v-if="event.end"> - {{ new Date(event.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
            </div>

            <div v-if="event.address" class="flex items-center text-sm">
                <MapPin class="mr-2 h-4 w-4 text-muted-foreground" />
                <span>{{ event.address }}</span>
            </div>

            <div v-if="event.capacity" class="flex items-center text-sm">
                <Users class="mr-2 h-4 w-4 text-muted-foreground" />
                <span>Capacidad: {{ event.capacity }}</span>
            </div>

            <div v-if="event.description" class="flex items-start text-sm">
                <Info class="mr-2 h-4 w-4 text-muted-foreground mt-0.5" />
                <span class="line-clamp-2">{{ event.description }}</span>
            </div>
        </div>

        <div class="mt-3 flex justify-end">
            <button class="hover:bg-muted/90 text-xs rounded px-2 py-1 transition-colors">
                Ver detalles
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Animación para el popup de vista previa */
@keyframes popup-appear {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-popover {
    animation: popup-appear 0.2s ease-out;
}
</style>
