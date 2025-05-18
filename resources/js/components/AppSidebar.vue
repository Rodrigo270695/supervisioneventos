<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, CalendarCheck2, Clock, Calendar, Users, CheckCircle } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Tipos de Anfitriones',
        href: '/host-types',
        icon: Users,
    },
    {
        title: 'Tipos de Tiempo',
        href: '/time-types',
        icon: Clock,
    },
    {
        title: 'Tipos de Planos',
        href: '/plan-types',
        icon: LayoutGrid,
    },
    {
        title: 'Tipos de Eventos',
        href: '/event-types',
        icon: Calendar,
    },
    {
        title: 'Eventos',
        href: '/events',
        icon: CalendarCheck2,
    },
    {
        title: 'Personal de Seguridad',
        href: '/security',
        icon: Users,
    },
    {
        title: 'Control de Acceso',
        href: '/guest-access',
        icon: CheckCircle,
    },
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
