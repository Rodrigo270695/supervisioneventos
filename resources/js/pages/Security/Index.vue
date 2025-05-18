<template>
    <Head title="Gestionar Personal de Seguridad" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-4 sm:p-6">
            <div class="mb-8 flex flex-col justify-between space-y-4 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-2xl font-bold tracking-tight">Gestionar Personal de Seguridad</h1>

                <div class="flex flex-wrap gap-3">
                    <!-- Buscador -->
                    <SearchBar
                        v-model="search"
                        placeholder="Buscar por nombre o DNI..."
                        min-width="230px"
                        @search="handleSearch"
                        @clear="handleClearSearch"
                    />

                    <!-- Botón de nuevo - solo visible en desktop -->
                    <div class="hidden md:block">
                        <CreateButton
                            label="Nuevo Personal"
                            @click="openCreateModal"
                        />
                    </div>
                </div>
            </div>

            <!-- Vista móvil -->
            <DataTableMobile
                :columns="columns"
                :items="securityUsers.data"
                :search-term="search"
                :hover="false"
                :showChevron="false"
                :pagination="securityUsers"
                @pagination-change="handlePaginationChange"
            >
                <!-- Slot para la celda de eventos -->
                <template #cell-events="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="event in item.events"
                            :key="event.id"
                            class="inline-flex items-center rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary"
                        >
                            {{ event.name }}
                        </span>
                    </div>
                </template>

                <!-- Slot para las acciones -->
                <template #actions="{ item }">
                    <ActionButtons gap="sm">
                        <EditButton
                            @click="openEditModal(item)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(item)"
                            size="sm"
                        />
                    </ActionButtons>
                </template>

                <!-- Slot para acciones en estado vacío -->
                <template #empty-actions>
                    <p v-if="search" class="text-sm text-muted-foreground/70">
                        Intenta con otros términos o <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">limpia la búsqueda</button>
                    </p>
                </template>
            </DataTableMobile>

            <!-- Tabla desktop -->
            <DataTable
                :columns="columns"
                :items="securityUsers.data"
                :pagination="securityUsers"
                :search-term="search"
                class="hidden md:block"
                @pagination-change="handlePaginationChange"
            >
                <!-- Slot para la celda de eventos -->
                <template #cell-events="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="event in item.events"
                            :key="event.id"
                            class="inline-flex items-center rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary"
                        >
                            {{ event.name }}
                        </span>
                    </div>
                </template>

                <!-- Slot para las acciones -->
                <template #actions="{ item }">
                    <ActionButtons gap="sm">
                        <EditButton
                            @click="openEditModal(item)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(item)"
                            size="sm"
                        />
                    </ActionButtons>
                </template>

                <!-- Slot para acciones en estado vacío -->
                <template #empty-actions>
                    <p v-if="search" class="text-sm text-muted-foreground/70">
                        Intenta con otros términos o <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">limpia la búsqueda</button>
                    </p>
                </template>
            </DataTable>

            <!-- Botón flotante para crear nuevo (solo en móvil) -->
            <button
                @click="openCreateModal"
                class="md:hidden fixed bottom-6 right-6 bg-primary hover:bg-primary/90 text-white rounded-full p-3 shadow-lg flex items-center justify-center z-10 transition-transform hover:scale-105"
                aria-label="Crear nuevo personal de seguridad"
            >
                <Plus class="h-6 w-6" />
            </button>
        </div>
    </AppLayout>

    <!-- Modal unificado para creación/edición -->
    <Modal
        :show="showModal"
        :title="formMode === 'create' ? 'Registrar Personal de Seguridad' : 'Editar Personal de Seguridad'"
        size="xl"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="showModal = false"
    >
        <SecurityForm
            :mode="formMode"
            :security-user="currentSecurityUser"
            :events="events"
            @success="handleFormSuccess"
            @error="handleFormError"
            @cancel="handleFormCancel"
        />
    </Modal>

    <!-- Modal de confirmación para eliminar -->
    <ConfirmDeleteModal
        :show="showDeleteModal"
        title="Eliminar Personal de Seguridad"
        :item-name="currentSecurityUser?.name"
        :processing-delete="deleteForm.processing"
        message="¿Estás seguro de que deseas eliminar este personal de seguridad?"
        warning-message="Esta acción no se puede deshacer y eliminará todos los accesos registrados por este personal."
        @close="showDeleteModal = false"
        @confirm="deleteSecurityUser"
    />
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, defineProps } from 'vue';
import SecurityForm from './SecurityForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useToast } from '@/composables/useToast';
import Modal from '@/components/Modal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import SearchBar from '@/components/SearchBar.vue';
import ActionButtons from '@/components/ActionButtons.vue';
import EditButton from '@/components/EditButton.vue';
import DeleteButton from '@/components/DeleteButton.vue';
import DataTable from '@/components/DataTable/DataTable.vue';
import DataTableMobile from '@/components/DataTableMobile.vue';
import CreateButton from '@/components/CreateButton.vue';
import { Plus } from 'lucide-vue-next';

interface Event {
    id: number;
    name: string;
}

interface SecurityUser {
    id: number;
    name: string;
    dni: string;
    email: string | null;
    events: Event[];
}

interface Filters {
    search: string;
}

interface Props {
    securityUsers: {
        data: SecurityUser[];
        links: { url: string | null; label: string; active: boolean }[];
        total: number;
        from: number;
        to: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    events: Event[];
    filters: Filters;
}

const props = defineProps<Props>();

// Estado para búsqueda
const search = ref(props.filters.search || '');

// Configuración de breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Personal de Seguridad',
        href: '/security',
    },
];

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentSecurityUser = ref<SecurityUser | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Formulario de búsqueda
const searchForm = useForm({
    search: props.filters.search || '',
});

// Definición de columnas para la tabla
const columns = [
    {
        key: 'name',
        label: 'Nombre',
    },
    {
        key: 'dni',
        label: 'DNI',
    },
    {
        key: 'email',
        label: 'Correo',
    },
    {
        key: 'events',
        label: 'Eventos Asignados',
    },
];

// Manejar evento de búsqueda
const handleSearch = (value: string) => {
    searchForm.search = value;
    searchForm.get(route('security.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['securityUsers'],
    });
};

// Manejar limpieza de búsqueda
const handleClearSearch = () => {
    searchForm.search = '';
    searchForm.get(route('security.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Abrir modal de creación
const openCreateModal = () => {
    formMode.value = 'create';
    currentSecurityUser.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (securityUser: SecurityUser) => {
    formMode.value = 'edit';
    currentSecurityUser.value = securityUser;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (securityUser: SecurityUser) => {
    currentSecurityUser.value = securityUser;
    showDeleteModal.value = true;
};

// Eliminar personal de seguridad
const deleteSecurityUser = () => {
    if (!currentSecurityUser.value) return;

    deleteForm.delete(route('security.destroy', currentSecurityUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            showToast('Personal de seguridad eliminado exitosamente', 'success');
        },
        onError: () => showToast('Error al eliminar el personal de seguridad', 'error'),
    });
};

// Manejar éxito del formulario
const handleFormSuccess = (message: string) => {
    showModal.value = false;
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

// Manejar cambio de página
const handlePaginationChange = (page: number) => {
    router.get(route('security.index', { page }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['securityUsers'],
    });
};
</script>
