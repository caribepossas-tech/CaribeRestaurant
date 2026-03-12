<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperadminSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin'], ['display_name' => 'Super Admin', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'admin@caribepos.com'],
            [
                'name' => 'CaribePOS SAS',
                'password' => bcrypt('CaribePOS2025@'),
            ]
        );

        $user->assignRole('Super Admin');
    }

}
