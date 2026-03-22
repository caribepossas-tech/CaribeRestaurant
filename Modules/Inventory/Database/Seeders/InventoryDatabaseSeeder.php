<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class InventoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Essential seeders always run (except codecanyon)
        if (!app()->environment('codecanyon')) {
            $this->call([
                InventoryItemCategoriesTableSeeder::class,
                UnitsTableSeeder::class,
                InventorySettingSeeder::class,
            ]);
        }

        // Demo data only in local/testing environments
        if (app()->environment('local', 'testing')) {
            $this->call([
                SuppliersTableSeeder::class,
                InventoryItemsTableSeeder::class,
                InventoryMovementsTableSeeder::class,
                InventoryStockTableSeeder::class,
                RecipesTableSeeder::class,
            ]);
        }
    }
}
