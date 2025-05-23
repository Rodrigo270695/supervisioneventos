<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SecurityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $securityId = $this->route('security')?->id;

        return [
            'name' => 'required|string|max:255|min:3',
            'dni' => ['required', 'string', 'size:8', Rule::unique('users', 'dni')->ignore($securityId)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($securityId)],
            'event_id' => 'required|exists:events,id',
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
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede tener más de :max caracteres',
            'name.min' => 'El nombre debe tener al menos :min caracteres',
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener exactamente :size dígitos',
            'dni.unique' => 'Este DNI ya está registrado',
            'email.email' => 'El correo electrónico debe ser una dirección válida',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'event_id.required' => 'Debe seleccionar un evento',
            'event_id.exists' => 'El evento seleccionado no es válido',
        ];
    }
}
