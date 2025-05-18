<?php

namespace App\Http\Controllers;

use App\Models\Time;
use App\Http\Requests\TimeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeRequest $request)
    {
        $time = Time::create($request->validated());
        $time->load('timeType');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tiempo registrado exitosamente',
                'time' => $time
            ]);
        }

        return redirect()->back()->with('success', 'Tiempo registrado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeRequest $request, Time $time)
    {
        $time->update($request->validated());
        $time->load('timeType');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tiempo actualizado exitosamente',
                'time' => $time
            ]);
        }

        return redirect()->back()->with('success', 'Tiempo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Time $time)
    {
        $time->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Tiempo eliminado exitosamente'
            ]);
        }

        return redirect()->back()->with('success', 'Tiempo eliminado exitosamente');
    }
}
