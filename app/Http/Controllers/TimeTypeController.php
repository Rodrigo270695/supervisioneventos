<?php

namespace App\Http\Controllers;

use App\Models\TimeType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\TimeTypeRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

class TimeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $timeTypes = TimeType::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('TimeTypes/Index', [
            'timeTypes' => $timeTypes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('TimeTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeTypeRequest $request): RedirectResponse
    {
        TimeType::create($request->validated());

        return redirect()->route('time-types.index')
            ->with('success', 'Tipo de tiempo creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeType $timeType)
    {
        return Inertia::render('TimeTypes/Show', [
            'timeType' => $timeType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeType $timeType)
    {
        return Inertia::render('TimeTypes/Edit', [
            'timeType' => $timeType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeTypeRequest $request, TimeType $timeType): RedirectResponse
    {
        $timeType->update($request->validated());

        return redirect()->route('time-types.index')
            ->with('success', 'Tipo de tiempo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeType $timeType): RedirectResponse
    {
        // Verificamos si el tipo de tiempo está siendo utilizado
        $hasRelatedTimes = DB::table('times')
            ->where('time_type_id', $timeType->id)
            ->exists();

        if ($hasRelatedTimes) {
            return back()->with('error', 'No es posible eliminar este tipo de tiempo porque está siendo utilizado por uno o más tiempos. Por favor, elimine primero los tiempos asociados.');
        }

        // Si no tiene tiempos relacionados, procedemos con la eliminación
        $timeType->delete();
        return redirect()->route('time-types.index')
            ->with('success', 'Tipo de tiempo eliminado exitosamente');
    }
}
