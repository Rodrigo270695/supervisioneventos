<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Event;
use App\Http\Requests\GuestRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $query = $event->guests()
            ->with('accesses')
            ->orderBy('table_number');

        // Aplicar filtro de búsqueda si existe
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $guests = $query->paginate(20)
            ->through(function ($guest) {
                return [
                    ...$guest->toArray(),
                    'full_name' => $guest->full_name,
                    'remaining_passes' => $guest->remaining_passes,
                ];
            });

        return Inertia::render('Events/Guests/Index', [
            'event' => $event,
            'guests' => $guests,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function store(GuestRequest $request, Event $event)
    {
        $validated = $request->validated();

        // Generar código único para QR
        $qrCode = Str::random(32);
        while (Guest::where('qr_code', $qrCode)->exists()) {
            $qrCode = Str::random(32);
        }

        $guest = $event->guests()->create([
            ...$validated,
            'qr_code' => $qrCode,
            'used_passes' => 0,
        ]);

        // Generar la imagen del QR
        $qrImage = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrCode);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Invitado registrado exitosamente',
                'guest' => [
                    ...$guest->toArray(),
                    'full_name' => $guest->full_name,
                    'qr_image' => base64_encode($qrImage),
                ],
            ]);
        }

        return back()->with('message', 'Invitado registrado exitosamente');
    }

    public function update(GuestRequest $request, Guest $guest)
    {
        $validated = $request->validated();
        $guest->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Invitado actualizado exitosamente',
                'guest' => [
                    ...$guest->fresh()->toArray(),
                    'full_name' => $guest->full_name,
                ],
            ]);
        }

        return back()->with('message', 'Invitado actualizado exitosamente');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->used_passes > 0) {
            return back()->with('error', 'No se puede eliminar un invitado que ya ha utilizado pases');
        }

        $guest->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Invitado eliminado exitosamente'
            ]);
        }

        return back()->with('message', 'Invitado eliminado exitosamente');
    }

    public function verifyAccess(Request $request)
    {
        $qrCode = $request->input('qr_code');
        $guest = Guest::where('qr_code', $qrCode)->firstOrFail();

        return response()->json([
            'guest' => [
                ...$guest->toArray(),
                'full_name' => $guest->full_name,
                'remaining_passes' => $guest->remaining_passes,
            ],
        ]);
    }

    public function registerAccess(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'people_count' => 'required|integer|min:1|max:10',
            'observations' => 'nullable|string|max:255',
        ]);

        try {
            $access = $guest->registerAccess(
                $validated['people_count'],
                $validated['observations'] ?? null
            );

            return response()->json([
                'message' => 'Acceso registrado exitosamente',
                'access' => $access,
                'guest' => [
                    ...$guest->fresh()->toArray(),
                    'full_name' => $guest->full_name,
                    'remaining_passes' => $guest->remaining_passes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function downloadAllQR(Event $event)
    {
        $guests = $event->guests()
            ->select('id', 'first_name', 'last_name', 'dni', 'qr_code')
            ->get()
            ->map(function ($guest) {
                return [
                    'qr_code' => $guest->qr_code,
                    'file_name' => $guest->dni . '-' . $guest->first_name . '-' . $guest->last_name,
                ];
            });

        return response()->json($guests);
    }

    public function getQrCode(Guest $guest)
    {
        $qrImage = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($guest->qr_code);

        return response($qrImage)->header('Content-Type', 'image/svg+xml');
    }
}
