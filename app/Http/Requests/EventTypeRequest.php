<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:30',
                'min:3',
                Rule::unique('event_types', 'name')->ignore($this->event_type)
            ],
            'color' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 30 caracteres.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.unique' => 'Este nombre ya está en uso.',
            'color.required' => 'El color es obligatorio.',
            'description.string' => 'La descripción debe ser texto.',
        ];
    }
}
