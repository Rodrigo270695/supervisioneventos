<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TimeRequest extends FormRequest
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
            'time_type_id' => [
                'required',
                'exists:time_types,id',
                Rule::unique('times')->where(function ($query) {
                    return $query->where('event_id', $this->event_id)
                                ->where('id', '!=', $this->route('time')?->id);
                })
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'evento',
            'time_type_id' => 'tipo de tiempo',
            'start_time' => 'hora de inicio',
            'end_time' => 'hora de fin',
            'description' => 'descripción',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'time_type_id.unique' => 'Este tipo de tiempo ya está registrado en este evento.',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:MM.',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }
}
