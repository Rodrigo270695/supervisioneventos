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

class ChatbotController extends Controller
{
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
                         ->with(['event'])
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

            // Formatear la fecha y hora del evento
            $eventDate = Carbon::parse($event->event_date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
            $startTime = Carbon::parse($event->start_time)->format('H:i');
            $endTime = $event->end_time ? Carbon::parse($event->end_time)->format('H:i') : null;

            // Información detallada del evento
            $eventInfo = [
                'name' => $event->name,
                'date' => $eventDate,
                'time' => $endTime ? "$startTime - $endTime" : "A partir de las $startTime",
                'location' => $event->address,
                'table' => "Mesa {$guest->table_number}",
                'description' => $event->description,
                'status' => $event->status->label(),
                'passes' => [
                    'total' => $guest->passes,
                    'used' => $guest->used_passes,
                    'available' => $guest->passes - $guest->used_passes
                ]
            ];

            // Mensaje de bienvenida personalizado
            $welcomeMessage = "¡Hola {$guest->first_name}! Bienvenido/a a {$event->name}. " .
                            "Tu mesa asignada es la {$guest->table_number}. " .
                            "Tienes {$eventInfo['passes']['available']} pase(s) disponible(s).";

            // Registrar el acceso para análisis
            Log::info('Acceso al chatbot', [
                'guest_id' => $guest->id,
                'event_id' => $event->id,
                'qr_code' => $request->qr_data,
                'event_status' => $event->status->value,
                'timestamp' => now()
            ]);

        return Inertia::render('Chatbot', [
            'welcomeMessage' => $welcomeMessage,
            'eventInfo' => $eventInfo,
                'guest' => [
                    'name' => $guest->first_name . ' ' . $guest->last_name,
                    'dni' => $guest->dni,
                    'tableNumber' => $guest->table_number,
                    'passes' => $eventInfo['passes']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar código QR: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Error al procesar el código QR. Por favor, intenta de nuevo.',
                'showErrorModal' => true
            ]);
        }
    }
}
