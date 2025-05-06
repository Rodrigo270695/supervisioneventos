<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ChatbotController extends Controller
{
    /**
     * Muestra la vista del chatbot para interactuar con el invitado
     * después de escanear un código QR.
     */
    public function show(Request $request): Response
    {
        // Recuperamos los datos del código QR
        $qrData = $request->input('qr_data');

        // En un sistema real, aquí decodificaríamos los datos del QR
        // y consultaríamos la base de datos para obtener información
        // sobre el evento y el invitado

        // Por ejemplo:
        // $eventInfo = Event::where('qr_code', $qrData)->first();
        // $guestInfo = Guest::where('qr_code', $qrData)->first();

        // Mensaje de bienvenida personalizado
        $welcomeMessage = "¡Bienvenido a nuestro evento especial! Estamos encantados de que hayas podido acompañarnos en este día tan importante.";

        // Información del evento (datos de demostración)
        $eventInfo = [
            'name' => 'Boda de Ana y Carlos',
            'date' => 'Sábado, 25 de Noviembre, 2023',
            'time' => '18:30 - 23:00',
            'location' => 'Salón de Eventos "El Paraíso", Av. Principal #123',
            'table' => 'Mesa 4'
        ];

        if ($qrData) {
            // Log del código QR escaneado para propósitos de demostración
            Log::info('Código QR escaneado: ' . $qrData);

            // Personalizaríamos el mensaje según los datos del QR
            // Por ahora usamos un mensaje fijo
            $welcomeMessage = "¡Hola! Bienvenido a la " . $eventInfo['name'] . ". Tu mesa asignada es la " . $eventInfo['table'] . ".";
        }

        return Inertia::render('Chatbot', [
            'welcomeMessage' => $welcomeMessage,
            'eventInfo' => $eventInfo,
            'qrData' => $qrData
        ]);
    }
}
