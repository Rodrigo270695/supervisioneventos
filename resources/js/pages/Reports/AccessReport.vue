<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable/DataTable.vue';
import DataTableMobile from '@/components/DataTableMobile.vue';
import FormSelect from '@/components/FormSelect.vue';
import { Download } from 'lucide-vue-next';

// Función para formatear fechas y horas
const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

interface Props {
    events: Array<{
        id: number;
        name: string;
    }>;
    accesses?: {
        data: Array<{
            id: number;
            guest: {
                first_name: string;
                last_name: string;
                table_number: number;
            };
            access_type: string;
            access_datetime: string;
            people_count: number;
            observations: string | null;
        }>;
        links: Array<{
            url?: string;
            label: string;
            active: boolean;
        }>;
        total: number;
        from: number;
        to: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    selectedEventId?: number;
}

const props = defineProps<Props>();

// Estado para el evento seleccionado
const selectedEventId = ref(props.selectedEventId || '');

// Formulario para cambiar de evento
const form = useForm({
    event_id: props.selectedEventId || '',
});

// Configuración de breadcrumbs
const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Reportes de Acceso',
        href: '/reports/access',
    },
];

// Definición de columnas para la tabla
const columns = [
    {
        key: 'guest',
        label: 'Invitado',
    },
    {
        key: 'access_type',
        label: 'Tipo',
    },
    {
        key: 'access_datetime',
        label: 'Fecha y Hora',
    },
    {
        key: 'people_count',
        label: 'Personas',
    },
    {
        key: 'observations',
        label: 'Observaciones',
    },
];

// Formatear el tipo de acceso
const formatAccessType = (type: string) => {
    return type === 'entry' ? 'Entrada' : 'Salida';
};

// Obtener las clases para el tipo de acceso
const getAccessTypeClasses = (type: string) => {
    const baseClasses = 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium';
    return type === 'entry'
        ? `${baseClasses} bg-green-50 text-green-700`
        : `${baseClasses} bg-red-50 text-red-700`;
};

// Manejar cambio de evento
watch(selectedEventId, (value) => {
    if (value) {
        form.event_id = value;
        form.get(route('reports.access'), {
            preserveState: true,
            preserveScroll: true,
            only: ['accesses'],
        });
    }
});

// Función para descargar el reporte
const downloadReport = () => {
    if (selectedEventId.value) {
        window.location.href = route('reports.access.export', selectedEventId.value);
    }
};
</script>

<template>
    <Head title="Reportes de Acceso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-xl p-4 sm:p-6">
            <div class="mb-8 flex flex-col justify-between space-y-4 md:flex-row md:items-center md:space-y-0">
                <h1 class="text-2xl font-bold tracking-tight">Reportes de Acceso</h1>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de eventos -->
                    <FormSelect
                        v-model="selectedEventId"
                        :options="events"
                        placeholder="Seleccionar evento"
                        class="w-full sm:w-[250px]"
                    />

                    <!-- Botón de descarga - solo visible si hay evento seleccionado -->
                    <button
                        v-if="selectedEventId"
                        @click="downloadReport"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Descargar Excel
                    </button>
                </div>
            </div>

            <div v-if="!selectedEventId" class="text-center py-12 text-muted-foreground">
                Selecciona un evento para ver sus registros de acceso
            </div>

            <template v-else>
                <!-- Vista móvil -->
                <DataTableMobile
                    v-if="accesses"
                    :columns="columns"
                    :items="accesses.data"
                    :pagination="accesses"
                    :hover="false"
                    :showChevron="false"
                >
                    <!-- Slot para la celda de invitado -->
                    <template #cell-guest="{ value }">
                        <div>
                            <div class="font-medium">{{ value.first_name }} {{ value.last_name }}</div>
                            <div class="text-sm text-muted-foreground">Mesa: {{ value.table_number }}</div>
                        </div>
                    </template>

                    <!-- Slot para la celda de tipo de acceso -->
                    <template #cell-access_type="{ value }">
                        <span :class="getAccessTypeClasses(value)">
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
                    v-if="accesses"
                    :columns="columns"
                    :items="accesses.data"
                    :pagination="accesses"
                    class="hidden md:block"
                >
                    <!-- Slot para la celda de invitado -->
                    <template #cell-guest="{ value }">
                        <div>
                            <div class="font-medium">{{ value.first_name }} {{ value.last_name }}</div>
                            <div class="text-sm text-muted-foreground">Mesa: {{ value.table_number }}</div>
                        </div>
                    </template>

                    <!-- Slot para la celda de tipo de acceso -->
                    <template #cell-access_type="{ value }">
                        <span :class="getAccessTypeClasses(value)">
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
            </template>
        </div>
    </AppLayout>
</template>
