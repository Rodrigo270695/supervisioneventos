<script setup lang="ts">
import { CalendarDays } from 'lucide-vue-next';
import { defineProps } from 'vue';

interface Props {
    total: number;
    upcoming: number;
    inProgress: number;
    completed: number;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-card hover:bg-card/90 backdrop-blur-sm dark:bg-card/90 flex items-center gap-4 rounded-lg p-4 shadow-sm transition-all hover:shadow-md">
            <div class="bg-primary/10 dark:bg-primary/20 flex h-12 w-12 items-center justify-center rounded-full">
                <CalendarDays class="h-6 w-6 text-primary" />
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Total Eventos</p>
                <p class="text-2xl font-semibold">{{ total }}</p>
            </div>
        </div>
        <div class="bg-card hover:bg-card/90 backdrop-blur-sm dark:bg-card/90 flex items-center gap-4 rounded-lg p-4 shadow-sm transition-all hover:shadow-md">
            <div class="bg-blue-100 dark:bg-blue-900/30 flex h-12 w-12 items-center justify-center rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 dark:text-blue-400"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="m7 10 4 4 6-6"></path></svg>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Próximos</p>
                <p class="text-2xl font-semibold">{{ upcoming }}</p>
            </div>
        </div>
        <div class="bg-card hover:bg-card/90 backdrop-blur-sm dark:bg-card/90 flex items-center gap-4 rounded-lg p-4 shadow-sm transition-all hover:shadow-md">
            <div class="bg-amber-100 dark:bg-amber-900/30 flex h-12 w-12 items-center justify-center rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500 dark:text-amber-400"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">En Progreso</p>
                <p class="text-2xl font-semibold">{{ inProgress }}</p>
            </div>
        </div>
        <div class="bg-card hover:bg-card/90 backdrop-blur-sm dark:bg-card/90 flex items-center gap-4 rounded-lg p-4 shadow-sm transition-all hover:shadow-md">
            <div class="bg-green-100 dark:bg-green-900/30 flex h-12 w-12 items-center justify-center rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 dark:text-green-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Completados</p>
                <p class="text-2xl font-semibold">{{ completed }}</p>
            </div>
        </div>
    </div>
</template>
