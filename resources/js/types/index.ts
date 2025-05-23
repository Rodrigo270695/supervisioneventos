import { LucideIcon } from 'lucide-vue-next';

export interface NavItem {
    title: string;
    href: string;
    icon: LucideIcon;
    roles: string[]; // Lista de roles que pueden acceder a este elemento
}

export interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
}

export interface Role {
    id: number;
    name: string;
}
