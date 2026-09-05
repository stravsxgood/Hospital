<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Billing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBillingRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berwenang melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Akses diberikan kepada staf medis, perawat, atau admin
        return $user->nurse !== null
            || in_array($user->role ?? '', ['nurse', 'admin', 'super-admin', 'staff', 'staff-pekerja', 'doctor'])
            || $user->can('access-pekerja-only');
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,appointment_id'],
            'amount' => ['nullable', 'numeric', 'min:1000', 'max:100000000'],
            'payment_type' => ['nullable', 'string', 'in:invoice,qris'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'appointment_id.required' => 'ID janji temu wajib disertakan.',
            'appointment_id.exists' => 'Janji temu tidak ditemukan dalam sistem.',
            'amount.min' => 'Nominal tagihan minimal Rp 1.000.',
            'amount.max' => 'Nominal tagihan melebihi batas maksimal yang diizinkan.',
            'payment_type.in' => 'Metode pembayaran harus berupa invoice atau qris.',
        ];
    }

    /**
     * Validasi lanjutan setelah aturan dasar terpenuhi:
     * Memastikan janji temu belum memiliki tagihan aktif yang belum kedaluwarsa/batal.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $appointmentId = $this->input('appointment_id');

            if (! $appointmentId) {
                return;
            }

            $existingActiveBilling = Billing::where('reservation_id', $appointmentId)
                ->orWhere('appointment_id', $appointmentId)
                ->whereIn('status', ['unpaid', 'pending', 'paid', 'UNPAID', 'PENDING', 'PAID', 'SETTLED'])
                ->first();

            if ($existingActiveBilling) {
                if ($existingActiveBilling->isPaid()) {
                    $validator->errors()->add('appointment_id', 'Tagihan untuk pasien ini sudah lunas (#'.$existingActiveBilling->invoice_number.').');
                } else {
                    $validator->errors()->add('appointment_id', 'Tagihan aktif untuk janji temu ini sudah ada (#'.$existingActiveBilling->invoice_number.'). Silakan selesaikan atau batalkan tagihan yang ada.');
                }
            }
        });
    }
}
