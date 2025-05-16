<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, defineProps, computed } from 'vue';
import HostTypeForm from './HostTypeForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useToast } from '@/composables/useToast';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import SearchBar from '@/components/SearchBar.vue';
import ActionButtons from '@/components/ActionButtons.vue';
import EditButton from '@/components/EditButton.vue';
import DeleteButton from '@/components/DeleteButton.vue';
import DataTable from '@/components/DataTable/DataTable.vue';
import DataTableMobile from '@/components/DataTableMobile.vue';
import CreateButton from '@/components/CreateButton.vue';
import { Plus, Search } from 'lucide-vue-next';

interface HostType {
    id: number;
    name: string;
    description: string | null;
}

interface Filters {
    search: string;
}

interface Props {
    hostTypes: {
        data: HostType[];
        links: { url: string | null; label: string; active: boolean }[];
        total: number;
        from: number;
        to: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    filters: Filters;
}

const props = defineProps<Props>();

// Estado para búsqueda - aseguramos que sea siempre un string
const search = ref(props.filters.search || '');

// Configuración de breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tipos de Anfitrión',
        href: '/host-types',
    },
];

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentHostType = ref<HostType | null>(null);
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
        key: 'description',
        label: 'Descripción',
    },
];

// Manejar evento de búsqueda
const handleSearch = (value: string) => {
    searchForm.search = value;
    searchForm.get(route('host-types.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['hostTypes'],
    });
};

// Manejar limpieza de búsqueda
const handleClearSearch = () => {
    searchForm.search = '';
    searchForm.get(route('host-types.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Abrir modal de creación
const openCreateModal = () => {
    formMode.value = 'create';
    currentHostType.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (hostType: HostType) => {
    formMode.value = 'edit';
    currentHostType.value = hostType;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (hostType: HostType) => {
    currentHostType.value = hostType;
    showDeleteModal.value = true;
};

// Eliminar tipo de anfitrión
const deleteHostType = () => {
    if (!currentHostType.value) return;

    deleteForm.delete(route('host-types.destroy', currentHostType.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
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
    router.get(route('host-types.index', { page }), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['hostTypes'],
    });
};
</script>

<template>
    <Head title="Gestionar Tipos de Anfitrión" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-4 sm:p-6">
            <div class="mb-8 flex flex-col justify-between space-y-4 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-2xl font-bold tracking-tight">Gestionar Tipos de Anfitrión</h1>

                <div class="flex flex-wrap gap-3">
                    <!-- Buscador usando el componente reutilizable -->
                    <SearchBar
                        v-model="search"
                        placeholder="Buscar por nombre..."
                        min-width="230px"
                        @search="handleSearch"
                        @clear="handleClearSearch"
                    />

                    <!-- Botón de nuevo usando el componente reutilizable - solo visible en desktop -->
                    <div class="hidden md:block">
                        <CreateButton
                            label="Nuevo Tipo"
                            @click="openCreateModal"
                        />
                    </div>
                </div>
            </div>

            <!-- Vista móvil de tipos de anfitrión -->
            <DataTableMobile
                :columns="columns"
                :items="hostTypes.data"
                :search-term="search"
                :hover="false"
                :showChevron="false"
                :pagination="hostTypes"
                @pagination-change="handlePaginationChange"
            >
                <!-- Slot para la celda de descripción -->
                <template #cell-description="{ value }">
                    <div class="line-clamp-2">
                        {{ value || 'Sin descripción' }}
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

            <!-- Tabla de tipos de anfitrión usando DataTable (visible solo en desktop) -->
            <DataTable
                :columns="columns"
                :items="hostTypes.data"
                :pagination="hostTypes"
                :search-term="search"
                class="hidden md:block"
                @pagination-change="handlePaginationChange"
            >
                <!-- Slot para la celda de descripción -->
                <template #cell-description="{ value }">
                    <div class="text-sm text-muted-foreground line-clamp-2">
                        {{ value || 'Sin descripción' }}
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

            <!-- Botón flotante para crear nuevo tipo (solo en móvil) -->
            <button
                @click="openCreateModal"
                class="md:hidden fixed bottom-6 right-6 bg-primary hover:bg-primary/90 text-white rounded-full p-3 shadow-lg flex items-center justify-center z-10 transition-transform hover:scale-105"
                aria-label="Crear nuevo tipo de anfitrión"
            >
                <Plus class="h-6 w-6" />
            </button>
        </div>
    </AppLayout>

    <!-- Modal unificado para creación/edición -->
    <Modal
        :show="showModal"
        :title="formMode === 'create' ? 'Crear Tipo de Anfitrión' : 'Editar Tipo de Anfitrión'"
        size="md"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="showModal = false"
    >
        <HostTypeForm
            :mode="formMode"
            :host-type="currentHostType"
            @success="handleFormSuccess"
            @error="handleFormError"
            @cancel="handleFormCancel"
        />
    </Modal>

    <!-- Modal de confirmación para eliminar - ahora usando el componente reutilizable -->
    <ConfirmDeleteModal
        :show="showDeleteModal"
        title="Eliminar Tipo de Anfitrión"
        :item-name="currentHostType?.name"
        :processing-delete="deleteForm.processing"
        message="¿Estás seguro de que deseas eliminar el tipo de anfitrión?"
        warning-message="Esta acción no se puede deshacer y podría afectar a los anfitriones que utilizan este tipo."
        @close="showDeleteModal = false"
        @confirm="deleteHostType"
    />
</template>
