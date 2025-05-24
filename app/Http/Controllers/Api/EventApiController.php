<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Host;
use App\Models\HostType;
use App\Models\Plan;
use App\Models\PlanType;
use App\Models\TimeType;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EventApiController extends Controller
{
    /**
     * Obtener información general del evento
     */
    public function getEventInfo(Request $request): JsonResponse
    {
        try {
            $event = Event::with('eventType')
                         ->when($request->has('event_id'), function($query) use ($request) {
                             return $query->where('id', $request->event_id);
                         })
                         ->firstOrFail();

            return response()->json([
                'name' => $event->name,
                'type' => $event->eventType->name,
                'capacity' => $event->capacity,
                'description' => $event->description,
                'status' => $event->status->value
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información del evento'], 500);
        }
    }

    /**
     * Obtener información de horarios del evento
     */
    public function getEventTimes(Request $request): JsonResponse
    {
        try {
            $event = Event::with('times.timeType')
                         ->when($request->has('event_id'), function($query) use ($request) {
                             return $query->where('id', $request->event_id);
                         })
                         ->firstOrFail();

            return response()->json([
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'detailed_times' => $event->times->map(function($time) {
                    return [
                        'type' => $time->timeType->name,
                        'start_time' => $time->start_time,
                        'end_time' => $time->end_time,
                        'description' => $time->description
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información de horarios'], 500);
        }
    }

    /**
     * Obtener información de ubicación del evento
     */
    public function getEventLocation(Request $request): JsonResponse
    {
        try {
            $event = Event::when($request->has('event_id'), function($query) use ($request) {
                             return $query->where('id', $request->event_id);
                         })
                         ->firstOrFail();

            return response()->json([
                'address' => $event->address,
                'reference' => $event->description // Usando description como referencia
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información de ubicación'], 500);
        }
    }

    /**
     * Obtener información de los anfitriones del evento
     */
    public function getEventHosts(Request $request): JsonResponse
    {
        try {
            $event = Event::when($request->has('event_id'), function($query) use ($request) {
                             return $query->where('id', $request->event_id);
                         })
                         ->firstOrFail();

            $hosts = Host::with('hostType')
                        ->where('event_id', $event->id)
                        ->get()
                        ->map(function($host) {
                            return [
                                'nombres' => $host->nombres,
                                'apellidos' => $host->apellidos,
                                'host_type' => $host->hostType->name,
                                'edad' => $host->edad,
                                'correo' => $host->correo
                            ];
                        });

            return response()->json($hosts);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información de los anfitriones'], 500);
        }
    }

    /**
     * Obtener información de los planos del evento
     */
    public function getEventPlans(Request $request): JsonResponse
    {
        try {
            $event = Event::when($request->has('event_id'), function($query) use ($request) {
                             return $query->where('id', $request->event_id);
                         })
                         ->firstOrFail();

            $plans = Plan::with('planType')
                        ->where('event_id', $event->id)
                        ->get()
                        ->map(function($plan) {
                            return [
                                'plan_type' => $plan->planType->name,
                                'description' => $plan->description,
                                'image_url' => asset('storage/' . $plan->image)
                            ];
                        });

            return response()->json($plans);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información de los planos'], 500);
        }
    }

    /**
     * Obtener información de un invitado específico
     */
    public function getGuestInfo(string $guestId): JsonResponse
    {
        try {
            $guest = Guest::findOrFail($guestId);

            return response()->json([
                'table_number' => $guest->table_number,
                'passes' => [
                    'total' => $guest->passes,
                    'used' => $guest->used_passes,
                    'available' => $guest->passes - $guest->used_passes
                ],
                'last_access' => $guest->last_access ? $guest->last_access->format('d/m/Y H:i:s') : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información del invitado'], 500);
        }
    }

    /**
     * Obtener todos los tipos disponibles
     */
    public function getEventTypes(): JsonResponse
    {
        try {
            $types = [
                'event_types' => EventType::select('name', 'description')->get(),
                'host_types' => HostType::select('name', 'description')->get(),
                'plan_types' => PlanType::select('name', 'description')->get(),
                'time_types' => TimeType::select('name', 'description')->get()
            ];

            return response()->json($types);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la información de los tipos'], 500);
        }
    }

    /**
     * Listar todos los eventos disponibles
     */
    public function listEvents(): JsonResponse
    {
        try {
            $events = Event::with('eventType')
                          ->orderBy('event_date', 'asc')
                          ->get()
                          ->map(function($event) {
                              return [
                                  'id' => $event->id,
                                  'name' => $event->name,
                                  'type' => $event->eventType->name,
                                  'date' => $event->event_date->format('d/m/Y'),
                                  'time' => $event->start_time,
                                  'location' => $event->address,
                                  'status' => $event->status->value,
                                  'capacity' => $event->capacity
                              ];
                          });

            if ($events->isEmpty()) {
                return response()->json([
                    'message' => 'No hay eventos programados en este momento.',
                    'events' => []
                ]);
            }

            return response()->json([
                'message' => 'Eventos encontrados',
                'events' => $events
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al listar eventos: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se pudo obtener la lista de eventos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
