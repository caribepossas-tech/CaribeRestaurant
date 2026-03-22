<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use Modules\Inventory\Entities\InventorySetting;

class InventorySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurantId = Restaurant::first()->id;

        InventorySetting::create([
            'restaurant_id' => $restaurantId,
            'allow_auto_purchase' => true,
        ]);
    }
}
