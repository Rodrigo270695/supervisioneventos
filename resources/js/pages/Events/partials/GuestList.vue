<template>
    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
            <h2 class="text-lg font-medium">Invitados del evento</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Buscador -->
                <SearchBar
                    v-model="search"
                    placeholder="Buscar por nombre o DNI..."
                    min-width="230px"
                    @search="handleSearch"
                    @clear="handleClearSearch"
                />
                <button
                    @click="openCreateModal"
                    class="cursor-pointer bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar invitado
                </button>
            </div>
        </div>

        <!-- Lista de invitados -->
        <div v-if="guestList.length > 0" class="space-y-4">
            <div v-for="guest in guestList" :key="guest.id" class="flex items-start justify-between border-b border-border/40 pb-4 last:border-0">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <p class="font-medium">{{ guest.full_name }}</p>
                            <div class="mt-1 flex items-center gap-4 text-sm text-muted-foreground">
                                <span>DNI: {{ guest.dni }}</span>
                                <span>Mesa: {{ guest.table_number }}</span>
                                <span>Pases: {{ guest.used_passes }}/{{ guest.passes }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <ActionButtons gap="sm">
                        <QrButton
                            @click="showQR(guest)"
                            size="sm"
                        />
                        <EditButton
                            @click="openEditModal(guest)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(guest)"
                            size="sm"
                        />
                    </ActionButtons>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="hasPagination" class="mt-4">
                <Pagination
                    :links="props.guests.links"
                    :from="props.guests.from"
                    :to="props.guests.to"
                    :total="props.guests.total"
                    @page-change="handlePaginationChange"
                />
            </div>
        </div>

        <!-- Estado vacío con mensaje personalizado según búsqueda -->
        <div v-else class="py-10 flex flex-col items-center justify-center text-center">
            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                <Users class="h-6 w-6 text-primary" />
            </div>
            <template v-if="search">
                <h3 class="text-lg font-medium mb-1">No se encontraron invitados</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    No hay invitados que coincidan con tu búsqueda. Intenta con otros términos o
                    <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">
                        limpia la búsqueda
                    </button>
                </p>
            </template>
            <template v-else>
                <h3 class="text-lg font-medium mb-1">Sin invitados registrados</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    Este evento aún no tiene invitados registrados. Agrega invitados para gestionar la asistencia.
                </p>
                <button
                    @click="openCreateModal"
                    class="cursor-pointer mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar primer invitado
                </button>
            </template>
        </div>

        <!-- Modales -->
        <Modal
            :show="showModal"
            :title="formMode === 'create' ? 'Agregar Invitado' : 'Editar Invitado'"
            size="xl"
            :closeOnClickOutside="false"
            :closeOnEsc="true"
            @close="closeModal"
        >
            <GuestForm
                :mode="formMode"
                :event-id="eventId"
                :guest="currentGuest"
                :event="{
                    capacity: event.capacity,
                    totalGuests: totalGuests
                }"
                @success="handleSuccess"
                @cancel="closeModal"
            />
        </Modal>

        <Modal
            :show="showQRModal"
            title="Código QR del Invitado"
            size="md"
            @close="closeQRModal"
        >
            <div class="flex flex-col items-center justify-center p-4">
                <div class="mb-4 text-center">
                    <h3 class="font-medium">{{ currentGuest?.full_name }}</h3>
                    <p class="text-sm text-muted-foreground">DNI: {{ currentGuest?.dni }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-inner mb-4">
                    <QRCode :value="currentGuest?.qr_code" :size="200" level="H" />
                </div>
                <button
                    @click="downloadQR"
                    class="flex items-center gap-2 text-sm text-primary hover:text-primary/90"
                >
                    <Download class="h-4 w-4" />
                    <span>Descargar QR</span>
                </button>
            </div>
        </Modal>

        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Eliminar Invitado"
            :item-name="currentGuest?.full_name"
            :processing-delete="deleteForm.processing"
            message="¿Estás seguro de que deseas eliminar este invitado?"
            warning-message="Esta acción no se puede deshacer."
            @close="showDeleteModal = false"
            @confirm="deleteGuest"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import Modal from '@/components/Modal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import ActionButtons from '@/components/ActionButtons.vue';
import EditButton from '@/components/EditButton.vue';
import DeleteButton from '@/components/DeleteButton.vue';
import SearchBar from '@/components/SearchBar.vue';
import Pagination from '@/components/Pagination.vue';
import GuestForm from './GuestForm.vue';
import { Users, Download } from 'lucide-vue-next';
import QRCode from 'qrcode.vue';
import QrButton from '@/components/QrButton.vue';

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
}

interface PaginatedData {
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
    eventId: number;
    guests: PaginatedData;
    event: {
        capacity: number;
    };
    filters?: {
        search: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({
        search: ''
    })
});

const emit = defineEmits(['guestUpdated']);

// Estado para modales
const showModal = ref(false);
const showQRModal = ref(false);
const showDeleteModal = ref(false);
const currentGuest = ref<Guest | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Estado para búsqueda
const search = ref(props.filters?.search || '');

// Computed properties para manejar los datos
const guestList = computed(() => {
    return props.guests.data;
});

const hasPagination = computed(() => {
    return props.guests.last_page > 1;
});

// Computed para el total de pases
const totalGuests = computed(() => {
    return props.guests.data.reduce((total, guest) => total + guest.passes, 0);
});

// Manejar búsqueda
const handleSearch = (value: string) => {
    router.get(
        route('events.show', props.eventId),
        { search: value },
        { preserveState: true, preserveScroll: true }
    );
};

// Limpiar búsqueda
const handleClearSearch = () => {
    router.get(
        route('events.show', props.eventId),
        { search: '' },
        { preserveState: true, preserveScroll: true }
    );
};

// Manejar cambio de página
const handlePaginationChange = (page: number) => {
    router.get(
        route('events.show', props.eventId),
        { page, search: props.filters?.search || '' },
        { preserveState: true, preserveScroll: true }
    );
};

// Abrir modal de creación
const openCreateModal = () => {
    formMode.value = 'create';
    currentGuest.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (guest: Guest) => {
    formMode.value = 'edit';
    currentGuest.value = guest;
    showModal.value = true;
};

// Mostrar QR
const showQR = (guest: Guest) => {
    currentGuest.value = guest;
    showQRModal.value = true;
};

// Cerrar modal de QR
const closeQRModal = () => {
    showQRModal.value = false;
    currentGuest.value = null;
};

// Descargar QR
const downloadQR = () => {
    if (!currentGuest.value) return;

    const canvas = document.querySelector('canvas');
    if (!canvas) return;

    const link = document.createElement('a');
    link.download = `qr-${currentGuest.value.dni}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
};

// Abrir modal de eliminación
const openDeleteModal = (guest: Guest) => {
    currentGuest.value = guest;
    showDeleteModal.value = true;
};

// Cerrar modal
const closeModal = () => {
    showModal.value = false;
    currentGuest.value = null;
};

// Manejar éxito del formulario
const handleSuccess = () => {
    closeModal();
    emit('guestUpdated');
    showToast(
        formMode.value === 'create'
            ? 'Invitado registrado exitosamente'
            : 'Invitado actualizado exitosamente',
        'success'
    );
};

// Eliminar invitado
const deleteGuest = () => {
    if (!currentGuest.value) return;

    deleteForm.delete(route('guests.destroy', currentGuest.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            emit('guestUpdated');
            showToast('Invitado eliminado exitosamente', 'success');
        },
    });
};
</script>
