<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Http\Requests\NoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request)
    {
        $note = Note::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Nota registrada exitosamente',
                'note' => $note
            ]);
        }

        return redirect()->back()->with('success', 'Nota registrada exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Nota actualizada exitosamente',
                'note' => $note
            ]);
        }

        return redirect()->back()->with('success', 'Nota actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Nota eliminada exitosamente'
            ]);
        }

        return redirect()->back()->with('success', 'Nota eliminada exitosamente');
    }
}
