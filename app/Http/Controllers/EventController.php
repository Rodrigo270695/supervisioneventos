<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\HostType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\EventRequest;

class EventController extends Controller
{
    public function index()
    {
        // Cargar eventos con sus tipos, con nombres de clave alineados con el frontend
        $events = Event::with('eventType')->get()->map(function ($event) {
            // Renombrar la relación para que coincida con el frontend
            $data = $event->toArray();
            return $data;
        });

        // Cargar tipos de eventos
        $eventTypes = EventType::all();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'eventTypes' => $eventTypes
        ]);
    }

    public function show(Event $event)
    {
        // Cargar el evento con sus relaciones
        $event->load('eventType');

        // Cargar tipos de anfitriones para el formulario
        $hostTypes = HostType::all();
        
        // Cargar tipos de eventos para el formulario de edición
        $eventTypes = EventType::all();

        return Inertia::render('Events/Show', [
            'event' => $event,
            'hostTypes' => $hostTypes,
            'eventTypes' => $eventTypes
        ]);
    }

    public function store(EventRequest $request)
    {
        Event::create($request->validated());
        return redirect()->back()->with('success', 'Evento creado exitosamente');
    }

    public function update(EventRequest $request, Event $event)
    {
        $event->update($request->validated());
        return redirect()->back()->with('success', 'Evento actualizado exitosamente');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->back()->with('success', 'Evento eliminado exitosamente');
    }
}
