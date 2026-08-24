<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class MedicineController
 * 
 * Manajemen Master Data & Inventori Obat Farmasi Rumah Sakit.
 * Dibatasi secara ketat hanya untuk Staf / Perawat Tetap (Pekerja) via Gate 'access-pekerja-only'.
 */
class MedicineController extends Controller
{
    /**
     * Menampilkan katalog dan inventori obat rumah sakit.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('access-pekerja-only');

        $search = $request->query('search');
        $type = $request->query('type');
        $stockStatus = $request->query('stock_status', 'all');

        $medicinesQuery = Medicine::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_medicine', 'ilike', "%{$search}%")
                        ->orWhere('code_medicine', 'ilike', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($stockStatus === 'out', function ($query) {
                $query->outOfStock();
            })
            ->when($stockStatus === 'low', function ($query) {
                $query->lowStock();
            })
            ->when($stockStatus === 'available', function ($query) {
                $query->available();
            })
            ->orderBy('name_medicine', 'asc');

        $medicines = $medicinesQuery->paginate(12)->withQueryString();

        // Agregasi Inventori Farmasi (Zero in-memory overhead)
        $stats = [
            'total_items'           => Medicine::count(),
            'out_of_stock_count'    => Medicine::outOfStock()->count(),
            'low_stock_count'       => Medicine::lowStock()->count(),
            'available_count'       => Medicine::available()->count(),
            'total_stock_units'     => (int) Medicine::sum('stock'),
            'total_inventory_value' => (float) (Medicine::selectRaw('SUM(stock * price) as val')->value('val') ?? 0),
        ];

        // Daftar jenis sediaan yang tersedia untuk opsi filter
        $availableTypes = Medicine::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->orderBy('type', 'asc')
            ->pluck('type');

        return Inertia::render('staff/Medicines/Index', [
            'medicines'      => $medicines,
            'stats'          => $stats,
            'availableTypes' => $availableTypes,
            'filters'        => [
                'search'       => $search,
                'type'         => $type,
                'stock_status' => $stockStatus,
            ],
        ]);
    }

    /**
     * Menambahkan master obat baru ke inventori farmasi.
     */
    public function store(StoreMedicineRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $medicine = Medicine::create([
            'code_medicine' => strtoupper(trim($validated['code_medicine'])),
            'name_medicine' => trim($validated['name_medicine']),
            'type'          => trim($validated['type']),
            'stock'         => (int) $validated['stock'],
            'unit'          => trim($validated['unit']),
            'price'         => (float) $validated['price'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Obat baru berhasil ditambahkan ke inventori.',
                'data'    => $medicine,
            ], 201);
        }

        return redirect()->back()->with('success', "Obat {$medicine->name_medicine} ({$medicine->code_medicine}) berhasil ditambahkan.");
    }

    /**
     * Memperbarui informasi obat farmasi.
     */
    public function update(UpdateMedicineRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $medicine = Medicine::findOrFail($id);
        $validated = $request->validated();

        $medicine->update([
            'code_medicine' => strtoupper(trim($validated['code_medicine'])),
            'name_medicine' => trim($validated['name_medicine']),
            'type'          => trim($validated['type']),
            'stock'         => (int) $validated['stock'],
            'unit'          => trim($validated['unit']),
            'price'         => (float) $validated['price'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Data obat berhasil diperbarui.',
                'data'    => $medicine->fresh(),
            ]);
        }

        return redirect()->back()->with('success', "Data obat {$medicine->name_medicine} berhasil diperbarui.");
    }

    /**
     * Melakukan penyesuaian stok inventori (Restok / Pengurangan / Koreksi Manual).
     */
    public function adjustStock(AdjustStockRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $medicine = Medicine::findOrFail($id);
        $validated = $request->validated();

        $type = $validated['type'];
        $amount = (int) $validated['amount'];
        $currentStock = (int) $medicine->stock;

        $newStock = match ($type) {
            'add'      => $currentStock + $amount,
            'subtract' => max(0, $currentStock - $amount),
            'set'      => max(0, $amount),
            default    => $currentStock,
        };

        $medicine->update([
            'stock' => $newStock,
        ]);

        $message = "Stok {$medicine->name_medicine} berhasil disesuaikan menjadi {$newStock} {$medicine->unit}.";

        if ($request->wantsJson()) {
            return response()->json([
                'status'        => true,
                'message'       => $message,
                'data'          => $medicine->fresh(),
                'previous_stock'=> $currentStock,
                'new_stock'     => $newStock,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Menghapus obat dari inventori jika belum terkait dengan riwayat resep.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        Gate::authorize('access-pekerja-only');

        $medicine = Medicine::withCount('prescriptionItems')->findOrFail($id);

        if ($medicine->prescription_items_count > 0) {
            $errorMsg = "Obat {$medicine->name_medicine} tidak dapat dihapus karena sudah memiliki riwayat resep medis terkait.";
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => $errorMsg,
                ], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $medicineName = $medicine->name_medicine;
        $medicine->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => "Obat {$medicineName} berhasil dihapus dari sistem.",
            ]);
        }

        return redirect()->back()->with('success', "Obat {$medicineName} berhasil dihapus dari inventori.");
    }
}
