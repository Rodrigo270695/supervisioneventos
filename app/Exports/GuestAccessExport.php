<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class GuestAccessExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function collection()
    {
        return $this->event->guestAccesses()
            ->with(['guest'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Invitado',
            'DNI',
            'Mesa',
            'Tipo de Acceso',
            'Personas',
            'Observaciones'
        ];
    }

    public function map($access): array
    {
        return [
            Carbon::parse($access->created_at)->format('d/m/Y H:i:s'),
            $access->guest->first_name . ' ' . $access->guest->last_name,
            $access->guest->dni,
            $access->guest->table_number,
            $access->access_type === 'entry' ? 'Entrada' : 'Salida',
            $access->people_count,
            $access->observations ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
