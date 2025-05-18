<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Calendar, Clock, MapPin, Users, CalendarDays, UserPlus, Pencil, Map, LayoutGrid, ArrowLeft, FileText } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import Modal from '@/components/Modal.vue';
import EventForm from '../Events/EventForm.vue';
import HostList from './partials/HostList.vue';
import TimeList from './partials/TimeList.vue';
import NoteList from './partials/NoteList.vue';
import GuestList from './partials/GuestList.vue';
import TabNavigation from '@/components/TabNavigation.vue';
import EventInfo from './components/EventInfo.vue';
import EventHeader from './components/EventHeader.vue';

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

interface TimeType {
    id: number;
    name: string;
    description: string | null;
}

interface Time {
    id: number;
    event_id: number;
    time_type_id: number;
    start_time: string;
    end_time: string | null;
    description: string | null;
    timeType: TimeType;
}

interface Note {
    id: number;
    event_id: number;
    description: string;
    amount: number;
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

interface Host {
    id: number;
    event_id: number;
    host_type_id: number;
    nombres: string;
    apellidos: string;
    dni: string;
    edad: number;
    correo: string;
    full_name: string;
    hostType: HostType;
}

interface Guest {
    id: number;
    event_id: number;
    first_name: string;
    last_name: string;
    dni: string;
    table_number: number;
    passes: number;
    used_passes: number;
    qr_code: string;
    full_name: string;
    remaining_passes: number;
}

interface PaginatedGuests {
    data: Guest[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number;
    to: number;
    current_page: number;
    last_page: number;
    per_page: number;
}

interface Props {
    event: Event;
    hostTypes: HostType[];
    eventTypes?: EventType[]; // Añadir tipos de evento para el formulario
    hosts: Host[]; // Lista de anfitriones del evento
    times: Time[];
    timeTypes: TimeType[];
    notes: Note[]; // Lista de notas del evento
    guests: PaginatedGuests; // Lista paginada de invitados del evento
    filters?: {
        search: string;
    };
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

// Configuración de las pestañas
const tabs = [
    { id: 'info', label: 'Información' },
    { id: 'hosts', label: 'Anfitriones' },
    { id: 'times', label: 'Tiempos' },
    { id: 'layout', label: 'Planos' },
    { id: 'guests', label: 'Invitados' },
    { id: 'notes', label: 'Notas' }
];

// Cargar la pestaña activa desde el hash al cargar la página
onMounted(() => {
    const hash = window.location.hash.substring(1);
    if (hash && tabs.some(tab => tab.id === hash)) {
        activeTab.value = hash;
    }
});

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

// Refrescar lista de anfitriones
function refreshHosts() {
    router.reload({ only: ['hosts'] });
}

// Refrescar lista de tiempos
function refreshTimes() {
    router.reload({ only: ['times'] });
}

// Refrescar lista de notas
function refreshNotes() {
    router.reload({ only: ['notes'] });
}

// Refrescar lista de invitados
function refreshGuests() {
    router.reload({ only: ['guests'] });
}
</script>

<template>
    <Head :title="event.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-2 sm:p-4 md:p-6">
            <!-- Cabecera con información básica y acciones -->
            <EventHeader
                :event="event"
                :on-edit="editEvent"
            />

            <!-- Navegación por pestañas -->
            <TabNavigation
                :active-tab="activeTab"
                :tabs="tabs"
                @change="changeTab"
            />

            <!-- Contenido de las pestañas -->
            <div class="mt-6">
                <!-- Información general -->
                <EventInfo
                    v-if="activeTab === 'info'"
                    :event="event"
                    :on-tab-change="changeTab"
                />

                <!-- Anfitriones -->
                <div v-if="activeTab === 'hosts'" class="animate-in fade-in duration-200">
                    <HostList
                        :event-id="event.id"
                        :hosts="hosts"
                        :host-types="hostTypes"
                        @host-updated="refreshHosts"
                    />
                </div>

                <!-- Tiempos -->
                <div v-if="activeTab === 'times'" class="animate-in fade-in duration-200">
                    <TimeList
                        :event-id="event.id"
                        :times="times"
                        :time-types="timeTypes"
                        @timeUpdated="refreshTimes"
                    />
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
                    <GuestList
                        :event-id="event.id"
                        :guests="guests"
                        :event="{
                            capacity: event.capacity,
                            name: event.name
                        }"
                        :filters="filters"
                        @guest-updated="refreshGuests"
                    />
                        </div>

                <!-- Notas -->
                <div v-if="activeTab === 'notes'" class="animate-in fade-in duration-200">
                    <NoteList
                        :event-id="event.id"
                        :notes="notes"
                        @noteUpdated="refreshNotes"
                    />
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
