<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run($restaurant)
    {
        // Create global roles (not restaurant-specific)
        $adminRole = Role::firstOrCreate(['name' => 'Admin_'.$restaurant->id, 'guard_name' => 'web'], ['display_name' => 'Admin', 'restaurant_id' => $restaurant->id   ]);
        $branchHeadRole = Role::firstOrCreate(['name' => 'Branch Head_'.$restaurant->id, 'guard_name' => 'web'], ['display_name' => 'Branch Head', 'restaurant_id' => $restaurant->id]);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter_'.$restaurant->id, 'guard_name' => 'web'], ['display_name' => 'Waiter', 'restaurant_id' => $restaurant->id]);
        $chefRole = Role::firstOrCreate(['name' => 'Chef_'.$restaurant->id, 'guard_name' => 'web'], ['display_name' => 'Chef', 'restaurant_id' => $restaurant->id]);

        $allPermissions = Permission::get()->pluck('name')->toArray();
        $adminRole->syncPermissions($allPermissions);
        $branchHeadRole->syncPermissions($allPermissions);
        // Restaurant-specific roles will be created when restaurants are created
    }
}
