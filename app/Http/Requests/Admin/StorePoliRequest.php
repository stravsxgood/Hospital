<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePoliRequest extends FormRequest
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
            'kode_poli' => ['required', 'string', 'max:20', 'unique:poli,kode_poli'],
            'name_poli' => ['required', 'string', 'max:255'],
            'location'  => ['required', 'string', 'max:255'],
            'status'    => ['required', 'string', 'in:Aktif,Nonaktif'],
        ];
    }
}
