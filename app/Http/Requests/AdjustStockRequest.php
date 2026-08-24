<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AdjustStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('access-pekerja-only');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'   => ['required', 'string', 'in:add,subtract,set'],
            'amount' => ['required', 'integer', 'min:0'],
            'notes'  => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom error messages for stock adjustments.
     */
    public function messages(): array
    {
        return [
            'type.required'   => 'Tipe penyesuaian stok wajib dipilih.',
            'type.in'         => 'Tipe penyesuaian harus berupa tambah (add), kurangi (subtract), atau atur (set).',
            'amount.required' => 'Jumlah unit penyesuaian stok wajib diisi.',
            'amount.min'      => 'Jumlah stok tidak boleh bernilai negatif.',
        ];
    }
}
