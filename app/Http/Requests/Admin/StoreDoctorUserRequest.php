<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorUserRequest extends FormRequest
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
            // User Data
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],

            // Doctor Master Profile
            'specialization_id' => ['required', 'integer', 'exists:specialization,specialization_id'],
            'sip_number' => ['required', 'string', 'max:50', 'unique:doctor,sip_number'],
            'gender' => ['required', 'string', 'in:Laki-laki,Perempuan'],
            'number_phone' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'join_date' => ['nullable', 'date'],

            // Optional Initial Practice Schedule
            'create_schedule' => ['sometimes', 'boolean'],
            'poli_id' => ['required_if:create_schedule,true', 'nullable', 'integer', 'exists:poli,poli_id'],
            'room_id' => ['required_if:create_schedule,true', 'nullable', 'integer', 'exists:room,room_id'],
            'day' => ['required_if:create_schedule,true', 'nullable', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required_if:create_schedule,true', 'nullable', 'date_format:H:i'],
            'end_time' => ['required_if:create_schedule,true', 'nullable', 'date_format:H:i'],
            'quota_day' => ['required_if:create_schedule,true', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
