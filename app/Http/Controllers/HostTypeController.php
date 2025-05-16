<?php

namespace App\Http\Controllers;

use App\Models\HostType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\HostTypeRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

class HostTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $hostTypes = HostType::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('HostTypes/Index', [
            'hostTypes' => $hostTypes,
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
        return Inertia::render('HostTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HostTypeRequest $request): RedirectResponse
    {
        HostType::create($request->validated());

        return redirect()->route('host-types.index')
            ->with('success', 'Tipo de anfitrión creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(HostType $hostType)
    {
        return Inertia::render('HostTypes/Show', [
            'hostType' => $hostType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HostType $hostType)
    {
        return Inertia::render('HostTypes/Edit', [
            'hostType' => $hostType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HostTypeRequest $request, HostType $hostType): RedirectResponse
    {
        $hostType->update($request->validated());

        return redirect()->route('host-types.index')
            ->with('success', 'Tipo de anfitrión actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HostType $hostType): RedirectResponse
    {
        // Verificamos si el tipo de anfitrión está siendo utilizado
        $hasRelatedHosts = DB::table('hosts')
            ->where('host_type_id', $hostType->id)
            ->exists();

        if ($hasRelatedHosts) {
            return back()->with('error', 'No es posible eliminar este tipo de anfitrión porque está siendo utilizado por uno o más anfitriones. Por favor, elimine primero los anfitriones asociados.');
        }

        // Si no tiene anfitriones relacionados, procedemos con la eliminación
        $hostType->delete();
        return redirect()->route('host-types.index')
            ->with('success', 'Tipo de anfitrión eliminado exitosamente');
    }
}
