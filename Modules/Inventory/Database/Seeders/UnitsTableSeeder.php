<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Modules\Inventory\Entities\Unit;

class UnitsTableSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = Branch::first()->id;

        foreach (Unit::UNITS as $unit) {
            Unit::firstOrCreate(array_merge($unit, [
                'branch_id' => $branchId // Assuming branch_id 1 exists
            ]));
        }
    }
}
