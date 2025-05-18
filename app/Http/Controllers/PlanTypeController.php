<?php

namespace App\Http\Controllers;

use App\Models\PlanType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\PlanTypeRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

class PlanTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $perPage = 10;

        $planTypes = PlanType::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('PlanTypes/Index', [
            'planTypes' => $planTypes,
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
        return Inertia::render('PlanTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlanTypeRequest $request): RedirectResponse
    {
        PlanType::create($request->validated());

        return redirect()->route('plan-types.index')
            ->with('success', 'Tipo de plano creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(PlanType $planType)
    {
        return Inertia::render('PlanTypes/Show', [
            'planType' => $planType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlanType $planType)
    {
        return Inertia::render('PlanTypes/Edit', [
            'planType' => $planType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlanTypeRequest $request, PlanType $planType): RedirectResponse
    {
        $planType->update($request->validated());

        return redirect()->route('plan-types.index')
            ->with('success', 'Tipo de plano actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlanType $planType): RedirectResponse
    {
        // Verificamos si el tipo de plano está siendo utilizado
        $hasRelatedPlans = DB::table('plans')
            ->where('plan_type_id', $planType->id)
            ->exists();

        if ($hasRelatedPlans) {
            return back()->with('error', 'No es posible eliminar este tipo de plano porque está siendo utilizado por uno o más planos. Por favor, elimine primero los planos asociados.');
        }

        // Si no tiene planos relacionados, procedemos con la eliminación
        $planType->delete();
        return redirect()->route('plan-types.index')
            ->with('success', 'Tipo de plano eliminado exitosamente');
    }
}
