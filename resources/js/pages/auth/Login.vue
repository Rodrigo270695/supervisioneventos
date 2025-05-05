<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, User, Lock, ArrowRight, QrCode, ScanLine } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    dni: '',
    password: '',
    remember: false,
});

const isLoading = ref(false);
const showPassword = ref(false);
const passwordInputType = ref('password');
const showGuestMode = ref(false);

// Validación para input DNI - solo permitir números
const onlyNumeric = (event: KeyboardEvent) => {
  // Permitir: backspace, delete, tab, escape, enter y teclas de navegación
  if ([46, 8, 9, 27, 13, 110].indexOf(event.keyCode) !== -1 ||
    // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
    (event.keyCode === 65 && event.ctrlKey === true) ||
    (event.keyCode === 67 && event.ctrlKey === true) ||
    (event.keyCode === 86 && event.ctrlKey === true) ||
    (event.keyCode === 88 && event.ctrlKey === true) ||
    // Permitir: home, end, left, right
    (event.keyCode >= 35 && event.keyCode <= 39)) {
    return
  }

  // Bloquear entrada si no es un número
  if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) &&
      (event.keyCode < 96 || event.keyCode > 105)) {
    event.preventDefault()
  }
};

// Efecto de animación al cargar
const hasLoaded = ref(false);
onMounted(() => {
    setTimeout(() => {
        hasLoaded.value = true;
    }, 100);
});

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
    passwordInputType.value = showPassword.value ? 'text' : 'password';
};

const toggleMode = () => {
    showGuestMode.value = !showGuestMode.value;
};

const submit = () => {
    isLoading.value = true;
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            isLoading.value = false;
        },
    });
};

const scanQrCode = () => {
    // En una implementación real, esto conectaría con el API de RASA
    // Por ahora, solo mostramos que está en desarrollo
    alert('Escaneo de código QR en desarrollo. Esta funcionalidad conectará con el chatbot RASA.');
};
</script>

<template>
    <AuthBase title="Bienvenido a Supervisión de Eventos" description="Introduce tus credenciales para gestionar tus eventos">
        <Head title="Iniciar sesión" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 animate-fade-in">
            {{ status }}
        </div>

        <!-- Contenedor principal con glassmorphism más grande -->
        <div class="relative mx-auto mb-10 w-full max-w-lg overflow-hidden rounded-3xl bg-white bg-opacity-50 backdrop-blur-sm transition-all duration-500 ease-out dark:bg-neutral-dark dark:bg-opacity-30"
             :class="[hasLoaded ? 'translate-y-0 opacity-100 shadow-2xl' : 'translate-y-4 opacity-0 shadow-none']">

            <!-- Elementos decorativos de fondo más grandes -->
            <div class="absolute -left-24 -top-24 h-60 w-60 rounded-full bg-navy opacity-10 blur-3xl dark:opacity-20"></div>
            <div class="absolute -bottom-24 -right-24 h-60 w-60 rounded-full bg-forest opacity-10 blur-3xl dark:opacity-20"></div>

            <!-- Selector de modo de acceso -->
            <div class="relative z-10 mx-auto flex w-full max-w-sm justify-center gap-2 pt-8">
                <button
                    @click="showGuestMode = false"
                    type="button"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-300"
                    :class="!showGuestMode ? 'bg-navy text-white dark:bg-navy-light' : 'bg-neutral-light/70 text-neutral-dark dark:bg-neutral-dark/70 dark:text-neutral-light/70'"
                >
                    <Lock class="h-4 w-4" />
                    Acceso administrador
                </button>
                <button
                    @click="showGuestMode = true"
                    type="button"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-300"
                    :class="showGuestMode ? 'bg-navy text-white dark:bg-navy-light' : 'bg-neutral-light/70 text-neutral-dark dark:bg-neutral-dark/70 dark:text-neutral-light/70'"
                >
                    <QrCode class="h-4 w-4" />
                    Modo invitado
                </button>
            </div>

            <!-- Formulario de administrador -->
            <form v-if="!showGuestMode" @submit.prevent="submit" class="relative z-10 flex flex-col gap-8 p-6 px-8 md:p-14">
                <!-- Título interior del formulario para más presencia -->
                <div class="text-center">
                    <h2 class="text-2xl font-semibold text-neutral-dark dark:text-white">Acceso al Sistema</h2>
                    <p class="mt-2 text-neutral-medium dark:text-neutral-light/80">Gestión de eventos de celebración</p>
                </div>

                <!-- Campo DNI con nuevo componente especializado y contador -->
                <div class="group transform transition-all duration-300 hover:scale-102">
                    <div class="neomorphic-input relative">
                        <Label for="dni" class="mb-2 block text-base font-medium text-neutral-dark opacity-90 dark:text-white dark:opacity-90">DNI</Label>
                        <div class="relative mb-1 w-full">
                            <User class="absolute left-3 sm:left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-neutral-medium opacity-70 dark:text-neutral-light/70 z-10" />
                            <Input
                                id="dni"
                                type="text"
                                required
                                :tabindex="1"
                                autocomplete="username"
                                v-model="form.dni"
                                placeholder="Ingresa tu DNI (8 dígitos)"
                                maxlength="8"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autofocus
                                class="w-full rounded-xl border-0 bg-neutral-light/50 py-3 pl-10 pr-16 text-base text-neutral-dark shadow-sm ring-1 ring-inset ring-neutral-medium/10 transition-all duration-300 placeholder:text-neutral-medium/50 placeholder:text-base placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-inset focus:ring-navy/30 dark:bg-neutral-dark/50 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/80 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-navy/50 dark:placeholder:text-neutral-light/30 sm:pl-12 sm:pr-16 font-sans h-[46px]"
                                @keydown="onlyNumeric"
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 select-none rounded-full bg-neutral-light px-2 py-0.5 text-xs font-medium text-neutral-dark/70 dark:bg-neutral-dark/70 dark:text-neutral-light/70">
                                {{ form.dni.length }}/8
                            </div>
                        </div>
                        <InputError :message="form.errors.dni" class="mt-1 text-sm" />
                    </div>
                </div>

                <!-- Campo Contraseña con neomorfismo -->
                <div class="group transform transition-all duration-300 hover:scale-102">
                    <div class="neomorphic-input relative">
                        <Label for="password" class="mb-2 block text-base font-medium text-neutral-dark opacity-90 dark:text-white dark:opacity-90">Contraseña</Label>
                        <div class="relative mb-1 w-full">
                            <Lock class="absolute left-3 sm:left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-neutral-medium opacity-70 dark:text-neutral-light/70 z-10" />
                            <Input
                                id="password"
                                :type="passwordInputType"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="Ingresa tu contraseña"
                                class="w-full rounded-xl border-0 bg-neutral-light/50 py-3 pl-10 pr-10 text-base text-neutral-dark shadow-sm ring-1 ring-inset ring-neutral-medium/10 transition-all duration-300 placeholder:text-neutral-medium/50 placeholder:text-base placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-inset focus:ring-navy/30 dark:bg-neutral-dark/50 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/80 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-navy/50 dark:placeholder:text-neutral-light/30 sm:pl-12 sm:pr-12 font-sans h-[46px]"
                            />
                            <!-- Botón mostrar/ocultar contraseña -->
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 text-neutral-medium hover:text-navy dark:text-neutral-light/70 dark:hover:text-navy-light z-10 cursor-pointer"
                            >
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></svg>
                            </button>
                        </div>
                        <InputError :message="form.errors.password" class="mt-1 text-sm" />
                    </div>
                </div>

                <!-- Opción Recordarme con diseño moderno -->
                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex cursor-pointer items-center space-x-3">
                        <Checkbox
                            id="remember"
                            v-model="form.remember"
                            :tabindex="3"
                            class="h-5 w-5 rounded border-neutral-medium/30 text-navy transition-colors focus:ring-navy/20 dark:border-neutral-light/20 dark:text-navy-light"
                        />
                        <span class="select-none text-sm text-neutral-medium dark:text-neutral-light/70">Recordarme</span>
                    </Label>
                </div>

                <!-- Botón de inicio de sesión con glassmorphism y microinteracción -->
                <div class="pt-4">
                    <Button
                        type="submit"
                        class="group flex w-full items-center justify-center gap-3 rounded-xl bg-navy px-5 py-4 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:bg-opacity-90 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-navy/50 focus:ring-offset-2 disabled:opacity-70 dark:bg-navy-light dark:hover:bg-navy-light/90 dark:focus:ring-navy-light/50"
                        :tabindex="4"
                        :disabled="form.processing || isLoading"
                    >
                        <span class="transition-transform duration-300 group-hover:translate-x-1">Iniciar sesión</span>
                        <ArrowRight v-if="!form.processing && !isLoading" class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" />
                        <LoaderCircle v-else class="h-5 w-5 animate-spin" />
                    </Button>
                </div>
            </form>

            <!-- Modo Invitado - Escaneo QR -->
            <div v-else class="relative z-10 flex flex-col gap-8 p-6 px-8 md:p-14">
                <div class="text-center">
                    <h2 class="text-2xl font-semibold text-neutral-dark dark:text-white">Acceso como Invitado</h2>
                    <p class="mt-2 text-neutral-medium dark:text-neutral-light/80">Escanea el código QR de tu invitación</p>
                </div>

                <!-- Area de escaneo QR -->
                <div class="group mx-auto flex flex-col items-center justify-center rounded-xl bg-neutral-light/50 p-6 shadow-sm transition-all duration-300 dark:bg-neutral-dark/50">
                    <div class="mb-4 rounded-lg bg-white p-2 dark:bg-neutral-dark">
                        <QrCode class="h-24 w-24 text-navy dark:text-navy-light" />
                    </div>
                    <p class="mb-4 text-center text-sm text-neutral-medium dark:text-neutral-light/80">
                        Escanea el código QR que recibiste en tu invitación para acceder directamente al evento.
                    </p>
                    <Button
                        @click="scanQrCode"
                        type="button"
                        class="group flex items-center justify-center gap-3 rounded-xl bg-navy px-5 py-3 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:bg-opacity-90 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-navy/50 focus:ring-offset-2 disabled:opacity-70 dark:bg-navy-light dark:hover:bg-navy-light/90 dark:focus:ring-navy-light/50"
                    >
                        <ScanLine class="h-5 w-5" />
                        <span>Escanear código QR</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Nota de seguridad como tendencia de transparencia -->
        <div class="mx-auto mt-8 max-w-lg text-center text-xs text-neutral-medium/60 transition-opacity duration-500 dark:text-neutral-light/50" :class="hasLoaded ? 'opacity-100' : 'opacity-0'">
            Acceso seguro con encriptación de extremo a extremo para garantizar la protección de tus datos.
        </div>
    </AuthBase>
</template>


