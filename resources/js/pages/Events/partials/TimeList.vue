<template>
    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
            <h2 class="text-lg font-medium">Tiempos del evento</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Buscador -->
                <SearchBar
                    v-model="search"
                    placeholder="Buscar por tipo, hora..."
                    min-width="230px"
                    @search="handleSearch"
                    @clear="handleClearSearch"
                />
                <button
                    @click="openCreateModal"
                    class="cursor-pointer bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar tiempo
                </button>
            </div>
        </div>

        <!-- Lista de tiempos -->
        <div v-if="filteredTimes.length > 0" class="space-y-4">
            <div v-for="time in filteredTimes" :key="time.id" class="flex items-start justify-between border-b border-border/40 pb-4 last:border-0">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ time?.timeType?.name || 'Tipo no especificado' }}</h3>
                            <p class="text-sm text-muted-foreground">
                                {{ time.start_time }}
                                <template v-if="time.end_time">
                                    - {{ time.end_time }}
                                </template>
                            </p>
                        </div>
                        <div class="text-right text-sm text-muted-foreground">
                            <p v-if="time.description" class="line-clamp-2">{{ time.description }}</p>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <ActionButtons gap="sm">
                        <EditButton
                            @click="openEditModal(time)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(time)"
                            size="sm"
                        />
                    </ActionButtons>
                </div>
            </div>
        </div>

        <!-- Estado vacío con mensaje personalizado según búsqueda -->
        <div v-else class="py-10 flex flex-col items-center justify-center text-center">
            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                <Clock class="h-6 w-6 text-primary" />
            </div>
            <template v-if="search">
                <h3 class="text-lg font-medium mb-1">No se encontraron tiempos</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    No hay tiempos que coincidan con tu búsqueda. Intenta con otros términos o
                    <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">
                        limpia la búsqueda
                    </button>
                </p>
            </template>
            <template v-else>
                <h3 class="text-lg font-medium mb-1">Sin tiempos programados</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    Este evento aún no tiene tiempos programados. Agrega tiempos para definir el cronograma del evento.
                </p>
                <button
                    @click="openCreateModal"
                    class="cursor-pointer mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar primer tiempo
                </button>
            </template>
        </div>

        <!-- Modal para crear/editar tiempo -->
        <Modal
            :show="showModal"
            :title="formMode === 'create' ? 'Agregar Tiempo' : 'Editar Tiempo'"
            size="xl"
            :closeOnClickOutside="false"
            :closeOnEsc="true"
            @close="showModal = false"
        >
            <TimeForm
                :mode="formMode"
                :time="currentTime"
                :event-id="eventId"
                :time-types="timeTypes"
                @success="handleFormSuccess"
                @error="handleFormError"
                @cancel="handleFormCancel"
            />
        </Modal>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Eliminar Tiempo"
            :item-name="currentTime?.timeType?.name || 'Tiempo'"
            :processing-delete="deleteForm.processing"
            message="¿Estás seguro de que deseas eliminar este tiempo?"
            warning-message="Esta acción no se puede deshacer."
            @close="showDeleteModal = false"
            @confirm="deleteTime"
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
import { Clock } from 'lucide-vue-next';
import TimeForm from './TimeForm.vue';

interface TimeType {
    id: number;
    name: string;
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

const props = withDefaults(defineProps<{
    eventId: number;
    times: Time[];
    timeTypes: TimeType[];
}>(), {
    times: () => [],
    timeTypes: () => []
});

const emit = defineEmits(['timeUpdated']);

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentTime = ref<Time | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Estado para búsqueda
const search = ref('');

// Lista filtrada de tiempos
const filteredTimes = computed(() => {
    if (!search.value) return props.times;

    const searchTerm = search.value.toLowerCase();
    return props.times.filter(time =>
        (time?.timeType?.name || '').toLowerCase().includes(searchTerm) ||
        time.start_time.toLowerCase().includes(searchTerm) ||
        (time.end_time && time.end_time.toLowerCase().includes(searchTerm)) ||
        (time.description && time.description.toLowerCase().includes(searchTerm))
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
    currentTime.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (time: Time) => {
    formMode.value = 'edit';
    currentTime.value = time;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (time: Time) => {
    currentTime.value = time;
    showDeleteModal.value = true;
};

// Eliminar tiempo
const deleteTime = () => {
    if (!currentTime.value) return;

    deleteForm.delete(route('times.destroy', currentTime.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            emit('timeUpdated');
            showToast('Tiempo eliminado exitosamente', 'success');
        },
    });
};

// Manejar éxito del formulario
const handleFormSuccess = (message: string) => {
    showModal.value = false;
    emit('timeUpdated');
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
