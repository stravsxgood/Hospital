<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat role admin
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // 2. Buat User Admin
        $user = User::firstOrCreate(
            ['email' => 'admin@hospital.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Pasangkan role (idempoten, tanpa memicu benturan operator Pint)
        $user->syncRoles([$adminRole]);

        // 4. Jalankan seeder master lainnya
        $this->call([
            DoctorSeeder::class,
            HospitalMasterSeeder::class,
            HospitalClinicalMasterSeeder::class,
        ]);
    }

}