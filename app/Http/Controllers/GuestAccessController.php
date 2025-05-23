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
use Inertia\Response;

class GuestAccessController extends Controller
{
    public function index(): Response
    {
        // Obtener los eventos asignados al usuario actual
        $userEvents = auth()->user()->events()
            ->where('active', true)
            ->pluck('events.id');

        $query = GuestAccess::with('guest')
            ->whereIn('event_id', $userEvents);

        // Aplicar búsqueda si existe
        if (request()->has('search')) {
            $search = request()->input('search');
            $query->whereHas('guest', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $guestAccesses = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('GuestAccesses/Index', [
            'guestAccesses' => $guestAccesses,
            'filters' => request()->only(['search'])
        ]);
    }

    public function scan(): Response
    {
        return Inertia::render('GuestAccesses/Scan');
    }

    public function validateAccess(Request $request)
    {
        $qrCode = $request->input('qr_code');
        $guest = Guest::where('qr_code', $qrCode)->first();

        if (!$guest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Código QR no válido o invitado no encontrado'
            ], 404);
        }

        // Verificar que el usuario tenga acceso al evento
        $userHasAccess = auth()->user()->events()
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
            $userHasAccess = auth()->user()->events()
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
