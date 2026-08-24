<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service FEFODispensationService
 *
 * Mengelola algoritma First Expired, First Out (FEFO) untuk pengeluaran obat di Farmasi Rumah Sakit.
 * Memastikan obat dengan tanggal kedaluwarsa paling dekat dikeluarkan terlebih dahulu secara atomik.
 */
class FEFODispensationService
{
    /**
     * Potong stok obat menggunakan prinsip FEFO lintas batch aktif.
     *
     * @param Medicine $medicine Master obat yang akan dipotong
     * @param int $requestedQty Jumlah obat yang diminta dalam resep
     * @return array Daftar batch dan kuantitas yang dipotong
     * @throws InvalidArgumentException|RuntimeException
     */
    public function deductMedicine(Medicine $medicine, int $requestedQty): array
    {
        if ($requestedQty <= 0) {
            throw new InvalidArgumentException("Kuantitas pengeluaran obat harus lebih dari 0.");
        }

        if ($medicine->stock < $requestedQty) {
            $medName = $medicine->name_medicine ?? $medicine->name ?? 'Obat';
            throw new RuntimeException(
                "Stok obat '{$medName}' tidak mencukupi. Tersedia: {$medicine->stock}, diminta: {$requestedQty}."
            );
        }

        $deductedBatches = [];
        $remainingToDeduct = $requestedQty;

        // Ambil seluruh batch aktif diurutkan tanggal kedaluwarsa terdekat (FEFO)
        $batches = MedicineBatch::query()
            ->where('medicine_id', $medicine->medicine_id)
            ->where('stock_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductFromThisBatch = min($batch->stock_quantity, $remainingToDeduct);
            
            $batch->stock_quantity -= $deductFromThisBatch;
            $batch->save();

            $deductedBatches[] = [
                'medicine_batch_id' => $batch->medicine_batch_id,
                'batch_number'      => $batch->batch_number,
                'expiry_date'       => $batch->expiry_date->toDateString(),
                'quantity_deducted' => $deductFromThisBatch,
            ];

            $remainingToDeduct -= $deductFromThisBatch;
        }

        // Potong master stock obat
        $medicine->stock -= $requestedQty;
        $medicine->save();

        return $deductedBatches;
    }

    /**
     * Potong seluruh item obat dalam resep sekaligus secara transaksional
     *
     * @param \App\Models\Prescription $prescription
     * @return array
     */
    public function dispensePrescription(\App\Models\Prescription $prescription): array
    {
        return DB::transaction(function () use ($prescription) {
            $results = [];
            $prescription->loadMissing('items.medicine');

            foreach ($prescription->items as $item) {
                if ($item->medicine) {
                    $results[$item->medicine_id] = $this->deductMedicine($item->medicine, (int) $item->quantity);
                }
            }

            return $results;
        });
    }
}
