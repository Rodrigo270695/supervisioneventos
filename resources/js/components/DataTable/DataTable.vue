<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Search } from 'lucide-vue-next';
import { computed, provide, withDefaults, defineProps, defineEmits } from 'vue';

// Definir las propiedades del componente
interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: string;
    class?: string;
    headerClass?: string;
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

    // Estilos y comportamiento
    hover?: boolean;
    striped?: boolean;
    bordered?: boolean;
    dense?: boolean;
    useCard?: boolean;
    emptyText?: string;
    searchEmptyText?: string;

    // Estado
    loading?: boolean;
    searchTerm?: string;
}

const props = withDefaults(defineProps<Props>(), {
    itemKey: 'id',
    hover: true,
    striped: false,
    bordered: true,
    dense: false,
    useCard: true,
    emptyText: 'No hay datos para mostrar',
    searchEmptyText: 'No se encontraron resultados para la búsqueda',
    loading: false,
    searchTerm: '',
});

const emit = defineEmits(['row-click', 'pagination-change']);

// Manejar click en una fila
const handleRowClick = (item: any, index: number) => {
    emit('row-click', { item, index });
};

// Manejar cambio de página
const handlePaginationChange = (page: number) => {
    emit('pagination-change', page);
};

// Computar clases para la tabla
const tableClasses = computed(() => {
    return {
        'min-w-full': true,
        'divide-y': true,
        'divide-border/60': true,
    };
});

// Computar clases para el thead
const theadClasses = computed(() => {
    return {
        'bg-muted/60': true,
    };
});

// Computar clases para el tbody
const tbodyClasses = computed(() => {
    return {
        'divide-y': true,
        'divide-border/40': true,
        'bg-card/30': true,
    };
});

// Computar clases para las filas
const rowClasses = computed(() => {
    return {
        'transition-colors': true,
        'hover:bg-muted/40': props.hover,
        'cursor-pointer': true,
        group: true,
    };
});

// Verificar si hay datos
const hasData = computed(() => {
    return props.items && props.items.length > 0;
});

// Proveer propiedades al contexto para uso en slots
provide('dataTable', {
    columns: props.columns,
    items: props.items,
    hover: props.hover,
    striped: props.striped,
    bordered: props.bordered,
    dense: props.dense,
});
</script>

<template>
    <div :class="['border-border/40 overflow-hidden rounded-xl border shadow-sm backdrop-blur-sm', useCard ? 'bg-card/50' : '']">
        <!-- Tabla con scroll horizontal -->
        <div class="overflow-x-auto">
            <table :class="tableClasses">
                <!-- Cabeceras de tabla -->
                <thead :class="theadClasses">
                    <tr>
                        <th
                            v-for="(column, index) in columns"
                            :key="index"
                            :class="[
                                'text-muted-foreground px-6 py-4 text-xs font-medium tracking-wider uppercase',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : 'text-left',
                                column.headerClass || '',
                            ]"
                            :style="column.width ? { width: column.width } : {}"
                        >
                            <slot :name="`header-${column.key}`" :column="column">
                                {{ column.label }}
                            </slot>
                        </th>

                        <!-- Columna adicional para acciones si existe el slot -->
                        <th v-if="$slots.actions" class="text-muted-foreground px-6 py-4 text-right text-xs font-medium tracking-wider uppercase">
                            <slot name="actions-header">Acciones</slot>
                        </th>
                    </tr>
                </thead>

                <!-- Cuerpo de la tabla -->
                <tbody :class="tbodyClasses">
                    <!-- Estado de carga -->
                    <tr v-if="loading">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-10 text-center">
                            <slot name="loading">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="border-primary inline-block h-6 w-6 animate-spin rounded-full border-2 border-t-transparent"></div>
                                    <p class="text-muted-foreground text-sm">Cargando...</p>
                                </div>
                            </slot>
                        </td>
                    </tr>

                    <!-- Filas de datos -->
                    <template v-else-if="hasData">
                        <tr v-for="(item, index) in items" :key="item[itemKey] || index" :class="rowClasses" @click="handleRowClick(item, index)">
                            <!-- Celdas de datos -->
                            <td
                                v-for="(column, colIndex) in columns"
                                :key="colIndex"
                                :class="[
                                    'px-6 py-5',
                                    column.align === 'center' ? 'text-center' : '',
                                    column.align === 'right' ? 'text-right' : 'text-left',
                                    column.key === 'actions' ? 'whitespace-nowrap' : '',
                                    column.class || '',
                                ]"
                            >
                                <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]" :column="column" :index="index">
                                    <template v-if="column.format">
                                        {{ column.format(item[column.key], item) }}
                                    </template>
                                    <template v-else>
                                        {{ item[column.key] }}
                                    </template>
                                </slot>
                            </td>

                            <!-- Columna de acciones -->
                            <td v-if="$slots.actions" class="px-6 py-5 text-right whitespace-nowrap">
                                <slot name="actions" :item="item" :index="index"></slot>
                            </td>
                        </tr>
                    </template>

                    <!-- Estado vacío -->
                    <tr v-else>
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-12 text-center">
                            <slot name="empty">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="bg-muted/60 rounded-full p-3">
                                        <Search class="text-muted-foreground/60 h-6 w-6" />
                                    </div>
                                    <p class="text-muted-foreground font-medium">
                                        {{ searchTerm ? searchEmptyText : emptyText }}
                                    </p>
                                    <slot name="empty-actions"></slot>
                                </div>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="pagination && hasData" class="border-border/40 border-t">
            <slot name="pagination" :pagination="pagination" :on-change="handlePaginationChange">
                <Pagination
                    :links="pagination.links"
                    :total="pagination.total"
                    :from="pagination.from"
                    :to="pagination.to"
                    @page-change="handlePaginationChange"
                />
            </slot>
        </div>
    </div>
</template>
