<template>
    <Head title="Registro Manual de Acceso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-4 sm:p-6">
            <div class="mb-8 flex flex-col justify-between space-y-4 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-2xl font-bold tracking-tight">Registro Manual de Acceso</h1>

                <div class="flex flex-wrap gap-3">
                    <!-- Buscador -->
                    <SearchBar
                        v-model="search"
                        placeholder="Buscar por nombre o DNI..."
                        min-width="230px"
                        @search="handleSearch"
                        @clear="handleClearSearch"
                    />
                </div>
            </div>

            <!-- Vista móvil -->
            <DataTableMobile
                :columns="columns"
                :items="guests.data"
                :pagination="guests"
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
                        <div class="font-medium">{{ value.full_name }}</div>
                        <div class="text-sm text-muted-foreground">
                            <span>DNI: {{ value.dni || 'No registrado' }}</span>
                            <span class="mx-2">•</span>
                            <span>Mesa: {{ value.table_number }}</span>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            <span>Pases: {{ value.used_passes }}/{{ value.passes }}</span>
                            <span class="mx-2">•</span>
                            <span>Evento: {{ value.event_name }}</span>
                        </div>
                    </div>
                </template>

                <!-- Slot para acciones -->
                <template #cell-actions="{ item }">
                    <ActionButtons gap="sm">
                        <QrButton
                            @click="showQR(item)"
                            size="sm"
                        />
                        <ManualRegisterButton
                            @click="openAccessModal(item)"
                            size="sm"
                            :disabled="item.available_passes <= 0"
                            label="Registrar"
                        />
                    </ActionButtons>
                </template>
            </DataTableMobile>

            <!-- Vista desktop -->
            <DataTable
                :columns="columns"
                :items="guests.data"
                :pagination="guests"
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
                        <div class="font-medium">{{ value.full_name }}</div>
                        <div class="text-sm text-muted-foreground">
                            <span>DNI: {{ value.dni || 'No registrado' }}</span>
                            <span class="mx-2">•</span>
                            <span>Mesa: {{ value.table_number }}</span>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            <span>Pases: {{ value.used_passes }}/{{ value.passes }}</span>
                            <span class="mx-2">•</span>
                            <span>Evento: {{ value.event_name }}</span>
                        </div>
                    </div>
                </template>

                <!-- Slot para acciones -->
                <template #cell-actions="{ item }">
                    <ActionButtons gap="sm">
                        <QrButton
                            @click="showQR(item)"
                            size="sm"
                        />
                        <ManualRegisterButton
                            @click="openAccessModal(item)"
                            size="sm"
                            :disabled="item.available_passes <= 0"
                            label="Registrar"
                        />
                    </ActionButtons>
                </template>
            </DataTable>
        </div>
    </AppLayout>

    <!-- Modal para mostrar QR -->
    <Modal
        :show="showQRModal"
        title="Código QR del Invitado"
        size="md"
        @close="closeQRModal"
    >
        <div class="flex flex-col items-center justify-center p-4">
            <div class="mb-4 text-center">
                <h3 class="font-medium">{{ currentGuest?.full_name }}</h3>
                <p class="text-sm text-muted-foreground">DNI: {{ currentGuest?.dni || 'No registrado' }}</p>
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

    <!-- Modal para registro de acceso -->
    <Modal
        :show="showAccessModal"
        title="Registrar Acceso"
        size="lg"
        :closeOnClickOutside="false"
        :closeOnEsc="true"
        @close="closeAccessModal"
    >
        <GuestAccessForm
            v-if="currentGuest"
            :qr-code="currentGuest.qr_code"
            :guest-data="guestData"
            @success="handleAccessSuccess"
            @error="handleAccessError"
            @cancel="closeAccessModal"
        />
    </Modal>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable/DataTable.vue';
import DataTableMobile from '@/components/DataTableMobile.vue';
import Modal from '@/components/Modal.vue';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import GuestAccessForm from './GuestAccessForm.vue';
import SearchBar from '@/components/SearchBar.vue';
import ActionButtons from '@/components/ActionButtons.vue';
import QrButton from '@/components/QrButton.vue';
import { DoorOpen, Download } from 'lucide-vue-next';
import QRCode from 'qrcode.vue';
import ManualRegisterButton from '@/components/ManualRegisterButton.vue';
import { router } from '@inertiajs/vue3';

interface Guest {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    dni: string;
    table_number: number;
    passes: number;
    used_passes: number;
    available_passes: number;
    qr_code: string;
    event_name: string;
    last_access: string | null;
}

interface Props {
    guests: {
        data: Guest[];
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
const showQRModal = ref(false);
const showAccessModal = ref(false);
const currentGuest = ref<Guest | null>(null);

// Estado para búsqueda
const search = ref(props.filters.search || '');

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Registro Manual',
        href: '/manual-guest-accesses',
    },
];

const columns = [
    {
        key: 'guest',
        label: 'Invitado',
    },
    {
        key: 'actions',
        label: 'Acciones',
    },
];

// Computed para datos del invitado en formato requerido por GuestAccessForm
const guestData = computed(() => {
    if (!currentGuest.value) return null;
    return {
        id: currentGuest.value.id,
        first_name: currentGuest.value.first_name,
        last_name: currentGuest.value.last_name,
        table_number: currentGuest.value.table_number,
        total_passes: currentGuest.value.passes,
        available_passes: currentGuest.value.available_passes,
        event_name: currentGuest.value.event_name
    };
});

// Manejar búsqueda
const handleSearch = (value: string) => {
    router.get(
        route('manual-guest-accesses.index'),
        { search: value },
        { preserveState: true, preserveScroll: true }
    );
};

// Limpiar búsqueda
const handleClearSearch = () => {
    router.get(
        route('manual-guest-accesses.index'),
        { search: '' },
        { preserveState: true, preserveScroll: true }
    );
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
    link.download = `qr-${currentGuest.value.dni || currentGuest.value.id}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
};

// Abrir modal de acceso
const openAccessModal = async (guest: Guest) => {
    try {
        const response = await fetch(route('manual-guest-accesses.validate', guest.id));
        const data = await response.json();

        if (data.status === 'success') {
            currentGuest.value = guest;
            showAccessModal.value = true;
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Error al validar el invitado', 'error');
    }
};

// Cerrar modal de acceso
const closeAccessModal = () => {
    showAccessModal.value = false;
    currentGuest.value = null;
};

// Manejar éxito del registro de acceso
const handleAccessSuccess = (message: string) => {
    closeAccessModal();
    showToast(message, 'success');
};

// Manejar error del registro de acceso
const handleAccessError = (message: string) => {
    showToast(message, 'error');
};
</script>
