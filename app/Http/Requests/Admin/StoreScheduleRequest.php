<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'doctor_id'  => ['required', 'integer', 'exists:doctor,doctor_id'],
            'poli_id'    => ['required', 'integer', 'exists:poli,poli_id'],
            'room_id'    => ['required', 'integer', 'exists:room,room_id'],
            'day'        => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'quota_day'  => ['required', 'integer', 'min:1', 'max:100'],
            'status'     => ['required', 'string', 'in:Aktif,Libur'],
        ];
    }
}
