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
        Role::create(['name' => 'Super Admin', 'display_name' => 'Super Admin', 'guard_name' => 'web']);

        $user  = User::create([
            'name' => 'CaribePOS SAS',
            'email' => 'admin@caribepos.com',
            'password' => bcrypt('CaribePOS2025@'),
        ]);

        $user->assignRole('Super Admin');

    }

}
