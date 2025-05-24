<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Event;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    protected $rasaUrl;

    public function __construct()
    {
        // Asegurarnos de que la URL de RASA sea correcta y use localhost
        $this->rasaUrl = rtrim(env('RASA_API_URL', 'http://127.0.0.1:5005'), '/');

        Log::info('ChatbotController inicializado', [
            'rasa_url' => $this->rasaUrl
        ]);
    }

    /**
     * Muestra la vista del chatbot para interactuar con el invitado
     * después de escanear un código QR.
     */
    public function show(Request $request)
    {
        // Validar los datos del QR
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors([
                'error' => 'Código QR inválido o no proporcionado.',
                'showErrorModal' => true
            ]);
        }

        try {
            // Buscar el invitado por su código QR
            $guest = Guest::where('qr_code', $request->qr_data)
                         ->with(['event.eventType'])
                         ->firstOrFail();

            // Obtener el evento asociado
            $event = $guest->event;

            // Si el evento no está programado o en progreso, mostrar error
            if (!in_array($event->status, [EventStatus::SCHEDULED, EventStatus::IN_PROGRESS])) {
                $mensaje = match($event->status) {
                    EventStatus::COMPLETED => 'El evento ya ha finalizado.',
                    EventStatus::CANCELLED => 'El evento ha sido cancelado.',
                    default => 'El evento no está disponible en este momento.'
                };

                return back()->withErrors([
                    'error' => $mensaje,
                    'showErrorModal' => true,
                    'eventStatus' => $event->status->value,
                    'eventName' => $event->name
                ]);
            }

            // Formatear fechas y horas
            $eventDate = Carbon::parse($event->event_date)->locale('es');
            $startTime = Carbon::parse($event->start_time);
            $endTime = $event->end_time ? Carbon::parse($event->end_time) : null;

            // Información detallada del evento
            $eventInfo = [
                'name' => $event->name,
                'type' => $event->eventType->name,
                'date' => [
                    'formatted' => $eventDate->isoFormat('dddd, D [de] MMMM [de] YYYY'),
                    'day' => $eventDate->day,
                    'month' => $eventDate->monthName,
                    'year' => $eventDate->year,
                    'dayName' => $eventDate->dayName,
                    'daysUntil' => $eventDate->diffInDays(now(), false)
                ],
                'time' => [
                    'start' => $startTime->format('H:i'),
                    'end' => $endTime ? $endTime->format('H:i') : null,
                    'formatted' => $endTime ? "{$startTime->format('H:i')} - {$endTime->format('H:i')}" : "A partir de las {$startTime->format('H:i')}",
                    'duration' => $endTime ? $startTime->diffInHours($endTime) : null
                ],
                'location' => [
                    'address' => $event->address,
                    'coordinates' => $event->coordinates ?? null,
                    'mapUrl' => $event->map_url ?? null
                ],
                'capacity' => [
                    'total' => $event->capacity,
                    'current' => $event->guests()->count()
                ],
                'table' => [
                    'number' => $guest->table_number,
                    'formatted' => "Mesa {$guest->table_number}"
                ],
                'description' => $event->description,
                'status' => [
                    'code' => $event->status->value,
                    'label' => $event->status->label()
                ]
            ];

            // Información del invitado
            $guestInfo = [
                'id' => $guest->id,
                'name' => [
                    'first' => $guest->first_name,
                    'last' => $guest->last_name,
                    'full' => "{$guest->first_name} {$guest->last_name}"
                ],
                'dni' => $guest->dni,
                'tableNumber' => $guest->table_number,
                'passes' => [
                    'total' => $guest->passes,
                    'used' => $guest->used_passes,
                    'available' => $guest->passes - $guest->used_passes
                ],
                'lastAccess' => $guest->last_access ? Carbon::parse($guest->last_access)->format('Y-m-d H:i:s') : null
            ];

            // Mensaje de bienvenida personalizado
            $welcomeMessage = "¡Hola {$guestInfo['name']['first']}! Bienvenido/a a {$eventInfo['name']}. " .
                            "Tu mesa asignada es la {$guestInfo['tableNumber']}. " .
                            "Tienes {$guestInfo['passes']['available']} pase(s) disponible(s).";

            // Registrar el acceso
            Log::info('Acceso al chatbot', [
                'guest_id' => $guest->id,
                'event_id' => $event->id,
                'qr_code' => $request->qr_data,
                'timestamp' => now()
            ]);

            // Actualizar último acceso
            $guest->update(['last_access' => now()]);

            return Inertia::render('Chatbot', [
                'welcomeMessage' => $welcomeMessage,
                'eventInfo' => $eventInfo,
                'guest' => $guestInfo,
                'event_id' => $event->id,
                'chatbotResponses' => $this->getChatbotResponses($eventInfo)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar código QR: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Error al procesar el código QR. Por favor, intenta de nuevo.',
                'showErrorModal' => true
            ]);
        }
    }

    /**
     * Obtiene las respuestas predefinidas del chatbot basadas en la información del evento
     */
    private function getChatbotResponses($eventInfo)
    {
        return [
            'horario' => "El evento {$eventInfo['name']} está programado para el {$eventInfo['date']['formatted']} y {$eventInfo['time']['formatted']}. " .
                        "Te recomendamos llegar 15 minutos antes para el registro.",

            'ubicacion' => "El evento se realizará en {$eventInfo['location']['address']}. " .
                          ($eventInfo['location']['mapUrl'] ? "Puedes ver la ubicación en el mapa proporcionado." : ""),

            'fecha' => "El evento será el {$eventInfo['date']['formatted']}. " .
                      ($eventInfo['date']['daysUntil'] > 0 ? "Faltan {$eventInfo['date']['daysUntil']} días para el evento." : "¡El evento es hoy!"),

            'capacidad' => "El evento tiene una capacidad total de {$eventInfo['capacity']['total']} personas. " .
                          "Actualmente hay {$eventInfo['capacity']['current']} invitados confirmados.",

            'duracion' => $eventInfo['time']['duration'] ?
                         "El evento tiene una duración estimada de {$eventInfo['time']['duration']} horas." :
                         "El evento comienza a las {$eventInfo['time']['start']}.",

            'estado' => "El evento se encuentra {$eventInfo['status']['label']}.",

            'tipo_evento' => "Este es un evento tipo {$eventInfo['type']}.",

            'ayuda' => "Puedes preguntarme sobre:\n" .
                      "- Horario del evento\n" .
                      "- Ubicación y cómo llegar\n" .
                      "- Fecha y tiempo restante\n" .
                      "- Capacidad y asistentes\n" .
                      "- Duración del evento\n" .
                      "- Estado actual\n" .
                      "- Tipo de evento"
        ];
    }

    /**
     * Procesa los mensajes enviados al chatbot a través de RASA
     */
    public function processMessage(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'guest_id' => 'required',
                'message' => 'required|string',
                'table_number' => 'sometimes|integer'
            ]);

            // Obtener información del invitado y el evento con sus relaciones
            $guest = Guest::with([
                'event',
                'event.hosts' => function($query) {
                    $query->with('hostType');
                },
                'event.eventType',
                'event.plans' => function($query) {
                    $query->with('planType');
                },
                'event.times' => function($query) {
                    $query->with('timeType')
                          ->orderBy('start_time', 'asc');
                }
            ])->findOrFail($request->guest_id);

            $event = $guest->event;

            // Preparar los metadatos del evento
            $eventMetadata = [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_type' => $event->eventType->name,
                'event_date' => $event->event_date,
                'event_time' => $event->start_time,
                'event_location' => $event->address,
                'table_number' => $guest->table_number,
                'hosts' => $event->hosts->map(function($host) {
                    return [
                        'id' => $host->id,
                        'name' => $host->nombres . ' ' . $host->apellidos,
                        'type' => $host->host_type_id,
                        'type_name' => $host->hostType->name,
                        'edad' => $host->edad,
                        'correo' => $host->correo
                    ];
                })->toArray(),
                'plans' => $event->plans->map(function($plan) {
                    return [
                        'id' => $plan->id,
                        'type_name' => $plan->planType->name,
                        'description' => $plan->description,
                        'image_url' => $plan->image
                    ];
                })->toArray(),
                'times' => $event->times->map(function($time) {
                    return [
                        'id' => $time->id,
                        'type_name' => $time->timeType->name,
                        'start_time' => $time->start_time,
                        'end_time' => $time->end_time,
                        'description' => $time->description
                    ];
                })->toArray(),
                'guest_info' => [
                    'id' => $guest->id,
                    'name' => $guest->first_name . ' ' . $guest->last_name,
                    'table' => $guest->table_number,
                    'passes' => [
                        'total' => $guest->passes,
                        'used' => $guest->used_passes,
                        'available' => $guest->passes - $guest->used_passes
                    ],
                    'last_access' => $guest->last_access ? $guest->last_access->format('Y-m-d H:i:s') : null
                ]
            ];

            Log::info('Enviando mensaje a RASA', [
                'guest_id' => $request->guest_id,
                'message' => $request->message,
                'metadata' => $eventMetadata,
                'rasa_url' => $this->rasaUrl
            ]);

            // Preparar los datos para RASA con toda la metadata
            $rasaData = [
                'sender' => (string)$request->guest_id,
                'message' => $request->message,
                'metadata' => $eventMetadata
            ];

            // Intentar conectar con RASA con un timeout más corto
            try {
                $response = Http::timeout(15)
                    ->retry(3, 100)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])
                    ->post("{$this->rasaUrl}/webhooks/rest/webhook", $rasaData);

                Log::info('Respuesta de RASA', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'headers' => $response->headers()
                ]);

                if ($response->successful()) {
                    $rasaResponse = $response->json();

                    // Si RASA no devuelve respuesta, intentar manejar localmente
                    if (empty($rasaResponse)) {
                        // Intentar manejar preguntas comunes
                        $answer = $this->handleCommonQuestions($request->message, $eventMetadata);
                        if ($answer) {
                            return response()->json([['text' => $answer]]);
                        }

                        return response()->json([
                            ['text' => 'Lo siento, no pude encontrar la información que buscas. ¿Podrías reformular tu pregunta?']
                        ]);
                    }

                    return response()->json($rasaResponse);
                }

                Log::error('Error en respuesta de RASA', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'headers' => $response->headers()
                ]);

                return response()->json([
                    ['text' => 'Lo siento, hubo un problema al procesar tu mensaje. ¿Podrías intentar con otra pregunta?']
                ], 500);

            } catch (\Exception $e) {
                Log::error('Error en la comunicación con RASA', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Respuesta más amigable para el usuario
                return response()->json([
                    ['text' => 'Parece que el servicio está un poco lento. ¿Podrías intentar tu pregunta de nuevo en unos momentos?']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error general en processMessage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                ['text' => 'Lo siento, ocurrió un error inesperado. Por favor, intenta de nuevo con otra pregunta.']
            ], 500);
        }
    }

    /**
     * Maneja preguntas comunes cuando Rasa no puede responder
     */
    private function handleCommonQuestions($message, $metadata)
    {
        $message = strtolower($message);

        // Preguntas sobre anfitriones
        if (str_contains($message, 'novio') || str_contains($message, 'novia') ||
            str_contains($message, 'anfitrión') || str_contains($message, 'anfitriona') ||
            str_contains($message, 'padrino') || str_contains($message, 'madrina')) {
            $hosts = collect($metadata['hosts']);
            $hostsByType = $hosts->groupBy('type_name');

            $response = "Los anfitriones del evento son:\n";
            foreach ($hostsByType as $type => $typeHosts) {
                $names = $typeHosts->pluck('name')->join(', ');
                $response .= "- {$type}: {$names}\n";
            }
            return $response;
        }

        // Preguntas sobre horarios
        if (str_contains($message, 'horario') || str_contains($message, 'programa') ||
            str_contains($message, 'actividad')) {
            $times = collect($metadata['times']);
            $response = "El programa del evento es el siguiente:\n";
            foreach ($times as $time) {
                $timeStr = $time['start_time'];
                if ($time['end_time']) {
                    $timeStr .= " - " . $time['end_time'];
                }
                $response .= "- {$time['type_name']}: {$timeStr}";
                if ($time['description']) {
                    $response .= " ({$time['description']})";
                }
                $response .= "\n";
            }
            return $response;
        }

        // Preguntas sobre planos
        if (str_contains($message, 'plano') || str_contains($message, 'mapa') ||
            str_contains($message, 'distribución')) {
            $plans = collect($metadata['plans']);
            if ($plans->isEmpty()) {
                return "Lo siento, no hay planos disponibles para este evento.";
            }
            $response = "Los planos disponibles son:\n";
            foreach ($plans as $plan) {
                $response .= "- {$plan['type_name']}";
                if ($plan['description']) {
                    $response .= ": {$plan['description']}";
                }
                $response .= "\n";
            }
            return $response;
        }

        // Preguntas sobre la mesa
        if (str_contains($message, 'mesa') || str_contains($message, 'asiento') ||
            str_contains($message, 'ubicación en el salón')) {
            return "Tu mesa asignada es la número {$metadata['guest_info']['table']}.";
        }

        // Preguntas sobre el evento
        if (str_contains($message, 'evento') || str_contains($message, 'celebración') ||
            str_contains($message, 'fiesta') || str_contains($message, 'ceremonia')) {
            return "Este es un evento de tipo {$metadata['event_type']} llamado \"{$metadata['event_name']}\".";
        }

        // Preguntas sobre la fecha/hora
        if (str_contains($message, 'hora') || str_contains($message, 'cuándo') ||
            str_contains($message, 'fecha') || str_contains($message, 'día')) {
            $date = \Carbon\Carbon::parse($metadata['event_date'])->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
            return "El evento es el {$date} a las {$metadata['event_time']}.";
        }

        // Preguntas sobre la ubicación
        if (str_contains($message, 'dónde') || str_contains($message, 'ubicación') ||
            str_contains($message, 'lugar') || str_contains($message, 'dirección') ||
            str_contains($message, 'local')) {
            return "El evento se realizará en: {$metadata['event_location']}.";
        }

        // Preguntas sobre pases disponibles
        if (str_contains($message, 'pase') || str_contains($message, 'pases') ||
            str_contains($message, 'invitación') || str_contains($message, 'invitaciones') ||
            str_contains($message, 'acompañante') || str_contains($message, 'acompañantes')) {
            $available = $metadata['guest_info']['passes']['available'];
            $total = $metadata['guest_info']['passes']['total'];
            $used = $metadata['guest_info']['passes']['used'];

            $response = "Tienes {$available} pase(s) disponible(s) de un total de {$total}.\n";
            if ($used > 0) {
                $response .= "Has utilizado {$used} pase(s) hasta ahora.";
            }
            return $response;
        }

        return null;
    }
}
