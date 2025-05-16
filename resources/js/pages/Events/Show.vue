<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Calendar, Clock, MapPin, Users, CalendarDays, UserPlus, Pencil, Map, LayoutGrid, ArrowLeft } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import Modal from '@/components/Modal.vue';
import EventForm from '../Events/EventForm.vue';

interface EventType {
    id: number;
    name: string;
    color: string;
}

interface HostType {
    id: number;
    name: string;
    description: string | null;
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
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    eventType?: EventType;
}

interface Props {
    event: Event;
    hostTypes: HostType[];
    eventTypes?: EventType[]; // Añadir tipos de evento para el formulario
}

const props = defineProps<Props>();

// Estado para el modal de edición
const showModal = ref(false);

// Configuración para breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Calendario de Eventos',
        href: '/events',
    },
    {
        title: props.event.name,
        href: route('events.show', props.event.id),
    },
];

// Estado para la pestaña activa
const activeTab = ref('info');
const tabIdMap = {
    'info': 'info',
    'hosts': 'hosts',
    'schedule': 'schedule',
    'layout': 'layout',
    'guests': 'guests'
};

// Cargar la pestaña activa desde el hash al cargar la página
onMounted(() => {
    const hash = window.location.hash.substring(1);
    if (hash && tabIdMap[hash as keyof typeof tabIdMap]) {
        activeTab.value = hash;
    }
});

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

// Función para cambiar de pestaña
function changeTab(tab: string) {
    activeTab.value = tab;
    // Actualizar la URL con el hash para permitir enlaces directos a pestañas
    window.location.hash = tab;
}

// Abrir el modal de edición
function editEvent() {
    showModal.value = true;
}

// Cerrar el modal de edición
function closeModal() {
    showModal.value = false;
}

// Manejar el éxito de la edición
function handleSuccess() {
    closeModal();
    router.reload();
}
</script>

<template>
    <Head :title="event.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-2 sm:p-4 md:p-6">
            <!-- Cabecera con información básica y acciones -->
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
                        @click="editEvent"
                        class="inline-flex items-center gap-1 rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                        <span>Editar</span>
                    </button>
                </div>
            </div>

            <!-- Navegación por pestañas -->
            <div class="border-b border-border">
                <nav class="flex space-x-4 overflow-x-auto">
                    <button
                        @click="changeTab('info')"
                        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                        :class="activeTab === 'info' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                    >
                        Información
                    </button>
                    <button
                        @click="changeTab('hosts')"
                        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                        :class="activeTab === 'hosts' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                        id="hosts"
                    >
                        Anfitriones
                    </button>
                    <button
                        @click="changeTab('schedule')"
                        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                        :class="activeTab === 'schedule' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                        id="schedule"
                    >
                        Agenda
                    </button>
                    <button
                        @click="changeTab('layout')"
                        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                        :class="activeTab === 'layout' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                        id="layout"
                    >
                        Planos
                    </button>
                    <button
                        @click="changeTab('guests')"
                        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                        :class="activeTab === 'guests' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                        id="guests"
                    >
                        Invitados
                    </button>
                </nav>
            </div>

            <!-- Contenido de las pestañas -->
            <div class="mt-6">
                <!-- Información general -->
                <div v-if="activeTab === 'info'" class="animate-in fade-in duration-200">
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
                                        @click="changeTab('hosts')"
                                        class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                                    >
                                        <Users class="h-4 w-4 text-primary" />
                                        <span>Gestionar anfitriones</span>
                                    </button>

                                    <button
                                        @click="changeTab('schedule')"
                                        class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                                    >
                                        <Calendar class="h-4 w-4 text-primary" />
                                        <span>Configurar agenda</span>
                                    </button>

                                    <button
                                        @click="changeTab('layout')"
                                        class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                                    >
                                        <LayoutGrid class="h-4 w-4 text-primary" />
                                        <span>Diseñar planos</span>
                                    </button>

                                    <button
                                        @click="changeTab('guests')"
                                        class="w-full flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors"
                                    >
                                        <UserPlus class="h-4 w-4 text-primary" />
                                        <span>Administrar invitados</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anfitriones -->
                <div v-if="activeTab === 'hosts'" class="animate-in fade-in duration-200">
                    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
                        <div class="flex justify-between items-center mb-5">
                            <h2 class="text-lg font-medium">Anfitriones del evento</h2>
                            <button class="bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar anfitrión
                            </button>
                        </div>

                        <!-- Lista de anfitriones (vacía inicialmente) -->
                        <div class="py-10 flex flex-col items-center justify-center text-center">
                            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                                <Users class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-medium mb-1">Sin anfitriones</h3>
                            <p class="text-muted-foreground text-sm max-w-md">
                                Este evento aún no tiene anfitriones asignados. Agrega anfitriones para definir quiénes están a cargo de este evento.
                            </p>
                            <button class="mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar primer anfitrión
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Agenda -->
                <div v-if="activeTab === 'schedule'" class="animate-in fade-in duration-200">
                    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
                        <div class="flex justify-between items-center mb-5">
                            <h2 class="text-lg font-medium">Configuración de agenda</h2>
                            <button class="bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar actividad
                            </button>
                        </div>

                        <!-- Lista de actividades (vacía inicialmente) -->
                        <div class="py-10 flex flex-col items-center justify-center text-center">
                            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                                <Calendar class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-medium mb-1">Sin actividades programadas</h3>
                            <p class="text-muted-foreground text-sm max-w-md">
                                Este evento aún no tiene actividades programadas. Agrega actividades para definir un cronograma detallado.
                            </p>
                            <button class="mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar primera actividad
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Planos -->
                <div v-if="activeTab === 'layout'" class="animate-in fade-in duration-200">
                    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
                        <div class="flex justify-between items-center mb-5">
                            <h2 class="text-lg font-medium">Diseño de planos</h2>
                            <button class="bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Crear plano
                            </button>
                        </div>

                        <!-- Lista de planos (vacía inicialmente) -->
                        <div class="py-10 flex flex-col items-center justify-center text-center">
                            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                                <Map class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-medium mb-1">Sin planos disponibles</h3>
                            <p class="text-muted-foreground text-sm max-w-md">
                                Este evento aún no tiene planos diseñados. Crea planos para visualizar la distribución del espacio y las mesas.
                            </p>
                            <button class="mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Crear primer plano
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Invitados -->
                <div v-if="activeTab === 'guests'" class="animate-in fade-in duration-200">
                    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
                        <div class="flex justify-between items-center mb-5">
                            <h2 class="text-lg font-medium">Administración de invitados</h2>
                            <button class="bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar invitado
                            </button>
                        </div>

                        <!-- Lista de invitados (vacía inicialmente) -->
                        <div class="py-10 flex flex-col items-center justify-center text-center">
                            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                                <UserPlus class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-medium mb-1">Sin invitados</h3>
                            <p class="text-muted-foreground text-sm max-w-md">
                                Este evento aún no tiene invitados registrados. Agrega invitados para llevar un control de asistencia.
                            </p>
                            <button class="mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                Agregar primer invitado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Modal para editar evento -->
    <Modal
        :show="showModal"
        title="Editar Evento"
        size="2xl"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="closeModal"
    >
        <EventForm
            :event="event"
            :event-types="eventTypes"
            mode="edit"
            @success="handleSuccess"
            @cancel="closeModal"
        />
    </Modal>
</template>

<style>
.animate-in {
    animation-duration: 150ms;
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform, opacity;
}

.fade-in {
    animation-name: fadeIn;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
