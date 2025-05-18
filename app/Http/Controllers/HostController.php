<?php

namespace App\Http\Controllers;

use App\Http\Requests\HostRequest;
use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class HostController extends Controller
{
    /**
     * Store a newly created host in storage.
     */
    public function store(HostRequest $request)
    {
        $host = Host::create($request->validated());
        $host->load('hostType');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Anfitrión registrado exitosamente',
                'host' => $host,
            ]);
        }

        return back()->with('success', 'Anfitrión registrado exitosamente');
    }

    /**
     * Update the specified host in storage.
     */
    public function update(HostRequest $request, Host $host)
    {
        $host->update($request->validated());
        $host->load('hostType');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Anfitrión actualizado exitosamente',
                'host' => $host,
            ]);
        }

        return back()->with('success', 'Anfitrión actualizado exitosamente');
    }

    /**
     * Remove the specified host from storage.
     */
    public function destroy(Host $host)
    {
        $host->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Anfitrión eliminado exitosamente'
            ]);
        }

        return back()->with('success', 'Anfitrión eliminado exitosamente');
    }
}
