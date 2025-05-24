<template>
    <div class="bg-card dark:bg-card/90 rounded-lg shadow-sm border border-border/40 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
            <h2 class="text-lg font-medium">Planos del evento</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Buscador -->
                <SearchBar
                    v-model="search"
                    placeholder="Buscar por tipo..."
                    min-width="230px"
                    @search="handleSearch"
                    @clear="handleClearSearch"
                />
                <button
                    @click="openCreateModal"
                    class="cursor-pointer bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Crear plano
                </button>
            </div>
        </div>

        <!-- Lista de planos -->
        <div v-if="filteredPlans.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="plan in filteredPlans" :key="plan.id" class="bg-background rounded-lg border border-border/40 overflow-hidden">
                <div class="aspect-video relative group">
                    <img :src="'/storage/' + plan.image" :alt="'Plano - ' + plan.planType.name" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click="openEditModal(plan)" class="p-2 bg-white/20 hover:bg-white/30 rounded-full transition-colors">
                            <Pencil class="h-5 w-5 text-white" />
                        </button>
                        <button @click="openDeleteModal(plan)" class="p-2 bg-white/20 hover:bg-white/30 rounded-full transition-colors">
                            <Trash class="h-5 w-5 text-white" />
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-medium">{{ plan.planType.name }}</h3>
                    <p v-if="plan.description" class="text-sm text-muted-foreground mt-1">{{ plan.description }}</p>
                </div>
            </div>
        </div>

        <!-- Estado vacío -->
        <div v-else class="py-10 flex flex-col items-center justify-center text-center">
            <div class="bg-primary/10 dark:bg-primary/5 p-3 rounded-full mb-3">
                <Map class="h-6 w-6 text-primary" />
            </div>
            <template v-if="search">
                <h3 class="text-lg font-medium mb-1">No se encontraron planos</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    No hay planos que coincidan con tu búsqueda. Intenta con otros términos o
                    <button @click="handleClearSearch" class="text-primary underline underline-offset-2 cursor-pointer">
                        limpia la búsqueda
                    </button>
                </p>
            </template>
            <template v-else>
                <h3 class="text-lg font-medium mb-1">Sin planos disponibles</h3>
                <p class="text-muted-foreground text-sm max-w-md">
                    Este evento aún no tiene planos diseñados. Crea planos para visualizar la distribución del espacio y las mesas.
                </p>
                <button
                    @click="openCreateModal"
                    class="mt-4 bg-primary hover:bg-primary/90 text-white rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Crear primer plano
                </button>
            </template>
        </div>

        <!-- Modal para crear/editar plano -->
        <Modal
            :show="showModal"
            :title="formMode === 'create' ? 'Crear Plano' : 'Editar Plano'"
            size="xl"
            :closeOnClickOutside="false"
            :closeOnEsc="true"
            @close="showModal = false"
        >
            <PlanForm
                :mode="formMode"
                :plan="currentPlan"
                :event-id="eventId"
                :plan-types="planTypes"
                @success="handleFormSuccess"
                @error="handleFormError"
                @cancel="handleFormCancel"
            />
        </Modal>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Eliminar Plano"
            :item-name="currentPlan?.planType?.name"
            :processing-delete="deleteForm.processing"
            message="¿Estás seguro de que deseas eliminar este plano?"
            warning-message="Esta acción no se puede deshacer."
            @close="showDeleteModal = false"
            @confirm="deletePlan"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import Modal from '@/components/Modal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import SearchBar from '@/components/SearchBar.vue';
import { Map, Pencil, Trash } from 'lucide-vue-next';
import PlanForm from './PlanForm.vue';

interface PlanType {
    id: number;
    name: string;
}

interface Plan {
    id: number;
    event_id: number;
    plan_type_id: number;
    image: string;
    description: string | null;
    planType: PlanType;
}

const props = defineProps<{
    eventId: number;
    plans: Plan[];
    planTypes: PlanType[];
}>();

const emit = defineEmits(['planUpdated']);

// Estado para modales
const showModal = ref(false);
const showDeleteModal = ref(false);
const currentPlan = ref<Plan | null>(null);
const formMode = ref<'create' | 'edit'>('create');

// Toast global
const { showToast } = useToast();

// Formulario de eliminación
const deleteForm = useForm({});

// Estado para búsqueda
const search = ref('');

// Lista filtrada de planos
const filteredPlans = computed(() => {
    if (!search.value) return props.plans;

    const searchTerm = search.value.toLowerCase();
    return props.plans.filter(plan =>
        plan.planType.name.toLowerCase().includes(searchTerm) ||
        (plan.description && plan.description.toLowerCase().includes(searchTerm))
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
    currentPlan.value = null;
    showModal.value = true;
};

// Abrir modal de edición
const openEditModal = (plan: Plan) => {
    formMode.value = 'edit';
    currentPlan.value = plan;
    showModal.value = true;
};

// Abrir modal de eliminación
const openDeleteModal = (plan: Plan) => {
    currentPlan.value = plan;
    showDeleteModal.value = true;
};

// Eliminar plano
const deletePlan = () => {
    if (!currentPlan.value) return;

    deleteForm.delete(route('plans.destroy', currentPlan.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            emit('planUpdated');
            showToast('Plano eliminado exitosamente', 'success');
        },
    });
};

// Manejar éxito del formulario
const handleFormSuccess = (message: string) => {
    showModal.value = false;
    emit('planUpdated');
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
