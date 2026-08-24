<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
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
        $medicine = $this->route('medicine');
        $medicineId = is_object($medicine) ? $medicine->medicine_id : $medicine;

        return [
            'code_medicine' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicine', 'code_medicine')->ignore($medicineId, 'medicine_id'),
            ],
            'name_medicine' => ['required', 'string', 'max:255'],
            'type'          => ['required', 'string', 'max:100'],
            'stock'         => ['required', 'integer', 'min:0'],
            'unit'          => ['required', 'string', 'max:50'],
            'price'         => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom error messages for medicine update.
     */
    public function messages(): array
    {
        return [
            'code_medicine.required' => 'Kode obat wajib diisi.',
            'code_medicine.unique'   => 'Kode obat ini sudah digunakan oleh obat lain.',
            'name_medicine.required' => 'Nama obat wajib diisi.',
            'type.required'          => 'Bentuk sediaan/tipe obat wajib dipilih.',
            'stock.required'         => 'Jumlah stok wajib diisi.',
            'stock.min'              => 'Stok obat tidak boleh bernilai negatif.',
            'unit.required'          => 'Satuan obat wajib diisi.',
            'price.required'         => 'Harga satuan obat wajib diisi.',
            'price.min'              => 'Harga satuan obat tidak boleh bernilai negatif.',
        ];
    }
}
