<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\EventSecurity;
use App\Http\Requests\SecurityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('security')
            ->with(['events' => function ($query) {
                $query->select('events.id', 'name');
            }]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('dni', 'like', "%{$request->search}%");
            });
        }

        $securityUsers = $query->paginate(20)
            ->withQueryString();

        // Obtener solo eventos programados y en progreso
        $events = Event::where('status', 'scheduled')
            ->orWhere('status', 'in_progress')
            ->select('id', 'name')
            ->orderBy('event_date', 'asc')
            ->get();

        return Inertia::render('Security/Index', [
            'securityUsers' => $securityUsers,
            'events' => $events,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(SecurityRequest $request)
    {
        $validated = $request->validated();

        // Crear usuario con DNI como contraseña
        $user = User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['dni']), // Usar DNI como contraseña
        ]);

        // Asignar rol de seguridad
        $securityRole = Role::firstOrCreate(['name' => 'security']);
        $user->assignRole($securityRole);

        // Asignar al evento
        EventSecurity::create([
            'event_id' => $validated['event_id'],
            'user_id' => $user->id,
            'active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Personal de seguridad registrado exitosamente',
                'user' => $user->load('events'),
            ]);
        }

        return back()->with('message', 'Personal de seguridad registrado exitosamente');
    }

    public function update(SecurityRequest $request, User $security)
    {
        $validated = $request->validated();

        // Actualizar datos del usuario
        $security->update([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'],
        ]);

        // Actualizar asignación de evento
        EventSecurity::updateOrCreate(
            ['user_id' => $security->id],
            [
                'event_id' => $validated['event_id'],
                'active' => true,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Personal de seguridad actualizado exitosamente',
                'user' => $security->fresh()->load('events'),
            ]);
        }

        return back()->with('message', 'Personal de seguridad actualizado exitosamente');
    }

    public function destroy(User $security)
    {
        // Eliminar todas las asignaciones de eventos
        EventSecurity::where('user_id', $security->id)->delete();

        // Eliminar el usuario
        $security->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Personal de seguridad eliminado exitosamente'
            ]);
        }

        return back()->with('message', 'Personal de seguridad eliminado exitosamente');
    }
}
