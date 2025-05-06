<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, User, Lock, ArrowRight, QrCode, ScanLine, X } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';

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
const showQrScanner = ref(false);
const scanError = ref('');
const cameraPermissionDenied = ref(false);
const cameraLoading = ref(false);

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

const scanQrCode = async () => {
    showQrScanner.value = true;
    scanError.value = '';
    cameraPermissionDenied.value = false;
    cameraLoading.value = true;

    // Verificar si el navegador soporta la API de MediaDevices
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        scanError.value = 'Tu navegador no soporta el acceso a la cámara.';
        cameraLoading.value = false;
        return;
    }

    try {
        // Intenta obtener permiso de cámara explícitamente
        await navigator.mediaDevices.getUserMedia({ video: true });
        cameraLoading.value = false;
    } catch (error) {
        console.error('Error de permiso de cámara:', error);
        cameraPermissionDenied.value = true;
        cameraLoading.value = false;

        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            scanError.value = 'Permiso de cámara denegado. Por favor, permite el acceso a la cámara en la configuración de tu navegador.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            scanError.value = 'No se encontró ninguna cámara en tu dispositivo.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            scanError.value = 'No se pudo acceder a la cámara. Posiblemente esté siendo usada por otra aplicación.';
        } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
            scanError.value = 'No hay cámaras que cumplan con los requisitos solicitados.';
        } else {
            scanError.value = `Error al acceder a la cámara: ${error.message || 'Error desconocido'}`;
        }
    }
};

const onDecode = (result) => {
    // Al escanear el código QR, mostrar un breve mensaje de éxito
    alert(`Código QR escaneado con éxito. Redirigiendo...`);
    showQrScanner.value = false;

    // Redirigir al chatbot, pasando el resultado del escaneo como parámetro
    window.location.href = route('chatbot.show', { qr_data: result });
};

const onScanError = (error) => {
    cameraLoading.value = false;
    console.error('Error de escaneo:', error);

    if (error && error.name === 'NotAllowedError') {
        cameraPermissionDenied.value = true;
        scanError.value = 'Permiso de cámara denegado. Por favor, permite el acceso a la cámara.';
    } else if (error) {
        scanError.value = 'Error al escanear. Por favor, intenta de nuevo.';
    } else {
        scanError.value = '';
    }
};

const closeScanner = () => {
    showQrScanner.value = false;
};
</script>

<template>
    <AuthBase >
        <Head title="Iniciar sesión" />

        <!-- Fondo con elementos decorativos para sistema de eventos -->
        <div class="fixed inset-0 -z-10 bg-celebration-gradient opacity-20 dark:opacity-30"></div>

        <!-- Elementos decorativos flotantes simulando confeti/elementos de celebración -->
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <!-- Formas decorativas para eventos -->
            <div class="absolute -top-10 left-1/4 h-24 w-24 rotate-12 rounded-full border border-purple/20 bg-purple/5 blur-sm dark:border-purple/30 dark:bg-purple/10 animate-float-slow"></div>
            <div class="absolute top-1/4 -right-10 h-32 w-32 -rotate-12 rounded-full border border-navy/20 bg-navy/5 blur-sm dark:border-navy/30 dark:bg-navy/10 animate-float-slower"></div>
            <div class="absolute bottom-1/3 left-1/6 h-16 w-16 rotate-45 rounded-md border border-forest/20 bg-forest/5 blur-sm dark:border-forest/30 dark:bg-forest/10 animate-float"></div>

            <!-- Símbolos de celebración (anillos, flores, globos) -->
            <div class="absolute top-1/3 right-1/4 h-20 w-20 opacity-20 dark:opacity-30 animate-float-slow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-purple">
                    <circle cx="8" cy="8" r="7"></circle>
                    <circle cx="16" cy="16" r="7"></circle>
                </svg>
            </div>
            <div class="absolute bottom-1/4 left-1/3 h-16 w-16 opacity-20 dark:opacity-30 animate-float-slower">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-navy">
                    <path d="M12 2L7 6.5m5-4.5l5 4.5m-5-4.5v16m0-16c0 5.523-4.477 10-10 10m10-10c0 5.523 4.477 10 10 10"></path>
                </svg>
            </div>
            <div class="absolute top-2/3 right-1/3 h-16 w-16 opacity-20 dark:opacity-30 animate-float">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-forest">
                    <circle cx="12" cy="8" r="7"></circle>
                    <path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.11"></path>
                </svg>
            </div>
        </div>

        <!-- Círculos de luz para efecto visual -->
        <div class="fixed top-20 right-1/4 -z-10 h-96 w-96 rounded-full bg-purple opacity-10 blur-3xl dark:opacity-15"></div>
        <div class="fixed bottom-10 left-1/4 -z-10 h-80 w-80 rounded-full bg-navy opacity-10 blur-3xl dark:opacity-15"></div>
        <div class="fixed top-1/3 left-1/3 -z-10 h-64 w-64 rounded-full bg-forest opacity-10 blur-3xl dark:opacity-15"></div>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 animate-fade-in">
            {{ status }}
        </div>

        <!-- Título con animación sutil para destacar el propósito de la aplicación -->
        <div class="mb-2 text-center animate-fade-in-up">
            <div class="mb-2 inline-block rounded-xl bg-white/30 p-3 backdrop-blur-sm dark:bg-neutral-dark/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-purple dark:text-purple-light">
                    <path d="M8 19h8a4 4 0 0 0 4-4 7 7 0 0 0-7-7h-1a5 5 0 0 0-5 5v3a4 4 0 0 0 4 4Z"></path>
                    <path d="M6 12h4"></path>
                    <path d="M2 9h3"></path>
                    <path d="M2 15h3"></path>
                    <path d="m18 12 4 3-4 3V9l4 3-4 3"></path>
                </svg>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-neutral-dark dark:text-neutral-light">Crea Momentos Inolvidables</h1>
            <p class="mt-1 text-sm text-neutral-medium dark:text-neutral-light/70">Gestiona todos tus eventos especiales en un solo lugar</p>
        </div>

        <!-- Contenedor principal con glassmorphism más grande -->
        <div class="relative mx-auto mb-4 w-full max-w-lg overflow-hidden rounded-3xl bg-white bg-opacity-50 backdrop-blur-sm transition-all duration-500 ease-out dark:bg-neutral-dark dark:bg-opacity-30 border border-neutral-light/30 dark:border-neutral-dark/50"
             :class="[hasLoaded ? 'translate-y-0 opacity-100 shadow-2xl' : 'translate-y-4 opacity-0 shadow-none']">

            <!-- Elementos decorativos de fondo más grandes -->
            <div class="absolute -left-24 -top-24 h-60 w-60 rounded-full bg-navy opacity-20 blur-3xl dark:opacity-30"></div>
            <div class="absolute -bottom-24 -right-24 h-60 w-60 rounded-full bg-forest opacity-20 blur-3xl dark:opacity-30"></div>

            <!-- Selector de modo de acceso -->
            <div class="relative z-10 mx-auto flex w-full max-w-sm justify-center gap-2 pt-6">
                <button
                    @click="showGuestMode = false"
                    type="button"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-300 cursor-pointer"
                    :class="!showGuestMode ? 'bg-purple text-white dark:bg-purple' : 'bg-neutral-light/70 text-neutral-dark dark:bg-neutral-dark/70 dark:text-neutral-light/70'"
                >
                    <Lock class="h-4 w-4" />
                    Acceso administrador
                </button>
                <button
                    @click="showGuestMode = true"
                    type="button"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-300 cursor-pointer"
                    :class="showGuestMode ? 'bg-navy text-white dark:bg-navy' : 'bg-neutral-light/70 text-neutral-dark dark:bg-neutral-dark/70 dark:text-neutral-light/70'"
                >
                    <QrCode class="h-4 w-4" />
                    Modo invitado
                </button>
            </div>

            <!-- Formulario de administrador -->
            <form v-if="!showGuestMode" @submit.prevent="submit" class="relative z-10 flex flex-col gap-6 p-6 px-8 md:p-10">
                <!-- Título interior del formulario para más presencia -->
                <div class="text-center">
                    <h2 class="text-2xl font-semibold text-purple dark:text-purple-light">Acceso al Sistema</h2>
                    <p class="mt-1 text-neutral-medium dark:text-neutral-light/80">Gestión de eventos de celebración</p>
                </div>

                <!-- Campo DNI con nuevo componente especializado y contador -->
                <div class="group transform transition-all duration-300 hover:scale-102">
                    <div class="neomorphic-input relative">
                        <Label for="dni" class="mb-2 block text-base font-medium text-neutral-dark opacity-90 dark:text-white dark:opacity-90">DNI</Label>
                        <div class="relative mb-1 w-full">
                            <User class="absolute left-3 sm:left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-purple opacity-70 dark:text-purple-light/70 z-10" />
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
                                class="w-full rounded-xl border-0 bg-neutral-light/60 py-3 pl-10 pr-16 text-base text-neutral-dark shadow-sm ring-1 ring-inset ring-purple/20 transition-all duration-300 placeholder:text-neutral-medium/50 placeholder:text-base placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-inset focus:ring-purple/40 dark:bg-neutral-dark/60 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/80 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-purple/50 dark:placeholder:text-neutral-light/30 sm:pl-12 sm:pr-16 font-sans h-[46px]"
                                @keydown="onlyNumeric"
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 select-none rounded-full bg-purple/10 px-2 py-0.5 text-xs font-medium text-purple dark:bg-purple/20 dark:text-purple-light">
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
                            <Lock class="absolute left-3 sm:left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-purple opacity-70 dark:text-purple-light/70 z-10" />
                            <Input
                                id="password"
                                :type="passwordInputType"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="Ingresa tu contraseña"
                                class="w-full rounded-xl border-0 bg-neutral-light/60 py-3 pl-10 pr-10 text-base text-neutral-dark shadow-sm ring-1 ring-inset ring-purple/20 transition-all duration-300 placeholder:text-neutral-medium/50 placeholder:text-base placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-inset focus:ring-purple/40 dark:bg-neutral-dark/60 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/80 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-purple/50 dark:placeholder:text-neutral-light/30 sm:pl-12 sm:pr-12 font-sans h-[46px]"
                            />
                            <!-- Botón mostrar/ocultar contraseña -->
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 text-neutral-medium hover:text-purple dark:text-neutral-light/70 dark:hover:text-purple-light z-10 cursor-pointer"
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
                    <Label for="remember" class="flex cursor-pointer items-center space-x-1">
                        <Checkbox
                            id="remember"
                            v-model="form.remember"
                            :tabindex="3"
                            class="h-5 w-5 rounded border-neutral-medium/30 text-purple transition-colors focus:ring-purple/20 dark:border-neutral-light/20 dark:text-purple-light"
                        />
                        <span class="select-none text-sm text-neutral-medium dark:text-neutral-light/70">Recordarme</span>
                    </Label>
                </div>

                <!-- Botón de inicio de sesión con glassmorphism y microinteracción -->
                <div class="pt-4">
                    <Button
                        type="submit"
                        class="group relative flex w-full items-center justify-center gap-3 rounded-xl bg-premium-gradient px-5 py-4 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple/50 focus:ring-offset-2 disabled:opacity-70 overflow-hidden"
                        :tabindex="4"
                        :disabled="form.processing || isLoading"
                    >
                        <span class="relative z-10 transition-transform duration-300 group-hover:translate-x-1">Iniciar sesión</span>
                        <ArrowRight v-if="!form.processing && !isLoading" class="relative z-10 h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" />
                        <LoaderCircle v-else class="relative z-10 h-5 w-5 animate-spin" />
                        <div class="absolute inset-0 -z-0 bg-black opacity-0 transition-opacity duration-300 group-hover:opacity-10"></div>
                    </Button>
                </div>
            </form>

            <!-- Modo Invitado - Escaneo QR -->
            <div v-else class="relative z-10 flex flex-col gap-6 p-6 px-8 md:p-10">
                <div class="text-center">
                    <h2 class="text-2xl font-semibold text-navy dark:text-navy-light">Acceso como Invitado</h2>
                    <p class="mt-1 text-neutral-medium dark:text-neutral-light/80">Escanea el código QR de tu invitación</p>
                </div>

                <!-- Area de escaneo QR -->
                <div class="group mx-auto flex flex-col items-center justify-center rounded-xl bg-neutral-light/60 p-6 shadow-sm transition-all duration-300 dark:bg-neutral-dark/60 border border-navy/10 dark:border-navy/20">
                    <div class="mb-4 rounded-lg bg-white p-2 dark:bg-neutral-dark border border-navy/10 dark:border-navy/20">
                        <QrCode class="h-24 w-24 text-navy dark:text-navy-light" />
                    </div>
                    <p class="mb-4 text-center text-sm text-neutral-medium dark:text-neutral-light/80">
                        Escanea el código QR que recibiste en tu invitación para acceder directamente al evento.
                    </p>
                    <Button
                        @click="scanQrCode"
                        type="button"
                        class="cursor-pointer flex items-center justify-center gap-3 rounded-xl bg-navy px-5 py-3 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-navy/50 focus:ring-offset-2 disabled:opacity-70"
                    >
                        <ScanLine class="h-5 w-5" />
                        <span>Escanear código QR</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Iconos de los tipos de eventos en fila -->
        <div class="mx-auto mb-1 flex max-w-md flex-wrap items-center justify-center gap-4 animate-fade-in-up">
            <div class="flex flex-col items-center justify-center">
                <div class="rounded-full bg-purple/10 p-2 dark:bg-purple/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-purple dark:text-purple-light">
                        <path d="M17 3a2 2 0 1 1 4 0c0 .6-.4 1.4-.8 2.1-.4.7-.9 1.4-1.2 2.1-.2.4-.3.8-.3 1.1-.2.2-.3.2-.7.6-1 1-2.5 1-3.5.1-.3-.3-.3-.4-.5-.7 0-.3-.1-.7-.3-1.1-.3-.7-.8-1.4-1.2-2.1-.4-.7-.8-1.5-.8-2.1a2 2 0 0 1 4 0Z"></path>
                        <path d="M17 20a2 2 0 1 0 4 0c0-.6-.4-1.4-.8-2.1-.4-.7-.9-1.4-1.2-2.1-.2-.4-.3-.8-.3-1.1-.2-.2-.3-.2-.7-.6-1-1-2.5-1-3.5-.1-.3.3-.3.4-.5.7 0 .3-.1.7-.3 1.1-.3.7-.8 1.4-1.2 2.1-.4.7-.8 1.5-.8 2.1a2 2 0 0 0 4 0Z"></path>
                        <path d="M3 8a2 2 0 1 0 4 0c0-.6-.4-1.4-.8-2.1-.4-.7-.9-1.4-1.2-2.1-.2-.4-.3-.8-.3-1.1-.2-.2-.3-.2-.7-.6-1-1-2.5-1-3.5-.1-.3.3-.3.4-.5.7 0 .3-.1.7-.3 1.1-.3.7-.8 1.4-1.2 2.1-.4.7-.8 1.5-.8 2.1a2 2 0 0 0 4 0Z"></path>
                        <path d="M3 16a2 2 0 1 0 4 0c0-.6-.4-1.4-.8-2.1-.4-.7-.9-1.4-1.2-2.1-.2-.4-.3-.8-.3-1.1-.2-.2-.3-.2-.7-.6-1-1-2.5-1-3.5-.1-.3.3-.3.4-.5.7 0 .3-.1.7-.3 1.1-.3.7-.8 1.4-1.2 2.1-.4.7-.8 1.5-.8 2.1a2 2 0 0 0 4 0Z"></path>
                    </svg>
                </div>
                <span class="mt-1 text-xs text-neutral-medium dark:text-neutral-light/70">Bodas</span>
            </div>
            <div class="flex flex-col items-center justify-center">
                <div class="rounded-full bg-navy/10 p-2 dark:bg-navy/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-navy dark:text-navy-light">
                        <path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"></path>
                        <path d="M9 18h6"></path>
                        <path d="M10 22h4"></path>
                        <path d="m9 2 1 3h4l1-3"></path>
                        <path d="m17 6-2 2"></path>
                        <path d="m7 6 2 2"></path>
                    </svg>
                </div>
                <span class="mt-1 text-xs text-neutral-medium dark:text-neutral-light/70">XV Años</span>
            </div>
            <div class="flex flex-col items-center justify-center">
                <div class="rounded-full bg-forest/10 p-2 dark:bg-forest/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-forest dark:text-forest-light">
                        <path d="M4 17v5"></path>
                        <path d="M20 17v5"></path>
                        <path d="M12 17v2"></path>
                        <path d="M12 11v1.5"></path>
                        <path d="M3.5 11H4c3.5 0 7-1.5 7-6 0 0-1 0-2 1-4 1-8.5.5-8.5-3C.5 .5 3 2 3 2M20.5 11H20c-3.5 0-7-1.5-7-6 0 0 1 0 2 1 4 1 8.5.5 8.5-3 0-3-2.5-1.5-2.5-1.5"></path>
                    </svg>
                </div>
                <span class="mt-1 text-xs text-neutral-medium dark:text-neutral-light/70">Cumpleaños</span>
            </div>
            <div class="flex flex-col items-center justify-center">
                <div class="rounded-full bg-charcoal/10 p-2 dark:bg-charcoal/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-charcoal dark:text-charcoal-light">
                        <rect width="16" height="16" x="4" y="4"></rect>
                        <path d="M8 4v16"></path>
                        <path d="M16 4v16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 8h4"></path>
                        <path d="M4 16h4"></path>
                        <path d="M16 16h4"></path>
                        <path d="M16 8h4"></path>
                    </svg>
                </div>
                <span class="mt-1 text-xs text-neutral-medium dark:text-neutral-light/70">Corporativos</span>
            </div>
        </div>

        <!-- Modal para escaneo QR -->
        <div v-if="showQrScanner" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Fondo oscuro -->
            <div class="cursor-pointer absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeScanner"></div>

            <!-- Modal content -->
            <div class="relative z-10 w-full max-w-md rounded-2xl bg-neutral-dark p-6 shadow-2xl text-white">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Escanear código QR</h3>
                    <button
                        @click="closeScanner"
                        class="rounded-full p-1 text-neutral-light hover:bg-neutral-dark/50"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <!-- Estado de carga -->
                <div v-if="cameraLoading" class="flex flex-col items-center justify-center py-16">
                    <div class="mb-4 h-12 w-12 animate-spin rounded-full border-4 border-neutral-light border-t-purple"></div>
                    <p class="text-center text-neutral-light">Accediendo a la cámara...</p>
                </div>

                <!-- Error de permisos -->
                <div v-else-if="cameraPermissionDenied" class="flex flex-col items-center justify-center gap-4 py-8">
                    <div class="rounded-full bg-red-500/20 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-center text-red-400">{{ scanError }}</p>
                    <div class="mt-2 flex flex-col gap-2">
                        <p class="text-center text-sm text-neutral-light">Soluciones posibles:</p>
                        <ul class="list-disc pl-6 text-sm text-neutral-light">
                            <li>Verifica los permisos de cámara en la configuración de tu navegador</li>
                            <li>Intenta usar un navegador diferente (Chrome recomendado)</li>
                            <li>En iOS, usa Safari para un mejor soporte de cámara</li>
                        </ul>
                    </div>
                    <Button
                        @click="scanQrCode"
                        type="button"
                        class="mt-4 cursor-pointer flex items-center justify-center gap-2 rounded-xl bg-purple px-4 py-2 text-sm font-medium text-white"
                    >
                        <span>Reintentar</span>
                    </Button>
                </div>

                <!-- Componente de escaneo -->
                <div v-else class="overflow-hidden rounded-xl">
                    <QrcodeStream
                        @decode="onDecode"
                        @error="onScanError"
                        class="h-64 w-full"
                        :torch="false"
                        :camera="'auto'"
                    />
                </div>

                <p v-if="scanError && !cameraPermissionDenied" class="mt-3 text-center text-sm text-red-400">{{ scanError }}</p>

                <p v-if="!cameraPermissionDenied && !cameraLoading" class="mt-4 text-center text-sm text-neutral-light">
                    Coloca el código QR frente a la cámara para escanearlo automáticamente
                </p>

                <!-- Botón para cambio de cámara (solo visible en móviles con múltiples cámaras) -->
<!--                 <div v-if="!cameraPermissionDenied && !cameraLoading" class="mt-4 flex justify-center">
                    <Button
                        type="button"
                        class="cursor-pointer flex items-center justify-center gap-2 rounded-xl bg-navy/50 px-4 py-2 text-sm font-medium text-white hover:bg-navy/70"
                        @click="() => document.querySelector('.qrcode-stream-camera-select')?.click()"
                    >
                        <span>Cambiar cámara</span>
                    </Button>
                </div> -->
            </div>
        </div>
    </AuthBase>
</template>

<style scoped>
/* Animaciones para los elementos flotantes */
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}

@keyframes float-slow {
  0% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(5deg); }
  100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes float-slower {
  0% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(-5deg); }
  100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

.animate-float-slow {
  animation: float-slow 6s ease-in-out infinite;
}

.animate-float-slower {
  animation: float-slower 8s ease-in-out infinite;
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

/* Animación para el modal */
@keyframes modal-appear {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}

.modal-appear {
  animation: modal-appear 0.2s ease-out forwards;
}

/* Personalizar el selector de cámara */
::v-deep .qrcode-stream-camera-select {
  position: absolute;
  width: 1px;
  height: 1px;
  margin: -1px;
  padding: 0;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>


