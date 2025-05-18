<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Event;

class GuestRequest extends FormRequest
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
        $event = Event::findOrFail($this->event_id);
        $currentGuest = $this->route('guest');

        // Calcular el total de pases actuales del evento
        $totalCurrentPasses = $event->guests()
            ->when($currentGuest, function ($query) use ($currentGuest) {
                $query->where('id', '!=', $currentGuest->id);
            })
            ->sum('passes');

        return [
            'event_id' => ['required', 'exists:events,id'],
            'first_name' => ['required', 'string', 'max:40', 'min:2'],
            'last_name' => ['required', 'string', 'max:40', 'min:2'],
            'dni' => [
                'required',
                'string',
                'size:8',
                Rule::unique('guests')->where(function ($query) {
                    return $query->where('event_id', $this->event_id);
                })->ignore($this->guest)
            ],
            'table_number' => ['required', 'integer', 'min:1'],
            'passes' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                function ($attribute, $value, $fail) use ($event, $totalCurrentPasses) {
                    $newTotal = $totalCurrentPasses + $value;
                    if ($newTotal > $event->capacity) {
                        $remainingCapacity = $event->capacity - $totalCurrentPasses;
                        $fail("No hay suficiente capacidad. Capacidad restante: {$remainingCapacity} personas.");
                    }
                }
            ],
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
            'first_name.required' => 'El nombre es obligatorio',
            'first_name.min' => 'El nombre debe tener al menos :min caracteres',
            'first_name.max' => 'El nombre no puede tener más de :max caracteres',
            'last_name.required' => 'El apellido es obligatorio',
            'last_name.min' => 'El apellido debe tener al menos :min caracteres',
            'last_name.max' => 'El apellido no puede tener más de :max caracteres',
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener exactamente :size caracteres',
            'dni.unique' => 'Ya existe un invitado con este DNI en el evento',
            'table_number.required' => 'El número de mesa es obligatorio',
            'table_number.integer' => 'El número de mesa debe ser un número entero',
            'table_number.min' => 'El número de mesa debe ser mayor a :min',
            'passes.required' => 'El número de pases es obligatorio',
            'passes.integer' => 'El número de pases debe ser un número entero',
            'passes.min' => 'El número de pases debe ser mayor a :min',
            'passes.max' => 'El número de pases no puede ser mayor a :max',
        ];
    }
}
