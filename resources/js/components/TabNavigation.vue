<script setup lang="ts">
interface Tab {
    id: string;
    label: string;
}

interface Props {
    activeTab: string;
    tabs: Tab[];
}

defineProps<Props>();

const emit = defineEmits(['change']);

const handleTabChange = (tabId: string) => {
    emit('change', tabId);
};
</script>

<template>
    <div class="border-b border-border">
        <nav class="flex space-x-4 overflow-x-auto">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="handleTabChange(tab.id)"
                class="px-3 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer"
                :class="activeTab === tab.id ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'"
                :id="tab.id"
            >
                {{ tab.label }}
            </button>
        </nav>
    </div>
</template>
