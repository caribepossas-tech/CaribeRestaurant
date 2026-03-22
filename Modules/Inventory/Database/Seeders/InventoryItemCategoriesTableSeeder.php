<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Modules\Inventory\Entities\InventoryItemCategory;

class InventoryItemCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = Branch::first()->id;

        foreach (InventoryItemCategory::CATEGORIES as $category) {
            InventoryItemCategory::firstOrCreate([
                'branch_id' => $branchId,
                'name' => $category
            ]);
        }
    }
}
