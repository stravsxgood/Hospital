<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Bersihkan Data Lama (Menggunakan tabel singular 'doctor_schedule')
        DB::statement('TRUNCATE TABLE doctor_schedule, doctor, specialization, poli, room RESTART IDENTITY CASCADE;');

        // Hapus akun user dokter lama
        User::where('email', 'LIKE', 'dokter%@hospital.com')->delete();

        // 1. Master Data Spesialisasi & Poliklinik (15 Unit Layanan)
        $medicalUnits = [
            ['code_spec' => 'SP-PD', 'spec' => 'Spesialis Penyakit Dalam (Sp.PD)', 'kode_poli' => 'POLI-PD', 'poli' => 'Poli Penyakit Dalam', 'location' => 'Gedung A, Lantai 1'],
            ['code_spec' => 'SP-A', 'spec' => 'Spesialis Anak (Sp.A)', 'kode_poli' => 'POLI-ANAK', 'poli' => 'Poli Anak', 'location' => 'Gedung B, Lantai 1'],
            ['code_spec' => 'SP-JP', 'spec' => 'Spesialis Jantung & Pembuluh Darah (Sp.JP)', 'kode_poli' => 'POLI-JTG', 'poli' => 'Poli Jantung', 'location' => 'Gedung A, Lantai 2'],
            ['code_spec' => 'SP-M', 'spec' => 'Spesialis Mata (Sp.M)', 'kode_poli' => 'POLI-MATA', 'poli' => 'Poli Mata', 'location' => 'Gedung B, Lantai 2'],
            ['code_spec' => 'SP-S', 'spec' => 'Spesialis Saraf (Sp.S)', 'kode_poli' => 'POLI-SRF', 'poli' => 'Poli Saraf', 'location' => 'Gedung A, Lantai 2'],
            ['code_spec' => 'SP-B', 'spec' => 'Spesialis Bedah Umum (Sp.B)', 'kode_poli' => 'POLI-BDH', 'poli' => 'Poli Bedah', 'location' => 'Gedung C, Lantai 1'],
            ['code_spec' => 'SP-OG', 'spec' => 'Spesialis Kebidanan & Kandungan (Sp.OG)', 'kode_poli' => 'POLI-KDG', 'poli' => 'Poli Kebidanan & Kandungan', 'location' => 'Gedung B, Lantai 1'],
            ['code_spec' => 'SP-THT', 'spec' => 'Spesialis THT-KL (Sp.THT-BKL)', 'kode_poli' => 'POLI-THT', 'poli' => 'Poli THT', 'location' => 'Gedung B, Lantai 2'],
            ['code_spec' => 'SP-KK', 'spec' => 'Spesialis Kulit & Kelamin (Sp.KK)', 'kode_poli' => 'POLI-KLT', 'poli' => 'Poli Dermatologi', 'location' => 'Gedung C, Lantai 2'],
            ['code_spec' => 'SP-KJ', 'spec' => 'Spesialis Kedokteran Jiwa (Sp.KJ)', 'kode_poli' => 'POLI-JWA', 'poli' => 'Poli Psikiatri', 'location' => 'Gedung C, Lantai 2'],
            ['code_spec' => 'SP-OT', 'spec' => 'Spesialis Orthopedi & Traumatologi (Sp.OT)', 'kode_poli' => 'POLI-ORT', 'poli' => 'Poli Orthopedi', 'location' => 'Gedung C, Lantai 1'],
            ['code_spec' => 'SP-P', 'spec' => 'Spesialis Paru (Sp.P)', 'kode_poli' => 'POLI-PARU', 'poli' => 'Poli Paru', 'location' => 'Gedung A, Lantai 3'],
            ['code_spec' => 'SP-RAD', 'spec' => 'Spesialis Radiologi (Sp.Rad)', 'kode_poli' => 'POLI-RAD', 'poli' => 'Poli Radiologi', 'location' => 'Gedung Utama, Lantai 1'],
            ['code_spec' => 'SP-KG', 'spec' => 'Dokter Gigi Spesialis Konservasi Gigi (Sp.KG)', 'kode_poli' => 'POLI-GIGI', 'poli' => 'Poli Gigi & Mulut', 'location' => 'Gedung A, Lantai 1'],
            ['code_spec' => 'DR-UMUM', 'spec' => 'Dokter Umum', 'kode_poli' => 'POLI-UMUM', 'poli' => 'Poli Umum', 'location' => 'Gedung Utama, Lantai 1'],
        ];

        $specializations = [];
        $polis = [];

        foreach ($medicalUnits as $unit) {
            $specializations[] = Specialization::create([
                'code_specialization' => $unit['code_spec'],
                'name_specialization' => $unit['spec'],
            ]);

            $polis[] = Poli::create([
                'kode_poli' => $unit['kode_poli'],
                'name_poli' => $unit['poli'],
                'location' => $unit['location'],
                'status' => 'Aktif',
            ]);
        }

        // 2. Master Data Ruangan Pemeriksaan (Ruang 101 - 130)
        $rooms = [];
        for ($i = 101; $i <= 130; $i++) {
            $rooms[] = Room::create([
                'code_room' => "RM-$i",
                'name_room' => "Ruang Periksa $i",
                'type_room' => 'Pemeriksaan',
                'capacity' => 1,
                'floor' => (int) substr((string) $i, 0, 1),
            ]);
        }

        // 3. Pool Nama Dokter
        $maleFirstNames = [
            'Budi', 'Hendra', 'Agus', 'Dimas', 'Aditya', 'Faisal', 'Kevin', 'Bambang',
            'Doni', 'Farhan', 'Hari', 'Indra', 'Kemal', 'Lutfi', 'Naufal', 'Pandu',
            'Rio', 'Taufik', 'Usman', 'Wahyu', 'Rian', 'Fajar', 'Satria', 'Bagas', 'Ilham',
        ];

        $femaleFirstNames = [
            'Siti', 'Ratna', 'Maya', 'Jessica', 'Lina', 'Anisa', 'Cynthia', 'Eka',
            'Gita', 'Julia', 'Mega', 'Olivia', 'Qory', 'Sarah', 'Vina', 'Dian',
            'Nadia', 'Putri', 'Tiara', 'Zahra', 'Ayu', 'Rini', 'Tari', 'Bella', 'Dewi',
        ];

        $lastNames = [
            'Santoso', 'Rahmawati', 'Pratama', 'Dewi', 'Wijaya', 'Suryo', 'Kusuma',
            'Nugraha', 'Iskandar', 'Rahman', 'Marlina', 'Sanjaya', 'Permata', 'Irawan',
            'Melati', 'Prasetyo', 'Putri', 'Maulana', 'Savitri', 'Wibowo', 'Gunawan',
            'Perez', 'Palevi', 'Hakim', 'Utami', 'Azhar', 'Zalianty', 'Wicaksono',
            'Sandioriva', 'Febrian', 'Sechan', 'Hidayat', 'Harun', 'Panduwinata', 'Saputra',
        ];

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $timeSlots = [
            ['start' => '08:00:00', 'end' => '12:00:00'],
            ['start' => '13:00:00', 'end' => '16:30:00'],
            ['start' => '16:30:00', 'end' => '20:30:00'],
        ];

        $defaultPassword = Hash::make('password');

        // 4. Looping Pembuatan 100 Dokter & Jadwal
        for ($i = 1; $i <= 100; $i++) {
            $isMale = ($i % 2 !== 0);
            $gender = $isMale ? 'Laki-laki' : 'Perempuan';
            $firstName = $isMale
                ? $maleFirstNames[($i - 1) % count($maleFirstNames)]
                : $femaleFirstNames[($i - 1) % count($femaleFirstNames)];
            $lastName = $lastNames[($i * 3) % count($lastNames)];

            $specModel = $specializations[($i - 1) % count($specializations)];
            $prefix = ($specModel->code_specialization === 'SP-KG') ? 'drg. ' : 'dr. ';
            $fullName = $prefix.$firstName.' '.$lastName;

            $email = "dokter{$i}@hospital.com";

            // User Account
            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => $defaultPassword,
                'role' => 'doctor',
                'email_verified_at' => now(),
            ]);

            // Doctor Entity
            $sipNumber = 'SIP.503/'.str_pad((string) $i, 4, '0', STR_PAD_LEFT).'/DS/2026';

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'specialization_id' => $specModel->specialization_id ?? $specModel->id,
                'name' => $fullName,
                'sip_number' => $sipNumber,
                'gender' => $gender,
                'number_phone' => '0812'.rand(10000000, 99999999),
                'email' => $email,
                'alamat' => 'Jl. Kesehatan Raya No. '.$i.', Magelang',
                'join_date' => now()->subDays(rand(30, 1000))->format('Y-m-d'),
                'status' => 'aktif',
            ]);

            // Pasangkan Poli & Ruangan
            $poliModel = $polis[($i - 1) % count($polis)];
            $assignedRoom = $rooms[($i - 1) % count($rooms)];

            // 2 - 3 Jadwal Praktik per Dokter
            $assignedDays = [
                $days[($i) % 7],
                $days[($i + 2) % 7],
            ];

            if ($i % 3 === 0) {
                $assignedDays[] = $days[($i + 4) % 7];
            }

            foreach ($assignedDays as $dayIndex => $day) {
                $slot = $timeSlots[($i + $dayIndex) % count($timeSlots)];

                DoctorSchedule::create([
                    'doctor_id' => $doctor->doctor_id ?? $doctor->id,
                    'poli_id' => $poliModel->poli_id ?? $poliModel->id,
                    'room_id' => $assignedRoom->room_id ?? $assignedRoom->id,
                    'day' => $day,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'quota_day' => rand(20, 40),
                    'status' => 'Aktif',
                ]);
            }
        }
    }
}
