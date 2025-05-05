<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'dni' => 'required|digits:8|numeric',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.numeric' => 'El DNI debe contener solo números.',
        ]);

        // Para compatibilidad con el sistema de restablecimiento de contraseña predeterminado de Laravel
        // que usa el campo 'email', creamos un array con el campo 'email' apuntando al valor del DNI
        Password::sendResetLink(
            ['email' => $request->dni]
        );

        return back()->with('status', __('Se enviará un enlace de restablecimiento si la cuenta existe.'));
    }
}
