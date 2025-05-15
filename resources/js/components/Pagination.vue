<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    total?: number;
    from?: number;
    to?: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['page-change']);

// Computed properties para filtrar los enlaces
const previousLink = computed(() =>
    props.links.find(link => link.label.includes('Previous') && link.url !== null)
);

const nextLink = computed(() =>
    props.links.find(link => link.label.includes('Next') && link.url !== null)
);

const pageLinks = computed(() =>
    props.links.slice(1, -1)
);

/**
 * Determina si el enlace es un enlace actual
 */
function isCurrent(link: PaginationLink) {
    return link.active;
}

/**
 * Determina si el enlace es un separador (...)
 */
function isEllipsis(link: PaginationLink) {
    return link.label.includes('...');
}

/**
 * Limpia el texto de la etiqueta
 */
function cleanLabel(label: string) {
    return label.replace('&laquo; Previous', '')
        .replace('Next &raquo;', '')
        .trim();
}

/**
 * Extraer número de página de una URL
 */
function getPageFromUrl(url: string): number {
    const match = url.match(/page=(\d+)/);
    return match ? parseInt(match[1]) : 1;
}

/**
 * Manejar cambio de página
 */
function handlePageChange(url: string | null, event: Event) {
    if (url) {
        const page = getPageFromUrl(url);
        emit('page-change', page);
        event.preventDefault();
    }
}
</script>

<template>
    <div class="mt-4 mb-2 flex flex-wrap items-center justify-between gap-2 px-4 py-2">
        <!-- Información de registros mostrados -->
        <div class="text-sm text-muted-foreground">
            <span v-if="from && to && total">
                Mostrando {{ from }} a {{ to }} de {{ total }} registros
            </span>
            <span v-else>
                Mostrando registros
            </span>
        </div>

        <!-- Paginación a la derecha -->
        <div class="flex items-center gap-2">
            <!-- Botón Anterior -->
            <Link
                v-if="previousLink"
                :href="previousLink.url"
                class="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm font-medium text-card-foreground transition-colors hover:bg-muted"
                @click="(e) => handlePageChange(previousLink.url, e)"
            >
                <ChevronLeft class="mr-1 h-4 w-4" />
                <span>Anterior</span>
            </Link>

            <!-- Números de página -->
            <div class="flex flex-wrap gap-1">
                <template v-for="(link, key) in pageLinks" :key="key">
                    <div v-if="link.url === null || isEllipsis(link)">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-card px-2 py-1.5 text-sm text-muted-foreground">
                            <MoreHorizontal v-if="isEllipsis(link)" class="h-4 w-4" />
                            <span v-else>{{ cleanLabel(link.label) }}</span>
                        </span>
                    </div>
                    <div v-else>
                        <Link
                            :href="link.url"
                            :class="[
                                'inline-flex h-8 w-8 items-center justify-center rounded-md border text-sm transition-colors',
                                isCurrent(link)
                                    ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                                    : 'border-border bg-card text-card-foreground hover:bg-muted'
                            ]"
                            @click="(e) => handlePageChange(link.url, e)"
                        >
                            {{ cleanLabel(link.label) }}
                        </Link>
                    </div>
                </template>
            </div>

            <!-- Botón Siguiente -->
            <Link
                v-if="nextLink"
                :href="nextLink.url"
                class="inline-flex items-center rounded-md border border-border bg-card px-3 py-1.5 text-sm font-medium text-card-foreground transition-colors hover:bg-muted"
                @click="(e) => handlePageChange(nextLink.url, e)"
            >
                <span>Siguiente</span>
                <ChevronRight class="ml-1 h-4 w-4" />
            </Link>
        </div>
    </div>
</template>
