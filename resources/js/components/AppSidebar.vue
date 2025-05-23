<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, CalendarCheck2, Clock, Calendar, Users, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

// Obtener el usuario y sus roles del contexto de la página
const user = computed(() => usePage().props.auth.user);

// Definir todos los elementos del menú
const allNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        roles: ['admin', 'security'] // Accesible para ambos roles
    },
    {
        title: 'Tipos de Anfitriones',
        href: '/host-types',
        icon: Users,
        roles: ['admin']
    },
    {
        title: 'Tipos de Tiempo',
        href: '/time-types',
        icon: Clock,
        roles: ['admin']
    },
    {
        title: 'Tipos de Planos',
        href: '/plan-types',
        icon: LayoutGrid,
        roles: ['admin']
    },
    {
        title: 'Tipos de Eventos',
        href: '/event-types',
        icon: Calendar,
        roles: ['admin']
    },
    {
        title: 'Eventos',
        href: '/events',
        icon: CalendarCheck2,
        roles: ['admin']
    },
    {
        title: 'Personal de Seguridad',
        href: '/security',
        icon: Users,
        roles: ['admin']
    },
    {
        title: 'Registro de Acceso QR',
        href: '/guest-accesses',
        icon: CheckCircle,
        roles: ['admin', 'security'] // Accesible para ambos roles
    },
    {
        title: 'Registro de Acceso Manual',
        href: '/manual-guest-accesses',
        icon: CheckCircle,
        roles: ['admin', 'security'] // Accesible para ambos roles
    },
    {
        title: 'Reportes de Acceso',
        href: '/reports/access',
        icon: BookOpen,
        roles: ['admin'] // Solo accesible para admin
    },
];

// Filtrar los elementos del menú según el rol del usuario
const mainNavItems = computed(() => {
    const userRoles = user.value?.roles?.map(role => role.name) || [];
    return allNavItems.filter(item => {
        return item.roles.some(role => userRoles.includes(role));
    });
});
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
