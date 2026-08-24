<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    /**
     * Menampilkan semua jadwal dokter.
     * Menggunakan eager loading (with) untuk memuat relasi doctor, poli, dan room sekaligus.
     * Mencegah N+1 Query saat data diakses oleh frontend.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DoctorSchedule::with(['doctor', 'poli', 'room']);

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->poli_id);
        }

        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('day', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar jadwal dokter berhasil diambil.',
            'data' => $schedules,
        ], 200);
    }

    /**
     * Menyimpan jadwal baru dengan validasi foreign key ke tabel induk.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctor,doctor_id'],
            'poli_id' => ['required', 'integer', 'exists:poli,poli_id'],
            'room_id' => ['required', 'integer', 'exists:room,room_id'],
            'day' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'quota_day' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:Aktif,Nonaktif'],
        ]);

        $schedule = DoctorSchedule::create($validated);
        $schedule->load(['doctor', 'poli', 'room']);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal dokter berhasil ditambahkan.',
            'data' => $schedule,
        ], 201);
    }

    /**
     * Menampilkan detail satu jadwal dokter.
     */
    public function show(string $id): JsonResponse
    {
        $schedule = DoctorSchedule::with(['doctor', 'poli', 'room'])->find($id);

        if (! $schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal dokter tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail jadwal dokter berhasil diambil.',
            'data' => $schedule,
        ], 200);
    }

    /**
     * Memperbarui data jadwal dokter.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $schedule = DoctorSchedule::find($id);

        if (! $schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal dokter tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'doctor_id' => ['sometimes', 'integer', 'exists:doctor,doctor_id'],
            'poli_id' => ['sometimes', 'integer', 'exists:poli,poli_id'],
            'room_id' => ['sometimes', 'integer', 'exists:room,room_id'],
            'day' => ['sometimes', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'quota_day' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:Aktif,Nonaktif'],
        ]);

        $schedule->update($validated);
        $schedule->load(['doctor', 'poli', 'room']);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal dokter berhasil diperbarui.',
            'data' => $schedule,
        ], 200);
    }

    /**
     * Menghapus jadwal dokter.
     */
    public function destroy(string $id): JsonResponse
    {
        $schedule = DoctorSchedule::find($id);

        if (! $schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal dokter tidak ditemukan.',
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal dokter berhasil dihapus.',
        ], 200);
    }
}
