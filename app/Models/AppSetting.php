<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Eloquent model untuk tabel app_settings (konfigurasi dinamis sistem SIMRS).
 *
 * Menyediakan static helper get()/set() dengan caching agar pengaturan
 * tampilan antrean publik (DisplayBoard), parameter operasional, dan
 * preferensi sistem lainnya dapat dibaca dengan performa tinggi.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AppSetting extends Model
{
    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Durasi cache dalam detik (5 menit).
     */
    private const CACHE_TTL = 300;

    /**
     * Prefix untuk cache key agar tidak bentrok dengan cache lain.
     */
    private const CACHE_PREFIX = 'app_setting:';

    /**
     * Ambil nilai pengaturan berdasarkan key, dengan fallback ke default.
     *
     * Menggunakan cache 5 menit untuk menghindari query berulang pada
     * setiap request (terutama untuk DisplayBoard yang di-polling setiap 5 detik).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_PREFIX.$key,
            self::CACHE_TTL,
            fn () => static::where('key', $key)->value('value') ?? $default
        );
    }

    /**
     * Simpan atau perbarui nilai pengaturan, lalu bust cache-nya.
     *
     * Menggunakan updateOrCreate agar key baru otomatis dibuat jika belum ada.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group]
        );

        Cache::forget(self::CACHE_PREFIX.$key);
    }

    /**
     * Ambil semua pengaturan berdasarkan grup tertentu.
     *
     * @return Collection<string, string|null>
     */
    public static function getByGroup(string $group): Collection
    {
        return static::where('group', $group)
            ->pluck('value', 'key');
    }
}
