<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            [
                'code_medicine' => 'MED-PCT-500',
                'name_medicine' => 'Paracetamol 500 mg',
                'type' => 'Tablet',
                'stock' => 500,
                'unit' => 'Strip',
                'price' => 8500.00,
            ],
            [
                'code_medicine' => 'MED-AMX-500',
                'name_medicine' => 'Amoxicillin Trihydrate 500 mg',
                'type' => 'Kapsul',
                'stock' => 350,
                'unit' => 'Strip',
                'price' => 14000.00,
            ],
            [
                'code_medicine' => 'MED-CTZ-10',
                'name_medicine' => 'Cetirizine HCl 10 mg',
                'type' => 'Tablet',
                'stock' => 420,
                'unit' => 'Strip',
                'price' => 12500.00,
            ],
            [
                'code_medicine' => 'MED-IBU-400',
                'name_medicine' => 'Ibuprofen 400 mg',
                'type' => 'Tablet',
                'stock' => 300,
                'unit' => 'Strip',
                'price' => 11000.00,
            ],
            [
                'code_medicine' => 'MED-OMZ-20',
                'name_medicine' => 'Omeprazole 20 mg',
                'type' => 'Kapsul',
                'stock' => 280,
                'unit' => 'Strip',
                'price' => 22000.00,
            ],
            [
                'code_medicine' => 'MED-AMN-10',
                'name_medicine' => 'Amlodipine Besylate 10 mg',
                'type' => 'Tablet',
                'stock' => 400,
                'unit' => 'Strip',
                'price' => 18500.00,
            ],
            [
                'code_medicine' => 'MED-MET-500',
                'name_medicine' => 'Metformin HCl 500 mg',
                'type' => 'Tablet',
                'stock' => 450,
                'unit' => 'Strip',
                'price' => 16000.00,
            ],
            [
                'code_medicine' => 'MED-DEX-05',
                'name_medicine' => 'Dexamethasone 0.5 mg',
                'type' => 'Tablet',
                'stock' => 320,
                'unit' => 'Strip',
                'price' => 7500.00,
            ],
            [
                'code_medicine' => 'MED-SLB-INH',
                'name_medicine' => 'Salbutamol Inhaler 100 mcg',
                'type' => 'Inhaler',
                'stock' => 75,
                'unit' => 'Pcs',
                'price' => 85000.00,
            ],
            [
                'code_medicine' => 'MED-ANT-SYR',
                'name_medicine' => 'Antasida Doen Sirup 60 ml',
                'type' => 'Sirup',
                'stock' => 150,
                'unit' => 'Botol',
                'price' => 13000.00,
            ],
            [
                'code_medicine' => 'MED-OBH-SYR',
                'name_medicine' => 'OBH Tropica Plus Sirup 100 ml',
                'type' => 'Sirup',
                'stock' => 180,
                'unit' => 'Botol',
                'price' => 28000.00,
            ],
            [
                'code_medicine' => 'MED-CIP-500',
                'name_medicine' => 'Ciprofloxacin 500 mg',
                'type' => 'Tablet',
                'stock' => 220,
                'unit' => 'Strip',
                'price' => 25000.00,
            ],
            [
                'code_medicine' => 'MED-ATV-20',
                'name_medicine' => 'Atorvastatin Calcium 20 mg',
                'type' => 'Tablet',
                'stock' => 190,
                'unit' => 'Strip',
                'price' => 42000.00,
            ],
            [
                'code_medicine' => 'MED-MVT-CAP',
                'name_medicine' => 'Multivitamin & Mineral Kompleks',
                'type' => 'Kapsul',
                'stock' => 500,
                'unit' => 'Strip',
                'price' => 15000.00,
            ],
            [
                'code_medicine' => 'MED-BCO-TAB',
                'name_medicine' => 'Vitamin B-Complex',
                'type' => 'Tablet',
                'stock' => 600,
                'unit' => 'Strip',
                'price' => 6000.00,
            ],
            [
                'code_medicine' => 'MED-ORS-SAC',
                'name_medicine' => 'Oralit Sachet 200 ml',
                'type' => 'Serbuk',
                'stock' => 800,
                'unit' => 'Sachet',
                'price' => 2500.00,
            ],
        ];

        foreach ($medicines as $item) {
            Medicine::firstOrCreate(
                ['code_medicine' => $item['code_medicine']],
                $item
            );
        }
    }
}
