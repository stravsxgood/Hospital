<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk mengisi pengaturan default sistem SIMRS di tabel app_settings.
 *
 * Menggunakan updateOrCreate agar aman dijalankan berulang kali (idempotent)
 * tanpa menimpa pengaturan yang sudah dimodifikasi Super Admin.
 *
 * Jalankan independen:
 *   php artisan db:seed --class=AppSettingsSeeder
 */
class AppSettingsSeeder extends Seeder
{
    /**
     * Daftar pengaturan default sistem.
     *
     * @var array<int, array{key: string, value: string, group: string}>
     */
    private array $defaults = [
        // ── Pengaturan Tampilan Antrean Publik (DisplayBoard) ──
        [
            'key' => 'display.hospital_name',
            'value' => 'Rumah Sakit Hospital Population',
            'group' => 'display',
        ],
        [
            'key' => 'display.scroll_speed',
            'value' => '5000',
            'group' => 'display',
        ],
        [
            'key' => 'display.show_patient_name',
            'value' => 'true',
            'group' => 'display',
        ],
        [
            'key' => 'display.theme',
            'value' => 'evergreen',
            'group' => 'display',
        ],
        [
            'key' => 'display.announcement_text',
            'value' => 'Selamat datang di Rumah Sakit Hospital Population. Mohon sabar menunggu giliran Anda.',
            'group' => 'display',
        ],

        // ── Pengaturan Operasional ──
        [
            'key' => 'operational.inactive_threshold_days',
            'value' => '90',
            'group' => 'operational',
        ],
        [
            'key' => 'operational.default_quota_per_day',
            'value' => '20',
            'group' => 'operational',
        ],
    ];

    /**
     * Jalankan seeder pengaturan default.
     */
    public function run(): void
    {
        foreach ($this->defaults as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                ]
            );
        }

        $this->command->info('✅ Default app settings seeded successfully.');
    }
}
