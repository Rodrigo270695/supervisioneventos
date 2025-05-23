<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestAccess;
use App\Enums\EventStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\GuestAccessRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ManualGuestAccessController extends Controller
{
    public function index(): Response
    {
        // Obtener los eventos asignados al usuario actual
        $userEvents = Auth::user()->events()
            ->where('active', true)
            ->pluck('events.id');

        $query = Guest::with(['event', 'accesses' => function($query) {
            $query->latest('access_datetime');
        }])
        ->whereIn('event_id', $userEvents);

        // Aplicar búsqueda si existe
        if (request()->has('search')) {
            $search = request()->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $guests = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString()
            ->through(function ($guest) {
                return [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'full_name' => $guest->full_name,
                    'dni' => $guest->dni,
                    'table_number' => $guest->table_number,
                    'passes' => $guest->passes,
                    'used_passes' => $guest->used_passes,
                    'qr_code' => $guest->qr_code,
                    'event_name' => $guest->event->name,
                    'last_access' => $guest->accesses->first()?->access_datetime,
                    'available_passes' => $guest->passes - $guest->used_passes,
                    'guest' => [
                        'full_name' => $guest->full_name,
                        'dni' => $guest->dni,
                        'table_number' => $guest->table_number,
                        'passes' => $guest->passes,
                        'used_passes' => $guest->used_passes,
                        'event_name' => $guest->event->name
                    ]
                ];
            });

        return Inertia::render('GuestAccesses/Manual', [
            'guests' => $guests,
            'filters' => request()->only(['search'])
        ]);
    }

    public function validateGuest(Guest $guest)
    {
        // Verificar que el usuario tenga acceso al evento
        $userHasAccess = Auth::user()->events()
            ->where('events.id', $guest->event_id)
            ->where('active', true)
            ->exists();

        if (!$userHasAccess) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para registrar accesos en este evento'
            ], 403);
        }

        // Verificar si el evento está activo
        $event = $guest->event;
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el evento asociado al invitado'
            ], 400);
        }

        // Solo permitir acceso si el evento está en progreso
        if ($event->status !== EventStatus::IN_PROGRESS) {
            return response()->json([
                'status' => 'error',
                'message' => "El evento '{$event->name}' no está activo en este momento. Estado actual: {$event->status->label()}"
            ], 400);
        }

        // Obtener pases disponibles
        $availablePasses = $guest->passes - $guest->used_passes;

        if ($availablePasses <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay pases disponibles para este invitado'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'guest' => [
                'id' => $guest->id,
                'first_name' => $guest->first_name,
                'last_name' => $guest->last_name,
                'table_number' => $guest->table_number,
                'total_passes' => $guest->passes,
                'available_passes' => $availablePasses,
                'event_name' => $event->name
            ]
        ]);
    }

    public function store(GuestAccessRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $guest = Guest::where('qr_code', $request->qr_code)->first();

            // Verificar que el usuario tenga acceso al evento
            $userHasAccess = Auth::user()->events()
                ->where('events.id', $guest->event_id)
                ->where('active', true)
                ->exists();

            if (!$userHasAccess) {
                return back()->with('error', 'No tienes permiso para registrar accesos en este evento');
            }

            // Crear el registro de acceso
            GuestAccess::create([
                'guest_id' => $guest->id,
                'event_id' => $guest->event_id,
                'people_count' => $request->people_count,
                'access_datetime' => now()->setTimezone('America/Lima'),
                'access_type' => $request->access_type,
                'observations' => $request->observations
            ]);

            // Actualizar los pases utilizados
            if ($request->access_type === 'entry') {
                $guest->used_passes += $request->people_count;
                $guest->last_access = now()->setTimezone('America/Lima');
                $guest->save();
            }

            DB::commit();
            return back()->with('success', 'Acceso registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el acceso: ' . $e->getMessage());
        }
    }
}
