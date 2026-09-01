<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola pengaturan dinamis sistem SIMRS oleh Super Admin.
 *
 * Menangani CRUD key-value dari tabel app_settings, dikelompokkan berdasarkan
 * grup (display, general, operational). Perubahan pengaturan langsung
 * terefleksi di DisplayBoard dan modul terkait melalui cache busting.
 */
class AdminSettingController extends Controller
{
    /**
     * Tampilkan halaman manajemen pengaturan sistem.
     *
     * Mengambil seluruh pengaturan dari tabel app_settings, dikelompokkan
     * berdasarkan field `group`, lalu dikirim ke halaman Vue admin/Settings/Index.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $groupedSettings = [];
        $settings = AppSetting::orderBy('group')
            ->orderBy('key')
            ->get();

        foreach ($settings as $setting) {
            $groupedSettings[$setting->group][] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'group' => $setting->group,
            ];
        }

        $payload = [
            'settings' => $groupedSettings,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'App settings retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/Settings/Index', $payload);
    }

    /**
     * Perbarui pengaturan sistem secara bulk dari payload key-value.
     *
     * Menerima array `settings` berisi pasangan key-value. Setiap key
     * diperbarui melalui AppSetting::set() yang otomatis mem-bust cache,
     * sehingga perubahan langsung terlihat di DisplayBoard.
     *
     * @param  Request  $request  Payload: { settings: { key: value, ... } }
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            // Tentukan grup dari prefix key (display.*, operational.*, dll.)
            $group = str_contains($key, '.') ? explode('.', $key)[0] : 'general';
            AppSetting::set($key, $value, $group);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Pengaturan sistem berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
