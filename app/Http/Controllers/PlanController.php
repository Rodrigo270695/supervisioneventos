<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use App\Http\Requests\PlanRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use Inertia\Inertia;

class PlanController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(PlanRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Guardar la imagen
            $path = $request->file('image')->store('plans', 'public');
            $validated['image'] = $path;

            Plan::create($validated);

            DB::commit();
            return back()->with('success', 'Plano creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear el plano: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el plano');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Si hay una nueva imagen
            if ($request->hasFile('image')) {
                // Eliminar la imagen anterior
                if ($plan->image) {
                    Storage::disk('public')->delete($plan->image);
                }
                // Guardar la nueva imagen
                $path = $request->file('image')->store('plans', 'public');
                $validated['image'] = $path;
            }

            $plan->update($validated);

            DB::commit();
            return back()->with('success', 'Plano actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el plano: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el plano');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Eliminar la imagen
            if ($plan->image) {
                Storage::disk('public')->delete($plan->image);
            }

            $plan->delete();

            DB::commit();
            return back()->with('success', 'Plano eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar el plano: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el plano');
        }
    }
}
