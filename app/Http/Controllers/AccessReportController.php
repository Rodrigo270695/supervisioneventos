<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\GuestAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuestAccessExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AccessReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        // Lista de eventos para el selector
        $events = Event::select('id', 'name')
            ->orderBy('event_date', 'desc')
            ->get();

        // Accesos si hay un evento seleccionado
        $accesses = null;
        if ($request->has('event_id')) {
            $accesses = GuestAccess::with('guest')
                ->where('event_id', $request->event_id)
                ->orderBy('access_datetime', 'desc')
                ->paginate(50);
        }

        return Inertia::render('Reports/AccessReport', [
            'events' => $events,
            'accesses' => $accesses,
            'selectedEventId' => $request->event_id,
        ]);
    }

    public function export(Event $event)
    {
        // Verificar que el usuario tenga permiso para ver este evento
        $this->authorize('viewAny', GuestAccess::class);

        $fileName = "accesos_{$event->name}_" . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new GuestAccessExport($event), $fileName);
    }
}
