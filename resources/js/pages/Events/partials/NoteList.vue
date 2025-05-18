<template>
    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
            <h2 class="text-lg font-medium">Notas del evento</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Buscador -->
                <SearchBar
                    v-model="search"
                    placeholder="Buscar por descripción..."
                    min-width="230px"
                    @search="handleSearch"
                    @clear="handleClearSearch"
                />
                <button
                    @click="openCreateModal"
                    class="cursor-pointer bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar nota
                </button>
            </div>
        </div>

        <!-- Lista de notas -->
        <div v-if="filteredNotes.length > 0" class="space-y-4">
            <div v-for="note in filteredNotes" :key="note.id" class="flex items-start justify-between border-b border-border/40 pb-4 last:border-0">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm text-muted-foreground line-clamp-2">{{ note.description }}</p>
                            <p class="font-medium mt-1">S/. {{ note.amount }}</p>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <ActionButtons gap="sm">
                        <EditButton
                            @click="openEditModal(note)"
                            size="sm"
                        />
                        <DeleteButton
                            @click="openDeleteModal(note)"
                            size="sm"
                        />
                    </ActionButtons>
                </div>
            </div>
        </div>

        <!-- Estado vacío con mensaje personalizado según búsqueda -->
        <div v-else class="py-10 flex flex-col items-center justify-center text-center">
            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                <FileText class="h-6 w-6 text-primary" />
            </div>
            <template v-if="search">
                <h3 class="text-lg font-medium mb-1">No se encontraron notas</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    No hay notas que coincidan con tu búsqueda. Intenta con otros términos o
                    <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">
                        limpia la búsqueda
                    </button>
                </p>
            </template>
            <template v-else>
                <h3 class="text-lg font-medium mb-1">Sin notas registradas</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    Este evento aún no tiene notas registradas. Agrega notas para llevar un registro de gastos u observaciones importantes.
                </p>
                <button
                    @click="openCreateModal"
                    class="cursor-pointer mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Agregar primera nota
                </button>
            </template>
        </div>

        <!-- Modal para crear/editar nota -->
        <Modal
            :show="showModal"
            :title="formMode === 'create' ? 'Agregar Nota' : 'Editar Nota'"
            size="xl"
            :closeOnClickOutside="false"
            :closeOnEsc="true"
            @close="closeModal"
        >
            <NoteForm
                :mode="formMode"
                :event-id="eventId"
                :note="currentNote"
                @success="handleSuccess"
                @cancel="closeModal"
            />
        </Modal>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Eliminar Nota"
            :item-name="`la nota seleccionada`"
            :processing-delete="deleteForm.processing"
            message="¿Estás seguro de que deseas eliminar esta nota?"
            warning-message="Esta acción no se puede deshacer."
            @close="showDeleteModal = false"
            @confirm="deleteNote"
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
import NoteForm from './NoteForm.vue';
import { FileText } from 'lucide-vue-next';

interface Note {
    id: number;
    event_id: number;
    description: string;
    amount: number;
}

const props = withDefaults(defineProps<{
    eventId: number;
    notes: Note[];
}>(), {
    notes: () => []
});

const emit = defineEmits(['noteUpdated']);

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentNote = ref<Note | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Estado para búsqueda
const search = ref('');

// Lista filtrada de notas
const filteredNotes = computed(() => {
    if (!search.value) return props.notes;

    const searchTerm = search.value.toLowerCase();
    return props.notes.filter(note =>
        note.description.toLowerCase().includes(searchTerm)
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
    currentNote.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (note: Note) => {
    formMode.value = 'edit';
    currentNote.value = note;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (note: Note) => {
    currentNote.value = note;
    showDeleteModal.value = true;
};

// Cerrar modal
const closeModal = () => {
    showModal.value = false;
    currentNote.value = null;
};

// Manejar éxito del formulario
const handleSuccess = () => {
    closeModal();
    emit('noteUpdated');
    showToast(
        formMode.value === 'create'
            ? 'Nota registrada exitosamente'
            : 'Nota actualizada exitosamente',
        'success'
    );
};

// Eliminar nota
const deleteNote = () => {
    if (!currentNote.value) return;

    deleteForm.delete(route('notes.destroy', currentNote.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            emit('noteUpdated');
            showToast('Nota eliminada exitosamente', 'success');
        },
    });
};
</script>
