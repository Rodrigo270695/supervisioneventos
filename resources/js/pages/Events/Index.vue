<script setup lang="ts">
import CreateButton from '@/components/CreateButton.vue';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';
import FullCalendar from '@fullcalendar/vue3';
import { Head, router } from '@inertiajs/vue3';
import { Plus, CalendarDays, Clock, MapPin, Users, Info, Edit, Calendar, Map, UserPlus, Eye } from 'lucide-vue-next';
import { ref, defineProps, onMounted, watch, computed } from 'vue';
import EventForm from './EventForm.vue';
import EventContextMenu from './EventContextMenu.vue';
import EventPreview from './EventPreview.vue';
import EventStats from './EventStats.vue';

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

interface EventType {
    id: number;
    name: string;
    color: string;
}

interface Props {
    events: Event[];
    eventTypes: EventType[];
}

const props = defineProps<Props>();

// Configuración de breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Calendario de Eventos',
        href: '/events',
    },
];

const calendar = ref(null);
const showModal = ref(false);
const isEditing = ref(false);
const selectedEvent = ref<Event | null>(null);
const selectedDate = ref('');

const form = ref({
    event_type_id: '',
    name: '',
    capacity: '',
    event_date: '',
    start_time: '',
    end_time: '',
    address: '',
    description: '',
    status: 'scheduled' as const,
});

// Estado para el tema preferido
const userPrefersDark = ref(false);
const activeView = ref('dayGridMonth');

// Detectar preferencia de tema del usuario
onMounted(() => {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    userPrefersDark.value = prefersDark;

    // Añadir clase al body para tema oscuro si es necesario
    if (prefersDark) {
        document.body.classList.add('dark-theme');
    }

    // Listener para cambios en la preferencia de tema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        userPrefersDark.value = e.matches;
        if (e.matches) {
            document.body.classList.add('dark-theme');
        } else {
            document.body.classList.remove('dark-theme');
        }
    });
});

const eventColors = {
    scheduled: {
        light: '#3B82F6',
        dark: '#60A5FA'
    },
    in_progress: {
        light: '#F59E0B',
        dark: '#FBBF24'
    },
    completed: {
        light: '#10B981',
        dark: '#34D399'
    },
    cancelled: {
        light: '#EF4444',
        dark: '#F87171'
    }
};

function getStatusColor(status: string) {
    const colorSet = eventColors[status as keyof typeof eventColors] || eventColors.scheduled;
    return userPrefersDark.value ? colorSet.dark : colorSet.light;
}

// Estado para vista previa de evento
const previewEvent = ref<any>(null);
const showPreviewPopup = ref(false);
const previewPosition = ref({ top: '0px', left: '0px' });

// Valor computado para estadísticas rápidas
const eventStats = computed(() => {
    const total = props.events.length;
    const upcoming = props.events.filter(e => e.status === 'scheduled').length;
    const inProgress = props.events.filter(e => e.status === 'in_progress').length;
    const completed = props.events.filter(e => e.status === 'completed').length;

    return { total, upcoming, inProgress, completed };
});

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
    },
    views: {
        dayGridMonth: { buttonText: 'Mes' },
        timeGridWeek: { buttonText: 'Semana' },
        timeGridDay: { buttonText: 'Día' },
        listMonth: { buttonText: 'Lista' }
    },
    locale: esLocale,
    events: props.events.map((event) => {

        // Acceder al tipo de evento - La clave puede ser event_type o eventType dependiendo de la serialización
        const eventType = event.event_type || event.eventType;

        // Formatear correctamente la fecha y hora
        // Necesitamos extraer solo la parte de la fecha (YYYY-MM-DD)
        const dateOnly = typeof event.event_date === 'string'
            ? event.event_date.split('T')[0]  // Si es string con formato ISO, tomar solo YYYY-MM-DD
            : event.event_date;

        const startDateTime = `${dateOnly}T${event.start_time}`;
        const endDateTime = event.end_time ? `${dateOnly}T${event.end_time}` : null;

        // Asegurarnos de que el color tenga formato correcto (con # si no lo tiene)
        const color = eventType?.color || '';
        const formattedColor = color.startsWith('#') ? color : `#${color}`;

        const formattedEvent = {
            id: event.id,
            title: event.name,
            start: startDateTime,
            end: endDateTime,
            // Usar el color procesado o fallback al color por estado
            backgroundColor: formattedColor || getStatusColor(event.status),
            borderColor: formattedColor || getStatusColor(event.status),
            textColor: '#ffffff', // Asegurar que el texto sea legible
            extendedProps: {
                address: event.address,
                capacity: event.capacity,
                description: event.description,
                eventTypeId: event.event_type_id,
                status: event.status,
                eventTypeName: eventType?.name || ''
            },
            allDay: false
        };

        return formattedEvent;
    }),
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },
    // Añadir tooltips/popups para mostrar información al pasar el ratón
    eventDidMount: (info) => {
        // Agregar clases para efectos visuales modernos
        info.el.classList.add('event-card');

        // Añadir listener para mostrar vista previa al hover
        info.el.addEventListener('mouseenter', (e) => {
            const rect = info.el.getBoundingClientRect();
            previewEvent.value = {
                title: info.event.title,
                typeName: info.event.extendedProps.eventTypeName,
                start: info.event.start,
                end: info.event.end,
                address: info.event.extendedProps.address,
                capacity: info.event.extendedProps.capacity,
                description: info.event.extendedProps.description,
                status: info.event.extendedProps.status
            };

            // Calcular posición del popup
            previewPosition.value = {
                top: `${rect.bottom + 8}px`,
                left: `${rect.left + (rect.width / 2)}px`
            };

            showPreviewPopup.value = true;
        });

        info.el.addEventListener('mouseleave', () => {
            showPreviewPopup.value = false;
        });
    },
    dateClick: (info: any) => {
        selectedDate.value = info.dateStr;
        isEditing.value = false;
        selectedEvent.value = null;
        openModal();
    },
    eventClick: (info: any) => {
        const event = props.events.find((e) => e.id === parseInt(info.event.id));
        if (event) {
            // Obtener detalles como coordenadas para posicionar el menú contextual
            const rect = info.el.getBoundingClientRect();
            const position = {
                x: rect.left + window.scrollX + rect.width / 2,
                y: rect.top + window.scrollY + rect.height
            };

            // Mostrar menú contextual
            showContextMenu(event, position);
        }
    },
    viewDidMount: (view) => {
        activeView.value = view.view.type;
    }
};

function openModal() {
    if (!isEditing.value && selectedDate.value === '') {
        form.value.event_date = '';
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    isEditing.value = false;
    selectedEvent.value = null;
    selectedDate.value = '';
    form.value = {
        event_type_id: '',
        name: '',
        capacity: '',
        event_date: '',
        start_time: '',
        end_time: '',
        address: '',
        description: '',
        status: 'scheduled',
    };
}

function handleSuccess() {
    closeModal();
    router.reload();
}

// Función para cambiar el tema
function toggleTheme() {
    userPrefersDark.value = !userPrefersDark.value;
    if (userPrefersDark.value) {
        document.body.classList.add('dark-theme');
    } else {
        document.body.classList.remove('dark-theme');
    }
}

// Añadir estas propiedades de estado para el menú contextual
const contextMenuEvent = ref<Event | null>(null);
const showContextMenuState = ref(false);
const contextMenuPosition = ref({ x: 0, y: 0 });

function showContextMenu(event: Event, position: { x: number, y: number }) {
    contextMenuEvent.value = event;
    contextMenuPosition.value = position;
    showContextMenuState.value = true;
}

function closeContextMenu() {
    showContextMenuState.value = false;
}

function openEditModal() {
    if (!contextMenuEvent.value) return;

    selectedEvent.value = contextMenuEvent.value;
    selectedDate.value = '';
    isEditing.value = true;
    openModal();
    closeContextMenu();
}

function navigateToEventDetails() {
    if (!contextMenuEvent.value) return;

    router.visit(route('events.show', contextMenuEvent.value.id));
    closeContextMenu();
}
</script>

<template>
    <Head title="Calendario de Eventos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-2 sm:p-4 md:p-6">
            <div class="mb-4 flex flex-col justify-between space-y-4 md:mb-8 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-xl font-bold tracking-tight text-foreground md:text-2xl">Calendario de Eventos</h1>

                <div class="flex flex-wrap gap-3">
                    <!-- Botón de nuevo usando el componente reutilizable - solo visible en desktop -->
                    <div class="hidden md:block">
                        <CreateButton label="Nuevo Evento" @click="openModal" />
                    </div>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <EventStats
                :total="eventStats.total"
                :upcoming="eventStats.upcoming"
                :in-progress="eventStats.inProgress"
                :completed="eventStats.completed"
            />

            <!-- Calendario -->
            <div class="bg-card dark:bg-card/90 backdrop-blur-sm overflow-hidden rounded-xl shadow-sm transition-all hover:shadow-md">
                <div class="p-2 sm:p-4 md:p-6">
                    <div class="calendar-container
                        [&_.fc-header-toolbar]:flex-col [&_.fc-header-toolbar]:gap-2 sm:[&_.fc-header-toolbar]:flex-row sm:[&_.fc-header-toolbar]:items-center
                        [&_.fc-daygrid-day]:cursor-pointer [&_.fc-daygrid-day-frame]:cursor-pointer [&_.fc-daygrid-day-number]:cursor-pointer
                        [&_.fc-button]:transition-all [&_.fc-button]:border-0 [&_.fc-button]:rounded-md [&_.fc-button]:px-3 [&_.fc-button]:py-2 [&_.fc-button]:font-medium
                        [&_.fc-button-primary]:bg-primary/90 hover:[&_.fc-button-primary]:bg-primary
                        [&_.fc-button-active]:bg-primary [&_.fc-button-active]:shadow-sm
                        [&_.fc-day-today]:bg-primary/5 dark:[&_.fc-day-today]:bg-primary/10
                        [&_.fc-event]:rounded-md [&_.fc-event]:border-0 [&_.fc-event]:px-2 [&_.fc-event]:py-1 [&_.fc-event]:shadow-sm hover:[&_.fc-event]:shadow-md [&_.fc-event]:cursor-pointer [&_.fc-event]:transition-all duration-200
                        dark:[&_.fc-day]:text-foreground/90 dark:[&_.fc-day-other]:text-foreground/50
                        [&_.fc-list-table]:rounded-md [&_.fc-list-event]:cursor-pointer [&_.fc-list-event-time]:font-semibold
                        dark:[&_.fc-list-day-cushion]:bg-muted/50 dark:[&_.fc-list-event]:hover:bg-muted/80"
                    >
                        <FullCalendar
                            ref="calendar"
                            :options="calendarOptions"
                            class="h-[500px] sm:h-[600px] md:h-[700px]"
                        />
                    </div>
                </div>
            </div>

            <!-- Vista previa usando el nuevo componente -->
            <EventPreview
                :event="previewEvent"
                :show="showPreviewPopup"
                :position="previewPosition"
            />

            <!-- Botón flotante para crear nuevo evento (solo en móvil) -->
            <button
                @click="openModal"
                class="bg-primary hover:bg-primary/90 fixed right-4 bottom-4 z-10 flex h-14 w-14 items-center justify-center rounded-full text-primary-foreground shadow-lg transition-all hover:scale-105 active:scale-95 md:hidden"
                aria-label="Crear nuevo evento"
            >
                <Plus class="h-6 w-6" />
            </button>

            <!-- Menú contextual usando el nuevo componente -->
            <EventContextMenu
                :event="contextMenuEvent"
                :show="showContextMenuState"
                :position="contextMenuPosition"
                @edit="openEditModal"
                @view="navigateToEventDetails"
                @close="closeContextMenu"
            />
        </div>
    </AppLayout>

    <!-- Modal para crear/editar evento -->
    <Modal
        :show="showModal"
        :title="isEditing ? 'Editar Evento' : 'Nuevo Evento'"
        size="2xl"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="closeModal"
    >
        <EventForm
            :event="selectedEvent"
            :event-types="eventTypes"
            :mode="isEditing ? 'edit' : 'create'"
            :selected-date="selectedDate"
            @success="handleSuccess"
            @cancel="closeModal"
        />
    </Modal>
</template>

<style>
/* Estilos base para tema claro */
:root {
    --event-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    --event-hover-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    --card-bg: #ffffff;
}

/* Estilos para tema oscuro */
.dark-theme {
    --event-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    --event-hover-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    --card-bg: rgba(30, 30, 30, 0.95);
}

/* Animaciones y efectos */
.event-card {
    box-shadow: var(--event-shadow);
    transition: all 0.2s ease-in-out;
    border-radius: 6px;
    overflow: hidden;
}

.event-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--event-hover-shadow);
}

/* Animación para el popup de vista previa */
@keyframes popup-appear {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-popover {
    animation: popup-appear 0.2s ease-out;
}
</style>
