<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\HostType;
use App\Models\PlanType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\EventRequest;
use App\Models\TimeType;

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

    public function show(Request $request, Event $event)
    {
        // Cargar el evento con sus relaciones y ordenar los tiempos por hora de inicio
        $event->load([
            'eventType',
            'hosts.hostType',
            'times' => function($query) {
                $query->orderBy('start_time', 'asc');
            },
            'times.timeType',
            'notes',
            'plans.planType',
        ]);

        // Cargar tipos de anfitriones para el formulario
        $hostTypes = HostType::orderBy('name', 'asc')->get();

        // Cargar tipos de tiempo para el formulario
        $timeTypes = TimeType::orderBy('name', 'asc')->get();

        // Cargar tipos de eventos para el formulario de edición
        $eventTypes = EventType::orderBy('name', 'asc')->get();

        // Cargar tipos de planos para el formulario
        $planTypes = PlanType::orderBy('name', 'asc')->get();

        // Asegurarnos de que los hosts incluyan toda la información necesaria
        $hosts = $event->hosts->map(function ($host) {
            return [
                'id' => $host->id,
                'event_id' => $host->event_id,
                'host_type_id' => $host->host_type_id,
                'nombres' => $host->nombres,
                'apellidos' => $host->apellidos,
                'dni' => $host->dni,
                'edad' => $host->edad,
                'correo' => $host->correo,
                'full_name' => $host->full_name,
                'hostType' => [
                    'id' => $host->hostType->id,
                    'name' => $host->hostType->name
                ]
            ];
        });

        // Formatear los tiempos con su información completa
        $times = $event->times->map(function ($time) {
            // Formatear las horas a HH:mm
            $start_time = date('H:i', strtotime($time->start_time));
            $end_time = $time->end_time ? date('H:i', strtotime($time->end_time)) : null;

            return [
                'id' => $time->id,
                'event_id' => $time->event_id,
                'time_type_id' => $time->time_type_id,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'description' => $time->description,
                'timeType' => [
                    'id' => $time->timeType->id,
                    'name' => $time->timeType->name
                ]
            ];
        });

        // Formatear las notas
        $notes = $event->notes->map(function ($note) {
            return [
                'id' => $note->id,
                'event_id' => $note->event_id,
                'description' => $note->description,
                'amount' => $note->amount,
            ];
        });

        // Formatear los planos
        $plans = $event->plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'event_id' => $plan->event_id,
                'plan_type_id' => $plan->plan_type_id,
                'image' => $plan->image,
                'description' => $plan->description,
                'planType' => [
                    'id' => $plan->planType->id,
                    'name' => $plan->planType->name
                ]
            ];
        });

        // Preparar la consulta de invitados con paginación y búsqueda
        $guestsQuery = $event->guests()->orderBy('table_number');

        // Aplicar filtro de búsqueda si existe
        if ($request->has('search')) {
            $search = $request->input('search');
            $guestsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        // Paginar los invitados
        $guests = $guestsQuery->paginate(20)->through(function ($guest) {
            return [
                ...$guest->toArray(),
                'full_name' => $guest->full_name,
                'remaining_passes' => $guest->remaining_passes,
            ];
        });

        return Inertia::render('Events/Show', [
            'event' => $event,
            'hosts' => $hosts,
            'times' => $times,
            'notes' => $notes,
            'hostTypes' => $hostTypes,
            'timeTypes' => $timeTypes,
            'eventTypes' => $eventTypes,
            'guests' => $guests,
            'plans' => $plans,
            'planTypes' => $planTypes,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
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
