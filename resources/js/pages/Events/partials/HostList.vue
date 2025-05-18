<template>
    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
            <h2 class="text-lg font-medium">Anfitriones del evento</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Buscador -->
                <SearchBar
                    v-model="search"
                    placeholder="Buscar por nombre, DNI..."
                    min-width="230px"
                    @search="handleSearch"
                    @clear="handleClearSearch"
                />
                <button
                    @click="openCreateModal"
                    class="cursor-pointer bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar anfitrión
                </button>
            </div>
        </div>

        <!-- Lista de anfitriones -->
        <div v-if="filteredHosts.length > 0" class="space-y-4">
            <div v-for="host in filteredHosts" :key="host.id" class="flex items-start justify-between border-b border-border/40 pb-4 last:border-0">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ host.full_name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ host.hostType.name }}</p>
                        </div>
                        <div class="text-right text-sm text-muted-foreground">
                            <p>{{ host.dni }}</p>
                            <p>{{ host.correo }}</p>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <ActionButtons gap="sm">
                        <EditButton
                            @click="openEditModal(host)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(host)"
                            size="sm"
                        />
                    </ActionButtons>
                </div>
            </div>
        </div>

        <!-- Estado vacío con mensaje personalizado según búsqueda -->
        <div v-else class="py-10 flex flex-col items-center justify-center text-center">
            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                <Users class="h-6 w-6 text-primary" />
            </div>
            <template v-if="search">
                <h3 class="text-lg font-medium mb-1">No se encontraron anfitriones</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    No hay anfitriones que coincidan con tu búsqueda. Intenta con otros términos o
                    <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">
                        limpia la búsqueda
                    </button>
                </p>
            </template>
            <template v-else>
                <h3 class="text-lg font-medium mb-1">Sin anfitriones</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    Este evento aún no tiene anfitriones asignados. Agrega anfitriones para definir quiénes están a cargo de este evento.
                </p>
                <button
                    @click="openCreateModal"
                    class="cursor-pointer mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar primer anfitrión
                </button>
            </template>
        </div>

        <!-- Modal para crear/editar anfitrión -->
        <Modal
            :show="showModal"
            :title="formMode === 'create' ? 'Agregar Anfitrión' : 'Editar Anfitrión'"
            size="xl"
            :closeOnClickOutside="false"
            :closeOnEsc="true"
            @close="showModal = false"
        >
            <HostForm
                :mode="formMode"
                :host="currentHost"
                :event-id="eventId"
                :host-types="hostTypes"
                @success="handleFormSuccess"
                @error="handleFormError"
                @cancel="handleFormCancel"
            />
        </Modal>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Eliminar Anfitrión"
            :item-name="currentHost?.full_name"
            :processing-delete="deleteForm.processing"
            message="¿Estás seguro de que deseas eliminar este anfitrión?"
            warning-message="Esta acción no se puede deshacer."
            @close="showDeleteModal = false"
            @confirm="deleteHost"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import Modal from '@/components/Modal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import ActionButtons from '@/components/ActionButtons.vue';
import EditButton from '@/components/EditButton.vue';
import DeleteButton from '@/components/DeleteButton.vue';
import SearchBar from '@/components/SearchBar.vue';
import { Users } from 'lucide-vue-next';
import HostForm from './HostForm.vue';

interface HostType {
    id: number;
    name: string;
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

const props = defineProps<{
    eventId: number;
    hosts: Host[];
    hostTypes: HostType[];
}>();

const emit = defineEmits(['hostUpdated']);

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentHost = ref<Host | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Estado para búsqueda
const search = ref('');

// Lista filtrada de anfitriones
const filteredHosts = computed(() => {
    if (!search.value) return props.hosts;

    const searchTerm = search.value.toLowerCase();
    return props.hosts.filter(host =>
        host.full_name.toLowerCase().includes(searchTerm) ||
        host.dni.toLowerCase().includes(searchTerm) ||
        (host.correo && host.correo.toLowerCase().includes(searchTerm)) ||
        host.hostType.name.toLowerCase().includes(searchTerm)
    );
});

// Manejar búsqueda
const handleSearch = (value: string) => {
    search.value = value;
};

// Limpiar búsqueda
const handleClearSearch = () => {
    search.value = '';
};

// Abrir modal de creación
const openCreateModal = () => {
    formMode.value = 'create';
    currentHost.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (host: Host) => {
    formMode.value = 'edit';
    currentHost.value = host;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (host: Host) => {
    currentHost.value = host;
    showDeleteModal.value = true;
};

// Eliminar anfitrión
const deleteHost = () => {
    if (!currentHost.value) return;

    deleteForm.delete(route('hosts.destroy', currentHost.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            emit('hostUpdated');
            showToast('Anfitrión eliminado exitosamente', 'success');
        },
    });
};

// Manejar éxito del formulario
const handleFormSuccess = (message: string) => {
    showModal.value = false;
    emit('hostUpdated');
    showToast(message, 'success');
};

// Manejar error del formulario
const handleFormError = (message: string) => {
    showToast(message, 'error');
};

// Manejar cancelación del formulario
const handleFormCancel = () => {
    showModal.value = false;
};
</script>
