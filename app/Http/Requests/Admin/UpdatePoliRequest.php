<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePoliRequest extends FormRequest
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
        $poliId = $this->route('poli');
        if (is_object($poliId)) {
            $poliId = $poliId->poli_id;
        }

        return [
            'kode_poli' => ['required', 'string', 'max:20', 'unique:poli,kode_poli,' . $poliId . ',poli_id'],
            'name_poli' => ['required', 'string', 'max:255'],
            'location'  => ['required', 'string', 'max:255'],
            'status'    => ['required', 'string', 'in:Aktif,Nonaktif'],
        ];
    }
}
