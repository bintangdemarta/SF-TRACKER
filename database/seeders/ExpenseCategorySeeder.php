<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'BBM', 'type' => 'bbm'],
            ['name' => 'Parkir', 'type' => 'mikro'],
            ['name' => 'Retribusi', 'type' => 'mikro'],
            ['name' => 'Makan/Minum', 'type' => 'mikro'],
            ['name' => 'Servis Rutin', 'type' => 'pemeliharaan'],
            ['name' => 'Ganti Oli', 'type' => 'pemeliharaan'],
            ['name' => 'Perbaikan Ban', 'type' => 'pemeliharaan'],
            ['name' => 'Sinking Fund Kendaraan', 'type' => 'sinking_fund'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
