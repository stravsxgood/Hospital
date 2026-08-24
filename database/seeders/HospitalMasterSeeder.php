<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class HospitalMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Spesialisasi
        $spec = Specialization::firstOrCreate(
            ['code_specialization' => 'SP-UM'],
            [
                'name_specialization' => 'Dokter Umum',
                'description' => 'Pelayanan kesehatan umum',
            ]
        );

        // 2. Poli
        $poli = Poli::firstOrCreate(
            ['kode_poli' => 'POL-UM'],
            [
                'name_poli' => 'Poli Umum',
                'location' => 'Lantai 1 Sayap Barat',
                'status' => 'Aktif',
            ]
        );

        // 3. Ruangan
        $room = Room::firstOrCreate(
            ['code_room' => 'R-101'],
            [
                'name_room' => 'Ruang Periksa 1',
                'type_room' => 'Pemeriksaan',
                'capacity' => 1,
                'floor' => 1,
            ]
        );

        // 4. Akun & Profil Dokter
        $doctorUser = User::firstOrCreate(
            ['email' => 'dokter@hospital.com'],
            [
                'name' => 'dr. Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'is_active' => true,
            ]
        );

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'specialization_id' => $spec->specialization_id,
                'name' => 'dr. Budi Santoso',
                'sip_number' => 'SIP/2026/001',
                'gender' => 'Laki-laki',
                'number_phone' => '081234567890',
                'join_date' => now(),
                'status' => 'aktif',
            ]
        );

        // Hubungkan Dokter ke Poli jika belum terhubung
        if (! $doctor->polis()->where('doctor_poli.poli_id', $poli->poli_id)->exists()) {
            $doctor->polis()->attach($poli->poli_id);
        }

        // 5. Jadwal Praktik Dokter
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        foreach ($days as $day) {
            DoctorSchedule::firstOrCreate(
                [
                    'doctor_id' => $doctor->doctor_id,
                    'day' => $day,
                ],
                [
                    'poli_id' => $poli->poli_id,
                    'room_id' => $room->room_id,
                    'start_time' => '08:00:00',
                    'end_time' => '14:00:00',
                    'quota_day' => 20,
                    'status' => 'Aktif',
                ]
            );
        }

        // 6. Akun & Profil Perawat
        $nurseUser = User::firstOrCreate(
            ['email' => 'perawat@hospital.com'],
            [
                'name' => 'Suster Siti Rahma',
                'password' => Hash::make('password123'),
                'role' => 'nurse',
                'is_active' => true,
            ]
        );

        Nurse::firstOrCreate(
            ['user_id' => $nurseUser->id],
            [
                'name' => 'Suster Siti Rahma',
                'registration_number' => 'STR-NURSE-001',
                'type' => 'tetap',
                'gender' => 'Perempuan',
            ]
        );

        // 7. Akun & Profil Pasien
        $patientUser = User::firstOrCreate(
            ['email' => 'pasien@hospital.com'],
            [
                'name' => 'Budi Pasien',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'is_active' => true,
            ]
        );

        Patient::firstOrCreate(
            ['user_id' => $patientUser->id],
            [
                'resident_n' => '3308012345670001',
                'name' => 'Budi Pasien',
                'gender' => 'Laki-laki',
                'birthday_date' => '2000-05-15',
                'address' => 'Magelang',
                'number_phone' => '081234567890',
                'disease' => 'Pemeriksaan Rutin',
                'registration_date' => now(),
                'status' => 'active',
            ]
        );

        // 8. Inisialisasi Spatie Roles
        $roles = ['super-admin', 'dpjp-doctor', 'staff-pekerja', 'koas-intern', 'patient'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 9. Akun Super Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        $adminUser->assignRole('super-admin');

        // Assign default roles ke user contoh
        $doctorUser->assignRole('dpjp-doctor');
        $nurseUser->assignRole('staff-pekerja');
        $patientUser->assignRole('patient');
    }
}
