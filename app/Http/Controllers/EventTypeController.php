<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventTypeRequest;
use App\Models\EventType;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $eventTypes = EventType::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('EventTypes/Index', [
            'eventTypes' => $eventTypes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventTypeRequest $request): RedirectResponse
    {
        EventType::create($request->validated());

        return redirect()->route('event-types.index')
            ->with('success', 'Tipo de evento creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventTypeRequest $request, EventType $eventType): RedirectResponse
    {
        $eventType->update($request->validated());

        return redirect()->route('event-types.index')
            ->with('success', 'Tipo de evento actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventType $eventType): RedirectResponse
    {
        // Verificamos si el tipo de evento está siendo utilizado
        $hasRelatedEvents = DB::table('events')
            ->where('event_type_id', $eventType->id)
            ->exists();

        if ($hasRelatedEvents) {
            return back()->with('error', 'No es posible eliminar este tipo de evento porque está siendo utilizado por uno o más eventos. Por favor, elimine primero los eventos asociados.');
        }

        // Si no tiene eventos relacionados, procedemos con la eliminación
        $eventType->delete();
        return redirect()->route('event-types.index')
            ->with('success', 'Tipo de evento eliminado exitosamente');
    }
}
