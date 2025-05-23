<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Guest;

class GuestAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $guest = Guest::where('qr_code', $this->qr_code)->first();
        $availablePasses = $guest ? $guest->passes - $guest->used_passes : 0;

        return [
            'qr_code' => ['required', 'string', 'exists:guests,qr_code'],
            'people_count' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($guest, $availablePasses) {
                    if ($this->access_type === 'entry' && $value > $availablePasses) {
                        $fail("No hay suficientes pases disponibles. Pases disponibles: {$availablePasses}");
                    }
                }
            ],
            'access_type' => ['required', 'string', 'in:entry,exit'],
            'observations' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'qr_code.required' => 'El código QR es obligatorio.',
            'qr_code.exists' => 'El código QR no es válido.',
            'people_count.required' => 'El número de personas es obligatorio.',
            'people_count.integer' => 'El número de personas debe ser un número entero.',
            'people_count.min' => 'El número de personas debe ser al menos 1.',
            'access_type.required' => 'El tipo de acceso es obligatorio.',
            'access_type.in' => 'El tipo de acceso debe ser entrada o salida.',
            'observations.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ];
    }
}
