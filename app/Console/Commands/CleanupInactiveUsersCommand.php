<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Perintah Artisan untuk menonaktifkan akun pengguna yang tidak pernah login
 * dalam jangka waktu tertentu (default: 90 hari).
 *
 * Melindungi integritas data dengan TIDAK menghapus akun (SoftDelete maupun hard-delete),
 * melainkan hanya menetapkan is_active = false. Akun Super Admin & Admin dikecualikan.
 *
 * Penggunaan:
 *   php artisan admin:cleanup-inactive-users               # Deaktivasi (default 90 hari)
 *   php artisan admin:cleanup-inactive-users --days=60      # Threshold 60 hari
 *   php artisan admin:cleanup-inactive-users --dry-run      # Simulasi tanpa perubahan
 */
class CleanupInactiveUsersCommand extends Command
{
    protected $signature = 'admin:cleanup-inactive-users
        {--days=90 : Jumlah hari inaktivitas sebelum akun dinonaktifkan}
        {--dry-run : Simulasi — tampilkan daftar akun tanpa mengubah status}';

    protected $description = 'Nonaktifkan akun pengguna yang tidak login selama N hari (default: 90)';

    /**
     * Eksekusi perintah pembersihan akun inaktif.
     *
     * Logika:
     * 1. Cari user yang last_login_at < threshold ATAU (last_login_at NULL DAN created_at < threshold)
     * 2. Kecualikan admin/super-admin
     * 3. Set is_active = false
     * 4. Untuk dokter: set status = 'pensiun', jadwal = 'Libur'
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $isDryRun = (bool) $this->option('dry-run');
        $threshold = Carbon::now()->subDays($days);

        $this->info("🔍 Mencari akun inaktif (tidak login sejak {$threshold->toDateString()})...");

        // Query: user aktif yang tidak login dalam threshold, kecuali admin
        $inactiveUsers = User::where('is_active', true)
            ->where(function ($query) use ($threshold) {
                $query->where('last_login_at', '<', $threshold)
                    ->orWhere(function ($q) use ($threshold) {
                        $q->whereNull('last_login_at')
                            ->where('created_at', '<', $threshold);
                    });
            })
            ->whereNotIn('role', ['admin', 'super-admin'])
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin']);
            })
            ->with(['doctor', 'nurse'])
            ->get();

        if ($inactiveUsers->isEmpty()) {
            $this->info('✅ Tidak ada akun inaktif yang ditemukan.');

            return self::SUCCESS;
        }

        // Tampilkan tabel ringkasan
        $tableData = $inactiveUsers->map(fn (User $user) => [
            'ID' => $user->id,
            'Nama' => $user->name,
            'Email' => $user->email,
            'Role' => $user->role ?? '-',
            'Login Terakhir' => $user->last_login_at?->toDateString() ?? 'Belum Pernah',
            'Dibuat' => $user->created_at?->toDateString() ?? '-',
        ])->toArray();

        $this->table(
            ['ID', 'Nama', 'Email', 'Role', 'Login Terakhir', 'Dibuat'],
            $tableData
        );

        $this->warn("📊 Total: {$inactiveUsers->count()} akun akan dinonaktifkan.");

        if ($isDryRun) {
            $this->info('🏷️  Mode --dry-run: Tidak ada perubahan yang dilakukan.');

            return self::SUCCESS;
        }

        // Eksekusi deaktivasi dalam transaksi
        $deactivatedCount = 0;

        DB::transaction(function () use ($inactiveUsers, &$deactivatedCount) {
            foreach ($inactiveUsers as $user) {
                $user->is_active = false;
                $user->save();

                // Untuk dokter: set status master & jadwal
                if ($user->doctor) {
                    $user->doctor->status = 'pensiun';
                    $user->doctor->save();

                    DoctorSchedule::where('doctor_id', $user->doctor->doctor_id)
                        ->where('status', 'Aktif')
                        ->update(['status' => 'Libur']);
                }

                $deactivatedCount++;
            }
        });

        $this->info("✅ Berhasil menonaktifkan {$deactivatedCount} akun pengguna.");

        return self::SUCCESS;
    }
}
