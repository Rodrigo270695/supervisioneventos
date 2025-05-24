<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Send, MessageSquare, ChevronRight, Calendar, Clock, MapPin, User, ArrowLeft, ThumbsUp, ThumbsDown, Menu, X, Users, CreditCard, LogOut } from 'lucide-vue-next';
import axios from 'axios';

// Props que recibimos del controlador
const props = defineProps<{
    welcomeMessage: string;
    eventInfo: {
        name: string;
        type: string;
        date: {
            formatted: string;
            day: number;
            month: string;
            year: number;
            dayName: string;
            daysUntil: number;
        };
        time: {
            start: string;
            end: string | null;
            formatted: string;
            duration: number | null;
        };
        location: {
            address: string;
            coordinates: string | null;
            mapUrl: string | null;
        };
        capacity: {
            total: number;
            current: number;
        };
        table: {
            number: number;
            formatted: string;
        };
        description?: string;
        status: {
            code: string;
            label: string;
        };
    };
    guest: {
        id: number;
        name: {
            first: string;
            last: string;
            full: string;
        };
        dni: string;
        tableNumber: number;
        passes: {
            total: number;
            used: number;
            available: number;
        };
        lastAccess: string | null;
    };
    chatbotResponses: Record<string, string>;
}>();

// Estado del chatbot
const messages = ref([]);
const userInput = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const isTyping = ref(false);
const showSidebar = ref(false);

// Estado para el menú de salir
const showLogoutMenu = ref(false);

// Posibles respuestas predefinidas para simular el chatbot
const botResponses = {
    'horario': 'El evento comienza a las 18:30 y termina aproximadamente a las 23:00. Te recomendamos llegar 15 minutos antes para el registro de invitados.',
    'ubicación': 'El evento se llevará a cabo en Salón de Eventos "El Paraíso". Puedes encontrarlo en Av. Principal #123, Ciudad. Hemos habilitado un servicio de valet parking gratuito.',
    'estacionamiento': 'Contamos con estacionamiento gratuito para invitados. Solo menciona tu nombre en la entrada y nuestro personal te asistirá.',
    'codigo de vestimenta': 'El código de vestimenta es formal. Se recomienda traje para caballeros y vestido de noche para damas. Evita colores blanco y rojo que son exclusivos para los anfitriones.',
    'menu': 'El menú incluye una entrada de ensalada fresca, plato principal a elegir entre filete de res, pollo en salsa de champiñones o lasaña vegetariana. De postre, tendremos pastel de la casa y café. Habrá barra libre durante todo el evento.',
    'alergias': 'Si tienes alguna alergia alimentaria, por favor infórmanos de inmediato para coordinar con el chef. Tenemos opciones para personas con alergias a frutos secos, lácteos y gluten.',
    'fotos': 'Habrá un fotógrafo profesional capturando todos los momentos especiales. Además, hemos creado un hashtag para el evento: #EventoEspecial2023. ¡Comparte tus fotos y ayúdanos a crear recuerdos!',
    'wifi': 'La red WiFi es "Evento_Especial" y la contraseña es "celebracion2023". Tendrás acceso de alta velocidad durante todo el evento.',
    'música': 'Tenemos DJ y banda en vivo. Si tienes alguna canción especial que te gustaría escuchar, háznoslo saber con anticipación y trataremos de incluirla en la playlist.',
    'regalos': 'Los novios han habilitado una mesa de regalos en Liverpool con el código #A12345. También puedes contribuir con sobres en la recepción del evento si lo prefieres.',
    'transporte': 'Hemos coordinado un servicio de transporte desde el hotel sede hasta el salón del evento. Los horarios son: Ida - 17:30, 18:00. Regreso - 23:30, 00:30.',
    'niños': 'Tenemos una sala especial para niños con actividades supervisadas por personal capacitado. Incluye juegos, películas y refrigerios especiales para los pequeños invitados.',
    'ayuda': 'Puedes preguntarme sobre horarios, ubicación, estacionamiento, código de vestimenta, menú, alergias, fotos, WiFi, música, regalos, transporte, niños y más.',
};

// Opciones rápidas que mostraremos como botones
const quickOptions = [
    { text: 'Horario', query: '¿cuál es el horario?', icon: Clock },
    { text: 'Ubicación', query: '¿dónde es el evento?', icon: MapPin },
    { text: 'Mi Mesa', query: '¿cuál es mi mesa?', icon: User },
    { text: 'Pases', query: '¿cuántos pases tengo?', icon: Users },
    { text: 'Fecha', query: '¿cuándo es el evento?', icon: Calendar },
    { text: 'Capacidad', query: '¿cuál es la capacidad?', icon: User },
    { text: 'Estado', query: '¿cuál es el estado del evento?', icon: User },
    { text: 'Ayuda', query: '¿en qué me puedes ayudar?', icon: User },
];

// Estado para seguimiento de mensajes útiles
const messageRatings = ref({});

// Función para calificar un mensaje
const rateMessage = (messageId: number, rating: string) => {
    messageRatings.value[messageId] = rating;
};

// Mensaje del día con consejos útiles
const tipMessages = [
    "¡No olvides confirmar tu asistencia al menos 3 días antes del evento!",
    "Puedes compartir fotos del evento con el hashtag oficial",
    "Hay servicio de transporte disponible desde y hacia el hotel principal",
    "¿Consultas urgentes? Comunícate con el organizador al teléfono indicado en tu invitación"
];

const randomTip = computed(() => {
    const randomIndex = Math.floor(Math.random() * tipMessages.length);
    return tipMessages[randomIndex];
});

// Mensaje de bienvenida por defecto
const defaultWelcomeMessage = `¡Bienvenido al asistente virtual del evento! Estoy aquí para ayudarte con cualquier información que necesites. Tu mesa asignada es la ${props.eventInfo.table.number}.`;

// Añadir el mensaje de bienvenida al cargar
onMounted(() => {
    // Añadimos mensaje de bienvenida del sistema
    addMessage('bot', props.welcomeMessage || defaultWelcomeMessage);

    // Después de un momento, mostramos el mensaje con opciones
    setTimeout(() => {
        addMessage('bot', '¿En qué puedo ayudarte? Aquí tienes algunas opciones frecuentes, o puedes escribir tu pregunta.');
    }, 1000);

    // Añadimos un consejo útil después de un momento
    setTimeout(() => {
        addMessage('tip', randomTip.value);
    }, 3000);

    // Agregar y remover el event listener cuando el menú está abierto
    document.addEventListener('click', closeLogoutMenu);
});

// Función para añadir un nuevo mensaje al chat
const addMessage = (sender: 'user' | 'bot' | 'tip', text: string) => {
    const messageId = Date.now();
    messages.value.push({
        id: messageId,
        sender,
        text,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    });

    // Scroll al fondo del chat cuando se añade un mensaje
    setTimeout(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    }, 100);

    return messageId;
};

// Función para enviar un mensaje con reintentos
const sendMessage = async (retryCount = 0) => {
    if (!userInput.value.trim()) return;

    const userMessage = userInput.value;
    const messageId = addMessage('user', userMessage);
    userInput.value = '';

    // Simular que el bot está escribiendo
    isTyping.value = true;

    try {
        // Enviar mensaje a RASA a través del controlador
        const response = await axios.post(route('chatbot.message'), {
            guest_id: props.guest.id,
            message: userMessage,
            table_number: props.eventInfo.table.number
        });

        isTyping.value = false;

        if (response.data && response.data.length > 0) {
            response.data.forEach(msg => {
                const botMessageId = addMessage('bot', msg.text);

                // Si hay imágenes, botones u otros elementos, mostrarlos
                if (msg.image) {
                    addMessage('bot', `[Imagen: ${msg.image}]`);
                }
                if (msg.buttons) {
                    msg.buttons.forEach(button => {
                        addMessage('bot', `[Botón: ${button.title}]`);
                    });
                }
            });
        } else {
            addMessage('bot', 'Lo siento, no pude procesar tu mensaje. ¿Podrías intentar de otra manera?');
        }
    } catch (error) {
        console.error('Error al procesar mensaje:', error);
        isTyping.value = false;

        // Si es un error de timeout y no hemos excedido los reintentos
        if (error.code === 'ECONNABORTED' && retryCount < 2) {
            addMessage('bot', 'El servicio está un poco lento, intentando de nuevo...');
            // Esperar 1 segundo antes de reintentar
            await new Promise(resolve => setTimeout(resolve, 1000));
            return sendMessage(retryCount + 1);
        }

        // Mostrar mensaje de error específico según el tipo de error
        let errorMessage = 'Lo siento, hubo un problema al procesar tu mensaje. ¿Podrías intentar de nuevo?';

        if (error.response) {
            // El servidor respondió con un código de error
            if (error.response.status === 500) {
                errorMessage = error.response.data?.[0]?.text || 'El servicio está experimentando problemas. Por favor, intenta más tarde.';
            } else if (error.response.status === 404) {
                errorMessage = 'No se pudo conectar con el servicio de chat. Por favor, verifica tu conexión.';
            }
        } else if (error.request) {
            // La solicitud fue hecha pero no se recibió respuesta
            errorMessage = 'No se pudo establecer conexión con el servicio. Por favor, verifica tu conexión a internet.';
        }

        addMessage('bot', errorMessage);
    }
};

// Función para manejar respuestas rápidas al hacer clic en botones
const handleQuickOption = async (query: string, retryCount = 0) => {
    addMessage('user', query);
    isTyping.value = true;

    try {
        const response = await axios.post(route('chatbot.message'), {
            guest_id: props.guest.id,
            message: query,
            table_number: props.eventInfo.table.number
        });

        isTyping.value = false;

        if (response.data && response.data.length > 0) {
            response.data.forEach(msg => {
                addMessage('bot', msg.text);
            });
        } else {
            addMessage('bot', 'Lo siento, no pude procesar tu consulta. ¿Podrías intentar de otra manera?');
        }
    } catch (error) {
        console.error('Error al procesar consulta rápida:', error);
        isTyping.value = false;

        // Si es un error de timeout y no hemos excedido los reintentos
        if (error.code === 'ECONNABORTED' && retryCount < 2) {
            addMessage('bot', 'El servicio está un poco lento, intentando de nuevo...');
            // Esperar 1 segundo antes de reintentar
            await new Promise(resolve => setTimeout(resolve, 1000));
            return handleQuickOption(query, retryCount + 1);
        }

        let errorMessage = error.response?.data?.[0]?.text || 'Lo siento, hubo un problema al procesar tu consulta. ¿Podrías intentar de nuevo?';

        if (error.response?.status === 500) {
            errorMessage = 'El servicio está experimentando problemas. Por favor, intenta más tarde.';
        }

        addMessage('bot', errorMessage);
    }
};

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

// Función para manejar el cierre de sesión
const handleLogout = () => {
    window.location.href = route('login');
};

// Función para alternar el menú de salir
const toggleLogoutMenu = () => {
    showLogoutMenu.value = !showLogoutMenu.value;
};

// Cerrar el menú si se hace clic fuera
const closeLogoutMenu = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.logout-menu') && !target.closest('.logout-button')) {
        showLogoutMenu.value = false;
    }
};

// Función para formatear la hora de 24h a 12h
const formatTime = (time24h: string) => {
    const [hours, minutes] = time24h.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
};

// Función para formatear el rango de horas
const formatTimeRange = computed(() => {
    const startFormatted = formatTime(props.eventInfo.time.start);
    let endFormatted = '';

    if (props.eventInfo.time.end) {
        endFormatted = formatTime(props.eventInfo.time.end);
        return `${startFormatted} - ${endFormatted}`;
    }

    // Si solo hay hora de inicio
    return startFormatted;
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-navy/5 via-purple/2 to-forest/5 dark:from-navy/10 dark:via-purple/5 dark:to-forest/10">
        <Head :title="'Asistente - ' + eventInfo.name" />

        <!-- Barra superior con menú hamburguesa en móvil -->
        <header class="bg-white/80 dark:bg-neutral-dark/80 backdrop-blur-md shadow-sm sticky top-0 z-20">
            <div class="mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <button
                        @click="toggleSidebar"
                        class="md:hidden mr-3 p-2 rounded-full hover:bg-neutral-light/50 dark:hover:bg-neutral-dark/50"
                    >
                        <Menu v-if="!showSidebar" class="h-5 w-5 text-neutral-dark dark:text-white" />
                        <X v-else class="h-5 w-5 text-neutral-dark dark:text-white" />
                    </button>
                    <div class="flex items-center">
                        <MessageSquare class="h-5 w-5 text-purple dark:text-purple-light mr-2" />
                        <h1 class="font-bold text-lg text-neutral-dark dark:text-white">{{ eventInfo.name }}</h1>
                    </div>
                </div>
                <!-- Perfil del usuario con menú desplegable -->
                <div class="relative">
                    <button
                        @click="toggleLogoutMenu"
                        class="logout-button flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-neutral-light/50 dark:hover:bg-neutral-dark/50 transition-colors cursor-pointer group"
                    >
                        <div class="h-7 w-7 rounded-full bg-navy/10 dark:bg-navy/20 flex items-center justify-center group-hover:bg-navy/20 dark:group-hover:bg-navy/30 transition-colors">
                            <User class="h-4 w-4 text-navy dark:text-navy-light" />
                        </div>
                        <span class="text-sm font-medium text-neutral-dark dark:text-white">
                            {{ guest.name.full }}
                        </span>
                        <ChevronRight
                            class="h-4 w-4 text-neutral-medium dark:text-neutral-light/50 transition-transform duration-200"
                            :class="{ 'rotate-90': showLogoutMenu }"
                        />
                    </button>

                    <!-- Menú desplegable -->
                    <div
                        v-if="showLogoutMenu"
                        class="logout-menu absolute right-0 top-full mt-2 w-56 rounded-lg bg-white dark:bg-neutral-dark/95 shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1 z-50"
                    >
                        <div class="px-4 py-3 border-b border-neutral-light/20 dark:border-neutral-dark/60">
                            <p class="text-sm font-medium text-neutral-dark dark:text-white">{{ guest.name.full }}</p>
                            <p class="text-xs text-neutral-medium dark:text-neutral-light/70 mt-1">DNI: {{ guest.dni }}</p>
                        </div>
                        <div class="p-2">
                            <button
                                @click="handleLogout"
                                class="cursor-pointer w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors flex items-center gap-2"
                            >
                                <LogOut class="h-4 w-4" />
                                Salir del chat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row h-[calc(100vh-56px)]">
            <!-- Sidebar con información del evento (fijo en desktop, deslizable en móvil) -->
            <aside
                class="w-full md:w-80 lg:w-96 bg-white/80 dark:bg-neutral-dark/80 backdrop-blur-md md:shadow-sm overflow-y-auto transition-all duration-300 z-10"
                :class="[showSidebar ? 'fixed inset-0 pt-16' : 'hidden md:block']"
            >
                <div class="p-6">
                    <!-- Cabecera de la barra lateral -->
                    <div class="mb-6 text-center">
                        <div class="mb-2 inline-block rounded-xl bg-white/30 p-3 backdrop-blur-sm dark:bg-neutral-dark/30">
                            <Calendar class="h-8 w-8 text-navy dark:text-navy-light" />
                        </div>
                        <h2 class="text-xl font-bold text-neutral-dark dark:text-white">Información del evento</h2>
                    </div>

                    <!-- Tarjeta con información detallada -->
                    <div class="bg-white dark:bg-neutral-dark/50 rounded-xl p-5 shadow-sm border border-neutral-light/30 dark:border-neutral-dark/70 mb-6">
                        <h3 class="font-semibold text-lg text-neutral-dark dark:text-white mb-4">Detalles</h3>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple/10 dark:bg-purple/20 p-2 rounded-full">
                                    <Calendar class="h-5 w-5 text-purple dark:text-purple-light" />
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50">Fecha</p>
                                    <p class="text-sm font-medium text-neutral-dark dark:text-white">{{ eventInfo.date.formatted }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="bg-navy/10 dark:bg-navy/20 p-2 rounded-full">
                                    <Clock class="h-5 w-5 text-navy dark:text-navy-light" />
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50">Horario</p>
                                    <p class="text-sm font-medium text-neutral-dark dark:text-white">{{ formatTimeRange }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="bg-forest/10 dark:bg-forest/20 p-2 rounded-full">
                                    <MapPin class="h-5 w-5 text-forest dark:text-forest-light" />
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50">Ubicación</p>
                                    <p class="text-sm font-medium text-neutral-dark dark:text-white">{{ eventInfo.location.address }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="bg-purple/10 dark:bg-purple/20 p-2 rounded-full">
                                    <Users class="h-5 w-5 text-purple dark:text-purple-light" />
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50">Mesa asignada</p>
                                    <p class="text-sm font-medium text-neutral-dark dark:text-white">{{ eventInfo.table.formatted }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="bg-navy/10 dark:bg-navy/20 p-2 rounded-full">
                                    <CreditCard class="h-5 w-5 text-navy dark:text-navy-light" />
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50">Pases</p>
                                    <p class="text-sm font-medium text-neutral-dark dark:text-white">
                                        {{ guest.passes.available }} disponible(s) de {{ guest.passes.total }} total(es)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa de ubicación (simulado) -->
                    <div class="bg-white dark:bg-neutral-dark/50 rounded-xl overflow-hidden shadow-sm border border-neutral-light/30 dark:border-neutral-dark/70 mb-6">
                        <div class="h-40 bg-neutral-light/50 dark:bg-neutral-dark/80 flex items-center justify-center">
                            <span class="text-sm text-neutral-medium dark:text-neutral-light/70">
                                [Mapa de ubicación]
                            </span>
                        </div>
                        <div class="p-3">
                            <a href="#" class="text-sm text-navy dark:text-navy-light font-medium flex items-center gap-1">
                                <span>Ver en Google Maps</span>
                                <ChevronRight class="h-4 w-4" />
                            </a>
                        </div>
                    </div>

                    <!-- Contacto de emergencia -->
                    <div class="bg-white dark:bg-neutral-dark/50 rounded-xl p-4 shadow-sm border border-neutral-light/30 dark:border-neutral-dark/70">
                        <h3 class="font-medium text-sm text-neutral-dark dark:text-white mb-2">Contacto de emergencia</h3>
                        <p class="text-sm text-neutral-medium dark:text-neutral-light/70">
                            Si necesitas asistencia inmediata, contacta al organizador:
                        </p>
                        <p class="text-sm font-medium text-navy dark:text-navy-light mt-1">
                            +51 987654321
                        </p>
                    </div>

                    <!-- Botón para cerrar sidebar en móvil -->
                    <button
                        @click="toggleSidebar"
                        class="mt-6 w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-neutral-light/50 text-neutral-medium dark:bg-neutral-dark/50 dark:text-neutral-light/70 md:hidden"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>Volver al chat</span>
                    </button>
                </div>
            </aside>

            <!-- Contenedor principal del chat -->
            <main class="flex-1 flex flex-col max-h-[calc(100vh-56px)]">
                <!-- Mensajes del chat -->
                <div
                    ref="chatContainer"
                    class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-gradient-to-b from-transparent to-neutral-light/5 dark:to-navy/5"
                >
                    <!-- Indicador de fecha -->
                    <div class="text-center my-8">
                        <span class="inline-block px-3 py-1 text-xs font-medium text-neutral-medium dark:text-neutral-light/70 bg-white/80 dark:bg-neutral-dark/80 rounded-full shadow-sm border border-neutral-light/30 dark:border-neutral-dark/50">
                            {{ new Date().toLocaleDateString('es-ES', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'}) }}
                        </span>
                    </div>

                    <!-- Mensajes -->
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        :class="[
                            'max-w-[70%] md:max-w-[50%]',
                            message.sender === 'user' ? 'ml-auto' : '',
                            message.sender === 'tip' ? 'mx-auto max-w-lg' : '',
                            'animate-fade-in mb-5 group'
                        ]"
                    >
                        <!-- Mensaje de consejo -->
                        <div
                            v-if="message.sender === 'tip'"
                            class="bg-gradient-to-r from-navy/10 to-purple/10 dark:from-navy/20 dark:to-purple/20 rounded-xl p-3.5 shadow-sm border border-neutral-light/20 dark:border-neutral-dark/30 transform transition-all duration-300 hover:shadow-md backdrop-blur-sm group-hover:translate-y-[-2px]"
                        >
                            <div class="flex items-start gap-2.5">
                                <div class="bg-white/80 dark:bg-neutral-dark/50 p-2 rounded-full flex-shrink-0 pulse-subtle shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-navy dark:text-navy-light">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 8v4"></path>
                                        <path d="M12 16h.01"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center mb-1">
                                        <span class="text-xs font-medium bg-white/50 dark:bg-neutral-dark/50 text-navy dark:text-navy-light px-1.5 py-0.5 rounded-full">Consejo</span>
                                        <div class="ml-2 h-1 w-1 rounded-full bg-neutral-medium/30 dark:bg-neutral-light/30"></div>
                                        <span class="ml-2 text-xs text-neutral-medium/70 dark:text-neutral-light/50">{{ message.timestamp }}</span>
                                    </div>
                                    <p class="text-sm text-neutral-dark dark:text-white break-words whitespace-normal overflow-hidden">{{ message.text }}</p>
                                </div>
                            </div>
                            <div class="mt-2 text-right">
                                <button class="text-xs text-navy dark:text-navy-light opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:underline focus:outline-none">
                                    Ocultar
                                </button>
                            </div>
                        </div>

                        <!-- Mensaje del usuario -->
                        <div
                            v-else-if="message.sender === 'user'"
                            class="relative bg-gradient-to-br from-navy to-navy-dark dark:from-navy-light dark:to-navy rounded-xl rounded-tr-sm p-3.5 shadow-md ml-auto transform transition-all duration-300 group-hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="absolute -right-1 -top-1 w-4 h-4 bg-navy dark:bg-navy-light rotate-45 transform -z-10"></div>
                            <div class="flex justify-between items-start mb-1.5">
                                <span class="text-xs font-medium bg-white/10 text-white/90 px-1.5 py-0.5 rounded-full">Tú</span>
                                <span class="text-xs text-white/60">{{ message.timestamp }}</span>
                            </div>
                            <p class="text-sm text-white dark:text-white break-words whitespace-normal overflow-hidden">{{ message.text }}</p>
                            <div class="absolute -bottom-0.5 -right-0.5 w-7 h-7 rounded-full border-2 border-white/20 dark:border-navy-light/20 flex items-center justify-center bg-navy-dark dark:bg-navy opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <User class="h-3.5 w-3.5 text-white/80" />
                            </div>
                        </div>

                        <!-- Mensaje del bot -->
                        <div
                            v-else
                            class="relative bg-white dark:bg-neutral-dark/80 rounded-xl rounded-tl-sm p-3.5 shadow-sm border border-neutral-light/30 dark:border-neutral-dark/70 transform transition-all duration-300 group-hover:-translate-y-0.5 hover:shadow-md backdrop-filter backdrop-blur-sm"
                        >
                            <div class="absolute -left-1 -top-1 w-4 h-4 bg-white dark:bg-neutral-dark/80 rotate-45 transform -z-10 border-l border-t border-neutral-light/30 dark:border-neutral-dark/70"></div>
                            <div class="flex items-start gap-2 mb-2">
                                <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple to-navy dark:from-purple-light dark:to-navy-light flex items-center justify-center shadow-sm">
                                    <MessageSquare class="h-3.5 w-3.5 text-white" />
                                </div>
                                <div class="mt-0.5 flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-semibold bg-purple/10 dark:bg-purple/20 text-purple dark:text-purple-light px-1.5 py-0.5 rounded-full">Asistente</span>
                                        <span class="text-xs text-neutral-medium/60 dark:text-neutral-light/40">{{ message.timestamp }}</span>
                                    </div>
                                    <p class="text-sm text-neutral-dark dark:text-white whitespace-pre-line break-words overflow-hidden">{{ message.text }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-2.5 pt-2 border-t border-neutral-light/20 dark:border-neutral-dark/40">
                                <div class="flex items-center gap-1.5" v-if="message.sender === 'bot'">
                                    <p class="text-xs text-neutral-medium dark:text-neutral-light/50 mr-1">¿Te fue útil?</p>
                                    <button
                                        @click="rateMessage(message.id, 'helpful')"
                                        :class="[
                                            'p-1.5 rounded-full transition-colors duration-200',
                                            messageRatings[message.id] === 'helpful'
                                                ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 ring-1 ring-green-400/30'
                                                : 'text-neutral-medium/50 dark:text-neutral-light/30 hover:bg-neutral-light/50 dark:hover:bg-neutral-dark/50'
                                        ]"
                                        title="Útil"
                                    >
                                        <ThumbsUp class="h-3 w-3" />
                                    </button>
                                    <button
                                        @click="rateMessage(message.id, 'not-helpful')"
                                        :class="[
                                            'p-1.5 rounded-full transition-colors duration-200',
                                            messageRatings[message.id] === 'not-helpful'
                                                ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 ring-1 ring-red-400/30'
                                                : 'text-neutral-medium/50 dark:text-neutral-light/30 hover:bg-neutral-light/50 dark:hover:bg-neutral-dark/50'
                                        ]"
                                        title="No útil"
                                    >
                                        <ThumbsDown class="h-3 w-3" />
                                    </button>
                                </div>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-2">
                                    <button class="text-xs text-neutral-medium hover:text-neutral-dark dark:text-neutral-light/50 dark:hover:text-neutral-light focus:outline-none">
                                        Copiar
                                    </button>
                                    <div class="h-3 w-px bg-neutral-light dark:bg-neutral-dark/80"></div>
                                    <button class="text-xs text-neutral-medium hover:text-neutral-dark dark:text-neutral-light/50 dark:hover:text-neutral-light focus:outline-none">
                                        Compartir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicador de "escribiendo..." -->
                    <div v-if="isTyping" class="max-w-[82%] md:max-w-[70%] animate-fade-in mb-5">
                        <div class="bg-white dark:bg-neutral-dark/80 inline-block rounded-xl rounded-tl-sm p-3.5 shadow-sm border border-neutral-light/30 dark:border-neutral-dark/70 backdrop-filter backdrop-blur-sm">
                            <div class="flex items-start gap-2">
                                <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple to-navy dark:from-purple-light dark:to-navy-light flex items-center justify-center shadow-sm animate-pulse">
                                    <MessageSquare class="h-3.5 w-3.5 text-white" />
                                </div>
                                <div class="mt-0.5">
                                    <div class="flex items-center mb-1">
                                        <span class="text-xs font-semibold bg-purple/10 dark:bg-purple/20 text-purple dark:text-purple-light px-1.5 py-0.5 rounded-full">Asistente</span>
                                    </div>
                                    <div class="flex space-x-1.5 items-center">
                                        <div class="w-1.5 h-1.5 bg-purple/60 dark:bg-purple/70 rounded-full animate-pulse"></div>
                                        <div class="w-1.5 h-1.5 bg-purple/60 dark:bg-purple/70 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                                        <div class="w-1.5 h-1.5 bg-purple/60 dark:bg-purple/70 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opciones rápidas con iconos -->
                <div class="border-t border-neutral-light/30 dark:border-neutral-dark/70 p-3 bg-white/90 dark:bg-neutral-dark/90 backdrop-blur-md">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-medium text-neutral-medium dark:text-neutral-light/70 px-1">Consultas frecuentes:</p>
                        <button class="text-xs text-navy dark:text-navy-light hover:underline focus:outline-none">
                            Ver todas
                        </button>
                    </div>
                    <div class="overflow-x-auto scrollbar-thin pb-1">
                        <div class="flex gap-1.5 pb-1">
                            <button
                                v-for="option in quickOptions.slice(0, 6)"
                                :key="option.query"
                                @click="handleQuickOption(option.query)"
                                class="whitespace-nowrap rounded-full bg-white dark:bg-neutral-dark px-3 py-2 text-xs font-medium text-navy dark:text-navy-light border border-navy/20 dark:border-navy/40 shadow-sm hover:bg-navy hover:text-white dark:hover:bg-navy dark:hover:text-white transition-all duration-200 transform hover:-translate-y-0.5 flex items-center gap-1.5"
                            >
                                <div class="w-5 h-5 rounded-full bg-navy/10 dark:bg-navy/20 flex items-center justify-center">
                                    <component :is="option.icon" class="h-3 w-3 text-navy dark:text-navy-light" />
                                </div>
                                <span>{{ option.text }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Entrada de mensaje -->
                <div class="border-t border-neutral-light/30 dark:border-neutral-dark/70 p-3 pb-4 bg-white/95 dark:bg-neutral-dark/95 backdrop-blur-md sticky bottom-0">
                    <div class="relative">
                        <Input
                            v-model="userInput"
                            @keyup.enter="sendMessage"
                            placeholder="Escribe tu mensaje..."
                            class="w-full rounded-lg border-0 bg-white/90 dark:bg-neutral-dark/80 py-2.5 pl-3.5 pr-12 text-xs text-neutral-dark shadow-sm ring-1 ring-inset ring-purple/20 transition-all duration-300 placeholder:text-neutral-medium/50 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-navy/40 dark:text-white dark:ring-white/10 dark:focus:bg-neutral-dark/90 dark:focus:ring-2 dark:focus:ring-inset dark:focus:ring-navy/50 dark:placeholder:text-neutral-light/30 h-[42px]"
                        />
                        <Button
                            @click="sendMessage"
                            type="button"
                            class="absolute right-1 top-1 cursor-pointer flex items-center justify-center gap-2 rounded-md bg-gradient-to-r from-navy to-purple dark:from-navy-light dark:to-purple-light px-2.5 py-1.5 text-white shadow-md transition-all duration-300 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-navy/30 focus:ring-offset-1 disabled:opacity-70 h-[32px] min-w-[32px] transform hover:-translate-y-0.5"
                            :disabled="!userInput.trim()"
                        >
                            <Send class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="flex items-center gap-1.5">
                            <span class="flex h-1.5 w-1.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                            </span>
                            <p class="text-xs text-green-600 dark:text-green-400">En línea</p>
                        </div>
                        <p class="text-xs text-neutral-medium/70 dark:text-neutral-light/30">
                            Asistente virtual con IA para responder todas tus consultas sobre el evento
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Animaciones y estilos adicionales */
@keyframes pulse-subtle {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

.pulse-subtle {
  animation: pulse-subtle 2s ease-in-out infinite;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Animaciones para los mensajes */
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fade-in-up 0.3s ease-out forwards;
}
</style>
