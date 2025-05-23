<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'event_type_id' => ['required', 'exists:event_types,id'],
            'name' => ['required', 'string', 'max:100', 'min:3'],
            'capacity' => ['required', 'integer', 'min:1'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (!$value) return;

                    $startTime = Carbon::createFromFormat('H:i', $this->input('start_time'));
                    $endTime = Carbon::createFromFormat('H:i', $value);

                    // Si la hora de fin es menor que la hora de inicio, asumimos que es del día siguiente
                    if ($endTime->lt($startTime)) {
                        $endTime->addDay();
                    }

                    // Calculamos la duración en horas
                    $duration = $startTime->diffInHours($endTime);

                    // Validamos que la duración no sea mayor a 24 horas
                    if ($duration > 24) {
                        $fail('La duración del evento no puede ser mayor a 24 horas.');
                    }
                }
            ],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_type_id.required' => 'El tipo de evento es obligatorio.',
            'event_type_id.exists' => 'El tipo de evento seleccionado no es válido.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'capacity.required' => 'La capacidad es obligatoria.',
            'capacity.integer' => 'La capacidad debe ser un número entero.',
            'capacity.min' => 'La capacidad debe ser al menos 1.',
            'event_date.required' => 'La fecha del evento es obligatoria.',
            'event_date.date' => 'La fecha del evento no es válida.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:MM.',
            'address.required' => 'La dirección es obligatoria.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
