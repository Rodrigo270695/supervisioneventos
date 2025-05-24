<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PlanRequest extends FormRequest
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
        $rules = [
            'event_id' => ['required', 'exists:events,id'],
            'plan_type_id' => [
                'required',
                'exists:plan_types,id',
                function ($attribute, $value, $fail) {
                    $query = \App\Models\Plan::where('event_id', $this->event_id)
                        ->where('plan_type_id', $value);

                    // Si estamos actualizando, excluimos el plan actual
                    if ($this->isMethod('PUT')) {
                        $query->where('id', '!=', $this->route('plan')->id);
                    }

                    if ($query->exists()) {
                        $planType = \App\Models\PlanType::find($value);
                        $fail("Ya existe un plano de tipo '{$planType->name}' para este evento.");
                    }
                }
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];

        // La imagen es requerida solo en la creación
        if ($this->isMethod('POST')) {
            $rules['image'] = ['required', 'image', 'max:3072'];
        } else {
            $rules['image'] = ['nullable', 'image', 'max:3072'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'El evento es obligatorio.',
            'event_id.exists' => 'El evento seleccionado no existe.',
            'plan_type_id.required' => 'El tipo de plano es obligatorio.',
            'plan_type_id.exists' => 'El tipo de plano seleccionado no existe.',
            'image.required' => 'La imagen del plano es obligatoria.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.max' => 'La imagen no debe pesar más de 3MB.',
            'description.string' => 'La descripción debe ser texto.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.',
        ];
    }
}
