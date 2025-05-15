<script setup lang="ts">
import { computed, withDefaults, defineProps, defineEmits } from 'vue';
import { ChevronRight } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

// Definir las propiedades del componente
interface Column {
    key: string;
    label: string;
    hideOnMobile?: boolean;
    format?: (value: any, row: any) => string | number;
}

interface PaginationInfo {
    links?: { url: string | null; label: string; active: boolean }[];
    total?: number;
    from?: number;
    to?: number;
    current_page?: number;
    last_page?: number;
    per_page?: number;
}

interface Props {
    // Datos y estructura
    columns: Column[];
    items: any[];
    itemKey?: string;

    // Paginación
    pagination?: PaginationInfo;

    // Configuración
    titleKey?: string;
    subtitleKey?: string;
    imageKey?: string;
    colorKey?: string;
    showColorIndicator?: boolean;

    // Estilos
    divider?: boolean;
    rounded?: boolean;
    hover?: boolean;

    // Navegación
    showChevron?: boolean;

    // Estado
    loading?: boolean;
    emptyText?: string;
    searchEmptyText?: string;
    searchTerm?: string;
}

const props = withDefaults(defineProps<Props>(), {
    itemKey: 'id',
    titleKey: 'name',
    subtitleKey: '',
    imageKey: '',
    colorKey: '',
    showColorIndicator: false,
    divider: true,
    rounded: true,
    hover: true,
    showChevron: true,
    loading: false,
    emptyText: 'No hay datos para mostrar',
    searchEmptyText: 'No se encontraron resultados para la búsqueda',
    searchTerm: '',
});

const emit = defineEmits(['item-click', 'pagination-change']);

// Manejar click en un elemento
const handleItemClick = (item: any, index: number) => {
    emit('item-click', { item, index });
};

// Manejar cambio de página
const handlePaginationChange = (page: number) => {
    emit('pagination-change', page);
};

// Filtrar columnas visibles para móvil
const visibleColumns = computed(() => {
    return props.columns.filter(column => !column.hideOnMobile);
});

// Verificar si hay datos
const hasData = computed(() => {
    return props.items && props.items.length > 0;
});

// Determinar si usar color personalizado
const useColor = computed(() => {
    return props.colorKey && props.colorKey.length > 0 && props.showColorIndicator;
});

// Verificar si hay paginación disponible
const hasPagination = computed(() => {
    if (!props.pagination) return false;

    // Verificar si hay links de paginación o si hay más de una página
    if (props.pagination.links && props.pagination.links.length > 3) return true;
    if (props.pagination.last_page && props.pagination.last_page > 1) return true;
    if (props.pagination.total && props.pagination.total > (props.pagination.per_page || 10)) return true;

    return false;
});
</script>

<template>
    <div class="md:hidden">
        <!-- Estado de carga -->
        <div v-if="loading" class="py-8 flex justify-center items-center">
            <div class="flex flex-col items-center justify-center space-y-3">
                <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                <p class="text-sm text-muted-foreground">Cargando...</p>
            </div>
        </div>

        <!-- Lista de elementos para móvil -->
        <div v-else-if="hasData" class="space-y-3">
            <div
                v-for="(item, index) in items"
                :key="item[itemKey] || index"
                :class="[
                    'bg-card/50 border border-border/40 shadow-sm backdrop-blur-sm p-4',
                    rounded ? 'rounded-xl' : '',
                    hover ? 'hover:bg-muted/40 transition-colors cursor-pointer' : '',
                ]"
                @click="hover ? handleItemClick(item, index) : null"
            >
                <div class="flex items-start gap-3">
                    <!-- Avatar/Color indicator (opcional) -->
                    <div v-if="useColor && item[colorKey]" class="flex-shrink-0">
                        <div
                            class="h-10 w-10 rounded-md shadow-sm ring-1 ring-black/5"
                            :style="{ backgroundColor: item[colorKey] }"
                        ></div>
                    </div>

                    <!-- Imagen (opcional) -->
                    <div v-else-if="imageKey && item[imageKey]" class="flex-shrink-0">
                        <img
                            :src="item[imageKey]"
                            :alt="item[titleKey]"
                            class="h-10 w-10 rounded-md object-cover"
                        />
                    </div>

                    <!-- Contenido principal -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <!-- Título y subtítulo -->
                            <div>
                                <h3 class="text-base font-medium truncate">
                                    {{ item[titleKey] }}
                                </h3>
                                <p v-if="subtitleKey && item[subtitleKey]" class="text-sm text-muted-foreground truncate">
                                    {{ item[subtitleKey] }}
                                </p>
                            </div>

                            <!-- Icono de navegación (opcional) -->
                            <ChevronRight v-if="showChevron" class="h-5 w-5 text-muted-foreground/60 flex-shrink-0" />
                        </div>

                        <!-- Datos adicionales -->
                        <div class="mt-2 space-y-1">
                            <div
                                v-for="column in visibleColumns"
                                :key="column.key"
                                v-show="column.key !== titleKey && column.key !== subtitleKey"
                                class="grid grid-cols-[auto_1fr] gap-2"
                            >
                                <span class="text-xs font-medium text-muted-foreground">{{ column.label }}:</span>
                                <span class="text-xs">
                                    <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]" :column="column" :index="index">
                                        <template v-if="column.format">
                                            {{ column.format(item[column.key], item) }}
                                        </template>
                                        <template v-else>
                                            {{ item[column.key] || '-' }}
                                        </template>
                                    </slot>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div v-if="$slots.actions" class="mt-3 pt-2 flex justify-end" :class="{ 'border-t border-border/30': divider }">
                    <slot name="actions" :item="item" :index="index"></slot>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="hasPagination" class="mt-4 bg-card/50 border border-border/40 rounded-xl shadow-sm backdrop-blur-sm overflow-hidden">
                <slot name="pagination" :pagination="pagination" :on-change="handlePaginationChange">
                    <Pagination
                        :links="pagination?.links"
                        :total="pagination?.total"
                        :from="pagination?.from"
                        :to="pagination?.to"
                        @page-change="handlePaginationChange"
                    />
                </slot>
            </div>
        </div>

        <!-- Estado vacío -->
        <div v-else class="py-10 flex justify-center">
            <slot name="empty">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="p-3 rounded-full bg-muted/60">
                        <svg class="h-6 w-6 text-muted-foreground/60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-muted-foreground">
                        {{ searchTerm ? searchEmptyText : emptyText }}
                    </p>
                    <slot name="empty-actions"></slot>
                </div>
            </slot>
        </div>
    </div>
</template>
