<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'resident_n' => ['required', 'string', 'max:16', 'unique:patient,resident_n'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'birthday_date' => ['required', 'date'],
            'number_phone' => ['nullable', 'string', 'max:20'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'email_verified_at' => now(),
                'current_team_id' => null,
            ]);

            // Simpan profil data medis ke tabel patients
            Patient::create([
                'user_id' => $user->id,
                'name' => $input['name'],
                'resident_n' => $input['resident_n'],
                'gender' => $input['gender'],
                'birthday_date' => $input['birthday_date'],
                'number_phone' => $input['number_phone'] ?? null,
            ]);

            return $user;
        });
    }
}