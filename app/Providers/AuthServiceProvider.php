<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Configurar el token de restablecimiento de contraseña para usar DNI en lugar de email
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return route('password.reset', [
                'token' => $token,
                'email' => $user->dni, // Usamos el campo DNI pero mantenemos el nombre del parámetro como 'email' para compatibilidad
            ]);
        });
    }
}
