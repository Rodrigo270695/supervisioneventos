<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable/DataTable.vue';
import DataTableMobile from '@/components/DataTableMobile.vue';
import CreateButton from '@/components/CreateButton.vue';
import Modal from '@/components/Modal.vue';
import { QrCode } from 'lucide-vue-next';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';
import GuestAccessForm from './GuestAccessForm.vue';
import QrScanner from '@/components/QrScanner.vue';
import SearchBar from '@/components/SearchBar.vue';
import { useForm } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';

interface GuestAccess {
    id: number;
    guest: {
        first_name: string;
        last_name: string;
        table_number: number;
    };
    people_count: number;
    access_datetime: string;
    access_type: string;
    observations: string | null;
}

interface Props {
    guestAccesses: {
        data: GuestAccess[];
        links: { url: string | null; label: string; active: boolean }[];
        total: number;
        from: number;
        to: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    filters: {
        search: string;
    };
}

const props = defineProps<Props>();
const { showToast } = useToast();

// Estado para modales
const showModal = ref(false);
const showQrScanner = ref(false);
const showErrorModal = ref(false);
const errorMessage = ref('');
const currentQrCode = ref('');
const currentGuestData = ref(null);

// Estado para búsqueda
const search = ref(props.filters.search || '');

// Formulario de búsqueda
const searchForm = useForm({
    search: props.filters.search || '',
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Control de Acceso',
        href: '/guest-accesses',
    },
];

const columns = [
    {
        key: 'guest',
        label: 'Invitado',
    },
    {
        key: 'people_count',
        label: 'Personas',
    },
    {
        key: 'access_datetime',
        label: 'Fecha y Hora',
    },
    {
        key: 'access_type',
        label: 'Tipo',
    },
    {
        key: 'observations',
        label: 'Observaciones',
    },
];

const formatAccessType = (type: string) => {
    return type === 'entry' ? 'Entrada' : 'Salida';
};

const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString('es-ES', {
        dateStyle: 'short',
        timeStyle: 'short',
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
    currentQrCode.value = '';
    currentGuestData.value = null;
};

// Manejar escaneo de QR
const handleQrDecode = async (decodedString: string) => {
    try {
        // Obtener el token CSRF de la cookie
        const csrfToken = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN'))
            ?.split('=')[1];

        if (!csrfToken) {
            errorMessage.value = 'Error: No se encontró el token CSRF';
            showQrScanner.value = false;
            showModal.value = false;
            showErrorModal.value = true;
            return;
        }

        const response = await fetch('/api/guest-accesses/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(csrfToken),
            },
            body: JSON.stringify({ qr_code: decodedString }),
            credentials: 'include',
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.message || `Error: ${response.statusText}`;
            showQrScanner.value = false;
            showModal.value = false;
            showErrorModal.value = true;
            return;
        }

        if (data.status === 'success') {
            currentQrCode.value = decodedString;
            currentGuestData.value = data.guest;
            showQrScanner.value = false;
            showModal.value = true;
        } else {
            errorMessage.value = data.message || 'QR no válido';
            showQrScanner.value = false;
            showModal.value = false;
            showErrorModal.value = true;
        }
    } catch (error: any) {
        errorMessage.value = error.message || 'Error al validar el código QR';
        showQrScanner.value = false;
        showModal.value = false;
        showErrorModal.value = true;
    }
};

const handleQrError = (error: string) => {
    errorMessage.value = error;
    showQrScanner.value = false;
    showModal.value = false;
    showErrorModal.value = true;
};

// Cerrar modal de error
const closeErrorModal = () => {
    showErrorModal.value = false;
    errorMessage.value = '';
};

// Abrir modal de escaneo
const openScanModal = () => {
    showModal.value = true;
    showQrScanner.value = true;
    currentQrCode.value = '';
    currentGuestData.value = null;
};

// Manejar evento de búsqueda
const handleSearch = (value: string) => {
    searchForm.search = value;
    searchForm.get(route('guest-accesses.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['guestAccesses'],
    });
};

// Manejar limpieza de búsqueda
const handleClearSearch = () => {
    searchForm.search = '';
    searchForm.get(route('guest-accesses.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Control de Acceso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-4 sm:p-6">
            <div class="mb-8 flex flex-col justify-between space-y-4 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-2xl font-bold tracking-tight">Control de Acceso</h1>

                <div class="flex flex-wrap gap-3">
                    <!-- Buscador -->
                    <SearchBar
                        v-model="search"
                        placeholder="Buscar por nombre..."
                        min-width="230px"
                        @search="handleSearch"
                        @clear="handleClearSearch"
                    />

                    <!-- Botón de escaneo - solo visible en desktop -->
                    <div class="hidden md:block">
                        <CreateButton
                            label="Escanear QR"
                            :icon="QrCode"
                            @click="openScanModal"
                        />
                    </div>
                </div>
            </div>

            <!-- Vista móvil -->
            <DataTableMobile
                :columns="columns"
                :items="guestAccesses.data"
                :pagination="guestAccesses"
                :search-term="search"
                :hover="false"
                :showChevron="false"
            >
                <!-- Slot para acciones en estado vacío -->
                <template #empty-actions>
                    <p v-if="search" class="text-sm text-muted-foreground/70">
                        Intenta con otros términos o <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">limpia la búsqueda</button>
                    </p>
                </template>

                <!-- Slot para la celda de invitado -->
                <template #cell-guest="{ value }">
                    <div>
                        <div class="font-medium">{{ value.first_name }} {{ value.last_name }}</div>
                        <div class="text-sm text-muted-foreground">Mesa: {{ value.table_number }}</div>
                    </div>
                </template>

                <!-- Slot para la celda de tipo de acceso -->
                <template #cell-access_type="{ value }">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                            value === 'entry'
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700'
                        ]"
                    >
                        {{ formatAccessType(value) }}
                    </span>
                </template>

                <!-- Slot para la celda de fecha y hora -->
                <template #cell-access_datetime="{ value }">
                    {{ formatDateTime(value) }}
                </template>

                <!-- Slot para la celda de observaciones -->
                <template #cell-observations="{ value }">
                    <div class="line-clamp-2">
                        {{ value || 'Sin observaciones' }}
                    </div>
                </template>
            </DataTableMobile>

            <!-- Vista desktop -->
            <DataTable
                :columns="columns"
                :items="guestAccesses.data"
                :pagination="guestAccesses"
                :search-term="search"
                class="hidden md:block"
            >
                <!-- Slot para acciones en estado vacío -->
                <template #empty-actions>
                    <p v-if="search" class="text-sm text-muted-foreground/70">
                        Intenta con otros términos o <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">limpia la búsqueda</button>
                    </p>
                </template>

                <!-- Slot para la celda de invitado -->
                <template #cell-guest="{ value }">
                    <div>
                        <div class="font-medium">{{ value.first_name }} {{ value.last_name }}</div>
                        <div class="text-sm text-muted-foreground">Mesa: {{ value.table_number }}</div>
                    </div>
                </template>

                <!-- Slot para la celda de tipo de acceso -->
                <template #cell-access_type="{ value }">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                            value === 'entry'
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700'
                        ]"
                    >
                        {{ formatAccessType(value) }}
                    </span>
                </template>

                <!-- Slot para la celda de fecha y hora -->
                <template #cell-access_datetime="{ value }">
                    {{ formatDateTime(value) }}
                </template>

                <!-- Slot para la celda de observaciones -->
                <template #cell-observations="{ value }">
                    <div class="line-clamp-2">
                        {{ value || 'Sin observaciones' }}
                    </div>
                </template>
            </DataTable>

            <!-- Botón flotante para escanear (solo en móvil) -->
            <button
                @click="openScanModal"
                class="md:hidden fixed bottom-6 right-6 bg-primary hover:bg-primary/90 text-white rounded-full p-3 shadow-lg flex items-center justify-center z-10 transition-transform hover:scale-105"
                aria-label="Escanear código QR"
            >
                <QrCode class="h-6 w-6" />
            </button>
        </div>
    </AppLayout>

    <!-- Modal para escaneo y registro -->
    <Modal
        :show="showModal"
        :title="currentGuestData ? 'Registrar Acceso' : 'Escanear QR'"
        size="lg"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="handleFormCancel"
    >
        <GuestAccessForm
            v-if="currentQrCode && currentGuestData"
            :qr-code="currentQrCode"
            :guest-data="currentGuestData"
            @success="handleFormSuccess"
            @error="handleFormError"
            @cancel="handleFormCancel"
        />
        <QrScanner
            v-else
            @decode="handleQrDecode"
            @error="handleQrError"
        />
    </Modal>

    <!-- Modal de error -->
    <Modal
        :show="showErrorModal"
        title="Error de Validación"
        size="sm"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="closeErrorModal"
    >
        <div class="flex flex-col items-center space-y-4 p-4">
            <div class="rounded-full bg-rose-50 p-3">
                <svg class="h-6 w-6 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-center text-gray-700">{{ errorMessage }}</p>
            <button
                @click="closeErrorModal"
                class="mt-4 w-full rounded-md bg-rose-100 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2"
            >
                Cerrar
            </button>
        </div>
    </Modal>
</template>
