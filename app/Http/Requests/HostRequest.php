<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'host_type_id' => ['required', 'exists:host_types,id'],
            'nombres' => ['required', 'string', 'max:100', 'min:2'],
            'apellidos' => ['required', 'string', 'max:100', 'min:2'],
            'dni' => [
                'nullable',
                'string',
                'size:8',
                Rule::unique('hosts')->where(function ($query) {
                    return $query->where('event_id', $this->event_id);
                })->ignore($this->host)
            ],
            'edad' => ['nullable', 'integer', 'min:18', 'max:100'],
            'correo' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'host_type_id.required' => 'El tipo de anfitrión es obligatorio.',
            'host_type_id.exists' => 'El tipo de anfitrión seleccionado no es válido.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden tener más de 100 caracteres.',
            'nombres.min' => 'Los nombres deben tener al menos 2 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 100 caracteres.',
            'apellidos.min' => 'Los apellidos deben tener al menos 2 caracteres.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 caracteres.',
            'dni.unique' => 'Este DNI ya está registrado en este evento.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.min' => 'La edad mínima es 18 años.',
            'edad.max' => 'La edad máxima es 100 años.',
            'correo.email' => 'El correo electrónico debe ser una dirección válida.',
            'correo.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
        ];
    }
}
